# Implementación de Observaciones para CTG y PQR

## Descripción
Se ha implementado una nueva funcionalidad que permite a los responsables asignados a un CTG (Chat de Gestión) o PQR (Peticiones, Quejas y Recomendaciones) agregar y gestionar observaciones personalizadas sobre los clientes. Esta funcionalidad está diseñada para ser visible únicamente para los responsables, manteniendo la privacidad de la información.

## Características Implementadas

### 🔐 Seguridad y Permisos
- **Acceso restringido**: Solo los responsables asignados pueden ver y editar las observaciones
- **Validación de permisos**: Verificación doble de autenticación y autorización
- **Prevención de SQL Injection**: Uso de consultas preparadas
- **Validación de datos**: Límite de 700 caracteres según la estructura de la BD

### 📝 Funcionalidades del Frontend
- **Textarea responsivo**: Interfaz moderna y fácil de usar
- **Auto-guardado**: Las observaciones se guardan automáticamente al perder el foco
- **Guardado manual**: Botón para guardar explícitamente
- **Notificaciones**: Feedback visual del estado de las operaciones
- **Diseño responsivo**: Adaptado para dispositivos móviles

### 🔄 APIs Implementadas

#### Para CTG:
1. **GET** `/api/ctg/ctg_observaciones.php?ctg_id=12`
2. **POST** `/api/ctg/ctg_update_observaciones.php`

#### Para PQR:
1. **GET** `/api/pqr/pqr_observaciones.php?pqr_id=12`
2. **POST** `/api/pqr/pqr_update_observaciones.php`

### Detalles de las APIs

#### GET Observaciones (CTG/PQR)
**Propósito**: Obtener las observaciones actuales de un CTG/PQR específico

**Parámetros**:
- `ctg_id` o `pqr_id` (GET): ID del CTG/PQR

**Headers requeridos**:
- `Authorization: Bearer <token>`

**Respuesta exitosa**:
```json
{
  "ok": true,
  "observaciones": "Texto de las observaciones"
}
```

**Códigos de error**:
- `401`: No autenticado o token inválido
- `403`: No es responsable o no está asignado al CTG/PQR
- `400`: ctg_id/pqr_id requerido

#### POST Actualizar Observaciones (CTG/PQR)
**Propósito**: Actualizar las observaciones de un CTG/PQR específico

**Parámetros POST**:
- `ctg_id` o `pqr_id`: ID del CTG/PQR
- `observaciones`: Texto de las observaciones (máximo 700 caracteres)

**Headers requeridos**:
- `Authorization: Bearer <token>`

**Respuesta exitosa**:
```json
{
  "ok": true,
  "mensaje": "Observaciones actualizadas correctamente"
}
```

**Códigos de error**:
- `401`: No autenticado o token inválido
- `403`: No es responsable o no está asignado al CTG/PQR
- `400`: ctg_id/pqr_id requerido o observaciones muy largas
- `405`: Método no permitido

## Archivos Modificados/Creados

### Nuevos Archivos
1. **`api/ctg/ctg_observaciones.php`** - API para obtener observaciones de CTG
2. **`api/ctg/ctg_update_observaciones.php`** - API para actualizar observaciones de CTG
3. **`api/pqr/pqr_observaciones.php`** - API para obtener observaciones de PQR
4. **`api/pqr/pqr_update_observaciones.php`** - API para actualizar observaciones de PQR
5. **`test_observaciones.html`** - Archivo de prueba para las APIs de CTG
6. **`test_observaciones_pqr.html`** - Archivo de prueba para las APIs de PQR
7. **`README_OBSERVACIONES.md`** - Esta documentación

### Archivos Modificados
1. **`Front/ctg/ctg_detalle.php`** - Agregado HTML y JavaScript para el textarea de CTG
2. **`Front/assets/css/style_ctg_detalle.css`** - Agregados estilos para el área de observaciones de CTG
3. **`Front/pqr/pqr_detalle.php`** - Agregado HTML y JavaScript para el textarea de PQR
4. **`Front/assets/css/style_pqr_detalle.css`** - Agregados estilos para el área de observaciones de PQR

## Estructura de la Base de Datos

La funcionalidad utiliza el campo `observaciones` existente en las tablas `ctg` y `pqr`:

```sql
CREATE TABLE `ctg` (
  -- ... otros campos ...
  `observaciones` varchar(700) DEFAULT NULL
  -- ... otros campos ...
);

CREATE TABLE `pqr` (
  -- ... otros campos ...
  `observaciones` varchar(700) DEFAULT NULL
  -- ... otros campos ...
);
```

