<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Bases de Datos - Coordinador</title>
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .bases-container {
            padding: 20px;
        }
        
        .bases-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .bases-header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.8rem;
        }
        
        .btn-create {
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-create:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .bases-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .base-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .base-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .base-card-header {
            padding: 20px;
            background: linear-gradient(135deg, #2188ee 0%, #007bff 100%);
            color: white;
            position: relative;
        }
        
        .base-card-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0 0 8px 0;
        }
        
        .base-card-desc {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }
        
        .base-card-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-activa {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }
        
        .status-inactiva {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .base-card-body {
            padding: 20px;
        }
        
        .base-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin: 5px 0 0 0;
        }
        
        .base-asesor {
            margin-bottom: 20px;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }
        
        .base-asesor h4 {
            margin: 0 0 8px 0;
            color: #1976d2;
            font-size: 1rem;
        }
        
        .base-asesor p {
            margin: 0;
            color: #1976d2;
            font-size: 0.9rem;
        }
        
        .base-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-assign {
            background: #007bff;
            color: white;
        }
        
        .btn-assign:hover {
            background: #0056b3;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-edit:hover {
            background: #e0a800;
        }
        
        .btn-deactivate {
            background: #dc3545;
            color: white;
        }
        
        .btn-deactivate:hover {
            background: #c82333;
        }
        
        .btn-upload {
            background: #28a745;
            color: white;
        }
        
        .btn-upload:hover {
            background: #218838;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e1e5e9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #2c3e50;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: #f8f9fa;
            color: #495057;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .form-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e1e5e9;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        .empty-state p {
            margin: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .bases-grid {
                grid-template-columns: 1fr;
            }
            
            .bases-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .base-actions {
                flex-direction: column;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-database"></i>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php?action=coordinador_dashboard" class="nav-item" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
                <a href="index.php?action=coordinador_gestionar_bases" class="nav-item active" title="Gestionar Bases">
                    <i class="fas fa-database"></i>
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
                    <h1>Gestionar Bases de Datos</h1>
                </div>
                <div class="top-bar-right">
                    <span class="user-info">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Coordinador'); ?>
                    </span>
                </div>
            </div>

            <!-- Bases Container -->
            <div class="bases-container">
                <!-- Header -->
                <div class="bases-header">
                    <h1><i class="fas fa-database"></i> Mis Bases de Datos</h1>
                    <button class="btn-create" onclick="abrirModalCrear()">
                        <i class="fas fa-plus"></i>
                        Crear Nueva Base
                    </button>
                </div>

                <!-- Bases Grid -->
                <div class="bases-grid" id="basesGrid">
                    <?php if (empty($basesDatos)): ?>
                        <div class="empty-state">
                            <i class="fas fa-database"></i>
                            <h3>No tienes bases de datos</h3>
                            <p>Crea tu primera base de datos para comenzar a cargar clientes</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($basesDatos as $base): ?>
                            <div class="base-card" data-base-id="<?php echo $base['id']; ?>">
                                <div class="base-card-header">
                                    <h3 class="base-card-title"><?php echo htmlspecialchars($base['nombre_base']); ?></h3>
                                    <p class="base-card-desc"><?php echo htmlspecialchars($base['descripcion'] ?: 'Sin descripción'); ?></p>
                                    <span class="base-card-status status-<?php echo strtolower($base['estado']); ?>">
                                        <?php echo $base['estado']; ?>
                                    </span>
                                </div>
                                
                                <div class="base-card-body">
                                    <!-- Estadísticas -->
                                    <div class="base-stats">
                                        <div class="stat-item">
                                            <p class="stat-value"><?php echo number_format($base['total_clientes_actual']); ?></p>
                                            <p class="stat-label">Total Clientes</p>
                                        </div>
                                        <div class="stat-item">
                                            <p class="stat-value"><?php echo date('d/m/Y', strtotime($base['fecha_creacion'])); ?></p>
                                            <p class="stat-label">Fecha Creación</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Asesor Asignado -->
                                    <div class="base-asesor">
                                        <h4><i class="fas fa-user-tie"></i> Asesor Asignado</h4>
                                        <?php if ($base['asesor_nombre']): ?>
                                            <p><?php echo htmlspecialchars($base['asesor_nombre']); ?></p>
                                        <?php else: ?>
                                            <p style="color: #6c757d; font-style: italic;">Sin asesor asignado</p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Acciones -->
                                    <div class="base-actions">
                                        <button class="btn-action btn-assign" onclick="abrirModalAsignar(<?php echo $base['id']; ?>, '<?php echo htmlspecialchars($base['nombre_base']); ?>', <?php echo $base['asesor_id'] ?: 'null'; ?>)">
                                            <i class="fas fa-user-plus"></i>
                                            Asignar Asesor
                                        </button>
                                        
                                        <button class="btn-action btn-upload" onclick="irACargarArchivo(<?php echo $base['id']; ?>)">
                                            <i class="fas fa-upload"></i>
                                            Cargar Clientes
                                        </button>
                                        
                                        <button class="btn-action btn-edit" onclick="abrirModalEditar(<?php echo $base['id']; ?>, '<?php echo htmlspecialchars($base['nombre_base']); ?>', '<?php echo htmlspecialchars($base['descripcion']); ?>')">
                                            <i class="fas fa-edit"></i>
                                            Editar
                                        </button>
                                        
                                        <?php if ($base['estado'] === 'Activa'): ?>
                                            <button class="btn-action btn-deactivate" onclick="cambiarEstado(<?php echo $base['id']; ?>, 'Inactiva')">
                                                <i class="fas fa-pause"></i>
                                                Desactivar
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-action btn-assign" onclick="cambiarEstado(<?php echo $base['id']; ?>, 'Activa')">
                                                <i class="fas fa-play"></i>
                                                Activar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear Base de Datos -->
    <div id="modalCrear" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> Crear Nueva Base de Datos</h3>
                <button class="modal-close" onclick="cerrarModal('modalCrear')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCrearBase">
                    <div class="form-group">
                        <label for="nombre_base">Nombre de la Base *</label>
                        <input type="text" id="nombre_base" name="nombre_base" class="form-control" required>
                        <small class="form-text">Identifica esta base de datos (ej: "Clientes Enero 2024")</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3" placeholder="Descripción opcional de la base de datos"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="asesor_id">Asignar Asesor (Opcional)</label>
                        <select id="asesor_id" name="asesor_id" class="form-control">
                            <option value="">Seleccionar asesor...</option>
                            <?php foreach ($asesores as $asesor): ?>
                                <option value="<?php echo $asesor['id']; ?>">
                                    <?php echo htmlspecialchars($asesor['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text">Puedes asignar un asesor ahora o después</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalCrear')">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="crearBaseDatos()">Crear Base</button>
            </div>
        </div>
    </div>

    <!-- Modal Asignar Asesor -->
    <div id="modalAsignar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Asignar Asesor</h3>
                <button class="modal-close" onclick="cerrarModal('modalAsignar')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formAsignarAsesor">
                    <input type="hidden" id="base_datos_id_asignar" name="base_datos_id">
                    
                    <div class="form-group">
                        <label for="asesor_id_asignar">Seleccionar Asesor</label>
                        <select id="asesor_id_asignar" name="asesor_id" class="form-control">
                            <option value="">Sin asesor asignado</option>
                            <?php foreach ($asesores as $asesor): ?>
                                <option value="<?php echo $asesor['id']; ?>">
                                    <?php echo htmlspecialchars($asesor['nombre_completo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text">Selecciona un asesor para esta base de datos</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalAsignar')">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="asignarAsesor()">Asignar Asesor</button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Base -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Editar Base de Datos</h3>
                <button class="modal-close" onclick="cerrarModal('modalEditar')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditarBase">
                    <input type="hidden" id="base_datos_id_editar" name="base_datos_id">
                    
                    <div class="form-group">
                        <label for="nombre_base_editar">Nombre de la Base *</label>
                        <input type="text" id="nombre_base_editar" name="nombre_base" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion_editar">Descripción</label>
                        <textarea id="descripcion_editar" name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal('modalEditar')">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="editarBaseDatos()">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let basesDatos = <?php echo json_encode($basesDatos); ?>;
        let asesores = <?php echo json_encode($asesores); ?>;

        // Funciones de modal
        function abrirModalCrear() {
            document.getElementById('modalCrear').style.display = 'block';
            document.getElementById('formCrearBase').reset();
        }

        function abrirModalAsignar(baseId, nombreBase, asesorId) {
            document.getElementById('modalAsignar').style.display = 'block';
            document.getElementById('base_datos_id_asignar').value = baseId;
            document.getElementById('asesor_id_asignar').value = asesorId || '';
            
            // Actualizar título del modal
            document.querySelector('#modalAsignar .modal-header h3').innerHTML = 
                `<i class="fas fa-user-plus"></i> Asignar Asesor - ${nombreBase}`;
        }

        function abrirModalEditar(baseId, nombreBase, descripcion) {
            document.getElementById('modalEditar').style.display = 'block';
            document.getElementById('base_datos_id_editar').value = baseId;
            document.getElementById('nombre_base_editar').value = nombreBase;
            document.getElementById('descripcion_editar').value = descripcion || '';
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Crear base de datos
        function crearBaseDatos() {
            const form = document.getElementById('formCrearBase');
            const formData = new FormData(form);
            
            fetch('index.php?action=crear_base_datos', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Base de datos creada exitosamente', 'success');
                    cerrarModal('modalCrear');
                    location.reload();
                } else {
                    mostrarAlerta(data.error || 'Error al crear la base de datos', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión', 'error');
            });
        }

        // Asignar asesor
        function asignarAsesor() {
            const form = document.getElementById('formAsignarAsesor');
            const formData = new FormData(form);
            
            fetch('index.php?action=asignar_asesor_base_datos', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta(data.message, 'success');
                    cerrarModal('modalAsignar');
                    location.reload();
                } else {
                    mostrarAlerta(data.error || 'Error al asignar asesor', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión', 'error');
            });
        }

        // Editar base de datos
        function editarBaseDatos() {
            const form = document.getElementById('formEditarBase');
            const formData = new FormData(form);
            
            fetch('index.php?action=editar_base_datos', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Base de datos actualizada exitosamente', 'success');
                    cerrarModal('modalEditar');
                    location.reload();
                } else {
                    mostrarAlerta(data.error || 'Error al actualizar la base de datos', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión', 'error');
            });
        }

        // Cambiar estado de base de datos
        function cambiarEstado(baseId, nuevoEstado) {
            const confirmacion = confirm(`¿Estás seguro de que quieres ${nuevoEstado.toLowerCase()} esta base de datos?`);
            if (!confirmacion) return;
            
            const formData = new FormData();
            formData.append('base_datos_id', baseId);
            formData.append('estado', nuevoEstado);
            
            fetch('index.php?action=cambiar_estado_base_datos', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta(data.message, 'success');
                    location.reload();
                } else {
                    mostrarAlerta(data.error || 'Error al cambiar estado', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión', 'error');
            });
        }

        // Ir a cargar archivo con base específica
        function irACargarArchivo(baseId) {
            window.location.href = `index.php?action=coordinador_cargar_archivo&base_id=${baseId}`;
        }

        // Función para mostrar alertas
        function mostrarAlerta(mensaje, tipo) {
            const alertContainer = document.createElement('div');
            alertContainer.className = `alert alert-${tipo}`;
            alertContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                max-width: 400px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInRight 0.3s ease-out;
            `;
            
            if (tipo === 'success') {
                alertContainer.style.background = 'linear-gradient(135deg, #28a745, #20c997)';
            } else if (tipo === 'error') {
                alertContainer.style.background = 'linear-gradient(135deg, #dc3545, #fd7e14)';
            }
            
            alertContainer.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span>${mensaje}</span>
                    <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; margin-left: 10px;">&times;</button>
                </div>
            `;
            
            document.body.appendChild(alertContainer);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alertContainer.parentElement) {
                    alertContainer.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>
