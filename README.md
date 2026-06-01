# Mangiare a presto

Aplicación web para restaurante desarrollada en PHP con patrón MVC, persistencia en SQLite y renderizado server-side.

## Descripción

El proyecto permite:

- registro e inicio de sesión
- gestión de menú por administradores
- favoritos, carrito e historial para clientes
- compras con persistencia en base de datos
- envío de email de confirmación si se configura SMTP
- ventas en local y métricas para administración

## Stack técnico

- PHP 8
- MVC custom con Front Controller
- SQLite + PDO
- Bootstrap 5
- CSS propio para branding y presentación
- Vite para compilar assets frontend
- Symfony Mailer para emails

## Ejecución rápida

Desde la raíz del workspace:

```bash
./build_and_serve.sh 8000
```

La aplicación queda disponible en:

```text
http://localhost:8000
```

## Ejecución manual

```bash
cd restaurante
composer install
npm install
npm run build
cd ..
./serve.sh 8000
```

## Base de datos

La aplicación usa SQLite. El archivo de base está en:

```text
restaurante/database/database.sqlite
```

## Credenciales de prueba

### Administrador demo

- email: demo.admin@mangiare.local
- contraseña: MangiareAdmin2026!

### Cliente demo

- email: demo.cliente@mangiare.local
- contraseña: MangiareCliente2026!

## Email de confirmación

El envío de email está implementado, pero para que funcione hay que configurar SMTP.

Archivo de configuración:

```text
restaurante/app/config/Mail.php
```

Variables relevantes:

- MAIL_HOST
- MAIL_PORT
- MAIL_ENCRYPTION
- MAIL_USERNAME
- MAIL_PASSWORD
- MAIL_FROM
- MAIL_FROM_NAME

Si no hay credenciales SMTP configuradas, la compra se registra igual y el sistema no interrumpe el flujo.

## Video demo

Pendiente de agregar enlace al video de demostración.

## Estructura principal

```text
restaurante/
├── public/index.php
├── app/
│   ├── config/
│   ├── controllers/
│   ├── models/
│   └── views/
├── database/
└── public/css/
```

## Notas

- El Front Controller real del proyecto es `restaurante/public/index.php`.
- El frontend no resuelve lógica de negocio: solo renderiza y envía formularios.
- La lógica de permisos, validación y persistencia está del lado del servidor.