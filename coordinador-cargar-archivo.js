/**
 * JavaScript para la carga de archivos CSV del Coordinador
 * Maneja la lógica de carga, validación y procesamiento de archivos
 */

// Variables globales
let isUploading = false;
let uploadProgress = 0;

// Inicialización cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM cargado, inicializando carga de archivos...');
    
    try {
        // Configurar eventos del formulario
        setupFormEvents();
        console.log('✅ Eventos del formulario configurados');
        
        // Verificar si ya existe una base de datos
        verificarBaseExistente();
        console.log('✅ Verificación de base existente completada');
        
        console.log('🎉 Carga de archivos inicializada correctamente');
    } catch (error) {
        console.error('❌ Error durante la inicialización:', error);
    }
});

/**
 * Configurar eventos del formulario
 */
function setupFormEvents() {
    console.log('🔧 Configurando eventos del formulario...');
    
    const uploadForm = document.getElementById('uploadForm');
    const archivoInput = document.getElementById('archivo_csv');
    const nombreCargaInput = document.getElementById('nombre_carga');
    
    console.log('📝 Elementos encontrados:', {
        uploadForm: !!uploadForm,
        archivoInput: !!archivoInput,
        nombreCargaInput: !!nombreCargaInput
    });
    
    if (uploadForm) {
        uploadForm.addEventListener('submit', handleFileUpload);
        console.log('✅ Evento submit configurado en uploadForm');
    } else {
        console.error('❌ No se encontró el formulario uploadForm');
    }
    
    if (archivoInput) {
        archivoInput.addEventListener('change', handleFileSelection);
        console.log('✅ Evento change configurado en archivoInput');
    } else {
        console.error('❌ No se encontró el input archivo_csv');
    }
    
    // Solo configurar eventos para nombre de carga si existe (primera carga)
    if (nombreCargaInput) {
        nombreCargaInput.addEventListener('input', handleNombreCargaChange);
        console.log('✅ Evento input configurado en nombreCargaInput');
    } else {
        console.log('ℹ️ Campo nombre_carga no encontrado (base existente)');
    }
    
    console.log('✅ Configuración de eventos completada');
}

/**
 * Manejar selección de archivo
 */
function handleFileSelection(event) {
    const file = event.target.files[0];
    if (file) {
        // Validar tipo de archivo
        if (file.type !== 'text/csv' && !file.name.toLowerCase().endsWith('.csv')) {
            mostrarAlerta('❌ Solo se permiten archivos CSV', 'error');
            event.target.value = '';
            return;
        }
        
        // Validar tamaño (máximo 500MB)
        const maxSize = 500 * 1024 * 1024; // 500MB
        if (file.size > maxSize) {
            mostrarAlerta('❌ El archivo es demasiado grande. Máximo 500MB', 'error');
            event.target.value = '';
            return;
        }
        
        // Mostrar información del archivo
        mostrarInfoArchivo(file);
        
        // Habilitar botón de envío
        const submitBtn = document.querySelector('#uploadForm .btn-primary');
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    }
}

/**
 * Mostrar información del archivo seleccionado
 */
function handleNombreCargaChange(event) {
    const nombre = event.target.value.trim();
    const nombreCargaGroup = document.getElementById('nombreCargaGroup');
    
    if (nombre.length > 0) {
        nombreCargaGroup.classList.add('has-value');
    } else {
        nombreCargaGroup.classList.remove('has-value');
    }
}

/**
 * Mostrar información del archivo seleccionado
 */
function mostrarInfoArchivo(file) {
    const fileInfo = document.createElement('div');
    fileInfo.className = 'file-info';
    fileInfo.innerHTML = `
        <div class="file-details">
            <i class="fas fa-file-csv"></i>
            <div class="file-text">
                <strong>${file.name}</strong>
                <small>${formatFileSize(file.size)}</small>
            </div>
        </div>
    `;
    
    // Remover información anterior si existe
    const existingInfo = document.querySelector('.file-info');
    if (existingInfo) {
        existingInfo.remove();
    }
    
    // Insertar después del input de archivo
    const archivoInput = document.getElementById('archivo_csv');
    archivoInput.parentNode.appendChild(fileInfo);
}

