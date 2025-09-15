# Plan de Acción: Integración con Kiss Flow y Chatbot

## Resumen de la Estrategia

El objetivo es construir un sistema robusto para sincronizar datos de Kiss Flow a una base de datos local y permitir la interacción con estos datos a través de un chatbot. La arquitectura se basa en el desacoplamiento para asegurar eficiencia y escalabilidad.

1.  **Proceso de Sincronización (Backend - `sync_kissflow.php`):** Un script automatizado se conecta periódicamente a la API de Kiss Flow, obtiene los registros (nuevos o actualizados) de un proceso específico y los guarda en una base de datos MySQL local.
2.  **Chatbot Interactivo (Frontend/Backend - `chat.html`, `chat.js`, `chatbot_backend.php`):** La interfaz del chatbot consulta la base de datos local para obtener respuestas rápidas y eficientes, ofreciendo búsquedas simples, resúmenes de estado y búsquedas avanzadas.

---

## Fase 1: Sincronización de Datos (Kiss Flow -> MySQL)

Esta fase es la base de todo el sistema, asegurando que los datos de Kiss Flow estén disponibles localmente.

**Paso 1: Análisis de la API de Kiss Flow**
*   **Acción:** Entender la API de Kiss Flow para el proceso específico que se desea sincronizar.
    *   **Autenticación:** Se utilizan `X-Access-Key-ID` y `X-Access-Key-Secret` en las cabeceras de las solicitudes cURL. Las credenciales se definen en `kiss_flow/config.php`.
    *   **Endpoints:** Para obtener los ítems de un proceso, se usa el endpoint `/process/2/{accountId}/{processName}/myitems`. El `processName` es crucial y debe coincidir exactamente con el nombre del proceso en Kiss Flow (ej. `Emisio_n_de_Pagos_`).
    *   **Filtrado:** La API permite paginación (`page_number`, `page_size`). La sincronización se realiza de forma incremental usando la columna `_modified_at` para traer solo los registros que han cambiado desde la última sincronización.

