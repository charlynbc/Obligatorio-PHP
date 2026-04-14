<?php
// Archivo: app/views/home.php
// Renderizador PHP: arma el contenido dinámico y lo inyecta en una plantilla HTML pura.

$menuCards = '';

if (!empty($menus)) {
    foreach ($menus as $plato) {
        $nombre = htmlspecialchars($plato['nombre'] ?? 'Sin nombre');
        $categoria = htmlspecialchars($plato['categoria'] ?? 'Sin categoría');
        $descripcion = htmlspecialchars($plato['descripcion'] ?? '');
        $precio = number_format((float) ($plato['precio'] ?? 0), 2);

        $imagen = '';
        if (!empty($plato['imagen_url'])) {
            $imagenUrl = htmlspecialchars($plato['imagen_url']);
            $imagen = '<img class="card-image" src="' . $imagenUrl . '" alt="' . $nombre . '">';
        }

        $menuCards .= '<div class="card">'
            . '<h3>' . $nombre . '</h3>'
            . '<p><em>Categoría: ' . $categoria . '</em></p>'
            . '<p>' . $descripcion . '</p>'
            . $imagen
            . '<p class="precio">$' . $precio . '</p>'
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

echo str_replace(
    ['{{LOGIN_URL}}', '{{REGISTRO_URL}}', '{{MENU_CARDS}}'],
    ['/?controller=Usuario&action=login', '/?controller=Usuario&action=registro', $menuCards],
    $template
);
