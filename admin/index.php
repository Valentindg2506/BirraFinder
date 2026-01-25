<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - BirraFinder</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h1>Admin Panel</h1>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">Acceso Denegado.</div>
        <?php endif; ?>

        <form action="../app/controllers/AdminController.php" method="POST" class="auth-form active">
            <input type="hidden" name="action" value="login">
            <div class="input-group">
                <label>Admin Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Entrar como Admin</button>
        </form>
    </div>
</body>
</html>
