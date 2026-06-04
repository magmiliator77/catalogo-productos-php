# Gestión de un catálogo de productos (PHP + MySQL)

Aplicación web dinámica desarrollada en **PHP** con **MySQL** para gestionar un
catálogo de productos y sus fabricantes, con un sistema de permisos basado en
autenticación de usuarios.

Proyecto final del módulo · DAW · Curso 2025/2026.

---

## 1. Requisitos

- PHP 8.0 o superior (con la extensión PDO MySQL)
- MySQL / MariaDB
- Un navegador web

No hace falta framework ni Composer: es PHP "puro" con PDO.

---

## 2. Puesta en marcha

### 2.1. Crear la base de datos

Importa el script SQL que crea la base de datos `catalogo`, sus tablas y unos
datos de ejemplo:

```bash
mysql -u root -p < database/catalogo.sql
```

(También puedes copiar y pegar el contenido de `database/catalogo.sql` en
phpMyAdmin.)

### 2.2. Configurar la conexión

Edita `config/database.php` y ajusta el usuario y la contraseña de MySQL a tu
entorno:

```php
$user = 'root';
$pass = '';   // tu contraseña de MySQL
```

### 2.3. Arrancar el servidor

Desde la carpeta del proyecto:

```bash
php -S localhost:8000
```

Y abre `http://localhost:8000` en el navegador.
(También funciona colocando la carpeta en `htdocs` de XAMPP / `www` de WAMP.)

### 2.4. Usuario de prueba

| Usuario | Contraseña |
| ------- | ---------- |
| `admin` | `admin123` |

La contraseña se guarda **hasheada** en la base de datos (bcrypt) y se
comprueba con `password_verify()`.

---

## 3. Estructura del proyecto

```
catalogo/
├── index.php              → Catálogo público: búsqueda, orden y paginación
├── detalle.php            → Ficha ampliada de un producto
├── login.php              → Inicio de sesión (valida contra la BD)
├── logout.php             → Cierre de sesión
│
├── config/
│   └── database.php       → Conexión PDO a MySQL
│
├── includes/
│   ├── auth.php           → Sesión y funciones de autenticación
│   ├── header.php         → Cabecera y menú comunes
│   └── footer.php         → Pie común
│
├── admin/                 → Zona privada (requiere login)
│   ├── productos.php      → Listar / eliminar productos
│   ├── producto_form.php  → Crear / editar producto (+ subida de imagen)
│   ├── fabricantes.php    → Listar / eliminar fabricantes
│   └── fabricante_form.php→ Crear / editar fabricante
│
├── css/
│   └── style.css          → Estilos de la aplicación
│
├── uploads/               → Imágenes subidas de los productos
│
└── database/
    └── catalogo.sql       → Script de creación de la BD + datos de ejemplo
```

### Idea general de la arquitectura

- **Páginas de la raíz** → parte pública, accesible sin iniciar sesión.
- **Carpeta `admin/`** → parte privada; cada página llama a `requiereLogin()`
  al principio, de modo que un visitante sin sesión es redirigido al login.
- **`config/` e `includes/`** → código reutilizable (conexión, sesión,
  cabecera y pie) que se incluye desde las demás páginas. Así no se repite
  código y todo queda más ordenado.
- Los formularios de crear/editar (`*_form.php`) **muestran** el formulario
  (GET) y **procesan** el envío (POST) en el mismo fichero.

---

## 4. Funcionalidades

### Acceso público (sin login)
- [x] Visualizar el catálogo (nombre, precio, descripción e imagen).
- [x] Ficha de detalle de cada producto.
- [x] Paginación de resultados.
- [x] Ordenar por precio (ascendente / descendente).
- [x] Búsqueda por nombre o descripción.

### Sistema de autenticación
- [x] Login que valida credenciales contra la tabla `usuario`.
- [x] Uso de sesiones (`session_start()`) para mantener el estado.
- [x] Logout seguro (destruye la sesión y borra la cookie).

### Gestión privada (CRUD, solo usuarios identificados)
- [x] Productos: crear (con subida de imagen), editar y eliminar.
- [x] Fabricantes: crear, editar y eliminar.
- [x] El fabricante se elige con un `<select>` poblado desde la BD.

---

## 5. Detalles técnicos destacables

- **PDO + consultas preparadas** en todas las consultas → protección frente a
  **inyección SQL**.
- **`htmlspecialchars()`** al imprimir datos del usuario → protección frente a
  **XSS**.
- **`password_hash()` / `password_verify()`** → las contraseñas nunca se
  guardan en texto plano.
- **Subida de imágenes** con validación de extensión y tamaño, y nombre de
  fichero único (`uniqid`) para evitar colisiones.
- **Clave foránea** `producto.id_fabricante → fabricante.id` con
  `ON DELETE SET NULL`: al borrar un fabricante, sus productos no se pierden,
  solo quedan sin fabricante.
- Patrón **Post/Redirect/Get** tras guardar para evitar reenvíos del formulario.

---

## 6. Notas para el vídeo de entrega

Guion sugerido (5–10 min):

1. Qué es la aplicación y qué tablas tiene la base de datos.
2. Recorrido como **invitado**: catálogo, búsqueda, ordenación, paginación y
   ficha de detalle.
3. **Login** y explicación de las sesiones.
4. **CRUD** de productos (incluida la subida de imagen) y de fabricantes.
5. Repaso del **código y la estructura de carpetas** (este README ayuda).
6. Mención de la **seguridad**: consultas preparadas, hash de contraseñas y
   escape de la salida.
