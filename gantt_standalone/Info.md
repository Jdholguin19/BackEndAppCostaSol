# Información del Módulo de Gantt Standalone

## 1. Introducción

Este documento describe el funcionamiento del módulo de diagrama de Gantt, una herramienta personalizada para la gestión de proyectos y tareas. La interfaz está construida utilizando la librería DHTMLX Gantt, pero gran parte de la lógica de edición y guardado ha sido personalizada para cumplir con requisitos específicos.

## 2. Arquitectura General

El sistema se divide en dos componentes principales:

-   **Frontend:** Responsable de la interfaz y la interacción con el usuario.
    -   `index.html`: Contiene la estructura principal del Gantt, incluyendo la selección de proyectos, botones de acción y los contenedores para los modales personalizados de edición de tareas y creación de dependencias entre proyectos.
    -   `script.js`: Contiene toda la lógica de JavaScript para:
        -   Inicializar y configurar DHTMLX Gantt.
        -   Manejar la carga y visualización de proyectos y tareas.
        -   Gestionar los modales personalizados para la creación/edición de tareas y la adición de dependencias entre proyectos.
        -   Comunicarse con el backend a través de `fetch` y el `DataProcessor` de DHTMLX para operaciones CRUD.
        -   Cargar y guardar los anchos de las columnas en `localStorage`.
        -   Manejar la carga de usuarios para la asignación de tareas.
    -   `style.css`: Define los estilos para los componentes personalizados, como los modales y la apariencia general del Gantt.

-   **Backend:** Un conjunto de scripts PHP que actúan como una API RESTful para comunicar el Gantt con la base de datos MySQL. Todos los scripts utilizan sentencias preparadas para la seguridad y `error_log` para el registro de errores.

## 3. APIs de Backend

Los siguientes scripts PHP gestionan la interacción con la base de datos:

-   **`create_project.php`**:
    -   **Método:** `POST`
    -   **Función:** Crea un nuevo proyecto en la tabla `gantt_projects`.
    -   **Parámetros:** `name` (nombre del proyecto).
    -   **Validación:** Verifica que el nombre no esté vacío y que no exista un proyecto con el mismo nombre.
    -   **Respuesta:** JSON indicando `success`, `message` y el `id` del nuevo proyecto.

-   **`data.php`**:
    -   **Método:** `GET`
    -   **Función:** Carga todas las tareas y enlaces asociados a un `project_id` específico desde las tablas `gantt_tasks` y `gantt_links`.
    -   **Parámetros:** `project_id`.
    -   **Respuesta:** JSON con dos arrays: `data` (tareas) y `links` (enlaces). Las fechas de inicio de las tareas se formatean a `YYYY-MM-DD`.

-   **`get_cross_project_links.php`**:
    -   **Método:** `GET`
    -   **Función:** Obtiene las dependencias entre proyectos para una tarea y proyecto dados.
    -   **Parámetros:** `task_id`, `project_id`.
    -   **Respuesta:** JSON con una lista de objetos de enlace, incluyendo nombres de tareas y proyectos de origen y destino.

-   **`get_projects.php`**:
    -   **Método:** `GET`
    -   **Función:** Recupera una lista de todos los proyectos disponibles de la tabla `gantt_projects`.
    -   **Respuesta:** JSON con un array de objetos de proyecto (`id`, `name`).

-   **`get_tasks_by_project.php`**:
    -   **Método:** `GET`
    -   **Función:** Obtiene una lista simplificada de tareas (id y texto) para un `project_id` específico. Utilizado para rellenar selectores en el frontend.
    -   **Parámetros:** `project_id`.
    -   **Respuesta:** JSON con un array de objetos de tarea (`id`, `text`).

-   **`get_users.php`**:
    -   **Método:** `GET`
    -   **Función:** Obtiene la lista de usuarios de la base de datos principal (`portalao_appcostasol`, tabla `usuario`).
    -   **Conexión:** Se conecta a una base de datos diferente (`MAIN_DB_NAME`).
    -   **Respuesta:** JSON con un array de objetos de usuario (`id`, `name` - concatenación de `nombres` y `apellidos`).

-   **`save_cross_project_link.php`**:
    -   **Método:** `POST`
    -   **Función:** Guarda una nueva dependencia entre proyectos en la tabla `gantt_cross_project_links`.
    -   **Parámetros:** `source_task_id`, `source_project_id`, `target_task_id`, `target_project_id`, `type`.
    -   **Validación:** Verifica que todos los parámetros obligatorios estén presentes y que la dependencia no exista ya.
    -   **Respuesta:** JSON indicando `success`, `message` y el `id` del nuevo enlace.

-   **`save.php`**:
    -   **Método:** `POST` (principalmente)
    -   **Función:** Es el endpoint principal para las operaciones CRUD de tareas y enlaces de DHTMLX Gantt. Recibe datos del `DataProcessor`.
    -   **Parámetros:** Los datos se envían en `$_POST` con claves prefijadas por un ID temporal (ej. `temp_id_text`, `temp_id_!nativeeditor_status`).
    -   **Acciones Soportadas:** `inserted`, `updated`, `deleted` para tareas; `inserted_link`, `updated_link`, `deleted_link` para enlaces.
    -   **Manejo de Datos:** Sanitiza y convierte los datos a los tipos correctos antes de interactuar con la base de datos.
    -   **Respuesta:** JSON con `action`, `sid` (ID temporal del cliente) y `tid` (ID real de la base de datos para inserciones).

## 4. Flujo de Funcionamiento Detallado

