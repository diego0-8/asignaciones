-- Base de datos: citas2
-- Sistema IPS CRM - Gestión de Clientes y Citas

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS `citas2` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `citas2`;

-- Tabla de usuarios
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_completo` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','coordinador','asesor') NOT NULL DEFAULT 'asesor',
  `coordinador_id` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `coordinador_id` (`coordinador_id`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`coordinador_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de clientes
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_completo` varchar(255) NOT NULL,
  `cedula` varchar(20) NOT NULL UNIQUE,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estado_gestion` enum('nuevo','en_proceso','contactado','no_interesado','interesado','cita_agendada','cita_completada') DEFAULT 'nuevo',
  `asesor_id` int(11) DEFAULT NULL,
  `fecha_asignacion` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `asesor_id` (`asesor_id`),
  CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`asesor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de historial_gestion (ESTRUCTURA CORRECTA)
CREATE TABLE `historial_gestion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `asesor_id` int(11) NOT NULL,
  `tipo_contacto` enum('telefono','whatsapp','email','presencial','no_contactado') NOT NULL DEFAULT 'no_contactado',
  `tipo_gestion` enum('contactado','no_contactado','cita_agendada','cita_cancelada','cita_completada','no_interesado','interesado') NOT NULL DEFAULT 'no_contactado',
  `observaciones` text DEFAULT NULL,
  `fecha_gestion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resultado` varchar(100) DEFAULT NULL,
  `fecha_cita` date DEFAULT NULL,
  `hora_cita` time DEFAULT NULL,
  `lugar_cita` varchar(255) DEFAULT NULL,
  `fecha_proximo_contacto` datetime DEFAULT NULL,
  `proxima_accion` varchar(255) DEFAULT NULL,
  `fecha_proxima_accion` datetime DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `asesor_id` (`asesor_id`),
  KEY `fecha_gestion` (`fecha_gestion`),
  CONSTRAINT `historial_gestion_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `historial_gestion_ibfk_2` FOREIGN KEY (`asesor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar usuarios de prueba
INSERT INTO `usuarios` (`nombre_completo`, `email`, `password`, `rol`) VALUES
('Administrador', 'admin@ips.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Coordinador Principal', 'coordinador@ips.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coordinador'),
('Asesor Ejemplo', 'asesor@ips.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'asesor');

-- Actualizar coordinador_id para el asesor
UPDATE `usuarios` SET `coordinador_id` = 2 WHERE `id` = 3;

-- Insertar clientes de prueba
INSERT INTO `clientes` (`nombre_completo`, `cedula`, `telefono`, `email`, `ciudad`, `estado_gestion`, `asesor_id`, `fecha_asignacion`) VALUES
('Juan Pérez', '1234567890', '3001234567', 'juan.perez@email.com', 'Bogotá', 'contactado', 3, NOW()),
('María García', '9876543210', '3009876543', 'maria.garcia@email.com', 'Medellín', 'interesado', 3, NOW()),
('Carlos López', '1122334455', '3001122334', 'carlos.lopez@email.com', 'Cali', 'nuevo', 3, NOW());

-- Insertar historial de gestión de prueba
INSERT INTO `historial_gestion` (`cliente_id`, `asesor_id`, `tipo_contacto`, `tipo_gestion`, `observaciones`, `fecha_gestion`, `resultado`, `proxima_accion`, `fecha_proxima_accion`) VALUES
(1, 3, 'telefono', 'contactado', 'Cliente interesado en servicios de salud', '2024-01-15 10:30:00', 'Interesado', 'Agendar cita de seguimiento', '2024-01-20 14:00:00'),
(1, 3, 'whatsapp', 'cita_agendada', 'Cita confirmada para el 20 de enero', '2024-01-16 15:45:00', 'Cita agendada', 'Realizar seguimiento post-cita', '2024-01-21 10:00:00'),
(2, 3, 'email', 'contactado', 'Cliente solicita información sobre planes', '2024-01-14 09:15:00', 'Interesado', 'Enviar información detallada', '2024-01-17 16:00:00'),
(3, 3, 'telefono', 'no_contactado', 'No contesta llamadas', '2024-01-13 11:20:00', 'No contactado', 'Intentar contacto por WhatsApp', '2024-01-18 10:00:00');

-- Crear índices adicionales para optimizar consultas
CREATE INDEX `idx_clientes_estado` ON `clientes` (`estado_gestion`);
CREATE INDEX `idx_clientes_asesor` ON `clientes` (`asesor_id`);
CREATE INDEX `idx_historial_fecha` ON `historial_gestion` (`fecha_gestion`);
CREATE INDEX `idx_historial_tipo` ON `historial_gestion` (`tipo_gestion`);
CREATE INDEX `idx_usuarios_rol` ON `usuarios` (`rol`);
CREATE INDEX `idx_usuarios_coordinador` ON `usuarios` (`coordinador_id`);
