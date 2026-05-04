<?php
// Archivo: app/views/registro.php

$errorHtml = '';
if (!empty($error)) {
    $errorHtml = '<div class="alert alert-danger">'
        . '<i class="bi bi-exclamation-triangle me-1"></i>'
        . htmlspecialchars($error)
        . '</div>';
}

$csrfField = '<input type="hidden" name="csrf_token" value="'
    . htmlspecialchars(csrfToken()) . '">';

$templatePath = BASE_PATH . 'app/views/registro.template.html';
$template = file_get_contents($templatePath);

if ($template === false) {
    echo 'Error: No se pudo cargar la plantilla.';
    return;
}

echo str_replace(
    ['{{ERROR}}', '{{CSRF_FIELD}}'],
    [$errorHtml,  $csrfField],
    $template
);
