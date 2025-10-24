# CostaSol App – Backend y Frontend

Aplicación web para clientes y responsables que centraliza CTG, PQR, citas/calendario, notificaciones, perfil de usuario, reportes, noticias y **garantías**, con integraciones externas (OneSignal, Kiss Flow, Microsoft Graph/Outlook).

---

## Índice

- Descripción general
- Arranque rápido
- Configuración del entorno
- Overview interactivo (diagramas y guía)
- Arquitectura y módulos
  - Autenticación y sesiones
  - Menú y navegación
  - Notificaciones (push y en-app)
  - CTG
  - PQR
  - Citas y calendario (Outlook)
  - Perfil y gestión de usuarios
    - Sistema de Contexto de IA para Clientes
  - Reportes y recursos
  - Avance de obra / construcción
  - Garantías
  - Noticias
  - Auditoría y logs
  - Integraciones externas (Kiss Flow, Outlook, OneSignal)
- Flujos de usuario (Cliente vs Responsable)
- Seguridad y buenas prácticas
- Diagramas Mermaid: convenciones
- Solución de problemas (FAQ)
- Estructura de directorios (parcial)
- Contribución

---

## Descripción general

- Backend: API en PHP bajo `api/` con endpoints para autenticación, menú, CTG, PQR, citas, notificaciones, perfil, reportes, **garantías**, **contexto de IA**, integraciones y auditoría.
- Frontend: páginas PHP/HTML bajo `Front/` con vistas para login, menú principal, módulos (CTG, PQR, Citas), notificaciones, perfil/usuarios con **análisis inteligente de clientes**, overview interactivo y recursos.
- Integraciones: OneSignal (notificaciones push), Kiss Flow (webhooks y sincronización de datos), Outlook/Microsoft Graph (webhooks y sincronización de calendario), **IA para análisis de contexto de clientes**.

---
## Configuración del entorno

- `config/db.php`: credenciales de base de datos (host, usuario, password, base).
- `config/config_outlook.php`: credenciales y configuración para la integración con Microsoft Graph/Outlook (client_id, secret, tenant, scopes, webhook URLs, etc.).
- OneSignal:
  - `manifest.json`: configuración PWA/manifesto.
  - `OneSignalSDKWorker.js`: service worker requerido por OneSignal para push.
  - Registrar/actualizar `player_id` vía `api/update_player_id.php`.
- Rutas públicas principales:
  - `index.php`, `Front/menu_front.php`, `Front/login_front.php`, `Front/garantias.php`.
- Rutas administrativas:
  - `Front/admin/admin_garantias.php` (requiere rol responsable).
- Archivos útiles:
  - `Manual_de_uso.pdf`, `paleta_vegetal.pdf` (recursos estáticos).
  - `logs/csrf_debug.log`, `error_log` (diagnóstico).

---

## Overview interactivo (Front/overview.html)

`Front/overview.html` resume la arquitectura y flujos clave con Mermaid y secciones interactivas.

- UI: Bootstrap 5, Bootstrap Icons.
- Diagramas: Mermaid v10 (flowchart/sequence).
- Tema: botón para alternar claro/oscuro; estado en `localStorage.overview_theme`.
- Render seguro: se guarda el código original del diagrama en `data-src`, se valida con `mermaid.parse`, se re-renderiza únicamente cuando la tarjeta (card) está visible.
- Convenciones:
  - Evite etiquetas HTML o caracteres `<` y `>` en textos de diagramas; use texto plano.
  - Prefiera etiquetas cortas y ASCII si hay errores de parseo.

---

## Arquitectura y módulos

### Autenticación y sesiones

- Front: `Front/login_front.php`
- API: `api/login.php`, `api/logout.php`, `api/menu.php`, `api/log_module_access.php`
- Flujo:
  - Validación de credenciales → token (Bearer) + datos de usuario/responsable.
  - Almacenamiento local en Front: `cs_token`, `cs_usuario` (localStorage).
  - Acceso a módulos vía menú dinámico (`api/menu.php`).
- Notas:
  - Validación del token en tablas `usuario` y `responsable`.
  - Redirección a `Front/login_front.php` si no existe `cs_usuario`.

### Menú y navegación

- `Front/menu_front.php` y `Front/menu2.php`: vista principal post-login.
- Menú dinámico según rol; muestra avatar y nombre; acceso rápido a módulos.
- Auditoría de acceso a módulos con `api/log_module_access.php`.

### Notificaciones (push y en-app)

