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
                
                <div class="filter-dropdown">
                    <select id="estado-filter">
                        <option value="">Todos los estados</option>
                        <option value="Disponible" <?php echo $estado_filter === 'Disponible' ? 'selected' : ''; ?>>Disponible</option>
                        <option value="Contactado" <?php echo $estado_filter === 'Contactado' ? 'selected' : ''; ?>>Contactado</option>
                        <option value="En Proceso" <?php echo $estado_filter === 'En Proceso' ? 'selected' : ''; ?>>En Proceso</option>
                        <option value="Cita Programada" <?php echo $estado_filter === 'Cita Programada' ? 'selected' : ''; ?>>Cita Programada</option>
                        <option value="Cita Completada" <?php echo $estado_filter === 'Cita Completada' ? 'selected' : ''; ?>>Cita Completada</option>
                        <option value="No Interesado" <?php echo $estado_filter === 'No Interesado' ? 'selected' : ''; ?>>No le Interesa</option>
                        <option value="No Contactable" <?php echo $estado_filter === 'No Contactable' ? 'selected' : ''; ?>>No Contactable</option>
                    </select>
                </div>
                
                <div class="filter-dropdown">
                    <select id="tipificacion-filter">
                        <option value="">Todas las tipificaciones</option>
                        <option value="asignacion_cita" <?php echo $tipificacion_filter === 'asignacion_cita' ? 'selected' : ''; ?>>Cita Programada</option>
                        <option value="volver_llamar" <?php echo $tipificacion_filter === 'volver_llamar' ? 'selected' : ''; ?>>Volver a Llamar</option>
                        <option value="fuera_ciudad" <?php echo $tipificacion_filter === 'fuera_ciudad' ? 'selected' : ''; ?>>Fuera de Ciudad</option>
                        <option value="no_interesa" <?php echo $tipificacion_filter === 'no_interesa' ? 'selected' : ''; ?>>No le Interesa</option>
                        <option value="no_contactado" <?php echo $tipificacion_filter === 'no_contactado' ? 'selected' : ''; ?>>No Contactado</option>
                    </select>
                </div>
                
                <button class="btn-primary" onclick="aplicarFiltros()">
                    <i class="fas fa-filter"></i>
                    Aplicar Filtros
                </button>
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
                                    
                                    <?php if (isset($cliente['proxima_fecha']) && $cliente['proxima_fecha']): ?>
                                        <div class="next-call">
                                            <i class="fas fa-clock"></i>
                                            Próxima llamada: <?php echo date('d/m/Y H:i', strtotime($cliente['proxima_fecha'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginación elegante -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-elegant">
                            <?php if ($page > 1): ?>
                                <a href="?action=asesor_clientes&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&estado_filter=<?php echo urlencode($estado_filter); ?>" class="page-link prev">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            // Calcular el rango de páginas a mostrar
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            // Mostrar primera página si no está en el rango
                            if ($start_page > 1) {
                                echo '<a href="?action=asesor_clientes&page=1&search=' . urlencode($search) . '&estado_filter=' . urlencode($estado_filter) . '" class="page-link">1</a>';
                                if ($start_page > 2) {
                                    echo '<span class="page-ellipsis">...</span>';
                                }
                            }
                            
                            // Mostrar páginas del rango
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                $active_class = ($i == $page) ? 'active' : '';
                                echo '<a href="?action=asesor_clientes&page=' . $i . '&search=' . urlencode($search) . '&estado_filter=' . urlencode($estado_filter) . '" class="page-link ' . $active_class . '">' . $i . '</a>';
                            }
                            
                            // Mostrar última página si no está en el rango
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<span class="page-ellipsis">...</span>';
                                }
                                echo '<a href="?action=asesor_clientes&page=' . $total_pages . '&search=' . urlencode($search) . '&estado_filter=' . urlencode($estado_filter) . '" class="page-link">' . $total_pages . '</a>';
                            }
                            ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?action=asesor_clientes&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&estado_filter=<?php echo urlencode($estado_filter); ?>" class="page-link next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
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
    </script>
</body>
</html>
