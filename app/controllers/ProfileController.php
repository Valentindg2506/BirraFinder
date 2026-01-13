<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: ../../index.php");
        exit;
    }

    $accion = $_POST['accion'] ?? '';
    $id_usuario = $_SESSION['id_usuario'];

    if ($accion === 'update_profile') {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);

        if (empty($nombre) || empty($email)) {
             header("Location: ../../dashboard.php?profile_error=Campos vacios");
             exit;
        }

        // Update DB
        $sql = "UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssi", $nombre, $email, $id_usuario);
        
        if ($stmt->execute()) {
            // Update Session
            $_SESSION['usuario'] = $nombre;
            $_SESSION['email'] = $email;
            header("Location: ../../dashboard.php?profile_success=Perfil actualizado");
        } else {
            header("Location: ../../dashboard.php?profile_error=Error al actualizar");
        }
        $stmt->close();

    } elseif ($accion === 'change_password') {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (empty($current_pass) || empty($new_pass)) {
            header("Location: ../../dashboard.php?profile_error=Contraseñas vacias");
            exit;
        }

        if ($new_pass !== $confirm_pass) {
            header("Location: ../../dashboard.php?profile_error=Las nuevas contraseñas no coinciden");
            exit;
        }

        // Verify old password
        $sql = "SELECT password FROM usuarios WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($current_pass, $user['password'])) {
            // Update Password
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
            $update_stmt = $conexion->prepare($update_sql);
            $update_stmt->bind_param("si", $new_hash, $id_usuario);
            
            if ($update_stmt->execute()) {
                header("Location: ../../dashboard.php?profile_success=Contraseña cambiada exitosamente");
            } else {
                header("Location: ../../dashboard.php?profile_error=Error al cambiar contraseña");
            }
            $update_stmt->close();
        } else {
            header("Location: ../../dashboard.php?profile_error=La contraseña actual es incorrecta");
        }
    } else {
        header("Location: ../../dashboard.php");
    }
}
?>
