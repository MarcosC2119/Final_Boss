<?php

require_once 'db.php';

header('Content-Type: application/json');

try {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    if (empty($data['nombre']) || empty($data['ubicacion']) || empty($data['capacidad'])) {
        throw new Exception('Todos los campos son requeridos');
    }

    $nombre = $data['nombre'];
    $ubicacion = $data['ubicacion'];
    $capacidad = $data['capacidad'];

    // Prepare and execute the query
    $sql = "INSERT INTO salas (nombre, ubicacion, capacidad) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nombre, $ubicacion, $capacidad]);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Sala creada exitosamente'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}



?>