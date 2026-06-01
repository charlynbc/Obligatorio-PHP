<?php
$compraId = (int) ($compra['id'] ?? 0);
$fechaRaw = (string) ($compra['fecha'] ?? '');
$fechaTs = strtotime($fechaRaw);
$fechaFmt = $fechaTs !== false ? date('d/m/Y H:i', $fechaTs) : $fechaRaw;
$totalFmt = number_format((float) ($compra['total'] ?? 0), 2);
$itemsTotales = 0;
$filas = '';

foreach (($compra['items'] ?? []) as $item) {
    $nombre = htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8');
    $cantidad = (int) $item['cantidad'];
    $precioUnitario = (float) $item['precio_unitario'];
    $subtotal = number_format($precioUnitario * $cantidad, 2);
    $itemsTotales += $cantidad;

    $filas .= '<tr>'
        . '<td>' . $nombre . '</td>'
        . '<td class="text-center">' . $cantidad . '</td>'
        . '<td class="text-end">$' . number_format($precioUnitario, 2) . '</td>'
        . '<td class="text-end fw-semibold">$' . $subtotal . '</td>'
        . '</tr>';
}

$logoutForm = '<form method="POST" action="/?controller=Usuario&action=logout" class="d-inline">'
    . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">'
    . '<button type="submit" class="btn btn-outline-light btn-sm">'
    . '<i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión'
    . '</button></form>';
$navActions = '<div class="d-flex gap-2 align-items-center">'
    . '<span class="text-white-50 me-2">' . htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8') . '</span>'
    . '<a href="/?controller=Usuario&action=perfil#historial-compras" class="btn btn-outline-light btn-sm">'
    . '<i class="bi bi-clock-history me-1"></i>Historial</a>'
    . $logoutForm
    . '</div>';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante del pedido - Mangiare a presto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/mangiare.css">
</head>
<body class="brand-page confirmation-page">
    <nav class="navbar navbar-dark mangiare-navbar shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand brand-mark" href="/">
                <span class="brand-title">Mangiare a presto</span>
                <span class="brand-subtitle">cucina italiana · pedidos al momento</span>
            </a>
            <?= $navActions ?>
        </div>
    </nav>

    <div class="container py-5 d-flex justify-content-center">
        <div class="confirm-shell">
            <div class="confirm-banner mb-4 shadow text-center">
                <div class="mb-2"><i class="bi bi-receipt-cutoff fs-1"></i></div>
                <h3 class="fw-bold mb-1">Comprobante disponible</h3>
                <p class="mb-0 fs-5">Podés volver a este pedido desde tu historial cuando quieras.</p>
                <div class="d-flex justify-content-center gap-2 flex-wrap mt-3">
                    <span class="badge rounded-pill text-bg-light px-3 py-2">Pedido #<?= htmlspecialchars((string) $compraId, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="badge rounded-pill text-bg-light px-3 py-2"><?= htmlspecialchars((string) $itemsTotales, ENT_QUOTES, 'UTF-8') ?> <?= $itemsTotales === 1 ? 'unidad' : 'unidades' ?></span>
                    <span class="badge rounded-pill text-bg-light px-3 py-2"><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($fechaFmt, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>

            <div class="card page-card confirm-summary shadow-sm mb-4">
                <div class="card-header bg-dark text-white fw-semibold">
                    <i class="bi bi-bag-check me-2"></i>Detalle del pedido
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Plato</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Precio unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= $filas ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total pagado:</td>
                                <td class="text-end fw-bold text-success">$<?= htmlspecialchars($totalFmt, ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="/?controller=Usuario&action=perfil#historial-compras" class="btn btn-primary px-4">
                    <i class="bi bi-clock-history me-1"></i>Ir al historial
                </a>
                <a href="/" class="btn btn-outline-dark px-4">
                    <i class="bi bi-house me-1"></i>Volver al menú
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
