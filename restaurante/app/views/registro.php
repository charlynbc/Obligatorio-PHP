<?php
$safeName = htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8');
$safeEmail = htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8');
$safeRole = ($role ?? 'cliente') === 'admin' ? 'admin' : 'cliente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="/css/home.css">
</head>
<body>
    <header>
        <h1>Registro</h1>
        <p>Creá tu cuenta para continuar</p>
        <a href="/">Volver al inicio</a> |
        <a href="/?controller=Usuario&action=login">Ya tengo cuenta</a>
    </header>

    <main style="max-width: 520px; margin: 30px auto;">
        <?php if (!empty($error)): ?>
            <p style="color: #b91c1c; font-weight: 600;">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="/?controller=Usuario&action=registro">
            <label for="name">Nombre</label><br>
            <input id="name" name="name" type="text" value="<?php echo $safeName; ?>" required style="width: 100%; margin-bottom: 12px;"><br>

            <label for="email">Email</label><br>
            <input id="email" name="email" type="email" value="<?php echo $safeEmail; ?>" required style="width: 100%; margin-bottom: 12px;"><br>

            <label for="password">Contraseña</label><br>
            <input id="password" name="password" type="password" required style="width: 100%; margin-bottom: 12px;"><br>

            <label for="role">Rol</label><br>
            <select id="role" name="role" style="width: 100%; margin-bottom: 16px;">
                <option value="cliente" <?php echo $safeRole === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                <option value="admin" <?php echo $safeRole === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select><br>

            <button type="submit">Registrarme</button>
        </form>
    </main>
</body>
</html>