## Flujo de Funcionamiento

### Para Responsables:
1. **Acceso**: Al abrir un CTG/PQR asignado, se muestra automáticamente el área de observaciones
2. **Carga**: Se cargan automáticamente las observaciones existentes (si las hay)
3. **Edición**: El responsable puede escribir en el textarea
4. **Guardado**: 
   - Automático al perder el foco (con 1 segundo de delay)
   - Manual al hacer clic en "Guardar observaciones"
5. **Feedback**: Notificaciones visuales del estado de la operación

### Para Clientes:
- **No ven el área de observaciones**: La funcionalidad está completamente oculta
- **No afecta su experiencia**: El resto del chat funciona normalmente

## Características Técnicas

### Seguridad
- ✅ Validación de autenticación mediante token
- ✅ Verificación de rol (solo responsables)
- ✅ Verificación de asignación al CTG/PQR específico
- ✅ Prevención de SQL Injection
- ✅ Validación de longitud de datos

### UX/UI
- ✅ Diseño consistente con el resto de la aplicación
- ✅ Responsive design para móviles
- ✅ Feedback visual inmediato
- ✅ Auto-guardado inteligente
- ✅ Manejo de errores amigable

### Rendimiento
- ✅ Carga asíncrona de observaciones
- ✅ Actualización eficiente (solo cuando hay cambios)
- ✅ Manejo de errores de red
- ✅ Timeouts apropiados

## Instrucciones de Uso

### Para Desarrolladores:
1. **Pruebas CTG**: Usar `test_observaciones.html` para probar las APIs de CTG
2. **Pruebas PQR**: Usar `test_observaciones_pqr.html` para probar las APIs de PQR
3. **Tokens**: Reemplazar el token de ejemplo con un token real de responsable
4. **IDs**: Usar IDs de CTGs/PQRs existentes que tengan responsables asignados

### Para Responsables:
1. **Acceso**: Iniciar sesión como responsable
2. **Navegación**: Ir a un CTG o PQR asignado
3. **Observaciones**: El área aparecerá automáticamente debajo del chat
4. **Edición**: Escribir las observaciones en el textarea
5. **Guardado**: Las observaciones se guardan automáticamente o manualmente

## Consideraciones de Mantenimiento

### Monitoreo
- Revisar logs de error en las APIs
- Monitorear el uso del campo `observaciones` en las BD
- Verificar que los permisos funcionen correctamente

### Escalabilidad
- El campo `observaciones` tiene límite de 700 caracteres
- Las APIs están optimizadas para consultas individuales
- El frontend maneja eficientemente las actualizaciones

### Compatibilidad
- ✅ Compatible con la estructura existente de la BD
- ✅ No afecta otras funcionalidades del sistema
- ✅ Mantiene la compatibilidad con dispositivos móviles

## Troubleshooting

### Problemas Comunes:
1. **No aparece el área de observaciones**:
   - Verificar que el usuario sea responsable
   - Verificar que esté asignado al CTG/PQR
   - Revisar la consola del navegador

2. **Error al guardar observaciones**:
   - Verificar la conexión a internet
   - Verificar que el token sea válido
   - Revisar que no exceda 700 caracteres

3. **Error 403 (Forbidden)**:
   - Verificar que el responsable esté asignado al CTG/PQR
   - Verificar que el token sea de un responsable válido

### Logs a Revisar:
- `error_log` de PHP para errores de las APIs
- Consola del navegador para errores de JavaScript
- Logs de autenticación para problemas de tokens

## Diferencias entre CTG y PQR

### Estructura de Base de Datos
- **CTG**: Tabla `ctg` con campo `observaciones`
- **PQR**: Tabla `pqr` con campo `observaciones`

### APIs
- **CTG**: `/api/ctg/ctg_observaciones.php` y `/api/ctg/ctg_update_observaciones.php`
- **PQR**: `/api/pqr/pqr_observaciones.php` y `/api/pqr/pqr_update_observaciones.php`

### Frontend
- **CTG**: `Front/ctg/ctg_detalle.php` con estilos en `style_ctg_detalle.css`
- **PQR**: `Front/pqr/pqr_detalle.php` con estilos en `style_pqr_detalle.css`

### Funcionalidad
- **Misma lógica**: Ambas implementaciones siguen el mismo patrón
- **Mismos permisos**: Solo responsables asignados pueden ver/editar
- **Misma UX**: Interfaz idéntica para ambas funcionalidades
