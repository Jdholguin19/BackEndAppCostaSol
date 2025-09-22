# Configuración del Webhook de Kiss Flow para Sincronización

Esta guía detalla los pasos para configurar la acción "Make an HTTP call" en Kiss Flow y conectar con el endpoint de sincronización de la aplicación.

## Paso 1: Configuración General de la Petición

- **Method (Método):** `POST`
- **URL:** `https://app.costasol.com.ec/api/webhook_ds/webhook_ds_handler.php`

## Paso 2: Configuración de Cabeceras (HTTP Headers)

Esta es la parte de **seguridad**. Es crucial para que el servidor acepte la petición.

1.  En la sección **"HTTP headers"**, haz clic en "Add fields".
2.  Configura la única cabecera necesaria:
    - **Key:** `Authorization`
    - **Value:** `Bearer MiAppCostaSol_SyncWebApp_2025!`

    *Nota: El valor debe ser exactamente ese, incluyendo la palabra `Bearer` y el espacio.*

## Paso 3: Configuración del Cuerpo de la Petición (Body Parameters)

Aquí es donde se especifican los datos que Kiss Flow enviará al servidor. Debes mapear cada campo de tu dataset al cuerpo de la petición.

1.  En la sección **"Body parameters"**, haz clic en "Add fields" para cada uno de los siguientes campos.
2.  Para cada campo, escribe el "Key" tal como aparece en la lista y en "Value", usa el selector de campos de Kiss Flow (`fx`) para elegir el campo correspondiente de tu dataset.

### Lista de Keys y Valores a Mapear:

| Key                   | Value (Campo del Dataset en Kiss Flow) |
| --------------------- | -------------------------------------- |
| `_id`                 | Mapear al campo `_id`                  |
| `Identificacion`      | Mapear al campo `Identificacion`       |
| `Nombre_Cliente`      | Mapear al campo `Nombre_Cliente`       |
| `Comentario_Gerencia` | Mapear al campo `Comentario_Gerencia`  |
| `Tipo_de_Fachada`     | Mapear al campo `Tipo_de_Fachada`      |
| `RUC`                 | Mapear al campo `RUC`                  |
| `Rev_Gerencia`        | Mapear al campo `Rev_Gerencia`         |
| `Convenio`            | Mapear al campo `Convenio`             |
| `Etapa`               | Mapear al campo `Etapa`                |
| `Modelo`              | Mapear al campo `Modelo`               |
| `Mz_Solar`            | Mapear al campo `Mz_Solar`             |
| `Proyecto`            | Mapear al campo `Proyecto`             |
| `Fecha_de_Entrega`    | Mapear al campo `Fecha_de_Entrega`     |

## Paso 4: Guardar y Probar

1.  Guarda los cambios en la configuración de la integración en Kiss Flow.
2.  **No uses el botón de "Test"** que vimos anteriormente.
3.  Para probar, ve a tu dataset y **realiza una modificación real en un registro**. Esto disparará el webhook con datos reales y tu servidor podrá procesarlos.
