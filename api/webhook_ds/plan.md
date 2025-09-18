# Plan de Sincronización Unidireccional: Kiss Flow -> App

---

### **Objetivo**

Crear un script PHP que sincronice los datos desde el dataset `DS_Documentos_Cliente` de Kiss Flow hacia las tablas `usuario` y `propiedad` de la base de datos local de la aplicación. La sincronización será **unidireccional**: Kiss Flow es la fuente de verdad y los cambios fluyen hacia la base de datos de la app, no al revés.

---

### **Arquitectura Propuesta**

1.  **Script Central de Sincronización:**
    *   Se creará un único archivo PHP, `sync_ds_cliente.php`, que contendrá toda la lógica.
    *   **Ubicación:** `api/webhook_ds/sync_ds_cliente.php`.

2.  **Ejecución:**
    *   El script se diseñará para ser invocado de forma manual (a través de un navegador o terminal) o mediante una tarea programada (`cron job`) para ejecuciones periódicas (ej. cada noche).

---

### **Flujo de Ejecución del Script**

El script seguirá los siguientes pasos:

1.  **Obtener Datos de Kiss Flow:**
    *   Realizará llamadas a la API de Kiss Flow para obtener **todos** los registros del dataset `DS_Documentos_Cliente`. Se implementará un bucle para manejar la paginación de la API y asegurar que se recuperen todos los datos.

2.  **Procesar Registros en Lote:**
    *   El script iterará sobre cada registro (cada cliente/propiedad) obtenido de Kiss Flow.

3.  **Mapeo y Búsqueda Local:**
    *   Por cada registro de Kiss Flow, extraerá el campo `Identificacion` (cédula).
    *   Buscará en la tabla `usuario` de la base de datos local si ya existe un usuario con esa `cedula`.

4.  **Lógica de Creación o Actualización (UPSERT):**
    *   **Si el usuario NO existe:**
        1.  `INSERT`: Creará un nuevo registro en la tabla `usuario`.
        2.  `INSERT`: Creará un nuevo registro en la tabla `propiedad`, asociándolo al `id` del usuario recién creado.
    *   **Si el usuario SÍ existe:**
        1.  `UPDATE`: Actualizará la fila existente en la tabla `usuario` con los datos más recientes de Kiss Flow.
        2.  `UPDATE`: Actualizará la fila correspondiente en la tabla `propiedad`.

5.  **Integridad y Reporte:**
    *   Cada operación de creación/actualización por registro se envolverá en una **transacción de base de datos** para garantizar que los datos se guarden de forma atómica. Si algo falla, se revierte el cambio para ese registro.
    *   Al finalizar, el script mostrará un resumen simple: "Procesados X registros. Y usuarios creados. Z usuarios actualizados."

---

### **Preguntas Clave a Resolver**

Para poder construir este script, necesito que me ayudes a resolver las siguientes dudas:

**1. Disparador de la Sincronización:**
   *   **Pregunta:** ¿Cómo se ejecutará este script?
   *   **Respuesta:** La meta final es usar **Webhooks de Kiss Flow** para eventos de creación y actualización. Sin embargo, el **primer paso** será un script de ejecución **manual** para poblar la base de datos masivamente. También se necesitará un script SQL para preparar las tablas.

**2. Mapeo y Conversión de Datos:**
   *   **Pregunta:** ¿Cómo se debe dividir el campo `Mz_Solar` (ej: "Mz A / Villa B")?
   *   **Respuesta:** Se debe separar en `manzana` y `villa`. Se puede asumir el formato "Mz [manzana] / Villa [villa]".
   *   **Pregunta:** Si los textos de `Etapa` y `Modelo` de Kiss Flow no existen en las tablas locales, ¿qué se debe hacer?
   *   **Respuesta:** Se deberá **crear el nuevo registro** en las tablas maestras locales (`etapa_construccion`, `tipo_propiedad`).