1.  **Carga Inicial:**
    -   Al cargar `index.html`, `script.js` se ejecuta.
    -   Se llama a `loadUsers()` para obtener la lista de usuarios de `api/get_users.php`.
    -   Se llama a `loadProjects()` para obtener la lista de proyectos de `api/get_projects.php` y poblar el selector de proyectos.
    -   Si hay proyectos, se selecciona el primero por defecto y se llama a `loadGanttData()` para cargar las tareas y enlaces de ese proyecto desde `api/data.php`.

2.  **Edición de Tareas (Modal Personalizado):**
    -   El lightbox por defecto de DHTMLX Gantt está deshabilitado (`gantt.config.show_lightbox = false;`).
    -   Un doble clic en una tarea (`onTaskDblClick`) o un clic en el botón "Añadir Tarea" (`addNewTask`) abre el modal `custom-lightbox-modal`.
    -   El modal se rellena con los datos de la tarea existente o con valores por defecto para una nueva tarea.
    -   Permite editar `text`, `start_date`, `duration`, `progress`, `owners` y `color`.
    -   También muestra las dependencias entre proyectos y permite añadir nuevas.

3.  **Creación de Tareas:**
    -   Se puede crear una tarea principal desde el botón "Nuevo Proyecto" o "Añadir Tarea" en la cabecera.
    -   Se pueden crear subtareas haciendo clic en el icono `+` en la columna "add_subtask" de la cuadrícula.
    -   Ambas acciones abren el formulario de edición con valores por defecto. La tarea no se crea en el Gantt hasta que se pulsa "Guardar".

4.  **Guardado de Cambios (Tareas):**
    -   Al pulsar "Guardar" en el modal de edición de tareas, la función `saveCustomTask()` se ejecuta.
    -   Si es una **nueva tarea**:
        -   Se construye un objeto `newTask` con los datos del formulario.
        -   Se llama a `gantt.addTask(newTask, parentId)` para añadirla al Gantt.
        -   `dp.sendData()` se llama explícitamente para forzar el envío inmediato de la tarea con el estado `inserted` al `save.php` del backend.
    -   Si es una **tarea existente**:
        -   Se recupera el objeto de la tarea del Gantt (`gantt.getTask(taskId)`).
        -   Se actualizan las propiedades del objeto de la tarea con los valores del formulario.
        -   Se recalcula `task.end_date` para asegurar la consistencia con `start_date` y `duration`.
        -   Se llama a `gantt.updateTask(taskId)` para refrescar la UI.
        -   `dp.sendData(taskId)` se llama explícitamente para enviar la actualización al `save.php` del backend.
    -   El `DataProcessor` (`dp`) está configurado con `dp.setUpdateMode("row")` para asegurar que todos los datos de la fila se envíen en cada actualización.

5.  **Guardado de Cambios (Dependencias entre Proyectos):**
    -   El modal `cross-link-modal` permite seleccionar un proyecto y una tarea de destino, así como el tipo de dependencia.
    -   La función `saveCrossProjectLink()` envía los datos de la dependencia a `api/save_cross_project_link.php` mediante `fetch`.

## 5. Resumen de Correcciones Clave Implementadas

Durante el desarrollo y depuración, se realizaron los siguientes arreglos importantes:

1.  **Implementación de Lightbox Personalizado:** Se abandonó el lightbox por defecto de DHTMLX y se implementó toda la lógica para usar un modal HTML propio, lo que dio control total sobre la edición de tareas y la integración de funcionalidades personalizadas como las dependencias entre proyectos.

2.  **Manejo Robusto de Creación y Actualización de Tareas:** Se rediseñó el flujo de `saveCustomTask` para distinguir claramente entre la creación (`gantt.addTask()`) y la actualización (`gantt.updateTask()`) de tareas, asegurando que el `DataProcessor` envíe el estado correcto (`inserted` o `updated`) al backend. Esto resolvió problemas donde las nuevas tareas se enviaban incorrectamente como actualizaciones.

3.  **Envío Completo de Datos de Tarea:** Se configuró el `DataProcessor` con `dp.setUpdateMode("row")` para garantizar que el backend (`save.php`) reciba todos los campos de una tarea en cada actualización, satisfaciendo sus requisitos y evitando la pérdida de datos.

4.  **Formato de Fechas Consistente:** Se añadió `gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";` para estandarizar el formato de fecha enviado al servidor, haciéndolo compatible con MySQL. Además, se implementó `parseDateCustom` y `formatDateToYYYYMMDD` en `script.js` para un manejo consistente de fechas en el frontend.

5.  **Consistencia en la Duración y Fecha de Fin:** Se implementó una lógica en `saveCustomTask` para recalcular y actualizar explícitamente `task.end_date` en el cliente justo antes de guardar. Esto asegura que la fecha de fin sea consistente con la fecha de inicio y la duración, evitando que el auto-cálculo del Gantt revierta los cambios de duración.

6.  **Implementación Completa de Dependencias entre Proyectos:** Se añadió la funcionalidad para crear, guardar y visualizar dependencias con tareas de otros proyectos, incluyendo un modal secundario (`cross-link-modal`) y la comunicación con los scripts PHP `save_cross_project_link.php` y `get_cross_project_links.php`.

7.  **Gestión de Usuarios Dinámica:** Se implementó la carga dinámica de usuarios desde la base de datos principal (`api/get_users.php`) para permitir la asignación de "Dueños" a las tareas, mejorando la flexibilidad y la gestión de recursos.

8.  **Mejoras en la Interfaz y Experiencia de Usuario:** Se realizaron ajustes en la interfaz, como la resolución de problemas de superposición de ventanas (z-index), la mejora de la lógica de los diálogos de confirmación y la carga/guardado de anchos de columna para una experiencia más fluida.