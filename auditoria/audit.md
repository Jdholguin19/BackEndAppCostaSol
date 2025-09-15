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

Se creará un nuevo archivo `api/helpers/audit_helper.php`. Este archivo contendrá una función reutilizable para registrar eventos de auditoría.

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

Se modificará cada archivo de la API en el directorio `api/` para incluir y utilizar la función `log_audit_action`. Esto se hará en cada punto donde se realice una acción significativa.

**Ejemplo en `api/login.php`:**

```php
// ... al inicio del archivo
require_once '../helpers/audit_helper.php';

// ... después de un login exitoso
log_audit_action($conn, 'LOGIN_SUCCESS', $user['id'], 'usuario');

// ... después de un login fallido
log_audit_action($conn, 'LOGIN_FAILURE', null, 'sistema', null, null, ['correo_intentado' => $correo]);
```

## 4. Plan de Integración por Módulo

A continuación, se listan los módulos y las acciones clave a auditar:

*   **Autenticación (`api/login.php`, `api/logout.php`)**
    *   `LOGIN_SUCCESS`, `LOGIN_FAILURE`
    *   `LOGOUT`
*   **Gestión de Usuarios (`api/user_crud.php`)**
    *   `CREATE_USER`, `UPDATE_USER`, `DELETE_USER`
*   **Gestión de Citas (`api/cita/*`)**
    *   `CREATE_CITA`, `CANCEL_CITA`, `UPDATE_CITA_STATUS`, `DELETE_CITA`
*   **Gestión de CTG (`api/ctg/*`)**
    *   `CREATE_CTG`, `UPDATE_CTG_STATUS`, `ADD_CTG_RESPONSE`, `UPDATE_CTG_OBSERVATION`
*   **Gestión de PQR (`api/pqr/*`)**
    *   `CREATE_PQR`, `UPDATE_PQR_STATUS`, `ADD_PQR_RESPONSE`, `UPDATE_PQR_OBSERVATION`
*   **Selección de Acabados (`api/guardar_seleccion_acabados.php`)**
    *   `SAVE_ACABADOS`
*   **Perfil de Usuario (`api/update_profile_picture.php`)**
    *   `UPDATE_PROFILE_PICTURE`
*   **Notificaciones Push (`api/update_player_id.php`)**
    *   `UPDATE_ONESIGNAL_PLAYER_ID`

Este proceso se repetirá para **todas las funciones y endpoints relevantes** de la API.

---

## 5. Alcance Detallado y Aclaraciones

### Nivel de Detalle de Auditoría

Se ha definido que el nivel de auditoría debe ser **detallado**.

*   **Para todas las operaciones de actualización (UPDATE)**, el sistema deberá registrar tanto el **valor antiguo** como el **valor nuevo** de los campos que se modifiquen. Esto se almacenará en la columna `details` (JSON) de la tabla `audit_log`.
*   **Ejemplo de implementación:** Antes de ejecutar una consulta `UPDATE`, se realizará una consulta `SELECT` para obtener el estado actual del registro. Luego, ambos datos (el antiguo y el nuevo) se pasarán a la función `log_audit_action`.

### Registro de Fechas de Creación

La solicitud de poder consultar elementos creados dentro de un rango de fechas (ej. "cuántos CTG se crearon entre dos fechas") se cubre de forma nativa con el diseño propuesto.

*   Cada vez que se cree un nuevo elemento (un CTG, un usuario, una cita, etc.), se registrará una acción (`CREATE_CTG`, `CREATE_USER`, etc.) en la tabla `audit_log`.
*   La columna `timestamp` de esa tabla guardará la fecha y hora exactas de la creación.
*   Será posible realizar consultas SQL para filtrar por rangos de fechas. Ejemplo:
    ```sql
    SELECT COUNT(*)
    FROM audit_log
    WHERE action = 'CREATE_CTG'
    AND timestamp BETWEEN '2025-01-01 00:00:00' AND '2025-01-31 23:59:59';
    ```

---

## 6. Decisiones Finales y Prioridades

Se han definido los siguientes puntos para finalizar el alcance del proyecto:

*   **Prioridades de Implementación:** El trabajo se centrará inicialmente en los siguientes módulos, considerados los más críticos:
    1.  Gestión de Citas (`api/cita/*`)
    2.  Gestión de CTG (`api/ctg/*`)
    3.  Gestión de PQR (`api/pqr/*`)
    4.  Selección de Acabados (`api/guardar_seleccion_acabados.php`)

*   **Acciones No Autenticadas:** No se registrarán los intentos de inicio de sesión fallidos en la nueva tabla `audit_log`, ya que esta información ya es capturada por la tabla existente `registro_login`.

*   **Visualización de Auditoría:** La creación de una interfaz de usuario para consultar los registros de auditoría se pospone para una fase futura. El enfoque actual es 100% en la captura de datos en el backend.

*   **Estructura de la Tabla:** Se aprueba la estructura de la tabla `audit_log` como está definida. La columna `details` de tipo JSON proporciona la flexibilidad necesaria para añadir información detallada específica de cada evento sin requerir cambios futuros en la estructura de la base de datos.

## 7. Siguientes Pasos

1.  **Modificar el archivo `portalao_appcostasol.sql`:** Añadir la sentencia `CREATE TABLE` para la nueva tabla `audit_log`.
2.  **Crear el archivo `helper`:** Implementar el archivo `api/helpers/audit_helper.php` con la función `log_audit_action()`.
3.  **Comenzar la Integración:** Empezar a integrar la función de auditoría en el primer módulo prioritario: **Gestión de Citas**.
