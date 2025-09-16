<?php
/**
 * Controlador del Administrador
 * Maneja todas las acciones del panel de administración
 */

require_once __DIR__ . '/../models/UsuarioModel.php';

class AdminController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }
    
    /**
     * Dashboard principal del administrador
     */
    public function dashboard() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener estadísticas
        $estadisticas = $this->usuarioModel->getEstadisticasUsuarios();
        $coordinadores = $this->usuarioModel->getCoordinadoresDisponibles();
        $historial_asignaciones = $this->usuarioModel->getHistorialAsignaciones(5);
        
        // Incluir vista
        include __DIR__ . '/../views/admin_dashboard.php';
    }
    
    /**
     * Vista de usuarios
     */
    public function usuarios() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener parámetros de paginación y filtros
        $pagina_actual = $_GET['pagina'] ?? 1;
        $limite = 10;
        $busqueda = $_GET['buscar'] ?? '';
        $filtro_rol = $_GET['rol'] ?? '';
        $filtro_estado = $_GET['estado'] ?? '';
        
        // Obtener usuarios con paginación
        $usuarios = $this->usuarioModel->getAllUsuarios($pagina_actual, $limite, $busqueda, $filtro_rol, $filtro_estado);
        $total_usuarios = $this->usuarioModel->getTotalUsuarios($busqueda, $filtro_rol, $filtro_estado);
        $total_paginas = ceil($total_usuarios / $limite);
        
        // Obtener estadísticas
        $estadisticas = $this->usuarioModel->getEstadisticasUsuarios();
        
        // Obtener coordinadores para los selects
        $coordinadores = $this->usuarioModel->getCoordinadoresDisponibles();
        
        // Incluir vista
        include __DIR__ . '/../views/admin_usuarios.php';
    }
    
    /**
     * Vista de asignaciones
     */
    public function asignaciones() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Incluir vista
        include __DIR__ . '/../views/admin_asignar_asesores.php';
    }
    
    /**
     * Crear usuario (AJAX)
     */
    public function createUsuario() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        // Verificar método
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        // Obtener datos
        $data = [
            'nombre' => $_POST['nombre'] ?? '',
            'cedula' => $_POST['cedula'] ?? '',
            'usuario' => $_POST['usuario'] ?? '',
            'contrasena' => $_POST['contrasena'] ?? '',
            'rol' => $_POST['rol'] ?? '',
            'coordinador_id' => $_POST['coordinador_id'] ?: null
        ];
        
        // Validaciones
        $errors = $this->validarDatosUsuario($data);
        
        if (empty($errors)) {
            // Hash de contraseña
            $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
            
            // Crear usuario
            if ($this->usuarioModel->createUsuario($data)) {
                // Log de actividad
                $this->usuarioModel->logActividad(
                    $_SESSION['user_id'], 
                    'crear_usuario', 
                    'Usuario creado: ' . $data['nombre']
                );
                
                $this->jsonResponse(['success' => true, 'message' => 'Usuario creado exitosamente']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al crear el usuario']);
            }
        } else {
            $this->jsonResponse(['success' => false, 'message' => implode(', ', $errors)]);
        }
    }
    
    /**
     * Asignar asesor a coordinador (AJAX)
     */
    public function asignarAsesor() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        // Verificar método
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $asesorId = $_POST['asesor_id'] ?? null;
        $coordinadorId = $_POST['coordinador_id'] ?? null;
        
        if (!$asesorId || !$coordinadorId) {
            $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos']);
        }
        
        if ($asesorId === $coordinadorId) {
            $this->jsonResponse(['success' => false, 'message' => 'El asesor y el coordinador no pueden ser la misma persona']);
        }
        
        try {
            // Verificar que el asesor existe y es un asesor
            $asesor = $this->usuarioModel->getUsuarioById($asesorId);
            if (!$asesor || $asesor['rol'] !== 'asesor') {
                $this->jsonResponse(['success' => false, 'message' => 'El usuario seleccionado no es un asesor válido']);
            }
            
            // Verificar que el coordinador existe y es un coordinador
            $coordinador = $this->usuarioModel->getUsuarioById($coordinadorId);
            if (!$coordinador || $coordinador['rol'] !== 'coordinador') {
                $this->jsonResponse(['success' => false, 'message' => 'El usuario seleccionado no es un coordinador válido']);
            }
            
            // Verificar que el asesor no esté ya asignado
            if ($asesor['coordinador_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'El asesor ya está asignado a otro coordinador']);
            }
            
            // Asignar asesor al coordinador
            if ($this->usuarioModel->asignarAsesorACoordinador($asesorId, $coordinadorId)) {
                // Registrar en historial
                $this->registrarHistorialAsignacion($asesorId, $coordinadorId, 'asignacion');
                
                // Log de actividad
                $this->usuarioModel->logActividad(
                    $_SESSION['user_id'],
                    'Asignación de Asesor',
                    "Asesor '{$asesor['nombre_completo']}' asignado al coordinador '{$coordinador['nombre_completo']}'"
                );
                
                $this->jsonResponse(['success' => true, 'message' => 'Asesor asignado exitosamente']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al asignar asesor']);
            }
            
        } catch (Exception $e) {
            error_log("Error en asignarAsesor: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Liberar asesor de coordinador (AJAX)
     */
    public function liberarAsesor() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        // Verificar método
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $asesorId = $_POST['asesor_id'] ?? null;
        
        if (!$asesorId) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de asesor requerido']);
        }
        
        try {
            // Verificar que el asesor existe y es un asesor
            $asesor = $this->usuarioModel->getUsuarioById($asesorId);
            if (!$asesor || $asesor['rol'] !== 'asesor') {
                $this->jsonResponse(['success' => false, 'message' => 'El usuario seleccionado no es un asesor válido']);
            }
            
            // Verificar que el asesor esté asignado
            if (!$asesor['coordinador_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'El asesor no está asignado a ningún coordinador']);
            }
            
            $coordinadorId = $asesor['coordinador_id'];
            
            // Liberar asesor
            if ($this->usuarioModel->liberarAsesorDeCoordinador($asesorId)) {
                // Registrar en historial
                $this->registrarHistorialAsignacion($asesorId, $coordinadorId, 'liberacion');
                
                // Log de actividad
                $this->usuarioModel->logActividad(
                    $_SESSION['user_id'],
                    'Liberación de Asesor',
                    "Asesor '{$asesor['nombre_completo']}' liberado del coordinador"
                );
                
                $this->jsonResponse(['success' => true, 'message' => 'Asesor liberado exitosamente']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al liberar asesor']);
            }
            
        } catch (Exception $e) {
            error_log("Error en liberarAsesor: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Obtener lista de usuarios (AJAX)
     */
    public function getUsuarios() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 10;
        $search = $_GET['search'] ?? '';
        $rol_filter = $_GET['rol_filter'] ?? '';
        $estado_filter = $_GET['estado_filter'] ?? '';
        
        $usuarios = $this->usuarioModel->getAllUsuarios($page, $limit, $search, $rol_filter, $estado_filter);
        $total = $this->usuarioModel->getTotalUsuarios($search, $rol_filter, $estado_filter);
        
        $this->jsonResponse([
            'success' => true,
            'data' => $usuarios,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ]);
    }
    
    /**
     * Obtener estadísticas (AJAX)
     */
    public function getEstadisticas() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        $estadisticas = $this->usuarioModel->getEstadisticasUsuarios();
        $coordinadores = $this->usuarioModel->getCoordinadoresDisponibles();
        $asesores_sin_coordinador = $this->usuarioModel->getAsesoresSinCoordinador();
        $asesores_con_coordinador = $this->usuarioModel->getAsesoresConCoordinador();
        
        $this->jsonResponse([
            'success' => true,
            'data' => [
                'estadisticas' => $estadisticas,
                'coordinadores' => $coordinadores,
                'asesores_sin_coordinador' => $asesores_sin_coordinador,
                'asesores_con_coordinador' => $asesores_con_coordinador
            ]
        ]);
    }
    
    /**
     * Actualizar usuario (AJAX)
     */
    public function updateUsuario() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        // Verificar método
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de usuario requerido']);
        }
        
        // Obtener datos
        $data = [
            'nombre' => $_POST['nombre'] ?? '',
            'cedula' => $_POST['cedula'] ?? '',
            'usuario' => $_POST['usuario'] ?? '',
            'rol' => $_POST['rol'] ?? '',
            'coordinador_id' => $_POST['coordinador_id'] ?: null
        ];
        
        // Validaciones
        $errors = $this->validarDatosUsuarioUpdate($data, $id);
        
        if (empty($errors)) {
            // Actualizar usuario
            if ($this->usuarioModel->updateUsuario($id, $data)) {
                // Log de actividad
                $this->usuarioModel->logActividad(
                    $_SESSION['user_id'], 
                    'actualizar_usuario', 
                    'Usuario actualizado: ' . $data['nombre']
                );
                
                $this->jsonResponse(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al actualizar el usuario']);
            }
        } else {
            $this->jsonResponse(['success' => false, 'message' => implode(', ', $errors)]);
        }
    }
    
    /**
     * Eliminar usuario (AJAX)
     */
    public function deleteUsuario() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        // Verificar método
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de usuario requerido']);
        }
        
        // Verificar que no se esté eliminando a sí mismo
        if ($id == $_SESSION['user_id']) {
            $this->jsonResponse(['success' => false, 'message' => 'No puedes eliminar tu propio usuario']);
        }
        
        // Obtener información del usuario antes de eliminar
        $usuario = $this->usuarioModel->getUsuarioById($id);
        if (!$usuario) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        
        // Eliminar usuario
        if ($this->usuarioModel->deleteUsuario($id)) {
            // Log de actividad
            $this->usuarioModel->logActividad(
                $_SESSION['user_id'], 
                'eliminar_usuario', 
                'Usuario eliminado: ' . $usuario['nombre_completo']
            );
            
            $this->jsonResponse(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar el usuario']);
        }
    }
    
    /**
     * Cambiar estado de usuario (AJAX)
     */
    public function toggleEstadoUsuario() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        // Verificar método
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de usuario requerido']);
        }
        
        // Verificar que no se esté deshabilitando a sí mismo
        if ($id == $_SESSION['user_id']) {
            $this->jsonResponse(['success' => false, 'message' => 'No puedes deshabilitar tu propio usuario']);
        }
        
        // Obtener información del usuario antes de cambiar estado
        $usuario = $this->usuarioModel->getUsuarioById($id);
        if (!$usuario) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        
        // Cambiar estado
        if ($this->usuarioModel->toggleEstadoUsuario($id)) {
            $nuevo_estado = ($usuario['estado'] === 'Activo') ? 'Inactivo' : 'Activo';
            
            // Log de actividad
            $this->usuarioModel->logActividad(
                $_SESSION['user_id'], 
                'cambiar_estado_usuario', 
                "Usuario {$nuevo_estado}: " . $usuario['nombre_completo']
            );
            
            $this->jsonResponse([
                'success' => true, 
                'message' => "Usuario {$nuevo_estado} exitosamente",
                'nuevo_estado' => $nuevo_estado
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al cambiar el estado del usuario']);
        }
    }
    
    /**
     * Obtener usuario por ID (AJAX)
     */
    public function getUsuario() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
            $this->jsonResponse(['success' => false, 'message' => 'No autorizado'], 403);
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de usuario requerido']);
        }
        
        $usuario = $this->usuarioModel->getUsuarioById($id);
        if (!$usuario) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        
        $this->jsonResponse(['success' => true, 'data' => $usuario]);
    }
    
    /**
     * Validar datos de usuario
     */
    private function validarDatosUsuario($data) {
        $errors = [];

        if (empty($data['nombre'])) $errors[] = 'El nombre es obligatorio.';
        if (empty($data['cedula'])) $errors[] = 'La cédula es obligatoria.';
        if (empty($data['usuario'])) $errors[] = 'El usuario es obligatorio.';
        if (empty($data['contrasena'])) $errors[] = 'La contraseña es obligatoria.';
        if (empty($data['rol'])) $errors[] = 'El rol es obligatorio.';

        if (empty($errors)) {
            // Verificar cédula única
            if (!$this->usuarioModel->verificarCedulaUnica($data['cedula'])) {
                $errors[] = 'La cédula ya está registrada.';
            }

            // Verificar usuario único
            if (!$this->usuarioModel->verificarUsuarioUnico($data['usuario'])) {
                $errors[] = 'El nombre de usuario ya está en uso.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validar datos de usuario para actualización
     */
    private function validarDatosUsuarioUpdate($data, $excludeId) {
        $errors = [];

        if (empty($data['nombre'])) $errors[] = 'El nombre es obligatorio.';
        if (empty($data['cedula'])) $errors[] = 'La cédula es obligatoria.';
        if (empty($data['usuario'])) $errors[] = 'El usuario es obligatorio.';
        if (empty($data['rol'])) $errors[] = 'El rol es obligatorio.';

        if (empty($errors)) {
            // Verificar cédula única (excluyendo el usuario actual)
            if (!$this->usuarioModel->verificarCedulaUnica($data['cedula'], $excludeId)) {
                $errors[] = 'La cédula ya está registrada.';
            }

            // Verificar usuario único (excluyendo el usuario actual)
            if (!$this->usuarioModel->verificarUsuarioUnico($data['usuario'], $excludeId)) {
                $errors[] = 'El nombre de usuario ya está en uso.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Registrar historial de asignación
     */
    private function registrarHistorialAsignacion($asesorId, $coordinadorId, $tipo) {
        // Por ahora, solo registramos en el log de actividades
        // El historial de asignaciones se puede implementar después
        $this->usuarioModel->logActividad(
            $_SESSION['user_id'],
            'historial_asignacion',
            "Asesor ID: {$asesorId}, Coordinador ID: {$coordinadorId}, Acción: {$tipo}"
        );
    }
    
    /**
     * Respuesta JSON
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
