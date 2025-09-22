# Plan de Acción: Adaptación de Webhook para Proceso de Kiss Flow

## 1. Resumen de la Situación

Hemos identificado que la implementación actual del webhook (`webhook_handler.php`) está diseñada para recibir datos de un **Dataset** de Kiss Flow (`DS_Documentos_Cliente`). Sin embargo, el requisito real es que el webhook procese notificaciones de un **Proceso** (`Registro_Documentacio_n_de_Clientes`).

La estructura de datos (payload) que envía el Proceso es significativamente diferente a la del Dataset. Por lo tanto, el script `webhook_handler.php` debe ser modificado para poder interpretar y guardar los datos correctamente.

## 2. Plan de Modificación

El objetivo es actualizar `webhook_handler.php` para que sea totalmente compatible con el payload del Proceso.

**Pasos:**
1.  Analizar el nuevo payload (`put.txt`) y el esquema (`schema.txt`).
2.  Mapear los campos del nuevo payload a las columnas correspondientes en las tablas `usuario` y `propiedad` de la base de datos.
3.  Reescribir las secciones del script que extraen los datos para usar los nombres de campo correctos.
4.  Asegurar que la lógica de creación y actualización de usuarios y propiedades siga funcionando como se espera con la nueva estructura de datos.

## 3. Preguntas Clave para el Mapeo de Datos

Para poder completar la adaptación, necesito que me ayudes a localizar los siguientes campos en el payload del **Proceso**:

*   **[PREGUNTA 1] Correo Electrónico:** El campo `Comentario_Gerencia` que usábamos para el email ya no existe. ¿Qué campo del Proceso contiene el correo electrónico del cliente? como ahora no existe, el campop no lo elimines pero tenlo en mete ya que ese campo lo vamos a traer de otra database por medio de validaciones por ahora a todos ponle ( cedula o identificacion @placeholder.costasol.com.ec ) como ya lo haciamos con los correos que no hay 

*   **[PREGUNTA 2] Teléfono:** El campo `Tipo_de_Fachada` que se usaba para el teléfono ahora es una selección múltiple. ¿Cuál es el nuevo campo para el número de teléfono del cliente? dejalo null por defecto, ya que este campo tambien lo taeremos de otra data base

*   **[PREGUNTA 3] RUC (URL):** El campo `Ruc_Actualizado` no aparece en el nuevo payload. En el esquema veo un campo de tipo adjunto llamado `RUC_del_Cliente`. ¿Es este el campo correcto que debo usar para la URL del RUC?, este tambien dejalo en null

*   **[PREGUNTA 4] Fecha de Entrega:** El campo `fecha_entrega` no está en el payload de ejemplo. El esquema muestra un campo de fecha llamado `Fecha_de_Entrega_Contractual`. ¿Debo usar este? No, este tambien dejalo null ya que este campo tambien lo traeremos de otra base de datos 

*   **[CONFIRMACIÓN 5] ID del Registro:** El script actual usa `kissflow_ds_id` para encontrar propiedades existentes. El nuevo payload del Proceso también tiene un campo `_id`. Asumo que debo seguir usando este `_id` como identificador único para buscar/actualizar registros en nuestra base de datos. ¿Es correcta esta suposición? Es correcto

## 4. Mapeo de Campos Identificados (Proceso -> Base de Datos)

Estos son los campos que ya he podido mapear con la información disponible:

| Campo en Payload del Proceso | Campo en Base de Datos (`usuario`/`propiedad`) | Estado      |
| ---------------------------- | ---------------------------------------------- | ----------- |
| `Identificacion`             | `usuario.cedula`                               | ✅ Identificado |
| `Nombre_de_Cliente`          | `usuario.nombres`, `usuario.apellidos`         | ✅ Identificado |
| `Numero_de_Convenio`         | `usuario.kissflow_convenio`                    | ✅ Identificado |
| `_id`                        | `propiedad.kissflow_ds_id`                     | ✅ Identificado |
| `nometapa`                   | `propiedad.etapa_id` (via tabla `etapa_construccion`) | ✅ Identificado |
| `Modelo`                     | `propiedad.tipo_id` (via tabla `tipo_propiedad`) | ✅ Identificado |
| `Ubicacion`                  | `propiedad.manzana`, `propiedad.villa`         | ✅ Identificado |
| `Proyecto`                   | `propiedad.kissflow_proyecto`                  | ✅ Identificado |

---

Una vez que tengamos las respuestas a estas preguntas, podré proceder con la modificación del script `webhook_handler.php`.

## 5. Paso a Paso: Conectar Webhook en Kiss Flow (Método Actualizado)

Gracias a tu corrección, he investigado el método actualizado de Kiss Flow. La configuración ya no se hace directamente en el editor del proceso, sino a través de un módulo de **Integraciones** separado.

