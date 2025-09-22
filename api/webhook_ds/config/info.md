# Documentación del Script de Sincronización con Kiss Flow

Este documento detalla el funcionamiento del script `sync_ds_cliente.php` y proporciona una guía sobre cómo adaptarlo para sincronizar otros datasets de Kiss Flow.

---

## 1. Funcionamiento de `sync_ds_cliente.php`

### Propósito General

El script realiza una sincronización masiva y unidireccional desde el dataset `DS_Documentos_Cliente` de Kiss Flow hacia la base de datos local de la aplicación. Su objetivo principal es mantener actualizada la información de los clientes (`tabla usuario`) y sus propiedades (`tabla propiedad`).

### Flujo de Ejecución

1.  **Inicio y Configuración:** El script se prepara para una ejecución potencialmente larga, aumentando los límites de tiempo de ejecución y de memoria de PHP.
2.  **Conexión:** Carga las credenciales necesarias para conectarse tanto a la base de datos local como a la API de Kiss Flow.
3.  **Paginación de API:** Se conecta a la API de Kiss Flow para obtener todos los registros del dataset. Para no sobrecargar la API, los registros se obtienen en lotes (páginas de 50).
4.  **Procesamiento por Registro:** El script itera sobre cada registro individual obtenido de Kiss Flow.
5.  **Transacciones Atómicas:** Cada registro se procesa dentro de una **transacción** de base de datos. Esto garantiza la integridad de los datos: o todos los cambios para un registro (usuario y propiedad) se guardan correctamente, o no se guarda ninguno si ocurre un error en el proceso.

### Lógica de Sincronización de Usuarios

1.  **Clave Principal:** Utiliza el campo `Identificacion` (cédula) de Kiss Flow como la clave única para buscar, crear o actualizar usuarios en la tabla `usuario`.
2.  **Limpieza de Datos (Data Cleaning):**
    *   **Cédula:** Antes de usarla, se eliminan todos los caracteres que no sean numéricos (ej. guiones, espacios). Esto asegura que cédulas como `091234567-8` se procesen correctamente como `0912345678`.
    *   **Teléfono:** Se limpia el número de teléfono para estandarizarlo. Se eliminan caracteres no numéricos y, si el número empieza con el prefijo de país `+593`, se reemplaza por un `0` para formar un número local.
3.  **Creación de Usuario:** Si la cédula del registro de Kiss Flow no existe en la tabla `usuario`, el script:
    *   Crea un nuevo usuario con `rol_id = 1` (Cliente).
    *   Asigna una contraseña por defecto (`1234`).
    *   Mapea los campos de Kiss Flow (`Nombre_Cliente`, `Comentario_Gerencia` para el correo, etc.) a las columnas de la tabla `usuario`.
    *   Si el correo ya existe, asigna uno temporal (`cedula@placeholder.costasol.com.ec`) para evitar conflictos.
4.  **Actualización de Usuario:** Si la cédula ya existe, el script compara los datos de Kiss Flow con los existentes y actualiza los campos que sean diferentes. Si el correo a actualizar ya pertenece a otro usuario, la actualización de ese campo se omite y se registra una advertencia en el log.

### Lógica de Sincronización de Propiedades

1.  **Clave Principal:** Utiliza el campo `_id` del registro de Kiss Flow como un identificador único (`kissflow_ds_id`) para vincularlo a una entrada en la tabla `propiedad`.
2.  **Creación/Actualización:** El script busca si ya existe una propiedad con ese `kissflow_ds_id`. Si no existe, la crea; si ya existe, la actualiza.
3.  **Tablas Maestras (Catálogos):** Para campos como `Etapa` o `Modelo`, el script utiliza una función (`get_or_create_master_id`) que busca el ID correspondiente en tablas catálogo (ej. `etapa_construccion`). Si el valor de Kiss Flow no existe en el catálogo, lo crea y usa el nuevo ID.
4.  **Parseo de `Mz_Solar`:** El script es capaz de interpretar dos formatos para separar la manzana y la villa:
    *   Formato original: `Mz A / Villa B`
    *   Formato con guion: `7146-01` o `TORRE D-3A`

### Manejo de Errores y Logs

1.  **Archivo de Log (`sync_log.txt`):** Toda la salida del script (mensajes informativos, advertencias y errores) se guarda en el archivo `sync_log.txt`, ubicado en el mismo directorio. Este archivo se sobreescribe en cada nueva ejecución.
2.  **Errores Fatales:** Un error de base de datos durante la transacción para un registro provoca un `rollback` (revierte los cambios para ese registro) y el error detallado se guarda en el log.
3.  **Registros Omitidos:** Si un registro de Kiss Flow llega sin una cédula válida, se omite por completo y se contabiliza en el reporte final como un "error".
4.  **Advertencias:** Conflictos de datos que no detienen el proceso (como correos duplicados o formatos de `Mz_Solar` no reconocidos) se registran como `ADVERTENCIA` para revisión manual.

---

## 2. Cómo Adaptar el Script para un Nuevo Dataset

Este script puede ser utilizado como una plantilla robusta para crear nuevas sincronizaciones con otros datasets de Kiss Flow. Los pasos generales son:

1.  **Copiar el Archivo:** No modifiques el original. Haz una copia de `sync_ds_cliente.php` y renómbrala a algo descriptivo, por ejemplo, `sync_mi_nuevo_ds.php`.

2.  **Cambiar el ID del Dataset:** Dentro del nuevo archivo, busca esta línea y reemplaza el ID del dataset por el que quieres usar:
    ```php
    $dataset_id = 'DS_Documentos_Cliente';
    ```

3.  **Ajustar el Mapeo de Campos (El paso más importante):**
    Esta es la parte crucial. Debes ir al bucle `foreach ($records as $record)` y reemplazar los nombres de los campos de Kiss Flow por los que correspondan a tu nuevo dataset. 

    *   **Ejemplo:** Si tu nuevo dataset no usa `Identificacion` sino `Cedula_Cliente`, debes cambiar todas las instancias de `$record['Identificacion']` por `$record['Cedula_Cliente']`.
    *   Revisa **toda** la sección de creación (`$new_user_data`) y actualización (`$update_fields`) para asegurarte de que todos los campos (`$record['...']`) coinciden con los de tu nuevo dataset.

4.  **Ajustar la Lógica de Base de Datos:**
    *   ¿Los datos se guardarán en las mismas tablas (`usuario`, `propiedad`) o en otras? Si son otras tablas, debes cambiar los nombres en las sentencias `INSERT` y `UPDATE`.
    *   Asegúrate de que las columnas a las que intentas insertar datos existan en tus tablas.

5.  **Revisar las Funciones de Limpieza:**
    *   ¿Tu nuevo dataset tiene campos con formatos especiales como teléfonos o cédulas? Si es así, puedes reutilizar las funciones `clean_phone_number()` o la limpieza con `preg_replace()`.
    *   Si tienes un campo con un formato nuevo y complejo (como `Mz_Solar`), necesitarás modificar o crear una nueva función de "parseo" para ese campo.

6.  **Probar en un Entorno Seguro:**
    **Nunca ejecutes un script modificado directamente en producción.** Pruébalo siempre en un entorno de desarrollo local, conectado a una base de datos de prueba, para verificar que el mapeo de datos es correcto y que no causa errores inesperados.
