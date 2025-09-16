/**
 * JavaScript para la Gestión de Bases de Datos del Coordinador
 * Maneja la asignación de asesores a bases de datos
 */

// Variables globales
let basesSeleccionadas = [];
let asesorActual = null;

// Inicialización cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando gestión de bases de datos...');
    initializeEventListeners();
    updateStats();
});

/**
 * Inicializar event listeners
 */
function initializeEventListeners() {
    // Cerrar modales al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            cerrarModal(e.target.id);
        }
    });
    
    // Botones de cerrar modal
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('close')) {
            const modal = e.target.closest('.modal');
            if (modal) {
                cerrarModal(modal.id);
            }
        }
    });
}

/**
 * Toggle select all bases
 */
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.base-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedBases();
}

/**
 * Update selected bases list
 */
function updateSelectedBases() {
    const checkboxes = document.querySelectorAll('.base-checkbox:checked');
    basesSeleccionadas = Array.from(checkboxes).map(cb => {
        const row = cb.closest('tr');
        return {
            id: cb.value,
            nombre: row.querySelector('.base-name strong').textContent,
            clientes: row.querySelector('.client-count').textContent
        };
    });
    
    // Actualizar contador en el botón
    const btnMultiple = document.querySelector('[onclick="abrirModalAsignarMultiple()"]');
    if (btnMultiple) {
        const count = basesSeleccionadas.length;
        btnMultiple.innerHTML = `<i class="fas fa-user-plus"></i> Asignar Múltiples (${count})`;
        btnMultiple.disabled = count === 0;
    }
}

// Event listener para checkboxes individuales
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('base-checkbox')) {
        updateSelectedBases();
    }
});

/**
 * Abrir modal para asignar asesor individual
 */
function abrirModalAsignar(baseId, baseNombre, asesorActualId) {
    console.log('Abriendo modal para base:', baseId);
    
    document.getElementById('baseDatosId').value = baseId;
    document.getElementById('baseNombre').value = baseNombre;
    document.getElementById('asesorSelect').value = asesorActualId || '';
    
    document.getElementById('asignarModal').style.display = 'block';
}

/**
 * Cerrar modal de asignación individual
 */
function cerrarModalAsignar() {
    document.getElementById('asignarModal').style.display = 'none';
    document.getElementById('formAsignarAsesor').reset();
}

/**
 * Abrir modal para asignar múltiples bases
 */
function abrirModalAsignarMultiple() {
    if (basesSeleccionadas.length === 0) {
        showAlert('Por favor selecciona al menos una base de datos', 'warning');
        return;
    }
    
    // Mostrar bases seleccionadas
    const container = document.getElementById('basesSeleccionadas');
    container.innerHTML = basesSeleccionadas.map(base => `
        <div class="selected-base-item">
            <i class="fas fa-database"></i>
            <span>${base.nombre}</span>
            <small>(${base.clientes} clientes)</small>
        </div>
    `).join('');
    
    document.getElementById('asignarMultipleModal').style.display = 'block';
}

/**
 * Cerrar modal de asignación múltiple
 */
function cerrarModalAsignarMultiple() {
    document.getElementById('asignarMultipleModal').style.display = 'none';
    document.getElementById('formAsignarMultiple').reset();
}

/**
 * Asignar asesor a base individual
 */
