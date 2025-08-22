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

    <!-- Modal de Detalles del Asesor -->
    <div id="asesorModal" class="modal" style="display: none;">
        <div class="modal-content modal-extra-large">
            <div class="modal-header">
                <h3 id="asesorModalTitle">Detalles del Asesor</h3>
                <span class="close" onclick="cerrarModalAsesor()">&times;</span>
            </div>
            <div class="modal-body">
                <!-- Información del Asesor -->
                <div class="asesor-info-section">
                    <div class="asesor-profile">
                        <div class="asesor-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="asesor-details">
                            <h4 id="asesorNombre">Nombre del Asesor</h4>
                            <p id="asesorEmail">email@ejemplo.com</p>
                            <span class="asesor-status-badge active">Activo</span>
                        </div>
                    </div>
                    
                    <div class="asesor-stats-summary">
                        <div class="stat-summary-item">
                            <span class="stat-number" id="totalClientesAsesor">0</span>
                            <span class="stat-label">Total Clientes</span>
                        </div>
                        <div class="stat-summary-item">
                            <span class="stat-number" id="clientesGestionados">0</span>
                            <span class="stat-label">Gestionados</span>
                        </div>
                        <div class="stat-summary-item">
                            <span class="stat-number" id="clientesPendientes">0</span>
                            <span class="stat-label">Pendientes</span>
                        </div>
                    </div>
                </div>

                <!-- Barra de Búsqueda y Filtros -->
                <div class="search-filters-section">
                    <div class="search-box">
                        <input type="text" id="searchCedula" placeholder="🔍 Buscar por cédula..." class="search-input">
                        <button type="button" onclick="buscarCliente()" class="btn-search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <div class="filters-container">
                        <select id="filterTipificacion" onchange="filtrarPorTipificacion()" class="filter-select">
                            <option value="">📋 Todas las tipificaciones</option>
                            <option value="asignacion_cita">📅 Asignación de Citas</option>
                            <option value="volver_llamar">📞 Volver a Llamar</option>
                            <option value="fuera_ciudad">🌍 Fuera de Ciudad</option>
                            <option value="no_interesa">❌ No Interesa</option>
                            <option value="contactado">✅ Contactado</option>
                            <option value="no_contactado">📵 No Contactado</option>
                        </select>
                        
                        <select id="filterEstado" onchange="filtrarPorEstado()" class="filter-select">
                            <option value="">🏷️ Todos los estados</option>
                            <option value="no_gestionado">⏳ No Gestionado</option>
                            <option value="gestionado">✅ Gestionado</option>
                            <option value="Cita Programada">📅 Cita Programada</option>
                            <option value="En Proceso">🔄 En Proceso</option>
                        </select>
                    </div>
                </div>

                <!-- Lista de Clientes -->
                <div class="clientes-section">
                    <h4>📋 Clientes del Asesor</h4>
                    <div class="clientes-table-container">
                        <table class="clientes-table" id="clientesTable">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Cédula</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    <th>Última Gestión</th>
                                    <th>Tipificación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="clientesTableBody">
                                <!-- Los clientes se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    <div id="noClientesMessage" class="no-data-message" style="display: none;">
                        <i class="fas fa-info-circle"></i>
                        <p>No se encontraron clientes con los filtros aplicados.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModalAsesor()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Transferencia de Cliente -->
    <div id="transferirModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔄 Transferir Cliente</h3>
                <span class="close" onclick="cerrarModalTransferir()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="cliente-transferir-info">
                    <h4 id="clienteTransferirNombre">Nombre del Cliente</h4>
                    <p><strong>Cédula:</strong> <span id="clienteTransferirCedula">1234567890</span></p>
                    <p><strong>Asesor Actual:</strong> <span id="clienteTransferirAsesorActual">Asesor Actual</span></p>
                </div>
                
                <div class="form-group">
                    <label for="nuevoAsesor">Nuevo Asesor:</label>
                    <select id="nuevoAsesor" class="form-select" required>
                        <option value="">Selecciona un asesor...</option>
                        <!-- Los asesores se cargarán dinámicamente -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="motivoTransferir">Motivo de la Transferencia:</label>
                    <textarea id="motivoTransferir" rows="3" placeholder="Explica el motivo de la transferencia..." class="form-textarea"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="cerrarModalTransferir()">Cancelar</button>
                <button class="btn btn-primary" onclick="confirmarTransferirCliente()">Confirmar Transferencia</button>
            </div>
        </div>
    </div>

    <script src="assets/js/coordinador-dashboard.js"></script>
</body>
</html>
