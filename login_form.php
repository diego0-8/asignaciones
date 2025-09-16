<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso para Asignación de Citas IPS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <div class="login-wrapper">
        <!-- ===== COLUMNA IZQUIERDA - IMAGEN Y DESCRIPCIÓN ===== -->
        <div class="login-info">
            <div class="login-info-content">
                <h1>IPS</h1>
                <h2>Gestión Inteligente de Citas Médicas</h2>
                <p>Accede a nuestro sistema integral de gestión de citas médicas. Diseñado para optimizar la experiencia tanto de pacientes como del personal médico.</p>
                
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-calendar-check"></i>
                        <span>Gestión de Citas Inteligente</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-users"></i>
                        <span>Asignación Automática de Asesores</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-chart-line"></i>
                        <span>Reportes y Estadísticas en Tiempo Real</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <span>Seguridad y Confidencialidad Garantizada</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== COLUMNA DERECHA - FORMULARIO DE LOGIN ===== -->
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-hospital-user"></i>
                <h2>Iniciar Sesión</h2>
                <p style="color: #94a3b8; margin-bottom: 2rem; font-size: 0.95rem;">Accede a tu cuenta de asesor</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="index.php?action=login" class="login-form">
                <div class="input-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Nombre de usuario
                    </label>
                    <input type="text" id="username" name="usuario" placeholder="Ingresa tu nombre de usuario" required value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>">
                </div>
                <div class="input-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Contraseña
                    </label>
                    <input type="password" id="password" name="contrasena" placeholder="Ingresa tu contraseña" required>
                </div>
                <button type="submit" class="login-button">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar sesión
                </button>
            </form>
            
            <div class="forgot-password">
                <a href="#">
                    <i class="fas fa-question-circle"></i>
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
            
            <div class="login-footer">
                <p>
                    <i class="fas fa-shield-alt"></i>
                    Sistema seguro y confidencial
                </p>
            </div>
        </div>
    </div>

</body>
</html>