**3. Manejo de Datos Vacíos:**
   *   **Pregunta:** Si un campo en Kiss Flow es nulo, ¿se actualiza en la base de datos local?
   *   **Respuesta:** No. Se debe **ignorar la actualización** para ese campo específico, manteniendo el valor que ya existe en la base de datos local.

**4. Creación de Nuevos Usuarios:**
   *   **Pregunta:** ¿Qué valores por defecto usar para `rol_id` y `contrasena_hash`?
   *   **Respuesta:**
     *   `rol_id`: Asignar siempre el rol `1` (Cliente).
     *   `contrasena_hash`: Usar un hash de la contraseña `1234` para todos los usuarios nuevos.

**5. Manejo de Registros Eliminados en Kiss Flow:**
   *   **Pregunta:** Si un registro se elimina en Kiss Flow, ¿qué pasa en la base de datos local?
   *   **Respuesta:** **No hacer nada.** El registro permanecerá en la base de datos local.

**6. Almacenamiento de ID de Kiss Flow:**
   *   **Pregunta:** ¿Añadimos una columna `kissflow_ds_id` para guardar el ID único de Kiss Flow?
   *   **Respuesta:** Sí, de acuerdo. Se sugiere añadirla a la tabla `propiedad`, ya que cada registro en el dataset representa una propiedad única.

---

### **7. Preguntas Adicionales**

**Por favor, ayúdame con estas últimas dudas para finalizar el plan:**

**7.1. Lógica de Actualización (UPDATE):**
   *   Cuando se encuentra un usuario existente por su cédula, ¿qué campos específicos debemos actualizar en las tablas `usuario` y `propiedad`? ¿Actualizamos **todos** los campos que vienen de Kiss Flow (Nombre, Correo, Teléfono, Etapa, Modelo, etc.) o solo algunos? Es importante para no sobrescribir datos que hayan sido modificados localmente.
    Campos que deberan traer de kiss flow hacia la tabla usuario:
      "Nombre_Cliente",
      "Identificacion",
      "Fecha",
      "RUC", (este guarda un pdf nose si puedas traer el link del pdf)
      "Rev_Gerencia"
      "Convenio"
      "Comentario_Gerencia" (este es correo solo que con otro nombre)
      "Tipo_de_Fachada" (este es el telefono solo que su ID es ese pero su name es Telefono)

    Campos que deberan traer de kiss flow hacia la tabla propiedad:
      "Etapa",
      "Mz_Solar",
      "Modelo",
      "Proyecto"
      "Fecha_de_Entrega" (de momento la mayoria no la tiene asi que solo dejala en blanco )

    Con respecto a algunos campos que estan en la tabla propiedad en la base de datos local que no estan en kiss flow por ejemplo
    `fecha_compra` date DEFAULT NULL,
    `fecha_hipotecario` date DEFAULT NULL, estos dos campos los puedes dejar en null no tienen mucha relevancia
    
    Con respecto a la tabla usuarios solo le vas a añdir las nuevas columnas que hagan falta las que ya estan las dejas tal cual, ya que eso funciona con un sistema propio del a aplicacion


**7.2. Múltiples Propiedades por Usuario:**
   *   Entiendo que si un cliente (una misma cédula) tiene 3 propiedades, existirán 3 registros en el dataset de Kiss Flow. El script entonces creará/actualizará 1 `usuario` y 3 `propiedades` asociadas en la base de datos local. La distinción entre estas propiedades se hará usando el `kissflow_ds_id` que guardaremos. ¿Es correcto este entendimiento? Si

**7.3. Clave Primaria del Dataset (`Name`/`Key`):**
   *   El dataset de Kiss Flow tiene un campo `Name` (con etiqueta "Key") que parece ser un ID legible (ej. "1000000"). ¿Tiene este número algún valor de negocio importante que debamos mapear a algún campo en la base de datos local, o podemos ignorarlo y usar solo el `_id` interno de Kiss Flow (que guardaremos en `kissflow_ds_id`)? ignaralo y solo usa el id interno de kiss flow

