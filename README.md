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

## Características y Funcionalidades Clave

El proyecto ofrece las siguientes funcionalidades principales:

*   **Autenticación y Autorización de Usuarios:**
    *   Inicio de sesión para usuarios (clientes/residentes) y responsables (personal).
    *   Autenticación basada en tokens.
    *   Control de acceso basado en roles (`rol` y `rol_menu` en la base de datos).
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
    *   Bootstrap (para el framework de UI y componentes)
    *   FullCalendar (para la visualización de calendarios)
    *   OneSignal (para la implementación de notificaciones push)
    *   Featherlight (para lightboxes de imágenes)
    *   jQuery (utilizado por Featherlight y posiblemente otros scripts)

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

Este `README.md` proporciona una visión general completa del proyecto. Para detalles específicos de implementación o depuración, se recomienda revisar el código fuente de los archivos relevantes.
