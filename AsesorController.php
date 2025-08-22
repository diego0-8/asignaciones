<?php
/**
 * Controlador del Asesor
 * Maneja todas las operaciones relacionadas con el rol de asesor
 */

require_once 'models/Database.php';

class AsesorController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Dashboard principal del asesor
     */
    public function dashboard() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'asesor') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener estadísticas del asesor
        $estadisticas = $this->getEstadisticasAsesor($_SESSION['user_id']);
        
        // Incluir la vista
        include 'views/asesor_dashboard.php';
    }
    
    /**
     * Gestionar clientes del asesor
     */
    public function clientes() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'asesor') {
            header('Location: index.php?action=login');
            exit;
        }
        
        $asesorId = $_SESSION['user_id'];
        
        // Obtener parámetros de paginación y búsqueda
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $estado_filter = isset($_GET['estado_filter']) ? $_GET['estado_filter'] : '';
        $tipificacion_filter = isset($_GET['tipificacion_filter']) ? $_GET['tipificacion_filter'] : '';
        
        // Configurar paginación
        $per_page = 5; // Mostrar solo 5 clientes por página
        $offset = ($page - 1) * $per_page;
        
        // Construir consulta base
        $where_conditions = ['asesor_id = ?'];
        $params = [$asesorId];
        
        if (!empty($search)) {
            $where_conditions[] = "(nombre_completo LIKE ? OR cedula LIKE ? OR telefono LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        if (!empty($estado_filter)) {
            $where_conditions[] = "estado_gestion = ?";
            $params[] = $estado_filter;
        }
        
        // Filtro por tipificación (última gestión del cliente)
        if (!empty($tipificacion_filter)) {
            $where_conditions[] = "id IN (
                SELECT DISTINCT hg.cliente_id 
                FROM historial_gestion hg 
                WHERE hg.tipo_gestion = ? OR 
                      (hg.tipo_contacto = 'no_contactado' AND ? = 'no_contactado')
                ORDER BY hg.fecha_gestion DESC
            )";
            $params[] = $tipificacion_filter;
            $params[] = $tipificacion_filter;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Obtener total de clientes
        $sql_count = "SELECT COUNT(*) as total FROM clientes WHERE $where_clause";
        $total_result = $this->db->fetch($sql_count, $params);
        $total_clientes = $total_result['total'];
        
        // Calcular total de páginas
        $total_pages = ceil($total_clientes / $per_page);
        
        // Obtener clientes con paginación
        $sql = "SELECT * FROM clientes WHERE $where_clause ORDER BY fecha_creacion DESC LIMIT ? OFFSET ?";
        $params_with_pagination = array_merge($params, [$per_page, $offset]);
        $clientes = $this->db->fetchAll($sql, $params_with_pagination);
        
        include 'views/asesor_clientes.php';
    }
    
    /**
     * Gestionar cliente específico
     */
    public function gestionarCliente() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'asesor') {
            header('Location: index.php?action=login');
            exit;
        }
        
        $asesorId = $_SESSION['user_id'];
        $clienteId = $_GET['cliente_id'] ?? null;
        
        if (!$clienteId) {
            // Si no hay ID de cliente, redirigir a la lista de clientes
            header('Location: index.php?action=asesor_clientes');
            exit;
        }
        
        // Obtener datos del cliente específico
        $sql = "SELECT * FROM clientes WHERE id = ? AND asesor_id = ?";
        $cliente = $this->db->fetch($sql, [$clienteId, $asesorId]);
        
        if (!$cliente) {
            // Si el cliente no existe o no pertenece a este asesor, redirigir
            header('Location: index.php?action=asesor_clientes');
            exit;
        }
        
        // Obtener historial de gestiones del cliente con información detallada
        $sql = "SELECT 
                    hg.*,
                    u.nombre_completo as asesor_nombre,
                    CASE 
                        WHEN hg.tipo_gestion = 'asignacion_cita' THEN 'Cita Programada'
                        WHEN hg.tipo_gestion = 'volver_llamar' THEN 'Volver a Llamar'
                        WHEN hg.tipo_gestion = 'fuera_ciudad' THEN 'Fuera de Ciudad'
                        WHEN hg.tipo_gestion = 'no_interesa' THEN 'No le Interesa'
                        WHEN hg.tipo_contacto = 'no_contactado' THEN 'No Contactado'
                        ELSE hg.tipo_gestion
                    END as estado_gestion_formateado
                FROM historial_gestion hg
                LEFT JOIN usuarios u ON hg.asesor_id = u.id
                WHERE hg.cliente_id = ? 
                ORDER BY hg.fecha_gestion DESC";
        $historialGestion = $this->db->fetchAll($sql, [$clienteId]);
        
        // Obtener citas del cliente
        $sql = "SELECT * FROM citas WHERE cliente_id = ? ORDER BY fecha_cita DESC";
        $citas = $this->db->fetchAll($sql, [$clienteId]);
        
        include 'views/asesor_gestionar_cliente.php';
    }
    
    /**
     * Gestionar citas del asesor
     */
    public function citas() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'asesor') {
            header('Location: index.php?action=login');
            exit;
        }
        
        $asesorId = $_SESSION['user_id'];
        
        // Obtener parámetros de paginación y búsqueda
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $estado_filter = isset($_GET['estado_filter']) ? $_GET['estado_filter'] : '';
        
        // Configurar paginación
        $per_page = 5; // Mostrar solo 5 citas por página
        $offset = ($page - 1) * $per_page;
        
        // Construir consulta base
        $where_conditions = ['c.asesor_id = ?'];
        $params = [$asesorId];
        
        if (!empty($search)) {
            $where_conditions[] = "(cl.nombre_completo LIKE ? OR cl.cedula LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        if (!empty($estado_filter)) {
            $where_conditions[] = "c.estado = ?";
            $params[] = $estado_filter;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Obtener total de citas
        $sql_count = "SELECT COUNT(*) as total FROM citas c 
                      INNER JOIN clientes cl ON c.cliente_id = cl.id 
                      WHERE $where_clause";
        $total_result = $this->db->fetch($sql_count, $params);
        $total_citas = $total_result['total'];
        
        // Calcular total de páginas
        $total_pages = ceil($total_citas / $per_page);
        
        // Obtener citas con paginación
        $sql = "SELECT c.*, cl.nombre_completo as cliente_nombre, cl.cedula, cl.telefono 
                FROM citas c 
                INNER JOIN clientes cl ON c.cliente_id = cl.id 
                WHERE $where_clause 
                ORDER BY c.fecha_cita DESC 
                LIMIT ? OFFSET ?";
        $params_with_pagination = array_merge($params, [$per_page, $offset]);
        $citas = $this->db->fetchAll($sql, $params_with_pagination);
        
        include 'views/asesor_citas.php';
    }
    
    /**
     * Cerrar sesión del asesor
     */
    public function cerrarSesion() {
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }
    
    /**
     * Obtener estadísticas del asesor
     */
    private function getEstadisticasAsesor($asesorId) {
        $sql = "SELECT 
                    COUNT(CASE WHEN c.estado_gestion = 'En Proceso' THEN 1 END) as clientes_gestionados,
                    COUNT(CASE WHEN c.estado_gestion = 'Disponible' THEN 1 END) as clientes_pendientes,
                    COUNT(CASE WHEN c.estado_gestion = 'Cita Programada' THEN 1 END) as citas_registradas
                FROM clientes c 
                WHERE c.asesor_id = ?";
        
        return $this->db->fetch($sql, [$asesorId]);
    }
    
    /**
     * Obtener clientes asignados al asesor
     */
    public function getClientesAsignados($asesorId) {
        $sql = "SELECT * FROM clientes WHERE asesor_id = ? ORDER BY fecha_creacion DESC";
        return $this->db->fetchAll($sql, [$asesorId]);
    }
    
    /**
     * Obtener citas del asesor
     */
    public function getCitasAsesor($asesorId) {
        $sql = "SELECT c.*, cl.nombre_completo as cliente_nombre, cl.telefono 
                FROM citas c 
                INNER JOIN clientes cl ON c.cliente_id = cl.id 
                WHERE c.asesor_id = ? 
                ORDER BY c.fecha_cita DESC";
        return $this->db->fetchAll($sql, [$asesorId]);
    }
    
    /**
     * Guardar gestión del cliente
     */
    public function guardarGestion() {
        // Log para debugging
        error_log("=== INICIO guardarGestion ===");
        error_log("POST data: " . print_r($_POST, true));
        error_log("SESSION data: " . print_r($_SESSION ?? [], true));
        error_log("SERVER data: " . print_r($_SERVER, true));
        
        // Verificar sesión de manera más robusta
        if (!isset($_SESSION) || !isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            error_log("Error: Sesión no válida o no iniciada");
            $this->jsonResponse(['success' => false, 'error' => 'Sesión no válida'], 401);
            return;
        }
        
        if ($_SESSION['user_role'] !== 'asesor') {
            error_log("Error: Usuario no es asesor. Rol: " . $_SESSION['user_role']);
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        // Verificar método de manera más robusta
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        if ($requestMethod !== 'POST') {
            error_log("Error: Método no permitido. Método: " . $requestMethod);
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        $asesorId = $_SESSION['user_id'];
        $clienteId = $_POST['cliente_id'] ?? null;
        $tipoContacto = $_POST['tipo_contacto'] ?? null;
        $tipoGestion = $_POST['tipo_gestion'] ?? null;
        $observaciones = $_POST['observaciones'] ?? null;
        $submitToken = $_POST['submit_token'] ?? null;
        
        error_log("Datos recibidos:");
        error_log("- Asesor ID: " . $asesorId);
        error_log("- Cliente ID: " . $clienteId);
        error_log("- Tipo Contacto: " . $tipoContacto);
        error_log("- Tipo Gestión: " . $tipoGestion);
        error_log("- Observaciones: " . $observaciones);
        error_log("- Submit Token: " . $submitToken);
        
        // Validar datos requeridos
        if (!$clienteId || !$tipoContacto || !$observaciones) {
            error_log("Error: Datos incompletos");
            $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
            return;
        }
        
        // PREVENCIÓN DE DUPLICADOS - Verificar si ya existe una gestión similar reciente
        error_log("Verificando duplicados...");
        $sql = "SELECT id FROM historial_gestion 
                WHERE cliente_id = ? AND asesor_id = ? AND tipo_contacto = ? 
                AND tipo_gestion = ? AND observaciones = ? 
                AND fecha_gestion >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                ORDER BY fecha_gestion DESC 
                LIMIT 1";
        
        $paramsDuplicado = [$clienteId, $asesorId, $tipoContacto, $tipoGestion, $observaciones];
        $duplicado = $this->db->fetch($sql, $paramsDuplicado);
        
        if ($duplicado) {
            error_log("⚠️ DUPLICADO DETECTADO - ID: " . $duplicado['id']);
            error_log("Se encontró una gestión similar en los últimos 5 minutos");
            $this->jsonResponse([
                'success' => false, 
                'error' => 'Esta gestión ya fue registrada recientemente. Evitando duplicado.',
                'duplicate_id' => $duplicado['id']
            ], 409); // Conflict - Duplicado
            return;
        }
        
        // Validar que el cliente pertenece a este asesor
        $sql = "SELECT id FROM clientes WHERE id = ? AND asesor_id = ?";
        $cliente = $this->db->fetch($sql, [$clienteId, $asesorId]);
        
        if (!$cliente) {
            error_log("Error: Cliente no válido. Cliente ID: $clienteId, Asesor ID: $asesorId");
            $this->jsonResponse(['success' => false, 'error' => 'Cliente no válido'], 400);
            return;
        }
        
        try {
            error_log("Iniciando transacción...");
            $this->db->beginTransaction();
            
            // Insertar en historial_gestion
            $sql = "INSERT INTO historial_gestion (
                        cliente_id, asesor_id, tipo_contacto, tipo_gestion, 
                        observaciones, resultado
                    ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $resultado = $tipoGestion ?: $tipoContacto;
            $params = [$clienteId, $asesorId, $tipoContacto, $tipoGestion, $observaciones, $resultado];
            
            error_log("Ejecutando SQL: " . $sql);
            error_log("Parámetros: " . print_r($params, true));
            
            $result = $this->db->query($sql, $params);
            
            if (!$result) {
                throw new Exception("Error al insertar en historial_gestion");
            }
            
            $gestionId = $this->db->getConnection()->lastInsertId();
            error_log("Gestión insertada con ID: " . $gestionId);
            
            // Si es asignación de cita, insertar en tabla citas
            if ($tipoGestion === 'asignacion_cita') {
                $fechaCita = $_POST['fecha_cita'] ?? null;
                $horaCita = $_POST['hora_cita'] ?? null;
                $lugarCita = $_POST['lugar_cita'] ?? null;
                
                // Validar que todos los campos de cita estén presentes
                if (!$fechaCita || !$horaCita || !$lugarCita) {
                    throw new Exception("Para asignar una cita, todos los campos (fecha, hora y lugar) son obligatorios");
                }
                
                if ($fechaCita && $horaCita && $lugarCita) {
                    $sql = "INSERT INTO citas (
                                cliente_id, asesor_id, fecha_cita, hora_cita, 
                                lugar_cita, estado, fecha_creacion
                            ) VALUES (?, ?, ?, ?, ?, 'Programada', NOW())";
                    
                    $result = $this->db->query($sql, [
                        $clienteId, $asesorId, $fechaCita, $horaCita, $lugarCita
                    ]);
                    
                    if (!$result) {
                        throw new Exception("Error al insertar cita");
                    }
                    
                    // Actualizar estado del cliente
                    $sql = "UPDATE clientes SET estado_gestion = 'Cita Programada' WHERE id = ?";
                    $this->db->query($sql, [$clienteId]);
                    error_log("Cita programada y estado del cliente actualizado");
                }
            }
            
            // Si es volver a llamar, actualizar fecha de próximo contacto
            if ($tipoGestion === 'volver_llamar') {
                $fechaProximo = $_POST['fecha_proximo_contacto'] ?? null;
                $horaProximo = $_POST['hora_proximo_contacto'] ?? null;
                
                // Validar que todos los campos de volver a llamar estén presentes
                if (!$fechaProximo || !$horaProximo) {
                    throw new Exception("Para marcar volver a llamar, fecha y hora son obligatorios");
                }
                
                if ($fechaProximo && $horaProximo) {
                    $sql = "UPDATE clientes SET 
                                estado_gestion = 'En Proceso',
                                proxima_fecha = ? 
                            WHERE id = ?";
                    
                    $this->db->query($sql, ["$fechaProximo $horaProximo", $clienteId]);
                    error_log("Cliente marcado para nueva llamada");
                }
            }
            
            // Actualizar estado del cliente según el tipo de gestión
            if ($tipoGestion === 'fuera_ciudad' || $tipoGestion === 'no_interesa') {
                $nuevoEstado = $tipoGestion === 'fuera_ciudad' ? 'Fuera de Ciudad' : 'No Interesado';
                $sql = "UPDATE clientes SET estado_gestion = ? WHERE id = ?";
                $this->db->query($sql, [$nuevoEstado, $clienteId]);
                error_log("Estado del cliente actualizado a: " . $nuevoEstado);
            }
            
            error_log("Confirmando transacción...");
            $this->db->commit();
            
            // ACTUALIZAR ESTADO DEL CLIENTE A 'GESTIONADO'
            $sql = "UPDATE clientes SET estado_gestion = 'gestionado' WHERE id = ?";
            $this->db->query($sql, [$clienteId]);
            error_log("Estado del cliente actualizado a 'gestionado'");
            
            $mensaje = "Gestión guardada correctamente";
            if ($tipoGestion === 'asignacion_cita') {
                $mensaje .= ". Cita programada exitosamente.";
            } elseif ($tipoGestion === 'volver_llamar') {
                $mensaje .= ". Cliente marcado para nueva llamada.";
            }
            
            error_log("Enviando respuesta exitosa: " . $mensaje);
            $this->jsonResponse([
                'success' => true,
                'message' => $mensaje,
                'gestion_id' => $gestionId,
                'submit_token' => $submitToken // Devolver el token para confirmación
            ]);
            
        } catch (Exception $e) {
            error_log("Error en guardarGestion: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            $this->db->rollback();
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Obtener detalles de una gestión específica
     */
    public function obtenerDetallesGestion() {
        // Log para debugging
        error_log("=== INICIO obtenerDetallesGestion ===");
        error_log("POST data: " . print_r($_POST, true));
        
        // Verificar sesión
        if (!isset($_SESSION) || !isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            error_log("Error: Sesión no válida");
            $this->jsonResponse(['success' => false, 'error' => 'Sesión no válida'], 401);
            return;
        }
        
        if ($_SESSION['user_role'] !== 'asesor') {
            error_log("Error: Usuario no es asesor");
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $asesorId = $_SESSION['user_id'];
        $gestionId = $_POST['gestion_id'] ?? null;
        $tipoGestion = $_POST['tipo_gestion'] ?? null;
        
        if (!$gestionId || !$tipoGestion) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
            return;
        }
        
        try {
            // Obtener detalles de la gestión
            $sql = "SELECT hg.*, c.nombre_completo as cliente_nombre, u.nombre_completo as asesor_nombre
                    FROM historial_gestion hg
                    INNER JOIN clientes c ON hg.cliente_id = c.id
                    INNER JOIN usuarios u ON hg.asesor_id = u.id
                    WHERE hg.id = ? AND hg.asesor_id = ?";
            
            $gestion = $this->db->fetch($sql, [$gestionId, $asesorId]);
            
            if (!$gestion) {
                $this->jsonResponse(['success' => false, 'error' => 'Gestión no encontrada'], 404);
                return;
            }
            
            $detalles = $gestion;
            
            // Obtener detalles adicionales según el tipo de gestión
            if ($tipoGestion === 'asignacion_cita') {
                // Buscar detalles de la cita
                $sql = "SELECT * FROM citas WHERE cliente_id = ? AND asesor_id = ? ORDER BY fecha_creacion DESC LIMIT 1";
                $cita = $this->db->fetch($sql, [$gestion['cliente_id'], $asesorId]);
                
                if ($cita) {
                    $detalles['fecha_cita'] = date('d/m/Y', strtotime($cita['fecha_cita']));
                    $detalles['hora_cita'] = date('H:i', strtotime($cita['hora_cita']));
                    $detalles['lugar_cita'] = $cita['lugar_cita'];
                }
                
            } elseif ($tipoGestion === 'volver_llamar') {
                // Buscar detalles de próxima fecha
                $sql = "SELECT proxima_fecha FROM clientes WHERE id = ?";
                $cliente = $this->db->fetch($sql, [$gestion['cliente_id']]);
                
                if ($cliente && $cliente['proxima_fecha']) {
                    $fechaHora = new DateTime($cliente['proxima_fecha']);
                    $detalles['fecha_proximo_contacto'] = $fechaHora->format('d/m/Y');
                    $detalles['hora_proximo_contacto'] = $fechaHora->format('H:i');
                }
            }
            
            // Formatear fecha de gestión
            $detalles['fecha_gestion'] = date('d/m/Y H:i', strtotime($gestion['fecha_gestion']));
            
            error_log("Detalles obtenidos exitosamente para gestión ID: " . $gestionId);
            
            $this->jsonResponse([
                'success' => true,
                'detalles' => $detalles
            ]);
            
        } catch (Exception $e) {
            error_log("Error en obtenerDetallesGestion: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Obtener notificaciones del día para el asesor
     * Solo clientes con tipificación "volver_llamar" para la fecha específica
     */
    public function obtenerNotificaciones() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'asesor') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $asesorId = $_SESSION['user_id'];
        $hoy = date('Y-m-d');
        
        try {
            // Obtener SOLO clientes con tipificación "volver_llamar" para hoy
            // y que NO hayan sido gestionados después de esa tipificación
            $sql = "SELECT DISTINCT 
                        c.id as cliente_id,
                        c.nombre_completo,
                        c.cedula,
                        c.telefono,
                        hg.tipo_gestion,
                        hg.fecha_gestion,
                        hg.proxima_fecha,
                        'Volver a Llamar' as tipificacion_nombre
                    FROM clientes c
                    INNER JOIN historial_gestion hg ON c.id = hg.cliente_id
                    WHERE c.asesor_id = ? 
                    AND hg.tipo_gestion = 'volver_llamar'
                    AND DATE(hg.fecha_gestion) = ?
                    AND hg.id = (
                        -- Obtener la gestión más reciente de este cliente
                        SELECT MAX(hg2.id)
                        FROM historial_gestion hg2
                        WHERE hg2.cliente_id = c.id
                        AND hg2.asesor_id = ?
                    )
                    AND NOT EXISTS (
                        -- Verificar que no haya gestiones posteriores que cambien la tipificación
                        SELECT 1
                        FROM historial_gestion hg3
                        WHERE hg3.cliente_id = c.id
                        AND hg3.asesor_id = ?
                        AND hg3.fecha_gestion > hg.fecha_gestion
                        AND hg3.tipo_gestion != 'volver_llamar'
                    )
                    ORDER BY hg.fecha_gestion DESC";
            
            $notificaciones = $this->db->fetchAll($sql, [$asesorId, $hoy, $asesorId, $asesorId]);
            
            $this->jsonResponse([
                'success' => true,
                'notificaciones' => $notificaciones
            ]);
            
        } catch (Exception $e) {
            error_log("Error en obtenerNotificaciones: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Obtener notificaciones de una fecha específica para el asesor
     * Útil para mostrar notificaciones futuras programadas
     */
    public function obtenerNotificacionesPorFecha() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'asesor') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $asesorId = $_SESSION['user_id'];
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        
        try {
            // Obtener clientes con tipificación "volver_llamar" para la fecha específica
            $sql = "SELECT DISTINCT 
                        c.id as cliente_id,
                        c.nombre_completo,
                        c.cedula,
                        c.telefono,
                        hg.tipo_gestion,
                        hg.fecha_gestion,
                        hg.proxima_fecha,
                        'Volver a Llamar' as tipificacion_nombre,
                        DATE(hg.fecha_gestion) as fecha_programada
                    FROM clientes c
                    INNER JOIN historial_gestion hg ON c.id = hg.cliente_id
                    WHERE c.asesor_id = ? 
                    AND hg.tipo_gestion = 'volver_llamar'
                    AND DATE(hg.fecha_gestion) = ?
                    AND hg.id = (
                        -- Obtener la gestión más reciente de este cliente
                        SELECT MAX(hg2.id)
                        FROM historial_gestion hg2
                        WHERE hg2.cliente_id = c.id
                        AND hg2.asesor_id = ?
                    )
                    AND NOT EXISTS (
                        -- Verificar que no haya gestiones posteriores que cambien la tipificación
                        SELECT 1
                        FROM historial_gestion hg3
                        WHERE hg3.cliente_id = c.id
                        AND hg3.asesor_id = ?
                        AND hg3.fecha_gestion > hg.fecha_gestion
                        AND hg3.tipo_gestion != 'volver_llamar'
                    )
                    ORDER BY hg.fecha_gestion DESC";
            
            $notificaciones = $this->db->fetchAll($sql, [$asesorId, $fecha, $asesorId, $asesorId]);
            
            $this->jsonResponse([
                'success' => true,
                'notificaciones' => $notificaciones,
                'fecha_consultada' => $fecha
            ]);
            
        } catch (Exception $e) {
            error_log("Error en obtenerNotificacionesPorFecha: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
}
?>
