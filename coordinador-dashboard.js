/**
 * JavaScript para el Dashboard del Coordinador
 * Maneja la lógica de modales, búsqueda, filtros y transferencia de clientes
 */

// Variables globales
let asesorActual = null;
let clientesActuales = [];
let clientesFiltrados = [];
let clienteTransferirActual = null;

// Inicialización cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando dashboard del coordinador...');
    
    // Configurar eventos de búsqueda
    setupSearchEvents();
    
    console.log('Dashboard del coordinador inicializado correctamente');
});

/**
 * Configurar eventos de búsqueda
 */
function setupSearchEvents() {
    const searchInput = document.getElementById('searchCedula');
    if (searchInput) {
        // Búsqueda en tiempo real
        searchInput.addEventListener('input', function() {
            if (this.value.length >= 3) {
                filtrarClientes();
            } else if (this.value.length === 0) {
                mostrarTodosLosClientes();
            }
        });
        
        // Búsqueda con Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarCliente();
            }
        });
    }
}

/**
 * Ver detalles del asesor
 */
function verDetallesAsesor(asesorId) {
    console.log('🔍 Ver detalles del asesor ID:', asesorId);
    
    // Mostrar modal
    const modal = document.getElementById('asesorModal');
    if (modal) {
        modal.style.display = 'block';
        
        // Mostrar indicador de carga
        mostrarCargandoDetalles();
        
        // Cargar datos del asesor
        cargarDetallesAsesor(asesorId);
    }
}

/**
 * Mostrar indicador de carga en detalles
 */
function mostrarCargandoDetalles() {
    const modalBody = document.querySelector('#asesorModal .modal-body');
    if (modalBody) {
        modalBody.innerHTML = `
            <div class="loading-container">
                <i class="fas fa-spinner fa-spin" style="font-size: 3em; color: #007bff; margin-bottom: 20px;"></i>
                <h4>Cargando detalles del asesor...</h4>
                <p>Por favor espera mientras se cargan los datos</p>
            </div>
        `;
    }
}

/**
 * Cargar detalles del asesor desde el servidor
 */
function cargarDetallesAsesor(asesorId) {
    console.log('📡 Cargando detalles del asesor ID:', asesorId);
    
    const formData = new FormData();
    formData.append('asesor_id', asesorId);
    
    fetch('index.php?action=coordinador_obtener_detalles_asesor', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Detalles del asesor recibidos:', data);
        
        if (data.success) {
            asesorActual = data.asesor;
            clientesActuales = data.clientes || [];
            clientesFiltrados = [...clientesActuales];
            
            mostrarDetallesAsesorEnModal(data.asesor);
            mostrarClientesEnTabla(clientesActuales);
            actualizarEstadisticasAsesor(data.asesor);
        } else {
            mostrarErrorDetalles(data.error || 'Error al cargar detalles del asesor');
        }
    })
    .catch(error => {
        console.error('Error al cargar detalles del asesor:', error);
        mostrarErrorDetalles('Error de conexión: ' + error.message);
    });
}

/**
 * Mostrar detalles del asesor en el modal
 */
