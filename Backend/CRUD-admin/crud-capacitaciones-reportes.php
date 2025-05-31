<?php
header('Content-Type: application/json');
include 'db.php';

switch ($metodo) {
    
    case 'GET':
        $sql = "SELECT * FROM proyectos";
        $resultado = $conn->query($sql);
        $proyectos = [];
    
        if ($resultado->num_rows > 0) {
            while($fila = $resultado->fetch_assoc()) {
                $proyectos[] = $fila;
            }
        }
        echo json_encode($proyectos);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        $nombre = $data['nombre'];
        $imagen = $data['imagen'];
        $descripcion = $data['descripcion'];
        $url_github = $data['url_github'];
        $url_produccion = $data['url_produccion'];

        $sql = "INSERT INTO proyectos (nombre, imagen, descripcion, url_github, url_produccion)
                VALUES ('$nombre', '$imagen', '$descripcion', '$url_github', '$url_produccion')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["mensaje" => "Proyecto creado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al crear el proyecto"]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        $sql = "DELETE FROM proyectos WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["mensaje" => "Proyecto eliminado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al eliminar el proyecto"]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $data['id'];
        $nombre = $data['nombre'];
        $imagen = $data['imagen'];
        $descripcion = $data['descripcion'];
        $url_github = $data['url_github'];
        $url_produccion = $data['url_produccion'];

        $sql = "UPDATE proyectos 
                SET nombre = '$nombre', imagen = '$imagen', descripcion = '$descripcion', 
                    url_github = '$url_github', url_produccion = '$url_produccion' 
                WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["mensaje" => "Proyecto actualizado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al actualizar el proyecto"]);
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Método no permitido']);
        break;
}
?>