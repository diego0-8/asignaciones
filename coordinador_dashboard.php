<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Coordinador</title>
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-users-cog"></i>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php?action=coordinador_dashboard" class="nav-item active" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
                <a href="index.php?action=coordinador_cargar_archivo" class="nav-item" title="Cargar Archivo">
                    <i class="fas fa-upload"></i>
                </a>
                <a href="index.php?action=coordinador_tareas" class="nav-item" title="Tareas">
                    <i class="fas fa-tasks"></i>
                </a>
                <a href="index.php?action=coordinador_transferir_clientes" class="nav-item" title="Transferir Clientes">
                    <i class="fas fa-exchange-alt"></i>
                </a>
                <a href="index.php?action=coordinador_descargar_archivos" class="nav-item" title="Descargar CSV">
                    <i class="fas fa-download"></i>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="index.php?action=coordinador_cerrar_sesion" class="nav-item" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <h1>Dashboard del Coordinador</h1>
                </div>
                <div class="top-bar-right">
                    <span class="user-info">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Coordinador'); ?>
                    </span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($totalClientesCargados); ?></h3>
                        <p>Total Clientes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($totalClientesAsignados); ?></h3>
                        <p>Clientes Asignados</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo number_format($totalClientesDisponibles); ?></h3>
                        <p>Clientes Disponibles</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($estadisticas); ?></h3>
                        <p>Asesores Activos</p>
                    </div>
                </div>
            </div>

            <!-- Asesores Cards -->
            <div class="asesores-section">
                <h2>Estado de Asesores</h2>
                <div class="asesores-grid">
                    <?php if (empty($estadisticas)): ?>
                        <div class="no-data-message">
                            <i class="fas fa-info-circle"></i>
                            <p>No hay asesores asignados actualmente.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($estadisticas as $asesor): ?>
                            <div class="asesor-card">
                                <div class="asesor-header">
                                    <h3><?php echo htmlspecialchars($asesor['asesor_nombre']); ?></h3>
                                    <span class="asesor-status active">Activo</span>
                                </div>
                                
                                <div class="asesor-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Total Clientes:</span>
                                        <span class="stat-value"><?php echo $asesor['total_clientes']; ?></span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Llamados:</span>
                                        <span class="stat-value"><?php echo $asesor['clientes_llamados']; ?></span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Pendientes:</span>
                                        <span class="stat-value"><?php echo $asesor['clientes_pendientes']; ?></span>
                                    </div>
                                </div>
                                
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $asesor['porcentaje_progreso']; ?>%"></div>
                                </div>
                                <span class="progress-text"><?php echo $asesor['porcentaje_progreso']; ?>% Completado</span>
                                
                                <div class="asesor-actions">
                                    <button class="btn btn-primary btn-sm" onclick="verDetallesAsesor(<?php echo $asesor['asesor_id']; ?>)">
                                        <i class="fas fa-eye"></i> Detalles
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Acciones Rápidas</h2>
                <div class="actions-grid">
                    <a href="index.php?action=coordinador_cargar_archivo" class="action-card">
                        <i class="fas fa-upload"></i>
                        <h3>Cargar Nuevo Archivo</h3>
                        <p>Subir base de datos de clientes en formato CSV</p>
                    </a>
                    
                    <a href="index.php?action=coordinador_tareas" class="action-card">
                        <i class="fas fa-tasks"></i>
                        <h3>Asignar Clientes</h3>
                        <p>Distribuir clientes entre los asesores del equipo</p>
                    </a>
                    
                    <a href="index.php?action=coordinador_transferir_clientes" class="action-card">
                        <i class="fas fa-exchange-alt"></i>
                        <h3>Transferir Clientes</h3>
                        <p>Mover clientes entre asesores o liberarlos</p>
                    </a>
                    
                    <a href="index.php?action=coordinador_descargar_archivos" class="action-card">
                        <i class="fas fa-download"></i>
                        <h3>Exportar Datos</h3>
                        <p>Descargar reportes en formato CSV</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/coordinador-dashboard.js"></script>
</body>
</html>
