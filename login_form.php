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
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a; /* Fondo azul oscuro, igual que la barra lateral del dashboard */
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            overflow: hidden; /* Evita el scroll */
        }

        .login-container {
            background-color: #1e293b; /* Un tono más claro que el fondo para el contenedor */
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 2.5rem;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .login-container h2 {
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #ffffff;
        }

        .input-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #cbd5e1;
            margin-bottom: 0.5rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #475569;
            background-color: #334155;
            color: #ffffff;
            font-size: 1rem;
            transition: border-color 0.2s;
            box-sizing: border-box; /* Asegura que el padding no afecte el ancho */
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #2563eb;
        }
        
        .login-button {
            width: 100%;
            padding: 0.75rem;
            background-color: #2563eb; /* Color azul del dashboard */
            color: #ffffff;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .login-button:hover {
            background-color: #1d4ed8;
        }
        
        .forgot-password {
            margin-top: 1rem;
            text-align: center;
        }

        .forgot-password a {
            font-size: 0.875rem;
            color: #6b7280;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .forgot-password a:hover {
            color: #9ca3af;
        }

        .alert {
            border-radius: 0.5rem;
            border: none;
            padding: 0.75rem;
            margin-bottom: 1rem;
            text-align: left;
        }

        .alert-danger {
            background-color: #dc2626;
            color: #ffffff;
        }

        .alert-success {
            background-color: #059669;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Acceso para Asignación de Citas IPS</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="index.php?action=login">
            <div class="input-group">
                <label for="username">Nombre de usuario</label>
                <input type="text" id="username" name="usuario" placeholder="Ingresa tu nombre de usuario" required value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="contrasena" placeholder="Ingresa tu contraseña" required>
            </div>
            <button type="submit" class="login-button">Iniciar sesión</button>
        </form>
        <div class="forgot-password">
            <a href="#">¿Olvidaste tu contraseña?</a>
        </div>
    </div>

</body>
</html>