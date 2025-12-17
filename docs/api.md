# Documentación API – Florería Bella

Base URL (local):
http://localhost/ejemplo_floreria

Esta API devuelve respuestas en **JSON** y usa un token simple (API Key).

---

## Autenticación

Puedes enviar el token de 2 formas:

### 1) Header (recomendado)
Header:
- `X-API-KEY: MI_TOKEN_123456`

### 2) Query string
Ejemplo:
- `?api_key=MI_TOKEN_123456`

Si no envías el token o es incorrecto:
```json
{"ok":false,"error":"No autorizado. Falta o es incorrecto el token X-API-KEY."}



Endpoint: Productos
1) Listar productos

GET /api/productos.php

Parámetros (opcionales)

q (string): búsqueda por nombre/descripcion (si está implementado).

cat (int): filtrar por categoría (si está implementado).

api_key (string): token (si no usas header).

Ejemplo
GET http://localhost/ejemplo_floreria/api/productos.php?api_key=MI_TOKEN_123456

Respuesta ejemplo
{
  "ok": true,
  "data": [
    {
      "id": 1,
      "nombre": "Ramo de rosas",
      "precio": "50.00",
      "categoria_id": 2,
      "imagen_principal": "assets/uploads/productos/rosas_rojas.jpg",
      "destacado": 1,
      "activo": 1
    }
  ]
}

2) Obtener detalle de producto

GET /api/productos.php?id=ID

Parámetros

id (int): ID del producto

api_key (string): token (si no usas header)

Ejemplo
GET http://localhost/ejemplo_floreria/api/productos.php?id=1&api_key=MI_TOKEN_123456

Respuesta ejemplo
{
  "ok": true,
  "data": {
    "id": 1,
    "nombre": "Ramo de rosas",
    "descripcion": "Ramo clásico…",
    "precio": "50.00",
    "categoria_id": 2,
    "stock": 10,
    "imagen_principal": "assets/uploads/productos/rosas_rojas.jpg",
    "destacado": 1,
    "activo": 1,
    "fecha_creacion": "2025-12-16 08:00:00"
  }
}

Códigos / Errores comunes

Token faltante o inválido:

{"ok":false,"error":"No autorizado. Falta o es incorrecto el token X-API-KEY."}


Producto no encontrado:

{"ok":false,"error":"Producto no encontrado."}

Notas

Esta API es básica y está pensada como demostración académica.

Para uso real se recomienda JWT/OAuth y control de permisos por rol.