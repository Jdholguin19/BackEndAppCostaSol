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

## 5. Paso a Paso: Conectar Webhook en Kiss Flow (Disparador de Creación)

Aquí está la guía para configurar la integración que se activa al **crear** un nuevo cliente.

**Paso 1: Crear una Nueva Integración**
1.  En Kiss Flow, ve a `+ Create` > `Integration`.
2.  Dale un nombre (p. ej., `Crear Cliente en AppCostaSol`).

**Paso 2: Configurar el Disparador (Trigger)**
1.  Conector: **Kissflow Process**.
2.  Proceso: **`Registro_Documentacio_n_de_Clientes`**.
3.  Evento Disparador: **`When a draft item is submitted`**.

**Paso 3: Configurar la Acción (Action)**
1.  Añade una acción **`HTTP`**.
2.  Configúrala con la URL de tu script, el método `POST`, y el cuerpo `JSON` con las variables dinámicas, asegurándote de que las variables queden **dentro de comillas dobles**.

---

## 6. Paso a Paso: Conectar Webhook en Kiss Flow (Disparador de Actualización)

Aquí se explica cómo configurar la integración para que se active al **actualizar** un cliente.

**Paso 1: Crear una Segunda Integración**
1.  Ve a `+ Create` > `Integration` de nuevo.
2.  Dale un nombre claro, por ejemplo: `Actualizar Cliente en AppCostaSol`.

**Paso 2: Configurar el Disparador de Actualización**
1.  Conector: **Kissflow Process**.
2.  Proceso: **`Registro_Documentacio_n_de_Clientes`**.
3.  **Evento Disparador (Trigger Event):** Para registrar **ACTUALIZACIONES**, usa el disparador: **`When an item exits this step`** o **`When an item advances to next step`**.

---


