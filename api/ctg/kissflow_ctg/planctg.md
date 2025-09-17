# Plan de Acción: Creación de CTG en Kiss Flow (Versión Simplificada)

## Resumen

El objetivo es crear un nuevo item en el proceso "Warranty_Claim" de Kiss Flow cada vez que se crea un CTG en la aplicación local. El flujo de datos es **Aplicación Local -> Kiss Flow**.

**Supuesto Clave:** Se garantiza que todo usuario de la aplicación existe previamente en el Dataset `DS_Documentos_Cliente` de Kiss Flow. Esto elimina la necesidad de gestionar casos donde el cliente no existe.

---

## Flujo de Trabajo Detallado

El proceso se dividirá en dos fases principales que se ejecutan en secuencia cuando un usuario crea un nuevo CTG.

### Fase 1: Script de Lógica de Kiss Flow (`api/ctg/kissflow_ctg/ctg_handler.php`)

Se creará un único script manejador que orquestará toda la interacción con Kiss Flow.

1.  **Creación del Script:**
    *   **Acción:** Crear el archivo `api/ctg/kissflow_ctg/ctg_handler.php`.
    *   **Propósito:** Centralizar la lógica de búsqueda de cliente y creación del `Warranty_Claim`.

2.  **Paso 1: Búsqueda del Cliente en Kiss Flow**
    *   El script recibirá los datos del CTG, incluyendo la **cédula (C.I.)** del cliente.
    *   Realizará una petición `GET` a la API de Kiss Flow para **buscar** en el Dataset `DS_Documentos_Cliente` un registro donde el campo `Identificacion` coincida con la cédula recibida. (Ver **Anexo A**).
    *   **Manejo de Errores:** Si la búsqueda falla o no devuelve ningún resultado, el proceso se abortará y se registrará un error detallado.
    *   **Extracción de ID:** Si la búsqueda es exitosa, se extraerá el `_id` del registro del cliente encontrado en Kiss Flow.

3.  **Paso 2: Creación del "Warranty Claim"**
    *   **Construcción del Payload:** Con el `_id` del cliente obtenido, el script construirá el cuerpo (payload) de la petición `POST` en formato JSON. (Ver **Anexo B** para la estructura exacta).
    *   **Llamada a la API:** Se enviará la petición `POST` al endpoint del proceso `Warranty_Claim`. (Ver **Anexo B** para la URL exacta).
    *   **Respuesta:** El script devolverá una respuesta JSON indicando si la creación fue exitosa o no.

### Fase 2: Integración con el Flujo de Creación de CTG

1.  **Punto de Integración:** El archivo `api/ctg/ctg_create.php`.

2.  **Modificación de `api/ctg/ctg_create.php`:**
    *   **Acción:** Justo después de que el nuevo CTG se guarde en la base de datos local, se invocará al `ctg_handler.php`, pasándole los datos relevantes.
    *   Se registrará el resultado de la operación de Kiss Flow para tener trazabilidad.

---

## Anexo A: Determinación del Endpoint de Búsqueda de Dataset

Para implementar la búsqueda de clientes por cédula, es indispensable conocer la URL exacta y el formato del filtro que utiliza la API. Para obtener esta información, se seguirán estos pasos:

1.  **Abrir Herramientas de Desarrollador (F12)** y seleccionar la pestaña **Network (Red)**.
2.  **Navegar al Dataset:** En Kiss Flow, ir a la página del Dataset `DS_Documentos_Cliente`.
3.  **Realizar una Búsqueda:** Usar el buscador de la página para encontrar un cliente por su **cédula/identificación**.
4.  **Inspeccionar la Petición:** En Network, localizar la petición principal que recupera los datos.
5.  **Capturar la Información:** Copiar la **Request URL** completa.

---

## Anexo B: Determinación del Endpoint y Payload de Creación de Items

Tras las pruebas iniciales (error `404 Not Found`), se determinó que la URL de creación es incorrecta. Para obtener la URL y la estructura del payload correctas, se deben seguir estos pasos:

1.  **Navegar a la Interfaz de Creación:** En Kiss Flow, ir a la sección para crear un nuevo "Ticket De Atención de Contingencia" (`Warranty_Claim`).
2.  **Abrir Herramientas de Desarrollador (F12)** y seleccionar la pestaña **Network (Red)**.
3.  **Rellenar y Enviar el Formulario:** Completar los campos del formulario con datos de prueba y hacer clic en el botón de enviar/crear.
4.  **Inspeccionar la Petición POST:** En Network, localizar la nueva petición con el método **POST**.
5.  **Capturar la Información:** Seleccionar dicha petición y copiar los siguientes datos para el desarrollo:
    *   **Request URL:** La URL completa a la que se realizó la petición `POST`.
    *   **Request Payload:** El contenido exacto del payload enviado (en la pestaña "Payload" o "Carga útil"), que mostrará la estructura JSON que espera la API.