/**
 * Formatear tamaño de archivo
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Manejar envío del formulario
 */
function handleFileUpload(event) {
    event.preventDefault();
    
    console.log('🔄 Iniciando proceso de carga de archivo...');
    
    if (isUploading) {
        mostrarAlerta('❌ Ya hay una carga en progreso', 'error');
        return;
    }
    
    const formData = new FormData();
    const archivo = document.getElementById('archivo_csv').files[0];
    const nombreCargaInput = document.getElementById('nombre_carga');
    const nombreCarga = nombreCargaInput ? nombreCargaInput.value.trim() : '';
    
    console.log('📁 Archivo seleccionado:', archivo ? archivo.name : 'Ninguno');
    console.log('🏷️ Campo nombre de carga existe:', !!nombreCargaInput);
    console.log('📝 Nombre de carga:', nombreCarga);
    
    // Validaciones
    if (!archivo) {
        mostrarAlerta('❌ Por favor selecciona un archivo CSV', 'error');
        return;
    }
    
    // Solo validar nombre de carga si es la primera carga
    if (nombreCargaInput && !nombreCarga) {
        mostrarAlerta('❌ Por favor ingresa un nombre para la carga', 'error');
        return;
    }
    
    // Confirmar carga
    let mensajeConfirmacion = `¿Estás seguro de que quieres cargar el archivo "${archivo.name}"?\n\n`;
    if (nombreCargaInput) {
        mensajeConfirmacion += `Nombre de la carga: ${nombreCarga}\n\n`;
    } else {
        mensajeConfirmacion += `Los nuevos clientes se agregarán a tu base existente.\n\n`;
    }
    mensajeConfirmacion += `Esta acción puede tomar varios minutos dependiendo del tamaño del archivo.`;
    
    if (!confirm(mensajeConfirmacion)) {
        console.log('❌ Usuario canceló la carga');
        return;
    }
    
    // Preparar datos
    formData.append('archivo', archivo);
    if (nombreCargaInput) {
        formData.append('nombre_carga', nombreCarga);
    }
    
    console.log('✅ Datos preparados, iniciando carga...');
    
    // Iniciar carga
    iniciarCarga(formData);
}

/**
 * Iniciar proceso de carga
 */
function iniciarCarga(formData) {
    console.log('🚀 Iniciando carga al servidor...');
    
    isUploading = true;
    
    // Mostrar progreso
    mostrarProgreso(true);
    
    // Deshabilitar formulario
    const form = document.getElementById('uploadForm');
    const submitBtn = form.querySelector('.btn-primary');
    
    form.style.pointerEvents = 'none';
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
    
    // Simular progreso
    simularProgreso();
    
    console.log('📤 Enviando archivo al servidor...');
    
    // Realizar carga
    fetch('index.php?action=coordinador_procesar_archivo', {
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
        console.log('Respuesta de carga:', data);
        
        if (data.success) {
            // Mostrar alerta de éxito
            mostrarAlerta(
                `Se procesaron ${data.clientes_procesados || 0} clientes exitosamente. ${data.errores && data.errores.length > 0 ? `Con ${data.errores.length} errores.` : ''}`,
                'success',
                '✅ Carga Completada'
            );
            
            // Mostrar resultados exitosos
            mostrarResultadosExitosos(data);
            
            // Limpiar formulario
            limpiarFormulario();
            
            // Actualizar información de base
            actualizarInfoBase();
            
        } else {
            mostrarAlerta(
                `Error en la carga: ${data.error || 'Error desconocido'}`,
                'error',
                '❌ Error de Carga'
            );
        }
    })
    .catch(error => {
        console.error('Error en carga:', error);
        mostrarAlerta(
            `Error de conexión: ${error.message}`,
            'error',
            '❌ Error de Conexión'
        );
    })
    .finally(() => {
        // Finalizar carga
        finalizarCarga();
    });
}

/**
 * Simular progreso de carga
 */
function simularProgreso() {
    uploadProgress = 0;
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    const interval = setInterval(() => {
        if (uploadProgress < 90) {
            uploadProgress += Math.random() * 10;
            if (progressFill) progressFill.style.width = uploadProgress + '%';
            if (progressText) progressText.textContent = Math.round(uploadProgress) + '%';
        } else {
            clearInterval(interval);
        }
    }, 500);
}