- Front: `Front/notificaciones.php` + indicador en `menu_front.php`.
- API:
  - Listado: `api/notificaciones.php`
  - Conteo: `api/notificaciones_count.php`
  - Marcar leído: `api/notificaciones_mark_read.php`
  - Push OneSignal: `api/helpers/notificaciones.php`
  - Player: `api/update_player_id.php`
- Flujo en Front:
  - Suscripción a OneSignal, obtención de `player_id`, sincronización con backend.
  - Indicador con conteo real-time (hasta 9, y “+9” para superiores).

### CTG

- Front: `Front/ctg/ctg.php`, `Front/ctg/ctg_nuevo.php`, `Front/ctg/ctg_detalle.php`.
- API: `api/ctg/ctg_create.php`, `api/ctg/ctg_list.php`, `api/ctg/ctg_respuestas.php`, `api/ctg/ctg_insert_form.php`, `api/ctg/ctg_update_estado.php`, `api/ctg/ctg_observaciones.php`.
- Catálogos/estados: `api/ctg/ctg_estados.php`, `api/ctg/tipo_ctg.php`, `api/ctg/subtipo_ctg.php`.

### PQR

- Front: `Front/pqr/pqr.php`, `Front/pqr/pqr_nuevo.php`, `Front/pqr/pqr_detalle.php`.
- API: `api/pqr/pqr_create.php`, `api/pqr/pqr_list.php`, `api/pqr/pqr_respuestas.php`, `api/pqr/pqr_insert_form.php`, `api/pqr/pqr_update_estado.php`, `api/pqr/pqr_observaciones.php`.
- Catálogos/estados: `api/pqr/pqr_estados.php`, `api/pqr/tipo_pqr.php`, `api/pqr/subtipo_pqr.php`.

### Citas y calendario (Outlook)

- Front: `Front/citas.php`, `Front/cita_nueva.php`, `Front/cita_responsable.php`, `Front/panel_calendario.php`.
- API: `api/cita/dias_disponibles.php`, `api/cita/horas_disponibles.php`, `api/cita/cita_create.php`, `api/cita/citas_list.php`, `api/cita/cita_cancelar.php`, `api/cita/cita_eliminar.php`.
- Calendario responsable (feed): `api/calendario_responsable.php`.
- Integración Outlook:
  - Webhook de cambios: `api/outlook_webhook.php`.
  - Tabla `agendamiento_visitas` con `duracion_minutos` para tipos de cita (ej. 120 min “Elección de acabados”).
  - Asignación inteligente de responsables: evita solapamientos y distribuye carga (excluye ID 3 si aplica reglas internas).

### Perfil y gestión de usuarios

- Front: `Front/perfil.php`, `Front/users.php`, `Front/chat/perfil/perfil.php`.
- API: `api/perfil.php`, `api/user_crud.php`, `api/user_list.php`, `api/chat/perfil/contexto_ia.php`.
- Roles y permisos (RBAC): tablas `rol`, `rol_menu` determinan accesos a vistas y endpoints.

#### Sistema de Contexto de IA para Clientes

Sistema inteligente de análisis de clientes que genera automáticamente contexto profesional basado en el historial completo del usuario.

##### Funcionalidades principales:
- **Generación automática**: El contexto se genera automáticamente al acceder al perfil del cliente (`Front/chat/perfil/perfil.php?user_id=X`)
- **Análisis integral**: Incluye información personal, propiedades, CTGs, PQRs, mensajes de chat y historial de citas
- **Detección de patrones**: Identifica comportamientos, problemas recurrentes y patrones de interacción
- **Recomendaciones inteligentes**: Sugiere estrategias de manejo y puntos de atención especial
- **Límite de palabras**: Contexto conciso limitado a 300 palabras para lectura rápida

##### Datos analizados:
- **Información personal**: Datos básicos del usuario y propiedades asociadas
- **CTGs (Control de Tareas Generales)**: Últimos 10 CTGs con todas sus respuestas asociadas
- **PQRs (Peticiones, Quejas y Reclamos)**: Últimos 10 PQRs con todas sus respuestas
- **Mensajes de chat**: Últimos 5 mensajes del usuario con fechas y tipo de remitente
- **Historial de citas**: Citas programadas y su estado

##### API Endpoint:
- **Contexto IA**: `api/chat/perfil/contexto_ia.php` - Genera contexto inteligente del cliente
  - Requiere autenticación Bearer token
  - Parámetro: `user_id` (ID del cliente a analizar)
  - Respuesta: JSON con contexto generado por IA

