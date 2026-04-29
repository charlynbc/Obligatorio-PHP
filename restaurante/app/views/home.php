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
        $menuFeedback = '<p style="color: #166534; font-weight: 600;">' . htmlspecialchars($messages[$menuSuccessKey], ENT_QUOTES, 'UTF-8') . '</p>';
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
            $imagen = '<img class="card-image" src="' . $imagenUrl . '" alt="' . $nombre . '">';
        }

        $adminActions = '';
        if ($isAdmin && $id > 0) {
            $adminActions = '<div style="margin-top: 10px;">'
                . '<a href="/?controller=Menu&action=editar&id=' . $id . '">Editar</a>'
                . '<form method="POST" action="/?controller=Menu&action=eliminar" style="display:inline; margin-left: 8px;" onsubmit="return confirm(\'¿Eliminar este plato?\');">'
                . '<input type="hidden" name="csrf_token" value="' . $csrfToken . '">'
                . '<input type="hidden" name="id" value="' . $id . '">'
                . '<button type="submit">Eliminar</button>'
                . '</form>'
                . '</div>';
        }

        $menuCards .= '<div class="card">'
            . '<h3>' . $nombre . '</h3>'
            . '<p><em>Categoría: ' . $categoria . '</em></p>'
            . '<p>' . $descripcion . '</p>'
            . $imagen
            . '<p class="precio">$' . $precio . '</p>'
            . $adminActions
            . '</div>';
    }
} else {
    $menuCards = '<p>Actualmente no hay platos disponibles en el menú.</p>';
}

$templatePath = BASE_PATH . 'app/views/home.template.html';
$template = file_get_contents($templatePath);

if ($template === false) {
    echo 'Error: No se pudo cargar la plantilla HTML de la vista.';
    return;
}

$userNav = '<a href="/?controller=Usuario&action=login">Iniciar Sesión</a> | '
    . '<a href="/?controller=Usuario&action=registro">Registrarse</a>';

if (!empty($_SESSION['user_id'])) {
    $userName = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
    $userRole = htmlspecialchars($_SESSION['user_role'] ?? 'cliente', ENT_QUOTES, 'UTF-8');
    $userNav = '<span>Hola, ' . $userName . ' (' . $userRole . ')</span> | '
        . '<a href="/?controller=Usuario&action=logout">Cerrar Sesión</a>';

    if ($isAdmin) {
        $userNav .= ' | <a href="/?controller=Menu&action=crear">Agregar menú</a>';
    }
}

echo str_replace(
    ['{{USER_NAV}}', '{{MENU_FEEDBACK}}', '{{MENU_CARDS}}'],
    [$userNav, $menuFeedback, $menuCards],
    $template
);
