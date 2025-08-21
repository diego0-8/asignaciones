-- =====================================================
-- BASE DE DATOS SIMPLIFICADA PARA SISTEMA CRM
-- Solo incluye usuarios esenciales y estructura completa
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS citas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE citas;

-- =====================================================
-- TABLA DE USUARIOS (Solo los 3 roles esenciales)
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(255) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'coordinador', 'asesor') NOT NULL,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    coordinador_id INT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rol (rol),
    INDEX idx_estado (estado),
    INDEX idx_cedula (cedula),
    INDEX idx_usuario (usuario),
    INDEX idx_usuarios_coordinador (coordinador_id)
);

-- Insertar solo los 3 usuarios esenciales
INSERT INTO usuarios (nombre_completo, cedula, usuario, contrasena, rol, estado) VALUES
('Administrador del Sistema', '12345678', 'admin', 'admin123', 'administrador', 'Activo'),
('Coordinador Principal', '87654321', 'coordinador', 'coord123', 'coordinador', 'Activo'),
('Asesor Principal', '11223344', 'asesor', 'asesor123', 'asesor', 'Activo');

-- =====================================================
-- TABLA DE CLIENTES (Estructura lista para recibir datos)
-- =====================================================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(255) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20) DEFAULT NULL,
    celular2 VARCHAR(20) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    direccion TEXT DEFAULT NULL,
    ciudad VARCHAR(100) DEFAULT NULL,
    estado_gestion ENUM('Disponible', 'Contactado', 'En Proceso', 'Cita Programada', 'Cita Completada', 'No Interesado', 'No Contactable') DEFAULT 'Disponible',
    fecha_nacimiento DATE DEFAULT NULL,
    estado ENUM('Nuevo', 'Contactado', 'Asignado', 'Agendado', 'Completado', 'No Interesado') DEFAULT 'Nuevo',
    asesor_id INT DEFAULT NULL,
    coordinador_id INT DEFAULT NULL,
    carga_excel_id INT DEFAULT NULL,
    observaciones TEXT DEFAULT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_estado_gestion (estado_gestion),
    INDEX idx_asesor (asesor_id),
    INDEX idx_coordinador (coordinador_id),
    INDEX idx_cedula (cedula),
    INDEX idx_fecha_creacion (fecha_creacion)
);

-- =====================================================
-- TABLA DE ASIGNACIONES DE CLIENTES
-- =====================================================
CREATE TABLE asignaciones_clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    asesor_id INT NOT NULL,
    coordinador_id INT NOT NULL,
    fecha_asignacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Activa', 'Completada', 'Cancelada') DEFAULT 'Activa',
    observaciones TEXT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (coordinador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_estado (estado),
    INDEX idx_asesor (asesor_id),
    INDEX idx_coordinador (coordinador_id),
    INDEX idx_fecha_asignacion (fecha_asignacion)
);

-- =====================================================
-- SISTEMA DE TIPIFICACIÓN COMPLETO
-- =====================================================

-- Categorías principales de tipificación
CREATE TABLE categorias_tipificacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    color VARCHAR(7) DEFAULT '#007bff',
    icono VARCHAR(50) NULL,
    orden INT DEFAULT 0,
    estado ENUM('Activa', 'Inactiva') NOT NULL DEFAULT 'Activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orden (orden),
    INDEX idx_estado (estado)
);

-- Subcategorías de tipificación
CREATE TABLE subcategorias_tipificacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    color VARCHAR(7) DEFAULT '#6c757d',
    icono VARCHAR(50) NULL,
    orden INT DEFAULT 0,
    estado ENUM('Activa', 'Inactiva') NOT NULL DEFAULT 'Activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_tipificacion(id) ON DELETE CASCADE,
    INDEX idx_categoria (categoria_id),
    INDEX idx_orden (orden),
    INDEX idx_estado (estado)
);

-- Tipificaciones específicas
CREATE TABLE tipificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subcategoria_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(10) UNIQUE NOT NULL,
    descripcion TEXT NULL,
    color VARCHAR(7) DEFAULT '#28a745',
    icono VARCHAR(50) NULL,
    orden INT DEFAULT 0,
    estado ENUM('Activa', 'Inactiva') NOT NULL DEFAULT 'Activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subcategoria_id) REFERENCES subcategorias_tipificacion(id) ON DELETE CASCADE,
    INDEX idx_subcategoria (subcategoria_id),
    INDEX idx_codigo (codigo),
    INDEX idx_orden (orden),
    INDEX idx_estado (estado)
);

