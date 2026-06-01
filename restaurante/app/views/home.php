<?php
// Archivo: app/views/home.php

// ── Navbar dinámica ────────────────────────────────────────────────────────────
if (isLoggedIn()) {
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
        . '<i class="bi bi-person-gear me-1"></i>Mi Perfil</a>';

    if (!isAdmin()) {
        require_once BASE_PATH . 'app/models/CarritoModel.php';
        $carritoModel = new CarritoModel();
        $cartCount    = $carritoModel->countItems((int) $_SESSION['user_id']);
        $cartBadge    = $cartCount > 0
            ? ' <span class="badge bg-danger rounded-pill">' . $cartCount . '</span>'
            : '';
        $navActions .= '<a href="/?controller=Carrito&action=index" class="btn btn-outline-warning btn-sm position-relative">'
            . '<i class="bi bi-cart3"></i>' . $cartBadge . '</a>';
    } else {
        $navActions .= '<a href="/#panel-admin" class="btn btn-outline-danger btn-sm">'
            . '<i class="bi bi-speedometer2 me-1"></i>Panel</a>'
            . '<a href="/#venta-local" class="btn btn-outline-warning btn-sm">'
            . '<i class="bi bi-shop me-1"></i>Venta local</a>';
    }

    $navActions .= $logoutForm;
} else {
    $navActions = '<a href="/?controller=Usuario&action=login" class="btn btn-outline-light btn-sm">'
        . '<i class="bi bi-person-circle me-1"></i>Iniciar Sesión</a>'
        . '<a href="/?controller=Usuario&action=registro" class="btn btn-warning btn-sm fw-semibold">'
        . '<i class="bi bi-person-plus-fill me-1"></i>Registrarse</a>';
}

// ── Alertas de gestión ────────────────────────────────────────────────────────
$menuAlert = '';
if (!empty($_GET['upload_error']) || !empty($_GET['menu_error']) || !empty($_GET['menu_status']) || !empty($_GET['favoritos_status']) || !empty($_GET['favoritos_error']) || !empty($_GET['local_status']) || !empty($_GET['local_error'])) {
    $message = '';
    $class = 'danger';

    if (!empty($_GET['upload_error'])) {
        $errores = [
            '1'       => 'Error al subir el archivo.',
            'tipo'    => 'Solo se permiten imágenes JPG, PNG, GIF o WEBP.',
            'tamano'  => 'La imagen supera el límite de 5 MB.',
            'guardado'=> 'No se pudo guardar la imagen en el servidor.',
        ];
        $codigo = $_GET['upload_error'];
        $message = $errores[$codigo] ?? 'Error desconocido al subir la imagen.';
    } elseif (!empty($_GET['menu_error'])) {
        $errores = [
            'campos' => 'Completá nombre, categoría, descripción y precio.',
            'precio' => 'El precio debe ser un número mayor a 0.',
            'id' => 'El plato seleccionado no es válido.',
            'noexiste' => 'El plato ya no existe o fue eliminado.',
        ];
        $codigo = $_GET['menu_error'];
        $message = $errores[$codigo] ?? 'No se pudo completar la acción sobre el menú.';
    } elseif (!empty($_GET['favoritos_error'])) {
        $errores = [
            'id' => 'No se pudo identificar el plato para favoritos.',
        ];
        $codigo = $_GET['favoritos_error'];
        $message = $errores[$codigo] ?? 'No se pudo actualizar favoritos.';
    } elseif (!empty($_GET['local_error'])) {
        $errores = [
            'sin_items' => 'Ingresá al menos una cantidad para registrar una venta en local.',
        ];
        $codigo = $_GET['local_error'];
        $message = $errores[$codigo] ?? 'No se pudo registrar la venta en local.';
    } else {
        $class = 'success';
        $mensajes = [
            'created' => 'Plato creado correctamente.',
            'updated' => 'Plato actualizado correctamente.',
            'deleted' => 'Plato eliminado correctamente.',
            'added' => 'Plato agregado a favoritos.',
            'removed' => 'Plato quitado de favoritos.',
            'ok' => 'Venta en local registrada correctamente.',
        ];
        $codigo = $_GET['menu_status'] ?? $_GET['favoritos_status'] ?? $_GET['local_status'];
        $message = $mensajes[$codigo] ?? 'Acción completada correctamente.';
    }

    $menuAlert = '<div class="alert alert-' . $class . '">'
        . '<i class="bi bi-' . ($class === 'success' ? 'check-circle' : 'exclamation-triangle') . ' me-1"></i>'
        . htmlspecialchars($message)
        . '</div>';
}

