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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>body { background-color: #f8f5f0; }</style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/"><i class="bi bi-shop"></i> Restaurante</a>
            <a class="btn btn-outline-light btn-sm" href="/"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="card-title fw-bold mb-1"><i class="bi bi-pencil-square"></i> Editar Plato</h3>
                        <p class="text-muted mb-4">Modificá los datos del plato</p>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="/?controller=Menu&action=actualizar" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="id" value="<?php echo $safeId; ?>">

                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input id="nombre" name="nombre" type="text" class="form-control" value="<?php echo $safeNombre; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea id="descripcion" name="descripcion" class="form-control" rows="4" required><?php echo $safeDescripcion; ?></textarea>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label for="precio" class="form-label">Precio ($)</label>
                                    <input id="precio" name="precio" type="number" step="0.01" min="0" class="form-control" value="<?php echo $safePrecio; ?>" required>
                                </div>
                                <div class="col-sm-6">
                                    <label for="categoria" class="form-label">Categoría</label>
                                    <input id="categoria" name="categoria" type="text" class="form-control" value="<?php echo $safeCategoria; ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="imagen" class="form-label">Imagen del plato</label>
                                <?php if (!empty($menu['imagen_url'])): ?>
                                    <div class="mb-2">
                                        <img src="/<?php echo htmlspecialchars($menu['imagen_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($menu['nombre'], ENT_QUOTES, 'UTF-8'); ?>" class="img-thumbnail" style="max-height: 160px; object-fit: cover;">
                                        <div class="form-text">Imagen actual</div>
                                    </div>
                                <?php endif; ?>
                                <input id="imagen" name="imagen" type="file" accept="image/*" class="form-control">
                                <div class="form-text">Subí una nueva imagen para reemplazar (opcional).</div>
                            </div>

                            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-floppy"></i> Actualizar Plato</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
