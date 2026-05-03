<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras - Restaurante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f5f0; }
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
        <h2 class="fw-bold mb-4"><i class="bi bi-clock-history"></i> Historial de Compras</h2>

        <?php if (empty($compras)): ?>
            <div class="text-center py-5">
                <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3 fs-5">Todavía no realizaste ninguna compra.</p>
                <a href="/" class="btn btn-dark"><i class="bi bi-book"></i> Ver menú</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover bg-white shadow-sm rounded">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Detalle</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $i => $compra): ?>
                            <tr>
                                <td class="fw-bold"><?= $i + 1 ?></td>
                                <td>
                                    <i class="bi bi-calendar3"></i>
                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($compra['created_at']))) ?>
                                </td>
                                <td class="text-muted" style="max-width: 350px;">
                                    <?= htmlspecialchars($compra['detalle']) ?>
                                </td>
                                <td class="text-end fw-bold text-success fs-5">
                                    $<?= number_format((float) $compra['total'], 2) ?>
                                </td>
                                <td class="text-center">
                                    <a href="/?controller=Carrito&action=comprobante&id=<?= (int) $compra['id'] ?>" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-receipt"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
