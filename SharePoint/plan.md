## Cómo ejecutar el script `SharePoint/ExtraerURL.php`

Puedes ejecutar el script `SharePoint/ExtraerURL.php` de dos maneras principales:

### Opción 1: A través de tu servidor web (recomendado si usas XAMPP)

Esta es la forma más común si tienes un entorno de desarrollo local como XAMPP y el archivo está en tu directorio `htdocs`.

**Paso a paso:**
1.  **Asegúrate de que Apache esté corriendo** en tu XAMPP. Puedes verificarlo abriendo el Panel de Control de XAMPP.
2.  Abre tu navegador web (Chrome, Firefox, Edge, etc.).
3.  Navega a la siguiente URL:
    ```
    http://localhost/BackEndAppCostaSol/SharePoint/ExtraerURL.php
    ```
    (Si tu configuración de XAMPP es diferente o has configurado un host virtual, ajusta `localhost` y la ruta según corresponda).

Al acceder a esta URL, el script se ejecutará y verás la salida (el proceso de exploración y el total de imágenes encontradas) directamente en la ventana de tu navegador.

### Opción 2: Desde la línea de comandos (CLI)

Esta opción es útil si prefieres ejecutar scripts PHP sin pasar por un navegador web, o si necesitas automatizar la ejecución.

**Paso a paso:**
1.  Abre tu terminal o Símbolo del sistema (CMD en Windows).
2.  Navega hasta el directorio donde se encuentra el script. Puedes usar el comando `cd` (change directory):
    ```bash
    cd C:\xampp\htdocs\BackEndAppCostaSol\SharePoint
    ```
3.  Ejecuta el script usando el intérprete de PHP:
    ```bash
    php ExtraerURL.php
    ```
    (Si el comando `php` no es reconocido, es posible que necesites proporcionar la ruta completa al ejecutable de PHP, por ejemplo: `C:\xampp\php\php.exe ExtraerURL.php`).

La salida del script (el proceso de exploración y el total de imágenes encontradas) se mostrará directamente en tu terminal.
