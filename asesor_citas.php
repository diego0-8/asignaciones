<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas - Asesor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/asesor-dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <h1><i class="fas fa-calendar-check"></i> Mis Citas</h1>
                <div class="header-actions">
                    <a href="index.php?action=dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                    <a href="index.php?action=asesor_clientes" class="btn btn-primary">
                        <i class="fas fa-users"></i> Ver Clientes
                    </a>
                </div>
            </div>
        </header>

        <!-- Filtros -->
        <div class="filters-section">
            <form method="GET" action="" class="filters-form">
                <input type="hidden" name="action" value="asesor_obtener_citas">
                
                <div class="filter-group">
                    <label for="estado_filter">Estado de Cita:</label>
                    <select name="estado_filter" id="estado_filter">
                        <option value="">Todos los estados</option>
                        <option value="Agendada" <?= ($estado_filter === 'Agendada') ? 'selected' : '' ?>>Agendada</option>
                        <option value="Confirmada" <?= ($estado_filter === 'Confirmada') ? 'selected' : '' ?>>Confirmada</option>
                        <option value="Completada" <?= ($estado_filter === 'Completada') ? 'selected' : '' ?>>Completada</option>
                        <option value="Cancelada" <?= ($estado_filter === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                        <option value="No Asistió" <?= ($estado_filter === 'No Asistió') ? 'selected' : '' ?>>No Asistió</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
            </form>
        </div>

        <!-- Lista de Citas -->
        <div class="citas-container">
            <?php if (empty($citas)): ?>
                <div class="no-citas">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No tienes citas programadas</h3>
                    <p>Cuando crees citas para tus clientes, aparecerán aquí.</p>
                    <a href="index.php?action=asesor_clientes" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Nueva Cita
                    </a>
                </div>
            <?php else: ?>
                <div class="citas-grid">
                    <?php foreach ($citas as $cita): ?>
                        <div class="cita-card <?= strtolower($cita['estado']) ?>">
                            <div class="cita-header">
                                <span class="cita-estado <?= strtolower($cita['estado']) ?>">
                                    <?= $cita['estado'] ?>
                                </span>
                                <span class="cita-tipo">
                                    <i class="fas fa-stethoscope"></i>
                                    <?= $cita['tipo_cita'] ?>
                                </span>
                            </div>
                            
                            <div class="cita-body">
                                <div class="cliente-info">
                                    <h4><?= htmlspecialchars($cita['cliente_nombre']) ?></h4>
                                    <p><i class="fas fa-id-card"></i> <?= htmlspecialchars($cita['cedula']) ?></p>
                                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($cita['telefono']) ?></p>
                                </div>
                                
                                <div class="cita-fecha">
                                    <i class="fas fa-calendar"></i>
                                    <strong><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></strong>
                                </div>
                                
                                <div class="cita-hora">
                                    <i class="fas fa-clock"></i>
                                    <strong><?= date('H:i', strtotime($cita['fecha_cita'])) ?></strong>
                                </div>
                                
                                <?php if (!empty($cita['observaciones'])): ?>
                                    <div class="cita-observaciones">
                                        <i class="fas fa-comment"></i>
                                        <p><?= htmlspecialchars($cita['observaciones']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cita-actions">
                                <?php if ($cita['estado'] === 'Agendada'): ?>
                                    <button class="btn btn-success btn-sm" onclick="confirmarCita(<?= $cita['id'] ?>)">
                                        <i class="fas fa-check"></i> Confirmar
                                    </button>
                                    <button class="btn btn-warning btn-sm" onclick="editarCita(<?= $cita['id'] ?>)">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="cancelarCita(<?= $cita['id'] ?>)">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                <?php elseif ($cita['estado'] === 'Confirmada'): ?>
                                    <button class="btn btn-success btn-sm" onclick="completarCita(<?= $cita['id'] ?>)">
                                        <i class="fas fa-check-double"></i> Completar
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="noAsistio(<?= $cita['id'] ?>)">
                                        <i class="fas fa-user-times"></i> No Asistió
                                    </button>
                                <?php endif; ?>
                                
                                <a href="index.php?action=asesor_gestionar_cliente&id=<?= $cita['cliente_id'] ?>" 
                                   class="btn btn-info btn-sm">
                                    <i class="fas fa-user"></i> Ver Cliente
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?action=asesor_obtener_citas&page=<?= $page - 1 ?>&estado_filter=<?= $estado_filter ?>" 
                               class="btn btn-secondary">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>
                        
                        <span class="page-info">
                            Página <?= $page ?> de <?= $total_pages ?>
                        </span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?action=asesor_obtener_citas&page=<?= $page + 1 ?>&estado_filter=<?= $estado_filter ?>" 
                               class="btn btn-secondary">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para editar cita -->
    <div id="editarCitaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Editar Cita</h3>
                <span class="close">&times;</span>
            </div>
            <form id="editarCitaForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_cita_id" name="cita_id">
                    
                    <div class="form-group">
                        <label for="edit_fecha_cita">Fecha y Hora:</label>
                        <input type="datetime-local" id="edit_fecha_cita" name="fecha_cita" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_tipo_cita">Tipo de Cita:</label>
                        <select id="edit_tipo_cita" name="tipo_cita" required>
                            <option value="Consulta">Consulta</option>
                            <option value="Seguimiento">Seguimiento</option>
                            <option value="Renovación">Renovación</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_observaciones">Observaciones:</label>
                        <textarea id="edit_observaciones" name="observaciones" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/asesor-citas.js"></script>
</body>
</html>
