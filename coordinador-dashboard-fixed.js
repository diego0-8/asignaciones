/**
 * Coordinador Dashboard JavaScript - Versión Corregida con Filtros Jerárquicos y de Fecha
 * Funcionalidades:
 * - Modal de detalles del asesor
 * - Filtros jerárquicos en cascada
 * - Filtros de fecha (predefinidos y personalizados)
 * - Búsqueda por cédula
 * - Transferencia de clientes
 */

// ===== VARIABLES GLOBALES =====
let asesorActual = null;
let asesorActualId = null;
let clientesAsesor = [];
let clientesFiltrados = [];

// ===== FUNCIONES PRINCIPALES =====

function abrirModalAsesor(asesorId) {
    console.log('🚀 Abriendo modal para asesor:', asesorId);
    
    asesorActualId = asesorId;
    asesorActual = null;
    
    // Mostrar modal primero
    const modal = document.getElementById('asesorModal');
    if (modal) {
        modal.style.display = 'block';
        console.log('✅ Modal abierto correctamente');
    }
    
    // Mostrar loading
    mostrarLoading();
    
    // Cargar detalles del asesor
    cargarDetallesAsesor(asesorId);
}

function cargarDetallesAsesor(asesorId) {
    console.log('📥 Cargando detalles del asesor:', asesorId);
    
    const url = `index.php?action=coordinador_obtener_detalles_asesor&asesor_id=${asesorId}`;
    
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
                
                // Inicializar filtros después de cargar los datos
                inicializarFiltros();
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

// ===== FUNCIONES DE MOSTRAR DATOS =====

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
    if (clientesGestionados) clientesGestionados.textContent = clientes.filter(c => c.ultima_tipificacion).length;
    if (clientesPendientes) clientesPendientes.textContent = clientes.filter(c => !c.ultima_tipificacion).length;
    
    // Mostrar clientes en la tabla
    mostrarClientesEnTabla(clientes);
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
        
        // Determinar si mostrar información de "volver a llamar"
        let tipificacionDisplay = obtenerNombreTipificacion(cliente.ultima_tipificacion);
        if (cliente.ultima_tipificacion === 'volver_llamar' && cliente.fecha_proxima_llamada) {
            tipificacionDisplay += `<br><small class="text-muted">📞 ${cliente.fecha_proxima_llamada}</small>`;
        }
        
        row.innerHTML = `
            <td>${cliente.nombre_completo}</td>
            <td>${cliente.cedula}</td>
            <td>${cliente.telefono || 'N/A'}</td>
            <td>
                <span class="badge badge-${getBadgeClass(cliente.estado_gestion)}">
                    ${cliente.estado_gestion}
                </span>
            </td>
            <td>${cliente.fecha_ultima_tipificacion || 'Sin fecha'}</td>
            <td>
                <span class="badge badge-${getTipificacionBadgeClass(cliente.ultima_tipificacion)}">
                    ${tipificacionDisplay}
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

// ===== FUNCIONES DE FILTRADO JERÁRQUICO =====

function inicializarFiltros() {
    console.log('🔧 Inicializando filtros jerárquicos...');
    
    // Crear filtros dinámicamente si no existen
    crearFiltrosJerarquicos();
    
    // Configurar event listeners para filtros en cascada
    configurarFiltrosCascada();
}

function crearFiltrosJerarquicos() {
    const filtrosContainer = document.getElementById('filtrosContainer');
    if (!filtrosContainer) return;
    
    filtrosContainer.innerHTML = `
        <div class="filtros-jerarquicos">
            <div class="filtro-nivel">
                <label for="filterNivel1">Nivel 1: Estado de Gestión</label>
                <select id="filterNivel1" onchange="actualizarFiltroNivel2()">
                    <option value="">Todos los clientes</option>
                    <option value="gestionado">Gestionado</option>
                    <option value="no_gestionado">No Gestionado</option>
                </select>
            </div>
            
            <div class="filtro-nivel" id="filtroNivel2" style="display: none;">
                <label for="filterNivel2Select">Nivel 2: Estado de Contacto</label>
                <select id="filterNivel2Select" onchange="actualizarFiltroNivel3()">
                    <option value="">Todos los gestionados</option>
                    <option value="contactado">Contactado</option>
                    <option value="no_contactado">No Contactado</option>
                </select>
            </div>
            
            <div class="filtro-nivel" id="filtroNivel3" style="display: none;">
                <label for="filterNivel3Select">Nivel 3: Tipificación Específica</label>
                <select id="filterNivel3Select">
                    <option value="">Todas las tipificaciones</option>
                </select>
            </div>
            
                         <!-- Filtro de fecha mejorado con botones de acción rápida -->
             <div class="filtro-nivel">
                 <label>Filtro por Fecha</label>
                 <div class="filtros-fecha-rapidos">
                     <button type="button" class="btn-fecha-rapida" onclick="aplicarFiltroFechaRapida('hoy')">
                         <i class="fas fa-calendar-day"></i> Hoy
                     </button>
                     <button type="button" class="btn-fecha-rapida" onclick="aplicarFiltroFechaRapida('ayer')">
                         <i class="fas fa-calendar-minus"></i> Ayer
                     </button>
                     <button type="button" class="btn-fecha-rapida" onclick="aplicarFiltroFechaRapida('esta_semana')">
                         <i class="fas fa-calendar-week"></i> Esta Semana
                     </button>
                     <button type="button" class="btn-fecha-rapida" onclick="aplicarFiltroFechaRapida('este_mes')">
                         <i class="fas fa-calendar-alt"></i> Este Mes
                     </button>
                     <button type="button" class="btn-fecha-rapida" onclick="aplicarFiltroFechaRapida('semana_pasada')">
                         <i class="fas fa-calendar-week"></i> Semana Pasada
                     </button>
                     <button type="button" class="btn-fecha-rapida" onclick="aplicarFiltroFechaRapida('mes_pasado')">
                         <i class="fas fa-calendar-alt"></i> Mes Pasado
                     </button>
                 </div>
                 
                 <!-- Calendario personalizado -->
                 <div class="filtros-fecha-calendario">
                     <div class="filtro-fecha-item">
                         <label for="fechaDesde">Desde:</label>
                         <input type="date" id="fechaDesde" onchange="aplicarFiltros()">
                     </div>
                     <div class="filtro-fecha-item">
                         <label for="fechaHasta">Hasta:</label>
                         <input type="date" id="fechaHasta" onchange="aplicarFiltros()">
                     </div>
                     <button type="button" class="btn-fecha-aplicar" onclick="aplicarFiltros()">
                         <i class="fas fa-search"></i> Aplicar
                     </button>
                 </div>
             </div>
            
            <div class="filtros-acciones">
                <button class="btn btn-primary" onclick="aplicarFiltros()">
                    <i class="fas fa-filter"></i> Aplicar Filtros
                </button>
                <button class="btn btn-secondary" onclick="limpiarFiltros()">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
        </div>
    `;
}

function configurarFiltrosCascada() {
    // Los event listeners ya están configurados en el HTML generado
    console.log('✅ Filtros en cascada configurados');
}

function actualizarFiltroNivel2() {
    const nivel1 = document.getElementById('filterNivel1');
    const filtroNivel2 = document.getElementById('filtroNivel2');
    const filtroNivel3 = document.getElementById('filtroNivel3');
    
    if (nivel1.value === 'gestionado') {
        filtroNivel2.style.display = 'block';
        filtroNivel3.style.display = 'none';
    } else {
        filtroNivel2.style.display = 'none';
        filtroNivel3.style.display = 'none';
    }
    
    // Limpiar selecciones de niveles inferiores
    if (filtroNivel2.style.display === 'none') {
        document.getElementById('filterNivel2Select').value = '';
    }
    if (filtroNivel3.style.display === 'none') {
        document.getElementById('filterNivel3Select').value = '';
    }
}

function actualizarFiltroNivel3() {
    const nivel2 = document.getElementById('filterNivel2Select');
    const filtroNivel3 = document.getElementById('filtroNivel3');
    const nivel3Select = document.getElementById('filterNivel3Select');
    
    if (nivel2.value === 'contactado') {
        filtroNivel3.style.display = 'block';
        // Opciones para clientes contactados (Nivel 2 de tipificación)
        nivel3Select.innerHTML = `
            <option value="">Todas las tipificaciones</option>
            <option value="asignacion_cita">Asignación de Cita</option>
            <option value="volver_llamar">Volver a Llamar</option>
            <option value="fuera_ciudad">Fuera de Ciudad</option>
            <option value="no_interesa">No le Interesa</option>
        `;
    } else if (nivel2.value === 'no_contactado') {
        filtroNivel3.style.display = 'block';
        // Opciones para clientes no contactados
        nivel3Select.innerHTML = `
            <option value="">Todas las tipificaciones</option>
            <option value="no_contactado">No Contactado</option>
            <option value="disponible">Disponible</option>
        `;
    } else {
        filtroNivel3.style.display = 'none';
        nivel3Select.value = '';
    }
}

// ===== FUNCIONES DE FILTRO DE FECHA =====

function aplicarFiltroFechaRapida(tipoFecha) {
    console.log('📅 Aplicando filtro de fecha rápida:', tipoFecha);
    
    // Limpiar campos de fecha personalizada
    const fechaDesde = document.getElementById('fechaDesde');
    const fechaHasta = document.getElementById('fechaHasta');
    if (fechaDesde) fechaDesde.value = '';
    if (fechaHasta) fechaDesde.value = '';
    
    // Aplicar filtro inmediatamente
    aplicarFiltrosConFechaRapida(tipoFecha);
}

function aplicarFiltrosConFechaRapida(tipoFecha) {
    const nivel1 = document.getElementById('filterNivel1')?.value || '';
    const nivel2 = document.getElementById('filterNivel2Select')?.value || '';
    const nivel3 = document.getElementById('filterNivel3Select')?.value || '';
    
    console.log('🔍 Aplicando filtros con fecha rápida:', { nivel1, nivel2, nivel3, tipoFecha });
    
    clientesFiltrados = clientesAsesor.filter(cliente => {
        let cumpleNivel1 = true;
        let cumpleNivel2 = true;
        let cumpleNivel3 = true;
        let cumpleFecha = true;
        
        // Nivel 1: Gestionado vs No Gestionado
        if (nivel1 === 'gestionado') {
            cumpleNivel1 = !!cliente.ultima_tipificacion;
        } else if (nivel1 === 'no_gestionado') {
            cumpleNivel1 = !cliente.ultima_tipificacion;
        }
        
        // Nivel 2: Contactado vs No Contactado
        if (nivel1 === 'gestionado' && nivel2) {
            if (nivel2 === 'contactado') {
                cumpleNivel2 = ['asignacion_cita', 'volver_llamar', 'fuera_ciudad', 'no_interesa'].includes(cliente.ultima_tipificacion);
            } else if (nivel2 === 'no_contactado') {
                cumpleNivel2 = ['no_contactado', 'disponible'].includes(cliente.ultima_tipificacion);
            }
        }
        
        // Nivel 3: Tipificación específica
        if (nivel3) {
            cumpleNivel3 = cliente.ultima_tipificacion === nivel3;
        }
        
        // Filtro por fecha rápida
        if (tipoFecha) {
            const clienteFecha = new Date(cliente.fecha_ultima_tipificacion || cliente.fecha_creacion);
            cumpleFecha = aplicarFiltroFechaPredefinida(clienteFecha, tipoFecha);
        }
        
        return cumpleNivel1 && cumpleNivel2 && cumpleNivel3 && cumpleFecha;
    });
    
    console.log(`✅ Filtros con fecha rápida aplicados: ${clientesFiltrados.length} de ${clientesAsesor.length} clientes`);
    mostrarClientesFiltrados();
    actualizarContadorFiltros();
}

function aplicarFiltros() {
    const nivel1 = document.getElementById('filterNivel1')?.value || '';
    const nivel2 = document.getElementById('filterNivel2Select')?.value || '';
    const nivel3 = document.getElementById('filterNivel3Select')?.value || '';
    const fechaDesde = document.getElementById('fechaDesde')?.value || '';
    const fechaHasta = document.getElementById('fechaHasta')?.value || '';
    
    console.log('🔍 Aplicando filtros jerárquicos y de fecha personalizada:', { nivel1, nivel2, nivel3, fechaDesde, fechaHasta });
    
    clientesFiltrados = clientesAsesor.filter(cliente => {
        let cumpleNivel1 = true;
        let cumpleNivel2 = true;
        let cumpleNivel3 = true;
        let cumpleFecha = true;
        
        // Nivel 1: Gestionado vs No Gestionado
        if (nivel1 === 'gestionado') {
            cumpleNivel1 = !!cliente.ultima_tipificacion;
        } else if (nivel1 === 'no_gestionado') {
            cumpleNivel1 = !cliente.ultima_tipificacion;
        }
        
        // Nivel 2: Contactado vs No Contactado (solo si es gestionado)
        if (nivel1 === 'gestionado' && nivel2) {
            if (nivel2 === 'contactado') {
                cumpleNivel2 = ['asignacion_cita', 'volver_llamar', 'fuera_ciudad', 'no_interesa'].includes(cliente.ultima_tipificacion);
            } else if (nivel2 === 'no_contactado') {
                cumpleNivel2 = ['no_contactado', 'disponible'].includes(cliente.ultima_tipificacion);
            }
        }
        
        // Nivel 3: Tipificación específica
        if (nivel3) {
            cumpleNivel3 = cliente.ultima_tipificacion === nivel3;
        }
        
        // Filtro por fecha personalizada (calendario)
        if (fechaDesde || fechaHasta) {
            const clienteFecha = new Date(cliente.fecha_ultima_tipificacion || cliente.fecha_creacion);
            
            if (fechaDesde && fechaHasta) {
                // Rango completo
                const fechaDesdeObj = new Date(fechaDesde);
                const fechaHastaObj = new Date(fechaHasta);
                fechaHastaObj.setHours(23, 59, 59, 999); // Incluir todo el día hasta
                
                cumpleFecha = clienteFecha >= fechaDesdeObj && clienteFecha <= fechaHastaObj;
            } else if (fechaDesde) {
                // Solo fecha desde
                const fechaDesdeObj = new Date(fechaDesde);
                cumpleFecha = clienteFecha >= fechaDesdeObj;
            } else if (fechaHasta) {
                // Solo fecha hasta
                const fechaHastaObj = new Date(fechaHasta);
                fechaHastaObj.setHours(23, 59, 59, 999); // Incluir todo el día hasta
                cumpleFecha = clienteFecha <= fechaHastaObj;
            }
            
            console.log(`📅 Filtro fecha: Cliente ${cliente.nombre_completo}, Fecha: ${clienteFecha}, Desde: ${fechaDesde}, Hasta: ${fechaHasta}, Cumple: ${cumpleFecha}`);
        }
        
        return cumpleNivel1 && cumpleNivel2 && cumpleNivel3 && cumpleFecha;
    });
    
    console.log(`✅ Filtros aplicados: ${clientesFiltrados.length} de ${clientesAsesor.length} clientes`);
    mostrarClientesFiltrados();
    actualizarContadorFiltros();
}

function aplicarFiltroFechaPredefinida(clienteFecha, filtroFecha) {
    const hoy = new Date();
    const inicioDia = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
    const finDia = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate(), 23, 59, 59);
    
    switch (filtroFecha) {
        case 'hoy':
            return clienteFecha >= inicioDia && clienteFecha <= finDia;
        case 'ayer':
            const ayer = new Date(hoy);
            ayer.setDate(hoy.getDate() - 1);
            const inicioAyer = new Date(ayer.getFullYear(), ayer.getMonth(), ayer.getDate());
            const finAyer = new Date(ayer.getFullYear(), ayer.getMonth(), ayer.getDate(), 23, 59, 59);
            return clienteFecha >= inicioAyer && clienteFecha <= finAyer;
        case 'esta_semana':
            const inicioSemana = new Date(hoy);
            inicioSemana.setDate(hoy.getDate() - hoy.getDay());
            return clienteFecha >= inicioSemana;
        case 'semana_pasada':
            const inicioSemanaPasada = new Date(hoy);
            inicioSemanaPasada.setDate(hoy.getDate() - hoy.getDay() - 7);
            const finSemanaPasada = new Date(hoy);
            finSemanaPasada.setDate(hoy.getDate() - hoy.getDay() - 1);
            return clienteFecha >= inicioSemanaPasada && clienteFecha <= finSemanaPasada;
        case 'este_mes':
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            return clienteFecha >= inicioMes;
        case 'mes_pasado':
            const inicioMesPasado = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
            const finMesPasado = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
            return clienteFecha >= inicioMesPasado && clienteFecha <= finMesPasado;
        default:
            return true;
    }
}

function limpiarFiltros() {
    const nivel1 = document.getElementById('filterNivel1');
    const filtroNivel2 = document.getElementById('filtroNivel2');
    const filtroNivel3 = document.getElementById('filtroNivel3');
    
    if (nivel1) nivel1.value = '';
    if (filtroNivel2) filtroNivel2.style.display = 'none';
    if (filtroNivel3) filtroNivel3.style.display = 'none';
    
    // Limpiar selecciones
    const nivel2Select = document.getElementById('filterNivel2Select');
    const nivel3Select = document.getElementById('filterNivel3Select');
    const fechaDesdeInput = document.getElementById('fechaDesde');
    const fechaHastaInput = document.getElementById('fechaHasta');
    
    if (nivel2Select) nivel2Select.value = '';
    if (nivel3Select) nivel3Select.value = '';
    if (fechaDesdeInput) fechaDesdeInput.value = '';
    if (fechaHastaInput) fechaHastaInput.value = '';
    
    // Mostrar todos los clientes
    clientesFiltrados = [...clientesAsesor];
    mostrarClientesFiltrados();
    actualizarContadorFiltros();
}

// ===== FUNCIONES DE MOSTRAR CLIENTES FILTRADOS =====

function mostrarClientesFiltrados() {
    if (clientesFiltrados.length === 0) {
        mostrarNoClientesMessage();
    } else {
        mostrarClientesEnTabla(clientesFiltrados);
    }
}

function mostrarNoClientesMessage() {
    const tbody = document.getElementById('clientesTableBody');
    const noClientesMessage = document.getElementById('noClientesMessage');
    
    if (tbody) tbody.innerHTML = '';
    if (noClientesMessage) noClientesMessage.style.display = 'block';
}

function actualizarContadorFiltros() {
    const contadorFiltros = document.getElementById('contadorFiltros');
    if (!contadorFiltros) return;
    
    const total = clientesAsesor.length;
    const filtrados = clientesFiltrados.length;
    
    if (filtrados === total) {
        contadorFiltros.innerHTML = `
            <span class="contador-info">
                <i class="fas fa-filter"></i> 
                Mostrando todos los clientes (${total})
            </span>
        `;
    } else {
        contadorFiltros.innerHTML = `
            <span class="contador-info">
                <i class="fas fa-filter"></i> 
                Mostrando ${filtrados} de ${total} clientes
            </span>
        `;
    }
}

// ===== FUNCIONES UTILITARIAS =====

function obtenerNombreTipificacion(tipificacion) {
    if (!tipificacion) return 'Sin tipificación';
    
    const nombres = {
        'asignacion_cita': 'Asignación de Cita',
        'volver_llamar': 'Volver a Llamar',
        'fuera_ciudad': 'Fuera de Ciudad',
        'no_interesa': 'No le Interesa',
        'no_contactado': 'No Contactado',
        'disponible': 'Disponible'
    };
    
    return nombres[tipificacion] || tipificacion;
}

function getBadgeClass(estado) {
    const clases = {
        'Disponible': 'success',
        'En Proceso': 'warning',
        'Cita Programada': 'info',
        'Completado': 'success',
        'Cancelado': 'danger'
    };
    
    return clases[estado] || 'secondary';
}

function getTipificacionBadgeClass(tipificacion) {
    if (!tipificacion) return 'secondary';
    
    const clases = {
        'asignacion_cita': 'success',
        'volver_llamar': 'warning',
        'fuera_ciudad': 'info',
        'no_interesa': 'danger',
        'no_contactado': 'secondary',
        'disponible': 'primary'
    };
    
    return clases[tipificacion] || 'secondary';
}

// ===== FUNCIONES DE BÚSQUEDA =====

function buscarCliente() {
    const searchCedula = document.getElementById('searchCedula')?.value?.trim();
    
    if (!searchCedula) {
        mostrarAlerta('Por favor ingresa una cédula para buscar', 'warning');
        return;
    }
    
    const clienteEncontrado = clientesAsesor.find(cliente => 
        cliente.cedula.includes(searchCedula)
    );
    
    if (clienteEncontrado) {
        // Filtrar solo el cliente encontrado
        clientesFiltrados = [clienteEncontrado];
        mostrarClientesFiltrados();
        actualizarContadorFiltros();
        
        // Limpiar búsqueda
        document.getElementById('searchCedula').value = '';
        
        mostrarAlerta(`Cliente encontrado: ${clienteEncontrado.nombre_completo}`, 'success');
    } else {
        mostrarAlerta('No se encontró ningún cliente con esa cédula', 'warning');
    }
}

// ===== FUNCIONES DE ACCIONES =====

function liberarCliente(clienteId) {
    if (confirm('¿Estás seguro de que quieres liberar este cliente?')) {
        console.log('🔓 Liberando cliente:', clienteId);
        // Aquí iría la lógica para liberar el cliente
        mostrarAlerta('Cliente liberado exitosamente', 'success');
    }
}

function transferirCliente(clienteId) {
    const cliente = clientesAsesor.find(c => c.id === clienteId);
    if (!cliente) return;
    
    // Mostrar modal de transferencia
    const modal = document.getElementById('transferirModal');
    if (modal) {
        document.getElementById('clienteTransferirNombre').textContent = cliente.nombre_completo;
        document.getElementById('clienteTransferirCedula').textContent = cliente.cedula;
        document.getElementById('clienteTransferirAsesorActual').textContent = asesorActual?.nombre_completo || 'N/A';
        
        modal.style.display = 'block';
    }
}

// ===== FUNCIONES DE MODAL =====

function cerrarModalAsesor() {
    const modal = document.getElementById('asesorModal');
    if (modal) {
        modal.style.display = 'none';
        limpiarFiltros();
    }
}

function cerrarModalTransferir() {
    const modal = document.getElementById('transferirModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function confirmarTransferirCliente() {
    const nuevoAsesor = document.getElementById('nuevoAsesor')?.value;
    const motivo = document.getElementById('motivoTransferir')?.value;
    
    if (!nuevoAsesor) {
        mostrarAlerta('Por favor selecciona un nuevo asesor', 'warning');
        return;
    }
    
    if (!motivo.trim()) {
        mostrarAlerta('Por favor ingresa el motivo de la transferencia', 'warning');
        return;
    }
    
    console.log('🔄 Confirmando transferencia:', { nuevoAsesor, motivo });
    // Aquí iría la lógica para confirmar la transferencia
    
    mostrarAlerta('Transferencia confirmada exitosamente', 'success');
    cerrarModalTransferir();
}

// ===== FUNCIONES DE LOADING =====

function mostrarLoading() {
    const modalBody = document.querySelector('#asesorModal .modal-body');
    if (modalBody) {
        const loadingDiv = document.createElement('div');
        loadingDiv.id = 'loadingOverlay';
        loadingDiv.className = 'loading-overlay';
        loadingDiv.innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Cargando...</p>
            </div>
        `;
        modalBody.appendChild(loadingDiv);
    }
}

function ocultarLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}

// ===== FUNCIONES DE ALERTAS =====

function mostrarAlerta(mensaje, tipo = 'info') {
    // Implementar sistema de alertas
    console.log(`[${tipo.toUpperCase()}] ${mensaje}`);
    alert(mensaje);
}

function mostrarErrorModal(titulo, mensaje) {
    // Implementar modal de error
    console.error(`[ERROR] ${titulo}: ${mensaje}`);
    alert(`Error: ${titulo}\n${mensaje}`);
}

// ===== INICIALIZACIÓN =====

document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Coordinador Dashboard JavaScript cargado correctamente');
});

// ===== EVENT LISTENERS =====

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalAsesor();
        cerrarModalTransferir();
    }
});
