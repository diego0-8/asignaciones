/**
 * JavaScript para la gestión de clientes del asesor
 * Maneja la lógica de formularios, validaciones y envío de datos
 */

// Variables globales
let currentClienteId = null;
let currentAsesorId = null;

// Inicialización cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando gestión de cliente...');
    
    // Obtener IDs del formulario
    const clienteIdInput = document.querySelector('input[name="cliente_id"]');
    if (clienteIdInput) {
        currentClienteId = clienteIdInput.value;
        console.log('Cliente ID:', currentClienteId);
    }
    
    // Inicializar contador de caracteres
    initCharCounter();
    
    // Configurar eventos del formulario
    setupFormEvents();
    
    console.log('Gestión de cliente inicializada correctamente');
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
    const tipoContacto = document.getElementById('tipo_contacto').value;
    const opcionesGestion = document.getElementById('opciones_gestion');
    const tipoGestionSelect = document.getElementById('tipo_gestion');
    
    if (tipoContacto === 'contactado') {
        opcionesGestion.style.display = 'block';
        opcionesGestion.classList.add('fade-in');
        // Agregar required cuando se muestra
        if (tipoGestionSelect) {
            tipoGestionSelect.setAttribute('required', 'required');
        }
    } else {
        opcionesGestion.style.display = 'none';
        opcionesGestion.classList.remove('fade-in');
        
        // Quitar required cuando se oculta
        if (tipoGestionSelect) {
            tipoGestionSelect.removeAttribute('required');
            tipoGestionSelect.value = ''; // Limpiar valor
        }
        
        // Ocultar campos específicos también
        ocultarTodosLosCampos();
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
            document.getElementById('campos_cita').style.display = 'block';
            document.getElementById('campos_cita').classList.add('fade-in');
            break;
            
        case 'volver_llamar':
            document.getElementById('campos_volver_llamar').style.display = 'block';
            document.getElementById('campos_volver_llamar').classList.add('fade-in');
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
            break;
    }
    
    return true;
}

/**
 * Procesar la gestión del cliente
 */
function procesarGestion(event) {
    event.preventDefault();
    
    console.log('🔍 Procesando gestión del cliente...');
    console.log('📝 Evento:', event);
    
    // Validar formulario
    if (!validarFormulario()) {
        console.log('❌ Validación falló');
        return false;
    }
    
    console.log('✅ Validación exitosa, mostrando indicador de carga...');
    
    // Mostrar indicador de carga
    mostrarCargando();
    
    // Recopilar datos del formulario
    const formData = new FormData(event.target);
    
    // Agregar datos adicionales
    formData.append('asesor_id', getAsesorId());
    formData.append('fecha_gestion', new Date().toISOString());
    
    console.log('📤 Enviando datos:', Object.fromEntries(formData));
    
    // Enviar datos al servidor
    enviarGestion(formData);
    
    return false;
}

/**
 * Enviar gestión al servidor
 */
function enviarGestion(formData) {
    console.log('Enviando gestión al servidor...');
    
    fetch('index.php?action=asesor_guardar_gestion', {
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
        console.log('Respuesta del servidor:', data);
        
        if (data.success) {
            mostrarExito(data.message || 'Gestión guardada correctamente');
            
            // Limpiar formulario
            limpiarFormulario();
            
            // Recargar historial si existe
            if (typeof recargarHistorial === 'function') {
                recargarHistorial();
            }
        } else {
            mostrarError(data.error || 'Error al guardar la gestión');
        }
    })
    .catch(error => {
        console.error('Error al enviar gestión:', error);
        mostrarError('Error de conexión: ' + error.message);
    })
    .finally(() => {
        ocultarCargando();
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

console.log('Script de gestión de cliente cargado correctamente');
