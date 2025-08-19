# Proyecto BackEndAppCostaSol

Este proyecto es una aplicación web integral diseñada para la gestión de propiedades, citas, solicitudes de servicio al cliente (CTG/PQR), noticias y administración de usuarios para la entidad "CostaSol". La aplicación está estructurada con una clara separación entre el frontend (HTML, CSS, JavaScript) y el backend (PHP API), lo que facilita su mantenimiento y escalabilidad.

## Estructura de Carpetas

A continuación, se detalla la organización de los archivos y directorios principales del proyecto:

```
. (Raíz del Proyecto)
├── api/                          # Contiene todos los endpoints de la API backend
│   ├── cita/                     # APIs relacionadas con la gestión de citas
│   │   ├── cita_cancelar.php
│   │   ├── cita_create.php
│   │   ├── cita_eliminar.php
│   │   ├── citas_list.php
│   │   ├── cita_update_estado.php  # NUEVO: API para actualizar el estado de las citas
│   │   ├── dias_disponibles.php
│   │   └── horas_disponibles.php
│   ├── ctg/                      # APIs relacionadas con la gestión de Contingencias (CTG)
│   │   ├── ctg_create.php
│   │   ├── ctg_estados.php
│   │   ├── ctg_insert_form.php
│   │   ├── ctg_list.php
│   │   ├── ctg_observaciones.php
│   │   ├── ctg_respuestas.php
│   │   ├── ctg_update_estado.php
│   │   └── ctg_update_observaciones.php
│   ├── pqr/                      # APIs relacionadas con la gestión de Peticiones, Quejas y Recomendaciones (PQR)
│   │   ├── pqr_create.php
│   │   ├── pqr_estados.php
│   │   ├── pqr_insert_form.php
│   │   ├── pqr_list.php
│   │   ├── pqr_observaciones.php
│   │   ├── pqr_respuestas.php
│   │   ├── pqr_update_estado.php
│   │   └── pqr_update_observaciones.php
│   ├── bottom_nav.php              # API para obtener ítems de navegación inferior
│   ├── calendario_responsable.php  # API para obtener datos del calendario de responsables
│   ├── etapas_manzana_villa.php    # API para obtener etapas de construcción por manzana/villa
│   ├── garantias.php               # API para obtener información de garantías
│   ├── login.php                   # API para autenticación de usuarios y responsables
│   ├── logout.php                  # API para cerrar sesión
│   ├── mcm.php                     # API para servir el manual de uso y mantenimiento (PDF)
│   ├── menu.php                    # API para obtener ítems de menú según el rol
│   ├── noticias.php                # API para gestionar noticias
│   ├── notificaciones.php          # API para obtener notificaciones
│   ├── notificaciones_count.php    # API para contar notificaciones no leídas
│   ├── notificaciones_mark_read.php# API para marcar notificaciones como leídas
│   ├── obtener_propiedades.php     # API para obtener propiedades de un usuario
│   ├── paletavegetal.php           # API para servir el PDF de paleta vegetal
│   ├── propiedad_fase.php          # API para obtener la fase de una propiedad
│   ├── propositos.php              # API para obtener propósitos de agendamiento
│   ├── responsables_list.php       # API para listar responsables
│   ├── update_player_id.php        # API para actualizar OneSignal Player ID
│   ├── user_crud.php               # API para operaciones CRUD de usuarios
│   └── validate_responsable.php    # API para validar tokens de responsables
├── appcostasol/                  # Contiene una versión alternativa o anterior de la aplicación
│   ├── api/                      # API de la versión alternativa
│   │   └── login.php
│   ├── config/                   # Configuración de la versión alternativa
│   │   └── db.php
│   └── Front/                    # Frontend de la versión alternativa
│       ├── user_crud.php
│       └── users.php
├── Front/                        # Contiene todas las páginas frontend (HTML/PHP) y sus assets
│   ├── assets/                   # Archivos estáticos (CSS, JS, imágenes)
│   │   └── css/                  # Hojas de estilo CSS
│   │       ├── style_cita_nueva.css
│   │       ├── style_citas.css
│   │       ├── style_ctg_detalle.css
│   │       ├── style_ctg_nuevo.css
│   │       ├── style_ctg.css
│   │       ├── style_fdetale.css
│   │       ├── style_garantia.css
│   │       ├── style_login.css
│   │       ├── style_main.css
│   │       ├── style_noticia.css
│   │       ├── style_notifications.css
│   │       ├── style_panel_calendario.css
│   │       ├── style_pqr_detalle.css
│   │       ├── style_pqr_nuevo.css
│   │       └── style_pqr.css
│   ├── ctg/                      # Páginas frontend para la gestión de CTG
│   │   ├── ctg_detalle.php
│   │   ├── ctg_nuevo.php
│   │   └── ctg.php
│   ├── includes/                 # Archivos de inclusión comunes (cabeceras, funciones, etc.)
│   ├── pqr/                      # Páginas frontend para la gestión de PQR
│   │   ├── pqr_detalle.php
│   │   ├── pqr_nuevo.php
│   │   └── pqr.php
│   ├── cita_nueva.php            # Página para agendar nuevas citas
│   ├── citas.php                 # Página para listar y gestionar citas
│   ├── fase_detalle.php          # Página para ver detalles de etapas de construcción
│   ├── garantias.php             # Página para ver información de garantías
│   ├── login_front.php           # Página de inicio de sesión
│   ├── menu_front.php            # Página del menú principal
│   ├── menu2.php                 # Página alternativa del menú principal
│   ├── noticia.php               # Página para la gestión de noticias (solo para admins)
│   ├── notificaciones.php        # Página para ver notificaciones
│   ├── panel_calendario.php      # Página del calendario de responsables
│   ├── register_front.php        # Página de registro de usuario
│   └── users.php                 # Página para la gestión de usuarios (solo para admins)
├── config/                       # Archivos de configuración
│   └── db.php                    # Configuración de conexión a la base de datos
├── imagenes/                     # Almacena varios assets de imagen utilizados en la aplicación
│   ├── admin.svg
│   ├── CalendarioResp.svg
│   ├── mcm.svg
│   ├── news.svg
│   ├── notificacion.svg
│   ├── pqr.svg
│   └── tree.svg
├── SharePoint/                   # Scripts relacionados con la integración de SharePoint
│   ├── ExtraerURL.php
│   ├── mostrar_imagen.php
│   └── MostrarImagenes.php
├── .geminiignore                 # Archivo de configuración para ignorar rutas por Gemini
├── GEMINI.md                     # Documentación específica de Gemini
├── Manual_de_uso.pdf             # Manual de uso de la aplicación
├── OneSignalSDKWorker.js         # Script de servicio de OneSignal para notificaciones push
├── paleta_vegetal.pdf            # Documento PDF de paleta vegetal
├── portalao_appcostasol.sql      # Esquema de la base de datos y datos de ejemplo


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
    *   Creación de nuevas citas (`cita_nueva.php`, `api/cita/cita_create.php`).
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

## Resumen de Funcionalidades de la API (para Migración a Laravel)

Esta sección detalla los endpoints de la API existentes y sus funcionalidades principales, sirviendo como referencia para la migración a un nuevo framework como Laravel.

### Autenticación y Gestión de Usuarios

*   **`api/login.php`**: Maneja el inicio de sesión de usuarios y responsables, devolviendo un token de autenticación.
*   **Mecanismo de Autenticación:** El sistema utiliza un mecanismo de autenticación basado en tokens. Tras un inicio de sesión exitoso en `api/login.php`, el servidor genera un token JWT (JSON Web Token) o un token de sesión seguro y lo devuelve al cliente.
*   **Manejo de Tokens en el Cliente:** El token recibido se almacena en el `localStorage` del navegador (clave `cs_token`). Para todas las solicitudes subsiguientes a endpoints protegidos de la API, este token debe incluirse en el encabezado `Authorization` como un `Bearer Token` (ej: `Authorization: Bearer <tu_token>`).
*   **Validación de Sesión:** El backend valida la autenticidad y vigencia de este token en cada solicitud protegida para asegurar que el usuario tiene permiso para acceder al recurso.
*   **`api/logout.php`**: Invalida la sesión del usuario.
*   **`api/user_crud.php`**: Proporciona operaciones CRUD (Crear, Leer, Actualizar, Eliminar) para la gestión de usuarios.
*   **`api/validate_responsable.php`**: Valida tokens específicos para responsables.
*   **`api/update_player_id.php`**: Actualiza el ID de OneSignal Player para notificaciones push.

### Gestión de Citas

*   **`api/cita/cita_create.php`**: Crea una nueva cita.
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

*   **`api/obtener_propiedades.php`**: Obtiene propiedades, filtradas por usuario o todas para responsables.
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
*   **`menu_front.php` / `menu2.php`**: Páginas principales post-autenticación. Muestran el nombre del usuario, avatar, y un menú dinámico basado en el rol. Incluyen la lógica para cerrar sesión (`api/logout.php`) y la integración con OneSignal para notificaciones push (`api/update_player_id.php`).

### Gestión de Propiedades y Avance de Obra

*   **`menu_front.php`**: Permite al usuario seleccionar entre sus propiedades asignadas (si tiene varias) a través de pestañas dinámicas.
*   **`fase_detalle.php`**: Muestra el detalle de las etapas de construcción de una propiedad específica (manzana y villa), incluyendo el porcentaje de avance y fotos asociadas. Interactúa con `api/etapas_manzana_villa.php` y `api/propiedad_fase.php`.

### Gestión de Citas

*   **`citas.php`**: Muestra una lista de citas agendadas por el usuario o asignadas al responsable. Permite cancelar citas (`api/cita/cita_cancelar.php`) y, para administradores/responsables, actualizar el estado de las citas (`api/cita/cita_update_estado.php`).
*   **`cita_nueva.php`**: Formulario para agendar una nueva cita. Permite seleccionar la propiedad, el propósito de la visita, la fecha (con un calendario interactivo `flatpickr.js`) y la hora (con un selector de rueda vertical). Interactúa con `api/obtener_propiedades.php`, `api/propositos.php`, `api/cita/dias_disponibles.php`, `api/cita/horas_disponibles.php` y `api/cita/cita_create.php`.

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
*   **`panel_calendario.php`**: Muestra un calendario de citas para los responsables. Los administradores pueden ver el calendario de todos los responsables. Utiliza FullCalendar.js. Interactúa con `api/responsables_list.php` y `api/calendario_responsable.php`.
*   **`noticia.php`**: (Panel de administración) Permite a los administradores crear, listar y eliminar noticias. Interactúa con `api/noticias.php`.
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
    *   La causa fue que la llamada `fetch` en `Front/menu_front.php` para obtener propiedades no estaba incluyendo el encabezado `Authorization` con el token de sesión, a pesar de que la API lo requería.
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

*   **Almacenamiento:** Los archivos adjuntos (imágenes de problemas/soluciones en CTG/PQR, imágenes de noticias, fotos de progreso de construcción) se almacenan actualmente en el **sistema de archivos local del servidor web**. Las rutas a estos archivos se guardan en la base de datos (campos `url_adjunto`, `url_problema`, `url_solucion`, `url_imagen` en tablas como `respuesta_ctg`, `respuesta_pqr`, `noticia`, `progreso_construccion`).
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

---

Este `README.md` proporciona una visión general completa del proyecto. Para detalles específicos de implementación o depuración, se recomienda revisar el código fuente de los archivos relevantes.
