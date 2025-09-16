<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Bases de Datos - Coordinador</title>
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="assets/css/coordinador-dashboard.css">
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
                <a href="index.php?action=coordinador_dashboard" class="nav-item" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
                <a href="index.php?action=coordinador_cargar_archivo" class="nav-item" title="Cargar Archivo">
                    <i class="fas fa-upload"></i>
                </a>
                <a href="index.php?action=coordinador_gestion" class="nav-item active" title="Gestión">
                    <i class="fas fa-database"></i>
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
                    <h1>Gestión de Bases de Datos</h1>
                    <p>Administra y asigna bases de datos de clientes a asesores</p>
                </div>
                <div class="top-bar-right">
                    <span class="user-info">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Coordinador'); ?>
                    </span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($basesDatos); ?></h3>
                        <p>Bases de Datos</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $basesAsignadas; ?></h3>
                        <p>Bases Asignadas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $basesDisponibles; ?></h3>
                        <p>Bases Disponibles</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $totalClientes; ?></h3>
                        <p>Total Clientes</p>
                    </div>
                </div>
            </div>

            <!-- Bases de Datos Table -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Bases de Datos de Clientes</h2>
                    <div class="section-actions">
                        <button class="btn btn-primary" onclick="abrirModalAsignarMultiple()">
                            <i class="fas fa-user-plus"></i>
                            Asignar Múltiples
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Nombre de la Base</th>
                                <th>Descripción</th>
                                <th>Total Clientes</th>
                                <th>Asesor Asignado</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($basesDatos)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="empty-state">
                                            <i class="fas fa-database"></i>
                                            <h3>No hay bases de datos</h3>
                                            <p>Sube un archivo CSV para crear tu primera base de datos</p>
                                            <a href="index.php?action=coordinador_cargar_archivo" class="btn btn-primary">
                                                <i class="fas fa-upload"></i>
                                                Subir Archivo
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($basesDatos as $base): ?>
                                    <tr data-base-id="<?php echo $base['id']; ?>">
                                        <td>
                                            <input type="checkbox" class="base-checkbox" value="<?php echo $base['id']; ?>">
                                        </td>
                                        <td>
                                            <div class="base-name">
                                                <strong><?php echo htmlspecialchars($base['nombre_base']); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="base-description">
                                                <?php echo htmlspecialchars($base['descripcion'] ?? 'Sin descripción'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="client-count">
                                                <?php echo number_format($base['total_clientes_actual']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($base['asesor_nombre']): ?>
                                                <div class="asesor-info">
                                                    <i class="fas fa-user"></i>
                                                    <span><?php echo htmlspecialchars($base['asesor_nombre']); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="no-asesor">Sin asignar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($base['estado']); ?>">
                                                <?php echo $base['estado']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="date-info">
                                                <?php echo date('d/m/Y H:i', strtotime($base['fecha_creacion'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-primary" 
                                                        onclick="abrirModalAsignar(<?php echo $base['id']; ?>, '<?php echo htmlspecialchars($base['nombre_base']); ?>', <?php echo $base['asesor_id'] ?: 'null'; ?>)"
                                                        title="Asignar Asesor">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                                <button class="btn btn-sm btn-info" 
                                                        onclick="verDetalles(<?php echo $base['id']; ?>)"
                                                        title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($base['asesor_id']): ?>
                                                    <button class="btn btn-sm btn-warning" 
                                                            onclick="liberarAsesor(<?php echo $base['id']; ?>, '<?php echo htmlspecialchars($base['nombre_base']); ?>')"
                                                            title="Liberar Asesor">
                                                        <i class="fas fa-user-minus"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Asignar Asesor Individual -->
    <div id="asignarModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Asignar Asesor a Base de Datos</h3>
                <span class="close" onclick="cerrarModalAsignar()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formAsignarAsesor">
                    <input type="hidden" id="baseDatosId" name="base_datos_id">
                    
                    <div class="form-group">
                        <label for="baseNombre">Base de Datos:</label>
                        <input type="text" id="baseNombre" class="form-control" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="asesorSelect">Seleccionar Asesor:</label>
                        <select id="asesorSelect" name="asesor_id" class="form-control" required>
                            <option value="">Seleccionar asesor...</option>
                            <?php foreach ($asesores as $asesor): ?>
                                <option value="<?php echo $asesor['id']; ?>">
                                    <?php echo htmlspecialchars($asesor['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="asignarClientes" name="asignar_clientes" checked>
                            Asignar todos los clientes de esta base al asesor
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalAsignar()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="asignarAsesor()">
                    <i class="fas fa-user-plus"></i>
                    Asignar Asesor
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Asignar Múltiples -->
    <div id="asignarMultipleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Asignar Múltiples Bases de Datos</h3>
                <span class="close" onclick="cerrarModalAsignarMultiple()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formAsignarMultiple">
                    <div class="form-group">
                        <label>Bases Seleccionadas:</label>
                        <div id="basesSeleccionadas" class="selected-bases">
                            <!-- Se llenará dinámicamente -->
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="asesorMultipleSelect">Seleccionar Asesor:</label>
                        <select id="asesorMultipleSelect" name="asesor_id" class="form-control" required>
                            <option value="">Seleccionar asesor...</option>
                            <?php foreach ($asesores as $asesor): ?>
                                <option value="<?php echo $asesor['id']; ?>">
                                    <?php echo htmlspecialchars($asesor['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="asignarClientesMultiple" name="asignar_clientes" checked>
                            Asignar todos los clientes de las bases seleccionadas al asesor
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalAsignarMultiple()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="asignarMultipleAsesores()">
                    <i class="fas fa-user-plus"></i>
                    Asignar Asesores
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalles -->
    <div id="detallesModal" class="modal">
        <div class="modal-content modal-extra-large">
            <div class="modal-header">
                <h3>Detalles de la Base de Datos</h3>
                <span class="close" onclick="cerrarModalDetalles()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="detallesContent">
                    <!-- Se llenará dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalDetalles()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/coordinador-gestion.js"></script>
</body>
</html>

