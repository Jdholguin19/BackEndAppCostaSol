# Documentación Técnica: Integración Kiss Flow (SDA)

Este documento describe la arquitectura técnica de la integración entre la aplicación y Kiss Flow para el proceso de "Selección de Acabados" (SDA) y provee una guía para replicar la integración en otros procesos.

---

### 1. Arquitectura de la Integración

La integración se compone de dos archivos PHP principales:

1.  **Disparador (Trigger): `api/guardar_seleccion_acabados.php`**
    *   **Responsabilidad:** Orquesta el proceso. Primero, guarda la selección de acabados del cliente en la base de datos local.
    *   Si la operación es exitosa, recopila los datos necesarios (principalmente la cédula del usuario) y realiza una llamada cURL (POST) al `sda_handler.php` para iniciar la integración con Kiss Flow.

2.  **Manejador (Handler): `api/acabados/kissflow_sda/sda_handler.php`**
    *   **Responsabilidad:** Contiene toda la lógica de comunicación con la API de Kiss Flow. Está diseñado para ser reutilizable y no depender directamente de la lógica de negocio de `guardar_seleccion_acabados.php`.
    *   Su única función es recibir un conjunto de datos mínimo (cédula), interactuar con Kiss Flow y devolver una respuesta JSON (`ok: true/false`).

---

### 2. Flujo Técnico del `sda_handler.php`

El manejador ejecuta una secuencia de 3 pasos:

1.  **Búsqueda en Dataset:**
    *   Recibe la `cedula` del cliente desde el disparador.
    *   Realiza una petición `GET` a la API de Kiss Flow para buscar en el dataset `DS_Documentos_Cliente` un registro que coincida con la `Identificacion`.
    *   Si no encuentra un registro, la ejecución termina con un error.

2.  **Creación del Proceso:**
    *   Con los datos obtenidos del dataset en el paso anterior, construye un `array` llamado `$initial_payload`.
    *   Este `payload` contiene los campos que se rellenarán al crear el nuevo ítem en el proceso de Kiss Flow (ej. `Ubicacion`, `Nombre_del_Cliente`, `Mz_Solar`, etc.).
    *   Realiza una petición `POST` a la URL del proceso (`/process/.../{process_name}`) enviando el `$initial_payload`.
    *   Si la creación es exitosa, Kiss Flow devuelve un `_id` y un `_activity_instance_id` para el nuevo ítem en estado "Draft" (borrador).

3.  **Envío del Proceso (Submit):**
    *   Usando los IDs del paso anterior, se construye una URL para la acción `submit`.
    *   Realiza una petición `POST` a esta URL para mover el ítem del estado "Draft" al siguiente paso del flujo de trabajo en Kiss Flow, haciéndolo visible para los usuarios asignados.

---

### 3. Variables y Funciones Esenciales

*   **`kiss_flow/config.php`:**
    *   `KISSFLOW_API_HOST`: URL base de la API de Kiss Flow (ej: `https://thaliavictoria.kissflow.com`).
    *   `KISSFLOW_ACCESS_KEY_ID`: Credencial de API (ID).
    *   `KISSFLOW_ACCESS_KEY_SECRET`: Credencial de API (Secreto).

*   **`sda_handler.php`:**
    *   `call_kissflow_api(string $url, string $method, ?array $payload)`: Función central que encapsula todas las llamadas cURL a la API. Se encarga de añadir las cabeceras de autenticación (`X-Access-Key-ID`, `X-Access-Key-Secret`) y manejar las respuestas y errores.
    *   `$process_name`: Variable que contiene el nombre exacto del proceso en Kiss Flow (ej: `Copia_de_Eleccio_n_de_Acabados_y_Adicion`). **Es sensible a mayúsculas, minúsculas y espacios.**
    *   `$initial_payload`: El `array` asociativo que se convierte a JSON y se envía en el cuerpo de la petición para crear el ítem. **Las claves de este array deben coincidir exactamente con los nombres de los campos en Kiss Flow.**

---

### 4. Guía para Adaptar a un Nuevo Proceso (Ej: Ticket PQR)

Para integrar otro proceso, como la creación de un ticket de PQR, sigue estos pasos:

**Paso 1: Identificar el Disparador**

Localiza el archivo que gestiona la creación del PQR en la base de datos local. Por ejemplo, `api/pqr/pqr_create.php`.

**Paso 2: Crear un Nuevo Handler**

Copia el archivo `api/acabados/kissflow_sda/sda_handler.php` a una nueva ubicación relevante para el PQR. Por ejemplo:
`api/pqr/kissflow_pqr/pqr_handler.php`.

**Paso 3: Modificar el Nuevo Handler (`pqr_handler.php`)**

Este es el paso más importante.

1.  **Ajustar el Nombre del Proceso:** Cambia el valor de la variable `$process_name` al nombre exacto del proceso de PQR en Kiss Flow (ej: `Proceso_de_PQR`).

2.  **Ajustar la Búsqueda (si es necesario):** El PQR probablemente también necesite buscar al cliente en `DS_Documentos_Cliente`. Si es así, esta parte de la lógica puede permanecer igual.

3.  **Reconstruir el Payload (`$initial_payload`):** Adapta las claves y valores del array `$initial_payload` para que coincidan con los campos del proceso de PQR. Por ejemplo, en lugar de `Mz_Solar`, necesitarás campos como `Tipo_de_PQR`, `Subtipo_PQR` o `Descripcion_del_Problema`.

    *Ejemplo de payload para PQR:*
    ```php
    $initial_payload = [
        // Campo de lookup al cliente/propiedad
        'Ubicacion' => ['_id' => $kissflow_cliente_id],
        
        // Campos específicos del PQR
        'Tipo_de_PQR' => $input_data['tipo_pqr'], // Dato que vendría desde el disparador
        'Descripcion' => $input_data['descripcion'], // Dato que vendría desde el disparador
        'Fecha_de_Creacion' => date('Y-m-d'),
        // ... otros campos que el proceso PQR requiera
    ];
    ```

**Paso 4: Invocar el Nuevo Handler desde el Disparador**

En `api/pqr/pqr_create.php`, después de guardar el PQR en la base de datos local, añade el código para llamar al nuevo `pqr_handler.php`.

1.  **Construir el payload** que se enviará al handler (con los datos que el handler necesita, como `cedula`, `tipo_pqr`, `descripcion`, etc.).
2.  **Realizar la llamada cURL** a la URL del `pqr_handler.php`.

**Paso 5: Revisar la Lógica de "Submit"**

Decide si los nuevos tickets de PQR deben enviarse inmediatamente o si deben permanecer como borradores para una revisión manual. Si no deben enviarse automáticamente, simplemente elimina el "Paso 3: Envío del Proceso (Submit)" del `pqr_handler.php`.
