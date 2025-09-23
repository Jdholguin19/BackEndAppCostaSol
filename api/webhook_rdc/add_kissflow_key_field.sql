-- Agregar campo kissflow_key a la tabla usuario
ALTER TABLE usuario 
ADD COLUMN kissflow_key VARCHAR(255) NULL 
COMMENT 'Key real de Kiss Flow para actualizaciones';

-- Crear índice para mejor performance
CREATE INDEX idx_usuario_kissflow_key ON usuario(kissflow_key);
