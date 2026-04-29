<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso denegado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>body { background-color: #f8f5f0; }</style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/"><i class="bi bi-shop"></i> Restaurante</a>
        </div>
    </nav>

    <div class="container d-flex justify-content-center align-items-start py-5">
        <div class="card border-danger shadow-sm text-center" style="max-width: 480px; width: 100%;">
            <div class="card-body p-5">
                <i class="bi bi-shield-lock text-danger" style="font-size: 3.5rem;"></i>
                <h1 class="display-4 fw-bold text-danger mt-2">403</h1>
                <p class="text-muted mb-4">No tenés permiso para acceder a esta sección.<br>Solo los administradores pueden realizar esta acción.</p>
                <a href="/" class="btn btn-dark"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
