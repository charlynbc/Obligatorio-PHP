# Explicación completa del proyecto — Restaurante PHP

---

## ¿Qué es este proyecto?

Una aplicación web de restaurante construida en **PHP puro con arquitectura MVC** (Modelo-Vista-Controlador) **sin usar ningún framework**. Los únicos "extras" que se usan son:

- **SQLite** como base de datos (un solo archivo `.sqlite`, sin servidor de BD)
- **Bootstrap 5.3.3** y **Bootstrap Icons 1.11.3** para el diseño visual (cargados desde CDN)
- **PDO** (clase nativa de PHP) para hablar con la base de datos de forma segura

---

## Cómo arrancar el servidor

```bash
PORT=8000 /opt/homebrew/bin/php -S 0.0.0.0:8000 \
  -t /Users/charly/Documents/GitHub/Obligatorio-PHP/restaurante/public \
  /Users/charly/Documents/GitHub/Obligatorio-PHP/restaurante/public/index.php
```

- `-S 0.0.0.0:8000` → escucha en todas las interfaces, puerto 8000
- `-t .../public` → la raíz web es la carpeta `public/`
- el último argumento es el "router" que redirige todo al front controller

Acceder en el navegador: `http://localhost:8000` o `http://192.168.1.21:8000` desde otro dispositivo en la misma red.

---

## Estructura de carpetas — dónde está cada cosa

```
restaurante/
├── public/
│   └── index.php          ← PUNTO DE ENTRADA ÚNICO (front controller)
│   └── images/            ← Imágenes subidas por el admin
│   └── css/               ← CSS extra (home.css)
│
├── app/
│   ├── config/
│   │   └── Database.php   ← Clase que abre la conexión PDO a SQLite
│   │
│   ├── Helpers/
│   │   ├── Auth.php        ← Control de sesiones y permisos
│   │   └── ImageUploader.php ← Sube imágenes al servidor
│   │
│   ├── Models/
│   │   ├── MenuModel.php   ← Consultas de la tabla "platos"
│   │   ├── UsuarioModel.php ← Consultas de la tabla "users"
│   │   ├── CarritoModel.php ← Consultas de "carrito_items"
│   │   ├── CompraModel.php  ← Consultas de "compras" y "compra_detalles"
│   │   └── User.php        ← Clase de usuario (auxiliar)
│   │
│   ├── controllers/
│   │   ├── MenuController.php     ← CRUD de platos
│   │   ├── UsuarioController.php  ← Login, registro, logout
│   │   └── CarritoController.php  ← Ver carrito, agregar, restar, pagar
│   │
│   └── views/
│       ├── home.template.html ← Esqueleto HTML de la página principal
│       ├── home.php           ← Genera las cards del menú + navbar
│       ├── login.php          ← Formulario de login
│       ├── registro.php       ← Formulario de registro
│       ├── 403.php            ← Página de acceso denegado
│       └── menu/
│           ├── crear.php      ← Formulario de creación de plato (admin)
│           └── editar.php     ← Formulario de edición de plato (admin)
│       └── carrito/
│           ├── index.php      ← Vista del carrito con tabla de ítems
│           └── comprobante.php ← Ticket de confirmación de compra
│
├── database/
│   ├── database.sqlite        ← BASE DE DATOS (archivo único SQLite)
│   └── migrations/            ← Definiciones de tablas (solo documentación)
│
└── Explicacion.md             ← Este archivo
```

---

## Cómo funciona el sistema MVC paso a paso

### 1. El usuario hace una petición

Ejemplo: el usuario abre `http://localhost:8000/?controller=Carrito&action=index`

### 2. Todo entra por `public/index.php`

Este archivo es el **front controller**: recibe TODAS las peticiones.

```php
$controllerName = isset($_GET['controller']) ? $_GET['controller'] . 'Controller' : 'MenuController';
$actionName     = isset($_GET['action'])     ? $_GET['action'] : 'index';
```

Lee `?controller=` y `?action=` de la URL. Si no hay nada, usa `MenuController` e `index` por defecto (es decir, la página principal).

### 3. Verifica permisos de admin

```php
$adminOnlyRoutes = [
    'Menu' => ['crear', 'guardar', 'editar', 'actualizar', 'eliminar'],
];
```

Si la ruta pedida está en este mapa, llama a `Auth::requireAdmin()` antes de cargar el controlador. Así no importa si alguien adivina la URL: si no es admin, ve la página 403.

### 4. Carga el controlador y ejecuta la acción

```php
require_once $controllerPath;
$controller = new $controllerName();
$controller->$actionName();
```

