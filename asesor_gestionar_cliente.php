<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Cliente - Asesor</title>
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
                <a href="index.php?action=asesor_clientes" class="nav-item" title="Clientes">
                    <i class="fas fa-users"></i>
                </a>
                <a href="index.php?action=asesor_gestionar_cliente" class="nav-item active" title="Gestionar Clientes">
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
                    <h1>Gestionar Cliente</h1>
                    <p>Gestiona la llamada del cliente</p>
                </div>
                <div class="top-bar-right">
                    <a href="index.php?action=asesor_clientes" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Volver a Clientes
                    </a>
                </div>
            </div>

            <!-- Información del cliente -->
            <div class="client-info-section">
                <div class="client-card-large">
                    <div class="client-avatar-large">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="client-details">
                        <h2><?php echo htmlspecialchars($cliente['nombre_completo'] ?? $cliente['nombre']); ?></h2>
                        <div class="client-info-grid">
                            <div class="info-item">
                                <i class="fas fa-id-card"></i>
                                <span><strong>Cédula:</strong> <?php echo htmlspecialchars($cliente['cedula']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-phone"></i>
                                <span><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['numero_telefono'] ?? $cliente['telefono']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <span><strong>Estado:</strong> <?php echo ucfirst(str_replace('_', ' ', $cliente['estado'] ?? 'no_gestionado')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de gestión -->
            <div class="management-section">
                <div class="management-container">
                    <!-- Columna izquierda: Instrucciones -->
                    <div class="management-instructions">
                        <h3>Instrucciones de Gestión</h3>
                        <div class="instruction-steps">
                            <div class="step">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h4>Selecciona el tipo de contacto</h4>
                                    <p>Elige si el cliente fue contactado o no</p>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h4>Especifica el resultado</h4>
                                    <p>Selecciona la opción más apropiada</p>
                                </div>
                            </div>
                            <div class="step">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h4>Agrega observaciones</h4>
                                    <p>Mínimo 10 caracteres para registrar la gestión</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="important-note">
                            <i class="fas fa-info-circle"></i>
                            <p><strong>Importante:</strong> Todas las gestiones deben incluir observaciones detalladas para mantener un historial completo del cliente.</p>
                        </div>
                    </div>

                    <!-- Columna derecha: Formulario -->
                    <div class="management-form">
                        <h3>Formulario de Gestión</h3>
                        
                        <form id="gestionForm" onsubmit="procesarGestion(event)">
                            <input type="hidden" name="cliente_id" value="<?php echo $cliente['id']; ?>">
                            
                            <!-- Tipo de contacto -->
                            <div class="form-group">
                                <label for="tipo_contacto">Tipo de Contacto *</label>
                                <select name="tipo_contacto" id="tipo_contacto" required onchange="mostrarOpcionesGestion()">
                                    <option value="">Selecciona una opción</option>
                                    <option value="contactado">Contactado</option>
                                    <option value="no_contactado">No Contactado</option>
                                </select>
                            </div>

                            <!-- Opciones de gestión (se muestran solo si es contactado) -->
                            <div id="opciones_gestion" class="form-group" style="display: none;">
                                <label for="tipo_gestion">Resultado de la Gestión *</label>
                                <select name="tipo_gestion" id="tipo_gestion" onchange="mostrarCamposEspecificos()">
                                    <option value="">Selecciona el resultado</option>
                                    <option value="asignacion_cita">Asignación de Cita</option>
                                    <option value="volver_llamar">Volver a Llamar</option>
                                    <option value="fuera_ciudad">Fuera de la Ciudad</option>
                                    <option value="no_interesa">No le Interesa</option>
                                </select>
                            </div>

                            <!-- Opciones de no contacto (se muestran solo si es no_contactado) -->
                            <div id="opciones_no_contacto" class="form-group" style="display: none;">
                                <label for="motivo_no_contacto">Motivo de No Contacto *</label>
                                <select name="motivo_no_contacto" id="motivo_no_contacto">
                                    <option value="">Selecciona el motivo</option>
                                    <option value="no_contesta">No Contesta</option>
                                    <option value="buzon_voz">Buzón de Voz</option>
                                    <option value="ocupado">Ocupado</option>
                                    <option value="fuera_servicio">Fuera de Servicio</option>
                                    <option value="numero_incorrecto">Número Incorrecto</option>
                                    <option value="no_disponible">No Disponible</option>
                                </select>
                            </div>

                            <!-- Campos específicos para asignación de cita -->
                            <div id="campos_cita" class="form-group" style="display: none;">
                                <div class="form-row">
                                    <div class="form-col">
                                        <label for="fecha_cita">Fecha de Cita *</label>
                                        <input type="date" name="fecha_cita" id="fecha_cita" min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="form-col">
                                        <label for="hora_cita">Hora de Cita *</label>
                                        <input type="time" name="hora_cita" id="hora_cita">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="lugar_cita">Lugar de Cita *</label>
                                    <input type="text" name="lugar_cita" id="lugar_cita" placeholder="Ej: Consultorio Principal">
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-col">
                                        <label for="edad_paciente">Edad del Paciente</label>
                                        <input type="number" name="edad_paciente" id="edad_paciente" min="0" max="120">
                                    </div>
                                    <div class="form-col">
                                        <label for="ocupacion">Ocupación</label>
                                        <input type="text" name="ocupacion" id="ocupacion" placeholder="Ej: Estudiante, Empleado">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="patologia">Patología</label>
                                    <input type="text" name="patologia" id="patologia" placeholder="Descripción de la patología">
                                </div>
                                
                                <div class="form-group">
                                    <label for="regimen_salud">Régimen de Salud</label>
                                    <select name="regimen_salud" id="regimen_salud">
                                        <option value="">Selecciona una opción</option>
                                        <option value="contributivo">Contributivo</option>
                                        <option value="subsidiado">Subsidiado</option>
                                        <option value="vinculado">Vinculado</option>
                                        <option value="particular">Particular</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Campos para volver a llamar -->
                            <div id="campos_volver_llamar" class="form-group" style="display: none;">
                                <div class="form-row">
                                    <div class="form-col">
                                        <label for="fecha_proximo_contacto">Fecha para Llamar *</label>
                                        <input type="date" name="fecha_proximo_contacto" id="fecha_proximo_contacto" required>
                                        <small class="form-text">Solo fechas futuras (De lunes a sábado)</small>
                                    </div>
                                    <div class="form-col">
                                        <label for="hora_proximo_contacto">Hora para Llamar *</label>
                                        <input type="time" name="hora_proximo_contacto" id="hora_proximo_contacto" min="07:30" max="18:00" required>
                                        <small class="form-text">Horario: 7:30 AM - 6:00 PM</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="form-group">
                                <label for="observaciones">Observaciones *</label>
                                <textarea name="observaciones" id="observaciones" rows="4" placeholder="Describe detalladamente el resultado de la gestión (mínimo 10 caracteres)" required minlength="10"></textarea>
                                <div class="char-counter">
                                    <span id="char_count">0</span>/<span id="char_limit">10</span> caracteres
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="form-actions">
                                <button type="button" class="btn-secondary" onclick="window.location.href='index.php?action=asesor_clientes'">
                                    <i class="fas fa-times"></i>
                                    Cancelar
                                </button>
                                                        <button type="submit" class="btn-primary" id="btnSubmit">
                            <i class="fas fa-save"></i>
                            Guardar Gestión
                        </button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Historial de gestiones -->
            <?php if (!empty($historialGestion)): ?>
            <div class="history-section">
                <h3>📋 Historial de Gestiones</h3>
                <p class="history-subtitle">Registro completo de todas las interacciones con este cliente</p>
                
                <div class="history-list">
                    <?php foreach ($historialGestion as $gestion): ?>
                        <div class="history-item">
                            <div class="history-icon">
                                <?php if ($gestion['tipo_gestion'] === 'asignacion_cita'): ?>
                                    <i class="fas fa-calendar-check" style="color: #28a745;"></i>
                                <?php elseif ($gestion['tipo_gestion'] === 'volver_llamar'): ?>
                                    <i class="fas fa-phone-volume" style="color: #ffc107;"></i>
                                <?php elseif ($gestion['tipo_gestion'] === 'fuera_ciudad'): ?>
                                    <i class="fas fa-map-marker-alt" style="color: #6c757d;"></i>
                                <?php elseif ($gestion['tipo_gestion'] === 'no_interesa'): ?>
                                    <i class="fas fa-times-circle" style="color: #dc3545;"></i>
                                <?php elseif ($gestion['tipo_contacto'] === 'no_contactado'): ?>
                                    <i class="fas fa-phone-slash" style="color: #6c757d;"></i>
                                <?php else: ?>
                                    <i class="fas fa-phone" style="color: #007bff;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="history-content">
                                <div class="history-header">
                                    <h4><?php echo htmlspecialchars($gestion['estado_gestion_formateado'] ?? 'Gestión'); ?></h4>
                                    <span class="history-date"><?php echo date('d/m/Y H:i', strtotime($gestion['fecha_gestion'])); ?></span>
                                </div>
                                <div class="history-details">
                                    <p><strong>Tipo de Contacto:</strong> <?php echo ucfirst(str_replace('_', ' ', $gestion['tipo_contacto'])); ?></p>
                                    <?php if (!empty($gestion['tipo_gestion'])): ?>
                                        <p><strong>Resultado:</strong> <?php echo ucfirst(str_replace('_', ' ', $gestion['tipo_gestion'])); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Observaciones:</strong> <?php echo htmlspecialchars($gestion['observaciones']); ?></p>
                                    <p><strong>Asesor:</strong> <?php echo htmlspecialchars($gestion['asesor_nombre'] ?? 'N/A'); ?></p>
                                </div>
                                
                                <!-- Botón de detalles solo para gestiones específicas -->
                                <?php if (in_array($gestion['tipo_gestion'], ['asignacion_cita', 'volver_llamar'])): ?>
                                    <div class="history-actions">
                                        <button type="button" class="btn-details" onclick="mostrarDetallesGestion(<?php echo $gestion['id']; ?>, '<?php echo $gestion['tipo_gestion']; ?>')">
                                            <i class="fas fa-eye"></i>
                                            Ver Detalles
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="history-section">
                <div class="no-history">
                    <i class="fas fa-history" style="font-size: 3em; color: #6c757d; margin-bottom: 15px;"></i>
                    <h3>Sin Historial</h3>
                    <p>Este cliente aún no tiene gestiones registradas. ¡Sé el primero en crear una!</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de éxito/error -->
    <div id="alertModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Resultado</h3>
                <span class="close" onclick="cerrarModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p id="modalMessage"></p>
            </div>
            <div class="modal-footer">
                <button class="btn-primary" onclick="cerrarModal()">Aceptar</button>
            </div>
        </div>
    </div>

    <!-- Modal de detalles de gestión -->
    <div id="detallesModal" class="modal" style="display: none;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3 id="detallesTitle">Detalles de la Gestión</h3>
                <span class="close" onclick="cerrarDetallesModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="detallesContent">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="cerrarDetallesModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <script src="assets/js/asesor-gestionar-cliente.js"></script>
</body>
</html>
