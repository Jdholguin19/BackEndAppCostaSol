# Sistema de Auditoría - Documentación Completa

## 📋 Resumen del Sistema

El Sistema de Auditoría de CostaSol es una herramienta integral que registra, monitorea y analiza todas las actividades críticas de usuarios y responsables dentro de la aplicación. Proporciona trazabilidad completa, seguridad y cumplimiento normativo.

## 🏗️ Arquitectura del Sistema

### Componentes Principales

1. **Base de Datos**: Tabla `audit_log` para almacenar registros
2. **API Backend**: Endpoints para consulta y gestión de datos
3. **Dashboard Frontend**: Interfaz web para visualización y análisis
4. **Helper Functions**: Funciones auxiliares para registro automático

### Estructura de Archivos

```
auditoria/
├── dashboard.php                    # Dashboard principal
├── assets/css/style_audit_dashboard.css  # Estilos CSS
├── api/
│   └── audit_dashboard_data.php    # API endpoint
├── audit.md                         # Documentación técnica
└── preguntas_aut.md                 # Esta documentación
```

## 🗄️ Estructura de la Base de Datos

### Tabla `audit_log`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT PRIMARY KEY | Identificador único |
| `user_id` | INT | ID del usuario/responsable |
| `user_type` | ENUM | Tipo: 'usuario', 'responsable', 'sistema' |
| `ip_address` | VARCHAR(45) | Dirección IP del usuario |
| `action` | VARCHAR(100) | Acción realizada |
| `target_resource` | VARCHAR(50) | Recurso objetivo |
| `target_id` | INT | ID del recurso objetivo |
| `details` | JSON | Detalles adicionales |
| `timestamp` | TIMESTAMP | Fecha y hora del evento |

## 🔧 Funcionalidades Implementadas

### 1. Dashboard Principal (`dashboard.php`)

#### Características:
- **Autenticación**: Solo responsables pueden acceder
- **Vista de Módulos**: Grid con tarjetas de cada módulo auditado
- **Auditorías Recientes**: Tabla con las 10 últimas actividades
- **Vista Detallada**: Análisis profundo por módulo seleccionado
- **Filtros Avanzados**: Múltiples criterios de búsqueda
- **Gráfico de Pastel**: Visualización de distribución de acciones
- **Diseño Responsive**: Adaptable a dispositivos móviles

#### Módulos Auditados:
- **Autenticación**: Login, logout, intentos fallidos
- **Usuarios**: CRUD de usuarios
- **Citas**: Gestión de citas y calendario
- **CTG**: Gestión de problemas técnicos
- **PQR**: Peticiones, quejas y reclamos
- **Acabados**: Selección y gestión de acabados
- **Perfil**: Actualizaciones de perfil
- **Notificaciones**: Envío y gestión de notificaciones
- **Acceso a Módulos**: Navegación entre secciones

### 2. API Backend (`api/audit_dashboard_data.php`)

#### Endpoints Disponibles:

##### `get_modules_data`
- **Propósito**: Obtener conteos de auditorías por módulo
- **Respuesta**: Array con módulos y sus conteos

##### `get_recent_audits`
- **Propósito**: Obtener las 10 auditorías más recientes
- **Respuesta**: Array con auditorías ordenadas por timestamp

##### `get_module_audits`
- **Propósito**: Obtener auditorías específicas de un módulo
- **Parámetros**:
  - `resource`: Módulo específico
  - `offset`: Paginación
  - `limit`: Cantidad de registros
  - `date_from/date_to`: Rango de fechas
  - `user_type`: Tipo de usuario
  - `action_filter`: Filtro por acción
  - `target_id`: ID específico
  - `search`: Búsqueda en detalles

### 3. Sistema de Filtros

#### Filtros Disponibles:
- **Rango de Fechas**: Desde/Hasta con selector de fecha
- **Tipo de Usuario**: Usuario, Responsable, Sistema
- **Acción**: Búsqueda por tipo de acción específica
- **ID Objetivo**: Filtro por ID de recurso específico
- **Búsqueda General**: Búsqueda en campo de detalles

#### Funcionalidades:
- **Aplicar Filtros**: Botón para ejecutar filtros
- **Limpiar Filtros**: Reset completo de criterios
- **Búsqueda con Enter**: Activación rápida con tecla Enter

### 4. Visualización de Datos

