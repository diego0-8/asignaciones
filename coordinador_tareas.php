<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tareas - Coordinador</title>
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Layout horizontal para gestión de tareas */
        .main-content-area {
            display: grid;
            grid-template-columns: 1fr 2fr; /* 4/8 ratio */
            gap: 30px;
            margin-top: 20px;
        }
        
        .left-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .stats-summary {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .summary-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        
        .summary-content h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .summary-content p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .assignment-form-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .section-header {
            margin-bottom: 20px;
        }
        
        .section-header h2 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 1.3rem;
        }
        
        .section-header p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .assignment-form h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 1.1rem;
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
            padding: 10px 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 0.8rem;
            color: #7f8c8d;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .right-column {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .clients-section h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 1.2rem;
        }
        
        .clients-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .client-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .client-item:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }
        
        .client-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .client-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .client-details {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        .btn-outline-primary {
            background: transparent;
            color: #007bff;
            border: 2px solid #007bff;
        }
        
        .btn-outline-primary:hover {
            background: #007bff;
            color: white;
        }
        
        .more-clients {
            text-align: center;
            padding: 15px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .no-data-message {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        
        .no-data-message i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .no-data-message p {
            margin: 0;
            font-size: 1.1rem;
        }
        
        .selected-clients {
            min-height: 40px;
            padding: 10px;
            background: #f8f9fa;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
        }
        
        .no-selection {
            margin: 0;
            color: #7f8c8d;
            font-style: italic;
            text-align: center;
        }
        
        .selected-clients-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .selected-client-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 6px;
        }
        
        .btn-outline-danger {
            background: transparent;
            color: #dc3545;
            border: 2px solid #dc3545;
        }
        
        .btn-outline-danger:hover {
            background: #dc3545;
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .main-content-area {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .stats-summary {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .stats-summary {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .client-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
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
                <a href="index.php?action=coordinador_tareas" class="nav-item active" title="Tareas">
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
                    <h1>Gestión de Tareas</h1>
                </div>
                <div class="top-bar-right">
                    <span class="user-info">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Coordinador'); ?>
                    </span>
                </div>
            </div>

            <!-- Main Content Area - Layout Horizontal -->
            <div class="main-content-area">
                <!-- Left Column - Statistics and Assignment Form -->
                <div class="left-column">
                    <!-- Statistics Summary -->
                    <div class="stats-summary">
                        <div class="summary-card">
                            <div class="summary-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="summary-content">
                                <h3><?php echo number_format($totalClientesCargados); ?></h3>
                                <p>Total Clientes Cargados</p>
                            </div>
                        </div>
                        
                        <div class="summary-card">
                            <div class="summary-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="summary-content">
                                <h3><?php echo number_format($totalClientesAsignados); ?></h3>
                                <p>Clientes Asignados</p>
                            </div>
                        </div>
                        
                        <div class="summary-card">
                            <div class="summary-icon">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <div class="summary-content">
                                <h3><?php echo number_format($totalClientesDisponibles); ?></h3>
                                <p>Clientes Disponibles</p>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Form -->
                    <?php if (!empty($asesores) && !empty($clientesDisponibles)): ?>
                        <div class="assignment-form-section">
                            <div class="section-header">
                                <h2>Asignar Clientes a Asesores</h2>
                                <p>Distribuye los clientes disponibles entre los asesores del equipo</p>
                            </div>
                            
                            <div class="assignment-form">
                                <h3>Asignar a Asesor</h3>
                                <form id="assignmentForm">
                                    <div class="form-group">
                                        <label for="asesor_id">Seleccionar Asesor:</label>
                                        <select id="asesor_id" name="asesor_id" required class="form-control">
                                            <option value="">-- Seleccionar Asesor --</option>
                                            <?php foreach ($asesores as $asesor): ?>
                                                <option value="<?php echo $asesor['id']; ?>">
                                                    <?php echo htmlspecialchars($asesor['nombre_completo']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cantidad_clientes">Cantidad de Clientes:</label>
                                        <input type="number" id="cantidad_clientes" name="cantidad_clientes" 
                                               min="1" max="<?php echo count($clientesDisponibles); ?>" 
                                               value="1" class="form-control">
                                        <small class="form-text">Máximo: <?php echo count($clientesDisponibles); ?> clientes</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="clientes_seleccionados">Clientes Seleccionados:</label>
                                        <div id="clientesSeleccionados" class="selected-clients">
                                            <p class="no-selection">Ningún cliente seleccionado</p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary" id="btnAsignar" disabled>
                                            <i class="fas fa-user-plus"></i> Asignar Clientes
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="limpiarSeleccion()">
                                            <i class="fas fa-undo"></i> Limpiar Selección
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column - Available Clients -->
                <div class="right-column">
                    <?php if (empty($asesores)): ?>
                        <div class="no-data-message">
                            <i class="fas fa-info-circle"></i>
                            <p>No hay asesores asignados a tu coordinación. Contacta al administrador.</p>
                        </div>
                    <?php elseif (empty($clientesDisponibles)): ?>
                        <div class="no-data-message">
                            <i class="fas fa-check-circle"></i>
                            <p>Todos los clientes han sido asignados. ¡Excelente trabajo!</p>
                        </div>
                    <?php else: ?>
                        <div class="clients-section">
                            <h3>Clientes Disponibles para Asignar</h3>
                            <div class="clients-list">
                                <?php foreach (array_slice($clientesDisponibles, 0, 10) as $cliente): ?>
                                    <div class="client-item" data-cliente-id="<?php echo $cliente['id']; ?>">
                                        <div class="client-info">
                                            <span class="client-name"><?php echo htmlspecialchars($cliente['nombre_completo']); ?></span>
                                            <span class="client-details">
                                                Cédula: <?php echo htmlspecialchars($cliente['cedula']); ?> | 
                                                Tel: <?php echo htmlspecialchars($cliente['telefono']); ?>
                                            </span>
                                        </div>
                                        <div class="client-actions">
                                            <button class="btn btn-sm btn-outline-primary" onclick="seleccionarCliente(<?php echo $cliente['id']; ?>)">
                                                <i class="fas fa-check"></i> Seleccionar
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($clientesDisponibles) > 10): ?>
                                    <div class="more-clients">
                                        <p>Y <?php echo count($clientesDisponibles) - 10; ?> clientes más...</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="alert-container"></div>

    <script>
        // Variables globales
        let clientesSeleccionados = [];
        const totalClientesDisponibles = <?php echo count($clientesDisponibles); ?>;
        
        function seleccionarCliente(clienteId) {
            if (clientesSeleccionados.includes(clienteId)) {
                // Deseleccionar
                clientesSeleccionados = clientesSeleccionados.filter(id => id !== clienteId);
            } else {
                // Seleccionar
                clientesSeleccionados.push(clienteId);
            }
            
            actualizarInterfaz();
        }
        
        function actualizarInterfaz() {
            const cantidadInput = document.getElementById('cantidad_clientes');
            const btnAsignar = document.getElementById('btnAsignar');
            const clientesSeleccionadosDiv = document.getElementById('clientesSeleccionados');
            
            // Actualizar cantidad máxima
            cantidadInput.max = clientesSeleccionados.length;
            
            // Habilitar/deshabilitar botón
            btnAsignar.disabled = clientesSeleccionados.length === 0;
            
            // Mostrar clientes seleccionados
            if (clientesSeleccionados.length === 0) {
                clientesSeleccionadosDiv.innerHTML = '<p class="no-selection">Ningún cliente seleccionado</p>';
            } else {
                let html = '<div class="selected-clients-list">';
                clientesSeleccionados.forEach(id => {
                    const cliente = <?php echo json_encode($clientesDisponibles); ?>.find(c => c.id == id);
                    if (cliente) {
                        html += `
                            <div class="selected-client-item">
                                <span>${cliente.nombre_completo} (${cliente.cedula})</span>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="seleccionarCliente(${id})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                    }
                });
                html += '</div>';
                clientesSeleccionadosDiv.innerHTML = html;
            }
            
            // Ajustar cantidad si es necesario
            if (parseInt(cantidadInput.value) > clientesSeleccionados.length) {
                cantidadInput.value = clientesSeleccionados.length;
            }
        }
        
        function limpiarSeleccion() {
            clientesSeleccionados = [];
            actualizarInterfaz();
        }
        
        // Form submission
        document.getElementById('assignmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const asesorId = document.getElementById('asesor_id').value;
            const cantidad = parseInt(document.getElementById('cantidad_clientes').value);
            
            if (!asesorId || cantidad <= 0 || cantidad > clientesSeleccionados.length) {
                mostrarAlerta('Por favor, completa todos los campos correctamente.', 'error');
                return;
            }
            
            // Tomar solo la cantidad especificada de clientes
            const clientesAAsignar = clientesSeleccionados.slice(0, cantidad);
            
            // Enviar asignación
            fetch('index.php?action=coordinador_asignar_clientes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'asesor_id': asesorId,
                    'cliente_ids': clientesAAsignar.join(',')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta(data.message, 'success');
                    // Limpiar selección y recargar página después de un momento
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    mostrarAlerta(data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error al procesar la solicitud.', 'error');
            });
        });
        
        function mostrarAlerta(mensaje, tipo) {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo}`;
            alertDiv.innerHTML = `
                <span>${mensaje}</span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            alertContainer.appendChild(alertDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>
