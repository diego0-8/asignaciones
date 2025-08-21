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
        
        include 'views/coordinador_cargar_archivo.php';
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
        
        include 'views/coordinador_tareas.php';
    }
    
    /**
     * Transferir clientes
     */
    public function transferirClientes() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
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
        $clienteIds = $_POST['cliente_ids'] ?? null;
        
        if (!$asesorId || !$clienteIds) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
            return;
        }
        
        // Verificar que el asesor pertenezca al coordinador
        $asesor = $this->verificarAsesorDelCoordinador($asesorId, $_SESSION['user_id']);
        if (!$asesor) {
            $this->jsonResponse(['success' => false, 'error' => 'Asesor no válido'], 400);
            return;
        }
        
        // Convertir string de IDs a array
        $clienteIdsArray = explode(',', $clienteIds);
        
        try {
            $this->db->beginTransaction();
            
            $asignacionesExitosas = 0;
            $errores = [];
            
            foreach ($clienteIdsArray as $clienteId) {
                $clienteId = trim($clienteId);
                if (empty($clienteId)) continue;
                
                // Verificar que el cliente esté disponible y pertenezca al coordinador
                $cliente = $this->verificarClienteDisponible($clienteId, $_SESSION['user_id']);
                if (!$cliente) {
                    $errores[] = "Cliente ID $clienteId no disponible";
                    continue;
                }
                
                // Asignar cliente al asesor
                $sql = "UPDATE clientes SET asesor_id = ?, estado_gestion = 'Asignado', fecha_asignacion = NOW() WHERE id = ?";
                $resultado = $this->db->query($sql, [$asesorId, $clienteId]);
                
                if ($resultado) {
                    $asignacionesExitosas++;
                    
                    // Registrar en historial
                    $this->registrarAsignacion($clienteId, $asesorId, $_SESSION['user_id']);
                } else {
                    $errores[] = "Error al asignar cliente ID $clienteId";
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
