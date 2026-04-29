<?php
$safeNombre = htmlspecialchars($menu['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$safeDescripcion = htmlspecialchars($menu['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$safePrecio = htmlspecialchars($menu['precio'] ?? '', ENT_QUOTES, 'UTF-8');
$safeCategoria = htmlspecialchars($menu['categoria'] ?? '', ENT_QUOTES, 'UTF-8');
$safeImagenUrl = htmlspecialchars($menu['imagen_url'] ?? '', ENT_QUOTES, 'UTF-8');
$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Plato</title>
    <link rel="stylesheet" href="/css/home.css">
</head>
<body>
    <header>
        <h1>Nuevo Plato</h1>
        <p>Alta de un plato del menú</p>
        <a href="/">Volver al inicio</a>
    </header>

    <main style="max-width: 640px; margin: 30px auto;">
        <?php if (!empty($error)): ?>
            <p style="color: #b91c1c; font-weight: 600;">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="/?controller=Menu&action=guardar">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

            <label for="nombre">Nombre</label><br>
            <input id="nombre" name="nombre" type="text" value="<?php echo $safeNombre; ?>" required style="width: 100%; margin-bottom: 12px;"><br>

            <label for="descripcion">Descripción</label><br>
            <textarea id="descripcion" name="descripcion" required style="width: 100%; margin-bottom: 12px; min-height: 110px;"><?php echo $safeDescripcion; ?></textarea><br>

            <label for="precio">Precio</label><br>
            <input id="precio" name="precio" type="number" step="0.01" min="0" value="<?php echo $safePrecio; ?>" required style="width: 100%; margin-bottom: 12px;"><br>

            <label for="categoria">Categoría</label><br>
            <input id="categoria" name="categoria" type="text" value="<?php echo $safeCategoria; ?>" style="width: 100%; margin-bottom: 12px;"><br>

            <label for="imagen_url">URL de imagen</label><br>
            <input id="imagen_url" name="imagen_url" type="url" value="<?php echo $safeImagenUrl; ?>" style="width: 100%; margin-bottom: 16px;"><br>

            <button type="submit">Guardar Plato</button>
        </form>
    </main>
</body>
</html>