#### Tabla de Auditorías:
- **Fecha y Hora**: Timestamp formateado
- **Usuario**: Tipo y nombre del usuario
- **Acción**: Badge con color según tipo
- **Recurso**: Módulo o recurso afectado
- **IP**: Dirección IP del usuario
- **Detalles**: Información adicional formateada

#### Gráfico de Pastel:
- **Distribución**: Porcentaje de cada tipo de acción
- **Leyenda Personalizada**: Con conteos y porcentajes
- **Colores Temáticos**: Paleta consistente con la aplicación
- **Responsive**: Adaptable a diferentes tamaños de pantalla

## 🔐 Seguridad y Autenticación

### Control de Acceso:
- **Token Validation**: Verificación de token en localStorage
- **Role-based Access**: Solo responsables pueden acceder
- **Session Management**: Gestión de sesiones segura
- **IP Tracking**: Registro de direcciones IP

### Auditoría de Acceso:
- **Login Tracking**: Registro de accesos al dashboard
- **Action Logging**: Todas las acciones son registradas
- **Error Logging**: Registro de errores y excepciones

## 📊 Tipos de Acciones Auditadas

### Autenticación:
- `LOGIN_SUCCESS`: Inicio de sesión exitoso
- `LOGIN_FAILED`: Intento de inicio fallido
- `LOGOUT`: Cierre de sesión
- `ACCESS_DASHBOARD`: Acceso al dashboard

### CRUD Operations:
- `CREATE_*`: Creación de recursos
- `UPDATE_*`: Actualización de recursos
- `DELETE_*`: Eliminación de recursos
- `READ_*`: Consulta de recursos

### Acceso a Módulos:
- `ACCESS_MODULE`: Acceso a módulos específicos
- `NAVIGATE_TO`: Navegación entre secciones

### Gestión de Citas:
- `CREATE_CITA`: Creación de citas con detalles completos
- `CANCEL_CITA`: Cancelación de citas con motivo
- `DELETE_CITA`: Eliminación de citas (excluido de gráficos)

### Selección de Acabados:
- `SAVE_ACABADOS`: Guardado de selección con kit y color
- `SELECT_KIT`: Selección específica de kit

### Gestión de Usuarios:
- `CREATE_USER`: Creación de usuarios
- `UPDATE_USER`: Actualización de perfiles
- `DELETE_USER`: Eliminación de usuarios

## 🎨 Interfaz de Usuario

### Diseño:
- **Bootstrap 5**: Framework CSS moderno
- **Bootstrap Icons**: Iconografía consistente
- **TailwindCSS**: Estilos personalizados
- **Responsive Design**: Adaptable a móviles

### Componentes:
- **Module Cards**: Tarjetas interactivas para módulos
- **Data Tables**: Tablas con hover effects
- **Filter Forms**: Formularios de filtrado intuitivos
- **Charts**: Gráficos interactivos con Chart.js
- **Loading States**: Indicadores de carga
- **Error Handling**: Manejo de errores user-friendly

## 🚀 Funcionalidades Avanzadas

### Paginación:
- **Load More**: Carga incremental de datos
- **Offset Management**: Control de paginación
- **Results Counter**: Contador de resultados

### Búsqueda:
- **Real-time Search**: Búsqueda en tiempo real
- **Multiple Criteria**: Múltiples criterios simultáneos
- **Search Highlighting**: Resaltado de resultados

### Formateo Inteligente de Detalles:
- **Acceso a Módulos**: Muestra nombre del menú en lugar de JSON
- **Acabados**: Formato "Kit Name + Color + Paquetes Adicionales"
- **Citas**: Formato completo con propósito, hora, fecha, responsable y duración
- **Propósitos Dinámicos**: Consulta automática a base de datos con fallback hardcodeado

### Gráficos Personalizados por Módulo:
- **Acceso a Módulos**: Distribución por nombres de menús accedidos
- **Acabados**: Distribución por kits seleccionados (nombre completo)
- **Citas**: Distribución por propósitos de citas creadas
- **Filtrado Inteligente**: Exclusión de acciones específicas según el contexto

### Exportación:
- **CSV Export**: Exportación a formato CSV
- **Excel Export**: Exportación a Excel (pendiente)
- **PDF Reports**: Reportes en PDF (pendiente)

## 📈 Métricas y Estadísticas

### Dashboard Metrics:
- **Total Audits**: Conteo total de auditorías
- **Module Distribution**: Distribución por módulo
- **User Activity**: Actividad por usuario
- **Time-based Analysis**: Análisis temporal

