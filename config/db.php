<?php
/**
 * DB Connection
 */

$host = "localhost";
$user = "bar_admin";
$pass = "BarTracker2026!";
$db   = "ProyectoBares";

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Connection failed: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>
