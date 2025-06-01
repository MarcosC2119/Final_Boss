<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para logging
function logError($message, $data = null) {
    error_log("Error en crud-capacitaciones.php: " . $message);
    if ($data !== null) {
        error_log("Datos: " . print_r($data, true));
    }
}

include __DIR__ . '/../config/db.php';
$conn = Database::getInstance()->getConnection();

// Verificar la conexión a la base de datos
if ($conn->connect_error) {
    logError("Error de conexión a la base de datos: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener el método HTTP
$met = $_SERVER['REQUEST_METHOD'];

// Manejo de peticiones OPTIONS
if ($met === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    switch ($met) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Si se solicita un archivo específico
                $id = (int) $_GET['id'];
                if (isset($_GET['download'])) {
                    // Obtener el archivo para descarga
                    $sql = "SELECT archivo_nombre, archivo_tipo, archivo_contenido FROM Capacitaciones WHERE id = ? AND estado = 1";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($row = $result->fetch_assoc()) {
                        // Establecer los headers correctos según el tipo de archivo
                        $contentType = $row['archivo_tipo'] === 'PDF' ? 'application/pdf' : 
                                      ($row['archivo_tipo'] === 'WORD' ? 'application/msword' : 'application/octet-stream');
                        
                        header('Content-Type: ' . $contentType);
                        header('Content-Disposition: attachment; filename="' . $row['archivo_nombre'] . '"');
                        header('Content-Length: ' . strlen($row['archivo_contenido']));
                        header('Cache-Control: no-cache, must-revalidate');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                        
                        echo $row['archivo_contenido'];
                        exit;
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Archivo no encontrado o capacitación inactiva']);
                        exit;
                    }
                } else {
                    // Obtener datos de la capacitación (sin el contenido del archivo)
                    $sql = "SELECT id, titulo, descripcion, archivo_nombre, archivo_tipo, fecha_creacion, fecha_actualizacion, estado 
                            FROM Capacitaciones WHERE id = ?";
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
                $sql = "SELECT id, titulo, descripcion, archivo_nombre, archivo_tipo, fecha_creacion, fecha_actualizacion, estado 
                        FROM Capacitaciones ORDER BY fecha_creacion DESC";
                $result = $conn->query($sql);
                $capacitaciones = [];
                while ($row = $result->fetch_assoc()) {
                    $capacitaciones[] = $row;
                }
                echo json_encode($capacitaciones);
            }
            break;

        case 'POST':
            // Log de los datos recibidos
            logError("Datos POST recibidos", $_POST);
            logError("Archivos recibidos", $_FILES);

            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $archivo_tipo = $_POST['tipo_archivo'] ?? '';
            $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;
            $id = (isset($_POST['id']) && $_POST['id'] !== '') ? (int) $_POST['id'] : null;

            // Validaciones básicas
            if (empty($titulo)) {
                throw new Exception('El título es requerido');
            }

            if (!in_array($archivo_tipo, ['PDF', 'WORD'])) {
                throw new Exception('Tipo de archivo inválido: ' . $archivo_tipo);
            }

            // Validar archivo si se está subiendo uno nuevo
            if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['archivo'];
                logError("Procesando archivo", [
                    'nombre' => $file['name'],
                    'tipo' => $file['type'],
                    'tamaño' => $file['size'],
                    'error' => $file['error']
                ]);

                $file_type = strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                // Validar tamaño (máximo 10MB)
                if ($file['size'] > 10 * 1024 * 1024) {
                    throw new Exception('El archivo es demasiado grande. Máximo permitido: 10MB');
                }

                // Validar tipo de archivo
                if (($archivo_tipo === 'PDF' && $file_type !== 'PDF') || 
                    ($archivo_tipo === 'WORD' && !in_array($file_type, ['DOC', 'DOCX']))) {
                    throw new Exception('El tipo de archivo no coincide con el formato seleccionado');
                }

                // Validar contenido del archivo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                logError("MIME type detectado", $mime_type);

                $allowed_mimes = [
                    'PDF' => ['application/pdf'],
                    'WORD' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
                ];

                if (!in_array($mime_type, $allowed_mimes[$archivo_tipo])) {
                    throw new Exception('El archivo no es válido o está corrupto. Tipo MIME: ' . $mime_type);
                }

                $archivo_nombre = $file['name'];
                $archivo_contenido = file_get_contents($file['tmp_name']);
                
                if ($archivo_contenido === false) {
                    throw new Exception('Error al leer el archivo');
                }
            } elseif (!$id) {
                throw new Exception('Se requiere un archivo para crear una capacitación');
            }

            if ($id) {
                // Actualizar capacitación
                if (isset($archivo_contenido)) {
                    $sql = "UPDATE Capacitaciones SET titulo = ?, descripcion = ?, archivo_nombre = ?, 
                            archivo_tipo = ?, archivo_contenido = ?, estado = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception('Error al preparar la consulta: ' . $conn->error);
                    }
                    $stmt->bind_param("sssssii", $titulo, $descripcion, $archivo_nombre, 
                                    $archivo_tipo, $archivo_contenido, $estado, $id);
                } else {
                    $sql = "UPDATE Capacitaciones SET titulo = ?, descripcion = ?, archivo_tipo = ?, 
                            estado = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception('Error al preparar la consulta: ' . $conn->error);
                    }
                    $stmt->bind_param("sssii", $titulo, $descripcion, $archivo_tipo, $estado, $id);
                }
                $mensaje = "Capacitación actualizada correctamente";
            } else {
                // Crear nueva capacitación
                $sql = "INSERT INTO Capacitaciones (titulo, descripcion, archivo_nombre, archivo_tipo, 
                        archivo_contenido, estado) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Error al preparar la consulta: ' . $conn->error);
                }
                $stmt->bind_param("sssssi", $titulo, $descripcion, $archivo_nombre, 
                                $archivo_tipo, $archivo_contenido, $estado);
                $mensaje = "Capacitación creada correctamente";
            }

            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            }

            echo json_encode(['success' => true, 'mensaje' => $mensaje]);
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"), true);
            if (isset($data['id'])) {
                $id = (int) $data['id'];
                // En lugar de eliminar, marcamos como inactivo
                $sql = "UPDATE Capacitaciones SET estado = 0 WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'mensaje' => 'Capacitación desactivada correctamente']);
                } else {
                    echo json_encode(['error' => 'Error al desactivar la capacitación: ' . $stmt->error]);
                }
            } else {
                echo json_encode(['error' => 'Se requiere el id de la capacitación']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido.']);
            break;
    }
} catch (Exception $e) {
    logError("Excepción capturada: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Error $e) {
    logError("Error PHP: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>