<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BirraFinder - Bienvenido</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="background-glass"></div>
    <div class="login-container">
        <div class="login-header">
            <h1>BirraFinder</h1>
            <p>Descubre y guarda tus bares favoritos.</p>
        </div>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert error">Credenciales incorrectas.</div>
        <?php endif; ?>
        <?php if (isset($_GET['registro']) && $_GET['registro'] == 'ok'): ?>
            <div class="alert success">Registro exitoso. Inicia sesión.</div>
        <?php endif; ?>

        <!-- Login Form -->
        <form id="login" class="auth-form active" action="app/controllers/LoginController.php" method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="tu@email.com">
            </div>
            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="contrasena" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
            <p class="toggle-link" onclick="showTab('register')">¿No tienes cuenta? <span style="font-weight:600; text-decoration:underline;">Regístrate</span></p>
        </form>

        <!-- Register Form -->
        <form id="register" class="auth-form" action="app/controllers/RegisterController.php" method="POST">
            <div class="input-group">
                <label>Nombre Completo</label>
                <input type="text" name="nombrecompleto" required placeholder="Ej: Juan Pérez">
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="tu@email.com">
            </div>
            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="contrasena" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-primary">Registrarse</button>
            <p class="toggle-link" onclick="showTab('login')">¿Ya tienes cuenta? <span style="font-weight:600; text-decoration:underline;">Inicia Sesión</span></p>
        </form>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
        }
    </script>
</body>
</html>
