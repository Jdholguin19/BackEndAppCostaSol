# CostaSol App – Backend y Frontend

Aplicación web para clientes y responsables que centraliza CTG, PQR, citas/calendario, notificaciones, perfil de usuario, reportes y noticias, con integraciones externas (OneSignal, Kiss Flow, Microsoft Graph/Outlook).

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
  - Reportes y recursos
  - Avance de obra / construcción
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

- Backend: API en PHP bajo `api/` con endpoints para autenticación, menú, CTG, PQR, citas, notificaciones, perfil, reportes, integraciones y auditoría.
- Frontend: páginas PHP/HTML bajo `Front/` con vistas para login, menú principal, módulos (CTG, PQR, Citas), notificaciones, perfil/usuarios, overview interactivo y recursos.
- Integraciones: OneSignal (notificaciones push), Kiss Flow (webhooks y sincronización de datos), Outlook/Microsoft Graph (webhooks y sincronización de calendario).

---
## Configuración del entorno

- `config/db.php`: credenciales de base de datos (host, usuario, password, base).
- `config/config_outlook.php`: credenciales y configuración para la integración con Microsoft Graph/Outlook (client_id, secret, tenant, scopes, webhook URLs, etc.).
- OneSignal:
  - `manifest.json`: configuración PWA/manifesto.
  - `OneSignalSDKWorker.js`: service worker requerido por OneSignal para push.
  - Registrar/actualizar `player_id` vía `api/update_player_id.php`.
- Rutas públicas principales:
  - `index.php`, `Front/menu_front.php`, `Front/login_front.php`.
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

- Front: `Front/perfil.php`, `Front/users.php`.
- API: `api/perfil.php`, `api/user_crud.php`, `api/user_list.php`.
- Roles y permisos (RBAC): tablas `rol`, `rol_menu` determinan accesos a vistas y endpoints.

### Reportes y recursos

- Reportes: `Front/reportes_usuario.php`, `api/general_report_data.php`, `api/user_report_data.php`.
- Recursos y documentos: `api/mcm.php` (Manual de Uso), `api/paletavegetal.php`.

### Avance de obra / construcción

- Front: `Front/fase_detalle.php` (detalle de etapas por manzana/villa).
- API: `api/etapas_manzana_villa.php`, `api/propiedad_fase.php`.

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
  - Login → menú dinámico → CTG/PQR → citas → notificaciones → perfil.
  - Visualiza noticias y reportes propios.
- Responsable:
  - Login → menú con módulos operativos → gestión CTG/PQR → calendario responsable → notificaciones y perfil.
  - Puede ver reportes y recursos según rol.

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
- Base de datos:
  - Revise `config/db.php`; asegure disponibilidad de tablas referenciadas (usuarios, responsables, ctg, pqr, agendamiento_visitas, etc.).

---

## Estructura de directorios (parcial)

- `Front/`: vistas del frontend, overview, módulos (CTG, PQR, Citas, Perfil, Noticias, Reportes).
- `api/`: endpoints PHP (autenticación, menú, CTG, PQR, citas, notificaciones, perfil, reportes, integraciones).
- `config/`: configuración de base de datos y Outlook.
- `kiss_flow/`, `api/webhook_rdc/`, `api/webhook_ds/`: integraciones y sincronización.
- `logs/`, `error_log`: diagnóstico y auditoría.
- `uploads/`: almacenamiento de imágenes y archivos.

---

## Contribución

- Añada nuevas tarjetas al `Front/overview.html` con diagramas Mermaid y referencias a endpoints.
- Mantenga los nombres y convenciones de Mermaid para evitar errores.
- Documente cambios relevantes en este README (módulos, endpoints, flujos, integraciones).