##### Características técnicas:
- **Integración con IA**: Utiliza prompts estructurados para análisis profesional
- **Datos completos**: Combina información de múltiples tablas (usuarios, propiedades, CTG, PQR, chat_message)
- **Respuestas concatenadas**: Utiliza `GROUP_CONCAT` para incluir todas las respuestas de CTG y PQR
- **Generación automática**: Se ejecuta automáticamente al cargar el perfil del cliente
- **Interfaz responsive**: Muestra el contexto en una sección dedicada con indicador de carga

##### Flujo de uso:
1. **Acceso al perfil**: Responsable accede a `perfil.php?user_id=X`
2. **Generación automática**: El sistema genera el contexto automáticamente
3. **Análisis IA**: La IA analiza todo el historial del cliente
4. **Contexto profesional**: Se muestra un resumen con recomendaciones y puntos clave
5. **Toma de decisiones**: El responsable puede usar el contexto para mejorar la atención

### Reportes y recursos

- Reportes: `Front/reportes_usuario.php`, `api/general_report_data.php`, `api/user_report_data.php`.
- Recursos y documentos: `api/mcm.php` (Manual de Uso), `api/paletavegetal.php`.

### Avance de obra / construcción

- Front: `Front/fase_detalle.php` (detalle de etapas por manzana/villa).
- API: `api/etapas_manzana_villa.php`, `api/propiedad_fase.php`.

### Garantías

Sistema completo de gestión de garantías con filtrado inteligente por tipo de propiedad y roles de usuario.

#### Funcionalidades principales:
- **Vista pública**: `Front/garantias.php` - Muestra garantías aplicables según tipo de propiedad del usuario
- **Administración**: `Front/admin/admin_garantias.php` - Dashboard completo para gestión CRUD
- **Filtrado inteligente**:
  - **Responsables**: Ven todas las garantías del sistema
  - **Usuarios**: Ven garantías generales + específicas de su tipo de propiedad
- **Tiempo en meses**: Cálculo preciso de vigencia (no años)
- **Responsive design**: Optimizado para móviles y desktop

#### Base de datos:
- Tabla `garantias`: `id`, `nombre`, `descripcion`, `tiempo_garantia_meses`, `tipo_propiedad_id`, `estado`, `orden`
- Relaciones: FK a `tipo_propiedad.id`
- Script: `garantias_structure.sql`

#### API Endpoints:
- **Pública**: `api/garantias.php` - Lista garantías filtradas con cálculo de vigencia
- **Administrativa**: `api/admin_garantias.php` - CRUD completo con autenticación de responsable
  - `GET /`: Listar todas las garantías
  - `GET /?action=get_tipo_propiedad`: Obtener tipos de propiedad
  - `POST /`: Crear nueva garantía
  - `PUT /`: Actualizar garantía existente
  - `DELETE /?id={id}`: Eliminar garantía

#### Características técnicas:
- **Autenticación**: Token Bearer requerido
- **Roles**: Diferenciación entre responsables (acceso total) y usuarios (filtrado)
- **Cálculo de vigencia**: Basado en fecha de entrega de propiedad
- **Ordenamiento**: Campo `orden` para control manual del orden de display
- **Estados**: Activo/Inactivo para control de visibilidad
- **Responsive**: Media queries para móviles, tablas scrollables, botones adaptativos

#### Flujo de usuario:
1. **Usuario normal**: Ve garantías aplicables a su propiedad con cálculo de vigencia
2. **Responsable**: Accede a dashboard administrativo desde botón "Administrar"
3. **Administración**: CRUD completo con filtros por tipo de propiedad
4. **Orden automático**: Al crear garantías, se asigna el orden máximo + 1

### Noticias

- Front: `Front/noticia.php`.
- API: `api/noticias.php`.

### Auditoría y logs

- Auditoría de acceso: `api/log_module_access.php`, `api/helpers/audit_helper.php`.
- Diagnóstico: `error_log`, `logs/csrf_debug.log`.

### Integraciones externas

- Kiss Flow (RDC/DS):
  - Webhooks: `api/webhook_rdc/webhook_handler.php`.
  - Sincronizaciones: `api/webhook_rdc/sync_ds_cliente.php`, `api/webhook_ds/sync_logic.php`.
- Microsoft Graph/Outlook:
  - `api/outlook_webhook.php` + helpers de sincronización.
