# Información del Módulo de Gantt Standalone

## 1. Introducción

Este documento describe el funcionamiento del módulo de diagrama de Gantt, una herramienta personalizada para la gestión de proyectos y tareas. La interfaz está construida utilizando la librería DHTMLX Gantt, pero gran parte de la lógica de edición y guardado ha sido personalizada para cumplir con requisitos específicos.

## 2. Arquitectura General

El sistema se divide en dos componentes principales:

-   **Frontend:** Responsable de la interfaz y la interacción con el usuario.
    -   `index.html`: Contiene la estructura principal del Gantt y los formularios modales personalizados para la edición de tareas y la creación de dependencias.
    -   `script.js`: Contiene toda la lógica de JavaScript para inicializar el Gantt, manejar eventos, abrir los modales, guardar datos y comunicarse con el backend.
    -   `style.css`: Define los estilos para los componentes personalizados, como los modales.

-   **Backend:** Un conjunto de scripts PHP que actúan como una API para comunicar el Gantt con la base de datos MySQL.
    -   `save.php`: Gestiona la creación, actualización y eliminación de tareas y enlaces.
    -   `data.php`: Carga las tareas y enlaces de un proyecto específico.
    -   `get_projects.php`, `get_users.php`, etc.: Scripts auxiliares para obtener datos específicos como listas de proyectos o usuarios.

## 3. Flujo de Funcionamiento

1.  **Carga Inicial:** Al abrir `index.html`, `script.js` se ejecuta y pide al servidor la lista de proyectos (`get_projects.php`). Al seleccionar un proyecto, se cargan sus datos (`data.php`).
2.  **Edición de Tareas:** Para editar o crear tareas, se utiliza un **formulario (lightbox) personalizado**, no el que viene por defecto con la librería. El lightbox por defecto fue deshabilitado para tener control total sobre el formulario.
3.  **Creación de Tareas:**
    -   Se puede crear una tarea principal desde el botón "Añadir Tarea" en la cabecera.
    -   Se pueden crear subtareas desde el icono `+` en cada fila de la parrilla.
    -   Ambas acciones abren el formulario de edición con valores por defecto. La tarea no se crea realmente hasta que se pulsa "Guardar".
4.  **Guardado de Cambios:**
    -   Al pulsar "Guardar", la función `saveCustomTask` distingue si la tarea es nueva o es una actualización.
    -   Si es **nueva**, construye un objeto con los datos del formulario y llama a `gantt.addTask()`. Este método se encarga de añadir la tarea y comunicárselo al **DataProcessor** con el estado `inserted`.
    -   Si es una **actualización**, modifica el objeto de la tarea existente y llama a `gantt.updateTask()` y `dp.sendData()` para forzar el envío de los datos al servidor.
    -   El **DataProcessor** es el componente de DHTMLX que gestiona la comunicación con el backend (`save.php`).

## 4. Resumen de Correcciones Clave Implementadas

Durante el desarrollo y depuración, se realizaron los siguientes arreglos importantes:

1.  **Implementación de Lightbox Personalizado:** Se abandonó el lightbox por defecto de DHTMLX y se implementó toda la lógica para usar un modal HTML propio, lo que dio control total sobre la edición de tareas.

2.  **Solución a Fallo de Creación de Tareas:** Se detectó que el evento `onTaskCreated` no registraba las tareas de forma fiable. Se rediseñó el flujo para usar `gantt.addTask()` al momento de guardar, solucionando una cadena de errores que impedían crear nuevas tareas.

3.  **Solución a Fallo de Guardado (Status `updated`):** Se diagnosticó mediante los logs del servidor que las nuevas tareas se enviaban incorrectamente como "actualizadas". El cambio a `gantt.addTask()` corrigió este comportamiento, asegurando que se envíen como `inserted`.

4.  **Solución a Fallo de Guardado Parcial:** Se descubrió que el backend (`save.php`) esperaba recibir todos los campos de una tarea en cada actualización. Se solucionó configurando el DataProcessor con `dp.setUpdateMode("row")` para forzar el envío de la fila completa de datos, satisfaciendo los requisitos del backend.

5.  **Corrección de Guardado de Fechas:** Se detectó que el formato de fecha que se enviaba al servidor no era compatible con MySQL. Se solucionó añadiendo `gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";` para estandarizar el formato.

6.  **Corrección de Guardado de Duración:** El último y más difícil problema. Se descubrió que el auto-cálculo del Gantt revertía el cambio en la duración. La solución fue **calcular y actualizar explícitamente la fecha de fin (`task.end_date`)** en el cliente justo antes de guardar, para que todos los datos de la tarea (inicio, fin y duración) fueran consistentes.

7.  **Implementación de Dependencias entre Proyectos:** Se añadió la funcionalidad completa para crear y visualizar dependencias con tareas de otros proyectos, incluyendo un modal secundario y la comunicación con los scripts PHP correspondientes.

8.  **Ajustes de Interfaz:** Se solucionaron problemas de superposición de ventanas (z-index) y se mejoró la lógica de los diálogos de confirmación.
