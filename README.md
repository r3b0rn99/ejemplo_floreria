# Florería Bella – E-commerce en PHP + MySQL

Aplicación web dinámica desarrollada en **PHP** con **MySQL/MariaDB** para la venta de productos de florería (e-commerce). Incluye autenticación con roles (admin/cliente), carrito de compras, gestión de pedidos, administración de productos con subida de imágenes, reportes exportables y panel de administración con métricas y gráficos.

---

## Funcionalidades principales

### Público / Cliente
- Registro e inicio de sesión (sesiones seguras + contraseñas con hash).
- Catálogo de productos (destacados y listado general).
- Filtrado y búsqueda (si está habilitado en tu versión).
- Carrito de compras y checkout.
- Historial de pedidos.
- Perfil de usuario (incluye posibilidad de foto si está implementado).

### Administrador
- Dashboard con KPIs y gráficos estadísticos (ventas, pedidos, usuarios).
- CRUD de productos (crear, listar, editar, eliminar).
- Subida de imagen principal de producto (validación de tipo/tamaño).
- Gestión de pedidos (cambio de estado).
- Boletas en PDF tipo ticket + QR (WhatsApp).
- Exportación de ventas a CSV.

---

## Requisitos

- **PHP >= 8.0** (recomendado 8.1+)
- Servidor local: **XAMPP** (Apache + MySQL/MariaDB)
- Extensiones PHP recomendadas:
  - `pdo_mysql`
  - `mbstring`
  - `gd` (para QR)
- Base de datos: **MySQL / MariaDB**
- Navegador moderno (Chrome/Edge)

---

## Instalación (local)

1. Clona el repositorio:
   ```bash
   git clone https://github.com/r3b0rn99/ejemplo_floreria.git
Copia el proyecto dentro de:

makefile
Copiar código
C:\xampp\htdocs\ejemplo_floreria
Inicia XAMPP:

Apache ✅

MySQL ✅

Crea la base de datos:

Nombre: floreria_db

Importa el SQL:

phpMyAdmin → Importar → selecciona el archivo schema.sql

luego importa seed.sql (si aplica)

Configura credenciales de base de datos:

Edita: includes/config/database.php

Ajusta host/usuario/password según tu XAMPP.

Ejecutar el sistema
Página principal:

ruby
Copiar código
http://localhost/ejemplo_floreria/public/index.php
Panel administrador:

arduino
Copiar código
http://localhost/ejemplo_floreria/admin/dashboard.php
Credenciales de prueba
Si deseas usar estas credenciales, asegúrate de que existan en tu seed.sql
o crea los usuarios desde la interfaz de registro.

Admin

Email: admin@floreriabella.com

Password: Admin12345

Cliente

Email: cliente@floreriabella.com

Password: Cliente12345

Reportes / Export
Exportación de ventas en CSV:

Ruta: admin/reportes/ventas_csv.php

Ejemplo:

arduino
Copiar código
http://localhost/ejemplo_floreria/admin/reportes/ventas_csv.php
API (básica) – Productos
La API devuelve JSON y utiliza token simple por header o parámetro.

Endpoint
GET /api/productos.php

Autenticación
Enviar token en header:

X-API-KEY: MI_TOKEN_123456

O por query string:

?api_key=MI_TOKEN_123456

Ejemplos
Listar productos:

arduino
Copiar código
http://localhost/ejemplo_floreria/api/productos.php?api_key=MI_TOKEN_123456
Detalle:

bash
Copiar código
http://localhost/ejemplo_floreria/api/productos.php?id=1&api_key=MI_TOKEN_123456
Arquitectura (MVC básico)
Estructura general:

public/ → vistas y páginas públicas

admin/ → vistas y páginas admin

app/Models/ → lógica de acceso a datos (Model)

app/Controllers/ → controladores (Controller)

includes/ → configuración, auth, utilidades

assets/ → CSS/JS/imagenes/uploads

Seguridad aplicada
SQL Injection: consultas preparadas (PDO prepare/execute).

XSS: escape de salida con htmlspecialchars.

CSRF: token en formularios sensibles (si está implementado).

Sesiones: control de acceso por rol (admin/cliente).

Logs
Registro de errores importantes:

Archivo: logs/app.log

Función: app_log() en includes/config/functions.php

Performance
Paginación en listados (ej. admin/productos/listar.php) con LIMIT/OFFSET.

Consultas optimizadas evitando cargar listas completas innecesarias.

Pruebas básicas (manuales)
Registro y login (cliente).

Login admin y acceso a dashboard.

CRUD productos (crear con imagen, editar, eliminar).

Crear pedido desde carrito y verificar historial.

Exportar ventas CSV.

Generar boleta PDF y validar QR.

Probar API productos con token.

Créditos
Proyecto académico – Desarrollo de Soluciones Web Back-End.