-- Insertar categorías de tipificación
INSERT INTO categorias_tipificacion (nombre, descripcion, color, icono, orden) VALUES
('Ventas', 'Procesos relacionados con ventas y comercialización', '#28a745', 'shopping-cart', 1),
('Atención al Cliente', 'Gestión de consultas y soporte al cliente', '#17a2b8', 'headset', 2),
('Seguimiento', 'Acciones de seguimiento y recordatorios', '#ffc107', 'clock', 3),
('Problemas', 'Manejo de problemas y quejas', '#dc3545', 'exclamation-triangle', 4),
('Información', 'Solicitudes de información y consultas', '#6f42c1', 'info-circle', 5),
('Otros', 'Otras categorías de gestión', '#6c757d', 'ellipsis-h', 6);

-- Insertar subcategorías
INSERT INTO subcategorias_tipificacion (categoria_id, nombre, descripcion, color, orden) VALUES
-- Ventas
(1, 'Cotización', 'Proceso de cotización de productos/servicios', '#28a745', 1),
(1, 'Negociación', 'Proceso de negociación con el cliente', '#20c997', 2),
(1, 'Cierre de Venta', 'Cierre exitoso de la venta', '#198754', 3),
(1, 'Post Venta', 'Seguimiento después de la venta', '#6f42c1', 4),

-- Atención al Cliente
(2, 'Consulta General', 'Consultas generales del cliente', '#17a2b8', 1),
(2, 'Soporte Técnico', 'Soporte técnico y resolución de problemas', '#0dcaf0', 2),
(2, 'Reclamos', 'Manejo de reclamos del cliente', '#fd7e14', 3),
(2, 'Sugerencias', 'Sugerencias y feedback del cliente', '#6f42c1', 4),

-- Seguimiento
(3, 'Recordatorio', 'Recordatorios programados', '#ffc107', 1),
(3, 'Seguimiento Programado', 'Seguimientos programados con el cliente', '#fd7e14', 2),
(3, 'Recontacto', 'Recontacto después de un período', '#6f42c1', 3),
(3, 'Mantenimiento', 'Mantenimiento de la relación', '#20c997', 4),

-- Problemas
(4, 'Problema Técnico', 'Problemas técnicos reportados', '#dc3545', 1),
(4, 'Problema de Servicio', 'Problemas con el servicio', '#fd7e14', 2),
(4, 'Problema de Facturación', 'Problemas de facturación', '#6f42c1', 3),
(4, 'Otro Problema', 'Otros tipos de problemas', '#6c757d', 4),

-- Información
(5, 'Información de Producto', 'Solicitud de información de productos', '#6f42c1', 1),
(5, 'Información de Servicio', 'Solicitud de información de servicios', '#0dcaf0', 2),
(5, 'Información de Precios', 'Consulta de precios y tarifas', '#20c997', 3),
(5, 'Información General', 'Otras consultas de información', '#6c757d', 4),

-- Otros
(6, 'General', 'Gestiones generales', '#6c757d', 1),
(6, 'Interno', 'Gestiones internas', '#495057', 2),
(6, 'Capacitación', 'Capacitación y entrenamiento', '#20c997', 3),
(6, 'Reunión', 'Reuniones y encuentros', '#6f42c1', 4);

-- Insertar tipificaciones específicas
INSERT INTO tipificaciones (subcategoria_id, nombre, codigo, descripcion, color, orden) VALUES
-- Cotización
(1, 'Solicitud de Cotización', 'COT001', 'Cliente solicita cotización', '#28a745', 1),
(1, 'Cotización Enviada', 'COT002', 'Cotización enviada al cliente', '#20c997', 2),
(1, 'Cotización Revisada', 'COT003', 'Cliente revisó la cotización', '#6f42c1', 3),

-- Negociación
(2, 'Inicio de Negociación', 'NEG001', 'Inicio del proceso de negociación', '#28a745', 1),
(2, 'Negociación en Curso', 'NEG002', 'Negociación activa con el cliente', '#ffc107', 2),
(2, 'Contraoferta', 'NEG003', 'Cliente presenta contraoferta', '#fd7e14', 3),

-- Cierre de Venta
(3, 'Venta Cerrada', 'VEN001', 'Venta exitosamente cerrada', '#198754', 1),
(3, 'Venta Pendiente', 'VEN002', 'Venta pendiente de confirmación', '#ffc107', 2),
(3, 'Venta Cancelada', 'VEN003', 'Venta cancelada por el cliente', '#dc3545', 3),

-- Consulta General
(5, 'Consulta Inicial', 'CON001', 'Primera consulta del cliente', '#17a2b8', 1),
(5, 'Consulta de Seguimiento', 'CON002', 'Consulta de seguimiento', '#0dcaf0', 2),
(5, 'Consulta Específica', 'CON003', 'Consulta sobre tema específico', '#6f42c1', 3),

