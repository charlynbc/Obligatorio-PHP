<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - Restaurante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f5f0; }
        .card-img-top { height: 180px; object-fit: cover; }
        .precio-badge { font-size: 1.1rem; font-weight: 700; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/"><i class="bi bi-shop"></i> Restaurante</a>
            <a href="/" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Volver al menú</a>
        </div>
    </nav>

    <main class="container py-5">
        <h2 class="fw-bold mb-4"><i class="bi bi-heart-fill text-danger"></i> Mis Favoritos</h2>

        <?php if (empty($favoritos)): ?>
            <div class="text-center py-5">
                <i class="bi bi-heart text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3 fs-5">No tenés platos en favoritos todavía.</p>
                <a href="/" class="btn btn-dark"><i class="bi bi-book"></i> Ver menú</a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($favoritos as $plato): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <?php if (!empty($plato['imagen_url'])): ?>
                                <img src="<?= htmlspecialchars($plato['imagen_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($plato['nombre']) ?>">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($plato['nombre']) ?></h5>
                                <span class="badge bg-secondary mb-2" style="width: fit-content;"><?= htmlspecialchars($plato['categoria'] ?? 'Sin categoría') ?></span>
                                <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars($plato['descripcion'] ?? '') ?></p>
                                <p class="precio-badge text-success">$<?= number_format((float) $plato['precio'], 2) ?></p>
                                <div class="d-flex gap-2">
                                    <form method="POST" action="/?controller=Favorito&action=eliminar">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="plato_id" value="<?= (int) $plato['plato_id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-heart-break"></i> Quitar</button>
                                    </form>
                                    <form method="POST" action="/?controller=Carrito&action=agregar">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="plato_id" value="<?= (int) $plato['plato_id'] ?>">
                                        <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-cart-plus"></i> Al carrito</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