Equivale a: `new CarritoController()` → llama a `index()`.

### 5. El controlador habla con el modelo

El controlador crea una instancia del modelo y llama métodos:

```php
$items = $this->carrito->getByUser($userId);
```

El modelo ejecuta SQL con PDO y devuelve arrays de datos.

### 6. El controlador carga la vista

```php
require_once BASE_PATH . 'app/views/carrito/index.php';
```

Las variables declaradas antes del `require_once` quedan disponibles dentro de la vista porque PHP comparte el mismo scope.

### 7. La vista genera HTML y lo manda al navegador

La vista solo se ocupa de presentar los datos. No hace consultas a la BD.

---

## La base de datos — tablas y relaciones

Archivo: `database/database.sqlite`

### `users` — usuarios registrados
| columna | tipo | descripción |
|---------|------|-------------|
| id | integer PK | identificador único |
| name | text | nombre del usuario |
| email | text unique | email (también sirve de login) |
| password | text | contraseña hasheada con `password_hash()` |
| role | text | `'admin'` o `'cliente'` |

### `platos` — los platos del menú
| columna | tipo | descripción |
|---------|------|-------------|
| id | integer PK | identificador único |
| nombre | text | nombre del plato |
| descripcion | text | descripción |
| precio | numeric | precio en pesos |
| categoria | text | ej: Entradas, Platos principales, Postres |
| imagen_url | text | ruta relativa a la imagen en `public/images/` |

### `carrito_items` — carrito de cada usuario
| columna | tipo | descripción |
|---------|------|-------------|
| id | integer PK | identificador único |
| user_id | integer FK→users | a quién pertenece |
| plato_id | integer FK→platos | qué plato |
| cantidad | integer | cuántas unidades |
| UNIQUE(user_id, plato_id) | — | no puede haber dos filas del mismo plato para el mismo usuario |

### `compras` — cabecera de cada compra
| columna | tipo | descripción |
|---------|------|-------------|
| id | integer PK | identificador único |
| user_id | integer FK→users | quién compró |
| total | numeric | total de la compra |
| created_at | datetime | cuándo se realizó |

### `compra_detalles` — líneas de cada compra
| columna | tipo | descripción |
|---------|------|-------------|
| id | integer PK | identificador único |
| compra_id | integer FK→compras | a qué compra pertenece |
| plato_id | integer FK→platos | qué plato se compró |
| cantidad | integer | cuántas unidades |
| precio_unitario | numeric | precio al momento de la compra |

---

## Sistema de autenticación — `app/Helpers/Auth.php`

Esta clase estática centraliza todo el control de sesiones:

| método | qué hace |
|--------|----------|
| `Auth::isLoggedIn()` | retorna `true` si hay `$_SESSION['user_id']` |
| `Auth::isAdmin()` | retorna `true` si el rol de sesión es `'admin'` |
| `Auth::requireLogin()` | si no está logueado, redirige al login |
| `Auth::requireAdmin()` | si no es admin, muestra 403 y termina |
| `Auth::userId()` | devuelve el `id` del usuario logueado |
| `Auth::userName()` | devuelve el `name` del usuario logueado |

**La sesión guarda:**
```php
$_SESSION['user_id']    // ID en la BD
$_SESSION['user_name']  // Nombre a mostrar
$_SESSION['user_email'] // Email
$_SESSION['user_role']  // 'admin' o 'cliente'
$_SESSION['csrf_token'] // Token anti-CSRF
```

Al hacer login se llama a `session_regenerate_id(true)` para evitar session fixation.

---

## Protección CSRF

Todos los formularios POST incluyen un campo oculto:

```html
<input type="hidden" name="csrf_token" value="...">
```

El token se genera al inicio de la sesión con `bin2hex(random_bytes(32))` y se verifica en cada POST con `hash_equals()` (comparación constante, sin timing attacks).

---

## Flujo completo de un usuario cliente

1. **Entra al sitio** → ve las cards con los platos del menú
2. **Se registra** en `/?controller=Usuario&action=registro` con nombre, email y contraseña
3. **Inicia sesión** en `/?controller=Usuario&action=login`
4. **Agrega platos** al carrito con el botón en cada card (POST a `?controller=Carrito&action=agregar`)
5. **Ve su carrito** en `/?controller=Carrito&action=index` — puede sumar, restar o eliminar ítems
6. **Confirma la compra** con el botón "Pagar ahora" (POST a `?controller=Carrito&action=pagar`)
7. **Ve el comprobante** con el detalle de lo que compró y el total

---

## Flujo completo del administrador

