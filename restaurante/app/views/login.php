<?php
// Archivo: app/views/login.php

$registeredHtml = '';
if (!empty($_GET['registered'])) {
    $registeredHtml = '<div class="alert alert-success">'
        . '<i class="bi bi-check-circle me-1"></i>Cuenta creada. Iniciá sesión.'
        . '</div>';
}

$errorHtml = '';
if (!empty($error)) {
    $errorHtml = '<div class="alert alert-danger">'
        . '<i class="bi bi-exclamation-triangle me-1"></i>'
        . htmlspecialchars($error)
        . '</div>';
}

$csrfField = '<input type="hidden" name="csrf_token" value="'
    . htmlspecialchars(csrfToken()) . '">';

$templatePath = BASE_PATH . 'app/views/login.template.html';
$template = file_get_contents($templatePath);

if ($template === false) {
    echo 'Error: No se pudo cargar la plantilla.';
    return;
}

echo str_replace(
    ['{{REGISTERED}}', '{{ERROR}}', '{{CSRF_FIELD}}'],
    [$registeredHtml,  $errorHtml,  $csrfField],
    $template
);
