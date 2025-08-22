<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tareas - Coordinador</title>
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-content-area {
            padding: 20px;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 4px solid #007bff;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
            font-weight: 700;
            color: #007bff;
        }
        
        .stat-card p {
            margin: 0;
            color: #7f8c8d;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .assignment-table-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .table-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.5rem;
        }
        
        .assignment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .assignment-table th,
        .assignment-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .assignment-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .assignment-table tr:hover {
            background: #f8f9fa;
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
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading i {
            font-size: 2em;
            color: #007bff;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-info {
            background: #17a2b8;
            color: white;
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

            <div class="main-content-area">
                <!-- Resumen de Estadísticas -->
                <div class="stats-overview">
                    <div class="stat-card">
                        <h3><?php echo number_format($totalClientesCargados); ?></h3>
                        <p>Total Clientes Cargados</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3><?php echo number_format($totalClientesAsignados); ?></h3>
                        <p>Clientes Asignados</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3><?php echo number_format($totalClientesDisponibles); ?></h3>
                        <p>Clientes Disponibles</p>
                    </div>
                </div>

                <!-- Tabla de Asignación -->
                <div class="assignment-table-container">
                    <div class="table-header">
                        <h2>📋 Asignar Clientes a Asesores</h2>
                    </div>

                    <!-- Alertas -->
                    <div id="alertSuccess" class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span id="successMessage"></span>
                    </div>
                    
                    <div id="alertError" class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="errorMessage"></span>
                    </div>

                    <!-- Loading -->
                    <div id="loading" class="loading">
                        <i class="fas fa-spinner"></i>
                        <p>Procesando asignación...</p>
                    </div>

                    <table class="assignment-table">
                        <thead>
                            <tr>
                                <th>👤 Asesor</th>
                                <th>📊 Estado</th>
                                <th>📝 Cantidad a Asignar</th>
                                <th>✅ Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($asesores)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <p>No hay asesores disponibles para asignación.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($asesores as $asesor): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($asesor['nombre_completo']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">Activo</span>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="cantidad_<?php echo $asesor['id']; ?>"
                                                   min="1" 
                                                   max="<?php echo $totalClientesDisponibles; ?>"
                                                   placeholder="Cantidad de clientes"
                                                   style="width: 120px;">
                                        </td>
                                        <td>
                                            <button class="btn btn-success" 
                                                    onclick="asignarClientes(<?php echo $asesor['id']; ?>, '<?php echo htmlspecialchars($asesor['nombre_completo']); ?>')"
                                                    id="btn_asignar_<?php echo $asesor['id']; ?>">
                                                <i class="fas fa-user-plus"></i> Asignar
                                            </button>
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

    <script>
        // Variables globales
        let totalClientesDisponibles = <?php echo $totalClientesDisponibles; ?>;
        let asesores = <?php echo json_encode($asesores); ?>;
        
        /**
         * Asignar clientes a un asesor
         */
        function asignarClientes(asesorId, nombreAsesor) {
            const cantidadInput = document.getElementById(`cantidad_${asesorId}`);
            const btnAsignar = document.getElementById(`btn_asignar_${asesorId}`);
            const cantidad = parseInt(cantidadInput.value);
            
            // Validaciones
            if (!cantidad || cantidad <= 0) {
                mostrarAlerta('Por favor ingresa una cantidad válida', 'error');
                return;
            }
            
            if (cantidad > totalClientesDisponibles) {
                mostrarAlerta(`Solo hay ${totalClientesDisponibles} clientes disponibles`, 'error');
                return;
            }
            
            // Confirmar asignación
            if (!confirm(`¿Estás seguro de que quieres asignar ${cantidad} cliente(s) a ${nombreAsesor}?`)) {
                return;
            }
            
            // Deshabilitar botón y mostrar loading
            btnAsignar.disabled = true;
            btnAsignar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Asignando...';
            mostrarLoading(true);
            
            // Preparar datos
            const formData = new FormData();
            formData.append('asesor_id', asesorId);
            formData.append('cantidad', cantidad);
            
            // Realizar asignación
            fetch('index.php?action=coordinador_asignar_clientes', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    mostrarAlerta(`✅ Se asignaron exitosamente ${cantidad} cliente(s) a ${nombreAsesor}`, 'success');
                    
                    // Actualizar contadores
                    totalClientesDisponibles -= cantidad;
                    actualizarContadores();
                    
                    // Limpiar campo
                    cantidadInput.value = '';
                    
                    // Verificar si quedan clientes disponibles
                    if (totalClientesDisponibles <= 0) {
                        mostrarAlerta('🎉 ¡Todos los clientes han sido asignados!', 'success');
                        deshabilitarAsignaciones();
                    }
                    
                } else {
                    mostrarAlerta(`❌ Error: ${data.error || 'Error desconocido'}`, 'error');
                }
            })
            .catch(error => {
                console.error('Error en asignación:', error);
                mostrarAlerta('❌ Error de conexión', 'error');
            })
            .finally(() => {
                // Restaurar botón
                btnAsignar.disabled = false;
                btnAsignar.innerHTML = '<i class="fas fa-user-plus"></i> Asignar';
                mostrarLoading(false);
            });
        }
        
        /**
         * Mostrar alerta
         */
        function mostrarAlerta(mensaje, tipo) {
            const alertSuccess = document.getElementById('alertSuccess');
            const alertError = document.getElementById('alertError');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            
            if (tipo === 'success') {
                successMessage.textContent = mensaje;
                alertSuccess.style.display = 'block';
                alertError.style.display = 'none';
                
                // Ocultar después de 5 segundos
                setTimeout(() => {
                    alertSuccess.style.display = 'none';
                }, 5000);
                
            } else {
                errorMessage.textContent = mensaje;
                alertError.style.display = 'block';
                alertSuccess.style.display = 'none';
                
                // Ocultar después de 5 segundos
                setTimeout(() => {
                    alertError.style.display = 'none';
                }, 5000);
            }
        }
        
        /**
         * Mostrar/ocultar loading
         */
        function mostrarLoading(mostrar) {
            const loading = document.getElementById('loading');
            loading.style.display = mostrar ? 'block' : 'none';
        }
        
        /**
         * Actualizar contadores en la interfaz
         */
        function actualizarContadores() {
            // Actualizar el contador de clientes disponibles
            const statCard = document.querySelector('.stats-overview .stat-card:last-child h3');
            if (statCard) {
                statCard.textContent = totalClientesDisponibles;
            }
            
            // Actualizar el máximo en los inputs
            asesores.forEach(asesor => {
                const input = document.getElementById(`cantidad_${asesor.id}`);
                if (input) {
                    input.max = totalClientesDisponibles;
                }
            });
        }
        
        /**
         * Deshabilitar asignaciones cuando no hay clientes
         */
        function deshabilitarAsignaciones() {
            asesores.forEach(asesor => {
                const input = document.getElementById(`cantidad_${asesor.id}`);
                const btn = document.getElementById(`btn_asignar_${asesor.id}`);
                
                if (input) input.disabled = true;
                if (btn) btn.disabled = true;
            });
        }
        
        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Vista de gestión de tareas inicializada');
            
            // Verificar si hay clientes disponibles
            if (totalClientesDisponibles <= 0) {
                mostrarAlerta('🎉 ¡Todos los clientes han sido asignados!', 'success');
                deshabilitarAsignaciones();
            }
        });
    </script>
</body>
</html>
