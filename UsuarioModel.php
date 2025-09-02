<?php
/**
 * Modelo de Usuario
 * Maneja todas las operaciones relacionadas con usuarios
 */

require_once __DIR__ . '/Database.php';

class UsuarioModel {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Autenticar usuario
     */
    public function authenticateUser($usuario, $contrasena) {
        $sql = "SELECT * FROM usuarios WHERE usuario = ? AND estado = 'Activo'";
        $user = $this->db->fetch($sql, [$usuario]);
        
        if ($user) {
            // Primero intentar con password_verify (para contraseñas hasheadas)
            if (password_verify($contrasena, $user['contrasena'])) {
                return $user;
            }
            
            // Si no funciona, verificar si es contraseña en texto plano
            if ($contrasena === $user['contrasena']) {
                // Actualizar la contraseña a hash para futuras autenticaciones
                $this->actualizarContrasenaHash($user['id'], $contrasena);
                return $user;
            }
        }
        
        return false;
    }
    
    /**
     * Obtener usuario por ID
     */
    public function getUsuarioById($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Obtener usuario por nombre de usuario
     */
    public function getUsuarioByUsername($username) {
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        return $this->db->fetch($sql, [$username]);
    }
    
    /**
     * Obtener usuario por cédula
     */
    public function getUsuarioByCedula($cedula) {
        $sql = "SELECT * FROM usuarios WHERE cedula = ?";
        return $this->db->fetch($sql, [$cedula]);
    }
    
    /**
     * Crear nuevo usuario
     */
    public function createUsuario($data) {
        $userData = [
            'nombre_completo' => $data['nombre'],
            'cedula' => $data['cedula'],
            'usuario' => $data['usuario'],
            'contrasena' => $data['contrasena'],
            'rol' => $data['rol'],
            'estado' => 'Activo',
            'coordinador_id' => $data['coordinador_id'] ?: null
        ];
        
        return $this->db->insert('usuarios', $userData);
    }
    
    /**
     * Actualizar usuario
     */
    public function updateUsuario($id, $data) {
        $userData = [
            'nombre_completo' => $data['nombre'],
            'cedula' => $data['cedula'],
            'usuario' => $data['usuario'],
            'rol' => $data['rol'],
            'coordinador_id' => $data['coordinador_id'] ?: null
        ];
        
        return $this->db->update('usuarios', $userData, 'id = ?', [$id]);
    }
    
    /**
     * Eliminar usuario
     */
    public function deleteUsuario($id) {
        return $this->db->delete('usuarios', 'id = ?', [$id]);
    }
    
    /**
     * Cambiar estado de usuario
     */
    public function toggleEstadoUsuario($id) {
        $sql = "UPDATE usuarios SET estado = CASE WHEN estado = 'Activo' THEN 'Inactivo' ELSE 'Activo' END WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Verificar cédula única
     */
    public function verificarCedulaUnica($cedula, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE cedula = ?";
        $params = [$cedula];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] == 0;
    }
    
    /**
     * Verificar usuario único
     */
    public function verificarUsuarioUnico($usuario, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE usuario = ?";
        $params = [$usuario];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] == 0;
    }
    
