# Restaurante — Obligatorio PHP

Aplicación web de menú de restaurante desarrollada en PHP puro con arquitectura MVC custom, base de datos SQLite y servidor de desarrollo integrado.

---

## Tecnologías

- **PHP 8.x** — sin frameworks (MVC propio)
- **SQLite** — base de datos local (`database/database.sqlite`)
- **HTML / CSS** — vistas en PHP + plantillas HTML
- **Servidor de desarrollo**: `php -S`

---

## Cómo correr el proyecto

```bash
/opt/homebrew/bin/php -S 0.0.0.0:8000 \
  -t /ruta/al/proyecto/restaurante/public \
  /ruta/al/proyecto/restaurante/public/index.php
```

Acceder en el navegador: http://localhost:8000

Desde otro dispositivo en la misma red: http://\<IP-de-la-Mac\>:8000

---

## Estructura del proyecto

```
restaurante/
├── public/
│   ├── index.php          # Front controller (punto de entrada)
│   ├── css/               # Estilos
│   └── images/            # Imágenes subidas por el admin
├── app/
│   ├── config/
│   │   └── Database.php   # Conexión PDO a SQLite
│   ├── controllers/
│   │   ├── MenuController.php
│   │   └── UsuarioController.php
│   ├── Models/
│   │   ├── MenuModel.php
│   │   └── UsuarioModel.php
│   ├── Helpers/
│   │   ├── Auth.php        # Autenticación y autorización centralizada
│   │   └── ImageUploader.php
│   └── views/
│       ├── home.php
│       ├── home.template.html
│       ├── login.php
│       ├── registro.php
│       ├── 403.php
│       └── menu/
│           ├── crear.php
│           └── editar.php
└── database/
    └── database.sqlite
```

---

## Rutas disponibles

| URL | Descripción | Requiere |
|-----|-------------|----------|
| `/` | Menú principal | — |
| `/?controller=Usuario&action=login` | Iniciar sesión | — |
| `/?controller=Usuario&action=registro` | Registrarse | — |
| `/?controller=Usuario&action=logout` | Cerrar sesión | Login |
| `/?controller=Menu&action=crear` | Crear plato | **Admin** |
| `/?controller=Menu&action=editar&id=X` | Editar plato | **Admin** |
| `/?controller=Menu&action=eliminar` (POST) | Eliminar plato | **Admin** |

---

## Credenciales de prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | admin@localhost | admin |
| Cliente | (registrarse) | — |

---

## Funcionalidades

- Registro e inicio de sesión con hash de contraseña (password_hash)
- Protección CSRF en todos los formularios POST
- Control de acceso por rol: rutas admin protegidas centralmente en index.php
- CRUD de platos del menú (solo admin)
- Upload de imágenes desde el dispositivo (JPEG, PNG, GIF, WebP — máx. 5 MB)
- Eliminación automática de imagen al borrar/actualizar un plato
- Vista de error 403 para accesos no autorizados
