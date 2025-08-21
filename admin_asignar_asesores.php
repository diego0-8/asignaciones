<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Asesores - IPS CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-logo">IPS CRM</div>
        <nav class="sidebar-nav">
            <ul>
                <li onclick="window.location.href='index.php?action=admin_dashboard'"><i class="fas fa-th-large"></i> Dashboard</li>
                <li onclick="window.location.href='index.php?action=admin_usuarios'"><i class="fas fa-users"></i> Usuarios</li>
                <li class="active"><i class="fas fa-user-friends"></i> Asignar</li>
            </ul>
        </nav>
        
        <!-- Botón de Cerrar Sesión en la parte inferior -->
        <div class="sidebar-footer">
            <a href="index.php?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <div class="main-container">
        <!-- Encabezado Superior -->
        <header class="top-header">
            <div class="header-left">
                <i class="fas fa-user-friends"></i>
                <span>Asignar Asesores</span>
                <span><?php echo $_SESSION['user_name'] ?? 'Usuario'; ?></span>
            </div>
            <div class="header-right">
                <span><i class="fas fa-circle-info"></i></span>
                <span><i class="fas fa-bell"></i></span>
                <img src="https://placehold.co/30x30/FFFFFF/000000?text=<?php echo substr($_SESSION['user_name'] ?? 'A', 0, 1); ?>" style="border-radius:50%;">
                <span><?php echo $_SESSION['user_name'] ?? 'Admin'; ?> <i class="fas fa-caret-down"></i></span>
            </div>
        </header>

        <!-- Sección Principal -->
        <section class="current-call-section">
            <div class="call-details">
                <h3>ASIGNAR ASESORES</h3>
                <p class="call-info">Sistema IPS CRM</p>
                <p class="call-info">Gestión de Personal</p>
                <small>Acciones Principales</small>
                <div class="media-controls">
                    <button class="media-button" onclick="openModal('asignar-personal')">
                        <i class="fas fa-user-friends"></i> Asignar Personal
                    </button>
                    <button class="media-button" onclick="window.location.href='index.php?action=admin_dashboard'">
                        <i class="fas fa-arrow-left"></i> Volver Dashboard
                    </button>
                </div>
            </div>
            
            <div class="call-main-view">
                <div class="client-info">
                    <i class="fas fa-user-friends"></i>
                    <div>
                        <span class="client-name">Gestión de Personal</span>
                        <span class="client-company">IPS CRM - Asignación de Asesores</span>
                    </div>
                </div>

                <div class="main-tabs">
                    <span class="active">COORDINADORES</span>
                    <span>ASESORES DISPONIBLES</span>
                    <span>ASESORES ASIGNADOS</span>
                    <span>ASIGNACIONES</span>
                </div>
                
                <div class="content-sections">
                    <!-- PESTAÑA 1: COORDINADORES -->
                    <div class="tab-content active" id="tab-coordinadores">
                        <div class="left-content">
                            <h4 style="margin-top: 0;">Coordinadores del Sistema</h4>
                            <div class="coordinadores-grid">
                                <?php if (!empty($coordinadores)): ?>
                                    <?php foreach ($coordinadores as $coordinador): ?>
                                        <div class="coordinador-card">
                                            <div class="coordinador-header">
                                                <i class="fas fa-user-tie"></i>
                                                <h5><?php echo htmlspecialchars($coordinador['nombre_completo']); ?></h5>
                                            </div>
                                            <div class="coordinador-info">
                                                <p><strong>ID:</strong> <?php echo $coordinador['id']; ?></p>
                                                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($coordinador['usuario']); ?></p>
                                                <p><strong>Asesores Asignados:</strong> 
                                                    <span class="asesores-count"><?php echo $coordinador['asesores_asignados'] ?? 0; ?></span>
                                                </p>
                                                <p><strong>Estado:</strong> 
                                                    <span class="status-badge status-<?php echo $coordinador['estado'] ?? 'activo'; ?>">
                                                        <?php echo ucfirst($coordinador['estado'] ?? 'activo'); ?>
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="coordinador-actions">
                                                <button class="action-btn edit-btn" onclick="editarCoordinador(<?php echo $coordinador['id']; ?>)" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn assign-btn" onclick="asignarAsesores(<?php echo $coordinador['id']; ?>)" title="Asignar Asesores">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-data-message">
                                        <i class="fas fa-info-circle"></i>
                                        <p>No hay coordinadores registrados</p>
                                        <button class="btn btn-primary" onclick="openModal('crear-coordinador')">Crear Coordinador</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- PESTAÑA 2: ASESORES DISPONIBLES -->
                    <div class="tab-content" id="tab-asesores-disponibles" style="display: none;">
                        <div class="left-content">
                            <h4 style="margin-top: 0;">Asesores Disponibles para Asignación</h4>
                            <div class="asesores-sin-coordinador">
                                <?php if (!empty($asesores_sin_coordinador)): ?>
                                    <div class="table-container">
                                        <table class="asesores-table">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nombre Completo</th>
                                                    <th>Usuario</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($asesores_sin_coordinador as $asesor): ?>
                                                    <tr>
                                                        <td><?php echo $asesor['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($asesor['nombre_completo']); ?></td>
                                                        <td><?php echo htmlspecialchars($asesor['usuario']); ?></td>
                                                        <td>
                                                            <span class="status-badge status-<?php echo $asesor['estado'] ?? 'activo'; ?>">
                                                                <?php echo ucfirst($asesor['estado'] ?? 'activo'); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="action-btn assign-btn" onclick="asignarAsesor(<?php echo $asesor['id']; ?>)" title="Asignar">
                                                                <i class="fas fa-user-plus"></i> Asignar
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="success-message">
                                        <i class="fas fa-check-circle"></i>
                                        <p>Todos los asesores están asignados a coordinadores</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- PESTAÑA 3: ASESORES ASIGNADOS -->
                    <div class="tab-content" id="tab-asesores-asignados" style="display: none;">
                        <div class="left-content">
                            <h4 style="margin-top: 0;">Asesores Asignados a Coordinadores</h4>
                            <div class="asesores-asignados">
                                <?php if (!empty($coordinadores)): ?>
                                    <?php foreach ($coordinadores as $coordinador): ?>
                                        <?php if (($coordinador['asesores_asignados'] ?? 0) > 0): ?>
                                            <div class="coordinador-section">
                                                <h5><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($coordinador['nombre_completo']); ?></h5>
                                                <div class="asesores-list">
                                                    <!-- Aquí se mostrarían los asesores asignados a este coordinador -->
                                                    <p>Asesores asignados: <?php echo $coordinador['asesores_asignados'] ?? 0; ?></p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-data-message">
                                        <i class="fas fa-info-circle"></i>
                                        <p>No hay asesores asignados</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- PESTAÑA 4: ASIGNACIONES -->
                    <div class="tab-content" id="tab-asignaciones" style="display: none;">
                        <div class="left-content">
                            <h4 style="margin-top: 0;">Historial de Asignaciones Recientes</h4>
                            <div class="historial-asignaciones">
                                <?php if (!empty($historial_asignaciones)): ?>
                                    <div class="historial-list">
                                        <?php foreach ($historial_asignaciones as $asignacion): ?>
                                            <div class="historial-item">
                                                <div class="historial-icon">
                                                    <i class="fas fa-user-plus"></i>
                                                </div>
                                                <div class="historial-content">
                                                    <p><strong><?php echo htmlspecialchars($asignacion['asesor_nombre']); ?></strong> 
                                                       fue asignado a <strong><?php echo htmlspecialchars($asignacion['coordinador_nombre']); ?></strong></p>
                                                    <small><?php echo $asignacion['fecha_asignacion']; ?></small>
                                                </div>
                                                <div class="historial-actions">
                                                    <button class="action-btn edit-btn" onclick="reasignarAsesor(<?php echo $asignacion['asesor_id']; ?>)" title="Reasignar">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-data-message">
                                        <i class="fas fa-info-circle"></i>
                                        <p>No hay historial de asignaciones</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENIDO ORIGINAL (se mantiene para compatibilidad) -->
                    <div class="left-content">
                        <!-- Historial de Asignaciones -->
                        <h4>Historial de Asignaciones Recientes</h4>
                        <div class="historial-asignaciones">
                            <?php if (!empty($historial_asignaciones)): ?>
                                <div class="historial-list">
                                    <?php foreach ($historial_asignaciones as $asignacion): ?>
                                        <div class="historial-item">
                                            <div class="historial-icon">
                                                <i class="fas fa-user-plus"></i>
                                            </div>
                                            <div class="historial-content">
                                                <p><strong><?php echo htmlspecialchars($asignacion['asesor_nombre']); ?></strong> 
                                                   fue asignado a <strong><?php echo htmlspecialchars($asignacion['coordinador_nombre']); ?></strong></p>
                                                <small><?php echo $asignacion['fecha_asignacion']; ?></small>
                                            </div>
                                            <div class="historial-actions">
                                                <button class="action-btn edit-btn" onclick="reasignarAsesor(<?php echo $asignacion['asesor_id']; ?>)" title="Reasignar">
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-data-message">
                                    <i class="fas fa-info-circle"></i>
                                    <p>No hay historial de asignaciones</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <aside class="right-sidebar">
                        <h4>Estadísticas de Asignación</h4>
                        <div class="stats-summary">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $estadisticas['total_coordinadores'] ?? 0; ?></span>
                                <span class="stat-label">Coordinadores</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $estadisticas['total_asesores'] ?? 0; ?></span>
                                <span class="stat-label">Total Asesores</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $estadisticas['asesores_asignados'] ?? 0; ?></span>
                                <span class="stat-label">Asesores Asignados</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo count($asesores_sin_coordinador ?? []); ?></span>
                                <span class="stat-label">Sin Asignar</span>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 20px;">Acciones Rápidas</h4>
                        <div class="quick-actions-sidebar">
                            <button class="action-btn-sidebar" onclick="openModal('asignar-personal')">
                                <i class="fas fa-user-friends"></i> Asignar Personal
                            </button>
                            <button class="action-btn-sidebar" onclick="openModal('crear-coordinador')">
                                <i class="fas fa-user-plus"></i> Nuevo Coordinador
                            </button>
                            <button class="action-btn-sidebar" onclick="openModal('crear-asesor')">
                                <i class="fas fa-user-graduate"></i> Nuevo Asesor
                            </button>
                            <button class="action-btn-sidebar" onclick="exportarAsignaciones()">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                        </div>

                        <!-- Progreso de Asignación -->
                        <h4 style="margin-top: 20px;">Progreso de Asignación</h4>
                        <div class="progress-container">
                            <?php 
                            $total_asesores = $estadisticas['total_asesores'] ?? 0;
                            $asesores_asignados = $estadisticas['asesores_asignados'] ?? 0;
                            $porcentaje = ($total_asesores > 0) ? round(($asesores_asignados / $total_asesores) * 100, 1) : 0;
                            ?>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                            </div>
                            <div class="progress-text">
                                <span><?php echo $asesores_asignados; ?> de <?php echo $total_asesores; ?> asesores asignados</span>
                                <span class="percentage"><?php echo $porcentaje; ?>%</span>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal Asignar Personal -->
    <div id="asignar-personal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Asignar Personal</h3>
                <button class="close-btn" onclick="closeModal('asignar-personal')">&times;</button>
            </div>
            <div class="modal-body">
                <form action="index.php?action=asignar_personal" method="POST" id="form-asignar-personal">
                    <div class="form-group">
                        <label for="asesor_id">Asesor *</label>
                        <select id="asesor_id" name="asesor_id" required>
                            <option value="">Seleccionar asesor</option>
                            <?php foreach ($asesores_sin_coordinador ?? [] as $asesor): ?>
                                <option value="<?php echo $asesor['id']; ?>"><?php echo htmlspecialchars($asesor['nombre_completo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="coordinador_id">Coordinador *</label>
                        <select id="coordinador_id" name="coordinador_id" required>
                            <option value="">Seleccionar coordinador</option>
                            <?php foreach ($coordinadores as $coord): ?>
                                <option value="<?php echo $coord['id']; ?>"><?php echo htmlspecialchars($coord['nombre_completo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('asignar-personal')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Asignar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Crear Coordinador -->
    <div id="crear-coordinador" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Crear Nuevo Coordinador</h3>
                <button class="close-btn" onclick="closeModal('crear-coordinador')">&times;</button>
            </div>
            <div class="modal-body">
                <form action="index.php?action=create_coordinador" method="POST" id="form-crear-coordinador">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo *</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="cedula">Cédula *</label>
                        <input type="text" id="cedula" name="cedula" required>
                    </div>
                    <div class="form-group">
                        <label for="usuario">Usuario *</label>
                        <input type="text" id="usuario" name="usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="contrasena">Contraseña *</label>
                        <input type="password" id="contrasena" name="contrasena" required>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('crear-coordinador')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Coordinador</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Crear Asesor -->
    <div id="crear-asesor" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Crear Nuevo Asesor</h3>
                <button class="close-btn" onclick="closeModal('crear-asesor')">&times;</button>
            </div>
            <div class="modal-body">
                <form action="index.php?action=create_asesor" method="POST" id="form-crear-asesor">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo *</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="cedula">Cédula *</label>
                        <input type="text" id="cedula" name="cedula" required>
                    </div>
                    <div class="form-group">
                        <label for="usuario">Usuario *</label>
                        <input type="text" id="usuario" name="usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="contrasena">Contraseña *</label>
                        <input type="password" id="contrasena" name="contrasena" required>
                    </div>
                    <div class="form-group">
                        <label for="coordinador_id">Coordinador</label>
                        <select id="coordinador_id" name="coordinador_id">
                            <option value="">Sin asignar</option>
                            <?php foreach ($coordinadores as $coord): ?>
                                <option value="<?php echo $coord['id']; ?>"><?php echo htmlspecialchars($coord['nombre_completo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('crear-asesor')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Asesor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/admin-dashboard.js"></script>
    <script src="assets/js/admin-asignar-asesores.js"></script>
</body>
</html>