### Visualizaciones:
- **Pie Charts**: Distribución de acciones
- **Bar Charts**: Comparativas por módulo
- **Line Charts**: Tendencias temporales
- **Heatmaps**: Mapas de calor de actividad

## 🔧 Configuración y Mantenimiento

### Configuración:
- **Database Connection**: Conexión a base de datos
- **API Endpoints**: Configuración de endpoints
- **CSS Variables**: Variables de estilo
- **Chart Configuration**: Configuración de gráficos

### Mantenimiento:
- **Log Rotation**: Rotación de logs
- **Data Archival**: Archivado de datos antiguos
- **Performance Monitoring**: Monitoreo de rendimiento
- **Error Tracking**: Seguimiento de errores

## 🛠️ Desarrollo y Extensión

### Estructura Modular:
- **Component-based**: Arquitectura basada en componentes
- **API-first**: Diseño API-first
- **Scalable**: Escalable y mantenible
- **Extensible**: Fácil de extender

### Mejoras Técnicas Implementadas:

#### Formateo Dinámico de Detalles:
- **Función `formatAuditDetails`**: Procesa JSON de detalles según el contexto
- **Consultas Dinámicas**: Integración con tablas `proposito_agendamiento`, `acabado_kit`, `responsable`
- **Fallback Robusto**: Nombres hardcodeados como respaldo en caso de errores de DB
- **Compatibilidad**: Manejo de auditorías antiguas sin campos nuevos

#### Gráficos Contextuales:
- **Lógica Modular**: Diferente procesamiento según el módulo auditado
- **Filtrado Inteligente**: Exclusión de acciones específicas (ej: DELETE_CITA)
- **Títulos Dinámicos**: Títulos de gráfico que cambian según el módulo
- **Extracción de Datos**: Procesamiento específico para cada tipo de módulo

#### Optimizaciones de Rendimiento:
- **Consultas Preparadas**: Uso de prepared statements para seguridad
- **Paginación Eficiente**: LIMIT/OFFSET optimizado para grandes volúmenes
- **Caché de Datos**: Minimización de consultas repetitivas
- **Validación de Parámetros**: Sanitización de inputs del usuario

### Mejoras Futuras:
- **Real-time Updates**: Actualizaciones en tiempo real
- **Advanced Analytics**: Analytics avanzados
- **Machine Learning**: Detección de anomalías
- **Integration APIs**: APIs de integración

## 📋 Checklist de Implementación

### ✅ Completado:
- [x] Estructura de base de datos
- [x] API endpoints básicos
- [x] Dashboard principal
- [x] Sistema de autenticación
- [x] Filtros básicos
- [x] Visualización de datos
- [x] Gráfico de pastel
- [x] Diseño responsive
- [x] Separación de CSS
- [x] Formateo inteligente de detalles
- [x] Integración con base de datos para propósitos
- [x] Gráficos personalizados por módulo
- [x] Exclusión de acciones específicas en gráficos

### 🔄 En Progreso:
- [ ] Exportación a Excel
- [ ] Reportes en PDF
- [ ] Analytics avanzados
- [ ] Notificaciones push

### 📋 Pendiente:
- [ ] Dashboard de métricas
- [ ] Alertas automáticas
- [ ] Integración con SIEM
- [ ] Machine Learning
- [ ] API de integración

## 🎯 Objetivos del Sistema

### Principales:
1. **Trazabilidad Completa**: Registro de todas las acciones críticas
2. **Seguridad**: Detección de actividades sospechosas
3. **Cumplimiento**: Cumplimiento de normativas de auditoría
4. **Análisis**: Insights sobre el uso de la aplicación

### Secundarios:
1. **Performance**: Optimización de consultas
2. **Usabilidad**: Interfaz intuitiva y fácil de usar
3. **Escalabilidad**: Capacidad de crecimiento
4. **Mantenibilidad**: Código limpio y documentado

## 📞 Soporte y Contacto

Para soporte técnico o consultas sobre el sistema de auditoría:
- **Documentación**: Este archivo y `audit.md`
- **Código**: Revisar implementación en archivos PHP
- **Issues**: Reportar problemas en el sistema de tickets
- **Mejoras**: Sugerir mejoras en el backlog

---

**Última actualización**: Enero 2025  
**Versión**: 1.1.0  
**Estado**: Implementación completa y funcional con mejoras avanzadas
