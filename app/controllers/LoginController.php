<?php
/**
 * CONTROLADOR: PROCESAR LOGIN
 */
session_start();
require_once '../../config/db.php'; 

if (isset($_POST['email']) && isset($_POST['contrasena'])) {
    
    $email = $_POST['email'];
    $pass_ingresada = $_POST['contrasena'];

    // 1. Buscamos por EMAIL
    $sql = "SELECT id, nombre, password FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email); 
    $stmt->execute();
    
    $resultado = $stmt->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        
        $hash_guardado = $fila['password']; 

        if (password_verify($pass_ingresada, $hash_guardado)) {
        
            $_SESSION['usuario'] = $fila['nombre']; // Display name
            $_SESSION['id_usuario'] = $fila['id'];
            $_SESSION['email'] = $email;
            
            header("Location: ../../dashboard.php"); 
            exit;
        } else {
            header("Location: ../../index.php?error=1");
            exit;
        }

    } else {
        header("Location: ../../index.php?error=1");
        exit;
    }

    $stmt->close();
    $conexion->close();

} else {
    header("Location: ../index.php");
}
?>