function mostrarDetallesAsesorEnModal(asesor) {
    const modalBody = document.querySelector('#asesorModal .modal-body');
    if (!modalBody) return;
    
    modalBody.innerHTML = `
        <!-- Información del Asesor -->
        <div class="asesor-info-section">
            <div class="asesor-profile">
                <div class="asesor-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="asesor-details">
                    <h4 id="asesorNombre">${asesor.nombre_completo || 'N/A'}</h4>
                    <p id="asesorUsuario">Usuario: ${asesor.usuario || 'N/A'}</p>
                    <span class="asesor-status-badge active">Activo</span>
                </div>
            </div>
            
            <div class="asesor-stats-summary">
                <div class="stat-summary-item">
                    <span class="stat-number" id="totalClientesAsesor">${asesor.total_clientes || 0}</span>
                    <span class="stat-label">Total Clientes</span>
                </div>
                <div class="stat-summary-item">
                    <span class="stat-number" id="clientesGestionados">${asesor.clientes_gestionados || 0}</span>
                    <span class="stat-label">Gestionados</span>
                </div>
                <div class="stat-summary-item">
                    <span class="stat-number" id="clientesPendientes">${asesor.clientes_pendientes || 0}</span>
                    <span class="stat-label">Pendientes</span>
                </div>
            </div>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="search-filters-section">
            <div class="search-box">
                <input type="text" id="searchCedula" placeholder="🔍 Buscar por cédula..." class="search-input">
                <button type="button" onclick="buscarCliente()" class="btn-search">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <div class="filters-container">
                <select id="filterTipificacion" onchange="filtrarPorTipificacion()" class="filter-select">
                    <option value="">📋 Todas las tipificaciones</option>
                    <option value="asignacion_cita">📅 Asignación de Citas</option>
                    <option value="volver_llamar">📞 Volver a Llamar</option>
                    <option value="fuera_ciudad">🌍 Fuera de Ciudad</option>
                    <option value="no_interesa">❌ No Interesa</option>
                    <option value="contactado">✅ Contactado</option>
                    <option value="no_contactado">📵 No Contactado</option>
                </select>
                
                <select id="filterEstado" onchange="filtrarPorEstado()" class="filter-select">
                    <option value="">🏷️ Todos los estados</option>
                    <option value="Disponible">⏳ Disponible</option>
                    <option value="Contactado">📵 Contactado</option>
                    <option value="En Proceso">🔄 En Proceso</option>
                    <option value="Cita Programada">📅 Cita Programada</option>
                    <option value="Cita Completada">✅ Cita Completada</option>
                    <option value="No Interesa">❌ No Interesa</option>
                </select>
            </div>
        </div>

        <!-- Lista de Clientes -->
        <div class="clientes-section">
            <h4>📋 Clientes del Asesor</h4>
            <div class="clientes-table-container">
                <table class="clientes-table" id="clientesTable">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Cédula</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Última Gestión</th>
                            <th>Tipificación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="clientesTableBody">
                        <!-- Los clientes se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>
            <div id="noClientesMessage" class="no-data-message" style="display: none;">
                <i class="fas fa-info-circle"></i>
                <p>No se encontraron clientes con los filtros aplicados.</p>
            </div>
        </div>
    `;
    
    // Reconfigurar eventos después de recrear el HTML
    setupSearchEvents();
}

/**
 * Mostrar clientes en la tabla
 */