Aquí está la guía corregida:

**Paso 1: Crear una Nueva Integración**
1.  Inicia sesión en tu cuenta de Kiss Flow.
2.  En el menú de navegación de la izquierda, haz clic en el botón azul **`+ Create`**.
3.  En el submenú que aparece, selecciona **`Integration`**.
4.  Dale un nombre descriptivo a tu integración (p. ej., `Sincronizar Clientes con AppCostaSol`) y una descripción si lo deseas. Haz clic en **`Create`**.

**Paso 2: Configurar el Disparador (Trigger)**
El disparador le dice a la integración "cuándo" debe ejecutarse.
1.  Dentro del editor de integraciones, serás recibido por la pantalla para configurar un "Trigger".
2.  Busca y selecciona el conector de **Kissflow Process**.
3.  Configúralo de la siguiente manera:
    *   **Proceso:** Selecciona tu proceso: **`Registro_Documentacio_n_de_Clientes`**.
    *   **Evento Disparador (Trigger Event):** Aquí es donde eliges "cuándo" se enviarán los datos. Basado en la imagen que me mostraste, te recomiendo la siguiente configuración:

        **Para registrar NUEVOS clientes:**
        *   Usa el disparador: **`When an item is created`**. Esto enviará los datos a tu script tan pronto como se cree un nuevo registro de cliente.

        **Para registrar ACTUALIZACIONES de clientes:**
        *   Este es más complejo ya que no hay un evento de "actualización de datos" simple. El mejor disparador general es **`When an item exits this step`**. Este se activa cuando un registro avanza, retrocede, etc., lo cual usualmente ocurre después de que un usuario modifica los datos y completa una tarea.
        *   Es posible que necesites crear **dos integraciones separadas**: una para la creación y otra para la actualización, cada una con su propio disparador.

        **Recomendación:** Comienza configurando la integración solo con el disparador **`When an item is created`**. Una vez que verifiques que funciona, puedes crear una segunda integración (o añadir un segundo disparador si es posible) usando **`When an item exits this step`** para manejar las actualizaciones.

**Paso 3: Configurar la Acción (Action)**
La acción le dice a la integración "qué hacer" cuando el disparador se activa.
1.  Después de configurar el disparador, haz clic para añadir una acción.
2.  Busca y selecciona el conector llamado **`HTTP`**.
3.  Configúralo de la siguiente manera:
    *   **Action:** Elige la opción para hacer una llamada personalizada, como **`Make an HTTP call (Custom)`**.
    *   **URL:** Pega la URL de tu script:
        ```
        https://app.costasol.com.ec/api/webhook_rdc/webhook_handler.php
        ```
    *   **Method:** Selecciona **`POST`**.
    *   **Headers:** Añade una cabecera para indicar que el contenido es JSON.
        *   Key: `Content-Type`
        *   Value: `application/json`
    *   **Body Type / Payload:** Selecciona la opción **`JSON (application/json)`**.

        ***Nota SÚPER IMPORTANTE: ¡La forma de construir el cuerpo del JSON es crucial!***

        Las variables dinámicas de Kiss Flow (las "píldoras") **DEBEN IR DENTRO DE LAS COMILLAS DOBLES** para que el JSON sea válido.

        **Ejemplo CORRECTO:**
        `"Nombre_de_Cliente": "` [Píldora de Nombre_de_Cliente] `"`

        **Ejemplo INCORRECTO:**
        `"Nombre_de_Cliente":` [Píldora de Nombre_de_Cliente]

        Usa el siguiente texto como plantilla, asegurándote de reemplazar cada `"{...}"` con la píldora dinámica correcta desde el menú "Insert field":

        ```json
        {
          "_id": "{1._id}",
          "Identificacion": "{1. Identificacion}",
          "Nombre_de_Cliente": "{1. Nombre_de_Cliente}",
          "Numero_de_Convenio": "{1. Numero_de_Convenio}",
          "nometapa": "{1. nometapa}",
          "Modelo": "{1. Modelo}",
          "Ubicacion": "{1. Ubicacion}",
          "Proyecto": "{1. Proyecto}"
        }
        ```

**Paso 4: Probar y Activar la Integración**
1.  Prueba la acción HTTP para asegurarte de que puede enviar datos a tu URL. Como vimos, es normal que el test automático falle por falta de datos de prueba. Lo importante es la prueba final.
2.  Una vez que todo esté configurado, activa la integración usando el interruptor en la esquina superior derecha.

**Paso 5: Realizar la Prueba Final (End-to-End)**
1.  Ve al proceso `Registro_Documentacio_n_de_Clientes` en Kiss Flow y crea o actualiza un registro con datos reales.
2.  Verifica en tu base de datos y en el archivo de log (`api/webhook_rdc/sync_log.txt`) que el cambio se haya registrado correctamente a través de la integración.