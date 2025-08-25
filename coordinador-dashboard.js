/**
 * JavaScript para el Dashboard del Coordinador
 * Maneja modales, filtros y funcionalidades de gestión de asesores
 */

// Variables globales
let asesorActual = null;
let clientesAsesor = [];
let clientesFiltrados = [];

// ===== FUNCIONES DE MODALES =====

function abrirModalAsesor(asesorId) {
    asesorActual = asesorId;
    cargarDetallesAsesor(asesorId);
    document.getElementById('asesorModal').style.display = 'block';
}

function cerrarModalAsesor() {
    document.getElementById('asesorModal').style.display = 'none';
    asesorActual = null;
    clientesAsesor = [];
    clientesFiltrados = [];
}

function abrirModalTransferir(clienteId, clienteNombre, clienteCedula, asesorActual) {
    document.getElementById('clienteTransferirNombre').textContent = clienteNombre;
    document.getElementById('clienteTransferirCedula').textContent = clienteCedula;
    document.getElementById('clienteTransferirAsesorActual').textContent = asesorActual;
    
    // Cargar asesores disponibles
    cargarAsesoresDisponibles();
    
    document.getElementById('transferirModal').style.display = 'block';
}

function cerrarModalTransferir() {
    document.getElementById('transferirModal').style.display = 'none';
}

// ===== FUNCIONES DE CARGA DE DATOS =====

