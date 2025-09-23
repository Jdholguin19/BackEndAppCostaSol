# Estado y Plan de Sincronización (Contexto para Continuar)

## 1. Resumen de Contexto

Esta es una "partida guardada" de nuestra sesión.

**Objetivo Inicial (Completado):**
- Se configuró una sincronización desde un **Proceso** de Kiss Flow (`Registro_Documentacio_n_de_Clientes`) hacia la base de datos local.
- Se creó un script `webhook_handler.php` para recibir los datos.
- Se resolvieron varios desafíos:
    - Se corrigieron los nombres de los campos del payload.
    - Se identificó el disparador correcto para la creación (`When a draft item is submitted`) y para la actualización (`When an item exits this step`).
    - Se superó el bloqueo del botón "Test action" de Kiss Flow aplicando y luego revirtiendo parches temporales en el script.
- **Resultado:** La sincronización desde Kiss Flow hacia la base de datos local está **100% funcional**.

**Nuevo Objetivo (En Progreso):**
- El usuario solicitó una nueva funcionalidad: una sincronización en la dirección opuesta, desde la **base de datos local hacia un Dataset de Kiss Flow** (`DS_Documentos_Cliente`).
- El propósito es rellenar campos que estén vacíos en el Dataset con datos que existen en la base de datos local (ej. `fecha_entrega`).

**Estado Actual:**
- Se ha definido un plan de ejecución final para este nuevo objetivo.
- El **Paso 1** de este plan es que el usuario modifique su base de datos.
- Se ha proporcionado el script SQL necesario para esta modificación.
- **La tarea actual está en espera de que el usuario ejecute el script SQL.**

---

## 2. Instrucciones para Mañana (Para el Usuario)

Hola. Cuando estés listo para continuar, tu primera y única tarea es ejecutar el script SQL que se encuentra más abajo en tu base de datos de pruebas.

Una vez que lo hayas hecho, simplemente avísame con un "listo" o "ya ejecuté el script" para que yo pueda proceder con el siguiente paso del plan (investigar la API de Kiss Flow).

---

## 3. Plan de Ejecución Final (Para la Nueva Sincronización)

### Paso 1: Modificar la Base de Datos (Tu Tarea Pendiente)

Ejecuta el siguiente script SQL en tu base de datos para añadir la columna `last_modified`. Esto es esencial para que podamos detectar qué registros se han modificado recientemente.

```sql
-- Script para añadir la columna de seguimiento de modificaciones
-- a las tablas de usuario y propiedad.

-- Añadir columna a la tabla 'usuario'
ALTER TABLE `usuario`
ADD COLUMN `last_modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Añadir columna a la tabla 'propiedad'
ALTER TABLE `propiedad`
ADD COLUMN `last_modified` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
```

### Paso 2: Investigar la API de Kiss Flow (Mi Tarea)

Investigaré y confirmaré el endpoint exacto y el formato del payload para actualizar un registro en un Dataset de Kiss Flow.

### Paso 3: Escribir el Script de Sincronización (Mi Tarea)

Crearé el nuevo script `sync_db_to_kf.php` que buscará registros modificados localmente y los enviará a Kiss Flow.

### Paso 4: Configurar el Cron Job (Tu Tarea)

Te proporcionaré las instrucciones para configurar un cron job en tu servidor que ejecute el script automáticamente cada 5 minutos.

---

## 4. Mapeo de Campos Confirmado (Contexto)

Esta es la correspondencia de campos que usaremos para la nueva sincronización hacia el **Dataset**.

| Campo en Dataset `DS_Documentos_Cliente` | Tabla Local | Columna Local |
| ---------------------------------------- | ----------- | ------------- |
| `Identificacion`                         | `usuario`   | `cedula`      |
| `Nombre_Cliente`                         | `usuario`   | `nombres` + `apellidos` |
| `Comentario_Gerencia` (Email)            | `usuario`   | `correo`      |
| `Tipo_de_Fachada` (Teléfono)             | `usuario`   | `telefono`    |
| `Convenio`                               | `usuario`   | `kissflow_convenio` |
| `Etapa`                                  | `propiedad` | `etapa_id` (nombre) |
| `Modelo`                                 | `propiedad` | `tipo_id` (nombre) |
| `Mz_Solar`                               | `propiedad` | `manzana` + `villa` |
| `Proyecto`                               | `propiedad` | `kissflow_proyecto` |
| `Fecha_de_Entrega`                       | `propiedad` | `fecha_entrega` |