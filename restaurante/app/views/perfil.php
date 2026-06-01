<?php
// Archivo: app/views/perfil.php

// ── Navbar ────────────────────────────────────────────────────────────────────
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
    . $logoutForm
    . '</div>';

// ── Alertas ───────────────────────────────────────────────────────────────────
$successHtml = '';
if (!empty($success)) {
    $successHtml = '<div class="alert alert-success">'
        . '<i class="bi bi-check-circle me-1"></i>'
        . htmlspecialchars($success)
        . '</div>';
}

$errorHtml = '';
if (!empty($error)) {
    $errorHtml = '<div class="alert alert-danger">'
        . '<i class="bi bi-exclamation-triangle me-1"></i>'
        . htmlspecialchars($error)
        . '</div>';
}

$adminAlerts = '';
if (isAdmin() && (!empty($_GET['admin_status']) || !empty($_GET['admin_error']))) {
    $class = 'success';
    $message = '';

    if (!empty($_GET['admin_status'])) {
        $messages = [
            'created' => 'Administrador creado correctamente.',
            'deleted' => 'Administrador eliminado correctamente.',
        ];
        $message = $messages[$_GET['admin_status']] ?? 'Acción completada correctamente.';
    } else {
        $class = 'danger';
        $messages = [
            'campos' => 'Completá todos los campos del nuevo administrador.',
            'email' => 'El email del administrador no es válido.',
            'password' => 'La contraseña debe tener al menos 8 caracteres.',
            'confirm' => 'Las contraseñas no coinciden.',
            'exists' => 'Ya existe un usuario con ese email.',
            'id' => 'El administrador seleccionado no es válido.',
            'self' => 'No podés eliminar tu propia cuenta de administrador.',
            'noexiste' => 'El administrador seleccionado no existe.',
        ];
        $message = $messages[$_GET['admin_error']] ?? 'No se pudo completar la gestión de administradores.';
    }

    $adminAlerts = '<div class="alert alert-' . $class . '">'
        . '<i class="bi bi-' . ($class === 'success' ? 'check-circle' : 'exclamation-triangle') . ' me-1"></i>'
        . htmlspecialchars($message)
        . '</div>';
}

// ── Badge de rol ──────────────────────────────────────────────────────────────
$roleBadge = isAdmin()
    ? '<span class="badge bg-danger me-1">Admin</span>'
    : '<span class="badge bg-secondary me-1">Cliente</span>';

// ── Datos del usuario (sanitizados) ──────────────────────────────────────────
$userName  = htmlspecialchars($user['name']  ?? '');
$userEmail = htmlspecialchars($user['email'] ?? '');
$csrfField = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';

$clientSection = '';
if (!isAdmin()) {
    $favoritosHtml = '';
    if (empty($favorites)) {
        $favoritosHtml = '<p class="text-muted mb-0">Todavía no agregaste platos a favoritos.</p>';
    } else {
        foreach ($favorites as $favorite) {
            $favoritosHtml .= '<div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">'
                . '<div><div class="fw-semibold">' . htmlspecialchars($favorite['nombre']) . '</div><div class="small text-muted">' . htmlspecialchars($favorite['categoria'] ?? 'Sin categoría') . ' · $' . number_format((float) $favorite['precio'], 2) . '</div></div>'
                . '<form method="POST" action="/?controller=Favoritos&action=toggle">'
                . $csrfField
                . '<input type="hidden" name="plato_id" value="' . (int) $favorite['id'] . '">'
                . '<button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-heartbreak me-1"></i>Quitar</button>'
                . '</form></div>';
        }
    }

    $historialHtml = '';
    if (empty($purchaseHistory)) {
        $historialHtml = '<p class="text-muted mb-0">Todavía no tenés compras registradas.</p>';
    } else {
        foreach ($purchaseHistory as $purchase) {
            $itemsHtml = '';
            foreach ($purchase['items'] as $item) {
                $itemsHtml .= '<li class="list-group-item d-flex justify-content-between align-items-center">'
                    . '<span>' . htmlspecialchars($item['nombre']) . ' <span class="text-muted small">x' . (int) $item['cantidad'] . '</span></span>'
                    . '<span class="fw-semibold">$' . number_format((float) $item['precio_unitario'] * (int) $item['cantidad'], 2) . '</span>'
                    . '</li>';
            }

            $historialHtml .= '<div class="card shadow-sm mb-3"><div class="card-body">'
                . '<div class="d-flex justify-content-between align-items-center mb-2"><div><div class="fw-semibold">Compra #' . (int) $purchase['id'] . '</div><div class="small text-muted">' . htmlspecialchars($purchase['fecha']) . '</div></div><div class="text-end fw-bold text-success">$' . number_format((float) $purchase['total'], 2) . '</div></div>'
                . '<ul class="list-group list-group-flush">' . $itemsHtml . '</ul>'
                . '</div></div>';
        }
    }

    $clientSection = '<div class="card shadow-sm mt-4"><div class="card-body">'
        . '<h5 class="card-title"><i class="bi bi-heart me-2 text-danger"></i>Mis favoritos</h5>'
        . $favoritosHtml
        . '</div></div>'
        . '<div class="card shadow-sm mt-4"><div class="card-body">'
        . '<h5 class="card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Historial de compras</h5>'
        . $historialHtml
        . '</div></div>';
}

