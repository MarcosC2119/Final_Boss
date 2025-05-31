<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "portafolio";

// Crear la conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar si hay error de conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>