**Credenciales por defecto:** `admin@localhost` / `admin`

1. **Inicia sesión** igual que cualquier usuario
2. **Ve botones extra** en cada card: Editar y Eliminar
3. **Ve botón "Agregar plato"** en la navbar
4. **Crea platos** en `/?controller=Menu&action=crear` — con nombre, descripción, precio, categoría e imagen
5. **Edita platos** en `/?controller=Menu&action=editar&id=X`
6. **Elimina platos** (también borra la imagen física del servidor)

El admin NO ve el botón "Agregar al carrito" porque no tiene sentido que compre.

---

## Upload de imágenes — `app/Helpers/ImageUploader.php`

- Solo acepta: JPEG, PNG, GIF, WebP
- Tamaño máximo: 5MB
- El nombre del archivo guardado es el MD5 del contenido original (evita colisiones y no expone el nombre original)
- Se guarda en `public/images/`
- Al editar un plato y subir nueva imagen, la imagen anterior se borra del disco

---

## Sistema de rutas — cómo se arman las URLs

No hay archivo de rutas separado. Todo se maneja con parámetros GET:

| URL | controlador | acción |
|-----|-------------|--------|
| `/` | MenuController | index |
| `/?controller=Usuario&action=login` | UsuarioController | login |
| `/?controller=Usuario&action=registro` | UsuarioController | registro |
| `/?controller=Usuario&action=logout` | UsuarioController | logout |
| `/?controller=Menu&action=crear` | MenuController | crear |
| `/?controller=Menu&action=editar&id=5` | MenuController | editar |
| `/?controller=Carrito&action=index` | CarritoController | index |
| `/?controller=Carrito&action=pagar` | CarritoController | pagar |
| `/?controller=Carrito&action=comprobante&id=3` | CarritoController | comprobante |

---

## Tecnologías usadas y por qué

| tecnología | para qué se usa |
|------------|-----------------|
| PHP 8.x | lenguaje del servidor |
| SQLite | base de datos sin necesidad de servidor separado |
| PDO | acceso seguro a SQLite con prepared statements (previene SQL injection) |
| Bootstrap 5.3.3 (CDN) | diseño responsive sin escribir CSS desde cero |
| Bootstrap Icons 1.11.3 (CDN) | íconos en botones y navbar |
| `password_hash()` / `password_verify()` | hasheo seguro de contraseñas (bcrypt) |
| `hash_equals()` | comparación constante de tokens CSRF |
| `session_regenerate_id()` | previene session fixation tras login |

---

## Preguntas frecuentes en una defensa

**¿Qué es MVC?**
Es un patrón de arquitectura. El **Modelo** maneja los datos (SQL), la **Vista** solo muestra HTML, el **Controlador** conecta ambos y decide qué hacer con cada petición.

**¿Por qué usar un front controller?**
Porque todo pasa por un solo punto de entrada (`public/index.php`). Así es fácil agregar validaciones globales (CSRF, sesión, permisos) sin repetir código en cada página.

**¿Por qué SQLite y no MySQL?**
Para facilitar el desarrollo local: no necesita instalar ni configurar un servidor de base de datos. La BD es un archivo `.sqlite` que se puede copiar y compartir directamente.

**¿Cómo se previene la inyección SQL?**
Con PDO y **prepared statements** (`prepare()` + `bindValue()`). Los valores nunca se concatenan directamente en el SQL.

**¿Cómo se hashean las contraseñas?**
Con `password_hash($password, PASSWORD_DEFAULT)` que usa bcrypt. Para verificar se usa `password_verify($input, $hash)`. Nunca se guarda la contraseña en texto plano.

**¿Qué es CSRF y cómo se protege?**
CSRF (Cross-Site Request Forgery) es un ataque donde un sitio externo hace un POST en nombre del usuario. Se previene generando un token secreto en la sesión y verificándolo en cada POST con `hash_equals()`.

**¿Cómo funciona el carrito?**
Cada ítem del carrito es una fila en `carrito_items` con `user_id` + `plato_id` + `cantidad`. La combinación `(user_id, plato_id)` tiene un índice UNIQUE, así que agregar el mismo plato dos veces solo aumenta la cantidad (upsert con `ON CONFLICT DO UPDATE`).

**¿Qué pasa cuando se paga?**
El controlador: 1) obtiene los ítems del carrito, 2) crea una fila en `compras` con el total, 3) crea una fila en `compra_detalles` por cada ítem (con el precio al momento de la compra), 4) vacía el carrito, 5) redirige al comprobante. Todo dentro de una transacción de BD para que o se guarda todo o no se guarda nada.