$adminSection = '';
if (isAdmin()) {
    $adminRows = '';
    foreach ($admins as $admin) {
        $adminId = (int) $admin['id'];
        $isCurrentAdmin = $adminId === (int) $_SESSION['user_id'];
        $adminRows .= '<tr>'
            . '<td class="fw-semibold">' . htmlspecialchars($admin['name'])
            . ($isCurrentAdmin ? ' <span class="badge bg-dark ms-1">Vos</span>' : '')
            . '</td>'
            . '<td>' . htmlspecialchars($admin['email']) . '</td>'
            . '<td class="text-end">';

        if ($isCurrentAdmin) {
            $adminRows .= '<span class="text-muted small">Cuenta activa</span>';
        } else {
            $adminRows .= '<form method="POST" action="/?controller=Usuario&action=deleteAdmin" class="d-inline">'
                . $csrfField
                . '<input type="hidden" name="admin_id" value="' . $adminId . '">'
                . '<button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm(\'¿Eliminar este administrador?\')">'
                . '<i class="bi bi-person-x me-1"></i>Eliminar</button>'
                . '</form>';
        }

        $adminRows .= '</td></tr>';
    }

    $adminSection = '<div class="card border-danger shadow-sm mt-4">'
        . '<div class="card-body">'
        . '<h5 class="card-title"><i class="bi bi-shield-lock me-2 text-danger"></i>Administración del sistema</h5>'
        . '<p class="text-muted small">El administrador puede crear otros admins, eliminarlos y gestionar el menú, pero no puede realizar compras.</p>'
        . $adminAlerts
        . '<form method="POST" action="/?controller=Usuario&action=createAdmin" class="mb-4">'
        . $csrfField
        . '<div class="row g-3">'
        . '<div class="col-md-6"><label class="form-label">Nombre</label><input type="text" class="form-control" name="name" required></div>'
        . '<div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" required></div>'
        . '<div class="col-md-6"><label class="form-label">Contraseña</label><input type="password" class="form-control" name="password" minlength="8" required></div>'
        . '<div class="col-md-6"><label class="form-label">Confirmar contraseña</label><input type="password" class="form-control" name="confirm" minlength="8" required></div>'
        . '<div class="col-12"><button type="submit" class="btn btn-danger"><i class="bi bi-person-plus-fill me-1"></i>Crear administrador</button></div>'
        . '</div></form>'
        . '<div class="table-responsive"><table class="table table-sm align-middle mb-0">'
        . '<thead><tr><th>Nombre</th><th>Email</th><th class="text-end">Acción</th></tr></thead>'
        . '<tbody>' . $adminRows . '</tbody>'
        . '</table></div></div></div>';
}

// ── Renderizar ────────────────────────────────────────────────────────────────
$templatePath = BASE_PATH . 'app/views/perfil.template.html';
$template     = file_get_contents($templatePath);

if ($template === false) {
    echo 'Error: No se pudo cargar la plantilla.';
    return;
}

echo str_replace(
    ['{{NAV_ACTIONS}}', '{{ROLE_BADGE}}', '{{SUCCESS}}', '{{ERROR}}',
     '{{CSRF_FIELD}}',  '{{EMAIL}}',      '{{NAME}}', '{{ADMIN_SECTION}}', '{{CLIENT_SECTION}}'],
    [$navActions,       $roleBadge,       $successHtml, $errorHtml,
     $csrfField,        $userEmail,       $userName,  $adminSection, $clientSection],
    $template
);
