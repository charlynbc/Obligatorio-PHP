<?php
$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
$userName  = htmlspecialchars(Auth::userName(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>body { background-color: #f8f5f0; }</style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/"><i class="bi bi-shop"></i> Restaurante</a>
            <a class="btn btn-outline-light btn-sm" href="/"><i class="bi bi-arrow-left"></i> Seguir comprando</a>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="fw-bold mb-4"><i class="bi bi-cart3"></i> Mi Carrito</h2>

        <?php if (!empty($_GET['error']) && $_GET['error'] === 'vacio'): ?>
            <div class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i>El carrito está vacío. Agregá platos antes de pagar.</div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
                <p class="text-muted mt-3 fs-5">Tu carrito está vacío.</p>
                <a href="/" class="btn btn-dark mt-2">Ver menú</a>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Plato</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio unit.</th>
                                <th class="text-end">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php
                                $nombre    = htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8');
                                $cantidad  = (int) $item['cantidad'];
                                $precio    = (float) $item['precio'];
                                $subtotal  = $precio * $cantidad;
                                $platoId   = (int) $item['plato_id'];
                                ?>
                                <tr>
                                    <td class="align-middle">
                                        <?php if (!empty($item['imagen_url'])): ?>
                                            <img src="/<?php echo htmlspecialchars($item['imagen_url'], ENT_QUOTES, 'UTF-8'); ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;" class="me-2">
                                        <?php endif; ?>
                                        <?php echo $nombre; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <!-- Restar -->
                                        <form method="POST" action="/?controller=Carrito&action=restar" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="plato_id" value="<?php echo $platoId; ?>">
                                            <button class="btn btn-sm btn-outline-secondary px-2 py-0">−</button>
                                        </form>
                                        <span class="mx-2 fw-bold"><?php echo $cantidad; ?></span>
                                        <!-- Sumar -->
                                        <form method="POST" action="/?controller=Carrito&action=agregar" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="plato_id" value="<?php echo $platoId; ?>">
                                            <button class="btn btn-sm btn-outline-secondary px-2 py-0">+</button>
                                        </form>
                                    </td>
                                    <td class="align-middle text-end">$<?php echo number_format($precio, 2); ?></td>
                                    <td class="align-middle text-end fw-bold">$<?php echo number_format($subtotal, 2); ?></td>
                                    <td class="align-middle text-end">
                                        <form method="POST" action="/?controller=Carrito&action=eliminar" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="plato_id" value="<?php echo $platoId; ?>">
                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-bold fs-5">Total:</td>
                                <td class="text-end fw-bold fs-5 text-success">$<?php echo number_format($total, 2); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <form method="POST" action="/?controller=Carrito&action=pagar" onsubmit="return confirm('¿Confirmar la compra por $<?php echo number_format($total, 2); ?>?');">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="bi bi-credit-card"></i> Pagar ahora
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