function asignarAsesor() {
    const form = document.getElementById('formAsignarAsesor');
    const formData = new FormData(form);
    
    const asesorId = formData.get('asesor_id');
    const baseId = formData.get('base_datos_id');
    const asignarClientes = formData.get('asignar_clientes') ? '1' : '0';
    
    if (!asesorId) {
        showAlert('Por favor selecciona un asesor', 'warning');
        return;
    }
    
    // Mostrar loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Asignando...';
    btn.disabled = true;
    
    fetch('index.php?action=coordinador_asignar_asesor_base_datos', {
        method: 'POST',
        body: new URLSearchParams({
            base_datos_id: baseId,
            asesor_id: asesorId,
            asignar_clientes: asignarClientes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            cerrarModalAsignar();
            location.reload(); // Recargar para actualizar la tabla
        } else {
            showAlert(data.error || 'Error al asignar asesor', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error de conexión', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

/**
 * Asignar asesor a múltiples bases
 */
function asignarMultipleAsesores() {
    const asesorId = document.getElementById('asesorMultipleSelect').value;
    
    if (!asesorId) {
        showAlert('Por favor selecciona un asesor', 'warning');
        return;
    }
    
    if (basesSeleccionadas.length === 0) {
        showAlert('No hay bases seleccionadas', 'warning');
        return;
    }
    
    // Mostrar loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Asignando...';
    btn.disabled = true;
    
    const asignarClientes = document.getElementById('asignarClientesMultiple').checked ? '1' : '0';
    const baseIds = basesSeleccionadas.map(base => base.id);
    
    fetch('index.php?action=coordinador_asignar_multiple_bases', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            base_ids: baseIds,
            asesor_id: asesorId,
            asignar_clientes: asignarClientes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            cerrarModalAsignarMultiple();
            location.reload(); // Recargar para actualizar la tabla
        } else {
            showAlert(data.error || 'Error al asignar asesores', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error de conexión', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

/**
 * Liberar asesor de una base
 */
function liberarAsesor(baseId, baseNombre) {
    if (!confirm(`¿Estás seguro de que quieres liberar el asesor de la base "${baseNombre}"?`)) {
        return;
    }
    
    fetch('index.php?action=coordinador_liberar_asesor_base', {
        method: 'POST',
        body: new URLSearchParams({
            base_datos_id: baseId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            location.reload();
        } else {
            showAlert(data.error || 'Error al liberar asesor', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error de conexión', 'error');
    });
}

/**
 * Ver detalles de una base
 */
function verDetalles(baseId) {
    // Mostrar loading
    const modal = document.getElementById('detallesModal');
    const content = document.getElementById('detallesContent');
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando detalles...</div>';
    modal.style.display = 'block';
    
    fetch(`index.php?action=coordinador_ver_detalles_base&base_id=${baseId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            content.innerHTML = `
                <div class="base-details">
                    <div class="detail-section">
                        <h4>Información General</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Nombre:</label>
                                <span>${data.base.nombre_base}</span>
                            </div>
                            <div class="detail-item">
                                <label>Descripción:</label>
                                <span>${data.base.descripcion || 'Sin descripción'}</span>
                            </div>
                            <div class="detail-item">
                                <label>Estado:</label>
                                <span class="status-badge status-${data.base.estado.toLowerCase()}">${data.base.estado}</span>
                            </div>
                            <div class="detail-item">
                                <label>Fecha Creación:</label>
                                <span>${data.base.fecha_creacion}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h4>Asignación</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Asesor Asignado:</label>
                                <span>${data.base.asesor_nombre || 'Sin asignar'}</span>
                            </div>
                            <div class="detail-item">
                                <label>Total Clientes:</label>
                                <span>${data.base.total_clientes}</span>
                            </div>
                        </div>
                    </div>
                    
                    ${data.clientes.length > 0 ? `
                    <div class="detail-section">
                        <h4>Clientes (${data.clientes.length})</h4>
                        <div class="clients-table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Cédula</th>
                                        <th>Teléfono</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.clientes.map(cliente => `
                                        <tr>
                                            <td>${cliente.nombre_completo}</td>
                                            <td>${cliente.cedula}</td>
                                            <td>${cliente.telefono}</td>
                                            <td><span class="status-badge status-${cliente.estado_gestion.toLowerCase().replace(' ', '-')}">${cliente.estado_gestion}</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
        } else {
            content.innerHTML = `<div class="alert alert-error">${data.error}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<div class="alert alert-error">Error al cargar los detalles</div>';
    });
}

/**
 * Cerrar modal de detalles
 */
function cerrarModalDetalles() {
    document.getElementById('detallesModal').style.display = 'none';
}

/**
 * Cerrar modal genérico
 */
function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Mostrar alerta
 */
function showAlert(message, type = 'info') {
    // Crear elemento de alerta
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
        ${message}
    `;
    
    // Añadir estilos
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        max-width: 400px;
        animation: slideIn 0.3s ease;
    `;
    
    // Colores según tipo
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };
    alert.style.backgroundColor = colors[type] || colors.info;
    
    // Añadir al DOM
    document.body.appendChild(alert);
    
    // Remover después de 5 segundos
    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 300);
    }, 5000);
}

/**
 * Actualizar estadísticas
 */
function updateStats() {
    // Esta función se puede usar para actualizar estadísticas en tiempo real
    // Por ahora solo se ejecuta al cargar la página
}

// Añadir estilos CSS para las animaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .selected-base-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 8px;
    }
    
    .selected-base-item i {
        color: #007bff;
    }
    
    .base-details {
        padding: 20px;
    }
    
    .detail-section {
        margin-bottom: 30px;
    }
    
    .detail-section h4 {
        color: #2c3e50;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .detail-item label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.9em;
    }
    
    .detail-item span {
        color: #2c3e50;
    }
    
    .clients-table {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 6px;
    }
    
    .clients-table table {
        margin: 0;
    }
    
    .clients-table th,
    .clients-table td {
        padding: 10px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .clients-table th {
        background: #f8f9fa;
        font-weight: 600;
        position: sticky;
        top: 0;
    }
`;
document.head.appendChild(style);

