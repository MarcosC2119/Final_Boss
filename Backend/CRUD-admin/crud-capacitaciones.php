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
                    $sql = "SELECT archivo_nombre, archivo_tipo, archivo_contenido FROM Capacitaciones WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($row = $result->fetch_assoc()) {
                        // Establecer los headers correctos según el tipo de archivo
                        $contentType = match($row['archivo_tipo']) {
                            'PDF' => 'application/pdf',
                            'WORD' => strpos($row['archivo_nombre'], '.docx') !== false ? 
                                     'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : 
                                     'application/msword',
                            default => 'application/octet-stream'
                        };
                        
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
            $id = (isset($_POST['id']) && $_POST['id'] !== '' && $_POST['id'] !== '0') ? (int) $_POST['id'] : null;

            // ✅ DEBUGGING CRÍTICO
            logError("ID recibido para edición", [
                'id_raw' => $_POST['id'] ?? 'no_set',
                'id_processed' => $id,
                'id_type' => gettype($id),
                'is_edit' => $id !== null ? 'YES' : 'NO',
                'titulo' => $titulo
            ]);

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
                    'PDF' => [
                        'application/pdf',
                        'application/x-pdf',
                        'application/acrobat',
                        'applications/vnd.pdf',
                        'text/pdf',
                        'text/x-pdf'
                    ],
                    'WORD' => [
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-word',
                        'application/doc',
                        'application/ms-doc',
                        'application/x-msword',
                        'application/x-doc',
                        'application/word'
                    ]
                ];

                // Validación más permisiva
                $mime_valid = false;
                foreach ($allowed_mimes[$archivo_tipo] as $allowed_mime) {
                    if (strpos($mime_type, $allowed_mime) !== false || $mime_type === $allowed_mime) {
                        $mime_valid = true;
                        break;
                    }
                }

                if (!$mime_valid) {
                    // Log pero permitir el archivo si la extensión es correcta
                    logError("MIME type no reconocido, pero extensión válida", [
                        'detected' => $mime_type,
                        'expected' => $allowed_mimes[$archivo_tipo],
                        'extension' => $file_type
                    ]);
                }

                $archivo_nombre = $file['name'];
                $archivo_contenido = file_get_contents($file['tmp_name']);
                
                if ($archivo_contenido === false) {
                    throw new Exception('Error al leer el archivo');
                }
            } elseif (!$id) {
                throw new Exception('Se requiere un archivo para crear una capacitación');
            } elseif (isset($_FILES['archivo']) && $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Manejo detallado de errores de subida
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP',
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo especificado en el formulario',
                    UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal',
                    UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco',
                    UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo'
                ];
                
                $error_code = $_FILES['archivo']['error'];
                $error_message = isset($upload_errors[$error_code]) ? 
                                $upload_errors[$error_code] : 
                                'Error desconocido en la subida del archivo (código: ' . $error_code . ')';
                
                throw new Exception($error_message);
            }

            if ($id) {
                logError("Entrando en modo ACTUALIZACIÓN", ['id' => $id, 'titulo' => $titulo]);
                
                // ✅ VERIFICAR QUE EL REGISTRO EXISTE ANTES DE ACTUALIZAR
                $check_sql = "SELECT id FROM Capacitaciones WHERE id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $id);
                $check_stmt->execute();
                $exists = $check_stmt->get_result()->num_rows > 0;
                
                if (!$exists) {
                    throw new Exception("No se encontró la capacitación con ID: $id");
                }
                
                logError("Capacitación encontrada, procediendo con actualización", ['id' => $id]);
                
                // Actualizar capacitación
                if (isset($archivo_contenido)) {
                    logError("Actualizando CON archivo nuevo", ['id' => $id]);
                    $sql = "UPDATE Capacitaciones SET titulo = ?, descripcion = ?, archivo_nombre = ?, 
                            archivo_tipo = ?, archivo_contenido = ?, estado = ?, fecha_actualizacion = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception('Error al preparar la consulta: ' . $conn->error);
                    }
                    $stmt->bind_param("ssssbie", $titulo, $descripcion, $archivo_nombre, 
                                    $archivo_tipo, $archivo_contenido, $estado, $id);
                } else {
                    logError("Actualizando SIN archivo nuevo", ['id' => $id]);
                    $sql = "UPDATE Capacitaciones SET titulo = ?, descripcion = ?, archivo_tipo = ?, 
                            estado = ?, fecha_actualizacion = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception('Error al preparar la consulta: ' . $conn->error);
                    }
                    $stmt->bind_param("sssii", $titulo, $descripcion, $archivo_tipo, $estado, $id);
                }
                $mensaje = "Capacitación actualizada correctamente";
            } else {
                logError("Entrando en modo CREACIÓN", ['titulo' => $titulo]);
                // Crear nueva capacitación
                $sql = "INSERT INTO Capacitaciones (titulo, descripcion, archivo_nombre, archivo_tipo, 
                        archivo_contenido, estado) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception('Error al preparar la consulta: ' . $conn->error);
                }
                $stmt->bind_param("ssssbi", $titulo, $descripcion, $archivo_nombre, 
                                $archivo_tipo, $archivo_contenido, $estado);
                $mensaje = "Capacitación creada correctamente";
            }

            // ✅ EJECUTAR Y VERIFICAR RESULTADO
            if (!$stmt->execute()) {
                logError("Error al ejecutar consulta", ['error' => $stmt->error, 'query' => $sql]);
                throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
            }

            // ✅ VERIFICAR FILAS AFECTADAS
            $rows_affected = $stmt->affected_rows;
            logError("Consulta ejecutada", [
                'rows_affected' => $rows_affected,
                'is_update' => $id !== null,
                'mensaje' => $mensaje
            ]);

            if ($id && $rows_affected === 0) {
                throw new Exception("No se pudo actualizar la capacitación. Es posible que no exista o no haya cambios.");
            }

            echo json_encode(['success' => true, 'mensaje' => $mensaje]);
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"), true);
            logError("Datos DELETE recibidos", $data);
            
            if (isset($data['id'])) {
                $id = (int) $data['id'];
                logError("Eliminando capacitación", ['id' => $id]);
                
                // Verificar que existe
                $check_sql = "SELECT id FROM Capacitaciones WHERE id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $id);
                $check_stmt->execute();
                
                if ($check_stmt->get_result()->num_rows === 0) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Capacitación no encontrada']);
                    break;
                }
                
                // Marcar como inactivo
                $sql = "DELETE FROM Capacitaciones WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    logError("Capacitación eliminada exitosamente", ['id' => $id]);
                    echo json_encode(['success' => true, 'mensaje' => 'Capacitación eliminada correctamente']);
                } else {
                    logError("Error al eliminar capacitación", ['id' => $id, 'error' => $stmt->error]);
                    echo json_encode(['error' => 'Error al eliminar la capacitación: ' . $stmt->error]);
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