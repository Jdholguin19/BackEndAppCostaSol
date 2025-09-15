# Plan de Acción: Implementación de Registro de Auditoría

## 1. Introducción

El objetivo de este plan es implementar un sistema de registro de auditoría (audit trail) completo para la aplicación BackEndAppCostaSol. Este sistema registrará todas las acciones críticas realizadas por los usuarios y responsables, proporcionando un historial detallado de eventos para fines de seguridad, depuración y cumplimiento.

## 2. Diseño de la Base de Datos

Se creará una nueva tabla en la base de datos llamada `audit_log` para almacenar todos los registros de auditoría.

### Estructura de la tabla `audit_log`

```sql
CREATE TABLE `audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` BIGINT UNSIGNED NULL,
  `user_type` ENUM('usuario', 'responsable', 'sistema') NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `action` VARCHAR(255) NOT NULL COMMENT 'Ej: LOGIN_SUCCESS, CREATE_CITA, UPDATE_CTG_STATUS',
  `target_resource` VARCHAR(100) NULL COMMENT 'Ej: cita, ctg, pqr, usuario',
  `target_id` BIGINT UNSIGNED NULL COMMENT 'El ID del recurso afectado',
  `details` JSON NULL COMMENT 'Un objeto JSON con detalles adicionales, como valores antiguos y nuevos',
  PRIMARY KEY (`id`),
  INDEX `idx_user` (`user_id`, `user_type`),
  INDEX `idx_action` (`action`),
  INDEX `idx_resource` (`target_resource`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```

**Descripción de las columnas:**

*   `id`: Identificador único para cada registro de auditoría.
*   `timestamp`: Fecha y hora en que ocurrió el evento.
*   `user_id`: El ID del `usuario` o `responsable` que realizó la acción. Será `NULL` si la acción es del sistema o no autenticada.
*   `user_type`: El tipo de actor (`usuario`, `responsable`, `sistema`).
*   `ip_address`: La dirección IP desde la que se originó la solicitud.
*   `action`: Una cadena que describe la acción realizada (p. ej., `LOGIN_SUCCESS`, `CREATE_CITA`).
*   `target_resource`: El tipo de recurso que fue afectado (p. ej., `cita`, `ctg`).
*   `target_id`: El ID del recurso específico que fue afectado.
*   `details`: Un campo JSON para almacenar datos contextuales, como cambios de valores (`{ "old_status": "Abierto", "new_status": "En Progreso" }`).

## 3. Implementación del Backend

### Creación de un Helper de Auditoría

Se creó el archivo `api/helpers/audit_helper.php`. Este archivo contiene una función reutilizable para registrar eventos de auditoría.

```php
<?php
// api/helpers/audit_helper.php

/**
 * Registra un evento en el log de auditoría.
 *
 * @param PDO $conn La conexión a la base de datos.
 * @param string $action La acción realizada.
 * @param int|null $user_id El ID del usuario/responsable.
 * @param string $user_type El tipo de usuario ('usuario', 'responsable', 'sistema').
 * @param string|null $target_resource El recurso afectado.
 * @param int|null $target_id El ID del recurso afectado.
 * @param array|null $details Detalles adicionales en un array asociativo.
 */
function log_audit_action($conn, $action, $user_id, $user_type, $target_resource = null, $target_id = null, $details = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $details_json = $details ? json_encode($details) : null;

    $sql = "INSERT INTO audit_log (user_id, user_type, ip_address, action, target_resource, target_id, details)
            VALUES (:user_id, :user_type, :ip_address, :action, :target_resource, :target_id, :details)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':user_type' => $user_type,
        ':ip_address' => $ip_address,
        ':action' => $action,
        ':target_resource' => $target_resource,
        ':target_id' => $target_id,
        ':details' => $details_json
    ]);
}
?>
```

### Integración en los Endpoints de la API

Se modificaron los archivos de la API en el directorio `api/` para incluir y utilizar la función `log_audit_action` en cada punto donde se realiza una acción significativa.

## 4. Módulos Auditados

A continuación, se listan los módulos y las acciones clave que han sido auditadas:

*   **Autenticación**
    *   `LOGIN_SUCCESS`: Registrado en `api/login.php` al iniciar sesión exitosamente (usuario o responsable).
    *   `LOGIN_FAILURE`: Registrado en `api/login.php` al fallar un intento de inicio de sesión.
    *   `LOGOUT`: Registrado en `api/logout.php` al cerrar sesión.

*   **Gestión de Usuarios**
    *   `CREATE_USER`: Registrado en `api/user_crud.php` al crear un nuevo usuario.
    *   `UPDATE_USER`: Registrado en `api/user_crud.php` al actualizar un usuario (incluye datos antiguos y nuevos).
    *   `DELETE_USER`: Registrado en `api/user_crud.php` al eliminar un usuario (incluye datos del usuario eliminado).

