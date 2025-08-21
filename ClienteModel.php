<?php
/**
 * Modelo de Cliente
 * Maneja todas las operaciones relacionadas con clientes
 */

require_once 'Database.php';

class ClienteModel {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Obtener cliente por ID
     */
    public function getClienteById($id) {
        $sql = "SELECT * FROM clientes WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Obtener clientes por coordinador
     */
    public function getClientesPorCoordinador($coordinadorId) {
        $sql = "SELECT * FROM clientes WHERE coordinador_id = ? ORDER BY fecha_creacion DESC";
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener clientes por asesor
     */
    public function getClientesPorAsesor($asesorId) {
        $sql = "SELECT * FROM clientes WHERE asesor_id = ? ORDER BY fecha_creacion DESC";
        return $this->db->fetchAll($sql, [$asesorId]);
    }
    
    /**
     * Obtener clientes disponibles
     */
    public function getClientesDisponibles($coordinadorId = null) {
        if ($coordinadorId) {
            $sql = "SELECT * FROM clientes WHERE estado_gestion = 'Disponible' AND coordinador_id = ? ORDER BY fecha_creacion DESC";
            return $this->db->fetchAll($sql, [$coordinadorId]);
        } else {
            $sql = "SELECT * FROM clientes WHERE estado_gestion = 'Disponible' ORDER BY fecha_creacion DESC";
            return $this->db->fetchAll($sql);
        }
    }
    
    /**
     * Crear nuevo cliente
     */
    public function createCliente($data) {
        $clienteData = [
            'nombre_completo' => $data['nombre_completo'],
            'cedula' => $data['cedula'],
            'telefono' => $data['telefono'] ?? null,
            'celular2' => $data['celular2'] ?? null,
            'email' => $data['email'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'ciudad' => $data['ciudad'] ?? null,
            'estado_gestion' => 'Disponible',
            'estado' => 'Nuevo',
            'coordinador_id' => $data['coordinador_id'] ?? null,
            'asesor_id' => $data['asesor_id'] ?? null
        ];
        
        return $this->db->insert('clientes', $clienteData);
    }
    
    /**
     * Actualizar cliente
     */
    public function updateCliente($id, $data) {
        return $this->db->update('clientes', $data, 'id = ?', [$id]);
    }
    
    /**
     * Asignar cliente a asesor
     */
    public function asignarClienteAAsesor($clienteId, $asesorId, $coordinadorId) {
        $data = [
            'asesor_id' => $asesorId,
            'coordinador_id' => $coordinadorId,
            'estado_gestion' => 'Asignado',
            'estado' => 'Asignado'
        ];
        
        return $this->db->update('clientes', $data, 'id = ?', [$clienteId]);
    }
    
    /**
     * Cambiar estado de gestión del cliente
     */
    public function cambiarEstadoGestion($clienteId, $estado) {
        $data = ['estado_gestion' => $estado];
        return $this->db->update('clientes', $data, 'id = ?', [$clienteId]);
    }
    
    /**
     * Obtener estadísticas de clientes por coordinador
     */
    public function getEstadisticasPorCoordinador($coordinadorId) {
        $sql = "SELECT 
                    COUNT(*) as total_clientes,
                    COUNT(CASE WHEN estado_gestion = 'Disponible' THEN 1 END) as disponibles,
                    COUNT(CASE WHEN estado_gestion = 'Asignado' THEN 1 END) as asignados,
                    COUNT(CASE WHEN estado_gestion = 'En Proceso' THEN 1 END) as en_proceso,
                    COUNT(CASE WHEN estado_gestion = 'Cita Programada' THEN 1 END) as citas_programadas,
                    COUNT(CASE WHEN estado_gestion = 'Cita Completada' THEN 1 END) as citas_completadas
                FROM clientes 
                WHERE coordinador_id = ?";
        
        return $this->db->fetch($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener estadísticas de clientes por asesor
     */
    public function getEstadisticasPorAsesor($asesorId) {
        $sql = "SELECT 
                    COUNT(*) as total_clientes,
                    COUNT(CASE WHEN estado_gestion = 'Disponible' THEN 1 END) as disponibles,
                    COUNT(CASE WHEN estado_gestion = 'Asignado' THEN 1 END) as asignados,
                    COUNT(CASE WHEN estado_gestion = 'En Proceso' THEN 1 END) as en_proceso,
                    COUNT(CASE WHEN estado_gestion = 'Cita Programada' THEN 1 END) as citas_programadas,
                    COUNT(CASE WHEN estado_gestion = 'Cita Completada' THEN 1 END) as citas_completadas
                FROM clientes 
                WHERE asesor_id = ?";
        
        return $this->db->fetch($sql, [$asesorId]);
    }
    
    /**
     * Buscar clientes
     */
    public function buscarClientes($termino, $coordinadorId = null) {
        if ($coordinadorId) {
            $sql = "SELECT * FROM clientes 
                    WHERE coordinador_id = ? AND 
                    (nombre_completo LIKE ? OR cedula LIKE ? OR telefono LIKE ?)
                    ORDER BY fecha_creacion DESC";
            $params = [$coordinadorId, "%$termino%", "%$termino%", "%$termino%"];
        } else {
            $sql = "SELECT * FROM clientes 
                    WHERE nombre_completo LIKE ? OR cedula LIKE ? OR telefono LIKE ?
                    ORDER BY fecha_creacion DESC";
            $params = ["%$termino%", "%$termino%", "%$termino%"];
        }
        
        return $this->db->fetchAll($sql, $params);
    }
}
?>
