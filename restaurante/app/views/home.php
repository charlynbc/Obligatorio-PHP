<?php
// Archivo: app/views/home.php
// Renderizador PHP: arma el contenido dinámico y lo inyecta en una plantilla HTML pura.

$menuCards = '';
$isAdmin = !empty($_SESSION['user_id']) && (($_SESSION['user_role'] ?? '') === 'admin');
$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');

$menuFeedback = '';
if (!empty($_GET['menu_success'])) {
    $messages = [
        'created' => 'Plato creado correctamente.',
        'updated' => 'Plato actualizado correctamente.',
        'deleted' => 'Plato eliminado correctamente.',
    ];

    $menuSuccessKey = $_GET['menu_success'];
    if (isset($messages[$menuSuccessKey])) {
        $menuFeedback = '<div class="alert alert-success alert-dismissible fade show" role="alert">'
            . '<i class="bi bi-check-circle-fill me-2"></i>'
            . htmlspecialchars($messages[$menuSuccessKey], ENT_QUOTES, 'UTF-8')
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
            . '</div>';
    }
}

if (!empty($menus)) {
    foreach ($menus as $plato) {
        $id = (int) ($plato['id'] ?? 0);
        $nombre = htmlspecialchars($plato['nombre'] ?? 'Sin nombre');
        $categoria = htmlspecialchars($plato['categoria'] ?? 'Sin categoría');
        $descripcion = htmlspecialchars($plato['descripcion'] ?? '');
        $precio = number_format((float) ($plato['precio'] ?? 0), 2);

        $imagen = '';
        if (!empty($plato['imagen_url'])) {
            $imagenUrl = htmlspecialchars($plato['imagen_url']);
            $imagen = '<img src="' . $imagenUrl . '" class="card-img-top" alt="' . $nombre . '">';
        }

        $adminActions = '';
        if ($isAdmin && $id > 0) {
            $adminActions = '<div class="d-flex gap-2 mt-2">'
                . '<a href="/?controller=Menu&action=editar&id=' . $id . '" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Editar</a>'
                . '<form method="POST" action="/?controller=Menu&action=eliminar" class="d-inline" onsubmit="return confirm(\'¿Eliminar este plato?\');">'
                . '<input type="hidden" name="csrf_token" value="' . $csrfToken . '">'
                . '<input type="hidden" name="id" value="' . $id . '">'
                . '<button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Eliminar</button>'
                . '</form>'
                . '</div>';
        }

        $menuCards .= '<div class="col">'
            . '<div class="card h-100 shadow-sm">'
            . $imagen
            . '<div class="card-body d-flex flex-column">'
            . '<h5 class="card-title">' . $nombre . '</h5>'
            . '<span class="badge bg-secondary mb-2" style="width: fit-content;">' . $categoria . '</span>'
            . '<p class="card-text text-muted flex-grow-1">' . $descripcion . '</p>'
            . '<p class="precio-badge text-success mt-2">$' . $precio . '</p>'
            . $adminActions
            . '</div>'
            . '</div>'
            . '</div>';
    }
} else {
    $menuCards = '<div class="col-12"><p class="text-muted">Actualmente no hay platos disponibles en el menú.</p></div>';
}

$templatePath = BASE_PATH . 'app/views/home.template.html';
$template = file_get_contents($templatePath);

if ($template === false) {
    echo 'Error: No se pudo cargar la plantilla HTML de la vista.';
    return;
}

$userNav = '<li class="nav-item"><a class="nav-link" href="/?controller=Usuario&action=login"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</a></li>'
    . '<li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/?controller=Usuario&action=registro">Registrarse</a></li>';

if (!empty($_SESSION['user_id'])) {
    $userName = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
    $userRole = htmlspecialchars($_SESSION['user_role'] ?? 'cliente', ENT_QUOTES, 'UTF-8');
    $userNav = '<li class="nav-item"><span class="navbar-text text-light me-2"><i class="bi bi-person-circle"></i> ' . $userName . ' <span class="badge bg-secondary">' . $userRole . '</span></span></li>'
        . '<li class="nav-item"><a class="nav-link" href="/?controller=Usuario&action=logout"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>';

    if ($isAdmin) {
        $userNav .= '<li class="nav-item"><a class="btn btn-warning btn-sm" href="/?controller=Menu&action=crear"><i class="bi bi-plus-lg"></i> Agregar plato</a></li>';
    }
}

echo str_replace(
    ['{{USER_NAV}}', '{{MENU_FEEDBACK}}', '{{MENU_CARDS}}'],
    [$userNav, $menuFeedback, $menuCards],
    $template
);