*   **Gestión de Citas**
    *   `CREATE_CITA`: Registrado en `api/cita/cita_create.php` al crear una nueva cita.
    *   `CANCEL_CITA`: Registrado en `api/cita/cita_cancelar.php` al cancelar una cita.
    *   `UPDATE_CITA_STATUS`: Registrado en `api/cita/cita_update_estado.php` al actualizar el estado de una cita (incluye estado antiguo y nuevo).
    *   `DELETE_CITA`: Registrado en `api/cita/cita_eliminar.php` al eliminar una cita (incluye datos de la cita eliminada).

*   **Gestión de CTG**
    *   `CREATE_CTG`: Registrado en `api/ctg/ctg_create.php` al crear una nueva solicitud de CTG.
    *   `UPDATE_CTG_STATUS`: Registrado en `api/ctg/ctg_update_estado.php` al actualizar el estado de una CTG (incluye estado antiguo y nuevo).
    *   `ADD_CTG_RESPONSE`: Registrado en `api/ctg/ctg_insert_form.php` al añadir una respuesta a una CTG.
    *   `UPDATE_CTG_OBSERVATION`: Registrado en `api/ctg/ctg_update_observaciones.php` al actualizar las observaciones de una CTG (incluye observaciones antiguas y nuevas).

*   **Gestión de PQR**
    *   `CREATE_PQR`: Registrado en `api/pqr/pqr_create.php` al crear una nueva solicitud de PQR.
    *   `UPDATE_PQR_STATUS`: Registrado en `api/pqr/pqr_update_estado.php` al actualizar el estado de una PQR (incluye estado antiguo y nuevo).
    *   `ADD_PQR_RESPONSE`: Registrado en `api/pqr/pqr_insert_form.php` al añadir una respuesta a una PQR.
    *   `UPDATE_PQR_OBSERVATION`: Registrado en `api/pqr/pqr_update_observaciones.php` al actualizar las observaciones de una PQR (incluye observaciones antiguas y nuevas).

*   **Selección de Acabados**
    *   `SAVE_ACABADOS`: Registrado en `api/guardar_seleccion_acabados.php` al guardar la selección de acabados de una propiedad.

*   **Perfil de Usuario**
    *   `UPDATE_PROFILE_PICTURE`: Registrado en `api/update_profile_picture.php` al actualizar la foto de perfil (incluye URLs antiguas y nuevas).

*   **Notificaciones Push (OneSignal)**
    *   `UPDATE_ONESIGNAL_PLAYER_ID`: Registrado en `api/update_player_id.php` al actualizar el Player ID de OneSignal (incluye ID antiguo, nuevo y tipo de cambio: suscripción/desuscripción).

## 5. Funcionalidades Adicionales Implementadas

Además de los módulos priorizados, se han implementado las siguientes funcionalidades de auditoría y mejoras:

*   **Registro de Acceso a Módulos**
    *   `ACCESS_MODULE`: Registrado en `api/log_module_access.php` cada vez que un usuario o responsable accede a un módulo del menú principal (`Front/menu_front.php` y `Front/menu2.php`). Esto permite analizar la interacción y el uso de los módulos.

*   **Renovación Automática de Suscripciones de Webhook de Outlook**
    *   Se creó el script `api/outlook_webhook_renewer.php` para ser ejecutado como un cron job. Este script se encarga de renovar automáticamente las suscripciones de webhook de Outlook que están próximas a expirar, asegurando la continuidad de la sincronización bidireccional (`Outlook -> App`).

## 6. Alcance Detallado y Aclaraciones

### Nivel de Detalle de Auditoría

Se ha definido que el nivel de auditoría es **detallado**.

*   **Para todas las operaciones de actualización (UPDATE)**, el sistema registra tanto el **valor antiguo** como el **valor nuevo** de los campos que se modifican. Esto se almacena en la columna `details` (JSON) de la tabla `audit_log`.

### Registro de Fechas de Creación

La solicitud de poder consultar elementos creados dentro de un rango de fechas (ej. "cuántos CTG se crearon entre dos fechas") se cubre de forma nativa con el diseño propuesto.

*   Cada vez que se crea un nuevo elemento (un CTG, un usuario, una cita, etc.), se registra una acción (`CREATE_CTG`, `CREATE_USER`, etc.) en la tabla `audit_log`.
*   La columna `timestamp` de esa tabla guarda la fecha y hora exactas de la creación.
*   Es posible realizar consultas SQL para filtrar por rangos de fechas. Ejemplo:
    ```sql
    SELECT COUNT(*)
    FROM audit_log
    WHERE action = 'CREATE_CTG'
    AND timestamp BETWEEN '2025-01-01 00:00:00' AND '2025-01-31 23:59:59';
    ```

## 7. Conclusiones y Próximos Pasos

*   **Prioridades de Implementación:** Los módulos considerados más críticos han sido completamente auditados.
*   **Visualización de Auditoría:** La creación de una interfaz de usuario para consultar los registros de auditoría se pospone para una fase futura. El enfoque actual se ha centrado en la captura de datos en el backend.
*   **Estructura de la Tabla:** La estructura de la tabla `audit_log` ha sido aprobada y proporciona la flexibilidad necesaria para añadir información detallada específica de cada evento sin requerir cambios futuros en la estructura de la base de datos.

La implementación del registro de auditoría en el backend ha sido completada según el alcance definido.