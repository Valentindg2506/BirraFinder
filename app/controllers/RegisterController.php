<?php
/**
 * CONTROLADOR: PROCESAR REGISTRO
 */
session_start();
require_once '../../config/db.php';

$nombre = $_POST['nombrecompleto'];
$email = $_POST['email'];
$contrasena = $_POST['contrasena'];

$errores = [];

// 1. Validar Email duplicado
$sql_check = "SELECT id FROM usuarios WHERE email = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $errores['email'] = "El correo ya está registrado.";
}
$stmt_check->close();
    
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores['email'] = "Email inválido.";
}

// 2. Insertar si no hay errores
if (!empty($errores)) {
    $_SESSION['errores'] = $errores;
    $_SESSION['datos_viejos'] = $_POST; 
    header("Location: ../../index.php?registro=error"); 
    exit;
}

$passHash = password_hash($contrasena, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sss", $nombre, $email, $passHash);

if($stmt->execute()){
    header("Location: ../../index.php?registro=ok"); 
} else {
    // Log error properly in real app
    header("Location: ../../index.php?registro=error_db");
}
?>
