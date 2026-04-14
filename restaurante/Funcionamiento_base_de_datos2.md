# Funcionamiento de la Base de Datos

## Diagrama de Relaciones

```
users (1) ──────┬──── (N) favoritos (N) ────── (1) platos
                │
                ├──── (N) carrito_items (N) ──── (1) platos
                │
                └──── (N) compras (1) ──── (N) compra_detalles (N) ──── (1) platos
```

---

## 1. Tabla `users`

**Archivo:** `migrations/0001_01_01_000000_create_users_table.php`

Almacena a todos los usuarios del sistema (administradores y clientes).

| Columna | Tipo | Propósito |
|---------|------|-----------|
| `id` | BIGINT (PK, AI) | Identificador único del usuario. |
| `name` | VARCHAR(255) | Nombre completo del usuario. |
| `email` | VARCHAR(255) UNIQUE | Correo electrónico. Se usa para iniciar sesión. El `UNIQUE` impide que dos usuarios se registren con el mismo email. |
| `email_verified_at` | TIMESTAMP (nullable) | Fecha en que el usuario verificó su email. Nullable porque no todos verifican. |
| `password` | VARCHAR(255) | Contraseña hasheada. Laravel nunca guarda la contraseña en texto plano. |
| `role` | VARCHAR(20) default `'cliente'` | **Clave para el obligatorio.** Distingue entre `'admin'` y `'cliente'`. Por defecto todo usuario nuevo es cliente. Solo el admin puede agregar/modificar/eliminar platos del menú. |
| `remember_token` | VARCHAR(100) (nullable) | Token para la funcionalidad "Recuérdame" al iniciar sesión. |
| `created_at` | TIMESTAMP | Fecha de registro del usuario. |
| `updated_at` | TIMESTAMP | Fecha de la última modificación del perfil. |

**Tablas auxiliares creadas en la misma migración:**

- **`password_reset_tokens`** — Guarda tokens temporales cuando un usuario solicita restablecer su contraseña.
- **`sessions`** — Registra las sesiones activas de los usuarios (quién está conectado, desde qué IP, etc.).

---

## 2. Tabla `platos`

**Archivo:** `migrations/0001_01_01_000001_create_platos_table.php`

Almacena todos los platos del menú del restaurante. Solo un admin puede crear, modificar o eliminar registros aquí.

| Columna | Tipo | Propósito |
|---------|------|-----------|
| `id` | BIGINT (PK, AI) | Identificador único del plato. |
| `nombre` | VARCHAR(100) | Nombre del plato (ej. "Hamburguesa Completa"). Permite cumplir el requisito de **ordenar alfabéticamente**. |
| `descripcion` | TEXT | Detalle de ingredientes, alérgenos o preparación. Se usa TEXT en vez de VARCHAR porque soporta cadenas mucho más largas. |
| `precio` | DECIMAL(10,2) | Precio del plato con 2 decimales fijos. **Nunca se guarda dinero como VARCHAR o INT.** DECIMAL garantiza matemáticas precisas, permite **ordenar por precio** y sumar totales en el carrito. |
| `categoria` | VARCHAR(50) (nullable) | Para agrupar platos en la Homepage (ej. "Entradas", "Platos Principales", "Bebidas"). Nullable porque un plato puede crearse sin categoría inicialmente. |
| `imagen_url` | VARCHAR(255) (nullable) | Ruta a la imagen del plato (ej. `assets/img/hamburguesa.jpg`). Se guarda la ruta, no el archivo, porque es mucho más eficiente. |
| `created_at` | TIMESTAMP | Fecha en que se agregó el plato al menú. |
| `updated_at` | TIMESTAMP | Fecha de la última modificación. |

---

## 3. Tabla `favoritos`

**Archivo:** `migrations/0001_01_01_000002_create_favoritos_table.php`

Relación muchos-a-muchos entre usuarios y platos. Un cliente puede marcar varios platos como favoritos, y un plato puede ser favorito de varios clientes.

| Columna | Tipo | Propósito |
|---------|------|-----------|
| `id` | BIGINT (PK, AI) | Identificador único del registro. |
| `user_id` | BIGINT (FK → users.id) | El cliente que marcó el plato como favorito. |
| `plato_id` | BIGINT (FK → platos.id) | El plato marcado como favorito. |
| `created_at` | TIMESTAMP | Cuándo se agregó a favoritos. |
| `updated_at` | TIMESTAMP | Última modificación. |

**Restricciones:**
- `UNIQUE(user_id, plato_id)` — Un usuario no puede marcar el mismo plato como favorito dos veces.
- `ON DELETE CASCADE` — Si se elimina el usuario o el plato, el favorito se borra automáticamente.

---

## 4. Tabla `carrito_items`

**Archivo:** `migrations/0001_01_01_000003_create_carrito_items_table.php`

