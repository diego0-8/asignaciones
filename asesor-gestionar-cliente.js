/**
 * JavaScript para la gestión de clientes del asesor
 * Maneja la lógica de formularios, validaciones y envío de datos
 */

// Variables globales
let currentClienteId = null;
let currentAsesorId = null;
let isSubmitting = false; // Prevenir doble envío
let submitToken = null; // Token único por envío

// Inicialización cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando gestión de cliente...');
    
    // Obtener IDs del formulario
    const clienteIdInput = document.querySelector('input[name="cliente_id"]');
    if (clienteIdInput) {
        currentClienteId = clienteIdInput.value;
        console.log('Cliente ID:', currentClienteId);
    }
    
    // Verificar que los elementos del formulario existen
    const tipoContacto = document.getElementById('tipo_contacto');
    const opcionesNoContacto = document.getElementById('opciones_no_contacto');
    const motivoNoContacto = document.getElementById('motivo_no_contacto');
    
    console.log('Elementos del formulario:', {
        tipoContacto: !!tipoContacto,
        opcionesNoContacto: !!opcionesNoContacto,
        motivoNoContacto: !!motivoNoContacto
    });
    
    if (!opcionesNoContacto) {
        console.error('❌ CRÍTICO: Elemento opciones_no_contacto no encontrado en el DOM');
    }
    
    if (!motivoNoContacto) {
        console.error('❌ CRÍTICO: Elemento motivo_no_contacto no encontrado en el DOM');
    }
    
    // Inicializar contador de caracteres
    initCharCounter();
    
    // Configurar eventos del formulario
    setupFormEvents();
    
    console.log('✅ Gestión de cliente inicializada correctamente');
});

/**
 * Inicializar contador de caracteres para observaciones
 */
function initCharCounter() {
    const textarea = document.getElementById('observaciones');
    const charCount = document.getElementById('char_count');
    const charLimit = document.getElementById('char_limit');
    
    if (textarea && charCount && charLimit) {
        textarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCount.textContent = currentLength;
            
            // Cambiar color según el límite
            if (currentLength < 10) {
                charCount.style.color = '#dc3545'; // Rojo
            } else {
                charCount.style.color = '#28a745'; // Verde
            }
        });
    }
}

/**
 * Configurar eventos del formulario
 */
function setupFormEvents() {
    // Evento para mostrar/ocultar opciones de gestión
    const tipoContactoSelect = document.getElementById('tipo_contacto');
    if (tipoContactoSelect) {
        tipoContactoSelect.addEventListener('change', mostrarOpcionesGestion);
    }
    
    // Evento para mostrar campos específicos
    const tipoGestionSelect = document.getElementById('tipo_gestion');
    if (tipoGestionSelect) {
        tipoGestionSelect.addEventListener('change', mostrarCamposEspecificos);
    }
    
    // Evento de envío del formulario
    const form = document.getElementById('gestionForm');
    if (form) {
        form.addEventListener('submit', procesarGestion);
    }
}

/**
 * Mostrar opciones de gestión cuando se selecciona "Contactado"
 */
