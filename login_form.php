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

        <div class="login-container">
        <div class="login-header">
            <i class="fas fa-hospital-user" style="font-size: 3rem; color: #3b82f6; margin-bottom: 1rem;"></i>
            <h2>Acceso para Asignación de Citas IPS</h2>
            <p style="color: #94a3b8; margin-bottom: 2rem; font-size: 0.95rem;">Sistema de Gestión de Citas Médicas</p>
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
            <p style="color: #64748b; font-size: 0.8rem; margin-top: 2rem;">
                <i class="fas fa-shield-alt"></i>
                Sistema seguro y confidencial
            </p>
        </div>
    </div>

</body>
</html>