# Plan de Implementación: Sincronización con Webhooks de Kiss Flow

---

### **Objetivo**

Evolucionar el sistema de sincronización manual a un **sistema automático y en tiempo real** utilizando Webhooks. El nuevo sistema escuchará los eventos de Kiss Flow y actualizará la base de datos de la aplicación instantáneamente cuando se cree o modifique un registro en el dataset `DS_Documentos_Cliente`.

### **Prerrequisitos**

1.  La base de datos ya debe estar preparada con las columnas adicionales (ejecutar `alter_tables_for_sync.sql`).
2.  Se debe haber ejecutado el script de carga masiva (`sync_ds_cliente.php`) al menos una vez para asegurar que la mayoría de los datos ya estén sincronizados.

---

### **Arquitectura Propuesta**

La clave de esta implementación es **reutilizar el código existente** para evitar duplicar la lógica.

1.  **Lógica de Sincronización Centralizada:**
    *   Se creará un nuevo archivo, `sync_logic.php`, en `api/webhook_ds/`.
    *   Este archivo contendrá una única función, por ejemplo `process_kissflow_record(array $record, PDO $conn)`, que encapsulará toda la lógica que ya validamos para procesar un solo registro (buscar usuario, crear/actualizar usuario, buscar propiedad, crear/actualizar propiedad).

2.  **Modificación del Script Manual:**
    *   El script `sync_ds_cliente.php` se modificará ligeramente para que, en lugar de contener la lógica directamente, simplemente llame a la nueva función `process_kissflow_record()` dentro de su bucle `foreach`.

3.  **Nuevo Manejador de Webhooks:**
    *   Se creará el archivo principal de esta fase: `webhook_ds_handler.php` en `api/webhook_ds/`.
    *   Este será el **endpoint público** que Kiss Flow llamará.
    *   Su única responsabilidad será recibir la notificación del webhook, verificarla y pasar los datos del registro a la misma función `process_kissflow_record()`.

![Arquitectura Webhook](https://i.imgur.com/rG3h3zT.png)

---

### **Flujo de Ejecución del Webhook Handler**

1.  **Recepción del Evento:** Kiss Flow detecta un cambio (ej. un registro actualizado) y envía una petición `POST` a `webhook_ds_handler.php`.
2.  **Verificación de Seguridad:** El handler verifica que la petición sea legítima, comparando un "secreto" compartido entre Kiss Flow y el script.
3.  **Extracción de Datos:** El script decodifica el cuerpo de la petición (JSON) para obtener los datos del registro que cambió.
4.  **Procesamiento:** Llama a la función centralizada `process_kissflow_record()` para aplicar los cambios en la base de datos local.
5.  **Respuesta a Kiss Flow:** El handler responde inmediatamente a Kiss Flow con un código `200 OK` para confirmar que recibió el evento. Esto es crucial para que Kiss Flow no intente enviarlo de nuevo.

---

### **Pasos de Implementación**

**Paso 1: Crear el Archivo de Lógica Central (`sync_logic.php`)**

1.  Crear el archivo `api/webhook_ds/sync_logic.php`.
2.  Mover toda la lógica de procesamiento que está dentro del bucle `foreach` de `sync_ds_cliente.php` a una nueva función `process_kissflow_record(array $record, PDO $conn)` dentro de este nuevo archivo.
3.  Esta función contendrá todo lo que ya hemos depurado: transacciones, búsqueda de usuario, creación/actualización de usuario, búsqueda de propiedad y creación/actualización de propiedad.

**Paso 2: Modificar el Script Manual (`sync_ds_cliente.php`)**

1.  Eliminar la lógica de procesamiento que se movió en el paso anterior.
2.  Añadir `require_once 'sync_logic.php';` al inicio del script.
3.  Dentro del bucle `foreach`, simplemente llamar a `process_kissflow_record($record, $conn);`.

**Paso 3: Crear el Manejador de Webhooks (`webhook_ds_handler.php`)**

1.  Crear el archivo `api/webhook_ds/webhook_ds_handler.php`.
2.  Añadir `require_once` para `db.php`, `config.php` y el nuevo `sync_logic.php`.
3.  Implementar la lógica:
    *   Leer el cuerpo de la petición: `$payload = json_decode(file_get_contents('php://input'), true);`
    *   Verificar el secreto (token de seguridad).
    *   Extraer el registro de los datos: `$record = $payload['Data'][0];`
    *   Llamar a la función `process_kissflow_record($record, DB::getDB());`.
    *   Asegurarse de responder siempre con `http_response_code(200);`.

**Paso 4: Configurar el Webhook en Kiss Flow**

Esta es la configuración final que deberás realizar en la plataforma de Kiss Flow:

1.  Ve al dataset `DS_Documentos_Cliente`.
2.  En el menú, selecciona **Settings -> Webhooks**.
3.  Haz clic en **"Add new webhook"**.
4.  **URL del Webhook:** Ingresa la URL pública completa de tu nuevo script. Será algo como: `https://app.costasol.com.ec/api/webhook_ds/webhook_ds_handler.php`.
5.  **Secreto:** Define una clave secreta (una cadena de texto larga y aleatoria). Deberás copiar esta clave y pegarla dentro del script `webhook_ds_handler.php` para la verificación.
6.  **Eventos:** Selecciona los eventos que activarán el webhook. Como mínimo, deberías elegir:
    *   **When a record is created**
    *   **When a record is updated**

Una vez guardado, Kiss Flow comenzará a notificar a tu aplicación de cada cambio, automatizando completamente la sincronización.
