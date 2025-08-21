<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Asesor - Sistema de Citas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/asesor-dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar izquierdo -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-user-tie"></i>
                <span class="user-role">ASESOR</span>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php?action=asesor_dashboard" class="nav-item active" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
                <a href="index.php?action=asesor_clientes" class="nav-item" title="Clientes">
                    <i class="fas fa-users"></i>
                </a>
                <a href="index.php?action=asesor_gestionar_cliente" class="nav-item" title="Gestionar Clientes">
                    <i class="fas fa-phone"></i>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="index.php?action=asesor_cerrar_sesion" class="nav-item logout" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="main-content">
            <!-- Barra superior -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <h1>Dashboard Asesor</h1>
                    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Asesor'); ?></p>
                </div>
                <div class="top-bar-right">
                    <div class="date-time">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('d/m/Y'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Estadísticas del día -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Clientes Gestionados</h3>
                        <p class="stat-number"><?php echo $estadisticas['clientes_gestionados'] ?? 0; ?></p>
                        <p class="stat-label">Total</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Clientes Pendientes</h3>
                        <p class="stat-number"><?php echo $estadisticas['clientes_pendientes'] ?? 0; ?></p>
                        <p class="stat-label">Por gestionar</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Citas Registradas</h3>
                        <p class="stat-number"><?php echo $estadisticas['citas_registradas'] ?? 0; ?></p>
                        <p class="stat-label">Hoy</p>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="actions-section">
                <h2>Acciones Rápidas</h2>
                <div class="actions-grid">
                    <a href="index.php?action=asesor_clientes" class="action-card">
                        <i class="fas fa-users"></i>
                        <h3>Ver Clientes</h3>
                        <p>Gestionar clientes asignados</p>
                    </a>
                    
                    <a href="index.php?action=asesor_gestionar_cliente" class="action-card">
                        <i class="fas fa-phone"></i>
                        <h3>Nueva Llamada</h3>
                        <p>Iniciar gestión de cliente</p>
                    </a>
                    
                    <a href="#" class="action-card" onclick="mostrarProximoCliente()">
                        <i class="fas fa-arrow-right"></i>
                        <h3>Siguiente Cliente</h3>
                        <p>Obtener próximo cliente</p>
                    </a>
                </div>
            </div>

            <!-- Resumen de actividad reciente -->
            <div class="recent-activity">
                <h2>Actividad Reciente</h2>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="activity-content">
                            <h4>Llamada realizada</h4>
                            <p>Cliente: Juan Pérez - Cédula: 12345678</p>
                            <span class="activity-time">Hace 2 horas</span>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="activity-content">
                            <h4>Cita programada</h4>
                            <p>Cliente: María García - Fecha: 15/12/2024</p>
                            <span class="activity-time">Hace 4 horas</span>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="activity-content">
                            <h4>Volver a llamar</h4>
                            <p>Cliente: Carlos López - Fecha: 16/12/2024</p>
                            <span class="activity-time">Hace 6 horas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/asesor-dashboard.js"></script>
</body>
</html>