function mostrarClientesEnTabla(clientes) {
    const tbody = document.getElementById('clientesTableBody');
    const noClientesMessage = document.getElementById('noClientesMessage');
    
    if (!tbody) return;
    
    if (clientes.length === 0) {
        tbody.innerHTML = '';
        if (noClientesMessage) {
            noClientesMessage.style.display = 'block';
        }
        return;
    }
    
    if (noClientesMessage) {
        noClientesMessage.style.display = 'none';
    }
    
    let html = '';
    
    clientes.forEach(cliente => {
        const estadoClass = getEstadoClass(cliente.estado_gestion);
        const tipificacionClass = getTipificacionClass(cliente.ultima_tipificacion);
        
        html += `
            <tr>
                <td>${cliente.nombre_completo || 'N/A'}</td>
                <td>${cliente.cedula || 'N/A'}</td>
                <td>${cliente.telefono || 'N/A'}</td>
                <td>
                    <span class="cliente-estado ${estadoClass}">
                        ${formatearEstado(cliente.estado_gestion)}
                    </span>
                </td>
                <td>${formatearFecha(cliente.ultima_gestion)}</td>
                <td>
                    <span class="cliente-tipificacion ${tipificacionClass}">
                        ${formatearTipificacion(cliente.ultima_tipificacion)}
                    </span>
                </td>
                <td>
                    ${generarBotonesAccion(cliente)}
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

/**
 * Generar botones de acción según el cliente
 */
function generarBotonesAccion(cliente) {
    let botones = '';
    
    // Botón de transferir para clientes que pueden ser transferidos
    if (cliente.ultima_tipificacion === 'volver_llamar' || 
        cliente.ultima_tipificacion === 'asignacion_cita' || 
        cliente.estado_gestion === 'Contactado' ||
        cliente.estado_gestion === 'En Proceso') {
        botones += `
            <button class="btn-accion btn-transferir" onclick="transferirCliente(${cliente.id}, '${cliente.nombre_completo}', '${cliente.cedula}')">
                <i class="fas fa-exchange-alt"></i> Transferir
            </button>
        `;
    }
    
    // Botón de ver detalles para todos
    botones += `
        <button class="btn-accion btn-ver-detalles" onclick="verDetallesCliente(${cliente.id})">
            <i class="fas fa-eye"></i> Ver
        </button>
    `;
    
    return botones;
}

/**
 * Obtener clase CSS para el estado
 */
function getEstadoClass(estado) {
    if (!estado) return 'disponible';
    
    switch (estado.toLowerCase()) {
        case 'disponible':
            return 'disponible';
        case 'contactado':
            return 'contactado';
        case 'en proceso':
            return 'en-proceso';
        case 'cita programada':
            return 'cita-programada';
        case 'cita completada':
            return 'cita-completada';
        case 'no interesa':
            return 'no-interesa';
        default:
            return 'disponible';
    }
}

/**
 * Obtener clase CSS para la tipificación
 */
function getTipificacionClass(tipificacion) {
    if (!tipificacion) return '';
    
    return tipificacion.replace(/\s+/g, '_').toLowerCase();
}

/**
 * Formatear estado para mostrar
 */
function formatearEstado(estado) {
    if (!estado) return 'No Gestionado';
    
    return estado.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

/**
 * Formatear tipificación para mostrar
 */
function formatearTipificacion(tipificacion) {
    if (!tipificacion) return 'Sin tipificación';
    
    return tipificacion.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

/**
 * Formatear fecha para mostrar
 */
function formatearFecha(fecha) {
    if (!fecha || fecha === '0000-00-00 00:00:00') return 'N/A';
    
    try {
        const date = new Date(fecha);
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return 'N/A';
    }
}

/**
 * Actualizar estadísticas del asesor
 */
function actualizarEstadisticasAsesor(asesor) {
    document.getElementById('totalClientesAsesor').textContent = asesor.total_clientes || 0;
    document.getElementById('clientesGestionados').textContent = asesor.clientes_gestionados || 0;
    document.getElementById('clientesPendientes').textContent = asesor.clientes_pendientes || 0;
}

/**
 * Buscar cliente por cédula
 */
function buscarCliente() {
    const searchTerm = document.getElementById('searchCedula').value.trim();
    
    if (searchTerm.length === 0) {
        mostrarTodosLosClientes();
        return;
    }
    
    console.log('🔍 Buscando cliente con cédula:', searchTerm);
    
    const clientesEncontrados = clientesActuales.filter(cliente => 
        cliente.cedula && cliente.cedula.toString().includes(searchTerm)
    );
    
    if (clientesEncontrados.length > 0) {
        clientesFiltrados = clientesEncontrados;
        mostrarClientesEnTabla(clientesEncontrados);
        console.log('✅ Clientes encontrados:', clientesEncontrados.length);
    } else {
        console.log('❌ No se encontraron clientes con esa cédula');
        mostrarClientesEnTabla([]);
        mostrarMensajeNoClientes();
    }
}

/**
 * Filtrar por tipificación
 */
function filtrarPorTipificacion() {
    const tipificacion = document.getElementById('filterTipificacion').value;
    const estado = document.getElementById('filterEstado').value;
    
    aplicarFiltros(tipificacion, estado);
}

/**
 * Filtrar por estado
 */
function filtrarPorEstado() {
    const tipificacion = document.getElementById('filterTipificacion').value;
    const estado = document.getElementById('filterEstado').value;
    
    aplicarFiltros(tipificacion, estado);
}

/**
 * Aplicar filtros combinados
 */
function aplicarFiltros(tipificacion, estado) {
    console.log('🔍 Aplicando filtros - Tipificación:', tipificacion, 'Estado:', estado);
    
    let filtrados = [...clientesActuales];
    
    // Filtrar por tipificación
    if (tipificacion) {
        filtrados = filtrados.filter(cliente => 
            cliente.ultima_tipificacion === tipificacion
        );
    }
    
    // Filtrar por estado
    if (estado) {
        filtrados = filtrados.filter(cliente => 
            cliente.estado_gestion === estado
        );
    }
    
    clientesFiltrados = filtrados;
    mostrarClientesEnTabla(filtrados);
}

/**
 * Mostrar todos los clientes
 */
function mostrarTodosLosClientes() {
    clientesFiltrados = [...clientesActuales];
    mostrarClientesEnTabla(clientesActuales);
}

/**
 * Filtrar clientes (función auxiliar)
 */
function filtrarClientes() {
    const searchTerm = document.getElementById('searchCedula').value.trim();
    
    if (searchTerm.length === 0) {
        mostrarTodosLosClientes();
        return;
    }
    
    const filtrados = clientesActuales.filter(cliente => 
        cliente.cedula && cliente.cedula.toLowerCase().includes(searchTerm.toLowerCase())
    );
    
    clientesFiltrados = filtrados;
    mostrarClientesEnTabla(filtrados);
}

/**
 * Transferir cliente a otro asesor
 */
function transferirCliente(clienteId, nombreCliente, cedulaCliente) {
    console.log('🔄 Transferir cliente:', clienteId, nombreCliente, cedulaCliente);
    
    // Asignar el cliente a la variable global
    clienteTransferirActual = { id: clienteId, nombre_completo: nombreCliente, cedula: cedulaCliente };

    // Mostrar modal de transferencia
    const modal = document.getElementById('transferirModal');
    if (modal) {
        // Configurar información del cliente
        document.getElementById('clienteTransferirNombre').textContent = nombreCliente;
        document.getElementById('clienteTransferirCedula').textContent = cedulaCliente;
        document.getElementById('clienteTransferirAsesorActual').textContent = asesorActual?.nombre_completo || 'N/A';
        
        // Cargar lista de asesores disponibles
        cargarAsesoresDisponibles();
        
        // Mostrar modal
        modal.style.display = 'block';
    }
}

/**
 * Cargar asesores disponibles para transferencia
 */
function cargarAsesoresDisponibles() {
    console.log('📡 Cargando asesores disponibles...');
    
    fetch('index.php?action=coordinador_obtener_asesores_disponibles', {
        method: 'POST'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Asesores disponibles recibidos:', data);
        
        if (data.success) {
            llenarSelectAsesores(data.asesores);
        } else {
            console.error('Error al cargar asesores:', data.error);
        }
    })
    .catch(error => {
        console.error('Error al cargar asesores disponibles:', error);
    });
}

/**
 * Llenar select de asesores
 */
function llenarSelectAsesores(asesores) {
    const select = document.getElementById('nuevoAsesor');
    if (!select) return;
    
    // Limpiar opciones existentes
    select.innerHTML = '<option value="">Selecciona un asesor...</option>';
    
    // Agregar asesores disponibles
    asesores.forEach(asesor => {
        if (asesor.id != asesorActual?.id) { // Excluir asesor actual
            const option = document.createElement('option');
            option.value = asesor.id;
            option.textContent = asesor.nombre_completo;
            select.appendChild(option);
        }
    });
}

/**
 * Confirmar transferencia de cliente
 */
function confirmarTransferirCliente() {
    const nuevoAsesorId = document.getElementById('nuevoAsesor').value;
    const motivo = document.getElementById('motivoTransferir').value.trim();
    
    if (!nuevoAsesorId) {
        alert('Por favor selecciona un nuevo asesor');
        return;
    }
    
    if (!motivo) {
        alert('Por favor explica el motivo de la transferencia');
        return;
    }
    
    console.log('✅ Confirmando transferencia...');
    console.log('Nuevo asesor ID:', nuevoAsesorId);
    console.log('Motivo:', motivo);
    
    // Mostrar indicador de carga
    const btnConfirmar = document.querySelector('#transferirModal .btn-primary');
    const textoOriginal = btnConfirmar.textContent;
    btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Transferiendo...';
    btnConfirmar.disabled = true;
    
    // Realizar transferencia
    const formData = new FormData();
    formData.append('cliente_id', clienteTransferirActual.id);
    formData.append('nuevo_asesor_id', nuevoAsesorId);
    formData.append('motivo', motivo);
    
    fetch('index.php?action=coordinador_transferir_cliente', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Respuesta de transferencia:', data);
        
        if (data.success) {
            // Mostrar mensaje de éxito
            alert(`✅ Cliente transferido exitosamente a ${data.asesor_nuevo}`);
            
            // Cerrar modal
            cerrarModalTransferir();
            
            // Recargar datos del asesor
            if (asesorActual) {
                cargarDetallesAsesor(asesorActual.id);
            }
        } else {
            alert('❌ Error al transferir cliente: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error en transferencia:', error);
        alert('❌ Error de conexión: ' + error.message);
    })
    .finally(() => {
        // Restaurar botón
        btnConfirmar.innerHTML = textoOriginal;
        btnConfirmar.disabled = false;
    });
}

/**
 * Ver detalles de un cliente específico
 */
function verDetallesCliente(clienteId) {
    console.log('👁️ Ver detalles del cliente ID:', clienteId);
    
    // Aquí puedes implementar un modal para mostrar detalles del cliente
    // Por ahora solo mostramos un alert
    alert('Función de ver detalles del cliente en desarrollo');
}

/**
 * Mostrar mensaje cuando no hay clientes
 */
function mostrarMensajeNoClientes() {
    const tbody = document.getElementById('clientesTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">
                    <div class="no-data-message">
                        <i class="fas fa-info-circle"></i>
                        <p>No se encontraron clientes con los criterios de búsqueda</p>
                    </div>
                </td>
            </tr>
        `;
    }
}

/**
 * Mostrar error en detalles
 */
function mostrarErrorDetalles(mensaje) {
    const modalBody = document.querySelector('#asesorModal .modal-body');
    if (modalBody) {
        modalBody.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 3em; margin-bottom: 15px;"></i>
                <h4>Error al cargar detalles</h4>
                <p>${mensaje}</p>
            </div>
        `;
    }
}

/**
 * Cerrar modal del asesor
 */
function cerrarModalAsesor() {
    const modal = document.getElementById('asesorModal');
    if (modal) {
        modal.style.display = 'none';
        
        // Limpiar variables
        asesorActual = null;
        clientesActuales = [];
        clientesFiltrados = [];
    }
}

/**
 * Cerrar modal de transferencia
 */
function cerrarModalTransferir() {
    const modal = document.getElementById('transferirModal');
    if (modal) {
        modal.style.display = 'none';
        
        // Limpiar campos
        document.getElementById('nuevoAsesor').value = '';
        document.getElementById('motivoTransferir').value = '';
        clienteTransferirActual = null; // Limpiar el cliente transferido
    }
}

// Cerrar modales al hacer clic fuera de ellos
window.onclick = function(event) {
    const asesorModal = document.getElementById('asesorModal');
    const transferirModal = document.getElementById('transferirModal');
    
    if (event.target === asesorModal) {
        cerrarModalAsesor();
    }
    
    if (event.target === transferirModal) {
        cerrarModalTransferir();
    }
}

// Cerrar modales con ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalAsesor();
        cerrarModalTransferir();
    }
});

console.log('Script del dashboard del coordinador cargado correctamente');
