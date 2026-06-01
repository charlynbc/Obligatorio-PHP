<?php
// Archivo: app/config/Mail.php
// Configuración SMTP para envío de correos.
// Podés usar Gmail, Outlook, Mailtrap (recomendado para pruebas), etc.
//
// Para usar Gmail:
//   MAIL_HOST=smtp.gmail.com  MAIL_PORT=587  MAIL_ENCRYPTION=tls
//   MAIL_USERNAME=tucuenta@gmail.com
//   MAIL_PASSWORD=tu_contraseña_de_aplicacion   (activar "Contraseñas de aplicación" en Google)
//
// Para usar Mailtrap (pruebas sin envío real):
//   MAIL_HOST=sandbox.smtp.mailtrap.io  MAIL_PORT=2525  MAIL_ENCRYPTION=tls
//   MAIL_USERNAME=<usuario mailtrap>  MAIL_PASSWORD=<clave mailtrap>

define('MAIL_HOST',       getenv('MAIL_HOST')       ?: 'sandbox.smtp.mailtrap.io');
define('MAIL_PORT',       (int)(getenv('MAIL_PORT') ?: 2525));
define('MAIL_ENCRYPTION', getenv('MAIL_ENCRYPTION') ?: 'tls');   // 'tls' o 'ssl'
define('MAIL_USERNAME',   getenv('MAIL_USERNAME')   ?: '');
define('MAIL_PASSWORD',   getenv('MAIL_PASSWORD')   ?: '');
define('MAIL_FROM',       getenv('MAIL_FROM')       ?: 'noreply@restaurante.local');
define('MAIL_FROM_NAME',  getenv('MAIL_FROM_NAME')  ?: 'Restaurante');
