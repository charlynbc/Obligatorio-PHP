<?php
$safeId = (int) ($menu['id'] ?? 0);
$safeNombre = htmlspecialchars($menu['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
$safeDescripcion = htmlspecialchars($menu['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
$safePrecio = htmlspecialchars((string) ($menu['precio'] ?? ''), ENT_QUOTES, 'UTF-8');
$safeCategoria = htmlspecialchars($menu['categoria'] ?? '', ENT_QUOTES, 'UTF-8');
$safeImagenUrl = htmlspecialchars($menu['imagen_url'] ?? '', ENT_QUOTES, 'UTF-8');
$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Plato</title>
    <link rel="stylesheet" href="/css/home.css">
</head>
<body>
    <header>
        <h1>Editar Plato</h1>
        <p>Modificá los datos del plato</p>
        <a href="/">Volver al inicio</a>
    </header>

    <main style="max-width: 640px; margin: 30px auto;">
        <?php if (!empty($error)): ?>
            <p style="color: #b91c1c; font-weight: 600;">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="/?controller=Menu&action=actualizar" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="id" value="<?php echo $safeId; ?>">

            <label for="nombre">Nombre</label><br>
            <input id="nombre" name="nombre" type="text" value="<?php echo $safeNombre; ?>" required style="width: 100%; margin-bottom: 12px;"><br>

            <label for="descripcion">Descripción</label><br>
            <textarea id="descripcion" name="descripcion" required style="width: 100%; margin-bottom: 12px; min-height: 110px;"><?php echo $safeDescripcion; ?></textarea><br>

            <label for="precio">Precio</label><br>
            <input id="precio" name="precio" type="number" step="0.01" min="0" value="<?php echo $safePrecio; ?>" required style="width: 100%; margin-bottom: 12px;"><br>

            <label for="categoria">Categoría</label><br>
            <input id="categoria" name="categoria" type="text" value="<?php echo $safeCategoria; ?>" style="width: 100%; margin-bottom: 12px;"><br>

            <label for="imagen">Imagen del plato</label><br>
            <?php if (!empty($menu['imagen_url'])): ?>
                <div style="margin-bottom: 12px;">
                    <img src="/<?php echo htmlspecialchars($menu['imagen_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($menu['nombre'], ENT_QUOTES, 'UTF-8'); ?>" style="max-width: 200px; max-height: 150px; border-radius: 4px;">
                    <p style="font-size: 0.9em; color: #666; margin-top: 8px;">Imagen actual</p>
                </div>
            <?php endif; ?>
            <input id="imagen" name="imagen" type="file" accept="image/*" style="width: 100%; margin-bottom: 16px;"><br>
            <p style="font-size: 0.85em; color: #666; margin-bottom: 16px;">Subí una nueva imagen para reemplazar (opcional)</p>

            <button type="submit">Actualizar Plato</button>
        </form>
    </main>
</body>
</html>
