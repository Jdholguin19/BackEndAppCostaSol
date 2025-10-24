-- Script SQL para agregar el campo 'notas' a la tabla usuario
-- Este campo permitirá almacenar notas del cliente con texto largo

ALTER TABLE usuario 
ADD COLUMN notas TEXT DEFAULT NULL 
COMMENT 'Notas del cliente - campo de texto largo para observaciones y comentarios';

-- Verificar que el campo se agregó correctamente
DESCRIBE usuario;