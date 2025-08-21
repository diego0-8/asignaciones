/**
 * JavaScript del Panel de Administrador
 * Maneja todas las interacciones AJAX del dashboard
 */

class AdminDashboard {
    constructor() {
        this.initializeEventListeners();
        this.loadEstadisticas();
    }
    
    /**
     * Inicializar event listeners
     */
    initializeEventListeners() {
        // Modal de crear usuario
        const formCrearUsuario = document.getElementById('form-crear-usuario');
        if (formCrearUsuario) {
            formCrearUsuario.addEventListener('submit', (e) => this.crearUsuario(e));
        }
        
        // Modal de asignar personal
        const formAsignarPersonal = document.getElementById('form-asignar-personal');
        if (formAsignarPersonal) {
            formAsignarPersonal.addEventListener('submit', (e) => this.asignarPersonal(e));
        }
        
        // Botones de liberar asesor
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-liberar-asesor')) {
                this.liberarAsesor(e.target.dataset.asesorId);
            }
        });
        
        // Botones de cerrar modal
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('close-modal')) {
                this.closeModal(e.target.closest('.modal').id);
            }
        });
        
        // Cerrar modal al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target.id);
            }
        });
    }
    
    /**
     * Cargar estadísticas del dashboard
     */
    async loadEstadisticas() {
        try {
            const response = await fetch('index.php?action=get_estadisticas', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.actualizarEstadisticas(data.data);
            }
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
        }
    }
    
    /**
     * Actualizar estadísticas en el dashboard
     */
    actualizarEstadisticas(data) {
        const { estadisticas, coordinadores, asesores_sin_coordinador, asesores_con_coordinador } = data;
        
        // Actualizar contadores
        if (estadisticas) {
            this.actualizarContador('total-usuarios', estadisticas.total_usuarios || 0);
            this.actualizarContador('usuarios-activos', estadisticas.usuarios_activos || 0);
            this.actualizarContador('total-coordinadores', estadisticas.total_coordinadores || 0);
            this.actualizarContador('total-asesores', estadisticas.total_asesores || 0);
            this.actualizarContador('asesores-asignados', estadisticas.asesores_asignados || 0);
            this.actualizarContador('asesores-sin-coordinador', estadisticas.asesores_sin_coordinador || 0);
        }
        
        // Actualizar listas
        this.actualizarListaCoordinadores(coordinadores);
        this.actualizarListaAsesoresSinCoordinador(asesores_sin_coordinador);
        this.actualizarListaAsesoresConCoordinador(asesores_con_coordinador);
    }
    
    /**
     * Actualizar contador específico
     */
    actualizarContador(id, valor) {
        const elemento = document.getElementById(id);
        if (elemento) {
            elemento.textContent = valor;
        }
    }
    
    /**
     * Actualizar lista de coordinadores
     */
    actualizarListaCoordinadores(coordinadores) {
        const select = document.getElementById('coordinador_id');
        if (select && coordinadores) {
            select.innerHTML = '<option value="">Sin asignar</option>';
            coordinadores.forEach(coord => {
                const option = document.createElement('option');
                option.value = coord.id;
                option.textContent = coord.nombre_completo;
                select.appendChild(option);
            });
        }
    }
    
    /**
     * Actualizar lista de asesores sin coordinador
     */
    actualizarListaAsesoresSinCoordinador(asesores) {
        const select = document.getElementById('asesor_id');
        if (select && asesores) {
            select.innerHTML = '<option value="">Seleccionar asesor</option>';
            asesores.forEach(asesor => {
                const option = document.createElement('option');
                option.value = asesor.id;
                option.textContent = asesor.nombre_completo;
                select.appendChild(option);
            });
        }
    }
    
    /**
     * Actualizar lista de asesores con coordinador
     */
    actualizarListaAsesoresConCoordinador(asesores) {
        const container = document.getElementById('asesores-asignados-container');
        if (container && asesores) {
            container.innerHTML = '';
            
            asesores.forEach(asesor => {
                const card = document.createElement('div');
                card.className = 'asesor-card';
                card.innerHTML = `
                    <div class="asesor-info">
                        <h4>${asesor.nombre_completo}</h4>
                        <p><strong>Coordinador:</strong> ${asesor.coordinador_nombre || 'Sin asignar'}</p>
                    </div>
                    <div class="asesor-actions">
                        <button class="btn btn-warning btn-sm btn-liberar-asesor" 
                                data-asesor-id="${asesor.id}">
                            <i class="fas fa-unlink"></i> Liberar
                        </button>
                    </div>
                `;
                container.appendChild(card);
            });
        }
    }
    
    /**
     * Crear usuario
     */
    async crearUsuario(event) {
        event.preventDefault();
        
        const form = event.target;
        const btnCrear = form.querySelector('button[type="submit"]');
        const alertContainer = document.getElementById('alert-container-crear');
        
        // Validar formulario
        if (!this.validarFormulario(form)) {
            return;
        }
        
        // Deshabilitar botón y mostrar loading
        btnCrear.disabled = true;
        btnCrear.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';
        
        // Limpiar alertas anteriores
        alertContainer.innerHTML = '';
        
        try {
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            const response = await fetch('index.php?action=create_usuario', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta(data.message, 'success', 'crear-usuario');
                form.reset();
                
                // Recargar estadísticas
                this.loadEstadisticas();
                
                // Cerrar modal después de 2 segundos
                setTimeout(() => {
                    this.closeModal('crear-usuario');
                }, 2000);
            } else {
                this.mostrarAlerta(data.message, 'error', 'crear-usuario');
            }
            
        } catch (error) {
            this.mostrarAlerta('Error de conexión: ' + error.message, 'error', 'crear-usuario');
        } finally {
            // Restaurar botón
            btnCrear.disabled = false;
            btnCrear.innerHTML = '<i class="fas fa-user-plus"></i> Crear Usuario';
        }
    }
    
    /**
     * Asignar personal
     */
    async asignarPersonal(event) {
        event.preventDefault();
        
        const form = event.target;
        const btnAsignar = form.querySelector('button[type="submit"]');
        const alertContainer = document.getElementById('alert-container-asignar');
        
        // Validar formulario
        if (!this.validarFormulario(form)) {
            return;
        }
        
        // Deshabilitar botón y mostrar loading
        btnAsignar.disabled = true;
        btnAsignar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Asignando...';
        
        // Limpiar alertas anteriores
        alertContainer.innerHTML = '';
        
        try {
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            const response = await fetch('index.php?action=asignar_asesor', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta(data.message, 'success', 'asignar-personal');
                form.reset();
                
                // Recargar estadísticas
                this.loadEstadisticas();
                
                // Cerrar modal después de 2 segundos
                setTimeout(() => {
                    this.closeModal('asignar-personal');
                }, 2000);
            } else {
                this.mostrarAlerta(data.message, 'error', 'asignar-personal');
            }
            
        } catch (error) {
            this.mostrarAlerta('Error de conexión: ' + error.message, 'error', 'asignar-personal');
        } finally {
            // Restaurar botón
            btnAsignar.disabled = false;
            btnAsignar.innerHTML = '<i class="fas fa-user-friends"></i> Asignar';
        }
    }
    
    /**
     * Liberar asesor
     */
    async liberarAsesor(asesorId) {
        if (!confirm('¿Estás seguro de que quieres liberar este asesor?')) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('asesor_id', asesorId);
            formData.append('ajax', '1');
            
            const response = await fetch('index.php?action=liberar_asesor', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta(data.message, 'success', 'dashboard');
                
                // Recargar estadísticas
                this.loadEstadisticas();
            } else {
                this.mostrarAlerta(data.message, 'error', 'dashboard');
            }
            
        } catch (error) {
            this.mostrarAlerta('Error de conexión: ' + error.message, 'error', 'dashboard');
        }
    }
    
    /**
     * Validar formulario
     */
    validarFormulario(form) {
        const inputs = form.querySelectorAll('input[required], select[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            } else {
                input.classList.remove('error');
            }
        });
        
        return isValid;
    }
    
    /**
     * Mostrar alerta
     */
    mostrarAlerta(mensaje, tipo, modalId) {
        const alertContainer = document.getElementById(`alert-container-${modalId.split('-')[0]}`);
        if (!alertContainer) return;
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${tipo}`;
        alertDiv.innerHTML = `
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            ${mensaje}
        `;
        
        alertContainer.appendChild(alertDiv);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    /**
     * Abrir modal
     */
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
        }
    }
    
    /**
     * Cerrar modal
     */
    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    new AdminDashboard();
});