-- Recordatorio
(9, 'Recordatorio de Pago', 'REC001', 'Recordatorio de pago pendiente', '#ffc107', 1),
(9, 'Recordatorio de Cita', 'REC002', 'Recordatorio de cita programada', '#fd7e14', 2),
(9, 'Recordatorio de Seguimiento', 'REC003', 'Recordatorio de seguimiento', '#6f42c1', 3);

-- =====================================================
-- TABLA DE HISTORIAL DE GESTIÓN (Estructura completa)
-- =====================================================
CREATE TABLE historial_gestion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asignacion_id INT NOT NULL,
    cliente_id INT NOT NULL,
    asesor_id INT NOT NULL,
    tipo_gestion ENUM('Llamada', 'WhatsApp', 'Email', 'Presencial', 'Asignación', 'Liberación') NOT NULL,
    resultado ENUM('Contactado', 'No Contactado', 'Asignado', 'Agendado', 'No Interesado', 'Completado') NOT NULL,
    observaciones TEXT NULL,
    fecha_gestion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    proxima_accion VARCHAR(255) NULL,
    fecha_proxima_accion DATE NULL,
    proxima_fecha DATETIME NULL,
    cita_id INT NULL,
    
    -- TIPIFICACIÓN COMPLETA (se agregará en el futuro)
    categoria_id INT NULL,
    subcategoria_id INT NULL,
    tipificacion_id INT NULL,
    
    -- MÉTRICAS DE LA GESTIÓN (se agregarán en el futuro)
    duracion_llamada INT NULL COMMENT 'Duración en segundos (se agregará en el futuro)',
    monto_venta DECIMAL(10,2) NULL,
    edad_cliente INT NULL,
    num_personas INT NULL,
    valor_cotizacion DECIMAL(10,2) NULL,
    whatsapp_enviado BOOLEAN DEFAULT FALSE,
    
    -- ESTADO Y SEGUIMIENTO (se agregarán en el futuro)
    estado_gestion ENUM('Pendiente', 'En Proceso', 'Completada', 'Cancelada') DEFAULT 'Pendiente',
    prioridad ENUM('Baja', 'Media', 'Alta', 'Urgente') DEFAULT 'Media',
    
    FOREIGN KEY (asignacion_id) REFERENCES asignaciones_clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias_tipificacion(id) ON DELETE SET NULL,
    FOREIGN KEY (subcategoria_id) REFERENCES subcategorias_tipificacion(id) ON DELETE SET NULL,
    FOREIGN KEY (tipificacion_id) REFERENCES tipificaciones(id) ON DELETE SET NULL,
    
    INDEX idx_asignacion (asignacion_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_asesor (asesor_id),
    INDEX idx_fecha_gestion (fecha_gestion),
    INDEX idx_tipo_gestion (tipo_gestion),
    INDEX idx_resultado (resultado)
);

-- =====================================================
-- TABLA DE CITAS PROGRAMADAS
-- =====================================================
CREATE TABLE citas_programadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    asesor_id INT NOT NULL,
    fecha_cita DATE NOT NULL,
    hora_cita TIME NOT NULL,
    tipo_cita ENUM('Presencial', 'Virtual', 'Telefónica') DEFAULT 'Presencial',
    estado ENUM('Programada', 'Confirmada', 'Completada', 'Cancelada', 'No Asistió', 'Reprogramada') DEFAULT 'Programada',
    observaciones TEXT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_asesor (asesor_id),
    INDEX idx_fecha_cita (fecha_cita),
    INDEX idx_estado (estado)
);

-- =====================================================
-- TABLA DE CARGA DE ARCHIVOS EXCEL
-- =====================================================
CREATE TABLE cargas_excel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cargue VARCHAR(255) NOT NULL,
    usuario_coordinador_id INT NOT NULL,
    fecha_cargue TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Activa', 'Inactiva', 'Procesando') DEFAULT 'Activa',
    registros_procesados INT DEFAULT 0,
    registros_exitosos INT DEFAULT 0,
    registros_con_error INT DEFAULT 0,
    FOREIGN KEY (usuario_coordinador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_coordinador (usuario_coordinador_id),
    INDEX idx_fecha_cargue (fecha_cargue),
    INDEX idx_estado (estado)
);

-- =====================================================
-- TABLA DE LOGS DE ACTIVIDADES
-- =====================================================
CREATE TABLE logs_actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    fecha_actividad TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_actividad),
    INDEX idx_accion (accion)
);

