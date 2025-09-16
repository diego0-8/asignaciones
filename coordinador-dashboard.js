/**
 * JavaScript para el Dashboard del Coordinador
 * Maneja modales, filtros y funcionalidades de gestión de asesores
 */

// Variables globales
let asesorActual = null;
let asesorActualId = null;
let clientesAsesor = [];
let clientesFiltrados = [];

// ===== FUNCIONES DE MODALES =====

function abrirModalAsesor(asesorId) {
    console.log('🚀 Abriendo modal para asesor:', asesorId);
    
    // Guardar el ID del asesor actual
    asesorActual = asesorId;
    asesorActualId = asesorId;
    
    // Abrir primero el modal para asegurar que sus nodos existen en el DOM
    const modal = document.getElementById('asesorModal');
    if (modal) {
        modal.style.display = 'block';
        console.log('✅ Modal abierto correctamente');
    } else {
        console.error('❌ Modal no encontrado');
        return;
    }
    
    // Cargar detalles del asesor
    cargarDetallesAsesor(asesorId);
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
    console.log('📡 Iniciando carga de detalles para asesor:', asesorId);
    
    // Guardar el ID del asesor actual
    asesorActualId = asesorId;
    
    // Mostrar loading
    mostrarLoading();
    
    const url = `index.php?action=coordinador_obtener_detalles_asesor&asesor_id=${asesorId}`;
    console.log('🌐 Haciendo fetch a:', url);
    
    fetch(url)
        .then(response => {
            console.log('📥 Respuesta recibida:', response);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('📊 Datos recibidos:', data);
            if (data.success) {
                console.log('✅ Datos cargados exitosamente');
                mostrarDetallesAsesor(data.asesor, data.clientes);
                clientesAsesor = data.clientes;
                clientesFiltrados = [...data.clientes];
                aplicarFiltros();
            } else {
                console.error('❌ Error en la respuesta:', data.error);
                mostrarErrorModal('No se pudieron cargar los detalles del asesor.', data.error || 'Error desconocido');
                mostrarAlerta(data.error || 'Error al cargar detalles del asesor', 'error');
            }
        })
        .catch(error => {
            console.error('💥 Error en fetch:', error);
            mostrarErrorModal('Error de conexión al cargar detalles del asesor.', error?.message || String(error));
            mostrarAlerta('Error al cargar detalles del asesor', 'error');
        })
        .finally(() => {
            console.log('🏁 Finalizando carga de detalles');
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
    const tipSel = document.getElementById('filterTipificacion');
    const estSel = document.getElementById('filterEstado');
    const filtroTipificacion = tipSel ? tipSel.value : '';
    const filtroEstado = estSel ? estSel.value : '';
    
    console.log('🔍 Aplicando filtros:', { filtroTipificacion, filtroEstado });
    
    clientesFiltrados = clientesAsesor.filter(cliente => {
        let cumpleTipificacion = true;
        let cumpleEstado = true;
        
        // Filtro de tipificación (usar ultima_tipificacion del backend)
        if (filtroTipificacion) {
            if (filtroTipificacion === 'contactado') {
                // Clientes que han sido contactados exitosamente
                cumpleTipificacion = ['asignacion_cita', 'volver_llamar', 'fuera_ciudad', 'no_interesa'].includes(cliente.ultima_tipificacion);
            } else if (filtroTipificacion === 'no_contactado') {
                // Clientes que no han sido contactados
                cumpleTipificacion = ['no_contactado', 'disponible'].includes(cliente.ultima_tipificacion);
            } else {
                // Filtro específico por tipificación
                cumpleTipificacion = cliente.ultima_tipificacion === filtroTipificacion;
            }
        }
        
        // Filtro de estado de gestión
        if (filtroEstado) {
            cumpleEstado = cliente.estado_gestion === filtroEstado;
        }
        
        return cumpleTipificacion && cumpleEstado;
    });
    
    console.log(`✅ Filtros aplicados: ${clientesFiltrados.length} de ${clientesAsesor.length} clientes`);
    mostrarClientesFiltrados();
    actualizarContadorFiltros();
}

function mostrarClientesEnTabla(clientes) {
    const tbody = document.getElementById('clientesTableBody');
    const noClientesMessage = document.getElementById('noClientesMessage');
    
    if (!tbody) {
        console.error('No se encontró el tbody de la tabla de clientes');
        // Intentar una vez más en el siguiente ciclo del event loop
        setTimeout(() => mostrarClientesEnTabla(clientes), 0);
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
        if (tbody) tbody.innerHTML = '';
        if (noClientesMessage) noClientesMessage.style.display = 'block';
        actualizarContadorFiltros();
        return;
    }
    
    if (noClientesMessage) noClientesMessage.style.display = 'none';
    
    if (!tbody) return;
    
    // Crear filas con mejor formato y badges
    tbody.innerHTML = clientesFiltrados.map(cliente => `
        <tr class="cliente-row" data-cliente-id="${cliente.id}">
            <td>
                <div class="cliente-info">
                    <strong>${cliente.nombre_completo}</strong>
                    <small class="text-muted">ID: ${cliente.id}</small>
                </div>
            </td>
            <td>
                <code class="cedula-code">${cliente.cedula}</code>
            </td>
            <td>
                <span class="telefono-info">
                    <i class="fas fa-phone"></i> ${cliente.telefono || 'N/A'}
                </span>
            </td>
            <td>
                <span class="estado-badge estado-${getEstadoBadgeClass(cliente.estado_gestion)}">
                    ${cliente.estado_gestion || 'N/A'}
                </span>
            </td>
            <td>
                <span class="fecha-info">
                    ${formatearFecha(cliente.ultima_gestion) || 'N/A'}
                </span>
            </td>
            <td>
                <span class="tipificacion-badge tipificacion-${getTipificacionBadgeClass(cliente.ultima_tipificacion)}">
                    ${obtenerNombreTipificacion(cliente.ultima_tipificacion)}
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
                    <button class="btn-accion btn-ver" onclick="verDetallesCliente(${cliente.id})" title="Ver Detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
    
    actualizarContadorFiltros();
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
        // Si no hay búsqueda, aplicar solo los filtros activos
        aplicarFiltros();
        return;
    }
    
    console.log('🔍 Buscando cliente con cédula:', cedula);
    
    // Aplicar búsqueda y filtros combinados
    const filtroTipificacion = document.getElementById('filterTipificacion')?.value || '';
    const filtroEstado = document.getElementById('filterEstado')?.value || '';
    
    clientesFiltrados = clientesAsesor.filter(cliente => {
        // Búsqueda por cédula
        const cumpleBusqueda = cliente.cedula.includes(cedula);
        
        // Filtros de tipificación y estado
        let cumpleTipificacion = true;
        let cumpleEstado = true;
        
        if (filtroTipificacion) {
            if (filtroTipificacion === 'contactado') {
                cumpleTipificacion = ['asignacion_cita', 'volver_llamar', 'fuera_ciudad', 'no_interesa'].includes(cliente.ultima_tipificacion);
            } else if (filtroTipificacion === 'no_contactado') {
                cumpleTipificacion = ['no_contactado', 'disponible'].includes(cliente.ultima_tipificacion);
            } else {
                cumpleTipificacion = cliente.ultima_tipificacion === filtroTipificacion;
            }
        }
        
        if (filtroEstado) {
            cumpleEstado = cliente.estado_gestion === filtroEstado;
        }
        
        return cumpleBusqueda && cumpleTipificacion && cumpleEstado;
    });
    
    console.log(`🔍 Búsqueda completada: ${clientesFiltrados.length} clientes encontrados`);
    mostrarClientesFiltrados();
    actualizarContadorFiltros();
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
    if (!tipificacion) return 'disponible';
    
    switch (tipificacion) {
        case 'asignacion_cita': return 'asignacion_cita';
        case 'volver_llamar': return 'volver_llamar';
        case 'fuera_ciudad': return 'fuera_ciudad';
        case 'no_interesa': return 'no_interesa';
        case 'contactado': return 'contactado';
        case 'no_contactado': return 'no_contactado';
        case 'disponible': return 'disponible';
        default: return 'disponible';
    }
}

function getEstadoBadgeClass(estado) {
    if (!estado) return 'default';
    
    // Convertir a formato CSS-friendly
    return estado.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
}

function formatearFecha(fecha) {
    if (!fecha) return null;
    
    try {
        const fechaObj = new Date(fecha);
        if (isNaN(fechaObj.getTime())) return fecha; // Si no es una fecha válida, devolver original
        
        const ahora = new Date();
        const diffTime = Math.abs(ahora - fechaObj);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) {
            return 'Hoy';
        } else if (diffDays === 1) {
            return 'Ayer';
        } else if (diffDays < 7) {
            return `Hace ${diffDays} días`;
        } else {
            return fechaObj.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
    } catch (error) {
        return fecha; // Si hay error, devolver fecha original
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
    // Agregar overlay de carga sin destruir el contenido del modal
    const modalBody = document.querySelector('#asesorModal .modal-body');
    if (!modalBody) return;
    let overlay = modalBody.querySelector('.loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="loading">Cargando...</div>';
        overlay.style.position = 'absolute';
        overlay.style.inset = '0';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.background = 'rgba(255,255,255,0.8)';
        overlay.style.zIndex = '10';
        // Asegurar posicionamiento relativo del contenedor
        const parent = modalBody.parentElement;
        if (parent && getComputedStyle(parent).position === 'static') {
            parent.style.position = 'relative';
        }
        modalBody.appendChild(overlay);
    } else {
        overlay.style.display = 'flex';
    }
}

function ocultarLoading() {
    const modalBody = document.querySelector('#asesorModal .modal-body');
    if (!modalBody) return;
    const overlay = modalBody.querySelector('.loading-overlay');
    if (overlay) overlay.style.display = 'none';
}

// Muestra errores dentro del modal para depuración rápida
function mostrarErrorModal(titulo, detalle) {
    const modal = document.getElementById('asesorModal');
    if (modal && modal.style.display !== 'block') {
        modal.style.display = 'block';
    }
    const modalBody = document.querySelector('#asesorModal .modal-body');
    if (!modalBody) return;
    const html = `
        <div class="error-box" style="border:1px solid #f5c2c7;background:#f8d7da;color:#842029;padding:12px;border-radius:6px;margin-bottom:12px;">
            <strong>${titulo}</strong>
            <pre style="white-space:pre-wrap;margin:8px 0 0;font-size:12px;">${(detalle || '').toString()}</pre>
        </div>
    `;
    // Insertar el error arriba, conservando el contenido existente
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    modalBody.prepend(wrapper.firstElementChild);
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

// Variable global para el ID del asesor actual (ya declarada arriba)
// asesorActualId = null; // Comentado porque ya está declarada arriba

// ===== FUNCIONES AUXILIARES =====

function actualizarContadorFiltros() {
    const totalClientes = clientesAsesor.length;
    const clientesFiltradosCount = clientesFiltrados.length;
    
    // Actualizar contador en el modal si existe
    const contadorElement = document.getElementById('contadorFiltros');
    if (contadorElement) {
        contadorElement.innerHTML = `
            <span class="contador-info">
                <i class="fas fa-filter"></i> 
                Mostrando ${clientesFiltradosCount} de ${totalClientes} clientes
            </span>
        `;
    }
    
    // Actualizar título de la tabla
    const tituloTabla = document.querySelector('.clientes-section h4');
    if (tituloTabla) {
        tituloTabla.innerHTML = `📋 Clientes del Asesor <small class="text-muted">(${clientesFiltradosCount}/${totalClientes})</small>`;
    }
}

function verDetallesCliente(clienteId) {
    console.log('👁️ Ver detalles del cliente:', clienteId);
    // Aquí puedes implementar la lógica para mostrar más detalles del cliente
    mostrarAlerta(`Ver detalles del cliente ${clienteId}`, 'info');
}

function abrirModalTransferir(clienteId, nombreCliente, cedulaCliente, asesorActual) {
    console.log('🔄 Abriendo modal de transferencia para cliente:', clienteId);
    
    // Guardar información del cliente para la transferencia
    window.clienteTransferirId = clienteId;
    
    // Actualizar modal de transferencia
    const nombreElement = document.getElementById('clienteTransferirNombre');
    const cedulaElement = document.getElementById('clienteTransferirCedula');
    const asesorElement = document.getElementById('clienteTransferirAsesorActual');
    
    if (nombreElement) nombreElement.textContent = nombreCliente;
    if (cedulaElement) cedulaElement.textContent = cedulaCliente;
    if (asesorElement) asesorElement.textContent = asesorActual;
    
    // Cargar asesores disponibles
    cargarAsesoresDisponibles();
    
    // Mostrar modal
    const modal = document.getElementById('transferirModal');
    if (modal) {
        modal.style.display = 'block';
        console.log('✅ Modal de transferencia abierto');
    } else {
        console.error('❌ Modal de transferencia no encontrado');
    }
}

function cerrarModalTransferir() {
    document.getElementById('transferirModal').style.display = 'none';
    // Limpiar campos
    document.getElementById('nuevoAsesor').value = '';
    document.getElementById('motivoTransferir').value = '';
}

function limpiarFiltros() {
    console.log('🧹 Limpiando filtros...');
    
    // Limpiar campos de filtro
    const filterTipificacion = document.getElementById('filterTipificacion');
    const filterEstado = document.getElementById('filterEstado');
    const searchCedula = document.getElementById('searchCedula');
    
    if (filterTipificacion) filterTipificacion.value = '';
    if (filterEstado) filterEstado.value = '';
    if (searchCedula) searchCedula.value = '';
    
    // Restaurar todos los clientes
    clientesFiltrados = [...clientesAsesor];
    
    // Mostrar clientes sin filtrar
    mostrarClientesFiltrados();
    
    // Actualizar contador
    actualizarContadorFiltros();
    
    mostrarAlerta('Filtros limpiados correctamente', 'success');
}

// ===== EVENT LISTENERS =====

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Coordinador Dashboard inicializado');
    
    // Cerrar modales al hacer clic fuera de ellos
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
});

// Cerrar modales con ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
    }
});
