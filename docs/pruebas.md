# Pruebas básicas (manuales) – Florería Bella

Estas pruebas verifican las funciones críticas del sistema (autenticación, CRUD, pedidos, reportes, PDF y API).

> Entorno de prueba: XAMPP (Apache + MySQL/MariaDB), PHP 8+, BD: floreria_db  
> URL: http://localhost/ejemplo_floreria/public/index.php

---

## TC-01 Registro de usuario (cliente)
**Objetivo:** validar registro con hash y sesión.
**Pasos:**
1. Ir a `includes/auth/register.php`
2. Completar nombre, email, contraseña y registrar
**Resultado esperado:**
- Usuario creado en tabla `usuarios`
- Redirección/login correcto
- No muestra la contraseña en BD (debe ser hash)

---

## TC-02 Login cliente (sesión)
**Objetivo:** validar inicio de sesión.
**Pasos:**
1. Ir a `includes/auth/login.php`
2. Ingresar credenciales de cliente
**Resultado esperado:**
- Se crea sesión `$_SESSION['usuario_id']`
- Se muestra el nombre en navbar
- No permite entrar a `/admin/*`

---

## TC-03 Login admin (autorización)
**Objetivo:** validar rol admin y acceso al dashboard.
**Pasos:**
1. Login con usuario tipo `admin`
2. Ir a `admin/dashboard.php`
**Resultado esperado:**
- Accede al panel
- Se muestran KPIs y gráficos (Chart.js)
- Un cliente normal debe ser redirigido al login o bloqueado

---

## TC-04 CRUD Productos – Crear con imagen
**Objetivo:** validar subida de imagen + creación.
**Pasos:**
1. Admin → `admin/productos/agregar.php`
2. Crear producto con imagen JPG/PNG/WEBP (<= 2MB), marcar “destacado”
**Resultado esperado:**
- Registro creado en `productos`
- Imagen guardada en `assets/uploads/productos/`
- En BD `imagen_principal` guarda ruta válida (assets/...)
- Se ve la imagen en:
  - Admin listar productos
  - Página pública (productos y destacados)

---

## TC-05 CRUD Productos – Editar
**Objetivo:** validar actualización.
**Pasos:**
1. Admin → `admin/productos/listar.php`
2. Editar nombre/precio/estado o cambiar destacado
**Resultado esperado:**
- Cambios reflejados en BD y en la tienda pública

---

## TC-06 CRUD Productos – Eliminar (lógico o físico)
**Objetivo:** validar eliminación.
**Pasos:**
1. Admin → listar productos → eliminar un producto
**Resultado esperado:**
- Producto deja de mostrarse en tienda si es eliminado o marcado como inactivo
- No rompe el listado ni genera errores

---

## TC-07 Filtrado y búsqueda (catálogo)
**Objetivo:** validar filtros y búsqueda.
**Pasos:**
1. Ir a `public/productos.php`
2. Buscar por texto (q) y filtrar por categoría (cat)
**Resultado esperado:**
- Lista de productos cambia según filtro
- Mantiene parámetros en URL (ej. `?q=...&cat=...`)

---

## TC-08 Carrito + Checkout + Pedido
**Objetivo:** validar flujo de compra.
**Pasos:**
1. Agregar 1–2 productos al carrito
2. Ir a `public/carrito.php`
3. Ir a `public/checkout.php` y confirmar pedido
**Resultado esperado:**
- Se crea registro en `pedidos`
- Se crean registros en `detalle_pedido`
- Se limpia el carrito
- El pedido aparece en `public/pedidos.php`

---

## TC-09 Gestión de pedidos (admin)
**Objetivo:** validar control de estados.
**Pasos:**
1. Admin → `admin/pedidos/gestionar.php`
2. Cambiar estado del pedido (pendiente → confirmado → entregado)
**Resultado esperado:**
- Estado actualizado en BD
- Cambios visibles en admin y en pedidos del usuario

---

## TC-10 Boleta PDF tipo ticket + QR
**Objetivo:** validar generación PDF + QR.
**Pasos:**
1. Desde admin/boletas o desde el pedido, generar boleta
2. Abrir el PDF generado
**Resultado esperado:**
- Se genera PDF en `assets/boletas/`
- Se genera QR en `assets/qr/`
- El PDF muestra correctamente tildes/ñ
- QR dirige a WhatsApp (wa.me)

---

## TC-11 Exportación CSV de ventas (admin)
**Objetivo:** validar reporte/export.
**Pasos:**
1. Admin → Dashboard → “Exportar ventas (CSV)”
2. Descargar archivo
**Resultado esperado:**
- Descarga archivo `.csv`
- El CSV tiene columnas: id, usuario_id, cliente, email, total, método, estado, fecha_pedido
- Excel abre bien (BOM UTF-8 para tildes)

---

## TC-12 API Productos (token simple)
**Objetivo:** validar API JSON + autenticación.
**Pasos:**
1. Abrir:
   `http://localhost/ejemplo_floreria/api/productos.php?api_key=MI_TOKEN_123456`
2. Probar detalle:
   `http://localhost/ejemplo_floreria/api/productos.php?id=1&api_key=MI_TOKEN_123456`
3. Probar sin token
**Resultado esperado:**
- Con token: `{ ok:true, data:[...] }`
- Sin token: `{ ok:false, error:"No autorizado..." }`