$adminPanel = '';
if (isAdmin()) {
    $csrfValue = htmlspecialchars(csrfToken());
    $editId = (int) ($editPlato['id'] ?? 0);
    $editNombre = htmlspecialchars($editPlato['nombre'] ?? '');
    $editCategoria = htmlspecialchars($editPlato['categoria'] ?? '');
    $editDescripcion = htmlspecialchars($editPlato['descripcion'] ?? '');
    $editPrecio = htmlspecialchars(isset($editPlato['precio']) ? number_format((float) $editPlato['precio'], 2, '.', '') : '');

    $editPanel = '';
    if ($editPlato) {
        $editPanel = '<div class="card border-warning shadow-sm mb-4">'
            . '<div class="card-body">'
            . '<div class="d-flex justify-content-between align-items-center mb-3">'
            . '<h5 class="card-title mb-0"><i class="bi bi-pencil-square me-2 text-warning"></i>Editar plato</h5>'
            . '<a href="/" class="btn btn-outline-secondary btn-sm">Cancelar edición</a>'
            . '</div>'
            . '<form method="POST" action="/?controller=Menu&action=update">'
            . '<input type="hidden" name="csrf_token" value="' . $csrfValue . '">'
            . '<input type="hidden" name="id" value="' . $editId . '">'
            . '<div class="row g-3">'
            . '<div class="col-md-6"><label class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" value="' . $editNombre . '" required></div>'
            . '<div class="col-md-3"><label class="form-label">Categoría</label><input type="text" class="form-control" name="categoria" value="' . $editCategoria . '" required></div>'
            . '<div class="col-md-3"><label class="form-label">Precio</label><input type="number" class="form-control" name="precio" min="0.01" step="0.01" value="' . $editPrecio . '" required></div>'
            . '<div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" rows="3" required>' . $editDescripcion . '</textarea></div>'
            . '<div class="col-12"><button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Guardar cambios</button></div>'
            . '</div></form></div></div>';
    }

    $metricCards = '<div class="row g-3 mb-4">'
        . '<div class="col-md-4"><div class="card shadow-sm border-0 bg-dark text-white"><div class="card-body"><div class="small text-uppercase text-white-50">Ventas online</div><div class="fs-3 fw-bold">$' . number_format((float) $salesSummary['total_online'], 2) . '</div><div class="small">' . (int) $salesSummary['ventas_online'] . ' compras registradas</div></div></div></div>'
        . '<div class="col-md-4"><div class="card shadow-sm border-0 bg-warning-subtle"><div class="card-body"><div class="small text-uppercase text-muted">Ventas en local</div><div class="fs-3 fw-bold">$' . number_format((float) $salesSummary['total_local'], 2) . '</div><div class="small">' . (int) $salesSummary['ventas_locales'] . ' tickets cargados</div></div></div></div>'
        . '<div class="col-md-4"><div class="card shadow-sm border-0 bg-danger text-white"><div class="card-body"><div class="small text-uppercase text-white-50">Facturación total</div><div class="fs-3 fw-bold">$' . number_format((float) $salesSummary['total_general'], 2) . '</div><div class="small">Dashboard administrativo</div></div></div></div>'
        . '</div>';

    $topSellingHtml = '<div class="card shadow-sm mb-4"><div class="card-body"><h5 class="card-title mb-3"><i class="bi bi-graph-up-arrow me-2 text-danger"></i>Platos más vendidos</h5>';
    if (empty($topSelling)) {
        $topSellingHtml .= '<p class="text-muted mb-0">Todavía no hay ventas registradas para generar ranking.</p>';
    } else {
        $rows = '';
        foreach ($topSelling as $index => $plato) {
            $rows .= '<tr>'
                . '<td class="fw-semibold">#' . ($index + 1) . '</td>'
                . '<td>' . htmlspecialchars($plato['nombre']) . '</td>'
                . '<td>' . htmlspecialchars($plato['categoria'] ?? 'Sin categoría') . '</td>'
                . '<td class="text-end">' . (int) $plato['unidades_vendidas'] . '</td>'
                . '<td class="text-end">$' . number_format((float) $plato['total_facturado'], 2) . '</td>'
                . '</tr>';
        }
        $topSellingHtml .= '<div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>#</th><th>Plato</th><th>Categoría</th><th class="text-end">Unidades</th><th class="text-end">Facturación</th></tr></thead><tbody>' . $rows . '</tbody></table></div>';
    }
    $topSellingHtml .= '</div></div>';

    $localSaleRows = '';
    foreach ($menus as $plato) {
        $platoId = (int) $plato['id'];
        $localSaleRows .= '<tr>'
            . '<td class="fw-semibold">' . htmlspecialchars($plato['nombre']) . '</td>'
            . '<td>' . htmlspecialchars($plato['categoria'] ?? 'Sin categoría') . '</td>'
            . '<td class="text-end">$' . number_format((float) $plato['precio'], 2) . '</td>'
            . '<td style="width:120px;"><input type="number" min="0" step="1" name="cantidad_' . $platoId . '" class="form-control form-control-sm" value="0"></td>'
            . '</tr>';
    }

    $localSalePanel = '<div id="venta-local" class="card shadow-sm mb-4"><div class="card-body">'
        . '<div class="d-flex justify-content-between align-items-start mb-3"><div><h5 class="card-title mb-1"><i class="bi bi-shop me-2 text-warning"></i>Venta en local</h5><p class="text-muted small mb-0">Usá este formulario para cargar consumos presenciales sin necesidad de login del cliente.</p></div></div>'
        . '<form method="POST" action="/?controller=Menu&action=localSale">'
        . '<input type="hidden" name="csrf_token" value="' . $csrfValue . '">'
        . '<div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Plato</th><th>Categoría</th><th class="text-end">Precio</th><th>Cantidad</th></tr></thead><tbody>' . $localSaleRows . '</tbody></table></div>'
        . '<button type="submit" class="btn btn-warning"><i class="bi bi-receipt me-1"></i>Registrar venta en local</button>'
        . '</form></div></div>';

    $adminPanel = '<div id="panel-admin">' . $metricCards . $topSellingHtml . $localSalePanel . '</div>'
        . '<div class="card shadow-sm mb-4">'
        . '<div class="card-body">'
        . '<h5 class="card-title mb-2"><i class="bi bi-shield-lock me-2 text-danger"></i>Panel de administración</h5>'
        . '<p class="text-muted small mb-3">Como administrador del sistema podés crear, modificar y eliminar platos. No tenés acceso a compras ni carrito.</p>'
        . '<form method="POST" action="/?controller=Menu&action=create">'
        . '<input type="hidden" name="csrf_token" value="' . $csrfValue . '">'
        . '<div class="row g-3">'
        . '<div class="col-md-4"><label class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" required></div>'
        . '<div class="col-md-4"><label class="form-label">Categoría</label><input type="text" class="form-control" name="categoria" required></div>'
        . '<div class="col-md-4"><label class="form-label">Precio</label><input type="number" class="form-control" name="precio" min="0.01" step="0.01" required></div>'
        . '<div class="col-12"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" rows="3" required></textarea></div>'
        . '<div class="col-12"><button type="submit" class="btn btn-danger"><i class="bi bi-plus-circle me-1"></i>Crear plato</button></div>'
        . '</div></form></div></div>'
        . $editPanel;
}

