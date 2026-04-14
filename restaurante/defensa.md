# Defensa del Proyecto — Restaurante con MVC en PHP

## 1. Resumen ejecutivo

Este proyecto implementa una aplicación web de restaurante siguiendo el patrón **MVC (Modelo - Vista - Controlador)** con **PHP nativo**.

El objetivo principal fue separar correctamente:

- la lógica de negocio,
- el acceso a datos,
- y la presentación visual.

La funcionalidad implementada y operativa es la **Homepage del restaurante**, que lista los platos disponibles desde la base de datos.

Además, el proyecto utiliza el concepto de **Front Controller**, es decir, un único punto de entrada para todas las peticiones web.

---

## 2. Qué tenía el proyecto al inicio

El repositorio ya contaba con una base estructural de Laravel:

- estructura de carpetas de Laravel,
- migraciones,
- archivo `artisan`,
- carpeta `vendor`,
- y configuración general del proyecto.

Además, la base de datos y su diseño ya habían sido realizados por el usuario. Eso incluye:

- el esquema de tablas,
- la lógica del modelo de datos,
- y el archivo SQLite ubicado en `database/database.sqlite`.

Sin embargo, para cumplir con el objetivo académico de **entender y construir el patrón MVC manualmente**, no se usó el sistema de controladores y rutas tradicional de Laravel para la funcionalidad principal.

En su lugar, se implementó una arquitectura MVC propia dentro del mismo proyecto, apoyándose en ese trabajo previo:

- la estructura ya existente,
- las migraciones,
- y la base de datos diseñada por el usuario.

Esto permite defender que el proyecto **no solo usa herramientas**, sino que además demuestra comprensión de cómo funciona MVC por dentro.

---

## 3. Tecnologías utilizadas

### Backend

- **PHP 8.3**
- **PHP nativo** para la implementación del patrón MVC
- **PDO** para el acceso seguro a base de datos

### Base de datos

- **SQLite** como motor activo del proyecto
- Archivo físico de base de datos en `database/database.sqlite`

### Infraestructura del proyecto

- **Laravel** como base estructural del repositorio
- Migraciones de Laravel para definir el esquema de tablas

### Frontend

- **HTML5**
- **CSS** embebido en la vista principal

### Ejecución local

- **Servidor embebido de PHP** con `php -S`
- Script de arranque `serve.sh`

---

## 4. Arquitectura elegida

Se implementó el patrón **MVC**.

### Modelo

El modelo es la capa que se conecta a la base de datos y ejecuta consultas.

En este proyecto:

- `app/config/Database.php` encapsula la conexión PDO.
- `app/models/MenuModel.php` consulta la tabla `platos`.

### Vista

La vista es la parte visual que renderiza HTML.

En este proyecto:

- `app/views/home.php` muestra el menú en pantalla.

### Controlador

El controlador recibe la petición, pide datos al modelo y carga la vista.

En este proyecto:

- `app/controllers/MenuController.php` coordina el flujo de la Homepage.

### Front Controller

El Front Controller es un archivo único que centraliza todas las peticiones.

En este proyecto:

- `public/index.php` recibe la URL,
- identifica el controlador y la acción,
- carga el archivo correcto,
- instancia la clase,
- y ejecuta el método solicitado.

Esto evita que el usuario entre directamente a archivos sueltos como `login.php`, `menu.php` o `carrito.php`.

---

