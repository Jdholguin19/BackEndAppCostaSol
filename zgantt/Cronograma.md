# Informe de Avance y Cronograma de Implementación



**Periodo del Informe:** 23 de Julio, 2025 – 10 de Septiembre, 2025

---

## Cronograma de Desarrollo por Semanas

### **Cimentación y Funcionalidades Base**

#### **Tarea: Diseño y Expansión de la Base de Datos**
*   **Descripción Detallada:** Se realizó un análisis de los requerimientos de las nuevas funcionalidades y se modificó la estructura de la base de datos para darles soporte. Esta fue una tarea crítica para asegurar la integridad y escalabilidad de los nuevos módulos.
*   **Cambios Realizados:**
    *   **Nuevas Tablas:** Se crearon `paquetes_adicionales`, `propiedad_paquetes_adicionales` (para el módulo de acabados) y `log_sincronizacion_outlook` (para depuración de la sincronización) etc.
    *   **Columnas Añadidas:** Se agregaron `costo` a `acabado_kit`, `duracion_minutos` a `agendamiento_visitas` y `leido` a `respuesta_ctg` y `respuesta_pqr`, etc.


---

### **Semana: Desarrollo del Nuevo Sistema de Agendamiento (Backend) (23 de julio – 1 de agosto)**

#### **Tarea 1: Modernización de la Interfaz de Agendamiento**
*   **Fechas Estimadas:** 23 de agosto - 1 de agosto
*   **Descripción Detallada:** Se modernizó la interfaz de `Front/cita_nueva.php` para ofrecer una experiencia de usuario de primer nivel.
*   **Implementación:**
    *   Se reemplazó el selector de fecha por un calendario interactivo (`flatpickr.js`).
    *   Se diseñó un selector de hora tipo "rueda" vertical con CSS (`scroll-snap`) para una selección más intuitiva en dispositivos móviles.

#### **Tarea 2: Desarrollo del Sistema de Notificaciones Push (OneSignal)**
*   **Fechas Estimadas:** 23 de julio - 1 de agosto
*   **Descripción Detallada:** Se implementó un sistema de notificaciones push para aumentar el engagement y comunicar novedades de forma instantánea. El foco fue crear una experiencia de usuario no intrusiva y profesional.
*   **Implementación:**
    *   **UX/UI:** Se diseñó una ventana emergente de suscripción personalizada, reemplazando el diálogo nativo de OneSignal. Se ocultó por completo el ícono de la campana para una integración visual limpia.
    *   **Control del Usuario:** Se añadió un `switch` en `Front/perfil.php` para que el usuario pueda gestionar su suscripción fácilmente.
    *   **Técnico:** Se corrigió un error crítico de autorización en `api/update_player_id.php` para garantizar la entrega fiable de notificaciones.

    #### **Tarea 3: Implementación de Agendamiento por Responsables**
*   **Fechas Estimadas:** 23 de julio - 1 de agosto
*   **Descripción Detallada:** Se creó una nueva funcionalidad para que el personal de CostaSol pueda agendar citas en nombre de los clientes, mejorando el servicio.
*   **Implementación:**
    *   Se desarrolló la nueva página `Front/cita_responsable.php`.
    *   Se creó la API `api/user_list.php` para poblar la lista de clientes seleccionables.
    *   Se adaptó `api/obtener_propiedades.php` para que pueda devolver las propiedades de un cliente específico al ser consultada por un responsable.

---

### **Semana: Funcionalidades para Responsables y Rediseño de Tickets (4 de agosto – 8 de agosto)**

#### **Tarea 1: Creación del Sistema de Perfil de Usuario**
*   **Fechas Estimadas:** 12 de julio - 19 de julio
*   **Descripción Detallada:** Se desarrolló desde cero la sección de perfil de usuario, una característica esencial para la personalización y gestión de la cuenta por parte del cliente.
*   **Implementación:**
    *   Se creó la interfaz `Front/perfil.php` con un diseño moderno y funcional.
    *   Se desarrolló la API `api/update_profile_picture.php` para manejar la lógica de subida, redimensionamiento y guardado de la foto de perfil en el servidor.
    *   Se modificó `Front/menu_front.php` para que el avatar del usuario enlace a esta nueva página.

#### **Tarea 2: Rediseño de la Interfaz de Hilo de Chat para CTG y PQR**
*   **Fechas Estimadas:** 4 de agosto - 8 de agosto
*   **Descripción Detallada:** Se mejoró radicalmente la forma en que se visualizan los tickets de servicio al cliente para que sean más fáciles de seguir.
*   **Implementación:**
    *   Las páginas `Front/ctg/ctg_detalle.php` y `Front/pqr/pqr_detalle.php` fueron rediseñadas para mostrar las respuestas como un hilo de conversación tipo chat Whatsapp, con burbujas de diálogo diferenciadas para el cliente y el responsable.

#### **Tarea 3: Implementación del Sistema de Envío de Correos Transaccionales**
*   **Fechas Estimadas:** 23 de julio - 3 de agosto
*   **Descripción Detallada:** Para mejorar la proactividad del equipo de servicio al cliente, se implementó un sistema de notificaciones por correo electrónico que se activa ante acciones clave del usuario. Se utilizó la API de Microsoft Graph para asegurar una entrega fiable y profesional.
*   **Implementación:**
    *   Se creó el script `correos/EnviarCorreoNotificacionResponsable.php`, que centraliza toda la lógica de autenticación con MS Graph y el envío de correos.
    *   Se integró este script en `api/ctg/ctg_create.php`, `api/pqr/pqr_create.php` y `api/cita/cita_create.php` para notificar al responsable asignado de forma inmediata.


