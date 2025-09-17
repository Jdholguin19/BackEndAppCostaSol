# Plan de Migración de CTG a Kiss Flow

Este documento detalla los pasos para reestructurar el módulo de CTG para que utilice las categorías del proceso "Warranty Claim" de Kiss Flow como la fuente de verdad.

---

### **Paso 1: Actualizar la Base de Datos**

**Objetivo:** Reemplazar las categorías de CTG locales con las 7 categorías estándar de Kiss Flow, manteniendo la integridad del módulo de Garantías.

**Script SQL Ejecutado:**
```sql
-- (Opcional pero recomendado) Hacer una copia de seguridad de la tabla
CREATE TABLE tipo_ctg_backup AS SELECT * FROM tipo_ctg;

-- Vaciar la tabla actual de tipos de CTG
TRUNCATE TABLE tipo_ctg;

-- Insertar las 7 nuevas contingencias de Kiss Flow
INSERT INTO `tipo_ctg` (`nombre`, `tiempo_garantia_min`, `tiempo_garantia_max`) VALUES
('FILTRACION DE AGUA POR CUBIERTA', '0.3', '1'),
('FILTRACION DE AGUA POR VENTANA(SILICON)', '0.3', '1'),
('CABLEADO DE PUNTOS ELÉCTRICOS / ACOMETIDA PRINCIPAL', '0.3', '1'),
('CORTOCIRCUITO', '0.3', '1'),
('FUGA DE AGUA EN ACCESORIOS, GRIFERÍAS Y/O PIEZAS SANITARIAS', '0.3', '1'),
('FUGA INCONTENIBLE DE AGUA', '0.3', '1'),
('OTROS', '0.3', '1');
```

---

### **Paso 2: Adaptar el Código de la API**

**Objetivo:** Modificar los scripts de la API para que funcionen con la nueva estructura de datos, eliminando la dependencia de los subtipos.

**2.1. Modificar `api/ctg/ctg_create.php`**
*   **Validación:** Se eliminará el requisito de `subtipo_id` al crear un nuevo CTG.
*   **Inserción en BD:** La consulta `INSERT` a la tabla `ctg` guardará `NULL` en la columna `subtipo_id`.
*   **Llamada al Handler:** Se añadirá una consulta para obtener el `nombre` de la contingencia desde la tabla `tipo_ctg` (usando el `tipo_id` recibido) y se pasará este nombre al script `ctg_handler.php`.

**2.2. Modificar `api/ctg/kissflow_ctg/ctg_handler.php`**
*   **Recepción de Datos:** El script recibirá el `contingencia_nombre` desde `ctg_create.php`.
*   **Construcción del Payload:** Usará este nombre para rellenar el campo `Contingencia` en el JSON que se envía a la API de Kiss Flow, asegurando que coincida con las categorías esperadas.

**2.3. (Futuro) Modificar el Frontend**
*   La aplicación móvil o web deberá ser actualizada para ya no mostrar un selector de subtipos.
*   El selector de "Tipo" deberá poblarse desde un endpoint que lea la tabla `tipo_ctg` actualizada (ej. `api/ctg/tipo_ctg.php`).

---
