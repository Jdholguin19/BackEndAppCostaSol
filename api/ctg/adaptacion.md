# Documentación y Guía de Adaptación: Integración con Kiss Flow

Este documento resume la integración del módulo de CTG con el proceso "Warranty Claim" de Kiss Flow y proporciona una guía para replicar este sistema en otros procesos de la aplicación.

---

## Parte 1: Resumen de la Integración CTG ("Warranty Claim")

### Objetivo

El objetivo final fue crear un ticket en el proceso "Warranty Claim" de Kiss Flow cada vez que un usuario registra un nuevo CTG en la aplicación. El ticket resultante queda en un estado que requiere aprobación de Servicio al Cliente, permitiendo una revisión manual antes de que el proceso interno de Kiss Flow continúe.

### Flujo de la API (`ctg_handler.php`)

Tras un análisis exhaustivo, se determinó que la creación del ticket se realiza mediante un flujo de 2 pasos principales:

1.  **Búsqueda del Cliente:**
    *   El sistema toma la **cédula** del usuario de la aplicación.
    *   Realiza una búsqueda en el Dataset de Kiss Flow `DS_Documentos_Cliente` para encontrar el registro coincidente.
    *   De la respuesta, se extrae el **ID interno** que Kiss Flow usa para ese cliente (ej: `DS_Documentos_Cliente_...`).

2.  **Creación del Borrador con Datos y Pausa:**
    *   Se realiza una única petición `POST` a la URL del proceso en Kiss Flow (`.../process/2/AcNcc9rydX9F/Warranty_Claim`).
    *   El **payload** de esta petición incluye todos los datos del formulario (descripción, email, teléfono, etc.) y, crucialmente, el ID del cliente obtenido en el paso anterior para enlazarlo.
    *   Se incluye el campo `Requiere_agendamiento_de_inspeccion: true`. Esta es una condición de negocio dentro de Kiss Flow que hace que el ticket se detenga para una aprobación, cumpliendo el requisito de revisión manual.

### Puntos Clave y Lecciones Aprendidas

*   **Flujo de la Interfaz vs. API:** La interfaz web de Kiss Flow utiliza un flujo complejo de múltiples pasos (crear borrador, guardar campos uno a uno, enviar). Sin embargo, para una integración de backend, a menudo es posible (y preferible) encontrar un endpoint que permita la creación con datos en menos pasos.
*   **La Captura de Red es Fundamental:** La herramienta "Network" del navegador fue indispensable para descubrir las URLs, los métodos y, sobre todo, la estructura de los payloads que la API de Kiss Flow realmente espera. No se puede confiar únicamente en la documentación genérica.
*   **Permisos de la API (`PermissionDeniedToUpdate`):** Descubrimos que algunos campos en Kiss Flow están "bloqueados" después de la creación inicial. La API nos prohibía actualizarlos, lo que nos llevó a la conclusión de que debíamos enviar toda la información posible en la primera llamada de creación.
*   **Denormalización de Datos:** Kiss Flow puede requerir datos denormalizados. En nuestro caso, no bastaba con enlazar el objeto `Cliente` a través de su ID; también tuvimos que enviar explícitamente los campos `Identificacion` y `Requestor_Name` en el payload principal para que se visualizaran correctamente.

---

## Parte 2: Guía para Adaptar la Integración a Otros Procesos

Has mencionado que otros procesos funcionan de manera similar, usando la **cédula como ID principal**. Esta es la clave que hace que el sistema sea fácilmente adaptable. Aquí tienes un paso a paso genérico.

### Ejemplo: Adaptar para "Selección de Acabados"

Supongamos que quieres que, cuando un usuario guarde su selección de acabados, también se inicie un proceso en Kiss Flow.

#### **Paso 1: Recopilar Información del Nuevo Proceso**

1.  **Nombre del Proceso:** Navega en Kiss Flow y encuentra el nombre exacto del proceso, por ejemplo: `"Proceso de Acabados"`.
2.  **URL de Creación:** Usa la pestaña "Network" del navegador. Abre el formulario para iniciar un nuevo proceso de acabados y captura la **URL** y el **Método** de la primera petición `POST` que se realiza para crear el borrador. Será muy similar a la que encontramos para `Warranty_Claim`.
Request URL
https://thaliavictoria.kissflow.com/process/2/AcNcc9rydX9F/Copia_de_Eleccio_n_de_Acabados_y_Adicion
Request Method
POST
Status Code
200 OK
Remote Address
104.18.9.118:443
Referrer Policy
strict-origin-when-cross-origi

3.  **Payload Requerido:** En esa misma petición, analiza el **Request Payload**. Anota todos los campos que la interfaz envía. Por ejemplo, podrías encontrar campos como `Kit_Seleccionado`, `Color_Encimera`, `Paquetes_Adicionales`, etc.

#### **Paso 2: Crear el Nuevo Handler de API**

1.  **Acción:** Crea un nuevo archivo PHP, por ejemplo: `api/acabados/kissflow_acabados_handler.php`.
2.  **Lógica:**
    *   Puedes **copiar y pegar** el contenido de `api/ctg/kissflow_ctg/ctg_handler.php` como punto de partida.
    *   La sección de **"Búsqueda del Cliente"** será **idéntica**. No necesitas cambiarla, ya que también usará la cédula para encontrar el ID del cliente en Kiss Flow.
    *   La sección de **"Creación del Borrador"** es la que debes adaptar:
        *   Cambia la URL en `$init_url` para que apunte al nuevo nombre del proceso (ej: `.../Proceso_de_Acabados`).
        *   Modifica el array `$initial_payload` para que coincida con los campos que identificaste en el Paso 1.3. Seguirás incluyendo el objeto `Cliente` con el ID encontrado.

#### **Paso 3: Integrar con el Flujo de la Aplicación**

1.  **Identificar el Disparador:** Encuentra el script que guarda la selección de acabados en la base de datos local. Según el `README.md`, este es `api/guardar_seleccion_acabados.php`.
2.  **Modificar el Disparador:**
    *   Dentro de `guardar_seleccion_acabados.php`, después de que los datos se guarden correctamente en tu base de datos, añade una llamada (usando cURL, como en `ctg_create.php`) al nuevo handler que creaste: `kissflow_acabados_handler.php`.
    *   Asegúrate de pasarle a este handler toda la información necesaria (cédula del usuario, ID del kit, nombre del color, etc.) para que pueda construir el payload de Kiss Flow.

¡Y eso es todo! Como la lógica de buscar al cliente por su cédula es el factor común, el proceso de adaptación se reduce a identificar el nuevo endpoint y ajustar los campos específicos de cada payload.