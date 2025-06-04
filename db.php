<?php
$host = "localhost";
$db = "portafoliov2";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}
?>