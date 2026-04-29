<?php
$userName  = htmlspecialchars(Auth::userName(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra confirmada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>body { background-color: #f8f5f0; }</style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/"><i class="bi bi-shop"></i> Restaurante</a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow text-center border-0">
                    <div class="card-body py-5">
                        <i class="bi bi-check-circle-fill text-success" style="font-size:4rem;"></i>
                        <h2 class="fw-bold mt-3">¡Compra confirmada!</h2>
                        <p class="text-muted">Gracias, <strong><?php echo $userName; ?></strong>. Tu pedido fue registrado.</p>
                        <p class="text-muted small">Fecha: <?php echo htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-dark text-white fw-bold"><i class="bi bi-receipt"></i> Detalle del pedido</div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Plato</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">Precio unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalles as $d): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center"><?php echo (int) $d['cantidad']; ?></td>
                                        <td class="text-end">$<?php echo number_format((float) $d['precio_unitario'], 2); ?></td>
                                        <td class="text-end">$<?php echo number_format($d['cantidad'] * $d['precio_unitario'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="3" class="text-end fw-bold">Total pagado:</td>
                                    <td class="text-end fw-bold text-success fs-5">$<?php echo number_format((float) $total, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="/" class="btn btn-dark btn-lg px-5"><i class="bi bi-house"></i> Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
