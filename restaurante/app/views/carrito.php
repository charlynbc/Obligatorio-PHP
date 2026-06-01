<?php
// Archivo: app/views/carrito.php

// ── Navbar ────────────────────────────────────────────────────────────────────
$carritoModel = new CarritoModel();
$cartCount    = $carritoModel->countItems((int) $_SESSION['user_id']);
$cartBadge    = $cartCount > 0
    ? ' <span class="badge bg-danger rounded-pill">' . $cartCount . '</span>'
    : '';

$badge      = isAdmin()
    ? '<span class="badge bg-danger me-2">Admin</span>'
    : '<span class="badge bg-secondary me-2">Cliente</span>';
$logoutForm = '<form method="POST" action="/?controller=Usuario&action=logout" class="d-inline">'
    . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">'
    . '<button type="submit" class="btn btn-outline-light btn-sm">'
    . '<i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión'
    . '</button></form>';
$navActions = '<div class="d-flex gap-2 align-items-center">'
    . $badge
    . '<span class="text-white-50 me-2">' . htmlspecialchars($_SESSION['name']) . '</span>'
    . '<a href="/?controller=Usuario&action=perfil" class="btn btn-outline-light btn-sm">'
    . '<i class="bi bi-person-gear me-1"></i>Mi Perfil</a>'
    . '<a href="/?controller=Carrito&action=index" class="btn btn-outline-warning btn-sm position-relative">'
    . '<i class="bi bi-cart3"></i>' . $cartBadge . '</a>'
    . $logoutForm
    . '</div>';

// ── Contenido del carrito ─────────────────────────────────────────────────────
$csrfField = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';

if (empty($items)) {
    $carritoContent = '<div class="alert alert-info">'
        . '<i class="bi bi-info-circle me-2"></i>'
        . 'Tu carrito está vacío. <a href="/">Ver el menú</a>.'
        . '</div>';
} else {
    $rows = '';
    foreach ($items as $item) {
        $nombre    = htmlspecialchars($item['nombre']);
        $precio    = number_format((float) $item['precio'], 2);
        $subtotal  = number_format((float) $item['precio'] * (int) $item['cantidad'], 2);
        $platoId   = (int) $item['plato_id'];
        $cantidad  = (int) $item['cantidad'];
        $cantidadControls = '<div class="d-inline-flex align-items-center gap-2">'
            . '<form method="POST" action="/?controller=Carrito&action=restar" class="d-inline">'
            . $csrfField
            . '<input type="hidden" name="plato_id" value="' . $platoId . '">'
            . '<button type="submit" class="btn btn-outline-secondary btn-sm px-2 py-0" aria-label="Restar una unidad de ' . $nombre . '">'
            . '<i class="bi bi-dash-lg"></i>'
            . '</button></form>'
            . '<span class="fw-semibold">' . $cantidad . '</span>'
            . '<form method="POST" action="/?controller=Carrito&action=agregar" class="d-inline">'
            . $csrfField
            . '<input type="hidden" name="plato_id" value="' . $platoId . '">'
            . '<button type="submit" class="btn btn-outline-secondary btn-sm px-2 py-0" aria-label="Sumar una unidad de ' . $nombre . '">'
            . '<i class="bi bi-plus-lg"></i>'
            . '</button></form>'
            . '</div>';

        if (!empty($item['imagen_url'])) {
            $img = '<img src="' . htmlspecialchars($item['imagen_url']) . '" alt="' . $nombre
                 . '" style="width:60px;height:50px;object-fit:cover;border-radius:4px;">';
        } else {
            $img = '<div style="width:60px;height:50px;background:#e9ecef;border-radius:4px;'
                 . 'display:flex;align-items:center;justify-content:center;">'
                 . '<i class="bi bi-image text-secondary"></i></div>';
        }

        $rows .= '<tr>'
            . '<td class="align-middle">' . $img . '</td>'
            . '<td class="align-middle fw-semibold">' . $nombre . '</td>'
            . '<td class="align-middle text-center">' . $cantidadControls . '</td>'
            . '<td class="align-middle text-end">$' . $precio . '</td>'
            . '<td class="align-middle text-end fw-bold">$' . $subtotal . '</td>'
            . '<td class="align-middle text-center">'
            . '<form method="POST" action="/?controller=Carrito&action=eliminar" class="d-inline">'
            . $csrfField
            . '<input type="hidden" name="plato_id" value="' . $platoId . '">'
            . '<button type="submit" class="btn btn-outline-danger btn-sm" title="Quitar">'
            . '<i class="bi bi-trash3"></i>'
            . '</button></form>'
            . '</td>'
            . '</tr>';
    }

    $totalFmt = number_format((float) $total, 2);

    $pagarForm = '<form method="POST" action="/?controller=Carrito&action=pagar" class="d-inline">'
        . $csrfField
        . '<button type="submit" class="btn btn-success btn-lg px-5">'
        . '<i class="bi bi-bag-check-fill me-2"></i>Pagar $' . $totalFmt
        . '</button></form>';

    $carritoContent = '<div class="card shadow-sm">'
        . '<div class="table-responsive">'
        . '<table class="table table-hover mb-0">'
        . '<thead class="table-dark"><tr>'
        . '<th></th><th>Plato</th>'
        . '<th class="text-center">Cant.</th>'
        . '<th class="text-end">Precio</th>'
        . '<th class="text-end">Subtotal</th>'
        . '<th></th>'
        . '</tr></thead>'
        . '<tbody>' . $rows . '</tbody>'
        . '<tfoot class="table-light"><tr>'
        . '<td colspan="4" class="text-end fw-bold fs-5">Total:</td>'
        . '<td class="text-end fw-bold fs-5 text-danger">$' . $totalFmt . '</td>'
        . '<td></td>'
        . '</tr></tfoot>'
        . '</table>'
        . '</div>'
        . '</div>'
        . '<div class="d-flex justify-content-end mt-3">' . $pagarForm . '</div>';
}

// ── Renderizar ────────────────────────────────────────────────────────────────
$templatePath = BASE_PATH . 'app/views/carrito.template.html';
$template     = file_get_contents($templatePath);

if ($template === false) {
    echo 'Error: No se pudo cargar la plantilla.';
    return;
}

echo str_replace(
    ['{{NAV_ACTIONS}}', '{{CARRITO_CONTENT}}'],
    [$navActions,       $carritoContent],
    $template
);
