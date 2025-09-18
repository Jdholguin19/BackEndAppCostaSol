# Plan de Acción y Sugerencias: Integración de Selección de Acabados con Kiss Flow

Este documento resume la implementación de la integración de la Selección de Acabados con Kiss Flow y propone mejoras futuras.

---

## 1. Resumen de la Integración Actual

### Objetivo

La integración actual tiene como objetivo iniciar automáticamente el proceso de "Selección de Acabados" en Kiss Flow cada vez que un usuario guarda su selección en la aplicación local. El proceso en Kiss Flow se inicia con los datos iniciales del cliente y la propiedad, y se avanza automáticamente a la siguiente etapa (la del encargado de los planos).

### Archivos Clave

*   `api/guardar_seleccion_acabados.php`: Script que actúa como disparador de la integración, llamando al handler de Kiss Flow después de guardar la selección localmente.
*   `api/acabados/kissflow_sda/sda_handler.php`: Contiene la lógica principal de comunicación con la API de Kiss Flow para este proceso.

### Flujo de la API (`sda_handler.php`)

El `sda_handler.php` ejecuta un flujo de 2 pasos para interactuar con Kiss Flow:

1.  **Búsqueda del Cliente:**
    *   Toma la `cedula` del usuario de la aplicación local.
    *   Busca al cliente en el Dataset `DS_Documentos_Cliente` de Kiss Flow para obtener su `_id` interno y su `Convenio`.

2.  **Creación y Envío del Proceso:**
    *   Realiza una petición `POST` a la URL del proceso `Copia_de_Eleccio_n_de_Acabados_y_Adicion` en Kiss Flow.
    *   El **payload inicial** de esta petición incluye los siguientes campos, obtenidos de la aplicación local o de la búsqueda en Kiss Flow:
        *   `Cliente` (`_id` del cliente de Kiss Flow)
        *   `Ubicacion` (`_id` del cliente de Kiss Flow)
        *   `Identificacion` (Cédula del usuario)
        *   `Nombre_del_Cliente` (Nombre completo del usuario)
        *   `Mz_Solar` (Manzana y Solar de la propiedad)
        *   `Etapa` (Etapa de la propiedad)
        *   `Modelo_1` (Modelo de la propiedad)
        *   `Convenio` (Obtenido de los datos del cliente en Kiss Flow)
        *   `Fecha` (Fecha actual)
    *   Después de la creación exitosa, realiza una segunda petición `POST` a la URL `/submit` del item creado para avanzar el proceso a la siguiente etapa en Kiss Flow.

---

## 2. Posibles Mejoras, Sugerencias y Preguntas

### 2.1. Integración de Datos Específicos de Acabados

*   **Pregunta:** Actualmente, solo se envían los datos iniciales del cliente y la propiedad. ¿Se desea enviar también los datos específicos de la selección de acabados (kit, color, paquetes adicionales) a Kiss Flow?
*   **Sugerencia:** Si la respuesta es sí, esto requeriría:
    *   Identificar los campos exactos en Kiss Flow para `Kit_Seleccionado`, `Color_Seleccionado`, `Paquetes_Adicionales`, etc. (posiblemente en una etapa posterior del proceso de Kiss Flow).
    *   Modificar `api/guardar_seleccion_acabados.php` para pasar estos datos a `sda_handler.php`.
    *   Modificar `sda_handler.php` para incluir estos datos en el payload inicial (si Kiss Flow lo permite en la primera etapa) o en un paso de actualización posterior (si Kiss Flow lo requiere en una etapa diferente).

### 2.2. Manejo de Errores y Notificaciones

*   **Pregunta:** Actualmente, los errores en la comunicación con Kiss Flow solo se registran en el log de errores de PHP. ¿Se desea notificar al usuario de la aplicación si la creación en Kiss Flow falla?
*   **Sugerencia:** Se podría añadir lógica en `api/guardar_seleccion_acabados.php` para capturar la respuesta de `sda_handler.php` y, si `ok` es `false`, mostrar un mensaje al usuario o registrarlo en una tabla de auditoría específica.

### 2.3. Flexibilidad del Proceso

*   **Pregunta:** ¿El proceso de Kiss Flow siempre debe avanzar automáticamente al siguiente paso (`/submit`)? ¿O hay casos en los que debería quedarse como borrador (similar a la idea inicial de CTG para revisión manual)?
*   **Sugerencia:** Si se necesita flexibilidad, se podría añadir un parámetro al `sda_handler.php` (ej. `finalizar_proceso=true/false`) para controlar si se hace el `submit` final o no, permitiendo que el ticket se quede como borrador si es necesario.

### 2.4. Campos Adicionales

*   **Pregunta:** ¿Hay otros campos en la etapa inicial del proceso de Kiss Flow que deban ser rellenados y que no estemos enviando actualmente? (e.g., `Email_1`, `Phone` del cliente, si no se autocompletan en Kiss Flow a partir de la `Identificacion` y `Nombre_del_Cliente`).

---




x
Este documento sirve como base para futuras mejoras y adaptaciones de la integración con Kiss Flow.