**Paso 2: Diseño de la Base de Datos Local**
*   **Acción:** Crear una tabla en tu base de datos MySQL que refleje la estructura de los datos del proceso de Kiss Flow. Es fundamental que la tabla tenga una clave única (ej. `kissflow_item_id`) para permitir la actualización de registros existentes.
*   **Archivo de Configuración DB:** La conexión a la base de datos para el módulo Kiss Flow se configura en `kiss_flow/config/db.php`.
*   **Ejemplo de SQL (para `Emisio_n_de_Pagos_` - `portalao_bdu_kissflow.sql`):**
    ```sql
    CREATE TABLE `kissflow_emision_pagos` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `kissflow_item_id` varchar(50) NOT NULL UNIQUE,
      `kissflow_activity_id` varchar(50) DEFAULT NULL,
      `Name` varchar(255) DEFAULT NULL,
      `_created_at` datetime DEFAULT NULL,
      `_completed_at` datetime DEFAULT NULL,
      `_status` varchar(100) DEFAULT NULL,
      -- ... (otras columnas que reflejan los campos del proceso de Kiss Flow)
      `_modified_at` datetime DEFAULT NULL,
      `fecha_sincronizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `kissflow_item_id` (`kissflow_item_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
    ```
    *   **Nota:** Para otros procesos, deberás adaptar el nombre de la tabla y todas las columnas para que coincidan con los campos de ese proceso específico en Kiss Flow.

**Paso 3: Creación del Script de Sincronización (`sync_kissflow.php`)**
*   **Acción:** Desarrollar un script PHP (`C:\xampp\htdocs\BackEndAppCostaSol\kiss_flow\sync_kissflow.php`) que realice la lógica de sincronización.
*   **Lógica del script:**
    1.  **Inclusión de Configuraciones:** Incluye `kiss_flow/config/db.php` (para la conexión a la DB) y `kiss_flow/config.php` (para las credenciales de la API de Kiss Flow).
    2.  **Obtención de `last_sync_date`:** Consulta `MAX(_modified_at)` de la tabla local para saber desde cuándo buscar actualizaciones en Kiss Flow.
    3.  **Bucle de Paginación:** Itera a través de las páginas de resultados de la API de Kiss Flow (`/process/2/{accountId}/{processName}/myitems`).
    4.  **Filtrado por `_modified_at`:** Solo procesa los ítems de Kiss Flow cuya fecha de modificación (`_modified_at`) sea posterior a `last_sync_date`. Esto asegura actualizaciones incrementales.
        *   **Importante:** Para una **sincronización inicial completa**, esta lógica de filtrado por `_modified_at` se puede **desactivar temporalmente** (comentando o eliminando el bloque de código) para asegurar que todos los registros históricos sean traídos. Una vez completada la sincronización inicial, se debe **volver a activar** para futuras actualizaciones incrementales.
    5.  **Obtención de Detalles del Ítem:** Para cada ítem resumido, se realiza una llamada adicional a la API (`/process/2/{accountId}/{processName}/{item_id}/{activity_instance_id}`) para obtener todos los detalles del registro.
    6.  **Inserción/Actualización (Upsert):** Utiliza una consulta `INSERT ... ON DUPLICATE KEY UPDATE` para guardar los datos en la tabla local. Si `kissflow_item_id` ya existe, el registro se actualiza; de lo contrario, se inserta uno nuevo. Esto es clave para evitar duplicados y mantener los datos actualizados.
    7.  **Manejo de Errores:** Incluye `try-catch` y `error_log()` para registrar cualquier problema durante la conexión a la API o la base de datos.

**Paso 4: Automatización del Script**
*   **Acción:** Configurar una tarea programada (Cron Job en Linux/macOS o Programador de Tareas en Windows) para que ejecute `sync_kissflow.php` periódicamente (ej. cada 1 o 4 horas).
*   **Ejemplo de comando para Cron Job (ejecutar cada 4 horas):**
    ```bash
    0 */4 * * * /usr/bin/php C:/xampp/htdocs/BackEndAppCostaSol/kiss_flow/sync_kissflow.php
    ```

---

## Fase 2: Chatbot Interactivo

Con los datos ya en nuestra base de datos local, podemos crear el chatbot.

**Paso 5: Creación de la Interfaz del Chat (`chat.html`)**
*   **Acción:** Un archivo HTML (`C:\xampp\htdocs\BackEndAppCostaSol\kiss_flow\chat.html`) con la estructura básica de un chat.
    *   Un `div` (`chat-window`) para mostrar los mensajes.
    *   Un `input` de texto (`user-input`) y un `button` (`send-button`) para la interacción.
    *   Un `div` (`status-summary`) para mostrar el resumen de estados de las emisiones de pago.
    *   Estilos básicos con `chat.css` para la apariencia.

**Paso 6: Creación del Backend del Chat (`chatbot_backend.php`)**
*   **Acción:** Un script PHP (`C:\xampp\htdocs\BackEndAppCostaSol\kiss_flow\chatbot_backend.php`) que actúa como el "cerebro" del bot.
*   **Estructura:** Implementado como una clase `Chatbot` para una mejor organización y mantenibilidad.
*   **Lógica del script (`Chatbot::handleQuery`):**
    1.  Recibe la consulta del usuario desde el frontend (vía `POST` en formato JSON).
    2.  **Manejo de Errores PHP:** Incluye `error_reporting(E_ALL); ini_set('display_errors', '0');` al inicio para suprimir la salida de errores PHP al navegador y asegurar respuestas JSON limpias.
    3.  **Comandos de Estado (`getStatusSummary`):** Si la consulta es "resumen estados", "estados" o "status", devuelve el conteo de ítems por estado (`Completed`, `InProgress`, `Rejected`, `Withdrawn`) desde la base de datos local. La respuesta incluye `type: 'status_summary'`.
    4.  **Búsqueda Avanzada (`advancedSearch`):** Si la consulta comienza con "buscar ", activa la lógica de búsqueda avanzada.
        *   **Formato de Consulta:** `buscar campo:"valor" [AND campo:"valor"] [...]`
        *   **Campos Soportados:** `proveedor`, `estado`, `monto`, `fecha_de_pago`, `fecha_de_factura`, `request_number`, `motivo`.
        *   **Búsquedas de Rango:**
            *   `monto_mayor_que:X`
            *   `monto_menor_que:Y`
            *   `monto_entre:X,Y`
            *   `fecha_despues_de:YYYY-MM-DD`
            *   `fecha_antes_de:YYYY-MM-DD`
        *   **Lógica:** Parsea la cadena de consulta, construye dinámicamente la cláusula `WHERE` de SQL con consultas preparadas y devuelve hasta 10 resultados.
        *   **Ejemplos de Uso:**
            *   `buscar proveedor:"Romance Eventos" AND estado:"InProgress"`
            *   `buscar monto_mayor_que:1000`
            *   `buscar fecha_de_pago_despues_de:2024-01-01`
            *   `buscar monto_entre:50,150`
            *   `buscar estado:"Rejected" AND motivo:"duplicado"`
    5.  **Búsqueda Simple (`searchRecords`):** Si no es un comando de estado ni una búsqueda avanzada, realiza una búsqueda simple:
        *   Si la consulta es numérica, busca por `request_number`.
        *   Si es texto, busca por `Proveedor` o `Motivo` (usando `LIKE`).
        *   Devuelve el primer resultado encontrado.
    6.  **Respuestas JSON:** Todas las respuestas se devuelven en formato JSON, indicando `ok: true/false` y un `mensaje` o `data`.

**Paso 7: Conexión Frontend-Backend (`chat.js`)**
*   **Acción:** El código JavaScript (`C:\xampp\htdocs\BackEndAppCostaSol\kiss_flow\chat.js`) maneja la interacción del usuario y la comunicación con el backend.
*   **Lógica del script:**
    1.  **Carga Inicial:** Al cargar la página, realiza una solicitud a `chatbot_backend.php` con la consulta "resumen estados" para obtener y mostrar el resumen de estados en el `status-summary` div.
    2.  **Envío de Mensajes:** Cuando el usuario envía un mensaje:
        *   Muestra el mensaje del usuario en la ventana del chat.
        *   Envía la consulta a `chatbot_backend.php` usando `fetch` (POST, JSON).
        *   Recibe la respuesta JSON del backend.
        *   Si la respuesta es de tipo `status_summary`, actualiza el `status-summary` div.
        *   Muestra el `mensaje` del bot en la ventana del chat.

---

## Consideraciones Adicionales

*   **Seguridad:** Las claves de la API de Kiss Flow (`kiss_flow/config.php`) deben almacenarse de forma segura (fuera del directorio web público si es posible, o usando variables de entorno). Siempre se usan consultas preparadas para prevenir inyección SQL.
*   **Manejo de Errores:** Ambos scripts (`sync_kissflow.php` y `chatbot_backend.php`) registran errores en el log del servidor (`error_log()`) para facilitar la depuración, sin exponer detalles al usuario final.
*   **Escalabilidad:** Esta arquitectura desacoplada es ideal para futuras integraciones con motores de IA más complejos, ya que el motor de IA podrá consultar la base de datos local de forma rápida y eficiente sin sobrecargar la API de Kiss Flow.

---

## Adaptación para Nuevos Procesos (Ej. `Tarjetas_de_Tickets_Atenci_n_PQR`)

Para adaptar esta solución a un nuevo proceso de Kiss Flow, sigue estos pasos:

1.  **Identificar el `processName`:** Obtén el nombre exacto del nuevo proceso en Kiss Flow (ej. `Tarjetas_de_Tickets_Atenci_n_PQR`). Este será el valor de la variable `$processName` en `sync_kissflow.php`.

2.  **Analizar la Estructura de Datos del Nuevo Proceso:**
    *   En Kiss Flow, revisa los campos y tipos de datos del nuevo proceso.
    *   Presta especial atención a los campos que necesitarás sincronizar y los que usarás para búsquedas.

3.  **Crear una Nueva Tabla MySQL:**
    *   Diseña una nueva tabla en tu base de datos local (ej. `kissflow_pqr_tickets`) con columnas que coincidan con los campos del nuevo proceso.
    *   Asegúrate de incluir un `kissflow_item_id` como `VARCHAR(50) NOT NULL UNIQUE` y `_modified_at` como `DATETIME` para la sincronización.
    *   **Ejemplo (adaptar según el proceso):**
        ```sql
        CREATE TABLE `kissflow_pqr_tickets` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `kissflow_item_id` varchar(50) NOT NULL UNIQUE,
          `_created_at` datetime DEFAULT NULL,
          `_status` varchar(100) DEFAULT NULL,
          `Ticket_Number` varchar(255) DEFAULT NULL,
          `Client_Name` varchar(255) DEFAULT NULL,
          `Issue_Description` TEXT DEFAULT NULL,
          -- ... otros campos del proceso PQR
          `_modified_at` datetime DEFAULT NULL,
          `fecha_sincronizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `kissflow_item_id` (`kissflow_item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ```

4.  **Modificar `sync_kissflow.php`:**
    *   **Cambiar `$processName`:** Actualiza la variable `$processName` al nombre del nuevo proceso (ej. `Tarjetas_de_Tickets_Atenci_n_PQR`).
    *   **Actualizar Consulta SQL:** Modifica la consulta `INSERT ... ON DUPLICATE KEY UPDATE` para que apunte a la nueva tabla y sus columnas.
        *   Asegúrate de que la lista de columnas en `INSERT` y `VALUES` coincida con la nueva tabla.
        *   Ajusta la parte `ON DUPLICATE KEY UPDATE` para actualizar los campos relevantes.
    *   **Ajustar `bind_param`:** La cadena de tipos (`$types`) y el orden de los parámetros en `$params_to_bind` deben coincidir exactamente con las columnas de la nueva tabla y el orden en que extraes los datos de la respuesta de la API de Kiss Flow.
    *   **Modificar Mapeo de Datos:** En la sección donde se construye `$params_to_bind`, adapta la extracción de datos (`$item['CampoDeKissFlow'] ?? null`) para que coincida con los nombres de los campos del nuevo proceso. Presta atención a los campos de tipo `_files` o arrays que requieren `get_file_names` o `implode`.

5.  **Modificar `chatbot_backend.php` (Opcional, si el chatbot necesita consultar el nuevo proceso):**
    *   Si el chatbot necesita buscar en la nueva tabla, deberás añadir o adaptar la lógica de búsqueda.
    *   **`searchRecords`:** Si quieres búsquedas simples por `Ticket_Number` o `Issue_Description`, modifica la consulta SQL y los campos de búsqueda.
    *   **`advancedSearch`:**
        *   Actualiza `$select_columns` para incluir los campos relevantes de la nueva tabla.
        *   Actualiza `$field_map` para mapear los nombres amigables a las columnas de la nueva tabla (ej. `'ticket_numero' => 'Ticket_Number'`).
        *   Ajusta la lógica de formateo de resultados para mostrar los campos del nuevo proceso.
    *   **`getStatusSummary`:** Si el nuevo proceso tiene estados que quieres resumir, asegúrate de que la columna de estado en la nueva tabla se llame `_status` o adapta la consulta.

Siguiendo estos pasos, podrás extender la solución a otros procesos de Kiss Flow de manera modular.