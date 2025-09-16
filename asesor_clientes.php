<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Asesor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/asesor-dashboard.css">
    <link rel="stylesheet" href="assets/css/asesor-filtros.css">
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
                <a href="index.php?action=asesor_dashboard" class="nav-item" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
                <a href="index.php?action=asesor_clientes" class="nav-item active" title="Clientes">
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
                    <h1>Mis Clientes</h1>
                    <p>Gestiona los clientes asignados a ti</p>
                </div>
                <div class="top-bar-right">
                    <!-- Campanita de notificaciones -->
                    <div class="notification-bell" id="notificationBell" onclick="mostrarNotificaciones()">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                    </div>
                    
                    <div class="date-time">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('d/m/Y'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Filtros y búsqueda -->
            <div class="filters-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-cedula" placeholder="Buscar por cédula..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <!-- Botones de filtro rápido -->
                <div class="quick-filter-buttons">
                    <button class="filter-btn active" data-filter="todos" onclick="filtrarRapido('todos')">
                        <i class="fas fa-users"></i>
                        Todos los Clientes
                        <span class="filter-count"><?php echo $total_clientes; ?></span>
                    </button>
                    <button class="filter-btn" data-filter="gestionados" onclick="filtrarRapido('gestionados')">
                        <i class="fas fa-phone"></i>
                        Gestionados
                        <span class="filter-count"><?php echo count(array_filter($clientes, function($c) { 
                            return !empty($c['ultima_gestion']); 
                        })); ?></span>
                    </button>
                    <button class="filter-btn" data-filter="no_gestionados" onclick="filtrarRapido('no_gestionados')">
                        <i class="fas fa-user-plus"></i>
                        No Gestionados
                        <span class="filter-count"><?php echo count(array_filter($clientes, function($c) { 
                            return empty($c['ultima_gestion']); 
                        })); ?></span>
                    </button>
                </div>
                
                <!-- Filtros avanzados -->
                <div class="advanced-filters">
                    <div class="filter-dropdown">
                        <select id="estado-filter">
                            <option value="">Estado de Gestión</option>
                            <option value="gestionado">Gestionado</option>
                            <option value="no_gestionado">No Gestionado</option>
                        </select>
                    </div>
                    
                    <div class="filter-dropdown">
                        <select id="tipificacion-filter">
                            <option value="">Tipificación</option>
                            <option value="asignacion_cita">Asignación de Cita</option>
                            <option value="volver_llamar">Volver a Llamar</option>
                            <option value="fuera_ciudad">Fuera de Ciudad</option>
                            <option value="no_interesa">No le Interesa</option>
                            <option value="no_contactado">No Contactado</option>
                        </select>
                    </div>
                    
                    <button class="btn-primary" onclick="aplicarFiltros()">
                        <i class="fas fa-filter"></i>
                        Aplicar Filtros
                    </button>
                    
                    <button class="btn-secondary" onclick="limpiarFiltros()">
                        <i class="fas fa-times"></i>
                        Limpiar Filtros
                    </button>
                </div>
            </div>

            <!-- Lista de clientes -->
            <div class="clients-section">
                <div class="section-header">
                    <h2>Clientes Asignados (<?php echo $total_clientes; ?>)</h2>
                    <div class="client-stats">
                        <span class="stat-item">
                            <i class="fas fa-users"></i>
                            Total: <?php echo $total_clientes; ?>
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-phone"></i>
                            Pendientes: <?php echo count(array_filter($clientes, function($c) { return ($c['estado_gestion'] ?? '') === 'Disponible'; })); ?>
                        </span>
                    </div>
                </div>

                <?php if (empty($clientes)): ?>
                    <div class="no-clients">
                        <i class="fas fa-users-slash"></i>
                        <h3>No hay clientes asignados</h3>
                        <p>No tienes clientes asignados en este momento.</p>
                    </div>
                <?php else: ?>
                    <div class="clients-grid">
                        <?php foreach ($clientes as $cliente): ?>
                            <div class="client-card">
                                <div class="client-header">
                                    <div class="client-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="client-info">
                                        <h3><?php echo htmlspecialchars($cliente['nombre_completo'] ?? $cliente['nombre']); ?></h3>
                                        <p class="client-cedula">Cédula: <?php echo htmlspecialchars($cliente['cedula']); ?></p>
                                        <p class="client-phone">
                                            <i class="fas fa-phone"></i>
                                            <?php echo htmlspecialchars($cliente['numero_telefono'] ?? $cliente['telefono']); ?>
                                        </p>
                                    </div>
                                    <div class="client-status <?php echo $cliente['estado_gestion'] ?? 'Disponible'; ?>">
                                        <?php echo $cliente['estado_gestion'] ?? 'Disponible'; ?>
                                    </div>
                                </div>
                                
                                <div class="client-actions">
                                    <button class="btn-manage" onclick="gestionarCliente(<?php echo $cliente['id']; ?>)">
                                        <i class="fas fa-phone"></i>
                                        Gestionar Cliente
                                    </button>
                                </div>
                                
                                <?php if (isset($cliente['ultima_tipificacion']) && $cliente['ultima_tipificacion'] === 'volver_llamar' && isset($cliente['proxima_fecha']) && $cliente['proxima_fecha']): ?>
                                    <div class="next-call-info">
                                        <i class="fas fa-clock"></i>
                                        <span class="next-call-text">Próxima llamada: <?php echo date('d/m/Y H:i', strtotime($cliente['proxima_fecha'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginación elegante mejorada -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-elegant">
                            <!-- Información de páginas -->
                            <div class="page-info">
                                Página <?php echo $page; ?> de <?php echo $total_pages; ?> 
                                (<?php echo $total_clientes; ?> clientes total)
                            </div>
                            
                            <!-- Navegación de páginas -->
                            <div class="page-navigation">
                                <?php if ($page > 1): ?>
                                    <a href="?action=asesor_clientes&page=1&search=<?php echo urlencode($search); ?>&estado_filter=<?php echo urlencode($estado_filter); ?>&tipificacion_filter=<?php echo urlencode($tipificacion_filter ?? ''); ?>" class="page-link first" title="Primera página">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                    <a href="?action=asesor_clientes&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&estado_filter=<?php echo urlencode($estado_filter); ?>&tipificacion_filter=<?php echo urlencode($tipificacion_filter ?? ''); ?>" class="page-link prev" title="Página anterior">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php
                                // Calcular el rango de páginas a mostrar (máximo 5 páginas)
                                $range = 2;
                                $start_page = max(1, $page - $range);
                                $end_page = min($total_pages, $page + $range);
                                
                                // Ajustar el rango para mostrar siempre 5 páginas si es posible
                                if ($end_page - $start_page < 4) {
                                    if ($start_page == 1) {
                                        $end_page = min($total_pages, $start_page + 4);
                                    } elseif ($end_page == $total_pages) {
                                        $start_page = max(1, $end_page - 4);
                                    }
                                }
                                
                                // Mostrar primera página si no está en el rango
                                if ($start_page > 1) {
                                    echo '<a href="?action=asesor_clientes&page=1&search=' . urlencode($search) . '&estado_filter=' . urlencode($estado_filter) . '&tipificacion_filter=' . urlencode($tipificacion_filter ?? '') . '" class="page-link">1</a>';
                                    if ($start_page > 2) {
                                        echo '<span class="page-ellipsis">...</span>';
                                    }
                                }
                                
                                // Mostrar páginas del rango
                                for ($i = $start_page; $i <= $end_page; $i++) {
                                    $active_class = ($i == $page) ? 'active' : '';
                                    echo '<a href="?action=asesor_clientes&page=' . $i . '&search=' . urlencode($search) . '&estado_filter=' . urlencode($estado_filter) . '&tipificacion_filter=' . urlencode($tipificacion_filter ?? '') . '" class="page-link ' . $active_class . '">' . $i . '</a>';
                                }
                                
                                // Mostrar última página si no está en el rango
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<span class="page-ellipsis">...</span>';
                                    }
                                    echo '<a href="?action=asesor_clientes&page=' . $total_pages . '&search=' . urlencode($search) . '&estado_filter=' . urlencode($estado_filter) . '&tipificacion_filter=' . urlencode($tipificacion_filter ?? '') . '" class="page-link">' . $total_pages . '</a>';
                                }
                                ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?action=asesor_clientes&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&estado_filter=<?php echo urlencode($estado_filter); ?>&tipificacion_filter=<?php echo urlencode($tipificacion_filter ?? ''); ?>" class="page-link next" title="Página siguiente">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                    <a href="?action=asesor_clientes&page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&estado_filter=<?php echo urlencode($estado_filter); ?>&tipificacion_filter=<?php echo urlencode($tipificacion_filter ?? ''); ?>" class="page-link last" title="Última página">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de Notificaciones -->
    <div id="notificationModal" class="modal-overlay" style="display: none;">
        <div class="modal-content notification-modal">
            <div class="modal-header">
                <h3><i class="fas fa-phone"></i> Clientes para Llamar Hoy</h3>
                <button class="modal-close" onclick="cerrarModalNotificaciones()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="notificationList">
                    <!-- Las notificaciones se cargarán aquí dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function aplicarFiltros() {
            const search = document.getElementById('search-cedula').value;
            const estadoFilter = document.getElementById('estado-filter').value;
            const tipificacionFilter = document.getElementById('tipificacion-filter').value;
            
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (estadoFilter) params.append('estado_filter', estadoFilter);
            if (tipificacionFilter) params.append('tipificacion_filter', tipificacionFilter);
            
            window.location.href = 'index.php?action=asesor_clientes&' + params.toString();
        }

        function gestionarCliente(clienteId) {
            window.location.href = 'index.php?action=asesor_gestionar_cliente&cliente_id=' + clienteId;
        }

        // Aplicar filtros al presionar Enter en la búsqueda
        document.getElementById('search-cedula').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                aplicarFiltros();
            }
        });

        // Cargar notificaciones al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarNotificaciones();
        });

        // Función para cargar notificaciones del día
        function cargarNotificaciones() {
            fetch('index.php?action=asesor_obtener_notificaciones')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        actualizarBadgeNotificaciones(data.notificaciones.length);
                        if (data.notificaciones.length > 0) {
                            mostrarBadgeNotificaciones();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error al cargar notificaciones:', error);
                });
        }

        // Función para mostrar el modal de notificaciones
        function mostrarNotificaciones() {
            fetch('index.php?action=asesor_obtener_notificaciones')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarModalNotificaciones(data.notificaciones);
                    }
                })
                .catch(error => {
                    console.error('Error al cargar notificaciones:', error);
                });
        }

        // Función para mostrar el modal con las notificaciones
        function mostrarModalNotificaciones(notificaciones) {
            const modal = document.getElementById('notificationModal');
            const notificationList = document.getElementById('notificationList');
            
            if (notificaciones.length === 0) {
                notificationList.innerHTML = `
                    <div class="no-notifications">
                        <i class="fas fa-check-circle"></i>
                        <h4>No hay clientes para llamar hoy</h4>
                        <p>¡Excelente! Has completado todas las llamadas programadas para hoy.</p>
                    </div>`;
            } else {
                notificationList.innerHTML = notificaciones.map(notif => `
                    <div class="notification-item" data-cliente-id="${notif.cliente_id}">
                        <div class="notification-header">
                            <h4>${notif.nombre_completo}</h4>
                            <span class="badge-volver-llamar">Volver a Llamar</span>
                        </div>
                        <div class="notification-details">
                            <p><strong>Cédula:</strong> ${notif.cedula}</p>
                            <p><strong>Teléfono:</strong> ${notif.telefono}</p>
                            <p><strong>Programado para:</strong> ${formatearFecha(notif.proxima_fecha)}</p>
                        </div>
                        <div class="notification-actions">
                            <button class="btn-gestionar" onclick="gestionarClienteDesdeNotificacion(${notif.cliente_id})">
                                <i class="fas fa-phone"></i> Gestionar Ahora
                            </button>
                        </div>
                    </div>
                `).join('');
            }
            
            modal.style.display = 'flex';
        }

        // Función para formatear fecha
        function formatearFecha(fechaString) {
            if (!fechaString) return 'No programado';
            
            const fecha = new Date(fechaString);
            const ahora = new Date();
            const hoy = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate());
            const manana = new Date(hoy);
            manana.setDate(hoy.getDate() + 1);
            
            // Si la fecha es hoy
            if (fecha.toDateString() === hoy.toDateString()) {
                const hora = fecha.toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'});
                return `Hoy a las ${hora}`;
            }
            
            // Si la fecha es mañana
            if (fecha.toDateString() === manana.toDateString()) {
                const hora = fecha.toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'});
                return `Mañana a las ${hora}`;
            }
            
            // Si la fecha es pasada
            if (fecha < ahora) {
                return 'Vencido - ' + fecha.toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
            
            // Para fechas futuras
            return fecha.toLocaleDateString('es-ES', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Función para cerrar el modal de notificaciones
        function cerrarModalNotificaciones() {
            document.getElementById('notificationModal').style.display = 'none';
        }

        // Función para gestionar cliente desde notificaciones
        function gestionarClienteDesdeNotificacion(clienteId) {
            // Redirigir a la vista de gestión
            window.location.href = 'index.php?action=asesor_gestionar_cliente&cliente_id=' + clienteId;
        }

        // Función para actualizar el badge de notificaciones
        function actualizarBadgeNotificaciones(cantidad) {
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                badge.textContent = cantidad;
                badge.style.display = cantidad > 0 ? 'flex' : 'none';
            }
        }

        // Función para mostrar el badge de notificaciones
        function mostrarBadgeNotificaciones() {
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                badge.style.display = 'flex';
            }
        }

        // Cerrar modal al hacer clic fuera de él
        document.getElementById('notificationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalNotificaciones();
            }
        });

        // ===== FUNCIONES DE FILTRADO =====
        
        // Función para filtro rápido (todos, gestionados, no gestionados)
        function filtrarRapido(tipo) {
            // Actualizar botones activos
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Limpiar filtros avanzados
            document.getElementById('estado-filter').value = '';
            document.getElementById('tipificacion-filter').value = '';
            
            // Aplicar filtro rápido
            let url = 'index.php?action=asesor_clientes&page=1';
            const search = document.getElementById('search-cedula').value;
            
            if (search) {
                url += '&search=' + encodeURIComponent(search);
            }
            
            if (tipo === 'gestionados') {
                url += '&estado_filter=gestionado';
            } else if (tipo === 'no_gestionados') {
                url += '&estado_filter=no_gestionado';
            }
            
            // Redirigir con el filtro aplicado
            window.location.href = url;
        }
        
        // Función para aplicar filtros avanzados
        function aplicarFiltros() {
            const search = document.getElementById('search-cedula').value;
            const estadoFilter = document.getElementById('estado-filter').value;
            const tipificacionFilter = document.getElementById('tipificacion-filter').value;
            
            // Construir URL con filtros
            let url = 'index.php?action=asesor_clientes&page=1';
            
            if (search) {
                url += '&search=' + encodeURIComponent(search);
            }
            
            if (estadoFilter) {
                url += '&estado_filter=' + encodeURIComponent(estadoFilter);
            }
            
            if (tipificacionFilter) {
                url += '&tipificacion_filter=' + encodeURIComponent(tipificacionFilter);
            }
            
            // Redirigir con los filtros aplicados
            window.location.href = url;
        }
        
        // Función para limpiar todos los filtros
        function limpiarFiltros() {
            document.getElementById('search-cedula').value = '';
            document.getElementById('estado-filter').value = '';
            document.getElementById('tipificacion-filter').value = '';
            
            // Remover clase active de todos los botones
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Marcar "Todos los Clientes" como activo
            document.querySelector('[data-filter="todos"]').classList.add('active');
            
            // Redirigir sin filtros
            window.location.href = 'index.php?action=asesor_clientes&page=1';
        }
        
        // Event listener para búsqueda con Enter
        document.getElementById('search-cedula').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                aplicarFiltros();
            }
        });
        
        // Marcar botón activo según filtros actuales
        document.addEventListener('DOMContentLoaded', function() {
            const estadoFilter = '<?php echo $estado_filter ?? ""; ?>';
            const tipificacionFilter = '<?php echo $tipificacion_filter ?? ""; ?>';
            
            // Si hay filtros aplicados, marcar el botón correspondiente
            if (estadoFilter === 'gestionado') {
                document.querySelector('[data-filter="gestionados"]').classList.add('active');
                document.querySelector('[data-filter="todos"]').classList.remove('active');
            } else if (estadoFilter === 'no_gestionado') {
                document.querySelector('[data-filter="no_gestionados"]').classList.add('active');
                document.querySelector('[data-filter="todos"]').classList.remove('active');
            }
        });
    </script>
</body>
</html>