function mostrarOpcionesGestion() {
    console.log('🔍 mostrarOpcionesGestion() ejecutada');
    
    const tipoContacto = document.getElementById('tipo_contacto').value;
    const opcionesGestion = document.getElementById('opciones_gestion');
    const opcionesNoContacto = document.getElementById('opciones_no_contacto');
    const tipoGestionSelect = document.getElementById('tipo_gestion');
    const motivoNoContactoSelect = document.getElementById('motivo_no_contacto');
    
    console.log('Tipo de contacto seleccionado:', tipoContacto);
    console.log('Elementos encontrados:', {
        opcionesGestion: !!opcionesGestion,
        opcionesNoContacto: !!opcionesNoContacto,
        tipoGestionSelect: !!tipoGestionSelect,
        motivoNoContactoSelect: !!motivoNoContactoSelect
    });
    
    // Ocultar todos los campos primero
    if (opcionesGestion) {
        opcionesGestion.style.display = 'none';
        opcionesGestion.classList.remove('fade-in');
    }
    if (opcionesNoContacto) {
        opcionesNoContacto.style.display = 'none';
        opcionesNoContacto.classList.remove('fade-in');
    }
    ocultarTodosLosCampos();
    
    if (tipoContacto === 'contactado') {
        console.log('Mostrando opciones de gestión para contactado');
        // Mostrar opciones de gestión para contactado
        if (opcionesGestion) {
            opcionesGestion.style.display = 'block';
            opcionesGestion.classList.add('fade-in');
        }
        // Agregar required cuando se muestra
        if (tipoGestionSelect) {
            tipoGestionSelect.setAttribute('required', 'required');
        }
    } else if (tipoContacto === 'no_contactado') {
        console.log('Mostrando opciones de no contacto');
        // Mostrar opciones de no contacto
        if (opcionesNoContacto) {
            opcionesNoContacto.style.display = 'block';
            opcionesNoContacto.classList.add('fade-in');
            console.log('Desplegable de no contacto mostrado');
        } else {
            console.error('❌ Elemento opciones_no_contacto no encontrado');
        }
        // Agregar required cuando se muestra
        if (motivoNoContactoSelect) {
            motivoNoContactoSelect.setAttribute('required', 'required');
        }
        
        // Establecer tipo_gestion como 'no_contactado' y remover required
        if (tipoGestionSelect) {
            tipoGestionSelect.value = 'no_contactado';
            tipoGestionSelect.removeAttribute('required'); // Remover required para evitar conflicto
        }
    } else {
        console.log('Limpiando campos');
        // Limpiar todos los campos
        if (tipoGestionSelect) {
            tipoGestionSelect.removeAttribute('required');
            tipoGestionSelect.value = '';
        }
        if (motivoNoContactoSelect) {
            motivoNoContactoSelect.removeAttribute('required');
            motivoNoContactoSelect.value = '';
        }
    }
}

/**
 * Mostrar campos específicos según el tipo de gestión
 */
function mostrarCamposEspecificos() {
    const tipoGestion = document.getElementById('tipo_gestion').value;
    
    // Ocultar todos los campos primero
    ocultarTodosLosCampos();
    
    // Mostrar campos según el tipo seleccionado
    switch (tipoGestion) {
        case 'asignacion_cita':
            const camposCita = document.getElementById('campos_cita');
            camposCita.style.display = 'block';
            camposCita.classList.add('fade-in');
            
            // Agregar required a los campos de cita
            const inputsCita = camposCita.querySelectorAll('input[type="date"], input[type="time"], input[type="text"]');
            inputsCita.forEach(input => {
                if (input.name === 'fecha_cita' || input.name === 'hora_cita' || input.name === 'lugar_cita') {
                    input.setAttribute('required', 'required');
                }
            });
            break;
            
        case 'volver_llamar':
            const camposVolverLlamar = document.getElementById('campos_volver_llamar');
            camposVolverLlamar.style.display = 'block';
            camposVolverLlamar.classList.add('fade-in');
            
            // Agregar required a los campos de volver a llamar
            const fechaProximo = document.getElementById('fecha_proximo_contacto');
            const horaProximo = document.getElementById('hora_proximo_contacto');
            if (fechaProximo) fechaProximo.setAttribute('required', 'required');
            if (horaProximo) horaProximo.setAttribute('required', 'required');
            
            // Configurar validaciones en tiempo real
            configurarValidacionesVolverLlamar();
            break;
            
        case 'fuera_ciudad':
        case 'no_interesa':
            // No hay campos adicionales para estos tipos
            break;
    }
}

/**
 * Ocultar todos los campos específicos
 */
function ocultarTodosLosCampos() {
    const campos = [
        'campos_cita',
        'campos_volver_llamar'
    ];
    
    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.style.display = 'none';
            campo.classList.remove('fade-in');
            
            // Remover required de todos los inputs dentro del campo oculto
            const inputs = campo.querySelectorAll('input[required], select[required]');
            inputs.forEach(input => {
                input.removeAttribute('required');
            });
        }
    });
}

/**
 * Validar formulario antes de enviar
 */
