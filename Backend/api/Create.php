<?php

require_once 'db.php';



function createSala($nombre, $ubicacion, $capacidad) {
    $sql = "INSERT INTO salas (nombre, ubicacion, capacidad) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nombre, $ubicacion, $capacidad]);
}



?>