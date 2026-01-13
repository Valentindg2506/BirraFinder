<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['id_usuario'];

$sql = "SELECT * FROM bares WHERE usuario_id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$bares = [];
while ($fila = $resultado->fetch_assoc()) {
    $bares[] = $fila;
}

echo json_encode($bares);

$stmt->close();
$conexion->close();
?>
