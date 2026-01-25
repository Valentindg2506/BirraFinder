<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $usuario_id = $_SESSION['id_usuario'];

    if ($accion === 'agregar') {
        $nombre = $_POST['nombre'];
        $direccion = $_POST['direccion'] ?? '';
        $lat = !empty($_POST['lat']) ? $_POST['lat'] : null;
        $lng = !empty($_POST['lng']) ? $_POST['lng'] : null;
        $estado = $_POST['estado']; // 'visitado', 'pendiente'
        $puntuacion = !empty($_POST['puntuacion']) ? $_POST['puntuacion'] : null;
        $comentario = $_POST['comentario'] ?? '';

        $sql = "INSERT INTO bares (usuario_id, nombre, direccion, lat, lng, estado, puntuacion, comentario) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("issddsis", $usuario_id, $nombre, $direccion, $lat, $lng, $estado, $puntuacion, $comentario);
        
        if ($stmt->execute()) {
            header("Location: ../../dashboard.php?exito=1");
        } else {
            header("Location: ../../dashboard.php?error=db");
        }
        $stmt->close();

    } elseif ($accion === 'eliminar') {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM bares WHERE id = ? AND usuario_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id, $usuario_id);
        
        if ($stmt->execute()) {
            header("Location: ../../dashboard.php?exito=eliminado");
        } else {
            header("Location: ../../dashboard.php?error=db");
        }
        $stmt->close();
    } elseif ($accion === 'editar') {
        // Implement edit logic similarly
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $estado = $_POST['estado'];
        $puntuacion = !empty($_POST['puntuacion']) ? $_POST['puntuacion'] : null;
        $comentario = $_POST['comentario'] ?? '';

        $sql = "UPDATE bares SET nombre = ?, estado = ?, puntuacion = ?, comentario = ? WHERE id = ? AND usuario_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssisii", $nombre, $estado, $puntuacion, $comentario, $id, $usuario_id);
        
        if ($stmt->execute()) {
            header("Location: ../../dashboard.php?exito=editado");
        } else {
            header("Location: ../../dashboard.php?error=edit");
        }
        $stmt->close();
    }
}
$conexion->close();
?>