/**
 * Mostrar resultados exitosos
 */
function mostrarResultadosExitosos(data) {
    const resultsSection = document.getElementById('uploadResults');
    const resultsContent = document.getElementById('resultsContent');
    
    if (resultsSection && resultsContent) {
        resultsContent.innerHTML = `
            <div class="results-success">
                <div class="result-header">
                    <i class="fas fa-check-circle"></i>
                    <h4>✅ Carga Completada Exitosamente</h4>
                </div>
                
                <div class="result-stats">
                    <div class="stat-item">
                        <span class="stat-label">Clientes Procesados:</span>
                        <span class="stat-value success">${data.clientes_procesados || 0}</span>
                    </div>
                    ${data.errores && data.errores.length > 0 ? `
                        <div class="stat-item">
                            <span class="stat-label">Errores:</span>
                            <span class="stat-value error">${data.errores.length}</span>
                        </div>
                    ` : ''}
                </div>
                
                ${data.errores && data.errores.length > 0 ? `
                    <div class="result-errors">
                        <h5>Detalles de Errores:</h5>
                        <div class="errors-list">
                            ${data.errores.slice(0, 10).map(error => `<div class="error-item">• ${error}</div>`).join('')}
                            ${data.errores.length > 10 ? `<div class="error-item">... y ${data.errores.length - 10} errores más</div>` : ''}
                        </div>
                    </div>
                ` : ''}
                
                <div class="result-actions">
                    <button class="btn btn-primary" onclick="window.location.reload()">
                        <i class="fas fa-plus"></i> Cargar Otro Archivo
                    </button>
                    <a href="index.php?action=coordinador_dashboard" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt"></i> Ir al Dashboard
                    </a>
                </div>
            </div>
        `;
        
        resultsSection.style.display = 'block';
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }
}

/**
 * Mostrar progreso
 */
function mostrarProgreso(mostrar) {
    const progressSection = document.getElementById('uploadProgress');
    if (progressSection) {
        progressSection.style.display = mostrar ? 'block' : 'none';
    }
}

/**
 * Finalizar carga
 */
function finalizarCarga() {
    isUploading = false;
    uploadProgress = 100;
    
    // Ocultar progreso
    mostrarProgreso(false);
    
    // Restaurar formulario
    const form = document.getElementById('uploadForm');
    const submitBtn = form.querySelector('.btn-primary');
    
    form.style.pointerEvents = 'auto';
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-upload"></i> Cargar Archivo';
    
    // Completar barra de progreso
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    if (progressFill) progressFill.style.width = '100%';
    if (progressText) progressText.textContent = '100%';
}

/**
 * Limpiar formulario
 */
function limpiarFormulario() {
    const form = document.getElementById('uploadForm');
    form.reset();
    
    // Limpiar información del archivo
    const existingInfo = document.querySelector('.file-info');
    if (existingInfo) {
        existingInfo.remove();
    }
    
    // Limpiar nombre de carga solo si existe
    const nombreCargaGroup = document.getElementById('nombreCargaGroup');
    if (nombreCargaGroup) {
        nombreCargaGroup.classList.remove('has-value');
    }
    
    // Remover mensajes de base existente si existen
    const mensajesBase = document.querySelectorAll('.base-existente-info');
    mensajesBase.forEach(mensaje => mensaje.remove());
}

/**
 * Verificar si ya existe una base de datos
 */
function verificarBaseExistente() {
    const baseInfo = document.getElementById('baseInfo');
    const nombreBaseActual = document.getElementById('nombreBaseActual');
    const nombreCargaInput = document.getElementById('nombre_carga');
    
    // Si no hay campo de nombre de carga, significa que ya existe una base
    if (!nombreCargaInput && baseInfo) {
        // La base ya existe, mostrar información
        baseInfo.style.display = 'block';
    } else if (nombreCargaInput && baseInfo) {
        // Es la primera carga, simular verificación
        setTimeout(() => {
            nombreBaseActual.textContent = 'Nueva base (primera carga)';
            baseInfo.style.display = 'block';
        }, 1000);
    }
}

