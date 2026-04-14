# Manual del Proyecto — Restaurante MVC en PHP

## Qué es este proyecto

Este proyecto es una aplicación web de restaurante construida con PHP nativo usando el patrón MVC.

El repositorio tiene una base estructural de Laravel, pero la funcionalidad principal implementada hasta ahora se resolvió con una arquitectura MVC propia para demostrar cómo funciona el patrón internamente.

La base de datos y su diseño fueron realizados por el usuario. Sobre esa base ya creada se integró la aplicación MVC.

---

## Tecnologías usadas

- PHP 8.3
- PHP nativo
- PDO
- SQLite
- HTML5
- CSS
- Laravel como estructura base del repositorio

---

## Estructura principal del proyecto

```text
restaurante/
├── app/
│   ├── config/
│   │   └── Database.php
│   ├── controllers/
│   │   └── MenuController.php
│   ├── models/
│   │   └── MenuModel.php
│   └── views/
│       └── home.php
├── database/
│   ├── database.sqlite
│   └── migrations/
├── public/
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── img/
└── ...
```

---

## Qué se hizo paso a paso

### Paso 1 — Revisión del proyecto base

Se revisó la estructura del repositorio para identificar:

- el punto de entrada web,
- la organización del proyecto,
- las tablas disponibles,
- y la base de datos ya creada por el usuario.

### Paso 2 — Organización MVC

Se ordenó el proyecto con carpetas separadas para:

- configuración,
- controladores,
- modelos,
- vistas,
- y archivos públicos.

Se trabajó principalmente sobre:

- `app/config`
- `app/controllers`
- `app/models`
- `app/views`
- `public`

### Paso 3 — Conexión a la base de datos

Se implementó [restaurante/app/config/Database.php](restaurante/app/config/Database.php) para centralizar la conexión PDO.

La base de datos ya había sido hecha por el usuario. La adaptación realizada consistió en conectar la aplicación a esa base existente.

En el entorno actual, MySQL no estaba operativo por un problema de driver, por lo que se usó SQLite, que ya estaba disponible y era compatible con la base existente.

### Paso 4 — Front Controller

Se implementó el Front Controller en [restaurante/public/index.php](restaurante/public/index.php).

Ese archivo:

- define `BASE_PATH`,
- lee `controller` y `action` desde la URL,
- localiza el controlador correspondiente,
- lo carga,
- lo instancia,
- y ejecuta la acción solicitada.

Si no se pasan parámetros, por defecto carga:

- `MenuController`
- acción `index`

### Paso 5 — Modelo

Se creó [restaurante/app/models/MenuModel.php](restaurante/app/models/MenuModel.php).

Ese modelo:

- abre la conexión,
- prepara la consulta,
- ejecuta `SELECT * FROM platos`,
- y devuelve los resultados como arreglo asociativo.

### Paso 6 — Controlador

Se creó [restaurante/app/controllers/MenuController.php](restaurante/app/controllers/MenuController.php).

Ese controlador:

- instancia `MenuModel`,
- solicita todos los platos,
- guarda el resultado en `$menus`,
- y carga la vista principal.

### Paso 7 — Vista

Se creó [restaurante/app/views/home.php](restaurante/app/views/home.php).

La vista:

- recorre los platos recibidos,
- genera una tarjeta por cada plato,
- muestra nombre, categoría, descripción, precio e imagen si existe,
- y usa `htmlspecialchars()` para imprimir datos de forma segura.

### Paso 8 — Datos de prueba

Para validar la Homepage se insertaron platos de prueba en la tabla `platos` de la base ya creada por el usuario.

Ejemplos cargados:

- Hamburguesa Clásica
- Papas Fritas con Cheddar
- Milanesa Napolitana
- Limonada Natural

### Paso 9 — Script de ejecución

Se adaptó [serve.sh](serve.sh) para levantar el proyecto con el servidor embebido de PHP.

El comando base es:

```bash
php -S 0.0.0.0:8000 -t public public/index.php
```

### Paso 10 — Separación de capas PHP / HTML / CSS

Se refactorizó la vista para que cada tecnología tenga su propio archivo y responsabilidad:

- `app/views/home.php` — solo PHP: construye el contenido dinámico y lo inyecta en la plantilla.
- `app/views/home.template.html` — solo HTML: estructura pura con placeholders `{{LOGIN_URL}}`, `{{REGISTRO_URL}}` y `{{MENU_CARDS}}`.
- `public/css/home.css` — solo CSS: estilos visuales de la Homepage.

El renderizador PHP lee la plantilla con `file_get_contents()` y reemplaza los placeholders con `str_replace()` antes de imprimir el HTML final.

### Paso 11 — Fix de archivos estáticos

Al usar `php -S` con un router personalizado, el servidor redirige todas las peticiones por `index.php`, incluyendo archivos `.css`, `.js` e imágenes. Esto impedía que el CSS se cargara.

Se agregó al inicio de `public/index.php` una condición que detecta si la petición es un archivo real del disco y lo sirve directamente sin pasar por el Front Controller.

### Paso 12 — Verificación

Se verificó que:

- la Homepage renderiza los platos correctamente,
- el CSS se sirve con HTTP 200,
- y los estilos se aplican en el navegador.

---

## Cómo funciona el programa

```text
1. El usuario entra a http://localhost:8000
2. La petición llega a public/index.php
3. El Front Controller resuelve el controlador y la acción
4. Se ejecuta MenuController::index()
5. El controlador llama a MenuModel
6. El modelo consulta la tabla platos
7. Los datos vuelven al controlador
8. El controlador carga home.php
9. La vista renderiza el HTML
10. El usuario ve el menú en pantalla
```

---

## Base de datos usada

La base de datos del proyecto fue realizada por el usuario.

La aplicación se conecta actualmente a:

- SQLite

Archivo usado:

- [restaurante/database/database.sqlite](restaurante/database/database.sqlite)

Tabla principal usada por la Homepage:

- `platos`

Campos relevantes:

- `nombre`
- `descripcion`
- `precio`
- `categoria`
- `imagen_url`

---

## Cómo ejecutar la aplicación

Desde la raíz del repositorio:

```bash
bash serve.sh
```

Luego abrir:

```text
http://localhost:8000
```

El script:

1. entra en la carpeta `restaurante`,
2. levanta el servidor embebido de PHP,
3. usa `public` como directorio público,
4. y usa [restaurante/public/index.php](restaurante/public/index.php) como router.

---

## Seguridad aplicada

- PDO para el acceso a datos
- `prepare()` como base para consultas seguras
- `htmlspecialchars()` para prevenir XSS en la vista
- `file_exists()` para validar controladores
- `method_exists()` para validar acciones
- `BASE_PATH` para estabilizar las rutas internas

---

## Estado actual respecto al obligatorio del PDF

Lo que está cubierto hasta ahora es:

- ver la lista de menú en la Homepage

Lo que todavía falta para completar el obligatorio mínimo es:

- registro de usuario
- inicio de sesión
- agregar menú solo admin
- modificar menú solo admin
- eliminar menú solo admin

Además, para la entrega final, el PDF también pide:

- un README con instrucciones detalladas
- una demo en video

---

## Conclusión

Hasta este punto ya quedó validada la arquitectura base del sistema:

- el MVC funciona,
- el Front Controller funciona,
- la conexión a la base funciona,
- y la Homepage ya consume datos reales desde la tabla `platos`.

La base de datos fue hecha por el usuario, y el trabajo realizado sobre ella consistió en integrar la aplicación MVC, la navegación centralizada y la ejecución del sistema.