La API `api/etapas_manzana_villa.php` funciona de la siguiente manera:

1.  **Obtención de Parámetros:** Recibe los parámetros `manzana` y `villa` a través de la URL (método GET).
2.  **Validación y Sanitización:** Antes de usar estos valores, los sanitiza y valida para prevenir ataques como inyección SQL y XSS. Utiliza `htmlspecialchars()` y `filter_var()` para asegurar que los datos sean seguros y del tipo esperado (por ejemplo, enteros para `manzana` y `villa` si aplica, o strings sanitizados).
3.  **Conexión a la Base de Datos:** Establece una conexión a la base de datos utilizando la configuración definida en `config/db.php`.
4.  **Consulta a la Base de Datos:** Realiza una consulta a la tabla `progreso_construccion` (o tablas relacionadas que contengan la información de las etapas) filtrando los resultados por la `manzana` y `villa` proporcionadas. Es crucial que esta consulta utilice **sentencias preparadas** para garantizar la seguridad.
5.  **Procesamiento de Resultados:** Una vez obtenidos los datos de las etapas de construcción (nombre de la etapa, descripción, estado, URLs de las fotos, etc.), los organiza en un formato estructurado.
6.  **Respuesta JSON:** Finalmente, devuelve una respuesta en formato JSON. Esta respuesta incluye un indicador `ok` (true/false) y, si la operación fue exitosa, un array `etapas` con todos los detalles de las etapas de construcción encontradas para esa propiedad. En caso de error, devuelve `ok: false` y un mensaje descriptivo.

En resumen, esta API actúa como un puente entre el frontend (`fase_detalle.php`) y la base de datos, proporcionando de forma segura y estructurada la información del progreso de construcción de una propiedad específica.