/**
 * Actualizar información de base después de carga
 */
function actualizarInfoBase() {
    const baseInfo = document.getElementById('baseInfo');
    const nombreBaseActual = document.getElementById('nombreBaseActual');
    
    if (baseInfo && nombreBaseActual) {
        nombreBaseActual.textContent = 'Base activa';
        baseInfo.style.display = 'block';
        
        // Ocultar campo de nombre de carga si existe
        const nombreCargaInput = document.getElementById('nombre_carga');
        if (nombreCargaInput) {
            nombreCargaInput.parentElement.style.display = 'none';
        }
        
        // Mostrar mensaje de base existente
        mostrarMensajeBaseExistente();
    }
}

/**
 * Mostrar mensaje de que la base ya existe
 */
function mostrarMensajeBaseExistente() {
    const form = document.getElementById('uploadForm');
    if (form) {
        const mensaje = document.createElement('div');
        mensaje.className = 'base-existente-info';
        mensaje.innerHTML = `
            <div class="info-card">
                <i class="fas fa-database"></i>
                <div class="info-content">
                    <h4>Base de Datos Actualizada</h4>
                    <p>Los nuevos clientes se han agregado a tu base existente</p>
                    <small>Para futuras cargas no será necesario especificar un nombre</small>
                </div>
            </div>
        `;
        
        // Insertar después del formulario
        form.parentNode.insertBefore(mensaje, form.nextSibling);
    }
}

/**
 * Mostrar alerta mejorada
 */
function mostrarAlerta(mensaje, tipo = 'info', titulo = '') {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${tipo === 'error' ? 'danger' : tipo}`;
    
    const alertContent = titulo ? 
        `<div class="alert-content">
            <div class="alert-title">${titulo}</div>
            <div class="alert-message">${mensaje}</div>
        </div>` : 
        `<div class="alert-content">
            <div class="alert-message">${mensaje}</div>
        </div>`;
    
    alert.innerHTML = `
        ${alertContent}
        <button type="button" class="close" onclick="cerrarAlerta(this.parentElement)">&times;</button>
    `;
    
    alertContainer.appendChild(alert);
    
    // Auto-remover después de 8 segundos
    setTimeout(() => {
        if (alert.parentElement) {
            cerrarAlerta(alert);
        }
    }, 8000);
}

/**
 * Cerrar alerta con animación
 */
function cerrarAlerta(alert) {
    if (!alert) return;
    
    alert.classList.add('removing');
    setTimeout(() => {
        if (alert.parentElement) {
            alert.remove();
        }
    }, 300);
}

console.log('🎯 Script de carga de archivos cargado correctamente');
console.log('📍 URL actual:', window.location.href);
console.log('🔍 Verificando elementos del DOM...');



// Verificación inmediata de elementos clave
setTimeout(() => {
    const uploadForm = document.getElementById('uploadForm');
    const archivoInput = document.getElementById('archivo_csv');
    
    console.log('🔍 Verificación post-carga:', {
        uploadForm: !!uploadForm,
        archivoInput: !!archivoInput,
        uploadFormId: uploadForm ? uploadForm.id : 'NO ENCONTRADO',
        archivoInputId: archivoInput ? archivoInput.id : 'NO ENCONTRADO'
    });
    
    if (uploadForm) {
        console.log('✅ Formulario encontrado, verificando eventos...');
        const events = uploadForm.onsubmit;
        console.log('📝 Evento submit del formulario:', events);
        
        // Verificar si ya tiene el evento configurado
        if (!uploadForm.hasAttribute('data-events-configured')) {
            console.log('⚠️ Eventos no configurados, configurando manualmente...');
            setupFormEvents();
            uploadForm.setAttribute('data-events-configured', 'true');
        }
    }
}, 1000);

// Fallback adicional para asegurar que los eventos se configuren
window.addEventListener('load', function() {
    console.log('🌐 Página completamente cargada, verificando eventos...');
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm && !uploadForm.hasAttribute('data-events-configured')) {
        console.log('🔄 Configurando eventos en fallback...');
        setupFormEvents();
        uploadForm.setAttribute('data-events-configured', 'true');
    }
});
