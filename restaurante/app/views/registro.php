<?php
$safeName = htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8');
$safeEmail = htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
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
        <div class="card shadow-sm" style="width: 100%; max-width: 440px;">
            <div class="card-body p-4">
                <h3 class="card-title mb-1 fw-bold"><i class="bi bi-person-plus"></i> Registro</h3>
                <p class="text-muted mb-4">Creá tu cuenta para continuar</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form method="POST" action="/?controller=Usuario&action=registro">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input id="name" name="name" type="text" class="form-control" value="<?php echo $safeName; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" class="form-control" value="<?php echo $safeEmail; ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input id="password" name="password" type="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Registrarme</button>
                </form>

                <hr class="my-3">
                <p class="text-center text-muted mb-0">¿Ya tenés cuenta? <a href="/?controller=Usuario&action=login">Iniciá sesión</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