$favoriteIds = [];
if (isLoggedIn() && !isAdmin()) {
    require_once BASE_PATH . 'app/models/FavoritosModel.php';
    $favoritosModel = new FavoritosModel();
    $favoriteIds = $favoritosModel->getFavoriteIdsByUser((int) $_SESSION['user_id']);
}

$sort = $_GET['sort'] ?? 'default';
$sortOptions = [
    'default' => 'Orden por defecto',
    'precio_asc' => 'Precio: menor a mayor',
    'precio_desc' => 'Precio: mayor a menor',
    'nombre_asc' => 'Nombre: A-Z',
    'nombre_desc' => 'Nombre: Z-A',
];

$sortHtml = '<form method="GET" action="/" class="row g-2 align-items-end mb-4">'
    . '<input type="hidden" name="controller" value="Menu">'
    . '<input type="hidden" name="action" value="index">'
    . '<div class="col-sm-8 col-md-4"><label class="form-label small text-muted">Ordenar menú</label><select name="sort" class="form-select">';
foreach ($sortOptions as $value => $label) {
    $selected = $sort === $value ? ' selected' : '';
    $sortHtml .= '<option value="' . htmlspecialchars($value) . '"' . $selected . '>' . htmlspecialchars($label) . '</option>';
}
$sortHtml .= '</select></div><div class="col-sm-4 col-md-auto"><button type="submit" class="btn btn-outline-secondary w-100">Aplicar</button></div></form>';

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

            $adminButtons = '';
            if (isAdmin()) {
                $adminButtons = '<div class="d-flex gap-2 mt-2">'
                . '<a href="/?edit_plato=' . $id . '" class="btn btn-outline-warning btn-sm flex-fill">'
                . '<i class="bi bi-pencil-square me-1"></i>Editar</a>'
                . '<form method="POST" action="/?controller=Menu&action=delete" class="flex-fill">'
                . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">'
                . '<input type="hidden" name="id" value="' . $id . '">'
                . '<button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm(\'¿Eliminar este plato del menú?\')">'
                . '<i class="bi bi-trash3 me-1"></i>Eliminar</button></form>'
                . '</div>';
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

        $favoritoBtn = '';
        if (isLoggedIn() && !isAdmin()) {
            $isFavorite = in_array($id, $favoriteIds, true);
            $favoritoBtn = '<form method="POST" action="/?controller=Favoritos&action=toggle" class="mt-2">'
                . '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">'
                . '<input type="hidden" name="plato_id" value="' . $id . '">'
                . '<button type="submit" class="btn btn-' . ($isFavorite ? 'outline-danger' : 'outline-secondary') . ' btn-sm w-100">'
                . '<i class="bi bi-heart' . ($isFavorite ? '-fill' : '') . ' me-1"></i>'
                . ($isFavorite ? 'Quitar de favoritos' : 'Agregar a favoritos')
                . '</button></form>';
        }

        $menuCards .= '<div class="col-sm-6 col-md-4 col-xl-3">'
            . '<div class="card menu-card h-100 shadow-sm">'
            . $imagenHtml
            . '<div class="card-body d-flex flex-column">'
            . '<h5 class="card-title menu-card-title mb-1">' . $nombre . '</h5>'
            . '<span class="badge menu-category mb-2 align-self-start">' . $categoria . '</span>'
            . '<p class="card-text menu-description small text-muted flex-grow-1">' . $descripcion . '</p>'
            . '<p class="precio mt-auto mb-2">$' . $precio . '</p>'
            . $carritoBtn
            . $favoritoBtn
            . $adminButtons
            . $uploadForm
            . '</div>'
            . '</div>'
            . '</div>';
    }
} else {
    $menuCards = '<p class="text-muted section-empty">Actualmente no hay platos disponibles en la carta.</p>';
}

// ── Renderizar template ───────────────────────────────────────────────────────
$templatePath = BASE_PATH . 'app/views/home.template.html';
$template     = file_get_contents($templatePath);

if ($template === false) {
    echo 'Error: No se pudo cargar la plantilla HTML de la vista.';
    return;
}

echo str_replace(
    ['{{NAV_ACTIONS}}', '{{MENU_ALERT}}', '{{ADMIN_PANEL}}', '{{SORT_CONTROLS}}', '{{MENU_CARDS}}', '{{BROWSER_TITLE}}', '{{PAGE_TITLE}}', '{{PAGE_SUBTITLE}}'],
    [$navActions,       $menuAlert,        $adminPanel,      $sortHtml,          $menuCards,       isAdmin() ? 'Sala de gestión' : 'Inicio', isAdmin() ? 'Sala de gestión' : 'Mangiare a presto', isAdmin() ? 'Gestioná ventas, menú y métricas del restaurante desde un solo lugar.' : 'Pastas artesanales, clásicos italianos y un menú pensado para pedir sin fricción.'],
    $template
);
