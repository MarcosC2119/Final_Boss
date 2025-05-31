<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include 'db.php';

// Manejo de peticiones OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

switch ($met) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Si se solicita un archivo específico
            $id = (int) $_GET['id'];
            if (isset($_GET['download'])) {
                // Obtener el archivo para descarga
                $sql = "SELECT archivo_nombre, archivo_tipo, archivo_contenido FROM Capacitaciones WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $row['archivo_nombre'] . '"');
                    echo $row['archivo_contenido'];
                    exit;
                }
            } else {
                // Obtener datos de la capacitación (sin el contenido del archivo)
                $sql = "SELECT id, titulo, descripcion, archivo_nombre, archivo_tipo, fecha_creacion, fecha_actualizacion, estado FROM Capacitaciones WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $capacitaciones = [];
                while ($row = $result->fetch_assoc()) {
                    $capacitaciones[] = $row;
                }
                echo json_encode($capacitaciones);
            }
        } else {
            // Listar todas las capacitaciones (sin el contenido de los archivos)
            $sql = "SELECT id, titulo, descripcion, archivo_nombre, archivo_tipo, fecha_creacion, fecha_actualizacion, estado FROM Capacitaciones";
            $result = $conn->query($sql);
            $capacitaciones = [];
            while ($row = $result->fetch_assoc()) {
                $capacitaciones[] = $row;
            }
            echo json_encode($capacitaciones);
        }
        break;

    case 'POST':
        $titulo = $_POST['titulo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $archivo_tipo = $_POST['tipo_archivo'] ?? '';
        $id = (isset($_POST['id']) && $_POST['id'] !== '') ? (int) $_POST['id'] : null;

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            if (!$id) {
                echo json_encode(['error' => 'Se requiere un archivo para crear una capacitación.']);
                exit;
            }
        }

        if ($id) {
            // Actualizar capacitación
            if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                // Actualizar con nuevo archivo
                $archivo_nombre = $_FILES['archivo']['name'];
                $archivo_contenido = file_get_contents($_FILES['archivo']['tmp_name']);
                $sql = "UPDATE Capacitaciones SET titulo = ?, descripcion = ?, archivo_nombre = ?, archivo_tipo = ?, archivo_contenido = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssi", $titulo, $descripcion, $archivo_nombre, $archivo_tipo, $archivo_contenido, $id);
            } else {
                // Actualizar sin cambiar el archivo
                $sql = "UPDATE Capacitaciones SET titulo = ?, descripcion = ?, archivo_tipo = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $titulo, $descripcion, $archivo_tipo, $id);
            }
            $mensaje = "Capacitación actualizada correctamente.";
        } else {
            // Crear nueva capacitación
            $archivo_nombre = $_FILES['archivo']['name'];
            $archivo_contenido = file_get_contents($_FILES['archivo']['tmp_name']);
            $sql = "INSERT INTO Capacitaciones (titulo, descripcion, archivo_nombre, archivo_tipo, archivo_contenido) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $titulo, $descripcion, $archivo_nombre, $archivo_tipo, $archivo_contenido);
            $mensaje = "Capacitación creada correctamente.";
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'mensaje' => $mensaje]);
        } else {
            echo json_encode(['error' => 'Error al guardar la capacitación: ' . $stmt->error]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $id = (int) $data['id'];
            $sql = "DELETE FROM Capacitaciones WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'mensaje' => 'Capacitación eliminada correctamente.']);
            } else {
                echo json_encode(['error' => 'Error al eliminar la capacitación: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['error' => 'Se requiere el id de la capacitación.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido.']);
        break;
}
?>