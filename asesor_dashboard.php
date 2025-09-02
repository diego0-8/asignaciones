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
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Clientes</h3>
                        <p class="stat-number"><?php echo number_format($estadisticas['total_clientes'] ?? 0); ?></p>
                        <p class="stat-label">Asignados</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Gestionados</h3>
                        <p class="stat-number"><?php echo number_format($estadisticas['clientes_gestionados'] ?? 0); ?></p>
                        <p class="stat-label">En Proceso</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Pendientes</h3>
                        <p class="stat-number"><?php echo number_format($estadisticas['clientes_pendientes'] ?? 0); ?></p>
                        <p class="stat-label">Por gestionar</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Citas Hoy</h3>
                        <p class="stat-number"><?php echo number_format($estadisticas['citas_agendadas_hoy'] ?? 0); ?></p>
                        <p class="stat-label">Agendadas</p>
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
                <h2>Actividad Reciente (Últimas 3 Gestiones)</h2>
                <div class="activity-list">
                    <?php if (!empty($actividadReciente)): ?>
                        <?php foreach ($actividadReciente as $actividad): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <?php 
                                    $icono = 'fas fa-phone';
                                    $titulo = 'Gestión realizada';
                                    
                                    switch($actividad['tipo_gestion']) {
                                        case 'asignacion_cita':
                                            $icono = 'fas fa-calendar-plus';
                                            $titulo = 'Cita programada';
                                            break;
                                        case 'volver_llamar':
                                            $icono = 'fas fa-clock';
                                            $titulo = 'Volver a llamar';
                                            break;
                                        case 'no_interesa':
                                            $icono = 'fas fa-times-circle';
                                            $titulo = 'No interesa';
                                            break;
                                        case 'fuera_ciudad':
                                            $icono = 'fas fa-map-marker-alt';
                                            $titulo = 'Fuera de ciudad';
                                            break;
                                        case 'no_contactado':
                                            $icono = 'fas fa-user-slash';
                                            $titulo = 'No contactado';
                                            break;
                                        default:
                                            $icono = 'fas fa-phone';
                                            $titulo = 'Gestión realizada';
                                    }
                                    ?>
                                    <i class="<?php echo $icono; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <h4><?php echo htmlspecialchars($titulo); ?></h4>
                                    <p>
                                        <strong>Cliente:</strong> <?php echo htmlspecialchars($actividad['cliente_nombre']); ?> 
                                        - <strong>Cédula:</strong> <?php echo htmlspecialchars($actividad['cliente_cedula']); ?>
                                    </p>
                                    <?php if (!empty($actividad['observaciones'])): ?>
                                        <p class="activity-observations">
                                            <strong>Observaciones:</strong> <?php echo htmlspecialchars(substr($actividad['observaciones'], 0, 100)); ?>
                                            <?php if (strlen($actividad['observaciones']) > 100): ?>...<?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($actividad['tipo_gestion'] === 'volver_llamar' && !empty($actividad['fecha_proxima_accion'])): ?>
                                        <p class="activity-next-action">
                                            <strong>Próxima acción:</strong> <?php echo date('d/m/Y', strtotime($actividad['fecha_proxima_accion'])); ?>
                                        </p>
                                    <?php endif; ?>
                                    <span class="activity-time">
                                        <?php 
                                        $fecha = new DateTime($actividad['fecha_gestion']);
                                        $ahora = new DateTime();
                                        $diferencia = $ahora->diff($fecha);
                                        
                                        if ($diferencia->days > 0) {
                                            echo 'Hace ' . $diferencia->days . ' día' . ($diferencia->days > 1 ? 's' : '');
                                        } elseif ($diferencia->h > 0) {
                                            echo 'Hace ' . $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '');
                                        } elseif ($diferencia->i > 0) {
                                            echo 'Hace ' . $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '');
                                        } else {
                                            echo 'Hace un momento';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-activity">
                            <i class="fas fa-info-circle"></i>
                            <p>No hay actividad reciente para mostrar.</p>
                            <small>Las gestiones aparecerán aquí después de que realices llamadas o gestiones.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Script unificado -->
    
</body>
</html>
