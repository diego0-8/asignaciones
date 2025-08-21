<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Asignación de Personal'; ?> - Sistema de Citas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar izquierdo -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-user-shield"></i>
                <span class="user-role">ADMINISTRADOR</span>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php?action=admin_dashboard" class="nav-item" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
                <a href="index.php?action=list_usuarios" class="nav-item" title="Usuarios">
                    <i class="fas fa-users"></i>
                </a>
                <a href="index.php?action=asignar_personal" class="nav-item active" title="Asignar Personal">
                    <i class="fas fa-user-plus"></i>
                </a>
                <a href="index.php?action=admin_reportes" class="nav-item" title="Reportes">
                    <i class="fas fa-chart-bar"></i>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="index.php?action=admin_cerrar_sesion" class="nav-item logout" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="main-content">
            <!-- Barra superior -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <h1>Asignación de Personal</h1>
                    <p>Gestiona la asignación de asesores a coordinadores</p>
                </div>
                <div class="top-bar-right">
                    <div class="date-time">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('d/m/Y'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Mensajes de éxito/error -->
            <div id="alert-container"></div>

            <!-- Estadísticas generales -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Asesores</h3>
                        <p class="stat-number"><?php echo count($asesores); ?></p>
                        <p class="stat-label">Disponibles</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Coordinadores</h3>
                        <p class="stat-number"><?php echo count($coordinadores); ?></p>
                        <p class="stat-label">Activos</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Asignaciones Activas</h3>
                        <p class="stat-number"><?php echo count($asesoresAsignados); ?></p>
                        <p class="stat-label">Asesores asignados</p>
                    </div>
                </div>
            </div>

            <!-- Sección de asignación -->
            <div class="assignment-section">
                <div class="section-header">
                    <h2>Asignar Asesor a Coordinador</h2>
                    <p>Selecciona un asesor y un coordinador para crear la asignación</p>
                    <button type="button" class="btn-primary" onclick="abrirModalAsignacion()">
                        <i class="fas fa-plus"></i>
                        Nueva Asignación
                    </button>
                </div>
            </div>

            <!-- Sección de asignaciones actuales -->
            <div class="current-assignments-section">
                <div class="section-header">
                    <h2>Asignaciones Actuales</h2>
                    <p>Gestiona las asignaciones existentes de asesores a coordinadores</p>
                </div>

                <?php if (empty($asesoresAsignados)): ?>
                    <div class="no-assignments">
                        <i class="fas fa-users-slash"></i>
                        <h3>No hay asignaciones activas</h3>
                        <p>No hay asesores asignados a coordinadores en este momento.</p>
                    </div>
                <?php else: ?>
                    <div class="assignments-grid">
                        <?php foreach ($asesoresAsignados as $asignacion): ?>
                            <div class="assignment-card">
                                <div class="assignment-header">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="user-info">
                                        <h3><?php echo htmlspecialchars($asignacion['nombre_completo']); ?></h3>
                                        <p class="user-cedula">Cédula: <?php echo htmlspecialchars($asignacion['cedula']); ?></p>
                                        <p class="user-role">
                                            <i class="fas fa-user-tie"></i>
                                            Asesor
                                        </p>
                                    </div>
                                    <div class="assignment-status">
                                        <span class="status-badge assigned">Asignado</span>
                                    </div>
                                </div>
                                
                                <div class="assignment-details">
                                    <div class="coordinator-info">
                                        <h4>Coordinador Asignado:</h4>
                                        <p>
                                            <i class="fas fa-user-shield"></i>
                                            <?php 
                                            $coordinador = array_filter($coordinadores, function($c) use ($asignacion) {
                                                return $c['id'] == $asignacion['coordinador_id'];
                                            });
                                            $coordinador = reset($coordinador);
                                            echo htmlspecialchars($coordinador['nombre_completo'] ?? 'No encontrado');
                                            ?>
                                        </p>
                                    </div>
                                    
                                    <div class="assignment-actions">
                                        <button type="button" 
                                                class="btn-secondary btn-small"
                                                onclick="liberarAsesor(<?php echo $asignacion['id']; ?>, <?php echo $asignacion['coordinador_id']; ?>)">
                                            <i class="fas fa-unlink"></i>
                                            Liberar Asesor
                                        </button>
                                        
                                        <a href="index.php?action=ver_gestion_asesor&id=<?php echo $asignacion['id']; ?>" 
                                           class="btn-primary btn-small">
                                            <i class="fas fa-eye"></i>
                                            Ver Gestión
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sección de asesores disponibles -->
            <div class="available-asesores-section">
                <div class="section-header">
                    <h2>Asesores Disponibles</h2>
                    <p>Asesores que no están asignados a ningún coordinador</p>
                </div>

                <?php 
                $asesoresDisponibles = array_filter($asesores, function($asesor) use ($asesoresAsignados) {
                    return !in_array($asesor['id'], array_column($asesoresAsignados, 'id'));
                });
                ?>

                <?php if (empty($asesoresDisponibles)): ?>
                    <div class="no-available">
                        <i class="fas fa-users-slash"></i>
                        <h3>No hay asesores disponibles</h3>
                        <p>Todos los asesores están asignados a coordinadores.</p>
                    </div>
                <?php else: ?>
                    <div class="available-asesores-grid">
                        <?php foreach ($asesoresDisponibles as $asesor): ?>
                            <div class="asesor-card">
                                <div class="asesor-header">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="user-info">
                                        <h3><?php echo htmlspecialchars($asesor['nombre_completo']); ?></h3>
                                        <p class="user-cedula">Cédula: <?php echo htmlspecialchars($asesor['cedula']); ?></p>
                                        <p class="user-role">
                                            <i class="fas fa-user-tie"></i>
                                            Asesor
                                        </p>
                                    </div>
                                    <div class="asesor-status">
                                        <span class="status-badge available">Disponible</span>
                                    </div>
                                </div>
                                
                                <div class="asesor-actions">
                                    <a href="index.php?action=ver_gestion_asesor&id=<?php echo $asesor['id']; ?>" 
                                       class="btn-primary btn-small">
                                        <i class="fas fa-eye"></i>
                                        Ver Gestión
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de Asignación -->
    <div id="modalAsignacion" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Asignar Asesor a Coordinador</h2>
                <span class="close" onclick="cerrarModalAsignacion()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formAsignacion" class="assignment-form">
                    <div class="form-group">
                        <label for="asesor_id">Asesor *</label>
                        <select name="asesor_id" id="asesor_id" required>
                            <option value="">Selecciona un asesor</option>
                            <?php foreach ($asesores as $asesor): ?>
                                <?php 
                                // Solo mostrar asesores no asignados
                                $estaAsignado = in_array($asesor['id'], array_column($asesoresAsignados, 'id'));
                                if (!$estaAsignado):
                                ?>
                                <option value="<?php echo $asesor['id']; ?>">
                                    <?php echo htmlspecialchars($asesor['nombre_completo']); ?> 
                                    (<?php echo htmlspecialchars($asesor['cedula']); ?>)
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="coordinador_id">Coordinador *</label>
                        <select name="coordinador_id" id="coordinador_id" required>
                            <option value="">Selecciona un coordinador</option>
                            <?php foreach ($coordinadores as $coordinador): ?>
                                <option value="<?php echo $coordinador['id']; ?>">
                                    <?php echo htmlspecialchars($coordinador['nombre_completo']); ?> 
                                    (<?php echo htmlspecialchars($coordinador['cedula']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="cerrarModalAsignacion()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-link"></i>
                            Asignar Asesor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Función para mostrar alertas
        function mostrarAlerta(mensaje, tipo = 'success') {
            const alertContainer = document.getElementById('alert-container');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${mensaje}
            `;
            
            alertContainer.appendChild(alertDiv);
            
            // Auto-hide después de 5 segundos
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 300);
            }, 5000);
        }

        // Funciones del modal
        function abrirModalAsignacion() {
            document.getElementById('modalAsignacion').style.display = 'block';
        }

        function cerrarModalAsignacion() {
            document.getElementById('modalAsignacion').style.display = 'none';
            document.getElementById('formAsignacion').reset();
        }

        // Cerrar modal al hacer clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modalAsignacion');
            if (event.target === modal) {
                cerrarModalAsignacion();
            }
        }

        // Manejar envío del formulario de asignación
        document.getElementById('formAsignacion').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const asesorId = document.getElementById('asesor_id').value;
            const coordinadorId = document.getElementById('coordinador_id').value;
            
            if (!asesorId || !coordinadorId) {
                mostrarAlerta('Por favor selecciona tanto el asesor como el coordinador', 'error');
                return false;
            }
            
            if (asesorId === coordinadorId) {
                mostrarAlerta('El asesor y el coordinador no pueden ser la misma persona', 'error');
                return false;
            }

            // Realizar asignación vía AJAX
            asignarAsesor(asesorId, coordinadorId);
        });

        // Función para asignar asesor
        function asignarAsesor(asesorId, coordinadorId) {
            const formData = new FormData();
            formData.append('asesor_id', asesorId);
            formData.append('coordinador_id', coordinadorId);
            formData.append('asignar_asesor', '1');

            fetch('index.php?action=asignar_asesor', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Asesor asignado exitosamente al coordinador');
                    cerrarModalAsignacion();
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    mostrarAlerta(data.error || 'Error al asignar asesor', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión al asignar asesor', 'error');
            });
        }

        // Función para liberar asesor
        function liberarAsesor(asesorId, coordinadorId) {
            if (!confirm('¿Estás seguro de que quieres liberar este asesor del coordinador?')) {
                return;
            }

            fetch(`index.php?action=liberar_asesor&asesor_id=${asesorId}&coordinador_id=${coordinadorId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Asesor liberado exitosamente del coordinador');
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    mostrarAlerta(data.error || 'Error al liberar asesor', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error de conexión al liberar asesor', 'error');
            });
        }
    </script>
</body>
</html>
