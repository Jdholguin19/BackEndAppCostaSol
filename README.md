# Proyecto BackEndAppCostaSol

Este proyecto es una aplicación web integral diseñada para la gestión de propiedades, citas, solicitudes de servicio al cliente (CTG/PQR), noticias y administración de usuarios para la entidad "CostaSol". La aplicación está estructurada con una clara separación entre el frontend (HTML, CSS, JavaScript) y el backend (PHP API), lo que facilita su mantenimiento y escalabilidad.

## Estructura de Carpetas

A continuación, se detalla la organización de los archivos y directorios principales del proyecto:

C:.
├── antiguo.php
├── index.php
├── manifest.json
├── Manual_de_uso.pdf
├── OneSignalSDKWorker.js
├── paleta_vegetal.pdf
├── portalao_appcostasol.sql
├── README.md
├── .well-known
│   └── assetlinks.json
├── api
│   ├── bottom_nav.php
│   ├── calendario_responsable.php
│   ├── etapas_manzana_villa.php
│   ├── garantias.php
│   ├── login.php
│   ├── logout.php
│   ├── mcm.php
│   ├── menu.php
│   ├── noticias.php
│   ├── notificaciones.php
│   ├── notificaciones_count.php
│   ├── notificaciones_mark_read.php
│   ├── obtener_propiedades.php
│   ├── paletavegetal.php
│   ├── perfil.php
│   ├── propiedad_fase.php
│   ├── propositos.php
│   ├── responsables_list.php
│   ├── update_player_id.php
│   ├── update_profile_picture.php
│   ├── user_crud.php
│   ├── user_list.php
│   ├── validate_responsable.php
│   ├── cita
│   │   ├── citas_list.php
│   │   ├── cita_cancelar.php
│   │   ├── cita_create.php
│   │   ├── cita_eliminar.php
│   │   ├── cita_update_estado.php
│   │   ├── dias_disponibles.php
│   │   └── horas_disponibles.php
│   ├── ctg
│   │   ├── ctg_create.php
│   │   ├── ctg_estados.php
│   │   ├── ctg_insert_form.php
│   │   ├── ctg_list.php
│   │   ├── ctg_observaciones.php
│   │   ├── ctg_respuestas.php
│   │   ├── ctg_update_estado.php
│   │   ├── ctg_update_observaciones.php
│   │   ├── subtipo_ctg.php
│   │   └── tipo_ctg.php
│   ├── helpers
│   │   └── notificaciones.php
│   └── pqr
│       ├── pqr_create.php
│       ├── pqr_estados.php
│       ├── pqr_insert_form.php
│       ├── pqr_list.php
│       ├── pqr_observaciones.php
│       ├── pqr_respuestas.php
│       ├── pqr_update_estado.php
│       ├── pqr_update_observaciones.php
│       ├── subtipo_pqr.php
│       └── tipo_pqr.php
├── appcostasol
│   ├── api
│   │   └── login.php
│   ├── config
│   │   └── db.php
│   └── Front
│       ├── users.php
│       └── user_crud.php
├── config
│   └── db.php
├── correos
│   ├── EnviarCorreoNotificacionResponsable.php
│   ├── EnviarCorreoNumeros.php
│   ├── EnviarCorreoPlantilla.php
│   ├── EnviarCorreoReciboPago.php
│   └── EnviarCorreos.php
├── Front
│   ├── citas.php
│   ├── cita_nueva.php
│   ├── cita_responsable.php
│   ├── fase_detalle.php
│   ├── garantias.php
│   ├── login_front.php
│   ├── menu2.php
│   ├── menu_front.php
│   ├── noticia.php
│   ├── notificaciones.php
│   ├── panel_calendario.php
│   ├── perfil.php
│   ├── register_front.php
│   ├── selec_acabado.php
│   ├── users.php
│   ├── assets
│   │   └── css
│   │       ├── style_citas.css
│   │       ├── style_cita_nueva.css
│   │       ├── style_ctg.css
│   │       ├── style_ctg_detalle.css
│   │       ├── style_ctg_nuevo.css
│   │       ├── style_fdetale.css
│   │       ├── style_garantia.css
│   │       ├── style_login.css
│   │       ├── style_main.css
│   │       ├── style_noticia.css
│   │       ├── style_notifications.css
│   │       ├── style_panel_calendario.css
│   │       ├── style_perfil.css
│   │       ├── style_pqr.css
│   │       ├── style_pqr_detalle.css
│   │       ├── style_pqr_nuevo.css
│   │       └── style_users.css
│   ├── ctg
│   │   ├── ctg.php
│   │   ├── ctg_detalle.php
│   │   └── ctg_nuevo.phpe
│   └── pqr
│       ├── pqr.php
│       ├── pqr_detalle.php
│       └── pqr_nuevo.php
├── imagenes
│   └── icons
│       ├── icon-192x192.png
│       ├── icon-256x256.png
│       ├── icon-384x384.png
│       └── icon-512x512.png
├── ImagenesCTG_problema
├── ImagenesCTG_respuestas
├── ImagenesNoticias
├── ImagenesPerfil
├── ImagenesPQR_problema
├── ImagenesPQR_respuestas
├── ImagenesPQR_solucion
├── SharePoint
│   ├── ExtraerURL.php
│   ├── MostrarImagenes.php
│   └── mostrar_imagen.php
└── uploads
```

## Archivos Clave para el Funcionamiento y Entendimiento

Aunque la estructura de carpetas detalla todos los archivos, los siguientes son considerados esenciales para comprender el funcionamiento central de la aplicación:

*   `config/db.php`: Configuración fundamental para la conexión a la base de datos.
*   `portalao_appcostasol.sql`: El esquema completo de la base de datos, crucial para la configuración inicial y el entendimiento de los datos.
*   `api/login.php`: Punto de entrada principal para la autenticación de usuarios en el backend.
*   `Front/login_front.php`: La interfaz de usuario para el inicio de sesión.
*   `Front/menu_front.php`: El panel principal del usuario después de iniciar sesión, donde se cargan las funcionalidades y se muestra el contador de notificaciones.
*   `api/ctg/`, `api/pqr/`, `api/cita/`: Directorios que contienen la lógica de negocio central para las funcionalidades de CTG, PQR y Citas, respectivamente.
*   `Front/ctg/`, `Front/pqr/`: Directorios que contienen las interfaces de usuario para las funcionalidades de CTG y PQR.
*   `api/user_crud.php`: Maneja las operaciones CRUD para los usuarios.
*   `Front/users.php`: Interfaz de usuario para la gestión de usuarios.

## Características y Funcionalidades Clave

El proyecto ofrece las siguientes funcionalidades principales:

*   **Autenticación y Autorización de Usuarios:**
    *   Inicio de sesión para usuarios (clientes/residentes) y responsables (personal).
    *   Autenticación basada en tokens.
    *   **Control de Acceso Basado en Roles (RBAC):** El sistema implementa un control de acceso basado en roles utilizando las tablas `rol` y `rol_menu`.
        *   **`rol`**: Define los tipos de usuarios (Cliente, Residente, SAC/Admin).
        *   **`rol_menu`**: Asigna ítems de menú específicos a cada rol, controlando qué funcionalidades son visibles en el frontend (`Front/menu_front.php`, `Front/menu2.php`).
        *   **Validación en Backend:** Los endpoints de la API (`api/menu.php`, `api/user_crud.php`, etc.) realizan validaciones en el lado del servidor basadas en el `rol_id` del usuario autenticado (obtenido del token) para asegurar que solo los usuarios con los permisos adecuados puedan ejecutar ciertas acciones o acceder a datos sensibles.
*   **Gestión de Citas:**
    *   Creación de nuevas citas por parte del cliente (`cita_nueva.php`, `api/cita/cita_create.php`).
    *   **Creación de citas por parte de un responsable para un cliente** (`cita_responsable.php`).
    *   Listado de citas (filtrado por usuario, responsable o todas para el admin) (`citas.php`, `api/cita/citas_list.php`).
    *   Cancelación de citas (`api/cita/cita_cancelar.php`).
    *   Eliminación de citas canceladas (`api/cita/cita_eliminar.php`).
    *   Gestión de la disponibilidad de los responsables (`api/cita/dias_disponibles.php`, `api/cita/horas_disponibles.php`).
*   **Gestión de CTG (Contingencias):**
    *   Creación, listado y visualización de detalles de solicitudes de contingencia.
    *   Funcionalidades para añadir respuestas, actualizar el estado y gestionar observaciones.
*   **Gestión de PQR (Peticiones, Quejas y Recomendaciones):**
    *   Funcionalidad similar a la gestión de CTG, para manejar peticiones, quejas y recomendaciones de los usuarios.
*   **Gestión de Propiedades:**
    *   Visualización de propiedades asignadas a los usuarios.
    *   Seguimiento del progreso de construcción de las propiedades (`fase_detalle.php`).
*   **Gestión de Noticias:**
    *   Visualización de noticias y comunicados.
    *   Funcionalidades para administradores para crear y eliminar noticias (`noticia.php`).
*   **Calendario de Responsables:**
    *   Cada responsable puede ver su propia agenda.
    *   El responsable administrador (ID 3) tiene la capacidad de ver los calendarios de todos los responsables (`panel_calendario.php`).
*   **Manuales y Documentos:**
    *   Acceso a documentos PDF como el Manual de Uso y Mantenimiento (`api/mcm.php`) y la Paleta Vegetal (`api/paletavegetal.php`).
*   **Gestión de Usuarios (Administrador):**
    *   Funcionalidades CRUD (Crear, Leer, Actualizar, Eliminar) para usuarios (`users.php`, `api/user_crud.php`).
*   **Sistema de Notificaciones:**
    *   Visualización de notificaciones para los usuarios (`notificaciones.php`).
    *   Contador de notificaciones no leídas en tiempo real en la barra de menú.
    *   El contador se actualiza automáticamente al visualizar un ticket (CTG o PQR).
    *   La lógica diferencia entre usuarios normales (ven respuestas de responsables) y responsables (ven respuestas de usuarios en sus tickets asignados).
    *   El contador muestra el número exacto hasta 9, y "+9" para cantidades superiores.
    *   **Sistema Avanzado de Notificaciones Push OneSignal:**
        *   Ventana emergente personalizada para suscripción con diseño de psicología inversa.
        *   Ocultación completa del icono rojo molesto de OneSignal.
        *   Gestión de desuscripción y resuscripción desde el perfil de usuario.
        *   Sincronización automática del estado entre páginas.
        *   Interfaz completamente responsiva para todos los dispositivos.
*   **Gestión de Perfil de Usuario:**
    *   Página de perfil completa (`perfil.php`) con información del usuario.
    *   Funcionalidad para cambiar foto de perfil con subida de archivos.
    *   Navegación directa desde el avatar en el menú principal.
    *   Soporte para usuarios normales y responsables.
    *   **Gestión Avanzada de Notificaciones desde el Perfil:**
        *   Nueva sección de notificaciones con opción de desuscripción.
        *   Ventana emergente de confirmación con diseño de psicología inversa.
        *   Funcionalidad de resuscripción para volver a activar notificaciones.
        *   Sincronización automática del estado con el menú principal.

## Resumen de Funcionalidades de la API.

Esta sección detalla los endpoints de la API existentes y sus funcionalidades principales, sirviendo como referencia.

### Autenticación y Gestión de Usuarios

*   **`api/login.php`**: Maneja el inicio de sesión de usuarios y responsables, devolviendo un token de autenticación.
*   **Mecanismo de Autenticación:** El sistema utiliza un mecanismo de autenticación basado en tokens. Tras un inicio de sesión exitoso en `api/login.php`, el servidor genera un token seguro y lo guarda en la base de datos para el usuario o responsable.
*   **Manejo de Tokens en el Cliente:** El token recibido se almacena en el `localStorage` del navegador (clave `cs_token`). Para todas las solicitudes subsiguientes a endpoints protegidos de la API, este token debe incluirse en el encabezado `Authorization` como un `Bearer Token` (ej: `Authorization: Bearer <tu_token>`).
*   **Validación de Sesión:** El backend valida la autenticidad de este token en cada solicitud protegida, buscándolo en la base de datos para asegurar que el usuario tiene permiso para acceder al recurso.
*   **`api/logout.php`**: Invalida la sesión del usuario.
*   **`api/user_crud.php`**: Proporciona operaciones CRUD (Crear, Leer, Actualizar, Eliminar) para la gestión de usuarios.
*   **`api/user_list.php`**: **(Nuevo)** Devuelve una lista de todos los usuarios (clientes/residentes) para ser usada en interfaces de administración. Requiere autenticación de responsable.
*   **`api/validate_responsable.php`**: Valida tokens específicos para responsables.
*   **`api/update_player_id.php`**: Actualiza el ID de OneSignal Player para notificaciones push.
*   **`api/update_profile_picture.php`**: Actualiza la foto de perfil del usuario autenticado.

### Gestión de Citas

*   **`api/cita/cita_create.php`**: Crea una nueva cita. **Modificado:** Ahora detecta si la petición viene de un responsable para permitirle crear una cita para otro usuario, o si es un usuario normal, en cuyo caso solo puede crear citas para sí mismo.
*   **`api/cita/citas_list.php`**: Lista citas, con opciones de filtrado por usuario o responsable.
*   **`api/cita/cita_cancelar.php`**: Cancela una cita existente.
*   **`api/cita/cita_eliminar.php`**: Elimina una cita (usualmente citas canceladas).
*   **`api/cita/dias_disponibles.php`**: Obtiene los días disponibles para agendar citas por responsable.
*   **`api/cita/horas_disponibles.php`**: Obtiene las horas disponibles para agendar citas en un día específico.

### Gestión de CTG (Contingencias)

*   **`api/ctg/ctg_create.php`**: Crea una nueva solicitud de contingencia.
*   **`api/ctg/ctg_list.php`**: Lista las solicitudes de contingencia.
*   **`api/ctg/ctg_estados.php`**: Proporciona los estados posibles para una CTG.
*   **`api/ctg/ctg_insert_form.php`**: Inserta datos de formulario para una CTG.
*   **`api/ctg/ctg_observaciones.php`**: Gestiona las observaciones de una CTG.
*   **`api/ctg/ctg_respuestas.php`**: Gestiona las respuestas a una CTG.
*   **`api/ctg/ctg_update_estado.php`**: Actualiza el estado de una CTG.
*   **`api/ctg/ctg_update_observaciones.php`**: Actualiza las observaciones de una CTG.
*   **`api/ctg/subtipo_ctg.php`**: Obtiene subtipos de CTG.
*   **`api/ctg/tipo_ctg.php`**: Obtiene tipos de CTG.

### Gestión de PQR (Peticiones, Quejas y Recomendaciones)

*   **`api/pqr/pqr_create.php`**: Crea una nueva solicitud PQR.
*   **`api/pqr/pqr_list.php`**: Lista las solicitudes PQR.
*   **`api/pqr/pqr_estados.php`**: Proporciona los estados posibles para una PQR.
*   **`api/pqr/pqr_insert_form.php`**: Inserta datos de formulario para una PQR.
*   **`api/pqr/pqr_observaciones.php`**: Gestiona las observaciones de una PQR.
*   **`api/pqr/pqr_respuestas.php`**: Gestiona las respuestas a una PQR.
*   **`api/pqr/pqr_update_estado.php`**: Actualiza el estado de una PQR.
*   **`api/pqr/pqr_update_observaciones.php`**: Actualiza las observaciones de una PQR.
*   **`api/pqr/subtipo_pqr.php`**: Obtiene subtipos de PQR.
*   **`api/pqr/tipo_pqr.php`**: Obtiene tipos de PQR.

### Notificaciones

*   **`api/notificaciones_count.php`**: Devuelve el número de notificaciones no leídas para el usuario autenticado.
*   **`api/notificaciones_mark_read.php`**: Marca notificaciones específicas como leídas.
*   **`api/notificaciones.php`**: Obtiene una lista de notificaciones.

### Propiedades y Construcción

*   **`api/obtener_propiedades.php`**: Obtiene propiedades. **Modificado:** Ahora acepta un parámetro opcional `id_usuario` en la URL. Si se provee, filtra las propiedades para ese usuario específico. Si no, mantiene el comportamiento original (un responsable ve todo, un usuario ve solo lo suyo).
*   **`api/propiedad_fase.php`**: Obtiene la fase de construcción de una propiedad.
*   **`api/etapas_manzana_villa.php`**: Obtiene etapas de construcción por manzana/villa.

### Otros

### Validación de Entradas y Manejo de Errores

*   **Validación de Entradas:** Las APIs implementan validación de entradas en el lado del servidor para asegurar la integridad y seguridad de los datos. Esto incluye la verificación de tipos de datos, formatos y la presencia de campos obligatorios.
*   **Respuestas de Error:** En caso de errores (validación fallida, autenticación/autorización denegada, errores internos del servidor), las APIs devuelven respuestas en formato JSON con una estructura consistente, incluyendo un campo `ok: false` y un `mensaje` descriptivo del error. Los códigos de estado HTTP (ej: 400 Bad Request, 401 Unauthorized, 403 Forbidden, 500 Internal Server Error) se utilizan para indicar la naturaleza del problema.

*   **`api/bottom_nav.php`**: Obtiene ítems para la navegación inferior.
*   **`api/calendario_responsable.php`**: Obtiene datos del calendario de responsables.
*   **`api/garantias.php`**: Obtiene información de garantías.
*   **`api/mcm.php`**: Sirve el manual de uso y mantenimiento (PDF).
*   **`api/menu.php`**: Obtiene ítems de menú según el rol del usuario.
*   **`api/noticias.php`**: Gestiona noticias.
*   **`api/paletavegetal.php`**: Sirve el PDF de paleta vegetal.
*   **`api/propositos.php`**: Obtiene propósitos de agendamiento.
*   **`api/responsables_list.php`**: Lista responsables.

## Resumen de Funcionalidades del Frontend (para Migración a Laravel)

Esta sección describe las funcionalidades implementadas en el frontend de la aplicación, que interactúan con la API y proporcionan la experiencia de usuario.

### Autenticación y Gestión de Sesión

*   **`login_front.php`**: Interfaz de usuario para el inicio de sesión de usuarios y responsables. Maneja la recolección de credenciales y el envío a `api/login.php`. Almacena el token de autenticación y los datos del usuario en `localStorage`.
*   **`register_front.php`**: Interfaz de usuario para el registro de nuevos usuarios. Recopila información básica (nombres, apellidos, correo, contraseña) y la envía a `api/user_crud.php` para la creación de un nuevo usuario con `rol_id: 1` (cliente).
*   **`menu_front.php` / `menu2.php`**: Páginas principales post-autenticación. Muestran el nombre del usuario, avatar, y un menú dinámico basado en el rol. El avatar es clickeable y redirige al perfil del usuario. Incluyen la lógica para cerrar sesión (`api/logout.php`) y la integración con OneSignal para notificaciones push (`api/update_player_id.php`). **Nuevas funcionalidades incluyen:**
    *   Sistema avanzado de gestión de notificaciones OneSignal con ventana emergente personalizada.
    *   Ocultación completa del icono rojo molesto de OneSignal.
    *   Psicología inversa en la interfaz de suscripción para mejorar la conversión.
    *   Detección inteligente del estado de suscripción con múltiples fallbacks.
    *   Sincronización automática del estado con la página de perfil.

### Gestión de Propiedades y Avance de Obra

*   **`menu_front.php`**: Permite al usuario seleccionar entre sus propiedades asignadas (si tiene varias) a través de pestañas dinámicas.
*   **`fase_detalle.php`**: Muestra el detalle de las etapas de construcción de una propiedad específica (manzana y villa), incluyendo el porcentaje de avance y fotos asociadas. Interactúa con `api/etapas_manzana_villa.php` y `api/propiedad_fase.php`.

### Gestión de Citas

*   **`citas.php`**: Muestra una lista de citas agendadas por el usuario o asignadas al responsable. Permite cancelar citas (`api/cita/cita_cancelar.php`) y, para administradores/responsables, actualizar el estado de las citas (`api/cita/cita_update_estado.php`). **Modificado:** El botón "Agendar" ahora es dinámico, llevando a `cita_nueva.php` para clientes y a `cita_responsable.php` para responsables.
*   **`cita_nueva.php`**: Formulario para que un cliente agende una nueva cita. Permite seleccionar la propiedad, el propósito de la visita, la fecha (con un calendario interactivo `flatpickr.js`) y la hora (con un selector de rueda vertical). Interactúa con `api/obtener_propiedades.php`, `api/propositos.php`, `api/cita/dias_disponibles.php`, `api/cita/horas_disponibles.php` y `api/cita/cita_create.php`.
*   **`cita_responsable.php`**: **(Nuevo)** Formulario para que un responsable agende una nueva cita para un cliente. El responsable primero selecciona al cliente de una lista, lo que carga dinámicamente las propiedades de ese cliente. Luego, el flujo es similar al de `cita_nueva.php`.

### Gestión de CTG (Contingencias)

*   **`ctg/ctg.php`**: Lista las solicitudes de contingencia del usuario, con opciones de filtrado por estado y ordenación. Permite navegar al detalle de cada CTG y crear nuevas solicitudes. Interactúa con `api/ctg/ctg_list.php` y `api/ctg/ctg_estados.php`.
*   **`ctg/ctg_nuevo.php`**: Formulario para crear una nueva solicitud de contingencia. Permite seleccionar la propiedad, el tipo, el subtipo y añadir una descripción y un archivo adjunto. Interactúa con `api/obtener_propiedades.php`, `api/ctg/tipo_ctg.php`, `api/ctg/subtipo_ctg.php` y `api/ctg/ctg_create.php`.
*   **`ctg/ctg_detalle.php`**: Muestra el hilo de conversación de una CTG específica. Permite al usuario enviar nuevas respuestas (con texto y adjuntos) y, si es responsable, actualizar el estado de la CTG y añadir/editar observaciones del cliente. Interactúa con `api/ctg/ctg_respuestas.php`, `api/ctg/ctg_insert_form.php`, `api/ctg/ctg_update_estado.php`, `api/ctg/ctg_observaciones.php` y `api/ctg/ctg_update_observaciones.php`. Incluye lógica para marcar notificaciones como leídas (`api/notificaciones_mark_read.php`).

### Gestión de PQR (Peticiones, Quejas y Recomendaciones)

*   **`pqr/pqr.php`**: Similar a CTG, lista las solicitudes PQR del usuario, con opciones de filtrado y ordenación. Permite navegar al detalle y crear nuevas solicitudes. Interactúa con `api/pqr/pqr_list.php` y `api/pqr/pqr_estados.php`.
*   **`pqr/pqr_nuevo.php`**: Formulario para crear una nueva solicitud PQR. Permite seleccionar la propiedad, el tipo y añadir una descripción y un archivo adjunto. Interactúa con `api/obtener_propiedades.php`, `api/pqr/tipo_pqr.php` y `api/pqr/pqr_create.php`.
*   **`pqr/pqr_detalle.php`**: Muestra el hilo de conversación de una PQR específica. Permite al usuario enviar nuevas respuestas (con texto y adjuntos) y, si es responsable, actualizar el estado de la PQR y añadir/editar observaciones del cliente. Interactúa con `api/pqr/pqr_respuestas.php`, `api/pqr/pqr_insert_form.php`, `api/pqr/pqr_update_estado.php`, `api/pqr/pqr_observaciones.php` y `api/pqr/pqr_update_observaciones.php`. Incluye lógica para marcar notificaciones como leídas (`api/notificaciones_mark_read.php`).

### Notificaciones

*   **`notificaciones.php`**: Muestra una lista de notificaciones para el usuario. Las notificaciones pueden ser respuestas a CTG o PQR. Interactúa con `api/notificaciones.php`.
*   **`menu_front.php`**: Muestra un contador de notificaciones no leídas en tiempo real, que se actualiza periódicamente. Interactúa con `api/notificaciones_count.php`.

### Otras Funcionalidades del Frontend

*   **`garantias.php`**: Muestra información sobre las garantías del usuario, incluyendo su duración y vigencia. Incluye un procedimiento de reclamación. Interactúa con `api/garantias.php`.
*   **`panel_calendario.php`**: Muestra un calendario de citas para los responsables. Los administradores pueden ver el calendario de todos los responsables. Utiliza FullCalendar.js. Interactúa con `api/responsables_list.php` y `api/calendario_responsable.php`. **Modificado:** Ahora incluye un botón para que los responsables agenden citas para los clientes.
*   **`noticia.php`**: (Panel de administración) Permite a los administradores crear, listar y eliminar noticias. Interactúa con `api/noticias.php`.
*   **`perfil.php`**: Página completa del perfil de usuario que permite ver información personal y cambiar la foto de perfil. Incluye funcionalidad de subida de archivos y navegación integrada. **Nuevas funcionalidades incluyen:**
    *   Gestión avanzada de notificaciones con opción de desuscripción.
    *   Ventana emergente de confirmación con diseño de psicología inversa.
    *   Funcionalidad de resuscripción para reactivar notificaciones.
    *   Sincronización automática del estado con el menú principal.
*   **`users.php`**: (Panel de administración) Permite a los responsables con permisos gestionar usuarios (CRUD). Interactúa con `api/user_crud.php`.
*   **`seleccion_acabados.php`**: (Vacío en la lectura actual, pero su nombre sugiere una funcionalidad de selección de acabados).
*   **`config/db.php`**: Aunque es un archivo de configuración de backend, es fundamental para entender cómo el frontend se conecta indirectamente a la base de datos a través de las APIs PHP.

### Estilos y Componentes Reutilizables

*   **`assets/css/*.css`**: Archivos CSS que definen la apariencia y el diseño de las diferentes secciones de la aplicación, siguiendo un estilo moderno y responsivo.
*   **`bottom_nav.php` (incluido desde `api/`)**: Componente de navegación inferior reutilizable en varias páginas del frontend.
*   **`style_main.css`**: Contiene estilos globales y componentes comunes utilizados en toda la aplicación.

### Mejoras en la Visualización de Propiedades y Corrección de Errores

Se han implementado mejoras para asegurar la correcta visualización de propiedades y se ha corregido un error crítico de inicialización:

*   **Visibilidad de Propiedades para Responsables:**
    *   Originalmente, los usuarios con rol de `responsable` no podían visualizar todas las propiedades debido a un filtrado excesivo en el frontend (`menu_front.php`).
    *   La API `obtener_propiedades.php` ya estaba diseñada para permitir a los responsables ver todas las propiedades (al no aplicar un filtro de `id_usuario` si el usuario autenticado era un `responsable`).
    *   **Solución:** Se modificó `Front/menu_front.php` para que la solicitud a `api/obtener_propiedades.php` no incluya el parámetro `id_usuario` cuando el usuario autenticado es un `responsable`. Esto permite que la lógica del backend funcione como se esperaba, mostrando todas las propiedades a los responsables.

*   **Corrección de Error de Autenticación y Carga de Propiedades (`401 Unauthorized`):**
    *   Se identificó un error donde las solicitudes a `api/obtener_propiedades.php` (tanto para usuarios regulares como para responsables) fallaban con un estado `401 Unauthorized`.
    *   **Causa:** La llamada `fetch` en `Front/menu_front.php` para obtener propiedades no estaba incluyendo el encabezado `Authorization` con el token de sesión, a pesar de que la API lo requería.
    *   **Solución:** Se añadió el encabezado `Authorization: Bearer <token>` a la solicitud `fetch` para `api/obtener_propiedades.php` en `Front/menu_front.php`.

*   **Corrección de `Uncaught ReferenceError: Cannot access 'token' before initialization`:**
    *   Tras la corrección anterior, surgió un `ReferenceError` en `Front/menu_front.php` porque la variable `token` se estaba utilizando en la llamada `fetch(API_PROP)` antes de que fuera declarada en ese ámbito.
    *   **Solución:** Se reubicó la declaración `const token = localStorage.getItem('cs_token');` a una posición anterior en el script de `Front/menu_front.php`, asegurando que la variable `token` esté disponible y correctamente inicializada antes de cualquier uso.

#### Nuevos Endpoints de API para Notificaciones

Se han añadido los siguientes endpoints para gestionar el contador de notificaciones:

*   `api/notificaciones_count.php`:
    *   **Método:** `GET`
    *   **Autenticación:** Requiere token de portador (`Bearer Token`).
    *   **Función:** Devuelve el número de respuestas no leídas (`leido = 0`) para el usuario autenticado. La lógica se adapta según si el usuario es un cliente/residente o un responsable.

*   `api/notificaciones_mark_read.php`:
    *   **Método:** `POST`
    *   **Autenticación:** Requiere token de portador (`Bearer Token`).
    *   **Cuerpo (Body):** `{ "type": "ctg" | "pqr", "id": <id_del_ticket> }`
    *   **Función:** Marca como leídas las respuestas de un ticket específico cuando un usuario lo visualiza.

#### Cambios en la Base de Datos

*   Se ha añadido una columna `leido` (TINYINT, default 0) a las tablas `respuesta_ctg` y `respuesta_pqr` para rastrear el estado de lectura de cada mensaje.

#### Archivos Frontend Modificados

*   `Front/menu_front.php`: Para mostrar el contador de notificaciones.
*   `Front/ctg/ctg_detalle.php`: Para llamar a la API y marcar las notificaciones de CTG como leídas.
*   `Front/pqr/pqr_detalle.php`: Para llamar a la API y marcar las notificaciones de PQR como leídas.
*   **Integración con SharePoint:**
    *   Scripts para interactuar con SharePoint, probablemente para la gestión de documentos e imágenes relacionadas con el progreso de construcción.

## Gestión de Archivos y Almacenamiento

La aplicación maneja la carga y visualización de archivos (imágenes, PDFs) en varias funcionalidades (CTG, PQR, Noticias, Progreso de Construcción).

*   **Almacenamiento:** Los archivos adjuntos (imágenes de problemas/soluciones en CTG/PQR, imágenes de noticias, fotos de progreso de construcción, fotos de perfil) se almacenan actualmente en el **sistema de archivos local del servidor web**. Las rutas a estos archivos se guardan en la base de datos (campos `url_adjunto`, `url_problema`, `url_solucion`, `url_imagen`, `url_foto_perfil` en tablas como `respuesta_ctg`, `respuesta_pqr`, `noticia`, `progreso_construccion`, `usuario`, `responsable`).
*   **Integración con SharePoint:** La carpeta `SharePoint/` y los campos `ruta_descarga_sharepoint`, `ruta_visualizacion_sharepoint`, `drive_item_id` en la tabla `progreso_construccion` sugieren una integración existente o planificada con Microsoft SharePoint para la gestión de documentos y activos relacionados con el avance de obra. Esto implica que algunos archivos podrían residir en SharePoint y ser accedidos a través de URLs generadas por esta integración.
*   **Consideraciones para la Migración:** Para la migración a Laravel, será crucial definir una estrategia de almacenamiento de archivos (Laravel Filesystem con S3, almacenamiento local, etc.) y adaptar la lógica de carga, acceso y visualización de archivos en consecuencia. La integración con SharePoint requerirá una revisión específica para asegurar su compatibilidad o reimplementación.

## Esquema de la Base de Datos

El esquema de la base de datos se define en `portalao_appcostasol.sql` y está diseñado para soportar las funcionalidades de la aplicación. Las tablas clave incluyen:

*   `usuario`: Almacena la información de los usuarios (clientes/residentes), incluyendo su `rol_id`.
*   `responsable`: Almacena la información del personal o responsables.
*   `rol`: Define los roles de usuario (e.g., Cliente, Residente, SAC/Admin).
*   `menu`: Contiene los ítems de menú disponibles en la aplicación.
*   `rol_menu`: Tabla de unión que asigna ítems de menú a roles específicos.
*   `agendamiento_visitas`: Registra los detalles de las citas, vinculando usuarios y responsables.
*   `responsable_disponibilidad`: Define los horarios y días de trabajo de cada responsable.
*   `propiedad`: Almacena los detalles de las propiedades, vinculadas a un `id_usuario`.
*   `progreso_construccion`: Registra el avance de la construcción de cada propiedad.
*   `ctg`, `pqr`: Tablas principales para las solicitudes de servicio al cliente.
*   `respuesta_ctg`, `respuesta_pqr`: Almacenan las respuestas a las solicitudes de CTG y PQR.
*   `noticia`: Contiene los artículos de noticias y comunicados.

## Tecnologías Utilizadas

*   **Backend:**
    *   PHP
    *   MySQL (a través de PDO para la conexión a la base de datos)
*   **Frontend:**
    *   HTML5
    *   CSS3
    *   JavaScript
    *   **Bootstrap:** Framework de UI y componentes (v5.3.3, vía CDN).
    *   **FullCalendar:** Para la visualización de calendarios (v6.1.11, vía CDN, utilizado en `panel_calendario.php`).
    *   **OneSignal:** Para la implementación de notificaciones push (v16 SDK, vía CDN). La configuración (`appId`, `safari_web_id`) se realiza directamente en los scripts de `menu_front.php` y `login_front.php`. El `player_id` se actualiza en el backend a través de `api/update_player_id.php`.
    *   **Featherlight:** Para lightboxes de imágenes (v1.7.14, vía CDN, utilizado en `fase_detalle.php` para ver fotos de progreso).
    *   **jQuery:** (v3.x, vía CDN, utilizado por Featherlight y posiblemente otros scripts heredados).
    *   **Flatpickr:** Selector de fechas interactivo (v4.6.13, vía CDN, utilizado en `Front/cita_nueva.php`).
    *   **Bootstrap Icons:** (v1.11.3, vía CDN).

## Configuración e Instalación

Para configurar y ejecutar el proyecto localmente, siga estos pasos generales:

1.  **Servidor Web:** Asegúrese de tener un entorno de servidor web (como Apache o Nginx) con soporte para PHP (versión 8.2.12 o superior, según `portalao_appcostasol.sql`). Se recomienda usar XAMPP o un stack LAMP/WAMP.
2.  **Base de Datos:**
    *   Cree una base de datos MySQL con el nombre `portalao_appcostasol`.
    *   Importe el archivo `portalao_appcostasol.sql` en su base de datos MySQL. Este archivo contiene el esquema de la base de datos y datos de ejemplo.
3.  **Configuración de la Base de Datos:**
    *   Abra el archivo `config/db.php`.
    *   Actualice las constantes `DB_HOST`, `DB_NAME`, `DB_USER`, y `DB_PASS` con las credenciales de su base de datos MySQL.
    ```php
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'portalao_appcostasol';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    ```
4.  **Archivos del Proyecto:**
    *   Coloque todos los archivos del proyecto en el directorio raíz de documentos de su servidor web (por ejemplo, `htdocs` para Apache en XAMPP).
5.  **OneSignal (Opcional):**
    *   Si desea utilizar las notificaciones push, regístrese en OneSignal y configure una aplicación. Reemplace el `appId` y `safari_web_id` en los scripts de OneSignal (`menu_front.php`, `login_front.php`) con los de su propia aplicación OneSignal.

## Uso

1.  Acceda a la aplicación a través de su navegador web, navegando a la URL donde ha desplegado el proyecto (por ejemplo, `http://localhost/Front/login_front.php`).
2.  Utilice las credenciales de ejemplo proporcionadas en `portalao_appcostasol.sql` para iniciar sesión.
3.  Una vez autenticado, podrá navegar por el menú principal (`menu_front.php`) para acceder a las diversas funcionalidades de la aplicación según su rol.

## Observaciones Adicionales

*   La carpeta `SharePoint/` contiene scripts que sugieren una integración con SharePoint para la gestión de archivos, aunque su implementación completa no fue detallada en esta revisión.

---

### Mejoras y Correcciones (Agosto 2025 - Sistema de Citas para Responsables)

*   **Agendamiento de Citas por Parte de Responsables:**
    *   Se implementó una nueva funcionalidad que permite a los usuarios con rol de "responsable" agendar citas en nombre de los clientes.
    *   **Nueva Interfaz (`Front/cita_responsable.php`):** Se creó una página dedicada donde el responsable primero selecciona a un cliente de una lista desplegable. Una vez seleccionado, se cargan las propiedades de ese cliente para continuar con el proceso de agendamiento de forma similar al flujo del cliente.
    *   **Nuevo Endpoint (`api/user_list.php`):** Se añadió un endpoint para poblar la lista de clientes, accesible únicamente por responsables.
    *   **API Modificada (`api/obtener_propiedades.php`):** Se actualizó para que, si recibe un `id_usuario` como parámetro, devuelva las propiedades de ese usuario específico. Esto permite cargar dinámicamente las propiedades del cliente seleccionado en la nueva interfaz.
    *   **API Modificada (`api/cita/cita_create.php`):** Se reforzó la seguridad para distinguir entre una petición de un responsable (que puede especificar para qué cliente es la cita) y la de un cliente (que solo puede agendar para sí mismo).
    *   **Mejoras de Usabilidad:**
        *   Se añadió un botón "Agendar para Cliente" en la vista del calendario (`Front/panel_calendario.php`) para dar un acceso directo a la nueva funcionalidad.
        *   El botón "Agendar" en la página de listado de citas (`Front/citas.php`) ahora es dinámico: lleva a la interfaz de agendamiento normal para clientes y a la nueva interfaz para responsables.

### Actualizaciones Recientes

*   **Modernización del Selector de Fecha y Hora en Agendamiento:**
    *   Se implementó un calendario interactivo (`flatpickr.js`) en `Front/cita_nueva.php` para la selección de fechas, reemplazando la lista anterior.
    *   La API `api/cita/dias_disponibles.php` fue reescrita para soportar la consulta de disponibilidad por mes y año, optimizando la carga del calendario.
    *   El selector de horas en `Front/cita_nueva.php` fue rediseñado con CSS para ofrecer una experiencia de "rueda" vertical con scroll (`scroll-snap`), similar a la de interfaces nativas.
    *   Se añadió lógica JavaScript (`IntersectionObserver`) para detectar la hora seleccionada automáticamente al hacer scroll.
    *   Esta implementación prepara la aplicación para futuras integraciones con servicios de calendario externos como Microsoft Graph API.

*   **Corrección en Carga de Propiedades para Citas Nuevas:**
    *   Se solucionó un error en `Front/cita_nueva.php` que impedía la carga de propiedades en el selector.
    *   **Causa:** La solicitud `fetch` a `api/obtener_propiedades.php` no incluía el token de autenticación en la cabecera `Authorization`.
    *   **Solución:** Se modificó la llamada `fetch` para incluir el `Bearer Token`, asegurando la correcta autenticación y carga de los datos.

*   **Visualización Dinámica de Módulos por Rol y Estado:**
    *   **Ocultar Módulo "Selección Acabados":** Se implementó una lógica en `Front/menu_front.php` y `Front/menu2.php` para que el módulo "Selección Acabados" se oculte completamente para los usuarios con el rol de "Residente" (`rol_id = 2`).
    *   **Desactivación de Módulos "Garantías" y "CTG":** Se extendió la funcionalidad para que el módulo "CTG" también se desactive (junto con "Garantías") si la garantía del usuario ha expirado. Esta restricción no se aplica a los usuarios "responsables".
    *   **Corrección de Visualización de Menú Principal:** Se ajustó la lógica en `Front/menu_front.php` para asegurar que, aunque se oculte un módulo para un rol específico, la vista principal siempre muestre 4 módulos, cargando el siguiente disponible en la lista.
    *   **Módulo de Notificaciones:**                                                                                                                                                      │
    *   **Previsualización de Imágenes:** Se mejoró la página de notificaciones (`notificaciones.php`) para que, si una notificación está asociada a un adjunto de imagen,             │
     se muestre una vista previa en miniatura (50x50px) directamente en la tarjeta de notificación, haciéndolas más informativas y visuales. 

    *   **Sistema Avanzado de Gestión de Notificaciones OneSignal (Diciembre 2024):**
    *   **Ventana Emergente Personalizada de Suscripción:** Se implementó en `Front/menu_front.php` una ventana emergente elegante y responsiva que reemplaza el icono rojo molesto de OneSignal con una interfaz personalizada que incluye el mensaje "Estate al tanto de todas las novedades" y botones "No, gracias" y "Subscribirse".
    *   **Psicología Inversa en la Interfaz:** El botón "No, gracias" se implementó en color rojo para crear psicología inversa y hacer que el usuario se sienta más inclinado a hacer clic en "Subscribirse".
    *   **Detección Inteligente del Estado de Suscripción:** Se implementó un sistema robusto que verifica correctamente si el usuario está suscrito a OneSignal usando la API v16, con múltiples fallbacks y verificación periódica del estado.
    *   **Ocultación Completa del Icono de OneSignal:** Se implementaron múltiples estrategias para ocultar completamente el icono rojo de notificación de OneSignal: CSS con `!important`, JavaScript dinámico, MutationObserver para elementos que aparezcan después, y verificación periódica como respaldo.
    *   **Gestión de Desuscripción desde el Perfil:** Se agregó en `Front/perfil.php` una nueva sección de notificaciones con opción para desuscribirse, incluyendo una ventana emergente de confirmación con el mismo diseño de psicología inversa.
    *   **Funcionalidad de Resuscripción:** Se implementó la capacidad de volver a suscribirse a las notificaciones desde el perfil, con sincronización automática del estado entre páginas.
    *   **Sincronización de Estado entre Páginas:** El estado de suscripción se mantiene consistente entre `menu_front.php` y `perfil.php`, con persistencia en localStorage y sincronización con el backend.
    *   **Estilos Completamente Responsivos:** La ventana emergente de suscripción se adapta perfectamente a todos los tamaños de pantalla: ultra grandes (4K+), desktop, tablets, móviles grandes, móviles pequeños, orientación landscape, modo oscuro del sistema, dispositivos táctiles y pantallas de alta densidad.
    *   **Manejo Robusto de Errores:** Se implementó manejo de errores para diferentes versiones de la API de OneSignal, con fallbacks a la API nativa del navegador cuando sea necesario.
    *   **Optimización de Performance:** Se implementó verificación inteligente que solo oculta elementos cuando es necesario, evitando procesamiento innecesario en usuarios suscritos.

Se han implementado las siguientes mejoras y correcciones en el proyecto:

*   **Corrección de Error en Notificaciones CTG:**
    *   Se resolvió un `TypeError: Cannot read properties of null (reading 'appendChild')` en `Front/ctg/ctg_detalle.php` añadiendo el elemento `<div id="notificationArea">` necesario para la visualización de alertas.
*   **Restricción de Asignación de Responsables en CTG/PQR:**
    *   Se modificaron los endpoints `api/ctg/ctg_create.php` y `api/pqr/pqr_create.php` para asegurar que los nuevos CTG y PQR solo sean asignados aleatoriamente a responsables con `id` 1 o 2, excluyendo al `id` 3.
*   **Gestión Dinámica del Módulo de Garantías:**
    *   Se implementó una lógica en `Front/menu_front.php` y `Front/menu2.php` para verificar la vigencia de las garantías de un usuario.
    *   Si todas las garantías de un usuario han expirado, el módulo de "Garantías" en el menú principal se desactiva visualmente y su funcionalidad de clic se reemplaza por una alerta informativa.
    *   Se añadió la función `checkGarantiasStatus()` para realizar esta verificación.
    *   Se incluyeron estilos CSS para la clase `.disabled-card` en `Front/assets/css/style_main.css` para la representación visual de los módulos inactivos.
*   **Actualización de la Estructura de Carpetas:**
    *   Se actualizó el diagrama de la estructura de carpetas en este `README.md` para incluir la carpeta `appcostasol/` y el directorio `Front/includes/`.
    *   También se añadieron los archivos `api/bottom_nav.php` y `Front/menu2.php` que faltaban en el diagrama.

*   **Corrección del Error de OneSignal en Producción:**
    *   Se resolvió el error crítico **"No autorizado para actualizar el Player ID de otro usuario"** que impedía el funcionamiento correcto de las notificaciones push en producción.
    *   **Causa:** Discrepancia entre el `user_id` enviado desde el frontend (extraído del localStorage) y el ID del usuario autenticado obtenido del token.
    *   **Solución Implementada:**
        *   **Backend (`api/update_player_id.php`)**: Se eliminó la validación problemática que comparaba `user_id` con `authenticated_user_id`, ahora usa directamente el ID del usuario autenticado del token.
        *   **Frontend (`Front/menu_front.php` y `Front/menu2.php`)**: Se eliminó el envío de `user_id` en las peticiones a la API, simplificando la estructura de datos enviada.
        *   **Seguridad**: Se mantiene la autenticación basada en tokens, asegurando que cada usuario solo pueda actualizar su propio Player ID.
    *   **Beneficios:**
        *   ✅ Eliminación completa del error de autorización
        *   ✅ Funcionamiento correcto de las notificaciones push OneSignal
        *   ✅ Mayor robustez ante problemas de sincronización del localStorage
        *   ✅ API simplificada y más segura
        *   ✅ Compatibilidad con usuarios normales y responsables
    *   **Archivos Modificados:**
        *   `api/update_player_id.php` - Lógica de autorización corregida
        *   `Front/menu_front.php` - Eliminado envío de user_id
        *   `Front/menu2.php` - Eliminado envío de user_id

---

### Mejoras y Correcciones (Agosto 2025)

*   **Sistema de Notificación por Correo para Nuevos CTG/PQR:**
    *   Se implementó un sistema para enviar notificaciones por correo electrónico al responsable asignado cuando un cliente crea un nuevo CTG o PQR.
    *   **Remitente:** Los correos se envían desde `sistemas@thaliavictoria.com.ec`.
    *   **Contenido:** El correo incluye el nombre del cliente, el tipo de solicitud (CTG o PQR), el tipo específico del ticket y el nombre de la propiedad relacionada.
    *   **Asignación de Responsables:** La asignación de responsables para nuevos CTG/PQR se realiza de forma **aleatoria** entre los responsables con ID 1 y 2. El responsable con ID 3 sigue **excluido** de esta asignación.
    *   **Archivos Involucrados:**
        *   `correos/EnviarCorreoNotificacionResponsable.php` (Nuevo archivo: Contiene la lógica para obtener tokens de acceso y enviar correos a través de Microsoft Graph API).
        *   `api/ctg/ctg_create.php` (Modificado: Se añadió la lógica para obtener los datos necesarios y llamar a la función de envío de correo tras la creación de un CTG).
        *   `api/pqr/pqr_create.php` (Modificado: Se añadió la lógica para obtener los datos necesarios y llamar a la función de envío de correo tras la creación de un PQR).

*   **Corrección en la Obtención del Nombre de Propiedad para Notificaciones:**
    *   Se corrigió un error donde la consulta para obtener el nombre de la propiedad en las notificaciones generaba un error de columna no encontrada.
    *   **Solución:** Ahora se concatenan los campos `manzana` y `villa` de la tabla `propiedad` para formar un identificador descriptivo de la propiedad (ej. "Manzana X, Villa Y").
    *   **Archivos Modificados:**
        *   `api/ctg/ctg_create.php`
        *   `api/pqr/pqr_create.php`

*   **Sistema de Notificación por Correo para Nuevas Citas:**
    *   Se implementó un sistema para enviar notificaciones por correo electrónico al responsable asignado cuando un cliente agenda una nueva cita.
    *   **Remitente:** Los correos se envían desde `sistemas@thaliavictoria.com.ec`.
    *   **Contenido:** El correo incluye el nombre del cliente, el propósito de la cita, la fecha, la hora y el nombre de la propiedad relacionada.
    *   **Asignación de Responsables:** La asignación de responsables para citas se basa en un balanceo de carga (responsable con menos citas asignadas) y disponibilidad, con un desempate aleatorio. El responsable con ID 3 sigue **excluido** de esta asignación.
    *   **Archivos Involucrados:**
        *   `correos/EnviarCorreoNotificacionResponsable.php` (Modificado: Ahora acepta parámetros de fecha y hora para citas).
        *   `api/cita/cita_create.php` (Modificado: Se añadió la lógica para obtener los datos necesarios y llamar a la función de envío de correo tras la creación de una cita).

*   **Mejoras de Diseño en "Nueva Cita" (`cita_nueva.php`):**
    *   Se realizaron varios ajustes de CSS para mejorar la experiencia de usuario en la pantalla de agendamiento de citas.
    *   **Cuadrícula de Propósitos:** Se estandarizó el tamaño de los botones de selección de propósito para que todos tengan las mismas dimensiones, independientemente del texto que contengan, y se hicieron responsivos.
    *   **Centrado del Calendario:** Se corrigió un problema de alineación con el componente de calendario (Flatpickr), asegurando su centrado en todas las resoluciones mediante la aplicación de `max-width` y márgenes automáticos a su `div` contenedor.

*   **Corrección de Lógica de Disponibilidad de Horas (`api/cita/horas_disponibles.php`):**
    *   Se detectó y corrigió un error crítico que impedía mostrar los horarios disponibles en producción debido a una lógica con valores fijos.
    *   Se reescribió el script para que calcule los horarios de forma dinámica basándose en las reglas de disponibilidad de la base de datos, asegurando consistencia con la lógica de `dias_disponibles.php`.

*   **Generalización de Permisos para "Responsables" (`menu2.php` y `api/menu.php`):**
    *   Se refactorizó el código para que todos los usuarios con el rol de "responsable" tengan acceso a las vistas de administrador, en lugar de solo un usuario con un ID específico (`id=3`).
    *   Se eliminó la lógica "hardcodeada" y ahora el sistema se basa en el flag `is_responsable` para una mayor flexibilidad.
    *   Se reparó una corrupción en el archivo `api/menu.php` causada por un error de escritura anterior.

*   **Implementación de Sistema de Perfil de Usuario:**
    *   Se creó una página completa de perfil (`Front/perfil.php`) con diseño tipo WhatsApp.
    *   Se implementó funcionalidad para cambiar foto de perfil con subida de archivos.
    *   Se creó nueva API (`api/update_profile_picture.php`) para gestionar fotos de perfil.
    *   Se modificó `Front/menu_front.php` para que el avatar redirija al perfil.
    *   Se agregaron estilos CSS (`Front/assets/css/style_perfil.css`) para la nueva funcionalidad.
    *   El sistema maneja tanto usuarios normales como responsables.

---

### Mejoras y Correcciones (Diciembre 2024 - Enero 2025)

*   **Implementación de Switch de Notificaciones en Perfil de Usuario:**
    *   Se reemplazó el sistema de botones separados por un switch moderno y intuitivo en `Front/perfil.php` para la gestión de notificaciones OneSignal.
    *   **Interfaz Mejorada:** Se implementó un switch tipo toggle que reemplaza los botones "Desuscribirse" y "Volver a suscribirse" por una interfaz más moderna y fácil de usar.
    *   **Lógica del Switch:** 
        *   **Activado (ON)**: Intenta resuscribir al usuario a OneSignal automáticamente
        *   **Desactivado (OFF)**: Muestra modal de confirmación para desuscripción
        *   **Estado Sincronizado**: Se mantiene sincronizado con localStorage y el estado del servidor
    *   **Modal de Confirmación:** Se implementó una ventana emergente elegante que confirma la desuscripción con botones "No, gracias" y "Desuscribirse", manteniendo el diseño de psicología inversa.
    *   **Manejo de Estados:** El switch se inicializa automáticamente según el estado actual del usuario y maneja errores revirtiendo el estado en caso de fallo.
    *   **API Mejorada:** Se corrigió `api/update_player_id.php` para manejar tanto valores `null` como cadenas vacías `""` para indicar desuscripción, resolviendo el error "onesignal_player_id es requerido".
    *   **Compatibilidad:** La solución funciona tanto con la API actual (cadena vacía) como con futuras versiones (null), asegurando compatibilidad inmediata y futura.
    *   **Funcionalidades Implementadas:**
        *   ✅ Switch moderno tipo toggle para activar/desactivar notificaciones
        *   ✅ Modal de confirmación con diseño de psicología inversa
        *   ✅ Sincronización automática del estado con localStorage y backend
        *   ✅ Manejo robusto de errores con reversión automática del estado
        *   ✅ Integración completa con OneSignal API v16
        *   ✅ Soporte para usuarios normales y responsables
    *   **Archivos Modificados:**
        *   `Front/perfil.php` - Implementación completa del switch de notificaciones
        *   `api/update_player_id.php` - Soporte para desuscripción con valores null y cadenas vacías

---

Este `README.md` proporciona una visión general completa del proyecto. Para detalles específicos de implementación o depuración, se recomienda revisar el código fuente de los archivos relevantes.
*   **Mejora en Carga de Archivos para CTG y PQR:**
    *   Se ha actualizado el sistema de subida de archivos en los módulos de CTG y PQR para mejorar la flexibilidad y robustez.
    *   **Tipos de Archivo:** Se eliminó la restricción que limitaba las subidas a imágenes y PDF. Ahora el sistema **acepta cualquier tipo de archivo**.
    *   **Tamaño de Archivo:** El límite de tamaño de archivo se ha incrementado a **1 GB**. 
        *   **Nota Importante:** Este cambio requiere configuración a nivel de servidor. En proveedores como Bluehost, esto debe ajustarse en el **"MultiPHP INI Editor"** dentro de cPanel, modificando directivas como `upload_max_filesize` y `post_max_size`.
    *   **Nomenclatura de Archivos:** Se ha modificado la forma en que se guardan los archivos para que conserven su **nombre original**. Para prevenir que un archivo sobrescriba a otro con el mismo nombre, el sistema ahora añade un contador numérico al final del nombre si detecta una colisión (ej: `documento(1).pdf`, `documento(2).pdf`).
    *   **Archivos Modificados:**
        *   `Front/ctg/ctg_detalle.php` (eliminada restricción `accept`)
        *   `Front/pqr/pqr_detalle.php` (eliminada restricción `accept`)
        *   `api/ctg/ctg_insert_form.php` (nueva lógica de guardado)
        *   `api/pqr/pqr_insert_form.php` (nueva lógica de guardado)

*   **Gestión de Citas con Duración Variable:**
    *   Se implementó la capacidad de manejar citas con duraciones variables. Específicamente, la cita para "Elección de acabados" ahora reserva un bloque de 2 horas (120 minutos), mientras que las demás citas conservan la duración por defecto del responsable.
    *   Para lograr esto, se realizaron los siguientes cambios técnicos:
        *   **Base de Datos:** Se añadió la columna `duracion_minutos` a la tabla `agendamiento_visitas` para registrar la duración real de cada cita.
        *   **Backend:** Se actualizaron las APIs (`cita_create.php`, `horas_disponibles.php`, `citas_list.php`) para guardar, verificar y leer esta duración específica, asegurando una correcta detección de colisiones para citas de diferente duración.
        *   **Frontend:** El frontend (`cita_nueva.php`, `citas.php`) fue modificado para solicitar la duración especial y para mostrar correctamente el rango de tiempo completo (ej. 13:00 a 15:00).

---

### Mejoras y Correcciones (Agosto 2025 - Sistema de Citas)

*   **Corrección Completa del Sistema de Verificación de Colisiones de Citas:**
    *   Se identificó y corrigió un error crítico en el sistema de verificación de disponibilidad de horarios que permitía la doble reserva de responsables.
    *   **Problema Identificado:** El sistema no verificaba correctamente los solapamientos de tiempo entre citas existentes y nuevos horarios solicitados, causando que un mismo responsable pudiera tener citas solapadas.
    *   **Solución Implementada:** Se reescribió completamente la lógica de verificación de colisiones en `api/cita/horas_disponibles.php` para:
        *   ✅ **Verificar solapamientos de tiempo**: Considerar la duración real de cada cita (1h, 2h, etc.)
        *   ✅ **Prevenir doble reserva**: Un responsable no puede tener citas que se solapen
        *   ✅ **Verificación global de disponibilidad**: Si todos los responsables están ocupados, el horario se bloquea completamente
        *   ✅ **Compatibilidad con duración variable**: Funciona correctamente para citas de diferentes duraciones
    *   **Lógica de Verificación Implementada:**
        *   **Caso 1**: El nuevo slot empieza durante una cita existente
        *   **Caso 2**: El nuevo slot termina durante una cita existente  
        *   **Caso 3**: El nuevo slot contiene completamente una cita existente
    *   **Correcciones Técnicas Implementadas:**
        *   **Formato de DateTime**: Se corrigió el uso de `DateTime::createFromFormat()` para manejar correctamente fechas y horas
        *   **Verificación de solapamiento**: Se implementó comparación de timestamps para máxima precisión
        *   **Manejo de errores**: Se agregó validación robusta para evitar fallos en la creación de objetos DateTime
        *   **Logging optimizado**: Se eliminaron logs de debug innecesarios, manteniendo solo errores críticos
    *   **Archivos Modificados:**
        *   `api/cita/horas_disponibles.php` - Lógica completa de verificación de colisiones reescrita
        *   `api/cita/cita_create.php` - Verificación de solapamiento en la asignación de responsables
    *   **Resultado Final:**
        *   ✅ **Sistema completamente robusto**: Previene todos los escenarios de doble reserva
        *   ✅ **Verificación de colisiones**: Detecta correctamente solapamientos de tiempo
        *   ✅ **Integridad de horarios**: Mantiene la disponibilidad real de todos los responsables
        *   ✅ **Funcionalidad preservada**: Mantiene balanceo de carga y asignación inteligente de responsables


        **Se implemento una nueva logica de seleccion de acabados**
        Se