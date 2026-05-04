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

// ── Badge de rol ──────────────────────────────────────────────────────────────
$roleBadge = isAdmin()
    ? '<span class="badge bg-danger me-1">Admin</span>'
    : '<span class="badge bg-secondary me-1">Cliente</span>';

// ── Datos del usuario (sanitizados) ──────────────────────────────────────────
$userName  = htmlspecialchars($user['name']  ?? '');
$userEmail = htmlspecialchars($user['email'] ?? '');
$csrfField = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';

// ── Renderizar ────────────────────────────────────────────────────────────────
$templatePath = BASE_PATH . 'app/views/perfil.template.html';
$template     = file_get_contents($templatePath);

if ($template === false) {
    echo 'Error: No se pudo cargar la plantilla.';
    return;
}

echo str_replace(
    ['{{NAV_ACTIONS}}', '{{ROLE_BADGE}}', '{{SUCCESS}}', '{{ERROR}}',
     '{{CSRF_FIELD}}',  '{{EMAIL}}',      '{{NAME}}'],
    [$navActions,       $roleBadge,       $successHtml, $errorHtml,
     $csrfField,        $userEmail,       $userName],
    $template
);