function cargarDetallesAsesor(asesorId) {
    // Guardar el ID del asesor actual
    asesorActualId = asesorId;
    
    // Mostrar loading
    mostrarLoading();
    
    fetch(`index.php?action=coordinador_obtener_detalles_asesor&asesor_id=${asesorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarDetallesAsesor(data.asesor, data.clientes);
                clientesAsesor = data.clientes;
                clientesFiltrados = [...data.clientes];
                aplicarFiltros();
            } else {
                mostrarAlerta(data.error, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error al cargar detalles del asesor', 'error');
        })
        .finally(() => {
            ocultarLoading();
        });
}

function cargarAsesoresDisponibles() {
    const select = document.getElementById('nuevoAsesor');
    select.innerHTML = '<option value="">Cargando asesores...</option>';
    
    fetch('index.php?action=coordinador_obtener_asesores_disponibles')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                select.innerHTML = '<option value="">Selecciona un asesor...</option>';
                data.asesores.forEach(asesor => {
                    const option = document.createElement('option');
                    option.value = asesor.id;
                    option.textContent = asesor.nombre_completo;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">Error al cargar asesores</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            select.innerHTML = '<option value="">Error al cargar asesores</option>';
        });
}

// ===== FUNCIONES DE FILTRADO =====

function filtrarPorTipificacion() {
    aplicarFiltros();
}

function filtrarPorEstado() {
    aplicarFiltros();
}

function aplicarFiltros() {
    const filtroTipificacion = document.getElementById('filterTipificacion').value;
    const filtroEstado = document.getElementById('filterEstado').value;
    
    clientesFiltrados = clientesAsesor.filter(cliente => {
        let cumpleTipificacion = true;
        let cumpleEstado = true;
        
        // Filtro de tipificación
        if (filtroTipificacion) {
            if (filtroTipificacion === 'contactado') {
                cumpleTipificacion = ['asignacion_cita', 'volver_llamar', 'fuera_ciudad', 'no_interesa'].includes(cliente.tipificacion);
            } else if (filtroTipificacion === 'no_contactado') {
                cumpleTipificacion = ['no_contactado', 'disponible'].includes(cliente.tipificacion);
            } else {
                cumpleTipificacion = cliente.tipificacion === filtroTipificacion;
            }
        }
        
        // Filtro de estado
        if (filtroEstado) {
            cumpleEstado = cliente.estado_gestion === filtroEstado;
        }
        
        return cumpleTipificacion && cumpleEstado;
    });
    
    mostrarClientesFiltrados();
}

function mostrarClientesEnTabla(clientes) {
    const tbody = document.getElementById('clientesTableBody');
    const noClientesMessage = document.getElementById('noClientesMessage');
    
    if (!tbody) {
        console.error('No se encontró el tbody de la tabla de clientes');
        return;
    }
    
    if (clientes.length === 0) {
        tbody.innerHTML = '';
        if (noClientesMessage) noClientesMessage.style.display = 'block';
        return;
    }
    
    if (noClientesMessage) noClientesMessage.style.display = 'none';
    
    tbody.innerHTML = '';
    
    clientes.forEach(cliente => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${cliente.nombre_completo}</td>
            <td>${cliente.cedula}</td>
            <td>${cliente.telefono || 'N/A'}</td>
            <td>
                <span class="badge badge-${getBadgeClass(cliente.estado_gestion)}">
                    ${cliente.estado_gestion}
                </span>
            </td>
            <td>${cliente.ultima_tipificacion || 'N/A'}</td>
            <td>
                <span class="badge badge-${getTipificacionBadgeClass(cliente.ultima_tipificacion)}">
                    ${cliente.ultima_tipificacion || 'Sin tipificación'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="liberarCliente(${cliente.id})" title="Liberar Cliente">
                        <i class="fas fa-unlock"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="transferirCliente(${cliente.id})" title="Transferir Cliente">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function mostrarClientesFiltrados() {
    const tbody = document.getElementById('clientesTableBody');
    const noClientesMessage = document.getElementById('noClientesMessage');
    
    if (clientesFiltrados.length === 0) {
        tbody.innerHTML = '';
        if (noClientesMessage) noClientesMessage.style.display = 'block';
        return;
    }
    
        noClientesMessage.style.display = 'none';
    
    tbody.innerHTML = clientesFiltrados.map(cliente => `
        <tr>
            <td>${cliente.nombre_completo}</td>
            <td>${cliente.cedula}</td>
                <td>${cliente.telefono || 'N/A'}</td>
                <td>
                <span class="estado-badge estado-${cliente.estado_gestion.toLowerCase().replace(' ', '-')}">
                    ${cliente.estado_gestion}
                    </span>
                </td>
            <td>${cliente.ultima_gestion || 'N/A'}</td>
                <td>
                <span class="tipificacion-badge tipificacion-${cliente.tipificacion || 'disponible'}">
                    ${obtenerNombreTipificacion(cliente.tipificacion)}
                    </span>
                </td>
                <td>
                <div class="acciones-cliente">
                    <button class="btn-accion btn-transferir" onclick="abrirModalTransferir('${cliente.id}', '${cliente.nombre_completo}', '${cliente.cedula}', '${asesorActual}')" title="Transferir Cliente">
                        <i class="fas fa-exchange-alt"></i>
                    </button>
                    <button class="btn-accion btn-liberar" onclick="liberarCliente('${cliente.id}')" title="Liberar Cliente">
                        <i class="fas fa-unlock"></i>
                    </button>
                </div>
                </td>
            </tr>
    `).join('');
}

function obtenerNombreTipificacion(tipificacion) {
    const nombres = {
        'disponible': 'Disponible',
        'contactado': 'Contactado',
        'asignacion_cita': 'Asignación de Cita',
        'volver_llamar': 'Volver a Llamar',
        'fuera_ciudad': 'Fuera de Ciudad',
        'no_interesa': 'No Interesa',
        'no_contactado': 'No Contactado'
    };
    
    return nombres[tipificacion] || 'Disponible';
}

// ===== FUNCIONES DE ACCIONES =====

function liberarCliente(clienteId) {
    if (!confirm('¿Estás seguro de que quieres liberar este cliente? Volverá a estar disponible para asignación.')) {
        return;
    }
    
    fetch('index.php?action=coordinador_liberar_cliente', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            cliente_id: clienteId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Cliente liberado exitosamente', 'success');
            // Recargar detalles del asesor
            cargarDetallesAsesor(asesorActual);
        } else {
            mostrarAlerta(data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al liberar el cliente', 'error');
    });
}

function confirmarTransferirCliente() {
    const nuevoAsesorId = document.getElementById('nuevoAsesor').value;
    const motivo = document.getElementById('motivoTransferir').value;
    
    if (!nuevoAsesorId) {
        mostrarAlerta('Debes seleccionar un nuevo asesor', 'error');
        return;
    }
    
    // Obtener el cliente actual del modal
    const clienteId = obtenerClienteIdDelModal();
    
    fetch('index.php?action=coordinador_transferir_cliente', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            cliente_id: clienteId,
            nuevo_asesor_id: nuevoAsesorId,
            motivo: motivo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Cliente transferido exitosamente', 'success');
            cerrarModalTransferir();
            // Recargar detalles del asesor
            cargarDetallesAsesor(asesorActual);
        } else {
            mostrarAlerta(data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al transferir el cliente', 'error');
    });
}

function obtenerClienteIdDelModal() {
    // Esta función debe obtener el ID del cliente del modal
    // Por ahora, usaremos una variable global o la pasaremos como parámetro
    return window.clienteTransferirId;
}

// ===== FUNCIONES DE BÚSQUEDA =====

function buscarCliente() {
    const cedula = document.getElementById('searchCedula').value.trim();
    
    if (!cedula) {
        clientesFiltrados = [...clientesAsesor];
        aplicarFiltros();
        return;
    }
    
    clientesFiltrados = clientesAsesor.filter(cliente => 
        cliente.cedula.includes(cedula)
    );
    
    mostrarClientesFiltrados();
}

// ===== FUNCIONES DE UTILIDAD =====

function getBadgeClass(estado) {
    switch (estado) {
        case 'Disponible': return 'warning';
        case 'Contactado': return 'info';
        case 'En Proceso': return 'primary';
        case 'Cita Programada': return 'success';
        case 'Cita Completada': return 'success';
        case 'No Interesado': return 'danger';
        case 'No Contactable': return 'secondary';
        case 'Asignado': return 'primary';
        default: return 'secondary';
    }
}

function getTipificacionBadgeClass(tipificacion) {
    switch (tipificacion) {
        case 'asignacion_cita': return 'success';
        case 'volver_llamar': return 'warning';
        case 'fuera_ciudad': return 'info';
        case 'no_interesa': return 'danger';
        case 'contactado': return 'success';
        case 'no_contactado': return 'secondary';
        default: return 'secondary';
    }
}

function mostrarDetallesAsesor(asesor, clientes) {
    console.log('Mostrando detalles del asesor:', asesor);
    console.log('Clientes del asesor:', clientes);
    
    // Actualizar título y nombre del asesor
    const modalTitle = document.getElementById('asesorModalTitle');
    const asesorNombre = document.getElementById('asesorNombre');
    const asesorEmail = document.getElementById('asesorEmail');
    
    if (modalTitle) modalTitle.textContent = `Detalles del Asesor: ${asesor.nombre_completo}`;
    if (asesorNombre) asesorNombre.textContent = asesor.nombre_completo;
    if (asesorEmail) asesorEmail.textContent = asesor.usuario || 'N/A';
    
    // Actualizar estadísticas
    const totalClientes = document.getElementById('totalClientesAsesor');
    const clientesGestionados = document.getElementById('clientesGestionados');
    const clientesPendientes = document.getElementById('clientesPendientes');
    
    if (totalClientes) totalClientes.textContent = clientes.length;
    if (clientesGestionados) clientesGestionados.textContent = clientes.filter(c => c.estado_gestion !== 'Disponible').length;
    if (clientesPendientes) clientesPendientes.textContent = clientes.filter(c => c.estado_gestion === 'Disponible').length;
    
    // Mostrar clientes en la tabla
    mostrarClientesEnTabla(clientes);
    
    // Mostrar el modal
    const modal = document.getElementById('asesorModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function mostrarLoading() {
    // Implementar indicador de carga
    const modalBody = document.querySelector('#asesorModal .modal-body');
    modalBody.innerHTML = '<div class="loading">Cargando...</div>';
}

function ocultarLoading() {
    // El contenido se carga en mostrarDetallesAsesor
}

function mostrarAlerta(mensaje, tipo) {
    // Crear contenedor de alertas si no existe
    let alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alertContainer';
        alertContainer.className = 'alert-container';
        document.body.appendChild(alertContainer);
    }
    
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

// ===== FUNCIONES DE ACCIONES DE CLIENTES =====

function liberarCliente(clienteId) {
    if (confirm('¿Estás seguro de que quieres liberar este cliente?')) {
        fetch('index.php?action=coordinador_liberar_cliente', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cliente_id=${clienteId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarAlerta('Cliente liberado exitosamente', 'success');
                // Recargar detalles del asesor
                cargarDetallesAsesor(asesorActualId);
            } else {
                mostrarAlerta(data.error || 'Error al liberar el cliente', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error al liberar el cliente', 'error');
        });
    }
}

function transferirCliente(clienteId) {
    const nuevoAsesorId = document.getElementById('nuevoAsesor').value;
    
    if (!nuevoAsesorId) {
        mostrarAlerta('Por favor selecciona un asesor', 'warning');
        return;
    }
    
    fetch('index.php?action=coordinador_transferir_cliente', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `cliente_id=${clienteId}&nuevo_asesor_id=${nuevoAsesorId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Cliente transferido exitosamente', 'success');
            // Recargar detalles del asesor
            cargarDetallesAsesor(asesorActualId);
        } else {
            mostrarAlerta(data.error || 'Error al transferir el cliente', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al transferir el cliente', 'error');
    });
}

// Variable global para el ID del asesor actual
let asesorActualId = null;

// ===== EVENT LISTENERS =====

document.addEventListener('DOMContentLoaded', function() {
// Cerrar modales al hacer clic fuera de ellos
window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
}

// Cerrar modales con ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.style.display = 'none';
            });
    }
    });
});
