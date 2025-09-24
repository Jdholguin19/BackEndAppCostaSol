## Plan para la Nueva Sección de Resumen de Tareas

Para poder implementar la nueva sección de resumen de tareas de manera efectiva, necesito algunas aclaraciones sobre los requisitos. Por favor, responde a las siguientes preguntas:

### 1. Ubicación de la Nueva Sección
- ¿Dónde te gustaría que se ubique esta nueva sección de resumen? ¿Debajo del Gantt actual, en una pestaña separada, o en una página completamente nueva?
- Si es debajo del Gantt, ¿debería ser colapsable/expandible? Si me gustaria debajo de l gant actual, claro deberia ser colapsable/expandible

### 2. Datos de Tareas y Subtareas
- La imagen de referencia muestra una estructura jerárquica. ¿La jerarquía de tareas (`parent_id`) ya está bien definida y disponible en la respuesta de `data.php` o `get_tasks_by_project.php`?
- ¿Cómo se define una "subtarea" en el contexto de la base de datos? ¿Es cualquier tarea que tiene un `parent_id` diferente de 0 o `null`? Sobre estas pregunta te voy a pasar un ejemplo mi base de datos para que entiendas mejor el contexto
- Para "Total de Subtareas": ¿Debe contar solo las subtareas directas de una tarea principal, o todas las subtareas descendientes (sub-subtareas, etc.)? debe ser todas las subtareas descendientes y subtareas

### 3. Cálculo de Progreso y Duración
- Para las tareas principales (padres), ¿el "Progreso" y la "Duración" deben ser un resumen/agregado de sus subtareas, o deben ser valores definidos directamente en la tarea padre? Si es un agregado, ¿cómo se calcula (promedio, suma ponderada, etc.)? Para las tareas padres debera ser un promedio de todas las subtareas y subtareas descendientes

### 4. Relación (Gantt_links)
- Mencionas "Aquí todavía no lo tengo claro ya que no hemos implementado la relación como yo quiero del todo esto pero aquí puedes poner el id de momento de la relación de la tarea si no tiene los del como 'No tiene'".
- ¿Te refieres a las dependencias entre tareas (links) que se muestran en el Gantt? Si
- ¿Qué información específica de la relación te gustaría mostrar? ¿El ID de la tarea predecesora/sucesora, el tipo de relación (Fin a Inicio, Inicio a Inicio, etc.)? Correcto
- ¿Deberíamos considerar las `cross_project_links` también en esta sección? No esta no ya que no se si se va a quedar o se va a eliminar

### 5. Diseño y Estilo
- Mencionas "un diseño básico tu lo puedes mejorar". ¿Hay alguna guía de estilo o componente UI existente en el proyecto que deba seguir para el diseño de esta sección? (Por ejemplo, clases CSS de Bootstrap, Material Design, etc., aunque el proyecto es PHP puro, podría haber un CSS base). Claro sobre el tema del diseño es solo con css y tambien puedes usar boostrap
- Para el "gráfico pastel": ¿Qué librería de gráficos prefieres usar (si hay alguna ya en el proyecto) o debería buscar una librería ligera de JavaScript (ej. Chart.js, D3.js)? ¿Qué datos específicos debería representar el gráfico (ej. progreso de subtareas, número de subtareas por estado)? Correcto, aqui puedes usar tanto una libreria de graficos como una libreria ligera, y los datos que debe presentar son ejemplo el las subtareas que hay en esa tarea padre 

### 6. Actualización de Datos
- ¿Cómo se debe actualizar esta sección? ¿Automáticamente cuando se selecciona un nuevo proyecto, o cuando se guarda una tarea, o se necesita un botón de "Actualizar"? Se debe actualizar cuando se crea una nueva/actuliza/borra una tarea o simplemente un boton que diga actualizar esto va a depende que que tanto se puede implementar 

### 7. Tecnología
- Dado que el proyecto usa PHP puro y JS vanilla, ¿debo mantener esta misma aproximación para la nueva sección (PHP para la API, JS para la lógica del frontend y renderizado del DOM)? Si