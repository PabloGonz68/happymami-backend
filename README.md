# Prueba Técnica Backend - Happymami

    Nombre: Pablo González Silva
    Fecha de entrega: 10/03/2026

Desarrollo del backend simplificado para el sistema de gestión de pedidos de Happymami, construido en **PHP puro** y **MySQL**, sin el uso de frameworks pesados, primando la estructuración clara, el control transaccional y el diseño relacional.

## Instrucciones para levantar el proyecto

Para ejecutar este proyecto en un entorno local (por ejemplo, usando XAMPP, WAMP o MAMP), sigue estos pasos:

1. **Clonar o mover el proyecto:**
   Coloca la carpeta del proyecto (idealmente nombrada `happymami-backend`) dentro del directorio de tu servidor web (por ejemplo, `htdocs` en XAMPP o `www` en WAMP).

2. **Base de Datos:**
   - Abre tu gestor de base de datos MySQL (phpMyAdmin, DBeaver, etc.).
   - Importa el archivo `database.sql` incluido en la raíz de este proyecto.
   - Este script creará automáticamente la base de datos `happymami_test` junto con sus 4 tablas relacionales (`clientes`, `productos`, `pedidos` y `detalles_pedido`).

3. **Configuración de conexión:**
   Si tu entorno MySQL tiene una contraseña distinta a la de por defecto (usuario `root`, sin contraseña), actualiza las credenciales en el archivo `config/database.php`.

4. **Probar la API:**
   La API responde en la ruta base: `http://localhost/happymami-backend/index.php/`.
   _(Nota: Ajusta la URL si el nombre de tu carpeta es distinto)._

---

## Endpoints Principales

El sistema sigue una arquitectura RESTful básica gestionada a través de un enrutador en el `index.php`. Todas las respuestas se devuelven en formato JSON.

### Clientes y Productos

Ambos módulos comparten la misma estructura estándar de operaciones CRUD:

- `GET /recurso` : Lista todos los registros.
- `GET /recurso/{id}` : Obtiene un registro específico por su ID.
- `POST /recurso` : Crea un nuevo registro.
- `PUT /recurso/{id}` : Actualiza los datos de un registro existente.
- `DELETE /recurso/{id}` : Elimina un registro del sistema.

### Pedidos (`/pedidos`)

Este controlador maneja una lógica transaccional más compleja que involucra múltiples tablas:

- `GET /pedidos` : Lista el histórico completo de pedidos, ensamblando y anidando los productos de cada uno.
- `GET /pedidos/{id}` : Devuelve el detalle completo de un pedido específico y sus líneas de compra.
- `POST /pedidos` : Ejecuta la creación transaccional de un pedido (insertando la cabecera y desglosando las líneas de detalle de forma segura), congelando el `precio_unitario` de ese momento.
- `PUT /pedidos/{id}` : Actualiza el `estado` (ej. procesando, enviado) y las `notas` de un pedido.
- `DELETE /pedidos/{id}` : Elimina la cabecera del pedido. La base de datos, mediante su restricción `ON DELETE CASCADE`, se encarga de fulminar automáticamente las líneas de detalle asociadas para mantener la integridad referencial.

---

## Pregunta Teórica: Implementación de "Bundles"

Para implementar la venta de Bundles sin romper la integridad de los pedidos anteriores ni alterar drásticamente la estructura actual, optaría por las siguientes modificaciones, apoyadas por nuevas tablas de definición.

**1. Modificaciones en la Base de Datos:**

- Crear tabla `bundles` (`id`, `nombre`, `descripcion`, `precio_pack`, `activo`).
- Crear tabla intermedia `bundle_productos` (`bundle_id`, `producto_id`, `cantidad_incluida`) para definir qué contiene cada pack de forma dinámica.
- Modificar la tabla actual `detalles_pedido`: Añadir una columna opcional `bundle_id INT NULL` (con Foreign Key a `bundles`).

**2. Lógica de negocio (El proceso):**

- Los pedidos anteriores se mantienen intactos, ya que su nueva columna `bundle_id` será `NULL`. No se rompe la retrocompatibilidad.
- Cuando un cliente compra un Bundle, el backend lo "desglosa". Si el bundle tiene 2 biberones y 1 termo, el backend insertará **3 líneas individuales** en la tabla `detalles_pedido`.
- La diferencia es que estas 3 líneas tendrán relleno el campo `bundle_id` apuntando al mismo pack. El `precio_unitario` de esas líneas se calculará de forma proporcional (o a 0 si es un regalo) basándose en el precio total del pack.

**3. Ventajas de este modelo:**

- **Stock:** El control de stock sigue siendo exacto y sencillo. Como el pack se desglosa en productos individuales, al hacer la venta se resta el stock directamente de la tabla `productos` sin cálculos paralelos.
- **Histórico y Analítica:** Mantenemos la trazabilidad perfecta de qué piezas exactas salieron del almacén, pero sabiendo que formaban parte de una promoción conjunta gracias al `bundle_id`.

---

## Política sobre Inteligencia Artificial

El diseño, la arquitectura relacional y el desarrollo de la lógica de negocio de esta prueba técnica han sido realizados de forma íntegramente autónoma por mí. He utilizado herramientas de IA exclusivamente como asistentes de productividad para tareas mecánicas, asegurándome de auditar, comprender al 100% y validar personalmente cada línea de código entregada:

1. **GitHub Copilot:**
   - **Uso:** Autocompletado de código repetitivo (boilerplate), agilización en la escritura de variables y redacción de los comentarios explicativos del código.
   - **Justificación:** Me permite ahorrar tiempo en el "picado de piedra" y en la documentación técnica una vez que la estructura SQL, las validaciones y el flujo del controlador ya han sido diseñados previamente en mi cabeza.

2. **Gemini (LLM):**
   - **Uso:** Herramienta de consulta puntual (a modo de _Rubber Duck Debugging_).
   - **Casos específicos:** Lo utilicé para re-confirmar la sintaxis exacta de PHP puro en la captura de excepciones (`PDOException`) y para contrastar mi implementación del control transaccional (`beginTransaction`, `commit`, `rollBack`) para evitar bloqueos en la base de datos.
   - **Justificación:** Lo uso como un motor de búsqueda avanzado para validar rápidamente decisiones de diseño arquitectónico que ya he tomado (como delegar la limpieza de datos al `ON DELETE CASCADE` de MySQL). Absolutamente todo el código generado o sugerido ha sido revisado críticamente, probado a fondo con Postman y adaptado a mi propio criterio técnico antes de ser incluido en el proyecto.