-- =====================================================
-- TABLA DE HISTORIAL DE ASIGNACIONES
-- =====================================================
CREATE TABLE historial_asignaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asesor_id INT NOT NULL,
    coordinador_id INT NOT NULL,
    fecha_asignacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo_accion ENUM('asignacion', 'reasignacion', 'liberacion') DEFAULT 'asignacion',
    usuario_admin_id INT NULL,
    observaciones TEXT NULL,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (coordinador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_admin_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_asesor (asesor_id),
    INDEX idx_coordinador (coordinador_id),
    INDEX idx_fecha_asignacion (fecha_asignacion)
);

-- =====================================================
-- TABLA DE GESTIONES
-- =====================================================
CREATE TABLE gestiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    asesor_id INT NOT NULL,
    tipo_contacto ENUM('Llamada', 'WhatsApp', 'Email', 'Presencial') NOT NULL,
    resultado ENUM('Contactado', 'No Contactado', 'Asignado', 'Agendado', 'No Interesado') NOT NULL,
    observaciones TEXT NULL,
    fecha_contacto TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    proxima_accion VARCHAR(255) NULL,
    fecha_proxima_accion DATE NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_asesor (asesor_id),
    INDEX idx_fecha_contacto (fecha_contacto)
);

-- =====================================================
-- TABLA DE HISTORIAL DE CITAS
-- =====================================================
CREATE TABLE historial_citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL,
    cliente_id INT NOT NULL,
    asesor_id INT NOT NULL,
    accion ENUM('Creación', 'Confirmación', 'Modificación', 'Cancelación', 'Completada', 'No Asistió') NOT NULL,
    observaciones TEXT NULL,
    fecha_accion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cita_id) REFERENCES citas_programadas(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_cita (cita_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_asesor (asesor_id),
    INDEX idx_fecha_accion (fecha_accion)
);

-- =====================================================
-- TABLA DE CARGAS DE ARCHIVOS (General)
-- =====================================================
CREATE TABLE cargas_archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    tipo_archivo ENUM('CSV', 'Excel') NOT NULL,
    usuario_id INT NOT NULL,
    registros_procesados INT DEFAULT 0,
    registros_exitosos INT DEFAULT 0,
    registros_con_error INT DEFAULT 0,
    estado ENUM('Procesando', 'Completado', 'Con Errores') DEFAULT 'Procesando',
    fecha_carga TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha_carga (fecha_carga),
    INDEX idx_estado (estado)
);

-- =====================================================
-- TABLA DE CITAS (General)
-- =====================================================
CREATE TABLE citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    asesor_id INT NOT NULL,
    fecha_cita DATETIME NOT NULL,
    tipo_cita ENUM('Consulta', 'Seguimiento', 'Renovación', 'Otro') NOT NULL,
    estado ENUM('Agendada', 'Confirmada', 'Completada', 'Cancelada', 'No Asistió') DEFAULT 'Agendada',
    observaciones TEXT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_asesor (asesor_id),
    INDEX idx_fecha_cita (fecha_cita),
    INDEX idx_estado (estado)
);

-- =====================================================
-- INSERCIÓN DE DATOS DE PRUEBA MÍNIMOS
-- =====================================================

-- Insertar algunos clientes de ejemplo (opcional, se pueden eliminar)
INSERT INTO clientes (nombre_completo, cedula, telefono, ciudad, estado) VALUES
('Cliente de Prueba 1', '1000125351', '3001234567', 'Bogotá', 'Nuevo'),
('Cliente de Prueba 2', '1000125520', '3001234568', 'Medellín', 'Nuevo');

-- Insertar asignaciones de ejemplo
INSERT INTO asignaciones_clientes (cliente_id, asesor_id, coordinador_id, estado) VALUES
(1, 3, 2, 'Activa'),
(2, 3, 2, 'Activa');

-- Insertar gestión de ejemplo
INSERT INTO historial_gestion (
    asignacion_id, cliente_id, asesor_id, tipo_gestion, resultado, observaciones
) VALUES
(1, 1, 3, 'Llamada', 'Contactado', 'Primera gestión de prueba desde script simplificado');

-- =====================================================
-- FINALIZACIÓN
-- =====================================================

-- Verificar que todo esté funcionando
SELECT 'Base de datos creada exitosamente' as mensaje;
SELECT COUNT(*) as total_usuarios FROM usuarios;
SELECT COUNT(*) as total_categorias FROM categorias_tipificacion;
SELECT COUNT(*) as total_subcategorias FROM subcategorias_tipificacion;
SELECT COUNT(*) as total_tipificaciones FROM tipificaciones;
SELECT COUNT(*) as total_clientes FROM clientes;
SELECT COUNT(*) as total_asignaciones FROM asignaciones_clientes;
SELECT COUNT(*) as total_gestiones FROM historial_gestion;
