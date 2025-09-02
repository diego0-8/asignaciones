-- Script para crear las tablas necesarias del sistema IPS CRM
-- Ejecutar este script en tu base de datos MySQL

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS citas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE citas;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(255) NOT NULL,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'coordinador', 'asesor') NOT NULL,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    coordinador_id INT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rol (rol),
    INDEX idx_estado (estado),
    INDEX idx_coordinador (coordinador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(255) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    telefono VARCHAR(20),
    celular2 VARCHAR(20),
    email VARCHAR(255),
    direccion TEXT,
    ciudad VARCHAR(100),
    estado_gestion ENUM('Disponible', 'Asignado', 'En Proceso', 'Cita Programada', 'Cita Completada') DEFAULT 'Disponible',
    estado ENUM('Nuevo', 'Asignado', 'En Proceso', 'Completado') DEFAULT 'Nuevo',
    coordinador_id INT,
    asesor_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cedula_coordinador (cedula, coordinador_id),
    INDEX idx_coordinador (coordinador_id),
    INDEX idx_asesor (asesor_id),
    INDEX idx_estado_gestion (estado_gestion),
    INDEX idx_fecha_creacion (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de historial de gestiones (ESTRUCTURA CORREGIDA)
CREATE TABLE IF NOT EXISTS historial_gestion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    asesor_id INT NOT NULL,
    tipo_gestion ENUM('asignacion_cita', 'volver_llamar', 'fuera_ciudad', 'no_interesa', 'no_contactado', 'contactado') NOT NULL,
    tipo_contacto ENUM('contactado', 'no_contactado') NOT NULL,
    resultado ENUM('Contactado', 'No Contactado', 'Agendado', 'Rechazado', 'Interesado') NOT NULL,
    observaciones TEXT,
    fecha_gestion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    proxima_accion VARCHAR(255),
    fecha_proxima_accion DATETIME,
    INDEX idx_cliente (cliente_id),
    INDEX idx_asesor (asesor_id),
    INDEX idx_fecha_gestion (fecha_gestion),
    INDEX idx_tipo_gestion (tipo_gestion),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de historial de asignaciones
CREATE TABLE IF NOT EXISTS historial_asignaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    asesor_id INT NOT NULL,
    coordinador_id INT NOT NULL,
    usuario_admin_id INT,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Asignado', 'Reasignado', 'Liberado') DEFAULT 'Asignado',
    observaciones TEXT,
    INDEX idx_cliente (cliente_id),
    INDEX idx_asesor (asesor_id),
    INDEX idx_coordinador (coordinador_id),
    INDEX idx_fecha_asignacion (fecha_asignacion),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (coordinador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_admin_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de citas
CREATE TABLE IF NOT EXISTS citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    asesor_id INT NOT NULL,
    fecha_cita DATETIME NOT NULL,
    tipo_cita ENUM('Consulta', 'Examen', 'Seguimiento', 'Otro') DEFAULT 'Consulta',
    estado ENUM('Programada', 'Confirmada', 'Completada', 'Cancelada', 'No Asistió') DEFAULT 'Programada',
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cliente (cliente_id),
    INDEX idx_asesor (asesor_id),
    INDEX idx_fecha_cita (fecha_cita),
    INDEX idx_estado (estado),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (asesor_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de logs de actividades
CREATE TABLE IF NOT EXISTS logs_actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (fecha_actividad),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario administrador por defecto
INSERT IGNORE INTO usuarios (nombre_completo, cedula, usuario, contrasena, rol, estado) VALUES 
('Administrador del Sistema', '12345678', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 'Activo');

-- Insertar usuario coordinador de prueba
INSERT IGNORE INTO usuarios (nombre_completo, cedula, usuario, contrasena, rol, estado) VALUES 
('Coordinador de Prueba', '87654321', 'coordinador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinador', 'Activo');

-- Insertar usuario asesor de prueba
INSERT IGNORE INTO usuarios (nombre_completo, cedula, usuario, contrasena, rol, estado, coordinador_id) VALUES 
('Asesor de Prueba', '11223344', 'asesor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asesor', 'Activo', 2);

-- Nota: La contraseña para todos los usuarios de prueba es 'password'
-- Cambiar las contraseñas en producción