### **Semana: Panel de Control y PWA (12 de agosto – 19 de agosto)**

#### **Tarea 2: Creación del Panel de Calendario Unificado**
*   **Fechas Estimadas:** 12 de agosto - 19 de agosto
*   **Descripción Detallada:** Se creó una herramienta visual para que los responsables puedan ver toda su agenda en un solo lugar.
*   **Implementación:**
    *   La página `Front/panel_calendario.php` utiliza la librería `FullCalendar.js` para renderizar un calendario completo que muestra tanto las citas creadas en la app como los eventos sincronizados desde Outlook (idea por desarrollarse), diferenciados por color.

#### **Tarea 3: Configuración de la Progressive Web App (PWA)**
*   **Fechas Estimadas:** 12 de agosto - 19 de agosto
*   **Descripción Detallada:** Se sentaron las bases para que la aplicación web se comporte como una aplicación nativa y pueda ser distribuida como un archivo APK en el futuro.
*   **Implementación:**
    *   Se creó y configuró el `manifest.json`, que define el nombre, los iconos, los colores y el comportamiento de la PWA.
    *   Se configuró el Service Worker de OneSignal (`OneSignalSDKWorker.js`) para gestionar las notificaciones push en segundo plano.
    *   Estos cambios permiten que la aplicación se pueda "Instalar" en el escritorio o en la pantalla de inicio de un dispositivo móvil.

---

### **Semana: Funcionalidades Avanzadas  (20 de agosto – 2 de septiembre)**

#### **Tarea 1: Desarrollo del Módulo de Selección de Acabados**
*   **Fechas Estimadas:** 20 de agosto - 2 de septiembre
*   **Descripción Detallada:** Se desarrolló el módulo interactivo completo para la selección de acabados, una de las funcionalidades más complejas y de mayor valor para el cliente.
*   **Implementación:**
    *   Se crearon todas las APIs (`api/acabados_*.php`, `api/paquetes_adicionales.php`, etc.) y la lógica de frontend en `Front/seleccion_acabados.php` para orquestar el flujo guiado de 5 pasos descrito en el `README.md`.

#### **Tarea 2: Integración de mejora para Visualización de Archivos e Imagenes**
*   **Fechas Estimadas:** 20 de agosto - 2 de septiembre
*   **Descripción Detallada:** Se implementó una solución para mostrar de forma segura las imágenes dentro de los chats en CTG y PQR.
*   **Implementación:**
    *   Se crearon los scripts en la carpeta `ctg/ y pqr/` que se conectan a la API, obtienen URLs de mapeadas en la base de datos y las sirven al frontend.

#### **Tarea 3: Mejoras de Flexibilidad en Carga de Archivos CTG/PQR**
*   **Fechas Estimadas:** 20 de agosto - 2 de septiembre
*   **Descripción Detallada:** Como mejora final, se flexibilizó el sistema de subida de archivos en los tickets de servicio al cliente.
*   **Implementación:**
    *   Se modificaron las APIs `api/ctg/ctg_insert_form.php` y `api/pqr/pqr_insert_form.php` para aceptar cualquier tipo de archivo (hasta 1GB) y para guardarlos de forma inteligente conservando el nombre original.


---

### **Semana: Integración Profunda con Microsoft (3 de septiembre – 10 de septiembre)**

#### **Tarea 1: Creación del Algoritmo de Disponibilidad y Asignación Inteligente**
*   **Fechas Estimadas:** 3 de septiembre - 10 de septiembre
*   **Descripción Detallada:** Se reescribió por completo el motor del sistema de citas. El nuevo algoritmo previene la doble reserva y optimiza el tiempo del personal.
*   **Implementación:**
    *   La API `api/cita/horas_disponibles.php` fue refactorizada para implementar una lógica de verificación de colisiones que considera la duración variable de las citas y la disponibilidad real de todos los responsables, incluyendo sus eventos de Outlook.
    *   La API `api/cita/cita_create.php` se modificó para implementar un balanceo de carga, asignando las nuevas citas al responsable cualificado con la menor cantidad de trabajo para ese día.

#### **Tarea 2: Desarrollo de la Sincronización Inicial y Completa con Outlook**
*   **Fechas Estimadas:** 3 de septiembre - 10 de septiembre
*   **Descripción Detallada:** Se construyó la lógica para realizar una sincronización completa y precisa del calendario de un responsable al conectar su cuenta de Microsoft.
*   **Implementación:**
    *   El script `oauth_callback.php` ahora orquesta un proceso de "borrón y cuenta nueva": limpia citas de Outlook antiguas de la BD local y luego realiza una importación masiva usando la `calendarview` de la API de Graph para obtener una copia fiel del calendario del responsable.

#### **Tarea 3: Implementación de Webhooks de Outlook para Sincronización en Tiempo Real**
*   **Fechas Estimadas:** 3 de septiembre - 10 de septiembre
*   **Descripción Detallada:** Para mantener los calendarios actualizados sin demora, se implementó un sistema de webhooks.
*   **Implementación:**
    *   Se desarrolló el endpoint `api/outlook_webhook.php` que escucha notificaciones de cambios (`created`, `updated`, `deleted`) desde Microsoft. La lógica en `api/helpers/outlook_sync_helper.php` procesa estas notificaciones y actualiza la base de datos de la app en tiempo real.


---