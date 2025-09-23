# Plan de Implementación

Aquí tienes un resumen de las tareas pendientes y los próximos pasos:

## Tareas Completadas:

1.  **Modificación de la Base de Datos:** Has ejecutado el script SQL para añadir la columna `last_modified` a las tablas `usuario` y `propiedad`.
2.  **Creación del Script de Sincronización:** He creado el script `api/webhook_rdc/sync_db_to_kf.php` que se encargará de sincronizar los datos modificados de tu base de datos local a Kiss Flow.

## Tareas Pendientes (Tu Acción):

1.  **Configurar el Cron Job (o Tarea Programada):** Debes configurar una tarea programada en tu servidor (o en el Programador de Tareas de Windows) para ejecutar el script `api/webhook_rdc/sync_db_to_kf.php` cada 5 minutos.
    *   **Ruta del script:** `C:\xampp\htdocs\BackEndAppCostaSol\api\webhook_rdc\sync_db_to_kf.php`
    *   **Frecuencia:** Cada 5 minutos.

    ### Instrucciones Detalladas para la Configuración del Cron Job en Bluehost (cPanel):

    1.  **Accede a tu cPanel:** Inicia sesión en tu cuenta de Bluehost y navega a tu cPanel.
    2.  **Busca 'Cron Jobs':** En la sección 'Avanzada' (o similar), busca y haz clic en 'Cron Jobs'.
    3.  **Configura el Intervalo:**
        *   En la sección 'Añadir nuevo Cron Job', selecciona la opción 'Cada 5 minutos' (o personaliza los valores para que se ejecute cada 5 minutos).
        *   Asegúrate de que los campos de 'Minuto', 'Hora', 'Día', 'Mes' y 'Día de la semana' estén configurados correctamente para que se ejecute cada 5 minutos (por ejemplo, `*/5` para Minuto, `*` para los demás).
    4.  **Comando:** En el campo 'Comando', introduce la siguiente línea:
        ```bash
        /usr/bin/php /home/USUARIO_CPANEL/public_html/BackEndAppCostaSol/api/webhook_rdc/sync_db_to_kf.php >/dev/null 2>&1
        ```
        **Importante:**
        *   Reemplaza `USUARIO_CPANEL` con tu nombre de usuario real de cPanel (lo puedes encontrar en la parte superior derecha de tu cPanel).
        *   La ruta `/home/USUARIO_CPANEL/public_html/` es la ruta estándar para los archivos de tu sitio web en Bluehost. Asegúrate de que la ruta completa a tu script sea correcta.
        *   `/usr/bin/php` es la ruta común al ejecutable de PHP en Bluehost. Si tienes problemas, puedes intentar con `php` directamente o consultar la documentación de Bluehost para la ruta exacta de PHP CLI.
        *   `>/dev/null 2>&1` redirige la salida estándar y los errores para evitar correos electrónicos. Si necesitas depurar, puedes cambiarlo a `>> /home/USUARIO_CPANEL/public_html/BackEndAppCostaSol/api/webhook_rdc/sync_db_to_kf_log.txt 2>&1` para que la salida se escriba en un archivo de log.
    5.  **Añadir Cron Job:** Haz clic en el botón 'Añadir nuevo Cron Job' (o 'Add Cron Job').

    Esto ejecutará el script `sync_db_to_kf.php` cada 5 minutos en tu servidor Bluehost.

    **Nota:** Si estás trabajando en un entorno de desarrollo local en Windows, las instrucciones del Programador de Tareas siguen siendo válidas.

## Verificación:

*   Una vez configurada la tarea, verifica el archivo de log `C:\xampp\htdocs\BackEndAppCostaSol\api\webhook_rdc\sync_db_to_kf_log.txt` para asegurarte de que el script se está ejecutando correctamente y registrando las sincronizaciones.

Por favor, avísame si tienes alguna duda o necesitas ayuda con la configuración del cron job.