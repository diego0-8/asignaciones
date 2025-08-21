<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - IPS CRM</title>
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
                <li class="active"><i class="fas fa-users"></i> Usuarios</li>
                <li onclick="window.location.href='index.php?action=admin_asignar_asesores'"><i class="fas fa-user-friends"></i> Asignar</li>
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
                <i class="fas fa-users"></i>
                <span>Gestión de Usuarios</span>
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
                <h3>GESTIÓN DE USUARIOS</h3>
                <p class="call-info">Sistema IPS CRM</p>
                <p class="call-info">Administración de Usuarios</p>
                <small>Acciones Principales</small>
                <div class="media-controls">
                    <button class="media-button" onclick="openModal('crear-usuario')">
                        <i class="fas fa-user-plus"></i> Crear Usuario
                    </button>
                    <button class="media-button" onclick="window.location.href='index.php?action=admin_dashboard'">
                        <i class="fas fa-arrow-left"></i> Volver Dashboard
                    </button>
                </div>
            </div>
            
            <div class="call-main-view">
                <div class="client-info">
                    <i class="fas fa-users"></i>
                    <div>
                        <span class="client-name">Usuarios del Sistema</span>
                        <span class="client-company">IPS CRM - Gestión de Usuarios</span>
                    </div>
                </div>

                <div class="main-tabs">
                    <span class="active">LISTA DE USUARIOS</span>
                    <span>ESTADÍSTICAS</span>
                    <span>PERMISOS</span>
                </div>
                
                <div class="content-sections">
                    <!-- PESTAÑA 1: LISTA DE USUARIOS -->
                    <div class="tab-content active" id="tab-lista-usuarios">
                        <div class="left-content">
                            <h4 style="margin-top: 0;">Usuarios del Sistema</h4>
                            
                            <!-- Filtros de búsqueda -->
                            <div class="form-section">
                                <div class="input-group">
                                    <label>Buscar por nombre</label>
                                    <input type="text" id="buscar-nombre" placeholder="Escribir nombre...">
                                </div>
                                <div class="input-group">
                                    <label>Filtrar por rol</label>
                                    <select id="filtro-rol">
                                        <option value="">Todos los roles</option>
                                        <option value="administrador">Administrador</option>
                                        <option value="coordinador">Coordinador</option>
                                        <option value="asesor">Asesor</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Estado</label>
                                    <select id="filtro-estado">
                                        <option value="">Todos</option>
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Tabla de Usuarios -->
                            <div class="table-container">
                                <table class="users-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre Completo</th>
                                        <th>Usuario</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Coordinador</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($usuarios)): ?>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td><?php echo $usuario['id']; ?></td>
                                                <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                                                <td>
                                                    <span class="role-badge role-<?php echo $usuario['rol']; ?>">
                                                        <?php echo ucfirst($usuario['rol']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $usuario['estado'] ?? 'activo'; ?>">
                                                        <?php echo ucfirst($usuario['estado'] ?? 'activo'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($usuario['rol'] === 'asesor' && isset($usuario['coordinador_nombre'])): ?>
                                                        <?php echo htmlspecialchars($usuario['coordinador_nombre']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="actions-cell">
                                                    <div class="action-buttons">
                                                        <button class="action-btn edit-btn" onclick="editarUsuario(<?php echo $usuario['id']; ?>)" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if (($usuario['estado'] ?? 'activo') === 'activo'): ?>
                                                            <button class="action-btn disable-btn" onclick="deshabilitarUsuario(<?php echo $usuario['id']; ?>)" title="Deshabilitar">
                                                                <i class="fas fa-user-slash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="action-btn enable-btn" onclick="habilitarUsuario(<?php echo $usuario['id']; ?>)" title="Habilitar">
                                                                <i class="fas fa-user-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button class="action-btn delete-btn" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="no-data">No hay usuarios registrados</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if (isset($total_paginas) && $total_paginas > 1): ?>
                            <div class="pagination">
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <a href="?action=admin_usuarios&pagina=<?php echo $i; ?>" 
                                       class="page-link <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>

                    <!-- PESTAÑA 2: ESTADÍSTICAS -->
                    <div class="tab-content" id="tab-estadisticas" style="display: none;">
                        <div class="left-content">
                            <h4 style="margin-top: 0;">Estadísticas del Sistema</h4>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <h5>Usuarios</h5>
                                    <div class="stat-value"><?php echo $estadisticas['total_usuarios'] ?? 0; ?></div>
                                    <div class="stat-subtitle">Total del sistema</div>
                                </div>
                                <div class="stat-card">
                                    <h5>Activos</h5>
                                    <div class="stat-value"><?php echo $estadisticas['usuarios_activos'] ?? 0; ?></div>
                                    <div class="stat-subtitle">Usuarios activos</div>
                                </div>
                                <div class="stat-card">
                                    <h5>Coordinadores</h5>
                                    <div class="stat-value"><?php echo $estadisticas['total_coordinadores'] ?? 0; ?></div>
                                    <div class="stat-subtitle">Total coordinadores</div>
                                </div>
                                <div class="stat-card">
                                    <h5>Asesores</h5>
                                    <div class="stat-value"><?php echo $estadisticas['total_asesores'] ?? 0; ?></div>
                                    <div class="stat-subtitle">Total asesores</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PESTAÑA 3: PERMISOS -->
                    <div class="tab-content" id="tab-permisos" style="display: none;">
                        <div class="left-content">
                            <h4 style="margin-top: 0;">Gestión de Permisos</h4>
                            <div class="permissions-section">
                                <p>Funcionalidad de permisos en desarrollo...</p>
                                <div class="no-data-message">
                                    <i class="fas fa-tools"></i>
                                    <p>Módulo de permisos próximamente disponible</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <aside class="right-sidebar">
                        <h4>Estadísticas de Usuarios</h4>
                        <div class="stats-summary">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $estadisticas['total_usuarios'] ?? 0; ?></span>
                                <span class="stat-label">Total Usuarios</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $estadisticas['usuarios_activos'] ?? 0; ?></span>
                                <span class="stat-label">Usuarios Activos</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $estadisticas['total_coordinadores'] ?? 0; ?></span>
                                <span class="stat-label">Coordinadores</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $estadisticas['total_asesores'] ?? 0; ?></span>
                                <span class="stat-label">Asesores</span>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 20px;">Acciones Rápidas</h4>
                        <div class="quick-actions-sidebar">
                            <button class="action-btn-sidebar" onclick="openModal('crear-usuario')">
                                <i class="fas fa-user-plus"></i> Nuevo Usuario
                            </button>
                            <button class="action-btn-sidebar" onclick="exportarUsuarios()">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <button class="action-btn-sidebar" onclick="importarUsuarios()">
                                <i class="fas fa-upload"></i> Importar
                            </button>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal Crear Usuario -->
    <div id="crear-usuario" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Crear Nuevo Usuario</h3>
                <button class="close-btn" onclick="closeModal('crear-usuario')">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Contenedor de alertas -->
                <div id="alert-container-crear" style="display: none;"></div>
                
                <form id="form-crear-usuario" onsubmit="crearUsuario(event)">
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
                        <label for="rol">Rol *</label>
                        <select id="rol" name="rol" required onchange="toggleCoordinadorField()">
                            <option value="">Seleccionar rol</option>
                            <option value="administrador">Administrador</option>
                            <option value="coordinador">Coordinador</option>
                            <option value="asesor">Asesor</option>
                        </select>
                    </div>
                    <div class="form-group" id="coordinador-field" style="display: none;">
                        <label for="coordinador_id">Coordinador</label>
                        <select id="coordinador_id" name="coordinador_id">
                            <option value="">Sin asignar</option>
                            <?php foreach ($coordinadores as $coord): ?>
                                <option value="<?php echo $coord['id']; ?>"><?php echo htmlspecialchars($coord['nombre_completo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('crear-usuario')">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btn-crear-usuario">
                            <i class="fas fa-user-plus"></i> Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div id="editar-usuario" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar Usuario</h3>
                <button class="close-btn" onclick="closeModal('editar-usuario')">&times;</button>
            </div>
            <div class="modal-body">
                <form action="index.php?action=update_usuario" method="POST" id="form-editar-usuario">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_nombre">Nombre Completo *</label>
                        <input type="text" id="edit_nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_cedula">Cédula *</label>
                        <input type="text" id="edit_cedula" name="cedula" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_usuario">Usuario *</label>
                        <input type="text" id="edit_usuario" name="usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_rol">Rol *</label>
                        <select id="edit_rol" name="rol" required onchange="toggleEditCoordinadorField()">
                            <option value="">Seleccionar rol</option>
                            <option value="administrador">Administrador</option>
                            <option value="coordinador">Coordinador</option>
                            <option value="asesor">Asesor</option>
                        </select>
                    </div>
                    <div class="form-group" id="edit_coordinador-field" style="display: none;">
                        <label for="edit_coordinador_id">Coordinador</label>
                        <select id="edit_coordinador_id" name="coordinador_id">
                            <option value="">Sin asignar</option>
                            <?php foreach ($coordinadores as $coord): ?>
                                <option value="<?php echo $coord['id']; ?>"><?php echo htmlspecialchars($coord['nombre_completo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('editar-usuario')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/admin-dashboard.js"></script>
    <script src="assets/js/admin-usuarios.js"></script>
</body>
</html>
