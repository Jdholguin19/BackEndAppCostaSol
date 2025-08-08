[Rol y contexto]  
Eres un desarrollador senior en PHP trabajando en un proyecto existente. El proyecto ya tiene conexión a la base de datos y APIs funcionales. La base de datos incluye una tabla llamada `ctg` que contiene un campo `observaciones` (tipo texto) y está definida en el archivo `portalao_appcostasol.sql`.  

[Objetivo]  
Implementar una nueva funcionalidad en el sistema para agregar un pequeño cuadro de texto tipo "notas" (textarea) llamado "Observaciones", que permita al responsable asignado a un chat de un cliente guardar y actualizar información personalizada sobre ese cliente.  

[Requisitos funcionales]  
1. El cuadro de texto aparecerá debajo de “caja nueva respuesta” o “área para mostrar notificaciones de éxito” en la interfaz de ctg_detalle.php.  
2. Cuando el responsable escriba en este cuadro, se debe guardar la información en la columna `observaciones` de la tabla `ctg`, usando una consulta SQL tipo `UPDATE` (ya que solo habrá un valor por cliente que se sobrescribe).  
3. Cada `ctg` tendrá su propia nota, visible únicamente para el responsable asignado a ese cliente.  
4. Los clientes **no** deben ver este cuadro ni su contenido.  
5. El cuadro debe mostrar siempre la última observación guardada para ese `ctg` cuando el responsable abra el chat.  
6. Se puede crear una nueva API o reutilizar/adaptar una API existente para esta operación.  

[Alcance y límites]  
- Mantener el estilo y estructura del código existente.  
- No modificar otras funcionalidades del chat.  
- Validar permisos para que solo el responsable asignado pueda leer o escribir en el campo.  

[Formato de salida]  
- Proporcionar el código PHP necesario para:  
  1. Backend: Endpoint/API para guardar y actualizar el campo `observaciones` en la tabla `ctg`.  
  2. Backend: Endpoint/API para obtener el valor actual de `observaciones` para un `ctg`.  
  3. Frontend: HTML y JavaScript necesarios para mostrar el textarea, pre-cargar su contenido y guardar los cambios.  

[Estilo y tono]  
Código claro, bien comentado en español y siguiendo buenas prácticas de seguridad (prevención de SQL Injection, validación de permisos).  
