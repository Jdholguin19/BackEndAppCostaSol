-- Script para crear notificaciones retroactivas para todas las noticias existentes
-- Esto asignará todas las noticias actuales a TODOS los usuarios (clientes y residentes)

-- Paso 1: Insertar notificaciones para todas las noticias existentes a todos los usuarios
INSERT INTO notificacion (usuario_id, titulo, mensaje, tipo, tipo_id, leido, fecha_creacion)
SELECT 
    u.id AS usuario_id,
    CONCAT('Nueva noticia: ', n.titulo) AS titulo,
    n.resumen AS mensaje,
    'Noticia' AS tipo,
    n.id AS tipo_id,
    0 AS leido,
    n.fecha_publicacion AS fecha_creacion
FROM noticia n
CROSS JOIN usuario u
WHERE n.estado = 1  -- Solo noticias activas
  AND u.rol_id IN (1, 2)  -- Solo clientes (1) y residentes (2)
  AND NOT EXISTS (
      -- Evitar duplicados: solo insertar si no existe ya una notificación
      SELECT 1 FROM notificacion notif
      WHERE notif.usuario_id = u.id
        AND notif.tipo = 'Noticia'
        AND notif.tipo_id = n.id
  );

-- Verificar cuántas notificaciones se crearon
SELECT 
    COUNT(*) AS total_notificaciones,
    COUNT(DISTINCT usuario_id) AS usuarios_con_notificaciones,
    COUNT(DISTINCT tipo_id) AS noticias_notificadas
FROM notificacion
WHERE tipo = 'Noticia';