    /**
     * Obtener todos los usuarios con paginación
     */
    public function getAllUsuarios($page = 1, $limit = 10, $search = '', $rol_filter = '', $estado_filter = '') {
        $offset = ($page - 1) * $limit;
        
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(nombre_completo LIKE ? OR usuario LIKE ? OR cedula LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if (!empty($rol_filter)) {
            $where[] = "rol = ?";
            $params[] = $rol_filter;
        }
        
        if (!empty($estado_filter)) {
            $where[] = "estado = ?";
            $params[] = $estado_filter;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT * FROM usuarios {$whereClause} ORDER BY fecha_creacion DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtener total de usuarios
     */
    public function getTotalUsuarios($search = '', $rol_filter = '', $estado_filter = '') {
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(nombre_completo LIKE ? OR usuario LIKE ? OR cedula LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if (!empty($rol_filter)) {
            $where[] = "rol = ?";
            $params[] = $rol_filter;
        }
        
        if (!empty($estado_filter)) {
            $where[] = "estado = ?";
            $params[] = $estado_filter;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) as total FROM usuarios {$whereClause}";
        $result = $this->db->fetch($sql, $params);
        
        return $result['total'];
    }
    
    /**
     * Obtener coordinadores disponibles
     */
    public function getCoordinadoresDisponibles() {
        $sql = "SELECT id, nombre_completo FROM usuarios WHERE rol = 'coordinador' AND estado = 'Activo' ORDER BY nombre_completo";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Obtener asesores disponibles
     */
    public function getAsesoresDisponibles() {
        $sql = "SELECT id, nombre_completo FROM usuarios WHERE rol = 'asesor' AND estado = 'Activo' ORDER BY nombre_completo";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Obtener asesores sin coordinador
     */
    public function getAsesoresSinCoordinador() {
        $sql = "SELECT id, nombre_completo FROM usuarios WHERE rol = 'asesor' AND estado = 'Activo' AND coordinador_id IS NULL ORDER BY nombre_completo";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Obtener asesores con coordinador
     */
    public function getAsesoresConCoordinador() {
        $sql = "SELECT u.id, u.nombre_completo, c.nombre_completo as coordinador_nombre 
                FROM usuarios u 
                LEFT JOIN usuarios c ON u.coordinador_id = c.id 
                WHERE u.rol = 'asesor' AND u.estado = 'Activo' 
                ORDER BY c.nombre_completo, u.nombre_completo";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Obtener usuarios por coordinador
     */
    public function getUsuariosPorCoordinador($coordinadorId) {
        $sql = "SELECT * FROM usuarios WHERE coordinador_id = ? AND estado = 'Activo' ORDER BY nombre_completo";
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Asignar asesor a coordinador
     */
    public function asignarAsesorACoordinador($asesorId, $coordinadorId) {
        $data = ['coordinador_id' => $coordinadorId];
        return $this->db->update('usuarios', $data, 'id = ?', [$asesorId]);
    }
    
    /**
     * Liberar asesor de coordinador
     */
    public function liberarAsesorDeCoordinador($asesorId) {
        $data = ['coordinador_id' => null];
        return $this->db->update('usuarios', $data, 'id = ?', [$asesorId]);
    }
    
    /**
     * Obtener estadísticas de usuarios
     */
    public function getEstadisticasUsuarios() {
        $sql = "SELECT 
                    COUNT(*) as total_usuarios,
                    SUM(CASE WHEN estado = 'Activo' THEN 1 ELSE 0 END) as usuarios_activos,
                    SUM(CASE WHEN rol = 'administrador' THEN 1 ELSE 0 END) as total_administradores,
                    SUM(CASE WHEN rol = 'coordinador' THEN 1 ELSE 0 END) as total_coordinadores,
                    SUM(CASE WHEN rol = 'asesor' THEN 1 ELSE 0 END) as total_asesores,
                    SUM(CASE WHEN rol = 'asesor' AND coordinador_id IS NOT NULL THEN 1 ELSE 0 END) as asesores_asignados,
                    SUM(CASE WHEN rol = 'asesor' AND coordinador_id IS NULL THEN 1 ELSE 0 END) as asesores_sin_coordinador
                FROM usuarios";
        
        return $this->db->fetch($sql);
    }
    
    /**
     * Obtener historial de asignaciones
     */
    public function getHistorialAsignaciones($limit = 10) {
        $sql = "SELECT ha.*, 
                       a.nombre_completo as asesor_nombre,
                       c.nombre_completo as coordinador_nombre,
                       ua.nombre_completo as admin_nombre
                FROM historial_asignaciones ha
                LEFT JOIN usuarios a ON ha.asesor_id = a.id
                LEFT JOIN usuarios c ON ha.coordinador_id = c.id
                LEFT JOIN usuarios ua ON ha.usuario_admin_id = ua.id
                ORDER BY ha.fecha_asignacion DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Registrar actividad
     */
    public function logActividad($usuarioId, $accion, $descripcion) {
        $data = [
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'fecha_actividad' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('logs_actividades', $data);
    }
    
    /**
     * Actualizar contraseña a hash
     */
    private function actualizarContrasenaHash($usuarioId, $contrasena) {
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET contrasena = ? WHERE id = ?";
        return $this->db->query($sql, [$contrasena_hash, $usuarioId]);
    }
}
?>
