<?php
/**
 * Controlador del Coordinador
 * Maneja todas las operaciones relacionadas con el rol de coordinador
 */

require_once 'models/Database.php';

class CoordinadorController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Dashboard principal del coordinador
     */
    public function dashboard() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener estadísticas generales
        $estadisticasGenerales = $this->getEstadisticasCoordinador($_SESSION['user_id']);
        
        // Obtener información detallada de asesores
        $estadisticas = $this->getEstadisticasDetalladasAsesores($_SESSION['user_id']);
        
        // Obtener totales
        $totalClientesCargados = $this->getTotalClientesCargados($_SESSION['user_id']);
        $totalClientesAsignados = $this->getTotalClientesAsignados($_SESSION['user_id']);
        $totalClientesDisponibles = $this->getTotalClientesDisponibles($_SESSION['user_id']);
        
        // Incluir la vista
        include 'views/coordinador_dashboard.php';
    }
    
    /**
     * Cargar archivo de clientes
     */
    public function cargarArchivo() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Verificar si ya existe una base de datos para este coordinador
        $coordinadorId = $_SESSION['user_id'];
        $baseExistente = $this->verificarBaseExistente($coordinadorId);
        
        include 'views/coordinador_cargar_archivo.php';
    }
    
    /**
     * Procesar archivo CSV de clientes
     */
    public function procesarArchivo() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'error' => 'Error al subir archivo'], 400);
            return;
        }
        
        $archivo = $_FILES['archivo'];
        $coordinadorId = $_SESSION['user_id'];
        
        // Verificar tipo de archivo
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $this->jsonResponse(['success' => false, 'error' => 'Solo se permiten archivos CSV'], 400);
            return;
        }
        
        // Verificar tamaño (máximo 500MB)
        if ($archivo['size'] > 500 * 1024 * 1024) {
            $this->jsonResponse(['success' => false, 'error' => 'El archivo es demasiado grande. Máximo 500MB'], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $clientesProcesados = 0;
            $errores = [];
            $batchSize = 1000; // Procesar en lotes de 1000
            $batch = [];
            
            // Abrir archivo CSV
            $handle = fopen($archivo['tmp_name'], 'r');
            if (!$handle) {
                throw new Exception('No se pudo abrir el archivo');
            }
            
            // Leer encabezados
            $encabezados = fgetcsv($handle);
            if (!$encabezados) {
                throw new Exception('Archivo CSV vacío o inválido');
            }
            
            // Mapear columnas
            $columnas = array_map('strtolower', $encabezados);
            $nombreIndex = array_search('nombre', $columnas);
            $cedulaIndex = array_search('cedula', $columnas);
            $telefonoIndex = array_search('telefono', $columnas);
            
            if ($nombreIndex === false || $cedulaIndex === false) {
                throw new Exception('El archivo debe contener las columnas: nombre, cedula, telefono');
            }
            
            // Procesar archivo línea por línea
            $linea = 2; // Empezar desde la línea 2 (después de encabezados)
            while (($datos = fgetcsv($handle)) !== false) {
                try {
                    $nombre = trim($datos[$nombreIndex] ?? '');
                    $cedula = trim($datos[$cedulaIndex] ?? '');
                    $telefono = trim($datos[$telefonoIndex] ?? '');
                    
                    // Validaciones básicas
                    if (empty($nombre) || empty($cedula)) {
                        $errores[] = "Línea $linea: Nombre y cédula son obligatorios";
                        $linea++;
                        continue;
                    }
                    
                    // Verificar si la cédula ya existe
                    $sql = "SELECT id FROM clientes WHERE cedula = ? AND coordinador_id = ?";
                    $existente = $this->db->fetch($sql, [$cedula, $coordinadorId]);
                    
                    if ($existente) {
                        $errores[] = "Línea $linea: Cédula $cedula ya existe";
                        $linea++;
                        continue;
                    }
                    
                    // Agregar a lote
                    $batch[] = [
                        'nombre_completo' => $nombre,
                        'cedula' => $cedula,
                        'telefono' => $telefono,
                        'coordinador_id' => $coordinadorId,
                        'estado_gestion' => 'Disponible',
                        'fecha_creacion' => date('Y-m-d H:i:s')
                    ];
                    
                    // Procesar lote cuando alcance el tamaño
                    if (count($batch) >= $batchSize) {
                        $this->procesarLoteClientes($batch);
                        $clientesProcesados += count($batch);
                        $batch = [];
                        
                        // Liberar memoria
                        gc_collect_cycles();
                    }
                    
                } catch (Exception $e) {
                    $errores[] = "Línea $linea: " . $e->getMessage();
                }
                
                $linea++;
            }
            
            // Procesar lote restante
            if (!empty($batch)) {
                $this->procesarLoteClientes($batch);
                $clientesProcesados += count($batch);
            }
            
            fclose($handle);
            
            // Confirmar transacción
            $this->db->commit();
            
            $mensaje = "Se procesaron exitosamente $clientesProcesados cliente(s)";
            if (!empty($errores)) {
                $mensaje .= ". Errores: " . count($errores);
            }
            
            $this->jsonResponse([
                'success' => true,
                'message' => $mensaje,
                'clientes_procesados' => $clientesProcesados,
                'errores' => $errores
            ]);
            
        } catch (Exception $e) {
            try {
                $this->db->rollback();
            } catch (Exception $rollbackError) {
                // Ignorar errores de rollback
            }
            if (isset($handle)) {
                fclose($handle);
            }
            
            $this->jsonResponse(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Procesar lote de clientes para optimizar memoria
     */
    private function procesarLoteClientes($batch) {
        if (empty($batch)) return;
        
        $placeholders = str_repeat('(?, ?, ?, ?, ?, ?),', count($batch));
        $placeholders = rtrim($placeholders, ',');
        
        $sql = "INSERT INTO clientes (nombre_completo, cedula, telefono, coordinador_id, estado_gestion, fecha_creacion) VALUES $placeholders";
        
        $valores = [];
        foreach ($batch as $cliente) {
            $valores[] = $cliente['nombre_completo'];
            $valores[] = $cliente['cedula'];
            $valores[] = $cliente['telefono'];
            $valores[] = $cliente['coordinador_id'];
            $valores[] = $cliente['estado_gestion'];
            $valores[] = $cliente['fecha_creacion'];
        }
        
        $this->db->query($sql, $valores);
    }
    
    /**
     * Gestionar tareas
     */
    public function tareas() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener estadísticas y datos necesarios para la vista
        $totalClientesCargados = $this->getTotalClientesCargados($_SESSION['user_id']);
        $totalClientesAsignados = $this->getTotalClientesAsignados($_SESSION['user_id']);
        $totalClientesDisponibles = $this->getTotalClientesDisponibles($_SESSION['user_id']);
        
        // Obtener asesores del coordinador
        $asesores = $this->getAsesoresDelCoordinador($_SESSION['user_id']);
        
        // Obtener clientes disponibles
        $clientesDisponibles = $this->getClientesDisponibles($_SESSION['user_id']);
        
        include 'views/coordinador_tareas_mejorada.php';
    }
    
    /**
     * Transferir clientes
     */
    public function transferirClientes() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener asesores del coordinador
        $asesores = $this->getAsesoresDelCoordinador($_SESSION['user_id']);
        
        // Obtener estadísticas para mostrar
        $totalClientesCargados = $this->getTotalClientesCargados($_SESSION['user_id']);
        $totalClientesAsignados = $this->getTotalClientesAsignados($_SESSION['user_id']);
        $totalClientesDisponibles = $this->getTotalClientesDisponibles($_SESSION['user_id']);
        
        include 'views/coordinador_transferir_clientes.php';
    }
    
    /**
     * Descargar archivos
     */
    public function descargarArchivos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        include 'views/coordinador_descargar_archivos.php';
    }
    
    /**
     * Obtener detalles completos de un asesor
     */
    public function obtenerDetallesAsesor() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $asesorId = $_POST['asesor_id'] ?? null;
        
        if (!$asesorId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID de asesor requerido'], 400);
            return;
        }
        
        try {
            // Verificar que el asesor pertenece al coordinador
            $sql = "SELECT u.id, u.nombre_completo, u.usuario, u.estado
                    FROM usuarios u
                    WHERE u.id = ? AND u.coordinador_id = ? AND u.rol = 'asesor'";
            
            $asesor = $this->db->fetch($sql, [$asesorId, $coordinadorId]);
            
            if (!$asesor) {
                $this->jsonResponse(['success' => false, 'error' => 'Asesor no encontrado o no autorizado'], 404);
                return;
            }
            
            // Obtener estadísticas del asesor
            $estadisticas = $this->getEstadisticasAsesor($asesorId);
            
            // Obtener clientes del asesor con información detallada
            $clientes = $this->getClientesDelAsesor($asesorId);
            
            // Combinar información
            $asesorCompleto = array_merge($asesor, $estadisticas);
            
            $this->jsonResponse([
                'success' => true,
                'asesor' => $asesorCompleto,
                'clientes' => $clientes
            ]);
            
        } catch (Exception $e) {
            error_log("Error en obtenerDetallesAsesor: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Obtener asesores disponibles para transferencia
     */
    public function obtenerAsesoresDisponibles() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        
        try {
            // Obtener asesores del coordinador (excluyendo el asesor actual si se especifica)
            $sql = "SELECT u.id, u.nombre_completo, u.estado
                    FROM usuarios u
                    WHERE u.coordinador_id = ? AND u.rol = 'asesor' AND u.estado = 'Activo'
                    ORDER BY u.nombre_completo";
            
            $asesores = $this->db->fetchAll($sql, [$coordinadorId]);
            
            $this->jsonResponse([
                'success' => true,
                'asesores' => $asesores
            ]);
            
        } catch (Exception $e) {
            error_log("Error en obtenerAsesoresDisponibles: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Transferir cliente a otro asesor
     */
    public function transferirCliente() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $clienteId = $_POST['cliente_id'] ?? null;
        $nuevoAsesorId = $_POST['nuevo_asesor_id'] ?? null;
        $motivo = $_POST['motivo'] ?? '';
        
        if (!$clienteId || !$nuevoAsesorId) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
            return;
        }
        
        try {
            // Verificar que el cliente pertenece al coordinador
            $sql = "SELECT c.id, c.nombre_completo, c.asesor_id
                    FROM clientes c
                    INNER JOIN usuarios u ON c.asesor_id = u.id
                    WHERE c.id = ? AND u.coordinador_id = ?";
            
            $cliente = $this->db->fetch($sql, [$clienteId, $coordinadorId]);
            
            if (!$cliente) {
                $this->jsonResponse(['success' => false, 'error' => 'Cliente no encontrado o no autorizado'], 404);
                return;
            }
            
            // Verificar que el nuevo asesor pertenece al coordinador
            $sql = "SELECT u.id, u.nombre_completo
                    FROM usuarios u
                    WHERE u.id = ? AND u.coordinador_id = ? AND u.rol = 'asesor'";
            
            $nuevoAsesor = $this->db->fetch($sql, [$nuevoAsesorId, $coordinadorId]);
            
            if (!$nuevoAsesor) {
                $this->jsonResponse(['success' => false, 'error' => 'Nuevo asesor no válido'], 400);
                return;
            }
            
            // Iniciar transacción
            $this->db->beginTransaction();
            
            try {
                // Actualizar asesor del cliente
                $sql = "UPDATE clientes SET asesor_id = ? WHERE id = ?";
                $this->db->query($sql, [$nuevoAsesorId, $clienteId]);
                
                // Registrar la transferencia en historial
                $sql = "INSERT INTO historial_transferencias (
                    cliente_id, asesor_anterior_id, asesor_nuevo_id, 
                    coordinador_id, motivo, fecha_transferencia
                ) VALUES (?, ?, ?, ?, ?, NOW())";
                
                $this->db->query($sql, [
                    $clienteId, 
                    $cliente['asesor_id'], 
                    $nuevoAsesorId, 
                    $coordinadorId, 
                    $motivo
                ]);
                
                // Confirmar transacción
                $this->db->commit();
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Cliente transferido exitosamente',
                    'cliente' => $cliente['nombre_completo'],
                    'asesor_anterior' => $cliente['asesor_id'],
                    'asesor_nuevo' => $nuevoAsesor['nombre_completo']
                ]);
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error en transferirCliente: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Cerrar sesión del coordinador
     */
    public function cerrarSesion() {
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }
    
    /**
     * Obtener estadísticas del coordinador
     */
    private function getEstadisticasCoordinador($coordinadorId) {
        $sql = "SELECT 
                    COUNT(DISTINCT c.id) as total_clientes,
                    COUNT(DISTINCT CASE WHEN c.estado_gestion = 'Asignado' THEN c.id END) as clientes_asignados,
                    COUNT(DISTINCT CASE WHEN c.estado_gestion = 'Disponible' THEN c.id END) as clientes_disponibles,
                    COUNT(DISTINCT u.id) as total_asesores
                FROM usuarios u
                LEFT JOIN clientes c ON u.id = c.asesor_id
                WHERE u.coordinador_id = ? AND u.rol = 'asesor' AND u.estado = 'Activo'";
        
        return $this->db->fetch($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener total de clientes cargados por el coordinador
     */
    private function getTotalClientesCargados($coordinadorId) {
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE coordinador_id = ?";
        $result = $this->db->fetch($sql, [$coordinadorId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtener total de clientes asignados
     */
    private function getTotalClientesAsignados($coordinadorId) {
        $sql = "SELECT COUNT(*) as total FROM clientes c 
                INNER JOIN usuarios u ON c.asesor_id = u.id 
                WHERE u.coordinador_id = ? AND c.estado_gestion = 'Asignado'";
        $result = $this->db->fetch($sql, [$coordinadorId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtener total de clientes disponibles
     */
    private function getTotalClientesDisponibles($coordinadorId) {
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE coordinador_id = ? AND estado_gestion = 'Disponible'";
        $result = $this->db->fetch($sql, [$coordinadorId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtener estadísticas detalladas de asesores
     */
    private function getEstadisticasDetalladasAsesores($coordinadorId) {
        $sql = "SELECT 
                    u.id as asesor_id,
                    u.nombre_completo as asesor_nombre,
                    COUNT(c.id) as total_clientes,
                    COUNT(CASE WHEN c.estado_gestion = 'En Proceso' THEN 1 END) as clientes_llamados,
                    COUNT(CASE WHEN c.estado_gestion = 'Disponible' THEN 1 END) as clientes_pendientes,
                    CASE 
                        WHEN COUNT(c.id) > 0 THEN 
                            ROUND((COUNT(CASE WHEN c.estado_gestion IN ('En Proceso', 'Cita Programada', 'Cita Completada') THEN 1 END) * 100.0 / COUNT(c.id)), 1)
                        ELSE 0 
                    END as porcentaje_progreso
                FROM usuarios u
                LEFT JOIN clientes c ON u.id = c.asesor_id
                WHERE u.coordinador_id = ? AND u.rol = 'asesor' AND u.estado = 'Activo'
                GROUP BY u.id, u.nombre_completo
                ORDER BY u.nombre_completo";
        
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener asesores del coordinador
     */
    private function getAsesoresDelCoordinador($coordinadorId) {
        $sql = "SELECT id, nombre_completo FROM usuarios 
                WHERE coordinador_id = ? AND rol = 'asesor' AND estado = 'Activo' 
                ORDER BY nombre_completo";
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener clientes disponibles del coordinador
     */
    private function getClientesDisponibles($coordinadorId) {
        $sql = "SELECT * FROM clientes 
                WHERE coordinador_id = ? AND estado_gestion = 'Disponible' 
                ORDER BY fecha_creacion DESC";
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener clientes disponibles para asignación (limitado por cantidad)
     */
    private function getClientesDisponiblesParaAsignacion($coordinadorId, $cantidad) {
        $sql = "SELECT * FROM clientes 
                WHERE coordinador_id = ? AND estado_gestion = 'Disponible' 
                ORDER BY fecha_creacion DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$coordinadorId, $cantidad]);
    }
    
    /**
     * Asignar clientes a un asesor
     */
    public function asignarClientes() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        $asesorId = $_POST['asesor_id'] ?? null;
        $cantidad = $_POST['cantidad'] ?? null;
        
        if (!$asesorId || !$cantidad) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
            return;
        }
        
        $cantidad = (int)$cantidad;
        if ($cantidad <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Cantidad debe ser mayor a 0'], 400);
            return;
        }
        
        // Verificar que el asesor pertenezca al coordinador
        $asesor = $this->verificarAsesorDelCoordinador($asesorId, $_SESSION['user_id']);
        if (!$asesor) {
            $this->jsonResponse(['success' => false, 'error' => 'Asesor no válido'], 400);
            return;
        }
        
        // Verificar que hay suficientes clientes disponibles
        $clientesDisponibles = $this->getClientesDisponiblesParaAsignacion($_SESSION['user_id'], $cantidad);
        if (count($clientesDisponibles) < $cantidad) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay suficientes clientes disponibles'], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $asignacionesExitosas = 0;
            $errores = [];
            
            // Tomar solo la cantidad solicitada
            $clientesAAsignar = array_slice($clientesDisponibles, 0, $cantidad);
            
            foreach ($clientesAAsignar as $cliente) {
                // Asignar cliente al asesor
                $sql = "UPDATE clientes SET asesor_id = ?, estado_gestion = 'Asignado', fecha_asignacion = NOW() WHERE id = ?";
                $resultado = $this->db->query($sql, [$asesorId, $cliente['id']]);
                
                if ($resultado) {
                    $asignacionesExitosas++;
                    
                    // Registrar en historial
                    $this->registrarAsignacion($cliente['id'], $asesorId, $_SESSION['user_id']);
                } else {
                    $errores[] = "Error al asignar cliente ID {$cliente['id']}";
                }
            }
            
            if ($asignacionesExitosas > 0) {
                $this->db->commit();
                
                $mensaje = "Se asignaron exitosamente $asignacionesExitosas cliente(s) al asesor.";
                if (!empty($errores)) {
                    $mensaje .= " Errores: " . implode(', ', $errores);
                }
                
                $this->jsonResponse([
                    'success' => true, 
                    'message' => $mensaje,
                    'asignaciones' => $asignacionesExitosas
                ]);
            } else {
                $this->db->rollback();
                $this->jsonResponse(['success' => false, 'error' => 'No se pudo asignar ningún cliente'], 400);
            }
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en asignarClientes: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Verificar que el asesor pertenezca al coordinador
     */
    private function verificarAsesorDelCoordinador($asesorId, $coordinadorId) {
        $sql = "SELECT id FROM usuarios WHERE id = ? AND coordinador_id = ? AND rol = 'asesor' AND estado = 'Activo'";
        return $this->db->fetch($sql, [$asesorId, $coordinadorId]);
    }
    
    /**
     * Verificar si ya existe una base de datos para el coordinador
     */
    private function verificarBaseExistente($coordinadorId) {
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE coordinador_id = ?";
        $result = $this->db->fetch($sql, [$coordinadorId]);
        return $result['total'] > 0;
    }
    
    /**
     * Obtener información de la base existente
     */
    private function obtenerInfoBaseExistente($coordinadorId) {
        $sql = "SELECT 
                    COUNT(*) as total_clientes,
                    MIN(fecha_creacion) as fecha_primera_carga,
                    MAX(fecha_creacion) as fecha_ultima_carga
                FROM clientes 
                WHERE coordinador_id = ?";
        return $this->db->fetch($sql, [$coordinadorId]);
    }
    
    /**
     * Verificar que el cliente esté disponible y pertenezca al coordinador
     */
    private function verificarClienteDisponible($clienteId, $coordinadorId) {
        $sql = "SELECT id FROM clientes WHERE id = ? AND coordinador_id = ? AND estado_gestion = 'Disponible'";
        return $this->db->fetch($sql, [$clienteId, $coordinadorId]);
    }
    
    /**
     * Registrar asignación en historial
     */
    private function registrarAsignacion($clienteId, $asesorId, $coordinadorId) {
        $sql = "INSERT INTO historial_asignaciones (cliente_id, asesor_id, coordinador_id, fecha_asignacion, estado) 
                VALUES (?, ?, ?, NOW(), 'Asignado')";
        $this->db->query($sql, [$clienteId, $asesorId, $coordinadorId]);
    }
    
    /**
     * Obtener estadísticas específicas de un asesor
     */
    private function getEstadisticasAsesor($asesorId) {
        // Total de clientes asignados
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE asesor_id = ?";
        $totalClientes = $this->db->fetch($sql, [$asesorId])['total'];
        
        // Clientes gestionados (con historial)
        $sql = "SELECT COUNT(DISTINCT c.id) as total
                FROM clientes c
                INNER JOIN historial_gestion hg ON c.id = hg.cliente_id
                WHERE c.asesor_id = ?";
        $clientesGestionados = $this->db->fetch($sql, [$asesorId])['total'];
        
        // Clientes pendientes
        $clientesPendientes = $totalClientes - $clientesGestionados;
        
        return [
            'total_clientes' => $totalClientes,
            'clientes_gestionados' => $clientesGestionados,
            'clientes_pendientes' => $clientesPendientes
        ];
    }
    
    /**
     * Obtener clientes de un asesor con información detallada
     */
    private function getClientesDelAsesor($asesorId) {
        $sql = "SELECT 
                    c.id,
                    c.nombre_completo,
                    c.cedula,
                    c.telefono,
                    c.estado_gestion,
                    c.fecha_asignacion,
                    hg.tipo_gestion as ultima_tipificacion,
                    hg.fecha_gestion as ultima_gestion,
                    hg.observaciones as ultima_observacion
                FROM clientes c
                LEFT JOIN (
                    SELECT 
                        cliente_id,
                        tipo_gestion,
                        fecha_gestion,
                        observaciones,
                        ROW_NUMBER() OVER (PARTITION BY cliente_id ORDER BY fecha_gestion DESC) as rn
                    FROM historial_gestion
                ) hg ON c.id = hg.cliente_id AND hg.rn = 1
                WHERE c.asesor_id = ?
                ORDER BY c.fecha_asignacion DESC";
        
        return $this->db->fetchAll($sql, [$asesorId]);
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
}
?>
