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
    *   Este archivo contendrá una única función, por ejemplo `process_kissflow_record(array $record, PDO $conn)`, que encapsulará toda la lógica que ya validamos para procesar un solo registro.

2.  **Modificación del Script Manual:**
    *   El script `sync_ds_cliente.php` se modificará para que use la lógica centralizada.

3.  **Nuevo Manejador de Webhooks:**
    *   Se creará el archivo `webhook_ds_handler.php` en `api/webhook_ds/`.
    *   Este será el **endpoint público** que Kiss Flow llamará para notificar cambios en tiempo real.

![Arquitectura Webhook](https://i.imgur.com/rG3h3zT.png)

---

### **Pasos de Implementación (Código)**

**Paso 1, 2 y 3: Creación y Refactorización de Scripts**

Estos pasos ya fueron completados. Se creó `sync_logic.php` con la lógica central, se refactorizó `sync_ds_cliente.php` para usarla, y se creó el manejador `webhook_ds_handler.php`.

---

### **Paso 4: Configurar el Webhook en Kiss Flow (Método Actualizado por Integraciones)**

Este es el paso final y se realiza completamente en la plataforma de Kiss Flow, siguiendo el método de "Integraciones".

1.  **Crear una Nueva Integración**
    *   En el panel principal de Kiss Flow, ve a la sección de **Integraciones**.
    *   Haz clic en **"Nueva Integración"** y asígnale un nombre descriptivo (ej. "Sincronización de Clientes a App").

2.  **Configurar el Disparador (El "Cuándo")**
    *   Dentro del editor de la integración, elige un **"Trigger"** (disparador).
    *   Busca y selecciona el conector **"Kissflow Dataset"**.
    *   Selecciona el evento que iniciará la automatización. Necesitarás **"When an existing row is updated"** (Cuando una fila es actualizada). Debes crear una segunda integración para el evento **"When a new row is created"** (Cuando se crea una nueva fila).
    *   Apunta este disparador a tu dataset: `DS_Documentos_Cliente`.

3.  **Configurar la Acción (El "Qué hacer")**
    *   Después del disparador, haz clic en el botón `+` para añadir una **"Acción"**.
    *   Busca el conector genérico **"HTTP"** y selecciona la acción **"Make an HTTP call (POST)"**.

4.  **Configurar la Autenticación de la Acción**
    *   Dentro de la configuración de la acción HTTP, verás una sección de autenticación que te pide "Choose an account".
    *   Crea una nueva cuenta. Se abrirá una ventana para verificar tu "HTTP account".
    *   **Connection label:** Dale un nombre (ej. `Auth Webhook CostaSol`).
    *   **HTTP authentication type:** Selecciona **"API key authentication"**.
    *   **Credentials:** Te aparecerán dos campos: `Key` y `Value`.
        *   En el campo **`Key`**: Escribe la palabra `Authorization`. (No es un ID, es el nombre del encabezado HTTP).
        *   En el campo **`Value`**: Escribe `Bearer ` seguido de tu secreto. Ejemplo: `Bearer TU_SECRETO_AQUI`.
    *   Guarda la cuenta de autenticación.

5.  **Completar la Configuración de la Petición POST**
    *   Después de guardar la autenticación, Kiss Flow te mostrará la pantalla final para configurar la petición.
    *   **URL:** Pega la URL de nuestro manejador:
        `https://app.costasol.com.ec/api/webhook_ds/webhook_ds_handler.php`
    *   **Body parameters:** Aquí es donde se envían los datos. Haz clic en "Select fields to allow mapping" y busca una variable del trigger que represente el **objeto completo del registro**. Mapea esa variable al cuerpo de la petición. Esto asegura que se envíe toda la información del registro que cambió.
    *   **HTTP headers:** Déjalo vacío. La autenticación ya se configuró en el paso anterior.
    *   **Query parameters:** Déjalo vacío.

6.  **Activar y Probar**
    *   Guarda y activa la integración.
    *   Para probar, modifica o crea un registro en el dataset `DS_Documentos_Cliente` en Kiss Flow.
    *   Revisa el archivo `sync_log.txt` en el servidor. Debería aparecer una nueva entrada confirmando la recepción del webhook.
