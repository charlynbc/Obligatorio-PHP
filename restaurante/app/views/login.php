<?php
$safeEmail = htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="/css/home.css">
</head>
<body>
    <header>
        <h1>Iniciar Sesión</h1>
        <p>Entrá con tu cuenta</p>
        <a href="/">Volver al inicio</a> |
        <a href="/?controller=Usuario&action=registro">Crear cuenta</a>
    </header>

    <main style="max-width: 520px; margin: 30px auto;">
        <?php if (!empty($success)): ?>
            <p style="color: #166534; font-weight: 600;">
                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p style="color: #b91c1c; font-weight: 600;">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="/?controller=Usuario&action=login">
            <label for="email">Email</label><br>
            <input id="email" name="email" type="email" value="<?php echo $safeEmail; ?>" required style="width: 100%; margin-bottom: 12px;"><br>

            <label for="password">Contraseña</label><br>
            <input id="password" name="password" type="password" required style="width: 100%; margin-bottom: 16px;"><br>

            <button type="submit">Ingresar</button>
        </form>
    </main>
</body>
</html>