Almacena los platos que un cliente tiene en su carrito de compras antes de confirmar la compra.

| Columna | Tipo | Propósito |
|---------|------|-----------|
| `id` | BIGINT (PK, AI) | Identificador único del item. |
| `user_id` | BIGINT (FK → users.id) | El cliente dueño del carrito. |
| `plato_id` | BIGINT (FK → platos.id) | El plato agregado al carrito. |
| `cantidad` | INT UNSIGNED default `1` | Cuántas unidades de ese plato quiere. Unsigned porque no puede ser negativo. |
| `created_at` | TIMESTAMP | Cuándo se agregó al carrito. |
| `updated_at` | TIMESTAMP | Última modificación (ej. cambió la cantidad). |

**Restricciones:**
- `UNIQUE(user_id, plato_id)` — Si el cliente agrega el mismo plato dos veces, se incrementa la `cantidad` en vez de crear un registro duplicado.
- `ON DELETE CASCADE` — Si se elimina el usuario o el plato, el item del carrito desaparece.

---

## 5. Tablas `compras` y `compra_detalles`

**Archivo:** `migrations/0001_01_01_000004_create_compras_table.php`

Cuando un cliente confirma su carrito, se crea una **compra** con sus **detalles**. Esto permite ver el historial de compras.

### Tabla `compras` (cabecera de la compra)

| Columna | Tipo | Propósito |
|---------|------|-----------|
| `id` | BIGINT (PK, AI) | Identificador único de la compra. |
| `user_id` | BIGINT (FK → users.id) | El cliente que realizó la compra. |
| `total` | DECIMAL(10,2) | Monto total de la compra (suma de todos los detalles). |
| `created_at` | TIMESTAMP | **Fecha de la compra.** Se usa para ordenar el historial con la más reciente primero. |
| `updated_at` | TIMESTAMP | Última modificación. |

### Tabla `compra_detalles` (cada plato dentro de la compra)

| Columna | Tipo | Propósito |
|---------|------|-----------|
| `id` | BIGINT (PK, AI) | Identificador único del detalle. |
| `compra_id` | BIGINT (FK → compras.id) | A qué compra pertenece este detalle. |
| `plato_id` | BIGINT (FK → platos.id) | Qué plato se compró. |
| `cantidad` | INT UNSIGNED | Cuántas unidades se compraron. |
| `precio_unitario` | DECIMAL(10,2) | **Precio del plato al momento de la compra.** Esto es fundamental: si mañana el admin cambia el precio del plato, el historial de compras anteriores no se altera. |
| `created_at` | TIMESTAMP | Fecha del registro. |
| `updated_at` | TIMESTAMP | Última modificación. |

**Restricciones:**
- `ON DELETE CASCADE` en `compra_id` — Si se elimina una compra, se borran todos sus detalles.
- `ON DELETE CASCADE` en `plato_id` — Si se elimina un plato, se eliminan los detalles asociados.

---

## Flujo de Compra (cómo se conectan las tablas)

```
1. Cliente agrega platos → se insertan en `carrito_items`
2. Cliente confirma compra →
   a. Se crea un registro en `compras` (con el total)
   b. Cada item del carrito se copia a `compra_detalles` (con el precio actual)
   c. Se vacía `carrito_items` del usuario
   d. Se envía email con el resumen (requisito opcional)
3. Cliente ve historial → se consulta `compras` ORDER BY created_at DESC
```

---

## Cobertura de Requisitos

| Requisito | Tabla(s) involucrada(s) | Estado |
|-----------|------------------------|--------|
| Registro de Usuario (admin/cliente) | `users` (columna `role`) | ✅ Obligatorio |
| Inicio de Sesión | `users` + `sessions` | ✅ Obligatorio |
| Agregar Menú (Solo admin) | `platos` | ✅ Obligatorio |
| Modificar Menú (Solo admin) | `platos` | ✅ Obligatorio |
| Eliminar Menú (Solo admin) | `platos` | ✅ Obligatorio |
| Ver lista de menú en Homepage | `platos` | ✅ Obligatorio |
| Agregar/Eliminar de Favorito | `favoritos` | ✅ Opcional |
| Ordenar por precio/alfabéticamente | `platos` (columnas `precio` y `nombre`) | ✅ Opcional |
| Agregar/Eliminar del Carrito | `carrito_items` | ✅ Opcional |
| Realizar compra + email | `compras` + `compra_detalles` | ✅ Opcional |
| Ver historial de compras | `compras` + `compra_detalles` | ✅ Opcional |

Todo listo. La base de datos está configurada y funcionando:

.env creado + APP_KEY generada
5 migraciones ejecutadas correctamente sobre SQLite
Tabla users ahora incluye la columna role
Todas las tablas creadas: users, password_reset_tokens, sessions, platos, favoritos, carrito_items, compras, compra_detalles