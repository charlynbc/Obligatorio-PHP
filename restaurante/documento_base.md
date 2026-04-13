# Documento de Base de Datos - Restaurante

Este documento describe las tablas de la base de datos del proyecto Laravel "Restaurante". Las tablas se definen en las migraciones ubicadas en `database/migrations/`.

## Tablas

### 1. users
Tabla para almacenar usuarios del sistema.

| Columna              | Tipo          | Descripción |
|----------------------|---------------|-------------|
| id                   | bigint (auto-increment) | Identificador único |
| name                 | varchar(255) | Nombre del usuario |
| email                | varchar(255) (unique) | Correo electrónico único |
| email_verified_at    | timestamp (nullable) | Fecha de verificación del email |
| password             | varchar(255) | Contraseña hasheada |
| remember_token       | varchar(100) | Token para recordar sesión |
| created_at           | timestamp | Fecha de creación |
| updated_at           | timestamp | Fecha de actualización |

### 2. password_reset_tokens
Tabla para tokens de restablecimiento de contraseña.

| Columna              | Tipo          | Descripción |
|----------------------|---------------|-------------|
| email                | varchar(255) (primary) | Correo electrónico (clave primaria) |
| token                | varchar(255) | Token de restablecimiento |
| created_at           | timestamp (nullable) | Fecha de creación |

### 3. sessions
Tabla para sesiones de usuario.

| Columna              | Tipo          | Descripción |
|----------------------|---------------|-------------|
| id                   | varchar(255) (primary) | ID de la sesión (clave primaria) |
| user_id              | bigint (nullable, index) | ID del usuario (clave foránea) |
| ip_address           | varchar(45) (nullable) | Dirección IP |
| user_agent           | text (nullable) | Agente de usuario del navegador |
| payload              | longtext | Datos de la sesión |
| last_activity        | int (index) | Última actividad (timestamp) |

### 4. cache
Tabla para almacenamiento en caché.

| Columna              | Tipo          | Descripción |
|----------------------|---------------|-------------|
| key                  | varchar(255) (primary) | Clave del caché (clave primaria) |
| value                | mediumtext | Valor almacenado |
| expiration           | bigint (index) | Tiempo de expiración |

### 5. cache_locks
Tabla para bloqueos de caché.

| Columna              | Tipo          | Descripción |
|----------------------|---------------|-------------|
| key                  | varchar(255) (primary) | Clave del bloqueo (clave primaria) |
| owner                | varchar(255) | Propietario del bloqueo |
| expiration           | bigint (index) | Tiempo de expiración |

### 6. jobs
Tabla para trabajos en cola.

| Columna              | Tipo          | Descripción |
|----------------------|---------------|-------------|
| id                   | bigint (auto-increment) | Identificador único |
| queue                | varchar(255) (index) | Nombre de la cola |
| payload              | longtext | Datos del trabajo |
| attempts             | tinyint unsigned | Número de intentos |
| reserved_at          | int unsigned (nullable) | Timestamp de reserva |
| available_at         | int unsigned | Timestamp de disponibilidad |
| created_at           | int unsigned | Timestamp de creación |

### 7. job_batches
Tabla para lotes de trabajos.

| Columna              | Tipo          | Descripción |
|----------------------|---------------|-------------|
| id                   | varchar(255) (primary) | ID del lote (clave primaria) |
| name                 | varchar(255) | Nombre del lote |
| total_jobs           | int | Total de trabajos |
| pending_jobs         | int | Trabajos pendientes |
| failed_jobs          | int | Trabajos fallidos |
| failed_job_ids       | longtext | IDs de trabajos fallidos |
| options              | mediumtext (nullable) | Opciones adicionales |
| cancelled_at         | int (nullable) | Timestamp de cancelación |
| created_at           | int | Timestamp de creación |
| finished_at          | int (nullable) | Timestamp de finalización |

### 8. failed_jobs
Tabla para trabajos fallidos.

| Columna              | Tipo          | Descripción |
|----------------------|---------------|-------------|
| id                   | bigint (auto-increment) | Identificador único |
| uuid                 | varchar(255) (unique) | UUID único |
| connection           | text | Conexión usada |
| queue                | text | Cola del trabajo |
| payload              | longtext | Datos del trabajo |
| exception            | longtext | Excepción ocurrida |
| failed_at            | timestamp | Fecha de fallo |

## Notas
- Estas son las tablas por defecto de Laravel. Si has agregado migraciones personalizadas (por ejemplo, para mesas, pedidos, etc.), actualiza este documento.
- Para ejecutar las migraciones: `php artisan migrate`.
- Para ver el estado: `php artisan migrate:status`.
- La base de datos se configura en `config/database.php`.