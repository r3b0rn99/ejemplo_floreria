## Modelo de datos (ER)

El sistema se basa en las siguientes entidades principales:

- **usuarios**: almacena clientes y administradores. Incluye datos como nombre, email, teléfono, tipo (admin/cliente) y estado activo.
- **categorias**: clasifica productos (relación 1:N con productos).
- **productos**: catálogo de florería. Guarda nombre, precio, stock, categoría, si es destacado/activo e imagen principal.
- **carrito**: registra productos agregados al carrito por usuario (relación N:1 con usuarios y N:1 con productos).
- **pedidos**: representa una compra confirmada (1:N con usuarios).
- **detalle_pedido**: items del pedido (relación 1:N con pedidos y N:1 con productos). Guarda cantidad y precio.
- **direcciones**: direcciones de envío asociadas a usuarios (1:N).
- **boletas**: registro/archivo de boletas generadas en PDF para cada pedido (1:1 o 1:N según tu implementación).

### Relaciones clave
- categorias (1) → (N) productos
- usuarios (1) → (N) pedidos
- pedidos (1) → (N) detalle_pedido
- productos (1) → (N) detalle_pedido
- usuarios (1) → (N) direcciones
