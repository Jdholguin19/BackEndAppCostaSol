# Planificación para la Integración de Kiss Flow: Proceso "Warranty Claim" (CTG)

Este documento detalla las preguntas y los puntos a aclarar para integrar el proceso "Warranty Claim" de Kiss Flow con el módulo CTG de la aplicación.

## 1. Detalles del Proceso en Kiss Flow

- **Confirmación del `processName`**: La URL proporcionada es `https://thaliavictoria.kissflow.com/view/process/Warranty_Claim/myitems/Draft`. ¿El `processName` exacto que debo usar para las llamadas a la API es `Warranty_Claim`? A veces, los nombres de los procesos en la API tienen un formato diferente (por ejemplo, con guiones bajos o al final). Si asi se llama el proceso Warranty_Claim

- **Estructura de Datos (Campos)**: Necesito una lista completa de todos los campos disponibles en el proceso `Warranty_Claim` y sus tipos de datos. Esto es fundamental para crear la tabla en la base de datos. Por ejemplo:
    - `Numero_Ticket` (Texto)
    - `Nombre_Cliente` (Texto)
    - `Descripcion_Problema` (Área de texto)
    - `Fecha_Creacion` (Fecha)
    - `Estado_Garantia` (Selección)
    - `Adjuntos` (Archivo)
    - etc., ya te paso el schema de los datos, junto con una muestra @ejemplodatactg.txt @schemactg.txt

- **Estados del Proceso**: ¿Cuáles son todos los posibles valores para el campo de estado (similar a `_status`) en una `Warranty_Claim`? (ej. 'Nuevo', 'En Progreso', 'Resuelto', 'Cerrado'). Esto es necesario para la funcionalidad de "resumen de estados" del chatbot., si son similares solo que esta bien no vamos a traer datos sin o a enviar datos en este caso seria de momento a _status": "Draft"

## 2. Base de Datos

- **Nombre de la Tabla**: ¿Cómo debería llamarse la nueva tabla en la base de datos para almacenar los datos de `Warranty_Claim`? Siguiendo el patrón existente, sugiero `kissflow_warranty_claims` o `kissflow_ctg`. De ninguna manera ya que no vamos a traer datos vamos a enviar datos desde la APP hacia Kiss_flow hacia ese proceso


## 3. Lógica de Sincronización

- **Nuevo Script de Sincronización**: ¿Debo crear un nuevo script de sincronización (por ejemplo, `sync_warranty_claims.php`) dentro de la carpeta `api/ctg/kissflow_ctg/`? Creeria que si, pero como esta vez el enfoque es diferente vamos a enviar datos, en ves e traer los datos

- **Campo de Sincronización Incremental**: ¿El proceso `Warranty_Claim` incluye un campo `_modified_at` o similar? Esto es crucial para realizar actualizaciones incrementales y no tener que descargar todos los datos en cada ejecución. Si mal no veo si 

- **Programación de Tareas (Cron Job)**: ¿Este nuevo script de sincronización se ejecutará con la misma frecuencia que el de "Emisión de Pagos" (cada 4 horas), o necesita una programación diferente? No, solo se ejecutara cada vez que se crea un nuevo ctg desde la app --> Kiss Flow --> process

## 4. Integración del Chatbot

- **Extensión o Nuevo Chatbot**: ¿Debo extender el chatbot existente (`kiss_flow/chatbot_backend.php`) para que maneje también las consultas sobre garantías, o creo un backend de chatbot nuevo y separado (ej. `chatbot_ctg.php`)? Aqui no va a ver chat bot, eso es solo para lo otro

## 5. Configuración y Credenciales

- **Credenciales de la API**: ¿Puedo reutilizar las mismas credenciales de la API de Kiss Flow que ya están definidas en `kiss_flow/config.php`, o esta nueva integración requiere un juego de claves de API diferente? claro vas a usar las mismas credenciales

Una vez que tengamos respuestas a estas preguntas, podré empezar a desarrollar la integración.

---

## 6. Análisis de Dependencias y Flujo de Datos (Nuevas Dudas)

Tras un análisis más profundo de los archivos `schema` y `ejemplo`, han surgido nuevas consideraciones críticas sobre el flujo de datos.

**Conclusión Clave:** El proceso `Warranty_Claim` no almacena directamente los datos del cliente, sino que **enlaza a un registro de cliente existente** en un "Dataset" de Kiss Flow llamado `DS_Documentos_Cliente`. Esto se confirma al ver que el campo `Cliente` es de tipo `Reference`.

Esto implica que el flujo de trabajo debe cambiar para incluir un **paso de validación previo** antes de intentar crear el `Warranty_Claim`.

### Abordando las Nuevas Dudas

1.  **¿Qué pasa si el cliente no registró su cédula en la APP?**
    *   **Respuesta:** La creación del CTG en Kiss Flow **debe fallar**. La cédula es el identificador único que necesitamos para buscar y confirmar al cliente en Kiss Flow. Sin este dato, el proceso no puede continuar de forma segura. Se debe devolver un error claro.

2.  **¿Qué pasa si el cliente no está registrado en Kiss Flow?**
    *   **Análisis:** El script deberá buscar en el Dataset `DS_Documentos_Cliente` usando la cédula del cliente. Si no se encuentra ningún registro, debemos decidir cómo proceder.
    *   **Opción A (Recomendada por seguridad):** El proceso se detiene. Se notifica un error indicando que el CTG se creó en la base de datos local, pero no se pudo registrar en Kiss Flow porque el cliente es desconocido. Esto requeriría una acción manual para dar de alta al cliente en Kiss Flow.
    *   **Opción B (Automatizada):** El script podría intentar crear un nuevo registro para el cliente en el Dataset `DS_Documentos_Cliente` usando la API. Si tiene éxito, usaría el nuevo ID para proceder con la creación del `Warranty_Claim`. Esta opción es más compleja porque requiere conocer los campos mínimos para crear un cliente y tener permisos para ello.
    *   **Decisión Requerida:** ¿Se debe detener el proceso (Opción A) o intentar la creación automática del cliente (Opción B)?

3.  **¿Qué pasa si el cliente está registrado pero no tiene una propiedad?**
    *   **Análisis:** Los datos de ejemplo sugieren que el registro en `DS_Documentos_Cliente` contiene tanto la información del cliente como la de su propiedad.
    *   **Respuesta:** Si el registro del cliente existe pero desde la APP no se proporciona el identificador de la propiedad (ej. `Mz_Solar`), la creación en Kiss Flow debería fallar. No podemos asociar una contingencia a una propiedad no especificada.

**Propuesta:** Actualizar el plan de acción para reflejar este nuevo flujo:
1.  **Recibir datos del CTG** desde la app (incluyendo cédula y Mz/Solar).
2.  **Buscar Cliente** en `DS_Documentos_Cliente` vía API usando la cédula.
3.  **Validar Resultado:**
    *   Si no hay cédula -> Fallar.
    *   Si no se encuentra cliente -> Aplicar Opción A o B.
    *   Si se encuentra cliente -> Obtener su ID de Kiss Flow.
4.  **Construir Payload** para `Warranty_Claim` usando el ID obtenido.
5.  **Crear `Warranty_Claim`** en Kiss Flow vía API.