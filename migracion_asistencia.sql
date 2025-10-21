-- Migración para agregar campo de asistencia a agendamiento_visitas
-- Fecha: 2025-10-21

ALTER TABLE `agendamiento_visitas` 
ADD COLUMN `asistencia` ENUM('NO_REGISTRADO', 'ASISTIO', 'NO_ASISTIO') 
DEFAULT 'NO_REGISTRADO' 
COMMENT 'Registro de asistencia del cliente a la cita' 
AFTER `resultado`;

-- Crear índice para búsquedas de asistencia
ALTER TABLE `agendamiento_visitas` 
ADD INDEX `idx_asistencia` (`asistencia`);