function validarFormulario() {
    console.log('🔍 Iniciando validación del formulario...');
    
    const tipoContacto = document.getElementById('tipo_contacto').value;
    const observaciones = document.getElementById('observaciones').value.trim();
    
    console.log('📞 Tipo de contacto:', tipoContacto);
    console.log('📝 Observaciones:', observaciones, 'Longitud:', observaciones.length);
    
    // Validar tipo de contacto
    if (!tipoContacto) {
        console.log('❌ Falta tipo de contacto');
        mostrarError('Debes seleccionar un tipo de contacto');
        return false;
    }
    
    // Validar observaciones
    if (observaciones.length < 10) {
        console.log('❌ Observaciones muy cortas');
        mostrarError('Las observaciones deben tener al menos 10 caracteres');
        return false;
    }
    
    // Validar campos específicos solo si es contactado
    if (tipoContacto === 'contactado') {
        const tipoGestion = document.getElementById('tipo_gestion').value;
        console.log('🎯 Tipo de gestión:', tipoGestion);
        
        if (!tipoGestion) {
            console.log('❌ Falta tipo de gestión');
            mostrarError('Debes seleccionar un resultado de la gestión');
            return false;
        }
        
        // Validar campos según el tipo de gestión
        if (!validarCamposEspecificos(tipoGestion)) {
            console.log('❌ Validación de campos específicos falló');
            return false;
        }
    } else if (tipoContacto === 'no_contactado') {
        // Para no_contactado, validar motivo de no contacto
        const motivoNoContacto = document.getElementById('motivo_no_contacto').value;
        console.log('🚫 Motivo de no contacto:', motivoNoContacto);
        
        if (!motivoNoContacto) {
            console.log('❌ Falta motivo de no contacto');
            mostrarError('Debes seleccionar el motivo de no contacto');
            return false;
        }
        
        // Asegurar que tipo_gestion sea 'no_contactado'
        const tipoGestionSelect = document.getElementById('tipo_gestion');
        if (tipoGestionSelect) {
            tipoGestionSelect.value = 'no_contactado';
        }
    }
    
    console.log('✅ Validación exitosa');
    return true;
}

/**
 * Validar campos específicos según el tipo de gestión
 */
function validarCamposEspecificos(tipoGestion) {
    switch (tipoGestion) {
        case 'asignacion_cita':
            const fechaCita = document.getElementById('fecha_cita').value;
            const horaCita = document.getElementById('hora_cita').value;
            const lugarCita = document.getElementById('lugar_cita').value;
            
            if (!fechaCita || !horaCita || !lugarCita) {
                mostrarError('Para asignar una cita, debes completar fecha, hora y lugar');
                return false;
            }
            break;
            
        case 'volver_llamar':
            const fechaProximo = document.getElementById('fecha_proximo_contacto').value;
            const horaProximo = document.getElementById('hora_proximo_contacto').value;
            
            if (!fechaProximo || !horaProximo) {
                mostrarError('Para programar una nueva llamada, debes especificar fecha y hora');
                return false;
            }
            
            // Validar que la fecha sea futura
            const fechaSeleccionada = new Date(fechaProximo);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0); // Resetear horas para comparar solo fechas
            
            if (fechaSeleccionada <= hoy) {
                mostrarError('La fecha debe ser futura (a partir de mañana)');
                return false;
            }
            
            // Validar que sea día laboral (lunes a sábado)
            const diaSemana = fechaSeleccionada.getDay(); // 0=domingo, 1=lunes, ..., 6=sábado
            if (diaSemana === 0) { // Domingo
                mostrarError('No se pueden programar llamadas los domingos. Selecciona un día de lunes a sábado');
                return false;
            }
            
            // Validar horario (7:30 AM - 6:00 PM)
            const hora = parseInt(horaProximo.split(':')[0]);
            const minuto = parseInt(horaProximo.split(':')[1]);
            const horaEnMinutos = hora * 60 + minuto;
            const horaInicio = 7 * 60 + 30; // 7:30 AM = 450 minutos
            const horaFin = 18 * 60; // 6:00 PM = 1080 minutos
            
            if (horaEnMinutos < horaInicio || horaEnMinutos > horaFin) {
                mostrarError('El horario debe estar entre 7:30 AM y 6:00 PM');
                return false;
            }
            
            break;
    }
    
    return true;
}

