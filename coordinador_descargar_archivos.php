<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descargar Archivos - Coordinador</title>
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
                <a href="index.php?action=coordinador_transferir_clientes" class="nav-item" title="Transferir Clientes">
                    <i class="fas fa-exchange-alt"></i>
                </a>
                <a href="index.php?action=coordinador_descargar_archivos" class="nav-item active" title="Descargar CSV">
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
                    <h1>Descargar Archivos CSV</h1>
                </div>
                <div class="top-bar-right">
                    <span class="user-info">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Coordinador'); ?>
                    </span>
                </div>
            </div>

            <!-- Export Options - Two Columns -->
            <div class="export-options">
                <div class="export-container">
                    <!-- Left Column - Export Information -->
                    <div class="export-info-column">
                        <div class="export-info-card">
                            <div class="export-info-header">
                                <i class="fas fa-info-circle"></i>
                                <h2>Información del Reporte</h2>
                            </div>
                            
                            <div class="export-info-content">
                                <h3>¿Qué incluye el reporte?</h3>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <i class="fas fa-file-csv"></i>
                                        <div>
                                            <h4>Formato CSV</h4>
                                            <p>Archivo compatible con Excel, Google Sheets y otros programas de hojas de cálculo</p>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-database"></i>
                                        <div>
                                            <h4>Datos Incluidos</h4>
                                            <p>Cédula, nombre, teléfono, asesor asignado, fecha de gestión, tipo de gestión, resultado, comentarios, monto de venta, duración de llamada y próxima fecha</p>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-filter"></i>
                                        <div>
                                            <h4>Filtros Disponibles</h4>
                                            <p>Filtra por rango de fechas para obtener reportes específicos de períodos determinados</p>
                                        </div>
                                    </div>
                                    
                                    <div class="info-item">
                                        <i class="fas fa-users"></i>
                                        <div>
                                            <h4>Alcance del Reporte</h4>
                                            <p>Incluye la gestión de todos los asesores asignados a tu coordinación</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="important-note">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <p><strong>Nota:</strong> El reporte se genera en tiempo real con los datos más actualizados de la base de datos.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Export Form -->
                    <div class="export-form-column">
                        <div class="export-form-card">
                            <div class="export-form-header">
                                <i class="fas fa-download"></i>
                                <h2>Exportar Gestión de Asesores</h2>
                                <p>Descarga la información de gestión de todos los asesores de tu equipo en formato CSV</p>
                            </div>
                            
                            <form id="exportForm" class="export-form">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha de Inicio:</label>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" 
                                           value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>" 
                                           class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha de Fin:</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin" 
                                           value="<?php echo date('Y-m-d'); ?>" 
                                           class="form-control" required>
                                </div>
                                
                                <div class="quick-dates">
                                    <h4>Fechas Rápidas:</h4>
                                    <div class="quick-buttons">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickDate('hoy')">
                                            <i class="fas fa-calendar-day"></i> Hoy
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickDate('semana')">
                                            <i class="fas fa-calendar-week"></i> Esta Semana
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickDate('mes')">
                                            <i class="fas fa-calendar-alt"></i> Este Mes
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setQuickDate('trimestre')">
                                            <i class="fas fa-calendar"></i> Este Trimestre
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-download"></i> Exportar CSV
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer" class="alert-container">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'no_data'): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>
                    <strong>No hay gestiones para mostrar</strong><br>
                    No se encontraron gestiones de asesores en el rango de fechas seleccionado 
                    (<?php echo htmlspecialchars($_GET['fecha_inicio'] ?? ''); ?> a <?php echo htmlspecialchars($_GET['fecha_fin'] ?? ''); ?>).
                    <br><small>Intenta con un rango de fechas más amplio o verifica que los asesores hayan realizado gestiones en ese período.</small>
                </span>
                <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function setQuickDate(tipo) {
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            const hoy = new Date();
            
            switch(tipo) {
                case 'hoy':
                    fechaInicio.value = hoy.toISOString().split('T')[0];
                    fechaFin.value = hoy.toISOString().split('T')[0];
                    break;
                    
                case 'semana':
                    const inicioSemana = new Date(hoy);
                    inicioSemana.setDate(hoy.getDate() - hoy.getDay());
                    fechaInicio.value = inicioSemana.toISOString().split('T')[0];
                    fechaFin.value = hoy.toISOString().split('T')[0];
                    break;
                    
                case 'mes':
                    const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
                    fechaInicio.value = inicioMes.toISOString().split('T')[0];
                    fechaFin.value = hoy.toISOString().split('T')[0];
                    break;
                    
                case 'trimestre':
                    const trimestre = Math.floor(hoy.getMonth() / 3);
                    const inicioTrimestre = new Date(hoy.getFullYear(), trimestre * 3, 1);
                    fechaInicio.value = inicioTrimestre.toISOString().split('T')[0];
                    fechaFin.value = hoy.toISOString().split('T')[0];
                    break;
            }
        }
        
        // Form submission
        document.getElementById('exportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            
            if (!fechaInicio || !fechaFin) {
                mostrarAlerta('Por favor, selecciona ambas fechas.', 'error');
                return;
            }
            
            if (fechaInicio > fechaFin) {
                mostrarAlerta('La fecha de inicio no puede ser mayor que la fecha de fin.', 'error');
                return;
            }
            
            // Crear formulario temporal para enviar por POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?action=coordinador_exportar_gestion';
            
            const inputInicio = document.createElement('input');
            inputInicio.type = 'hidden';
            inputInicio.name = 'fecha_inicio';
            inputInicio.value = fechaInicio;
            
            const inputFin = document.createElement('input');
            inputFin.type = 'hidden';
            inputFin.name = 'fecha_fin';
            inputFin.value = fechaFin;
            
            form.appendChild(inputInicio);
            form.appendChild(inputFin);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
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
        
        // Validar fechas en tiempo real
        document.getElementById('fecha_fin').addEventListener('change', function() {
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = this.value;
            
            if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
                this.setCustomValidity('La fecha de fin debe ser posterior a la fecha de inicio');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