- OneSignal:
  - `api/helpers/notificaciones.php`, `OneSignalSDKWorker.js`.

---

## Flujos de usuario

- Cliente:
  - Login → menú dinámico → CTG/PQR → citas → **garantías** → notificaciones → perfil.
  - Visualiza noticias, reportes propios y garantías aplicables a su propiedad.
- Responsable:
  - Login → menú con módulos operativos → gestión CTG/PQR → calendario responsable → **administrar garantías** → **análisis de contexto de clientes** → notificaciones y perfil.
  - Puede ver reportes, recursos, gestionar todas las garantías del sistema según rol y **acceder al contexto inteligente de clientes para mejorar la atención**.

---

## Seguridad y buenas prácticas

- Autenticación por token (Bearer) en `Authorization`.
- RBAC: verificación de acceso según `rol_id` en endpoints críticos.
- Validación y sanitización de entradas en API (evitar inyección).
- No incluir HTML/`<>` en textos de Mermaid (evitar errores y XSS en overview).
- Auditoría de accesos: registro al abrir módulos desde el menú.
- Manejo de CSRF y logs de depuración.

---

## Diagramas Mermaid: convenciones

- Use texto plano en labels; evite `<` y `>`.
- Prefiera ASCII (evitar acentos/paréntesis si generan parse errors).
- Guardar código original en `data-src`; validar con `mermaid.parse` antes de `render`.
- Renderizar al abrir la tarjeta para evitar errores en contenedores ocultos (`display:none`).
- Al cambiar tema, re-inicializar Mermaid y re-renderizar usando el código original.

---

## Solución de problemas (FAQ)

- Overview marca `UnknownDiagramError`:
  - Revise que el bloque tenga encabezado válido (`flowchart LR`, `graph TD`, `sequenceDiagram`).
  - Evite HTML o caracteres especiales en los labels; mantenga texto plano.
  - Borre `localStorage.overview_theme` si el tema quedó inconsistente.
- No carga menú o módulos:
  - Verifique token y presencia de `cs_usuario` en localStorage; si falta, vuelva a `Front/login_front.php`.
- OneSignal no suscribe:
  - Revise `OneSignalSDKWorker.js` y permisos de notificación del navegador; confirme envío de `player_id` mediante `api/update_player_id.php`.
- Calendario no sincroniza:
  - Confirme `config/config_outlook.php` y la suscripción de webhooks; verifique `api/outlook_webhook.php`.
- **Garantías no se muestran correctamente**:
  - Verifique que la tabla `garantias` existe y tiene datos; confirme permisos de token.
  - Para usuarios: asegure que tienen una propiedad con `fecha_entrega`; responsables ven todas las garantías.
  - Revise `api/garantias.php` y `api/admin_garantias.php` por errores en logs.
- Base de datos:
  - Revise `config/db.php`; asegure disponibilidad de tablas referenciadas (usuarios, responsables, ctg, pqr, agendamiento_visitas, **garantias**, etc.).

---

## Estructura de directorios (parcial)

- `Front/`: vistas del frontend, overview, módulos (CTG, PQR, Citas, Perfil, Noticias, Reportes, **Garantías**).
- `Front/admin/`: vistas administrativas (**admin_garantias.php** para gestión de garantías).
- `Front/chat/perfil/`: módulo de perfil de clientes con **contexto de IA** (**perfil.php** para vista de perfil con análisis automático).
- `api/`: endpoints PHP (autenticación, menú, CTG, PQR, citas, notificaciones, perfil, reportes, **garantías**, integraciones).
- `api/admin/`: endpoints administrativos (**admin_garantias.php** para CRUD de garantías).
- `api/chat/perfil/`: endpoints de análisis de clientes (**contexto_ia.php** para generación de contexto inteligente).
- `config/`: configuración de base de datos y Outlook.
- `garantias_structure.sql`: script de creación de tabla de garantías.
- `assets/css/style_garantia.css`: estilos específicos para el módulo de garantías.
- `kiss_flow/`, `api/webhook_rdc/`, `api/webhook_ds/`: integraciones y sincronización.
- `logs/`, `error_log`: diagnóstico y auditoría.
- `uploads/`: almacenamiento de imágenes y archivos.

---

## Contribución

- Añada nuevas tarjetas al `Front/overview.html` con diagramas Mermaid y referencias a endpoints.
- Mantenga los nombres y convenciones de Mermaid para evitar errores.
- Documente cambios relevantes en este README (módulos, endpoints, flujos, integraciones).