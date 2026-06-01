<?php
// Archivo: app/views/confirmacion.php

$minutos   = (int) $pedido['minutos'];
$totalFmt  = number_format((float) $pedido['total'], 2);
$horas     = intdiv($minutos, 60);
$mins      = $minutos % 60;
$tiempoStr = $horas > 0
    ? "1 hora y {$mins} minutos"
    : "{$minutos} minutos";

// Navbar igual que el carrito
require_once BASE_PATH . 'app/models/CarritoModel.php';
$logoutForm = '<form method="POST" action="/?controller=Usuario&action=logout" class="d-inline">'
    . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">'
    . '<button type="submit" class="btn btn-outline-light btn-sm">'
    . '<i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión'
    . '</button></form>';
$navActions = '<div class="d-flex gap-2 align-items-center">'
    . '<span class="text-white-50 me-2">' . htmlspecialchars($_SESSION['name']) . '</span>'
    . '<a href="/?controller=Usuario&action=perfil" class="btn btn-outline-light btn-sm">'
    . '<i class="bi bi-person-gear me-1"></i>Mi Perfil</a>'
    . $logoutForm
    . '</div>';

// Filas del resumen del pedido
$filas = '';
foreach ($pedido['items'] as $item) {
    $nombre   = htmlspecialchars($item['nombre']);
    $cantidad = (int) $item['cantidad'];
    $subtotal = number_format((float) $item['precio'] * $cantidad, 2);
    $filas .= '<tr>'
        . '<td>' . $nombre . '</td>'
        . '<td class="text-center">' . $cantidad . '</td>'
        . '<td class="text-end">$' . $subtotal . '</td>'
        . '</tr>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Pedido Confirmado! - Mangiare a presto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/mangiare.css">
    <style>
        @keyframes pulse-bg {
            0%, 100% { background-color: #1f6b4f; }
            50%       { background-color: #174d39; }
        }
        .confirm-banner {
            animation: pulse-bg 2s ease-in-out infinite;
            text-align: center;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spinner-icon {
            display: inline-block;
            animation: spin 1.4s linear infinite;
            font-size: 2.5rem;
        }
    </style>
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

            <!-- Banner principal -->
            <div class="confirm-banner mb-4 shadow">
                <div class="spinner-icon mb-2">🍳</div>
                <h3 class="fw-bold mb-1">¡Compra Realizada!</h3>
                <p class="mb-0 fs-5">Preparando tu pedido...</p>
            </div>

            <!-- Tiempo estimado -->
            <div class="alert alert-warning d-flex align-items-center gap-3 shadow-sm" role="alert">
                <i class="bi bi-clock-history fs-3"></i>
                <div>
                    <strong>Tiempo estimado de entrega:</strong><br>
                    <span class="fs-5"><?= htmlspecialchars($tiempoStr) ?></span>
                </div>
            </div>

            <!-- Resumen del pedido -->
            <div class="card page-card confirm-summary shadow-sm mb-4">
                <div class="card-header bg-dark text-white fw-semibold">
                    <i class="bi bi-receipt me-2"></i>Resumen del pedido
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Plato</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?= $filas ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Total pagado:</td>
                                <td class="text-end fw-bold text-success">$<?= htmlspecialchars($totalFmt) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="text-center">
                <a href="/" class="btn btn-primary px-4">
                    <i class="bi bi-house me-1"></i>Volver al menú
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
