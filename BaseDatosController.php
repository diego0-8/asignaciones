<?php
/**
 * Controlador para gestión de bases de datos de clientes
 * Maneja la creación, edición y asignación de bases de datos
 */

require_once __DIR__ . '/../models/Database.php';

class BaseDatosController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtener todas las bases de datos del coordinador
     */
    public function getBasesDatos($coordinadorId) {
        $sql = "SELECT bd.*, 
                       u.nombre_completo as asesor_nombre
                FROM base_datos_clientes bd
                LEFT JOIN usuarios u ON bd.asesor_id = u.id
                WHERE bd.coordinador_id = ?
                ORDER BY bd.fecha_creacion DESC";
        
        $basesDatos = $this->db->fetchAll($sql, [$coordinadorId]);
        
        // Obtener el conteo de clientes para cada base de datos
        foreach ($basesDatos as &$base) {
            $sqlCount = "SELECT COUNT(*) as total FROM clientes WHERE base_datos_id = ?";
            $result = $this->db->fetch($sqlCount, [$base['id']]);
            $base['total_clientes_actual'] = $result['total'];
        }
        
        return $basesDatos;
    }
    
    /**
     * Crear nueva base de datos
     */
    public function crearBaseDatos($data) {
        $baseData = [
            'nombre_base' => $data['nombre_base'],
            'descripcion' => $data['descripcion'] ?? '',
            'coordinador_id' => $data['coordinador_id'],
            'asesor_id' => $data['asesor_id'] ?? null,
            'estado' => 'Activa'
        ];
        
        return $this->db->insert('base_datos_clientes', $baseData);
    }
    
    /**
     * Actualizar base de datos
     */
    public function actualizarBaseDatos($id, $data) {
        $updateData = [
            'nombre_base' => $data['nombre_base'],
            'descripcion' => $data['descripcion'] ?? '',
            'asesor_id' => $data['asesor_id'] ?? null
        ];
        
        return $this->db->update('base_datos_clientes', $updateData, 'id = ?', [$id]);
    }
    
    /**
     * Obtener base de datos por ID
     */
    public function getBaseDatosById($id, $coordinadorId) {
        $sql = "SELECT bd.*, 
                       u.nombre_completo as asesor_nombre
                FROM base_datos_clientes bd
                LEFT JOIN usuarios u ON bd.asesor_id = u.id
                WHERE bd.id = ? AND bd.coordinador_id = ?";
        
        return $this->db->fetch($sql, [$id, $coordinadorId]);
    }
    
    /**
     * Asignar asesor a base de datos
     */
    public function asignarAsesor($baseDatosId, $asesorId, $coordinadorId) {
        // Verificar que el asesor pertenezca al coordinador
        $sql = "SELECT id FROM usuarios WHERE id = ? AND coordinador_id = ? AND rol = 'asesor' AND estado = 'Activo'";
        $asesor = $this->db->fetch($sql, [$asesorId, $coordinadorId]);
        
        if (!$asesor) {
            throw new Exception('El asesor no pertenece a este coordinador');
        }
        
        // Actualizar base de datos
        $data = ['asesor_id' => $asesorId];
        return $this->db->update('base_datos_clientes', $data, 'id = ?', [$baseDatosId]);
    }
    
    /**
     * Liberar asesor de base de datos
     */
    public function liberarAsesor($baseDatosId, $coordinadorId) {
        $data = ['asesor_id' => null];
        return $this->db->update('base_datos_clientes', $data, 'id = ?', [$baseDatosId]);
    }
    
    /**
     * Obtener estadísticas de base de datos
     */
    public function getEstadisticasBaseDatos($baseDatosId) {
        $sql = "SELECT 
                    COUNT(*) as total_clientes,
                    COUNT(CASE WHEN estado_gestion = 'Disponible' THEN 1 END) as disponibles,
                    COUNT(CASE WHEN estado_gestion = 'Asignado' THEN 1 END) as asignados,
                    COUNT(CASE WHEN estado_gestion = 'En Proceso' THEN 1 END) as en_proceso,
                    COUNT(CASE WHEN estado_gestion = 'Cita Programada' THEN 1 END) as citas_programadas,
                    COUNT(CASE WHEN estado_gestion = 'Cita Completada' THEN 1 END) as citas_completadas
                FROM clientes 
                WHERE base_datos_id = ?";
        
        return $this->db->fetch($sql, [$baseDatosId]);
    }
    
    /**
     * Verificar si existe base de datos con el mismo nombre
     */
    public function verificarNombreUnico($nombre, $coordinadorId, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM base_datos_clientes WHERE nombre_base = ? AND coordinador_id = ?";
        $params = [$nombre, $coordinadorId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] == 0;
    }
    
    /**
     * Cambiar estado de base de datos
     */
    public function cambiarEstado($baseDatosId, $estado, $coordinadorId) {
        $data = ['estado' => $estado];
        return $this->db->update('base_datos_clientes', $data, 'id = ? AND coordinador_id = ?', [$baseDatosId, $coordinadorId]);
    }
    
    /**
     * Obtener asesores disponibles del coordinador
     */
    public function getAsesoresDisponibles($coordinadorId) {
        $sql = "SELECT id, nombre_completo 
                FROM usuarios 
                WHERE coordinador_id = ? AND rol = 'asesor' AND estado = 'Activo' 
                ORDER BY nombre_completo";
        
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener bases de datos sin asesor asignado
     */
    public function getBasesSinAsesor($coordinadorId) {
        $sql = "SELECT * FROM base_datos_clientes 
                WHERE coordinador_id = ? AND asesor_id IS NULL AND estado = 'Activa'
                ORDER BY fecha_creacion DESC";
        
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener bases de datos de un asesor específico
     */
    public function getBasesPorAsesor($asesorId, $coordinadorId) {
        $sql = "SELECT bd.*, 
                       COUNT(c.id) as total_clientes_actual
                FROM base_datos_clientes bd
                LEFT JOIN clientes c ON bd.id = c.base_datos_id
                WHERE bd.asesor_id = ? AND bd.coordinador_id = ? AND bd.estado = 'Activa'
                GROUP BY bd.id
                ORDER BY bd.fecha_creacion DESC";
        
        return $this->db->fetchAll($sql, [$asesorId, $coordinadorId]);
    }
}
?>