/**
 * Procesar la gestión del cliente
 */
function procesarGestion(event) {
    console.log('🚀 procesarGestion() llamada');
    console.log('📝 Evento:', event);
    
    if (event) {
        event.preventDefault();
    }
    
    // PREVENIR DOBLE ENVÍO
    if (isSubmitting) {
        console.log('⚠️ Formulario ya se está enviando, ignorando clic adicional');
        return false;
    }
    
    console.log('🔍 Iniciando validación...');
    
    // Validar formulario
    if (!validarFormulario()) {
        console.log('❌ Validación falló');
        return false;
    }
    
    console.log('✅ Validación exitosa, mostrando indicador de carga...');
    
    // Marcar como enviando
    isSubmitting = true;
    
    // Generar token único para este envío
    submitToken = Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    // Mostrar indicador de carga
    mostrarCargando();
    
    // Recopilar datos del formulario
    const form = document.getElementById('gestionForm');
    if (!form) {
        console.error('❌ Formulario no encontrado');
        mostrarError('Error: Formulario no encontrado');
        resetSubmitState();
        return false;
    }
    
    const formData = new FormData(form);
    
    // Agregar datos adicionales
    formData.append('asesor_id', getAsesorId());
    formData.append('fecha_gestion', new Date().toISOString());
    formData.append('submit_token', submitToken); // Token único
    
    console.log('📤 Enviando datos:', Object.fromEntries(formData));
    console.log('🔒 Token de envío:', submitToken);
    
    // Enviar datos al servidor
    enviarGestion(formData);
    
    return false;
}

/**
 * Enviar gestión al servidor
 */
function enviarGestion(formData) {
    console.log('📡 Enviando gestión al servidor...');
    console.log('🔒 Token actual:', submitToken);
    
    // Verificar que el token sea válido
    if (!submitToken) {
        console.error('❌ Token de envío no válido');
        mostrarError('Error interno: Token de envío no válido');
        resetSubmitState();
        return;
    }
    
    console.log('🌐 Realizando petición a: index.php?action=asesor_guardar_gestion');
    
    fetch('index.php?action=asesor_guardar_gestion', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📥 Respuesta recibida:', response.status, response.statusText);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📋 Datos del servidor:', data);
        
        if (data.success) {
            console.log('✅ Guardado exitoso');
            mostrarExito(data.message || 'Gestión guardada correctamente');
            
            // Limpiar formulario
            limpiarFormulario();
            
            // Recargar historial si existe
            if (typeof recargarHistorial === 'function') {
                recargarHistorial();
            }
        } else {
            console.log('❌ Error del servidor:', data.error);
            mostrarError(data.error || 'Error al guardar la gestión');
        }
    })
    .catch(error => {
        console.error('💥 Error al enviar gestión:', error);
        mostrarError('Error de conexión: ' + error.message);
    })
    .finally(() => {
        console.log('🏁 Finalizando envío...');
        ocultarCargando();
        // Resetear estado de envío
        resetSubmitState();
    });
}

/**
 * Obtener ID del asesor desde la sesión
 */
function getAsesorId() {
    // Intentar obtener desde un campo oculto o variable global
    const asesorIdInput = document.querySelector('input[name="asesor_id"]');
    if (asesorIdInput) {
        return asesorIdInput.value;
    }
    
    // Si no hay campo oculto, intentar obtener desde la URL o sesión
    // Por ahora, retornamos null y el servidor deberá obtenerlo de la sesión
    return null;
}

/**
 * Limpiar formulario después de guardar
 */
function limpiarFormulario() {
    const form = document.getElementById('gestionForm');
    if (form) {
        form.reset();
        
        // Ocultar campos específicos
        ocultarTodosLosCampos();
        
        // Resetear contador de caracteres
        const charCount = document.getElementById('char_count');
        if (charCount) {
            charCount.textContent = '0';
            charCount.style.color = '#dc3545';
        }
    }
}

