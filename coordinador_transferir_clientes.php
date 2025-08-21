<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferir Clientes - Coordinador</title>
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
                <a href="index.php?action=coordinador_dashboard" class="nav-item" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
                <a href="index.php?action=coordinador_cargar_archivo" class="nav-item" title="Cargar Archivo">
                    <i class="fas fa-upload"></i>
                </a>
                <a href="index.php?action=coordinador_tareas" class="nav-item" title="Tareas">
                    <i class="fas fa-tasks"></i>
                </a>
                <a href="index.php?action=coordinador_transferir_clientes" class="nav-item active" title="Transferir Clientes">
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
                    <h1>Transferir Clientes</h1>
                </div>
                <div class="top-bar-right">
                    <span class="user-info">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Coordinador'); ?>
                    </span>
                </div>
            </div>

            <!-- Transfer Options -->
            <div class="transfer-options">
                <div class="option-tabs">
                    <button class="tab-btn active" onclick="cambiarTab('transferir')">
                        <i class="fas fa-exchange-alt"></i> Transferir Cliente
                    </button>
                    <button class="tab-btn" onclick="cambiarTab('liberar')">
                        <i class="fas fa-user-times"></i> Liberar Cliente
                    </button>
                    <button class="tab-btn" onclick="cambiarTab('liberar-todos')">
                        <i class="fas fa-users-slash"></i> Liberar Todos
                    </button>
                </div>
                
                <!-- Transfer Tab -->
                <div id="tab-transferir" class="tab-content active">
                    <div class="transfer-section">
                        <h3>Transferir Cliente a Otro Asesor</h3>
                        <p>Mueve un cliente de un asesor a otro dentro de tu equipo</p>
                        
                        <form id="transferForm" class="transfer-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="asesor_origen">Asesor de Origen:</label>
                                    <select id="asesor_origen" name="asesor_origen" required class="form-control" onchange="cargarClientesAsesor()">
                                        <option value="">-- Seleccionar Asesor --</option>
                                        <?php foreach ($asesores as $asesor): ?>
                                            <option value="<?php echo $asesor['id']; ?>">
                                                <?php echo htmlspecialchars($asesor['nombre_completo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="cliente_id">Cliente a Transferir:</label>
                                    <select id="cliente_id" name="cliente_id" required class="form-control" disabled>
                                        <option value="">-- Seleccionar Cliente --</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="asesor_destino">Asesor de Destino:</label>
                                    <select id="asesor_destino" name="asesor_destino" required class="form-control">
                                        <option value="">-- Seleccionar Asesor --</option>
                                        <?php foreach ($asesores as $asesor): ?>
                                            <option value="<?php echo $asesor['id']; ?>">
                                                <?php echo htmlspecialchars($asesor['nombre_completo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary" id="btnTransferir" disabled>
                                    <i class="fas fa-exchange-alt"></i> Transferir Cliente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Liberar Tab -->
                <div id="tab-liberar" class="tab-content">
                    <div class="liberar-section">
                        <h3>Liberar Cliente</h3>
                        <p>Quita la asignación de un cliente para que esté disponible para reasignar</p>
                        
                        <form id="liberarForm" class="liberar-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="asesor_liberar">Asesor:</label>
                                    <select id="asesor_liberar" name="asesor_liberar" required class="form-control" onchange="cargarClientesAsesorLiberar()">
                                        <option value="">-- Seleccionar Asesor --</option>
                                        <?php foreach ($asesores as $asesor): ?>
                                            <option value="<?php echo $asesor['id']; ?>">
                                                <?php echo htmlspecialchars($asesor['nombre_completo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="cliente_liberar">Cliente a Liberar:</label>
                                    <select id="cliente_liberar" name="cliente_liberar" required class="form-control" disabled>
                                        <option value="">-- Seleccionar Cliente --</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-warning" id="btnLiberar" disabled>
                                    <i class="fas fa-user-times"></i> Liberar Cliente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Liberar Todos Tab -->
                <div id="tab-liberar-todos" class="tab-content">
                    <div class="liberar-todos-section">
                        <h3>Liberar Todos los Clientes de un Asesor</h3>
                        <p>Quita la asignación de todos los clientes de un asesor específico</p>
                        
                        <form id="liberarTodosForm" class="liberar-todos-form">
                            <div class="form-group">
                                <label for="asesor_liberar_todos">Asesor:</label>
                                <select id="asesor_liberar_todos" name="asesor_liberar_todos" required class="form-control">
                                    <option value="">-- Seleccionar Asesor --</option>
                                    <?php foreach ($asesores as $asesor): ?>
                                        <option value="<?php echo $asesor['id']; ?>">
                                            <?php echo htmlspecialchars($asesor['nombre_completo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="warning-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p><strong>Advertencia:</strong> Esta acción liberará TODOS los clientes asignados al asesor seleccionado. 
                                Los clientes quedarán disponibles para reasignar a otros asesores.</p>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-danger" id="btnLiberarTodos">
                                    <i class="fas fa-users-slash"></i> Liberar Todos los Clientes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="alert-container"></div>

    <script>
        function cambiarTab(tabName) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Desactivar todos los botones
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Mostrar tab seleccionado
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Activar botón seleccionado
            event.target.classList.add('active');
        }
        
        function cargarClientesAsesor() {
            const asesorId = document.getElementById('asesor_origen').value;
            const clienteSelect = document.getElementById('cliente_id');
            const btnTransferir = document.getElementById('btnTransferir');
            
            if (!asesorId) {
                clienteSelect.disabled = true;
                clienteSelect.innerHTML = '<option value="">-- Seleccionar Cliente --</option>';
                btnTransferir.disabled = true;
                return;
            }
            
            // Cargar clientes del asesor
            fetch(`index.php?action=coordinador_get_clientes_asesor&asesor_id=${asesorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        clienteSelect.innerHTML = '<option value="">-- Seleccionar Cliente --</option>';
                        data.clientes.forEach(cliente => {
                            const option = document.createElement('option');
                            option.value = cliente.id;
                            option.textContent = `${cliente.nombre_completo} (${cliente.cedula})`;
                            clienteSelect.appendChild(option);
                        });
                        clienteSelect.disabled = false;
                        validarFormularioTransferir();
                    } else {
                        mostrarAlerta(data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarAlerta('Error al cargar clientes del asesor.', 'error');
                });
        }
        
        function cargarClientesAsesorLiberar() {
            const asesorId = document.getElementById('asesor_liberar').value;
            const clienteSelect = document.getElementById('cliente_liberar');
            const btnLiberar = document.getElementById('btnLiberar');
            
            if (!asesorId) {
                clienteSelect.disabled = true;
                clienteSelect.innerHTML = '<option value="">-- Seleccionar Cliente --</option>';
                btnLiberar.disabled = true;
                return;
            }
            
            // Cargar clientes del asesor
            fetch(`index.php?action=coordinador_get_clientes_asesor&asesor_id=${asesorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        clienteSelect.innerHTML = '<option value="">-- Seleccionar Cliente --</option>';
                        data.clientes.forEach(cliente => {
                            const option = document.createElement('option');
                            option.value = cliente.id;
                            option.textContent = `${cliente.nombre_completo} (${cliente.cedula})`;
                            clienteSelect.appendChild(option);
                        });
                        clienteSelect.disabled = false;
                        validarFormularioLiberar();
                    } else {
                        mostrarAlerta(data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarAlerta('Error al cargar clientes del asesor.', 'error');
                });
        }
        
        function validarFormularioTransferir() {
            const asesorOrigen = document.getElementById('asesor_origen').value;
            const cliente = document.getElementById('cliente_id').value;
            const asesorDestino = document.getElementById('asesor_destino').value;
            const btnTransferir = document.getElementById('btnTransferir');
            
            btnTransferir.disabled = !(asesorOrigen && cliente && asesorDestino && asesorOrigen !== asesorDestino);
        }
        
        function validarFormularioLiberar() {
            const asesor = document.getElementById('asesor_liberar').value;
            const cliente = document.getElementById('cliente_liberar').value;
            const btnLiberar = document.getElementById('btnLiberar');
            
            btnLiberar.disabled = !(asesor && cliente);
        }
        
        // Event listeners para validación
        document.getElementById('asesor_origen').addEventListener('change', validarFormularioTransferir);
        document.getElementById('cliente_id').addEventListener('change', validarFormularioTransferir);
        document.getElementById('asesor_destino').addEventListener('change', validarFormularioTransferir);
        document.getElementById('asesor_liberar').addEventListener('change', validarFormularioLiberar);
        document.getElementById('cliente_liberar').addEventListener('change', validarFormularioLiberar);
        
        // Form submission handlers
        document.getElementById('transferForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                cliente_id: formData.get('cliente_id'),
                nuevo_asesor_id: formData.get('asesor_destino')
            };
            
            fetch('index.php?action=coordinador_transferir_cliente', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta(data.message, 'success');
                    this.reset();
                    document.getElementById('cliente_id').innerHTML = '<option value="">-- Seleccionar Cliente --</option>';
                    document.getElementById('cliente_id').disabled = true;
                    document.getElementById('btnTransferir').disabled = true;
                } else {
                    mostrarAlerta(data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error al procesar la solicitud.', 'error');
            });
        });
        
        document.getElementById('liberarForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                cliente_id: formData.get('cliente_liberar')
            };
            
            fetch('index.php?action=coordinador_liberar_cliente', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta(data.message, 'success');
                    this.reset();
                    document.getElementById('cliente_liberar').innerHTML = '<option value="">-- Seleccionar Cliente --</option>';
                    document.getElementById('cliente_liberar').disabled = true;
                    document.getElementById('btnLiberar').disabled = true;
                } else {
                    mostrarAlerta(data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error al procesar la solicitud.', 'error');
            });
        });
        
        document.getElementById('liberarTodosForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!confirm('¿Estás seguro de que quieres liberar TODOS los clientes de este asesor? Esta acción no se puede deshacer.')) {
                return;
            }
            
            const formData = new FormData(this);
            const data = {
                asesor_id: formData.get('asesor_liberar_todos')
            };
            
            fetch('index.php?action=coordinador_liberar_todos_clientes_asesor', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta(data.message, 'success');
                    this.reset();
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
