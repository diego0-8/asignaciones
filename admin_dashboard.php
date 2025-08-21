<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - IPS CRM</title>
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
                <li class="active"><i class="fas fa-th-large"></i> Dashboard</li>
                <li onclick="window.location.href='index.php?action=admin_usuarios'"><i class="fas fa-users"></i> Usuarios</li>
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
                <i class="fas fa-user-shield"></i>
                <span>Administrador Activo</span>
                <span><?php echo $_SESSION['user_name'] ?? 'Usuario'; ?></span>
            </div>
            <div class="header-right">
                <span><i class="fas fa-circle-info"></i></span>
                <span><i class="fas fa-bell"></i></span>
                <img src="https://placehold.co/30x30/FFFFFF/000000?text=<?php echo substr($_SESSION['user_name'] ?? 'A', 0, 1); ?>" style="border-radius:50%;">
                <span><?php echo $_SESSION['user_name'] ?? 'Admin'; ?> <i class="fas fa-caret-down"></i></span>
            </div>
        </header>

        <!-- Sección Principal del Dashboard -->
        <section class="current-call-section">
            <div class="call-details">
                <h3>ESTADÍSTICAS GENERALES</h3>
                <p class="call-info">Sistema IPS CRM</p>
                <p class="call-info">Administración Central</p>
                <small>Acciones Principales</small>
                <div class="media-controls">
                    <button class="media-button" onclick="openModal('crear-usuario')">
                        <i class="fas fa-user-plus"></i> Crear Usuario
                    </button>
                    <button class="media-button" onclick="openModal('asignar-personal')">
                        <i class="fas fa-user-friends"></i> Asignar Personal
                    </button>
                    <button class="media-button" onclick="openModal('cargar-clientes')">
                        <i class="fas fa-upload"></i> Cargar Clientes
                    </button>
                    <button class="media-button" onclick="openModal('generar-reporte')">
                        <i class="fas fa-file-alt"></i> Generar Reporte
                    </button>
                </div>
                
            </div>
            
            <div class="call-main-view">
                <div class="client-info">
                    <i class="fas fa-chart-line"></i>
                    <div>
                        <span class="client-name">Panel de Control</span>
                        <span class="client-company">IPS CRM - Administración</span>
                    </div>
                </div>

                <div class="main-tabs">
                    <span class="active" onclick="cambiarTab('estadisticas')">ESTADÍSTICAS</span>
                    <span onclick="cambiarTab('usuarios')">USUARIOS</span>
                    <span onclick="cambiarTab('clientes')">CLIENTES</span>
                    <span onclick="cambiarTab('actividad')">ACTIVIDAD</span>
                </div>
                
                <div class="content-sections">
                    <!-- PESTAÑA 1: ESTADÍSTICAS -->
                    <div class="tab-content active" id="tab-estadisticas">
                        <div class="left-content">
                            <!-- Widgets de Estadísticas -->
                            <h4 style="margin-top: 0;">Resumen de Sistema</h4>
                            <div class="form-section">
                                <div class="input-group">
                                    <label>Total Usuarios</label>
                                    <input type="text" value="<?php echo $estadisticas['total_usuarios'] ?? 0; ?>" readonly>
                                </div>
                                <div class="input-group">
                                    <label>Usuarios Activos</label>
                                    <input type="text" value="<?php echo $estadisticas['usuarios_activos'] ?? 0; ?>" readonly>
                                </div>
                                <div class="input-group">
                                    <label>Total Coordinadores</label>
                                    <input type="text" value="<?php echo $estadisticas['total_coordinadores'] ?? 0; ?>" readonly>
                                </div>
                                <div class="input-group">
                                    <label>Coordinadores Disponibles</label>
                                    <input type="text" value="<?php echo $estadisticas['coordinadores_disponibles'] ?? 0; ?>" readonly>
                                </div>
                            </div>
                            
                            <!-- Segunda fila de estadísticas -->
                            <div class="form-section">
                                <div class="input-group">
                                    <label>Total Asesores</label>
                                    <input type="text" value="<?php echo $estadisticas['total_asesores'] ?? 0; ?>" readonly>
                                </div>
                                <div class="input-group">
                                    <label>Asesores Asignados</label>
                                    <input type="text" value="<?php echo $estadisticas['asesores_asignados'] ?? 0; ?>" readonly>
                                </div>
                                <div class="input-group">
                                    <label>Total Clientes</label>
                                    <input type="text" value="<?php echo $estadisticas['total_clientes'] ?? 0; ?>" readonly>
                                </div>
                                <div class="input-group">
                                    <label>Clientes Nuevos</label>
                                    <input type="text" value="<?php echo $estadisticas['clientes_nuevos'] ?? 0; ?>" readonly>
                                </div>
                            </div>

                            <!-- Porcentajes de Rendimiento -->
                            <h4>Rendimiento del Sistema</h4>
                            <div class="form-section">
                                <div class="input-group">
                                    <label>Usuarios Activos (%)</label>
                                    <input type="text" value="<?php 
                                        $total = $estadisticas['total_usuarios'] ?? 0;
                                        $activos = $estadisticas['usuarios_activos'] ?? 0;
                                        echo ($total > 0) ? round(($activos / $total) * 100, 1) : 0;
                                    ?>%" readonly>
                                </div>
                                <div class="input-group">
                                    <label>Coordinadores Disponibles (%)</label>
                                    <input type="text" value="<?php 
                                        $total = $estadisticas['total_coordinadores'] ?? 0;
                                        $disponibles = $estadisticas['coordinadores_disponibles'] ?? 0;
                                        echo ($total > 0) ? round(($disponibles / $total) * 100, 1) : 0;
                                    ?>%" readonly>
                                </div>
                                <div class="input-group">
                                    <label>Asesores Asignados (%)</label>
                                    <input type="text" value="<?php 
                                        $total = $estadisticas['total_asesores'] ?? 0;
                                        $asignados = $estadisticas['asesores_asignados'] ?? 0;
                                        echo ($total > 0) ? round(($asignados / $total) * 100, 1) : 0;
                                    ?>%" readonly>
                                </div>
                                <div class="input-group">
                                    <label>Clientes Nuevos (%)</label>
                                    <input type="text" value="<?php 
                                        $total = $estadisticas['total_clientes'] ?? 0;
                                        $nuevos = $estadisticas['clientes_nuevos'] ?? 0;
                                        echo ($total > 0) ? round(($nuevos / $total) * 100, 1) : 0;
                                    ?>%" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PESTAÑA 2: USUARIOS -->
                    <div class="tab-content" id="tab-usuarios" style="display: none;">
                        <div class="left-content">
                            <h4 style="margin-top: 0;">Resumen de Usuarios</h4>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <h5>Total Usuarios</h5>
                                    <div class="stat-value"><?php echo $estadisticas['total_usuarios'] ?? 0; ?></div>
                                    <div class="stat-subtitle">En el sistema</div>
                                </div>
                                <div class="stat-card">
                                    <h5>Usuarios Activos</h5>
                                    <div class="stat-value"><?php echo $estadisticas['usuarios_activos'] ?? 0; ?></div>
                                    <div class="stat-subtitle">Estado activo</div>
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
                            
                            <div class="quick-actions">
                                <button class="btn btn-primary" onclick="openModal('crear-usuario')">
                                    <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
                                </button>
                                <button class="btn btn-secondary" onclick="window.location.href='index.php?action=admin_usuarios'">
                                    <i class="fas fa-users"></i> Gestionar Usuarios
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PESTAÑA 3: CLIENTES -->
                    <div class="tab-content" id="tab-clientes" style="display: none;">
                        <div class="left-content">
                            <h4 style="margin-top: 0;">Resumen de Clientes</h4>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <h5>Total Clientes</h5>
                                    <div class="stat-value"><?php echo $estadisticas['total_clientes'] ?? 0; ?></div>
                                    <div class="stat-subtitle">En la base de datos</div>
                                </div>
                                <div class="stat-card">
                                    <h5>Gestionados</h5>
                                    <div class="stat-value"><?php echo $estadisticas['clientes_gestionados'] ?? 0; ?></div>
                                    <div class="stat-subtitle">Con al menos una gestión</div>
                                </div>
                                <div class="stat-card">
                                    <h5>Pendientes</h5>
                                    <div class="stat-value"><?php echo $estadisticas['clientes_pendientes'] ?? 0; ?></div>
                                    <div class="stat-subtitle">Sin gestionar</div>
                                </div>
                                <div class="stat-card">
                                    <h5>Nuevos (30 días)</h5>
                                    <div class="stat-value"><?php echo $estadisticas['clientes_nuevos'] ?? 0; ?></div>
                                    <div class="stat-subtitle">Último mes</div>
                                </div>
                            </div>
                            
                            <div class="quick-actions">
                                <button class="btn btn-primary" onclick="openModal('cargar-clientes')">
                                    <i class="fas fa-upload"></i> Cargar Nuevos Clientes
                                </button>
                                <button class="btn btn-secondary" onclick="openModal('generar-reporte')">
                                    <i class="fas fa-file-alt"></i> Generar Reporte
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PESTAÑA 4: ACTIVIDAD -->
                    <div class="tab-content" id="tab-actividad" style="display: none;">
                        <div class="left-content">
                            <h4 style="margin-top: 0;">Actividad Reciente del Sistema</h4>
                            <div class="activity-list">
                                <?php if (!empty($estadisticas['actividad_reciente'])): ?>
                                    <?php foreach ($estadisticas['actividad_reciente'] as $actividad): ?>
                                        <div class="history-item">
                                            <div class="activity-icon">
                                                <?php 
                                                $icono = 'fas fa-info-circle';
                                                switch($actividad['tipo']) {
                                                    case 'usuario_creado':
                                                        $icono = 'fas fa-user-plus';
                                                        break;
                                                    case 'carga_excel':
                                                        $icono = 'fas fa-upload';
                                                        break;
                                                    case 'asignacion_asesor':
                                                        $icono = 'fas fa-user-friends';
                                                        break;
                                                    case 'gestion_cliente':
                                                        $icono = 'fas fa-phone';
                                                        break;
                                                }
                                                ?>
                                                <i class="<?php echo $icono; ?>"></i>
                                            </div>
                                            <div class="activity-content">
                                                <h5><?php echo htmlspecialchars($actividad['descripcion']); ?></h5>
                                                <small>
                                                    <strong><?php echo htmlspecialchars($actividad['usuario_nombre']); ?></strong> 
                                                    (<?php echo ucfirst($actividad['usuario_rol']); ?>) - 
                                                    <?php echo $actividad['tiempo_relativo']; ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="history-item">
                                        <h5>No hay actividad reciente</h5>
                                        <small>El sistema está esperando actividad</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <aside class="right-sidebar">
                        <h4>Acciones Rápidas</h4>
                        <div class="quick-actions-sidebar">
                            <button class="action-btn-sidebar" onclick="openModal('crear-usuario')">
                                <i class="fas fa-user-plus"></i> Nuevo Usuario
                            </button>
                            <button class="action-btn-sidebar" onclick="openModal('asignar-personal')">
                                <i class="fas fa-user-friends"></i> Asignar
                            </button>
                            <button class="action-btn-sidebar" onclick="openModal('cargar-clientes')">
                                <i class="fas fa-upload"></i> Cargar
                            </button>
                            <button class="action-btn-sidebar" onclick="openModal('generar-reporte')">
                                <i class="fas fa-file-alt"></i> Reporte
                            </button>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </div>

    <!-- Modals -->
    <!-- Modal Crear Usuario -->
    <div id="crear-usuario" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Crear Nuevo Usuario</h3>
                <button class="close-btn" onclick="closeModal('crear-usuario')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-crear-usuario" onsubmit="crearUsuario(event)">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="cedula">Cédula</label>
                        <input type="text" id="cedula" name="cedula" required>
                    </div>
                    <div class="form-group">
                        <label for="usuario">Usuario</label>
                        <input type="text" id="usuario" name="usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" required>
                    </div>
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select id="rol" name="rol" required>
                            <option value="">Seleccionar rol</option>
                            <option value="administrador">Administrador</option>
                            <option value="coordinador">Coordinador</option>
                            <option value="asesor">Asesor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="coordinador_id">Coordinador (solo para asesores)</label>
                        <select id="coordinador_id" name="coordinador_id">
                            <option value="">Sin asignar</option>
                            <?php foreach ($coordinadores as $coord): ?>
                                <option value="<?php echo $coord['id']; ?>"><?php echo $coord['nombre_completo']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('crear-usuario')">Cancelar</button>
                        <button type="submit" id="btn-crear-usuario" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Crear Usuario
                        </button>
                    </div>
                </form>
                <div id="alert-container-crear"></div>
            </div>
        </div>
    </div>

    <!-- Modal Asignar Personal -->
    <div id="asignar-personal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Asignar Personal</h3>
                <button class="close-btn" onclick="closeModal('asignar-personal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-asignar-personal" onsubmit="asignarPersonal(event)">
                    <div class="form-group">
                        <label for="asesor_id">Asesor</label>
                        <select id="asesor_id" name="asesor_id" required>
                            <option value="">Seleccionar asesor</option>
                            <?php foreach ($estadisticas['asesores_sin_coordinador'] ?? [] as $asesor): ?>
                                <option value="<?php echo $asesor['id']; ?>"><?php echo $asesor['nombre_completo']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="coordinador_id">Coordinador</label>
                        <select id="coordinador_id" name="coordinador_id" required>
                            <option value="">Seleccionar coordinador</option>
                            <?php foreach ($coordinadores as $coord): ?>
                                <option value="<?php echo $coord['id']; ?>"><?php echo $coord['nombre_completo']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('asignar-personal')">Cancelar</button>
                        <button type="submit" id="btn-asignar-personal" class="btn btn-primary">
                            <i class="fas fa-user-friends"></i> Asignar
                        </button>
                    </div>
                </form>
                <div id="alert-container-asignar"></div>
            </div>
        </div>
    </div>

    <!-- Modal Cargar Clientes -->
    <div id="cargar-clientes" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cargar Clientes desde Excel</h3>
                <button class="close-btn" onclick="closeModal('cargar-clientes')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-cargar-clientes" onsubmit="cargarClientes(event)">
                    <div class="form-group">
                        <label for="archivo">Seleccionar archivo Excel/CSV</label>
                        <input type="file" id="archivo" name="archivo" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <div class="form-group">
                        <label for="coordinador_id">Asignar a Coordinador</label>
                        <select id="coordinador_id" name="coordinador_id" required>
                            <option value="">Seleccionar coordinador</option>
                            <?php foreach ($coordinadores as $coord): ?>
                                <option value="<?php echo $coord['id']; ?>"><?php echo $coord['nombre_completo']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('cargar-clientes')">Cancelar</button>
                        <button type="submit" id="btn-cargar-clientes" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Cargar Clientes
                        </button>
                    </div>
                </form>
                <div id="alert-container-cargar"></div>
            </div>
        </div>
    </div>

    <!-- Modal Generar Reporte -->
    <div id="generar-reporte" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Generar Reporte</h3>
                <button class="close-btn" onclick="closeModal('generar-reporte')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-generar-reporte" onsubmit="generarReporte(event)">
                    <div class="form-group">
                        <label for="tipo_reporte">Tipo de Reporte</label>
                        <select id="tipo_reporte" name="tipo_reporte" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="usuarios">Reporte de Usuarios</option>
                            <option value="clientes">Reporte de Clientes</option>
                            <option value="gestion">Reporte de Gestión</option>
                            <option value="productividad">Reporte de Productividad</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">Fecha de Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" required>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('generar-reporte')">Cancelar</button>
                        <button type="submit" id="btn-generar-reporte" class="btn btn-primary">
                            <i class="fas fa-file-alt"></i> Generar Reporte
                        </button>
                    </div>
                </form>
                <div id="alert-container-reporte"></div>
            </div>
        </div>
    </div>

    <script src="assets/js/admin-dashboard.js"></script>
    <script src="assets/js/admin.js"></script>
    <script>
        // Función para cambiar entre pestañas
        function cambiarTab(tabName) {
            // Ocultar todas las pestañas
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remover clase active de todas las pestañas
            const tabSpans = document.querySelectorAll('.main-tabs span');
            tabSpans.forEach(span => {
                span.classList.remove('active');
            });
            
            // Mostrar la pestaña seleccionada
            const selectedTab = document.getElementById('tab-' + tabName);
            if (selectedTab) {
                selectedTab.style.display = 'block';
            }
            
            // Marcar la pestaña como activa
            const selectedSpan = document.querySelector(`[onclick="cambiarTab('${tabName}')"]`);
            if (selectedSpan) {
                selectedSpan.classList.add('active');
            }
        }
        
        // Función para abrir modales
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
            }
        }
        
        // Función para cerrar modales
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        }
        
        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Función para crear usuario (AJAX)
        function crearUsuario(event) {
            event.preventDefault();
            
            const form = document.getElementById('form-crear-usuario');
            const btnCrear = document.getElementById('btn-crear-usuario');
            
            // Validar formulario
            if (!validateForm('form-crear-usuario')) {
                return;
            }
            
            // Deshabilitar botón y mostrar loading
            btnCrear.disabled = true;
            btnCrear.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';
            
            // Limpiar alertas anteriores
            const alertContainer = document.getElementById('alert-container-crear');
            alertContainer.innerHTML = '';
            
            // Recopilar datos del formulario
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            // Enviar solicitud AJAX
            fetch('index.php?action=create_usuario', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const result = JSON.parse(data);
                    if (result.success) {
                        mostrarAlerta(result.message, 'success', 'crear-usuario');
                        form.reset();
                        setTimeout(() => {
                            closeModal('crear-usuario');
                            location.reload();
                        }, 2000);
                    } else {
                        mostrarAlerta(result.message, 'error', 'crear-usuario');
                    }
                } catch (e) {
                    mostrarAlerta('Error al procesar la respuesta del servidor', 'error', 'crear-usuario');
                }
            })
            .catch(error => {
                mostrarAlerta('Error de conexión: ' + error.message, 'error', 'crear-usuario');
            })
            .finally(() => {
                // Restaurar botón
                btnCrear.disabled = false;
                btnCrear.innerHTML = '<i class="fas fa-user-plus"></i> Crear Usuario';
            });
        }
        
        // Función para mostrar alertas
        function mostrarAlerta(mensaje, tipo, modalId) {
            const alertContainer = document.getElementById('alert-container-crear');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${mensaje}
            `;
            
            alertContainer.appendChild(alertDiv);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Función para validar formulario
        function validateForm(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input[required], select[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('error');
                    isValid = false;
                } else {
                    input.classList.remove('error');
                }
            });
            
            return isValid;
        }
        
        // Función para asignar personal (AJAX)
        function asignarPersonal(event) {
            event.preventDefault();
            
            const form = document.getElementById('form-asignar-personal');
            const btnAsignar = document.getElementById('btn-asignar-personal');
            
            // Validar formulario
            if (!validateForm('form-asignar-personal')) {
                return;
            }
            
            // Deshabilitar botón y mostrar loading
            btnAsignar.disabled = true;
            btnAsignar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Asignando...';
            
            // Limpiar alertas anteriores
            const alertContainer = document.getElementById('alert-container-asignar');
            alertContainer.innerHTML = '';
            
            // Recopilar datos del formulario
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            // Enviar solicitud AJAX
            fetch('index.php?action=asignar_asesor', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const result = JSON.parse(data);
                    if (result.success) {
                        mostrarAlertaAsignar(result.message, 'success', 'asignar-personal');
                        form.reset();
                        setTimeout(() => {
                            closeModal('asignar-personal');
                            location.reload();
                        }, 2000);
                    } else {
                        mostrarAlertaAsignar(result.message, 'error', 'asignar-personal');
                    }
                } catch (e) {
                    mostrarAlertaAsignar('Error al procesar la respuesta del servidor', 'error', 'asignar-personal');
                }
            })
            .catch(error => {
                mostrarAlertaAsignar('Error de conexión: ' + error.message, 'error', 'asignar-personal');
            })
            .finally(() => {
                // Restaurar botón
                btnAsignar.disabled = false;
                btnAsignar.innerHTML = '<i class="fas fa-user-friends"></i> Asignar';
            });
        }
        
        // Función para mostrar alertas de asignación
        function mostrarAlertaAsignar(mensaje, tipo, modalId) {
            const alertContainer = document.getElementById('alert-container-asignar');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${mensaje}
            `;
            
            alertContainer.appendChild(alertDiv);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Función para cargar clientes (AJAX)
        function cargarClientes(event) {
            event.preventDefault();
            
            const form = document.getElementById('form-cargar-clientes');
            const btnCargar = document.getElementById('btn-cargar-clientes');
            const fileInput = document.getElementById('archivo');
            
            // Validar formulario
            if (!validateForm('form-cargar-clientes')) {
                return;
            }
            
            // Validar archivo
            if (!fileInput.files[0]) {
                mostrarAlertaCargar('Por favor seleccione un archivo', 'error', 'cargar-clientes');
                return;
            }
            
            // Deshabilitar botón y mostrar loading
            btnCargar.disabled = true;
            btnCargar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
            
            // Limpiar alertas anteriores
            const alertContainer = document.getElementById('alert-container-cargar');
            alertContainer.innerHTML = '';
            
            // Recopilar datos del formulario
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            // Enviar solicitud AJAX
            fetch('index.php?action=cargar_clientes', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const result = JSON.parse(data);
                    if (result.success) {
                        mostrarAlertaCargar(result.message, 'success', 'cargar-clientes');
                        form.reset();
                        setTimeout(() => {
                            closeModal('cargar-clientes');
                            location.reload();
                        }, 2000);
                    } else {
                        mostrarAlertaCargar(result.message, 'error', 'cargar-clientes');
                    }
                } catch (e) {
                    mostrarAlertaCargar('Error al procesar la respuesta del servidor', 'error', 'cargar-clientes');
                }
            })
            .catch(error => {
                mostrarAlertaCargar('Error de conexión: ' + error.message, 'error', 'cargar-clientes');
            })
            .finally(() => {
                // Restaurar botón
                btnCargar.disabled = false;
                btnCargar.innerHTML = '<i class="fas fa-upload"></i> Cargar Clientes';
            });
        }
        
        // Función para mostrar alertas de carga
        function mostrarAlertaCargar(mensaje, tipo, modalId) {
            const alertContainer = document.getElementById('alert-container-cargar');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${mensaje}
            `;
            
            alertContainer.appendChild(alertDiv);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Función para generar reportes (AJAX)
        function generarReporte(event) {
            event.preventDefault();
            
            const form = document.getElementById('form-generar-reporte');
            const btnGenerar = document.getElementById('btn-generar-reporte');
            
            // Validar formulario
            if (!validateForm('form-generar-reporte')) {
                return;
            }
            
            // Deshabilitar botón y mostrar loading
            btnGenerar.disabled = true;
            btnGenerar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
            
            // Limpiar alertas anteriores
            const alertContainer = document.getElementById('alert-container-reporte');
            alertContainer.innerHTML = '';
            
            // Recopilar datos del formulario
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            // Enviar solicitud AJAX
            fetch('index.php?action=generar_reporte', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                try {
                    const result = JSON.parse(data);
                    if (result.success) {
                        mostrarAlertaReporte(result.message, 'success', 'generar-reporte');
                        form.reset();
                        setTimeout(() => {
                            closeModal('generar-reporte');
                        }, 2000);
                    } else {
                        mostrarAlertaReporte(result.message, 'error', 'generar-reporte');
                    }
                } catch (e) {
                    mostrarAlertaReporte('Error al procesar la respuesta del servidor', 'error', 'generar-reporte');
                }
            })
            .catch(error => {
                mostrarAlertaReporte('Error de conexión: ' + error.message, 'error', 'generar-reporte');
            })
            .finally(() => {
                // Restaurar botón
                btnGenerar.disabled = false;
                btnGenerar.innerHTML = '<i class="fas fa-file-alt"></i> Generar Reporte';
            });
        }
        
        // Función para mostrar alertas de reportes
        function mostrarAlertaReporte(mensaje, tipo, modalId) {
            const alertContainer = document.getElementById('alert-container-reporte');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${mensaje}
            `;
            
            alertContainer.appendChild(alertDiv);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>