/**
 * Mostrar mensaje de éxito
 */
function mostrarExito(mensaje) {
    mostrarModal('Éxito', mensaje, 'success');
}

/**
 * Mostrar mensaje de error
 */
function mostrarError(mensaje) {
    mostrarModal('Error', mensaje, 'error');
}

/**
 * Mostrar modal de alerta
 */
function mostrarModal(titulo, mensaje, tipo = 'info') {
    const modal = document.getElementById('alertModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    
    if (modal && modalTitle && modalMessage) {
        modalTitle.textContent = titulo;
        modalMessage.textContent = mensaje;
        
        // Cambiar clase según el tipo
        modal.className = 'modal';
        if (tipo === 'success') {
            modal.classList.add('success');
        } else if (tipo === 'error') {
            modal.classList.add('error');
        }
        
        modal.style.display = 'block';
    }
}

/**
 * Cerrar modal
 */
function cerrarModal() {
    const modal = document.getElementById('alertModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Mostrar indicador de carga
 */
function mostrarCargando() {
    const btnSubmit = document.getElementById('btnSubmit');
    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        btnSubmit.classList.add('btn-disabled');
        
        // Agregar tooltip de prevención
        btnSubmit.title = 'Formulario en proceso de envío. Por favor espera...';
    }
}

/**
 * Ocultar indicador de carga
 */
function ocultarCargando() {
    const btnSubmit = document.getElementById('btnSubmit');
    if (btnSubmit) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar Gestión';
        btnSubmit.classList.remove('btn-disabled');
        btnSubmit.title = 'Guardar la gestión del cliente';
    }
}

/**
 * Resetear estado de envío
 */
function resetSubmitState() {
    isSubmitting = false;
    submitToken = null;
    console.log('🔄 Estado de envío reseteado');
}

/**
 * Mostrar detalles de una gestión específica
 */
function mostrarDetallesGestion(gestionId, tipoGestion) {
    console.log('🔍 Mostrando detalles de gestión:', gestionId, tipoGestion);
    
    // Mostrar modal
    const modal = document.getElementById('detallesModal');
    const modalTitle = document.getElementById('detallesTitle');
    const modalContent = document.getElementById('detallesContent');
    
    if (!modal || !modalTitle || !modalContent) {
        console.error('❌ Elementos del modal no encontrados');
        return;
    }
    
    // Configurar título según el tipo
    if (tipoGestion === 'asignacion_cita') {
        modalTitle.textContent = '📅 Detalles de Cita Asignada';
    } else if (tipoGestion === 'volver_llamar') {
        modalTitle.textContent = '📞 Detalles de Volver a Llamar';
    } else {
        modalTitle.textContent = '📋 Detalles de la Gestión';
    }
    
    // Mostrar indicador de carga
    modalContent.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando detalles...</div>';
    modal.style.display = 'block';
    
    // Cargar detalles desde el servidor
    cargarDetallesGestion(gestionId, tipoGestion);
}

/**
 * Cargar detalles de gestión desde el servidor
 */
function cargarDetallesGestion(gestionId, tipoGestion) {
    console.log('📡 Cargando detalles de gestión ID:', gestionId);
    
    const formData = new FormData();
    formData.append('gestion_id', gestionId);
    formData.append('tipo_gestion', tipoGestion);
    
    fetch('index.php?action=asesor_obtener_detalles_gestion', {
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
        console.log('Detalles recibidos:', data);
        
        if (data.success) {
            mostrarDetallesEnModal(data.detalles, tipoGestion);
        } else {
            mostrarErrorDetalles(data.error || 'Error al cargar detalles');
        }
    })
    .catch(error => {
        console.error('Error al cargar detalles:', error);
        mostrarErrorDetalles('Error de conexión: ' + error.message);
    });
}

/**
 * Mostrar detalles en el modal
 */
function mostrarDetallesEnModal(detalles, tipoGestion) {
    const modalContent = document.getElementById('detallesContent');
    
    let html = '';
    
    if (tipoGestion === 'asignacion_cita') {
        html = generarHTMLDetallesCita(detalles);
    } else if (tipoGestion === 'volver_llamar') {
        html = generarHTMLDetallesVolverLlamar(detalles);
    } else {
        html = generarHTMLDetallesGenericos(detalles);
    }
    
    modalContent.innerHTML = html;
}

/**
 * Generar HTML para detalles de cita
 */
function generarHTMLDetallesCita(detalles) {
    return `
        <div class="detalles-gestion detalles-cita">
            <h4><i class="fas fa-calendar-check"></i> Cita Programada</h4>
            
            <div class="detalle-item">
                <span class="detalle-label">Cliente:</span>
                <span class="detalle-value">${detalles.cliente_nombre || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Asesor:</span>
                <span class="detalle-value">${detalles.asesor_nombre || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Fecha de Cita:</span>
                <span class="detalle-value">${detalles.fecha_cita || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Hora de Cita:</span>
                <span class="detalle-value">${detalles.hora_cita || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Lugar:</span>
                <span class="detalle-value">${detalles.lugar_cita || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Estado:</span>
                <span class="detalle-value">
                    <span class="badge badge-success">Programada</span>
                </span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Observaciones:</span>
                <span class="detalle-value">${detalles.observaciones || 'Sin observaciones'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Fecha de Gestión:</span>
                <span class="detalle-value">${detalles.fecha_gestion || 'N/A'}</span>
            </div>
        </div>
    `;
}

/**
 * Generar HTML para detalles de volver a llamar
 */
function generarHTMLDetallesVolverLlamar(detalles) {
    return `
        <div class="detalles-gestion detalles-volver-llamar">
            <h4><i class="fas fa-phone-volume"></i> Programado para Nueva Llamada</h4>
            
            <div class="detalle-item">
                <span class="detalle-label">Cliente:</span>
                <span class="detalle-value">${detalles.cliente_nombre || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Asesor:</span>
                <span class="detalle-value">${detalles.asesor_nombre || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Fecha Programada:</span>
                <span class="detalle-value">${detalles.fecha_proximo_contacto || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Hora Programada:</span>
                <span class="detalle-value">${detalles.hora_proximo_contacto || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Observaciones:</span>
                <span class="detalle-value">${detalles.observaciones || 'Sin observaciones'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Fecha de Gestión:</span>
                <span class="detalle-value">${detalles.fecha_gestion || 'N/A'}</span>
            </div>
        </div>
    `;
}

/**
 * Generar HTML para detalles genéricos
 */
function generarHTMLDetallesGenericos(detalles) {
    return `
        <div class="detalles-gestion">
            <h4><i class="fas fa-info-circle"></i> Detalles de la Gestión</h4>
            
            <div class="detalle-item">
                <span class="detalle-label">Cliente:</span>
                <span class="detalle-value">${detalles.cliente_nombre || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Asesor:</span>
                <span class="detalle-value">${detalles.asesor_nombre || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Tipo de Contacto:</span>
                <span class="detalle-value">${detalles.tipo_contacto || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Resultado:</span>
                <span class="detalle-value">${detalles.tipo_gestion || 'N/A'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Observaciones:</span>
                <span class="detalle-value">${detalles.observaciones || 'Sin observaciones'}</span>
            </div>
            
            <div class="detalle-item">
                <span class="detalle-label">Fecha de Gestión:</span>
                <span class="detalle-value">${detalles.fecha_gestion || 'N/A'}</span>
            </div>
        </div>
    `;
}

/**
 * Mostrar error en detalles
 */
function mostrarErrorDetalles(mensaje) {
    const modalContent = document.getElementById('detallesContent');
    modalContent.innerHTML = `
        <div class="error-message">
            <i class="fas fa-exclamation-triangle" style="color: #dc3545; font-size: 2em; margin-bottom: 15px;"></i>
            <h4>Error al cargar detalles</h4>
            <p>${mensaje}</p>
        </div>
    `;
}

/**
 * Cerrar modal de detalles
 */
function cerrarDetallesModal() {
    const modal = document.getElementById('detallesModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * Recargar historial de gestiones
 */
function recargarHistorial() {
    console.log('Recargando historial de gestiones...');
    
    // Por ahora, recargar la página completa
    // En el futuro, se puede implementar carga AJAX
    setTimeout(() => {
        window.location.reload();
    }, 2000);
}

/**
 * Función para mostrar árbol de tipificación (cuando se implemente)
 */
function mostrarTipificacion() {
    console.log('Mostrando árbol de tipificación...');
    
    // TODO: Implementar modal o página con árbol de tipificación
    alert('Función de tipificación en desarrollo. Por favor, selecciona manualmente el resultado de la gestión.');
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById('alertModal');
    if (event.target === modal) {
        cerrarModal();
    }
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModal();
    }
});

/**
 * Configurar validaciones en tiempo real para volver a llamar
 */
function configurarValidacionesVolverLlamar() {
    const fechaInput = document.getElementById('fecha_proximo_contacto');
    const horaInput = document.getElementById('hora_proximo_contacto');
    
    if (fechaInput) {
        // Validar fecha cuando cambie
        fechaInput.addEventListener('change', function() {
            validarFechaVolverLlamar();
        });
        
        // Establecer fecha mínima (mañana)
        const manana = new Date();
        manana.setDate(manana.getDate() + 1);
        fechaInput.min = manana.toISOString().split('T')[0];
    }
    
    if (horaInput) {
        // Validar hora cuando cambie
        horaInput.addEventListener('change', function() {
            validarHoraVolverLlamar();
        });
        
        // Establecer límites de hora
        horaInput.min = '07:30';
        horaInput.max = '18:00';
    }
}

/**
 * Validar fecha para volver a llamar
 */
function validarFechaVolverLlamar() {
    const fechaInput = document.getElementById('fecha_proximo_contacto');
    const fecha = new Date(fechaInput.value);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    // Remover mensajes de error previos
    limpiarMensajesError(fechaInput);
    
    if (fecha <= hoy) {
        mostrarErrorCampo(fechaInput, 'La fecha debe ser futura (a partir de mañana)');
        return false;
    }
    
    // Validar que sea día laboral (lunes a sábado)
    const diaSemana = fecha.getDay();
    if (diaSemana === 0) { // Domingo
        mostrarErrorCampo(fechaInput, 'No se pueden programar llamadas los domingos');
        return false;
    }
    
    return true;
}

/**
 * Validar hora para volver a llamar
 */
function validarHoraVolverLlamar() {
    const horaInput = document.getElementById('hora_proximo_contacto');
    const hora = horaInput.value;
    
    // Remover mensajes de error previos
    limpiarMensajesError(horaInput);
    
    if (!hora) return true;
    
    const [horaStr, minutoStr] = hora.split(':');
    const horaNum = parseInt(horaStr);
    const minutoNum = parseInt(minutoStr);
    const horaEnMinutos = horaNum * 60 + minutoNum;
    
    const horaInicio = 7 * 60 + 30; // 7:30 AM
    const horaFin = 18 * 60; // 6:00 PM
    
    if (horaEnMinutos < horaInicio || horaEnMinutos > horaFin) {
        mostrarErrorCampo(horaInput, 'El horario debe estar entre 7:30 AM y 6:00 PM');
        return false;
    }
    
    return true;
}

/**
 * Mostrar error en un campo específico
 */
function mostrarErrorCampo(campo, mensaje) {
    // Remover error previo
    limpiarMensajesError(campo);
    
    // Crear elemento de error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '4px';
    errorDiv.textContent = mensaje;
    
    // Insertar después del campo
    campo.parentNode.insertBefore(errorDiv, campo.nextSibling);
    
    // Agregar clase de error al campo
    campo.classList.add('error');
}

/**
 * Limpiar mensajes de error de un campo
 */
function limpiarMensajesError(campo) {
    // Remover clase de error
    campo.classList.remove('error');
    
    // Remover mensajes de error existentes
    const errorDiv = campo.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

console.log('Script de gestión de cliente cargado correctamente');
