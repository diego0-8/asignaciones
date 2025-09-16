/**
 * JavaScript para la gestión de usuarios del administrador
 * Maneja todas las interacciones AJAX de la página de usuarios
 */

class AdminUsuarios {
    constructor() {
        this.currentPage = 1;
        this.totalPages = 1;
        this.totalUsers = 0;
        this.initializeEventListeners();
        this.loadUsuarios();
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
        
        // Modal de editar usuario
        const formEditarUsuario = document.getElementById('form-editar-usuario');
        if (formEditarUsuario) {
            formEditarUsuario.addEventListener('submit', (e) => this.editarUsuario(e));
        }
        
        // Filtros de búsqueda
        const buscarNombre = document.getElementById('buscar-nombre');
        if (buscarNombre) {
            buscarNombre.addEventListener('input', (e) => this.filtrarUsuarios());
        }
        
        const filtroRol = document.getElementById('filtro-rol');
        if (filtroRol) {
            filtroRol.addEventListener('change', (e) => this.filtrarUsuarios());
        }
        
        const filtroEstado = document.getElementById('filtro-estado');
        if (filtroEstado) {
            filtroEstado.addEventListener('change', (e) => this.filtrarUsuarios());
        }
        
        // Botones de cerrar modal
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('close-btn')) {
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
     * Cargar usuarios
     */
    async loadUsuarios(page = 1) {
        try {
            const search = document.getElementById('buscar-nombre')?.value || '';
            const rolFilter = document.getElementById('filtro-rol')?.value || '';
            const estadoFilter = document.getElementById('filtro-estado')?.value || '';
            
            const params = new URLSearchParams({
                page: page,
                limit: 10,
                search: search,
                rol_filter: rolFilter,
                estado_filter: estadoFilter
            });
            
            const response = await fetch(`index.php?action=get_usuarios&${params}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.currentPage = data.page;
                this.totalPages = data.total_pages;
                this.totalUsers = data.total;
                this.actualizarTablaUsuarios(data.data);
                this.actualizarPaginacion();
            }
        } catch (error) {
            console.error('Error al cargar usuarios:', error);
        }
    }
    
    /**
     * Actualizar tabla de usuarios
     */
    actualizarTablaUsuarios(usuarios) {
        const tbody = document.querySelector('.users-table tbody');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (usuarios.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="no-data">No hay usuarios registrados</td></tr>';
            return;
        }
        
        usuarios.forEach(usuario => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${usuario.id}</td>
                <td>${usuario.nombre_completo}</td>
                <td>${usuario.usuario}</td>
                <td>
                    <span class="role-badge role-${usuario.rol}">
                        ${usuario.rol.charAt(0).toUpperCase() + usuario.rol.slice(1)}
                    </span>
                </td>
                <td>
                    <span class="status-badge status-${usuario.estado || 'activo'}">
                        ${(usuario.estado || 'activo').charAt(0).toUpperCase() + (usuario.estado || 'activo').slice(1)}
                    </span>
                </td>
                <td>
                    ${usuario.coordinador_nombre || '<span class="text-muted">-</span>'}
                </td>
                <td class="actions-cell">
                    <div class="action-buttons">
                        <button class="action-btn edit-btn" onclick="adminUsuarios.editarUsuario(${usuario.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${(usuario.estado || 'activo') === 'activo' ? 
                            `<button class="action-btn disable-btn" onclick="adminUsuarios.deshabilitarUsuario(${usuario.id})" title="Deshabilitar">
                                <i class="fas fa-user-slash"></i>
                            </button>` :
                            `<button class="action-btn enable-btn" onclick="adminUsuarios.habilitarUsuario(${usuario.id})" title="Habilitar">
                                <i class="fas fa-user-check"></i>
                            </button>`
                        }
                        <button class="action-btn delete-btn" onclick="adminUsuarios.eliminarUsuario(${usuario.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    /**
     * Filtrar usuarios
     */
    filtrarUsuarios() {
        this.currentPage = 1;
        this.loadUsuarios(1);
    }
    
    /**
     * Actualizar paginación
     */
    actualizarPaginacion() {
        const pagination = document.getElementById('pagination');
        if (!pagination) return;
        
        if (this.totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }
        
        let html = '';
        
        // Botón anterior
        if (this.currentPage > 1) {
            html += `<a href="#" class="page-link" onclick="adminUsuarios.loadUsuarios(${this.currentPage - 1}); return false;">
                        <i class="fas fa-chevron-left"></i>
                     </a>`;
        }
        
        // Números de página
        const startPage = Math.max(1, this.currentPage - 2);
        const endPage = Math.min(this.totalPages, this.currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<a href="#" class="page-link ${i === this.currentPage ? 'active' : ''}" 
                        onclick="adminUsuarios.loadUsuarios(${i}); return false;">
                        ${i}
                     </a>`;
        }
        
        // Botón siguiente
        if (this.currentPage < this.totalPages) {
            html += `<a href="#" class="page-link" onclick="adminUsuarios.loadUsuarios(${this.currentPage + 1}); return false;">
                        <i class="fas fa-chevron-right"></i>
                     </a>`;
        }
        
        pagination.innerHTML = html;
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
                
                // Recargar usuarios
                this.loadUsuarios();
                
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
     * Editar usuario
     */
    async editarUsuario(usuarioId) {
        try {
            // Obtener datos del usuario
            const response = await fetch(`index.php?action=get_usuario&id=${usuarioId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const usuario = data.data;
                
                // Llenar formulario de edición
                document.getElementById('edit_id').value = usuario.id;
                document.getElementById('edit_nombre').value = usuario.nombre_completo;
                document.getElementById('edit_cedula').value = usuario.cedula;
                document.getElementById('edit_usuario').value = usuario.usuario;
                document.getElementById('edit_rol').value = usuario.rol;
                document.getElementById('edit_coordinador_id').value = usuario.coordinador_id || '';
                
                // Mostrar/ocultar campo coordinador según el rol
                this.toggleEditCoordinadorField();
                
                // Abrir modal
                this.openModal('editar-usuario');
            } else {
                this.mostrarAlerta(data.message, 'error', 'usuarios');
            }
        } catch (error) {
            this.mostrarAlerta('Error de conexión: ' + error.message, 'error', 'usuarios');
        }
    }
    
    /**
     * Actualizar usuario
     */
    async actualizarUsuario(event) {
        event.preventDefault();
        
        const form = event.target;
        const btnActualizar = form.querySelector('button[type="submit"]');
        
        // Validar formulario
        if (!this.validarFormulario(form)) {
            return;
        }
        
        // Deshabilitar botón y mostrar loading
        btnActualizar.disabled = true;
        btnActualizar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        
        try {
            const formData = new FormData(form);
            formData.append('ajax', '1');
            
            const response = await fetch('index.php?action=update_usuario', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta(data.message, 'success', 'usuarios');
                
                // Recargar usuarios
                this.loadUsuarios();
                
                // Cerrar modal
                this.closeModal('editar-usuario');
            } else {
                this.mostrarAlerta(data.message, 'error', 'usuarios');
            }
            
        } catch (error) {
            this.mostrarAlerta('Error de conexión: ' + error.message, 'error', 'usuarios');
        } finally {
            // Restaurar botón
            btnActualizar.disabled = false;
            btnActualizar.innerHTML = 'Actualizar Usuario';
        }
    }
    
    /**
     * Eliminar usuario
     */
    async eliminarUsuario(usuarioId) {
        if (!confirm('¿Estás seguro de que quieres eliminar este usuario? Esta acción no se puede deshacer.')) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('id', usuarioId);
            formData.append('ajax', '1');
            
            const response = await fetch('index.php?action=delete_usuario', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta(data.message, 'success', 'usuarios');
                
                // Recargar usuarios
                this.loadUsuarios();
            } else {
                this.mostrarAlerta(data.message, 'error', 'usuarios');
            }
            
        } catch (error) {
            this.mostrarAlerta('Error de conexión: ' + error.message, 'error', 'usuarios');
        }
    }
    
    /**
     * Deshabilitar usuario
     */
    async deshabilitarUsuario(usuarioId) {
        if (!confirm('¿Estás seguro de que quieres deshabilitar este usuario?')) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('id', usuarioId);
            formData.append('ajax', '1');
            
            const response = await fetch('index.php?action=toggle_estado_usuario', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta(data.message, 'success', 'usuarios');
                
                // Recargar usuarios
                this.loadUsuarios();
            } else {
                this.mostrarAlerta(data.message, 'error', 'usuarios');
            }
            
        } catch (error) {
            this.mostrarAlerta('Error de conexión: ' + error.message, 'error', 'usuarios');
        }
    }
    
    /**
     * Habilitar usuario
     */
    async habilitarUsuario(usuarioId) {
        if (!confirm('¿Estás seguro de que quieres habilitar este usuario?')) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('id', usuarioId);
            formData.append('ajax', '1');
            
            const response = await fetch('index.php?action=toggle_estado_usuario', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.mostrarAlerta(data.message, 'success', 'usuarios');
                
                // Recargar usuarios
                this.loadUsuarios();
            } else {
                this.mostrarAlerta(data.message, 'error', 'usuarios');
            }
            
        } catch (error) {
            this.mostrarAlerta('Error de conexión: ' + error.message, 'error', 'usuarios');
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
    mostrarAlerta(mensaje, tipo, contexto) {
        // Crear contenedor de alertas si no existe
        let alertContainer = document.getElementById('alert-container-' + contexto);
        if (!alertContainer) {
            alertContainer = document.createElement('div');
            alertContainer.id = 'alert-container-' + contexto;
            alertContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
            document.body.appendChild(alertContainer);
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${tipo}`;
        alertDiv.style.cssText = 'margin-bottom: 10px; padding: 15px; border-radius: 5px; color: white;';
        
        if (tipo === 'success') {
            alertDiv.style.backgroundColor = '#28a745';
        } else if (tipo === 'error') {
            alertDiv.style.backgroundColor = '#dc3545';
        } else if (tipo === 'warning') {
            alertDiv.style.backgroundColor = '#ffc107';
            alertDiv.style.color = '#000';
        }
        
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
    
    /**
     * Toggle campo coordinador en edición
     */
    toggleEditCoordinadorField() {
        const rol = document.getElementById('edit_rol').value;
        const coordinadorField = document.getElementById('edit_coordinador-field');
        
        if (rol === 'asesor') {
            coordinadorField.style.display = 'block';
        } else {
            coordinadorField.style.display = 'none';
        }
    }
}

// Funciones globales para los botones
function editarUsuario(id) {
    adminUsuarios.editarUsuario(id);
}

function eliminarUsuario(id) {
    adminUsuarios.eliminarUsuario(id);
}

function deshabilitarUsuario(id) {
    adminUsuarios.deshabilitarUsuario(id);
}

function habilitarUsuario(id) {
    adminUsuarios.habilitarUsuario(id);
}

function openModal(modalId) {
    adminUsuarios.openModal(modalId);
}

function closeModal(modalId) {
    adminUsuarios.closeModal(modalId);
}

function toggleCoordinadorField() {
    const rol = document.getElementById('rol').value;
    const coordinadorField = document.getElementById('coordinador-field');
    
    if (rol === 'asesor') {
        coordinadorField.style.display = 'block';
    } else {
        coordinadorField.style.display = 'none';
    }
}

function toggleEditCoordinadorField() {
    const rol = document.getElementById('edit_rol').value;
    const coordinadorField = document.getElementById('edit_coordinador-field');
    
    if (rol === 'asesor') {
        coordinadorField.style.display = 'block';
    } else {
        coordinadorField.style.display = 'none';
    }
}

// Inicializar cuando el DOM esté listo
let adminUsuarios;
document.addEventListener('DOMContentLoaded', () => {
    adminUsuarios = new AdminUsuarios();
});

