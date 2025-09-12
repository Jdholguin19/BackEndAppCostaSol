# Plan de Acción: Integración con Kiss Flow y Chatbot

## Resumen de la Estrategia

El objetivo es construir un sistema que sirva de base para un futuro chatbot con IA. La arquitectura elegida consiste en desacoplar la obtención de datos de la interacción con el usuario.

1.  **Proceso en Segundo Plano (Backend):** Un script automatizado se conectará periódicamente a la API de Kiss Flow, obtendrá solo los registros nuevos o actualizados y los guardará en una base de datos MySQL local.
2.  **Proceso Interactivo (Frontend/Chatbot):** La interfaz del chatbot consultará únicamente la base de datos local para obtener respuestas, asegurando velocidad y eficiencia.

---

## Fase 1: Sincronización de Datos (Kiss Flow -> MySQL)

Esta fase es la base de todo el sistema.

**Paso 1: Análisis de la API de Kiss Flow**
*   **Acción:** Investigar la documentación de la API de Kiss Flow para entender:
    *   **Autenticación:** ¿Cómo obtener un API Key o token de acceso?
    *   **Endpoints:** ¿Qué URL específica nos dará los datos que necesitamos?
    *   **Filtrado:** ¿Permite la API filtrar por fecha de creación o modificación? Esto es **crucial** para no descargar todos los datos cada vez. Buscaremos parámetros como `created_after`, `modified_since`, etc.

**Paso 2: Diseño de la Base de Datos Local**
*   **Acción:** Crear una tabla en tu base de datos MySQL que refleje la estructura de los datos que recibes de Kiss Flow.
*   **Ejemplo de SQL (a adaptar):**
    ```sql
    CREATE TABLE IF NOT EXISTS kissflow_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kissflow_record_id VARCHAR(255) UNIQUE NOT NULL, -- ID único del registro en Kiss Flow para evitar duplicados
        campo1 VARCHAR(255),
        campo2 TEXT,
        fecha_creacion_kissflow DATETIME, -- Fecha del registro en Kiss Flow
        fecha_sincronizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ```

**Paso 3: Creación del Script de Sincronización (`sync_kissflow.php`)**
*   **Acción:** Desarrollar un script en PHP que realice la lógica de sincronización.
*   **Lógica del script:**
    1.  Conectarse a la base de datos MySQL.
    2.  Obtener la fecha del último registro sincronizado: `SELECT MAX(fecha_creacion_kissflow) FROM kissflow_data;`.
    3.  Hacer una llamada a la API de Kiss Flow usando esa fecha para pedir solo los registros más nuevos.
    4.  Recorrer la respuesta de la API.
    5.  Para cada registro, insertarlo en la tabla `kissflow_data` usando **consultas preparadas (prepared statements)** para evitar inyección SQL. Usar `INSERT IGNORE` o verificar la existencia del `kissflow_record_id` para no crear duplicados.
    6.  Incluir un buen manejo de errores (¿qué pasa si la API no responde o la base de datos falla?).

**Paso 4: Automatización del Script**
*   **Acción:** Configurar una tarea programada (Cron Job en Linux/macOS o Programador de Tareas en Windows) para que ejecute `sync_kissflow.php` cada 1 o 4 horas.
*   **Ejemplo de comando para Cron Job (ejecutar cada 4 horas):**
    ```bash
    0 */4 * * * /usr/bin/php C:/xampp/htdocs/BackEndAppCostaSol/kiss_flow/sync_kissflow.php
    ```

---

## Fase 2: Chatbot Básico (Prueba de Concepto)

Con los datos ya en nuestra base de datos, podemos crear el chat.

**Paso 5: Creación de la Interfaz del Chat (`chat.html`)**
*   **Acción:** Un archivo HTML simple con:
    *   Un `div` que actuará como la ventana del chat.
    *   Un `input` de texto para que el usuario escriba.
    *   Un `button` para enviar.
    *   Estilos básicos con CSS para que parezca un chat.

**Paso 6: Creación del Backend del Chat (`chatbot_backend.php`)**
*   **Acción:** Un script PHP que será el "cerebro" del bot.
*   **Lógica del script:**
    1.  Recibe la pregunta del usuario desde el frontend (vía `POST`).
    2.  Sanitiza la entrada del usuario.
    3.  Se conecta a la base de datos MySQL.
    4.  Construye una consulta `SELECT` usando **consultas preparadas** para buscar información relevante en la tabla `kissflow_data`.
        ```php
        // Ejemplo de consulta
        $termino_busqueda = "%" . $_POST['mensaje'] . "%";
        $stmt = $conn->prepare("SELECT * FROM kissflow_data WHERE campo1 LIKE ?");
        $stmt->bind_param("s", $termino_busqueda);
        ```
    5.  Ejecuta la consulta y formatea los resultados.
    6.  Devuelve la respuesta en formato JSON al frontend.

**Paso 7: Conexión Frontend-Backend (`script.js`)**
*   **Acción:** Escribir el código JavaScript que se ejecutará en `chat.html`.
*   **Lógica del script:**
    1.  Cuando el usuario envía un mensaje, se previene el envío del formulario.
    2.  Se muestra el mensaje del usuario en la ventana del chat.
    3.  Se usa la API `fetch` de JavaScript para enviar el mensaje a `chatbot_backend.php`.
    4.  Se recibe la respuesta JSON del backend.
    5.  Se muestra la respuesta del bot en la ventana del chat.

---

## Consideraciones Adicionales

*   **Seguridad:** Las claves de la API de Kiss Flow no deben estar directamente en el código. Guárdalas en un archivo de configuración (`config.php`) fuera del directorio web público si es posible.
*   **Manejo de Errores:** Ambos scripts (`sync_kissflow.php` y `chatbot_backend.php`) deben registrar errores en un archivo de log para facilitar la depuración.
*   **Escalabilidad:** Esta arquitectura es la ideal para cuando decidas integrar una IA. El motor de IA podrá consultar la base de datos local de forma rápida y eficiente.