## 5. Estructura del proyecto relevante para la defensa

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
│       ├── home.php              # Renderizador PHP (lógica de render)
│       └── home.template.html   # Plantilla HTML pura
├── database/
│   ├── database.sqlite
│   └── migrations/
├── public/
│   ├── index.php
│   ├── css/
│   │   └── home.css             # Estilos de la Homepage
│   ├── js/
│   └── img/
├── pasos.md
└── defensa.md
```

---

## 6. Qué se hizo paso a paso

### Paso 1 — Análisis del proyecto existente

Primero se revisó la estructura del repositorio para entender:

- qué framework tenía,
- cómo estaba organizada la base de datos,
- qué tablas existían,
- y cuál era el punto de entrada actual.

Se detectó que el proyecto tenía una base Laravel como soporte estructural, y que la base de datos ya había sido diseñada y creada por el usuario.

### Paso 2 — Creación de la estructura MVC

Se crearon las carpetas necesarias para separar responsabilidades:

- `app/config`
- `app/controllers`
- `app/models`
- `app/views`
- `public/css`
- `public/js`
- `public/img`

La intención fue ordenar el proyecto para que cada parte tenga una responsabilidad clara.

### Paso 3 — Implementación de la conexión a base de datos

Se creó la clase `Database` en `app/config/Database.php`.

Originalmente la idea era usar MySQL con PDO, pero al ejecutar el proyecto apareció el error:

`could not find driver`

Al diagnosticar el entorno, se detectó que:

- el driver `pdo_mysql` no estaba operativo en el PHP activo,
- pero sí estaba disponible `pdo_sqlite`,
- y además ya estaba disponible la base `database.sqlite` creada por el usuario.

Por ese motivo, se adaptó la clase `Database` para conectarse mediante PDO a esa base SQLite ya construida.

Esta decisión fue técnica y pragmática: permitió ejecutar la aplicación correctamente sin depender de una instalación externa de MySQL.

### Paso 4 — Implementación del Front Controller

Se reemplazó el comportamiento de `public/index.php` para que actúe como Front Controller MVC.

Su trabajo es:

1. definir la constante `BASE_PATH`,
2. cargar la conexión,
3. leer `controller` y `action` desde la URL,
4. buscar el controlador correspondiente,
5. instanciarlo,
6. y ejecutar la acción.

Si el controlador o la acción no existen, el sistema responde con un error 404 simple.

### Paso 5 — Implementación del modelo del menú

Se creó `app/models/MenuModel.php`.

Responsabilidades:

- abrir conexión a la BD,
- preparar la consulta,
- ejecutar `SELECT * FROM platos`,
- devolver los resultados como arreglo asociativo.

Este archivo no contiene HTML ni manejo de rutas. Solo acceso a datos.

### Paso 6 — Implementación del controlador del menú

Se creó `app/controllers/MenuController.php`.

Responsabilidades:

- instanciar `MenuModel`,
- pedir todos los platos,
- guardar el resultado en `$menus`,
- cargar la vista `home.php`.

El controlador conecta el modelo con la vista.

### Paso 7 — Implementación de la vista principal

Se creó `app/views/home.php`.

La vista:

- muestra un encabezado,
- renderiza tarjetas para cada plato,
- imprime nombre, categoría, descripción, precio e imagen si existe,
- y muestra un mensaje alternativo si no hay platos.

Además se utilizó `htmlspecialchars()` para evitar problemas de XSS al imprimir datos en HTML.

### Paso 8 — Creación del script de ejecución

Se adaptó `serve.sh` para ejecutar el proyecto con el servidor embebido de PHP:

```bash
php -S 0.0.0.0:8000 -t public public/index.php
```

Esto hace que:

- el directorio público sea `public/`,
- el router principal sea `public/index.php`,
- y toda la app arranque con un solo comando.

### Paso 9 — Inserción de datos de prueba

La estructura de la base ya estaba creada por el usuario. Para validar visualmente la Homepage, se agregaron datos de prueba sobre la tabla `platos`.

Se insertaron platos de ejemplo en SQLite para poder validar visualmente el flujo completo:

- Hamburguesa Clásica
- Papas Fritas con Cheddar
- Milanesa Napolitana
- Limonada Natural

Estos datos quedan guardados en `database/database.sqlite` y persisten mientras no se borre o regenere esa base.

### Paso 10 — Verificación del funcionamiento

Se realizó una prueba real levantando el servidor y consultando la Home.

La verificación confirmó que se renderizaron correctamente los cuatro platos, lo que demuestra que el flujo MVC está operativo de punta a punta.

---

## 7. Flujo completo de una petición

Cuando el usuario entra a la aplicación ocurre lo siguiente:

```text
1. El navegador solicita la URL principal.
2. El servidor dirige la petición a public/index.php.
3. public/index.php actúa como Front Controller.
4. Si no se especifica nada en la URL, usa MenuController e index.
5. Se carga MenuController.
6. MenuController crea una instancia de MenuModel.
7. MenuModel usa Database para conectarse a SQLite.
8. Se ejecuta SELECT * FROM platos.
9. Los resultados vuelven al controlador.
10. El controlador carga home.php.
11. La vista recorre el arreglo de platos y genera el HTML.
12. El usuario ve el menú en pantalla.
```

---

## 8. Explicación de cada archivo importante

### `public/index.php`

Es el archivo más importante de la aplicación.

Funciones principales:

- definir la ruta base del proyecto,
- interpretar la URL,
- resolver el controlador,
- resolver la acción,
- y ejecutar la lógica correspondiente.

### `app/config/Database.php`

Encapsula la conexión con PDO.

Ventajas:

- centraliza la conexión,
- evita repetir código,
- facilita futuros cambios de motor,
- y mejora el mantenimiento.

### `app/models/MenuModel.php`

Contiene la lógica de acceso a datos del menú.

Ventajas:

- separa SQL del resto de la aplicación,
- facilita agregar filtros, ordenamientos o búsquedas más adelante.

### `app/controllers/MenuController.php`

Coordina la operación de la Homepage.

Ventajas:

- organiza la lógica del flujo,
- mantiene desacoplada la vista,
- y permite escalar a más acciones futuras.

### `app/views/home.php`

Renderiza la página visible para el usuario.

Ventajas:

- separa presentación de lógica,
- evita mezclar SQL con HTML,
- y simplifica el mantenimiento visual.

### `serve.sh`

Permite iniciar el programa con un único comando desde la raíz del repositorio.

---

## 9. Base de datos del proyecto

La base de datos del proyecto fue diseñada y creada por el usuario.

La aplicación usa actualmente **SQLite** para conectarse a esa base.

Archivo de la base:

`database/database.sqlite`

Tabla principal usada por la Homepage:

### `platos`

Columnas relevantes:

- `id`
- `nombre`
- `descripcion`
- `precio`
- `categoria`
- `imagen_url`
- `created_at`
- `updated_at`

Esta tabla es suficiente para cumplir con la funcionalidad de mostrar el menú.

---

## 10. Seguridad y buenas prácticas aplicadas

### Uso de PDO

Se usa PDO como capa de acceso a datos.

Beneficios:

- acceso unificado a base de datos,
- consultas preparadas,
- mejor manejo de errores,
- y mejor mantenimiento.

### Uso de `prepare()`

Aunque la consulta actual no tiene parámetros externos, se utiliza `prepare()` como buena práctica y base para consultas futuras.

### Uso de `htmlspecialchars()`

Se aplica en la vista para escapar el contenido antes de imprimirlo en HTML.

Esto reduce el riesgo de **Cross-Site Scripting (XSS)**.

### Uso de `file_exists()` y `method_exists()`

En el Front Controller se valida que:

- el archivo del controlador exista,
- la clase pueda cargarse,
- y el método solicitado exista.

Esto evita errores más graves y mejora el control del flujo.

### Uso de `BASE_PATH`

Se definió una ruta base absoluta para que los `require_once` no dependan de rutas relativas frágiles.

Esto mejora la estabilidad del proyecto.

---

## 11. Cómo se ejecuta el proyecto

Desde la raíz del repositorio:

```bash
bash serve.sh
```

El script hace lo siguiente:

1. entra a la carpeta `restaurante`,
2. levanta el servidor de PHP,
3. publica la app en el puerto `8000`,
4. y usa `public/index.php` como router.

Luego se abre en el navegador:

```text
http://localhost:8000
```

---

## 12. Qué se puede mostrar en la defensa en vivo

### Demostración recomendada

1. Mostrar la estructura de carpetas MVC.
2. Abrir `public/index.php` y explicar el Front Controller.
3. Abrir `MenuController.php` y explicar cómo coordina el flujo.
4. Abrir `MenuModel.php` y explicar la consulta a `platos`.
5. Abrir `home.php` y mostrar cómo renderiza los datos.
6. Ejecutar `bash serve.sh`.
7. Abrir la Home y mostrar los platos cargados desde la base de datos.

---

## 13. Qué decisiones técnicas conviene justificar

### ¿Por qué usar MVC?

Porque separa responsabilidades:

- el modelo maneja datos,
- la vista muestra información,
- el controlador coordina el flujo.

Esto hace el proyecto más ordenado, mantenible y escalable.

### ¿Por qué usar un Front Controller?

Porque centraliza todas las peticiones en un solo punto de entrada.

Ventajas:

- control uniforme del flujo,
- menor acoplamiento,
- mejor organización,
- y facilidad para agregar validaciones o middleware a futuro.

### ¿Por qué usar PDO?

Porque ofrece una capa estándar y segura para conectarse a la base de datos.

Además facilita el uso de sentencias preparadas y manejo de excepciones.

### ¿Por qué usar SQLite en lugar de MySQL?

Porque en el entorno real de ejecución el driver MySQL no estaba operativo, mientras que SQLite ya estaba disponible y la base creada por el usuario podía usarse directamente.

La decisión permitió que el proyecto funcionara correctamente de forma inmediata, manteniendo el uso de PDO y sin alterar el diseño MVC.

### ¿Por qué mantener Laravel en el proyecto si el MVC es manual?

Porque Laravel ya formaba parte del repositorio y aportaba estructura útil, especialmente migraciones y organización general.

La implementación manual del MVC demuestra aprendizaje de bajo nivel, mientras que la base del proyecto aprovecha herramientas ya existentes.

---

## 14. Limitaciones actuales

La funcionalidad implementada actualmente cubre la Homepage del menú, pero todavía faltan módulos para completar una aplicación más grande.

Pendientes razonables:

- login de usuarios,
- registro,
- manejo de sesión,
- favoritos,
- carrito,
- compras,
- panel administrador,
- alta, baja y modificación de platos.

Esto no invalida la arquitectura. Al contrario: la arquitectura quedó preparada para crecer agregando más controladores, modelos y vistas.

---

## 15. Posibles mejoras futuras

- Crear más controladores: `UsuarioController`, `CarritoController`, `FavoritosController`.
- Agregar métodos al modelo para filtros y ordenamientos.
- Mover estilos a archivos CSS externos.
- Incorporar validación de parámetros en el router.
- Agregar manejo de errores más amigable.
- Implementar seeders para que los datos de prueba se regeneren automáticamente.
- Agregar autenticación con contraseña hasheada.

---

## 16. Preguntas posibles del tribunal y respuestas cortas

### ¿Dónde está implementado el patrón MVC?

En las carpetas `app/models`, `app/views` y `app/controllers`, coordinadas desde `public/index.php`.

### ¿Cuál es el punto de entrada de la aplicación?

`public/index.php`, que funciona como Front Controller.

### ¿Cómo llega un plato desde la BD hasta la pantalla?

La petición entra por `public/index.php`, se ejecuta `MenuController`, este llama a `MenuModel`, el modelo consulta `platos`, devuelve los datos, y la vista `home.php` los renderiza.

### ¿Qué motor de base de datos usa?

SQLite, a través de PDO.

### ¿Qué tabla usa la Homepage?

La tabla `platos`.

### ¿Qué medidas de seguridad hay?

PDO, `prepare()`, `htmlspecialchars()`, validación de existencia de archivos y métodos, y uso de `BASE_PATH` para rutas estables.

### ¿Qué parte es Laravel y qué parte es propia?

Laravel aporta la base del repositorio. La base de datos y su diseño fueron realizados por el usuario. La lógica principal mostrada en la Homepage fue implementada manualmente con MVC propio sobre esa base.

---

## 17. Frase de cierre para la defensa

La parte más importante de este trabajo no es solo que la Homepage funcione, sino que quedó implementada sobre una arquitectura clara y explicable. El proyecto demuestra comprensión real de MVC, del patrón Front Controller, del acceso seguro a datos con PDO y de cómo organizar una aplicación web para que pueda crecer de forma mantenible.

---

## 18. Separación de capas PHP / HTML / CSS

Una decisión de diseño que vale resaltar en la defensa es la separación estricta entre:

- **PHP** (`app/views/home.php`) — construye el contenido dinámico y reemplaza placeholders.
- **HTML** (`app/views/home.template.html`) — estructura pura, sin bloques PHP. Solo define dónde van los datos con marcadores como `{{MENU_CARDS}}`.
- **CSS** (`public/css/home.css`) — estilos externos vinculados desde la plantilla.

Esta separación facilita modificar el diseño visual sin tocar código PHP, y modificar la lógica sin tocar HTML. Es la base del mantenimiento profesional de un proyecto web.

### Fix de servicio de archivos estáticos

Al usar el servidor embebido de PHP (`php -S`) con un router (`index.php`), por defecto **todas** las peticiones pasan por ese router, incluyendo `.css` y `.js`. Eso hacía que el CSS no llegara al navegador.

Se solucionó agregando al inicio de `public/index.php`:

```php
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false;
}
```

Esto le dice al servidor: si la petición corresponde a un archivo real en disco, servilo directamente sin pasar por el Front Controller.