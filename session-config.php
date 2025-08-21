<?php
/**
 * Configuración de Seguridad de Sesión - IPS CRM
 * Configuraciones para el manejo seguro de sesiones
 */

// Configuración de sesión
ini_set('session.cookie_httponly', 1);           // Prevenir acceso desde JavaScript
ini_set('session.cookie_secure', 0);              // 0 para HTTP, 1 para HTTPS
ini_set('session.use_strict_mode', 1);            // Modo estricto de sesión
ini_set('session.cookie_samesite', 'Strict');     // Protección CSRF
ini_set('session.gc_maxlifetime', 1800);         // 30 minutos en segundos
ini_set('session.cookie_lifetime', 0);            // Sesión hasta cerrar navegador

// Configuración de timeout
define('SESSION_TIMEOUT', 1800);                  // 30 minutos en segundos
define('SESSION_WARNING_TIME', 300);              // 5 minutos antes de cerrar
define('SESSION_CHECK_INTERVAL', 60);             // Verificar cada minuto

// Configuración de actividad
define('ACTIVITY_EVENTS', [
    'mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart',
    'click', 'keydown', 'input', 'change', 'focus', 'submit'
]);

// Función para inicializar sesión segura
function initSecureSession() {
    // Configurar parámetros de sesión
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Cambiar a true en producción con HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    // Iniciar sesión
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Configurar timestamp de última actividad
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }
    
    // Verificar si la sesión ha expirado
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        // Sesión expirada
        session_destroy();
        return false;
    }
    
    // Actualizar timestamp de actividad
    $_SESSION['last_activity'] = time();
    
    return true;
}

// Función para verificar si la sesión es válida
function isSessionValid() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    if (!isset($_SESSION['last_activity'])) {
        return false;
    }
    
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        return false;
    }
    
    return true;
}

// Función para extender sesión
function extendSession() {
    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
        return true;
    }
    return false;
}

// Función para cerrar sesión
function closeSession() {
    // Limpiar todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
}

// Función para obtener tiempo restante de sesión
function getSessionTimeRemaining() {
    if (!isset($_SESSION['last_activity'])) {
        return 0;
    }
    
    $elapsed = time() - $_SESSION['last_activity'];
    $remaining = SESSION_TIMEOUT - $elapsed;
    
    return max(0, $remaining);
}

// Función para obtener tiempo transcurrido desde última actividad
function getSessionTimeElapsed() {
    if (!isset($_SESSION['last_activity'])) {
        return 0;
    }
    
    return time() - $_SESSION['last_activity'];
}

// Función para registrar actividad del usuario
function logUserActivity($action = 'general') {
    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
        
        // Aquí podrías registrar la actividad en la base de datos
        // logActivity($_SESSION['user_id'], $action, $_SERVER['REMOTE_ADDR']);
        
        return true;
    }
    return false;
}

// Función para verificar si el usuario está activo
function isUserActive() {
    if (!isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Considerar activo si ha tenido actividad en los últimos 5 minutos
    return (time() - $_SESSION['last_activity']) < 300;
}

// Función para obtener información de la sesión
function getSessionInfo() {
    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'user_role' => $_SESSION['user_role'] ?? null,
        'last_activity' => $_SESSION['last_activity'] ?? null,
        'time_remaining' => getSessionTimeRemaining(),
        'time_elapsed' => getSessionTimeElapsed(),
        'is_active' => isUserActive(),
        'is_valid' => isSessionValid()
    ];
}

// Función para limpiar sesiones expiradas (cron job)
function cleanupExpiredSessions() {
    // Esta función se puede ejecutar desde un cron job
    // para limpiar sesiones expiradas de la base de datos
    
    $expiredTime = time() - SESSION_TIMEOUT;
    
    // Ejemplo de limpieza (ajustar según tu estructura de BD)
    // $sql = "DELETE FROM user_sessions WHERE last_activity < ?";
    // $stmt = $pdo->prepare($sql);
    // $stmt->execute([$expiredTime]);
    
    return true;
}

// Configuración de headers de seguridad
function setSecurityHeaders() {
    // Prevenir clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevenir MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Prevenir XSS
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (básico)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; font-src 'self' cdnjs.cloudflare.com; img-src 'self' data:;");
}

// Inicializar configuración de seguridad
setSecurityHeaders();

// Función para manejar logout por inactividad
function handleInactivityLogout() {
    if (isset($_GET['reason']) && $_GET['reason'] === 'inactivity') {
        // Mostrar mensaje específico para logout por inactividad
        $_SESSION['logout_reason'] = 'inactivity';
    }
}

// Función para mostrar mensaje de logout
function getLogoutMessage() {
    if (isset($_SESSION['logout_reason'])) {
        $reason = $_SESSION['logout_reason'];
        unset($_SESSION['logout_reason']);
        
        switch ($reason) {
            case 'inactivity':
                return 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.';
            case 'security':
                return 'Tu sesión ha sido cerrada por razones de seguridad.';
            default:
                return 'Has cerrado sesión exitosamente.';
        }
    }
    
    return 'Has cerrado sesión exitosamente.';
}
?>
