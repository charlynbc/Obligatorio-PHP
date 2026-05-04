<?php
// Archivo: app/views/home.php

// ── Navbar dinámica ────────────────────────────────────────────────────────────
if (isLoggedIn()) {
    // Badge con cantidad de items en el carrito
    require_once BASE_PATH . 'app/models/CarritoModel.php';
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
    $navActions = $badge
        . '<span class="text-white-50 me-2">'
        . htmlspecialchars($_SESSION['name'])
        . '</span>'
        . '<a href="/?controller=Usuario&action=perfil" class="btn btn-outline-light btn-sm">'
        . '<i class="bi bi-person-gear me-1"></i>Mi Perfil</a>'
        . '<a href="/?controller=Carrito&action=index" class="btn btn-outline-warning btn-sm position-relative">'
        . '<i class="bi bi-cart3"></i>' . $cartBadge . '</a>'
        . $logoutForm;
} else {
    $navActions = '<a href="/?controller=Usuario&action=login" class="btn btn-outline-light btn-sm">'
        . '<i class="bi bi-person-circle me-1"></i>Iniciar Sesión</a>'
        . '<a href="/?controller=Usuario&action=registro" class="btn btn-warning btn-sm fw-semibold">'
        . '<i class="bi bi-person-plus-fill me-1"></i>Registrarse</a>';
}

// ── Mensaje de error al subir imagen ──────────────────────────────────────────
$uploadError = '';
if (!empty($_GET['upload_error'])) {
    $errores = [
        '1'       => 'Error al subir el archivo.',
        'tipo'    => 'Solo se permiten imágenes JPG, PNG, GIF o WEBP.',
        'tamano'  => 'La imagen supera el límite de 5 MB.',
        'guardado'=> 'No se pudo guardar la imagen en el servidor.',
    ];
    $codigo = htmlspecialchars($_GET['upload_error']);
    $msg    = $errores[$codigo] ?? 'Error desconocido al subir la imagen.';
    $uploadError = '<div class="upload-error"><i class="bi bi-exclamation-triangle me-1"></i>' . $msg . '</div>';
}

// ── Tarjetas de menú ──────────────────────────────────────────────────────────
$menuCards = '';

if (!empty($menus)) {
    foreach ($menus as $plato) {
        $id          = (int) $plato['id'];
        $nombre      = htmlspecialchars($plato['nombre']      ?? 'Sin nombre');
        $categoria   = htmlspecialchars($plato['categoria']   ?? 'Sin categoría');
        $descripcion = htmlspecialchars($plato['descripcion'] ?? '');
        $precio      = number_format((float) ($plato['precio'] ?? 0), 2);

        if (!empty($plato['imagen_url'])) {
            $imagenUrl  = htmlspecialchars($plato['imagen_url']);
            $imagenHtml = '<img class="card-img-top card-image" src="' . $imagenUrl . '" alt="' . $nombre . '">';
        } else {
            $imagenHtml = '<div class="card-image-placeholder">'
                . '<i class="bi bi-image fs-1 text-secondary"></i>'
                . '</div>';
        }

        // El botón de subida solo aparece si el usuario es administrador
        $uploadForm = '';
        if (isAdmin()) {
            $uploadForm = '<form class="upload-form" action="/?controller=Menu&action=upload"'
                . ' method="POST" enctype="multipart/form-data">'
                . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">'
                . '<input type="hidden" name="id" value="' . $id . '">'
                . '<label class="upload-label">'
                . '<i class="bi bi-camera me-1"></i>'
                . '<span>' . (empty($plato['imagen_url']) ? 'Agregar imagen' : 'Cambiar imagen') . '</span>'
                . '<input type="file" name="imagen" accept="image/*" onchange="this.form.submit()">'
                . '</label>'
                . '</form>';
        }

        // Botón "Agregar al carrito" solo para clientes autenticados (no admin)
        $carritoBtn = '';
        if (isLoggedIn() && !isAdmin()) {
            $carritoBtn = '<form method="POST" action="/?controller=Carrito&action=agregar">'
                . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">'
                . '<input type="hidden" name="plato_id" value="' . $id . '">'
                . '<button type="submit" class="btn btn-success btn-sm w-100">'
                . '<i class="bi bi-cart-plus me-1"></i>Agregar al carrito'
                . '</button></form>';
        }

        $menuCards .= '<div class="col-sm-6 col-md-4 col-xl-3">'
            . '<div class="card h-100 shadow-sm">'
            . $imagenHtml
            . '<div class="card-body d-flex flex-column">'
            . '<h5 class="card-title mb-1">' . $nombre . '</h5>'
            . '<span class="badge bg-secondary mb-2 align-self-start">' . $categoria . '</span>'
            . '<p class="card-text small text-muted flex-grow-1">' . $descripcion . '</p>'
            . '<p class="precio mt-auto mb-2">$' . $precio . '</p>'
            . $carritoBtn
            . $uploadForm
            . '</div>'
            . '</div>'
            . '</div>';
    }
} else {
    $menuCards = '<p class="text-muted">Actualmente no hay platos disponibles en el menú.</p>';
}

// ── Renderizar template ───────────────────────────────────────────────────────
$templatePath = BASE_PATH . 'app/views/home.template.html';
$template     = file_get_contents($templatePath);

if ($template === false) {
    echo 'Error: No se pudo cargar la plantilla HTML de la vista.';
    return;
}

echo str_replace(
    ['{{NAV_ACTIONS}}', '{{UPLOAD_ERROR}}', '{{MENU_CARDS}}'],
    [$navActions,       $uploadError,       $menuCards],
    $template
);
