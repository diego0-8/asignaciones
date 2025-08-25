<?php
/**
 * Router Principal de la Aplicación
 * Maneja todas las rutas y acciones del sistema
 */

// Iniciar sesión
session_start();

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración
require_once 'config.php';

// Incluir modelos y controladores
require_once 'models/Database.php';
require_once 'models/UsuarioModel.php';
require_once 'models/ClienteModel.php';
require_once 'controllers/AdminController.php';
require_once 'controllers/CoordinadorController.php';
require_once 'controllers/AsesorController.php';

// Obtener acción
$action = $_GET['action'] ?? 'login';

// Router principal
switch ($action) {
    // ===== ACCIONES PÚBLICAS =====
    case 'login':
        mostrarLogin();
        break;
        
    case 'logout':
        logout();
        break;
        
    // ===== DASHBOARD ADMINISTRADOR =====
    case 'dashboard':
        verificarSesion('administrador');
        $controller = new AdminController();
        $controller->dashboard();
        break;
        
    case 'admin_usuarios':
        verificarSesion('administrador');
        $controller = new AdminController();
        $controller->usuarios();
        break;
        
    case 'admin_asignaciones':
        verificarSesion('administrador');
        $controller = new AdminController();
        $controller->asignaciones();
        break;
        
    // ===== ACCIONES AJAX DEL ADMINISTRADOR =====
    case 'create_usuario':
        verificarSesion('administrador');
        $controller = new AdminController();
        $controller->createUsuario();
        break;
        
    case 'asignar_asesor':
        verificarSesion('administrador');
        $controller = new AdminController();
        $controller->asignarAsesor();
        break;
        
    case 'liberar_asesor':
        verificarSesion('administrador');
        $controller = new AdminController();
        $controller->liberarAsesor();
        break;
        
    case 'get_usuarios':
        verificarSesion('administrador');
        $controller = new AdminController();
        $controller->getUsuarios();
        break;
        
    case 'get_estadisticas':
        verificarSesion('administrador');
        $controller = new AdminController();
        $controller->getEstadisticas();
        break;
        
    // ===== DASHBOARD COORDINADOR =====
    case 'coordinador_dashboard':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->dashboard();
        break;
        
    case 'coordinador_cargar_archivo':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->cargarArchivo();
        break;
        
    case 'coordinador_tareas':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->tareas();
        break;
        
    case 'coordinador_asignar_clientes':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->asignarClientes();
        break;
        
    case 'coordinador_transferir_clientes':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->transferirClientes();
        break;
        
    case 'coordinador_descargar_archivos':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->descargarArchivos();
        break;
        
    case 'coordinador_procesar_archivo':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->procesarArchivo();
        break;
        
    case 'coordinador_obtener_detalles_asesor':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->obtenerDetallesAsesor();
        break;
        
    case 'coordinador_obtener_asesores_disponibles':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->obtenerAsesoresDisponibles();
        break;
        
    case 'coordinador_transferir_cliente':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->transferirCliente();
        break;
        
    case 'coordinador_liberar_cliente':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->liberarCliente();
        break;
        
    case 'coordinador_liberar_todos_clientes_asesor':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->liberarTodosClientesAsesor();
        break;
        
    case 'coordinador_get_clientes_asesor':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->getClientesAsesor();
        break;
        
    case 'coordinador_exportar_gestion':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->exportarGestionCSV();
        break;
        
    case 'coordinador_cerrar_sesion':
        verificarSesion('coordinador');
        $controller = new CoordinadorController();
        $controller->cerrarSesion();
        break;
        
    // ===== DASHBOARD ASESOR =====
    case 'asesor_dashboard':
        verificarSesion('asesor');
        $controller = new AsesorController();
        $controller->dashboard();
        break;
        
    case 'asesor_clientes':
        verificarSesion('asesor');
        $controller = new AsesorController();
        $controller->clientes();
        break;
        
    case 'asesor_gestionar_cliente':
        verificarSesion('asesor');
        $controller = new AsesorController();
        $controller->gestionarCliente();
        break;
        
    case 'asesor_guardar_gestion':
        verificarSesion('asesor');
        $controller = new AsesorController();
        $controller->guardarGestion();
        break;
        
    case 'asesor_obtener_detalles_gestion':
        verificarSesion('asesor');
        $controller = new AsesorController();
        $controller->obtenerDetallesGestion();
        break;
        
    case 'asesor_obtener_notificaciones':
        verificarSesion('asesor');
        $controller = new AsesorController();
        $controller->obtenerNotificaciones();
        break;
        
    case 'asesor_obtener_notificaciones_fecha':
        verificarSesion('asesor');
        $controller = new AsesorController();
        $controller->obtenerNotificacionesPorFecha();
        break;
        
    case 'asesor_citas':
        verificarSesion('asesor');
        $controller = new AsesorController();
        $controller->citas();
        break;
        
    case 'asesor_cerrar_sesion':
        $controller = new AsesorController();
        $controller->cerrarSesion();
        break;
        
    // ===== ACCIÓN POR DEFECTO =====
    default:
        mostrarLogin();
        break;
}

/**
 * Verificar sesión y rol
 */
function verificarSesion($rolRequerido) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header('Location: index.php?action=login');
        exit;
    }
    
    if ($_SESSION['user_role'] !== $rolRequerido) {
        header('Location: index.php?action=login');
        exit;
    }
}

/**
 * Mostrar formulario de login
 */
function mostrarLogin() {
    // Si ya está logueado, redirigir al dashboard correspondiente
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
        if ($_SESSION['user_role'] === 'administrador') {
            header('Location: index.php?action=dashboard');
            exit;
        } elseif ($_SESSION['user_role'] === 'coordinador') {
            header('Location: index.php?action=coordinador_dashboard');
            exit;
        } elseif ($_SESSION['user_role'] === 'asesor') {
            header('Location: index.php?action=asesor_dashboard');
            exit;
        }
    }
    
    // Procesar login si es POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = $_POST['usuario'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        
        if (!empty($usuario) && !empty($contrasena)) {
            $usuarioModel = new UsuarioModel();
            $user = $usuarioModel->authenticateUser($usuario, $contrasena);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre_completo'];
                $_SESSION['user_role'] = $user['rol'];
                $_SESSION['user_usuario'] = $user['usuario'];
                
                // Redirigir según el rol
                if ($user['rol'] === 'administrador') {
                    header('Location: index.php?action=dashboard');
                    exit;
                } elseif ($user['rol'] === 'coordinador') {
                    header('Location: index.php?action=coordinador_dashboard');
                    exit;
                } elseif ($user['rol'] === 'asesor') {
                    header('Location: index.php?action=asesor_dashboard');
                    exit;
                } else {
                    // Rol desconocido
                    header('Location: index.php?action=login');
                    exit;
                }
            } else {
                $error = 'Usuario o contraseña incorrectos.';
            }
        } else {
            $error = 'Por favor, completa todos los campos.';
        }
    }
    
    // Mostrar formulario de login
    include 'views/login_form.php';
}

/**
 * Cerrar sesión
 */
function logout() {
    session_destroy();
    header('Location: index.php?action=login');
    exit;
}
?>
