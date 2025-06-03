<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para logging
function logError($message, $data = null) {
    error_log("Error en crud-manuales.php: " . $message);
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
$method = $_SERVER['REQUEST_METHOD'];

// Manejo de peticiones OPTIONS
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    switch ($method) {
        case 'GET':
            handleGetManuales();
            break;
        case 'POST':
            handleCreateOrUpdateManual();
            break;
        case 'PUT':
            handleUpdateManual();
            break;
        case 'DELETE':
            handleDeleteManual();
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
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

// ========== OBTENER MANUALES ==========
function handleGetManuales() {
    global $conn;
    
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        
        if (isset($_GET['download'])) {
            // Descargar archivo
            $sql = "SELECT archivo_nombre, archivo_tipo, archivo_contenido FROM Manuales WHERE id = ? AND estado = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $contentType = getContentType($row['archivo_tipo']);
                
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
                echo json_encode(['error' => 'Archivo no encontrado']);
                exit;
            }
        } else {
            // Obtener datos del manual (sin contenido del archivo)
            $sql = "SELECT m.*, u.nombre as creado_por_nombre 
                    FROM Manuales m 
                    LEFT JOIN usuarios u ON m.creado_por = u.id 
                    WHERE m.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Manual no encontrado']);
            }
        }
    } else {
        // Listar todos los manuales con filtros
        $categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
        $estado = isset($_GET['estado']) ? (int)$_GET['estado'] : null;
        $busqueda = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $where_conditions = [];
        $params = [];
        $types = "";
        
        if (!empty($categoria)) {
            $where_conditions[] = "m.categoria = ?";
            $params[] = $categoria;
            $types .= "s";
        }
        
        if ($estado !== null) {
            $where_conditions[] = "m.estado = ?";
            $params[] = $estado;
            $types .= "i";
        }
        
        if (!empty($busqueda)) {
            $where_conditions[] = "(m.titulo LIKE ? OR m.descripion LIKE ?)";
            $search_param = "%{$busqueda}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }
        
        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        $sql = "SELECT m.id, m.titulo, m.descripcion, m.categoria, m.version, 
                       m.archivo_nombre, m.archivo_tipo, m.estado, 
                       m.fecha_creacion, m.fecha_actualizacion,
                       u.nombre as creado_por_nombre
                FROM Manuales m 
                LEFT JOIN usuarios u ON m.creado_por = u.id 
                $where_clause 
                ORDER BY m.fecha_creacion DESC";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $manuales = [];
        while ($row = $result->fetch_assoc()) {
            $manuales[] = $row;
        }
        
        echo json_encode($manuales);
    }
}

// ========== CREAR O ACTUALIZAR MANUAL ==========
function handleCreateOrUpdateManual() {
    global $conn;
    
    logError("Datos POST recibidos", $_POST);
    logError("Archivos recibidos", $_FILES);
    
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria = $_POST['categoria'] ?? 'general';
    $version = trim($_POST['version'] ?? '1.0');
    $archivo_tipo = $_POST['tipo_archivo'] ?? 'PDF';
    $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;
    $creado_por = isset($_POST['creado_por']) ? (int)$_POST['creado_por'] : null;
    $id = (isset($_POST['id']) && $_POST['id'] !== '' && $_POST['id'] !== '0') ? (int) $_POST['id'] : null;
    
    // Validaciones
    if (empty($titulo)) {
        throw new Exception('El título es requerido');
    }
    
    if (!in_array($categoria, ['usuario', 'administrador', 'tecnico', 'general'])) {
        throw new Exception('Categoría inválida');
    }
    
    if (!in_array($archivo_tipo, ['PDF', 'WORD', 'HTML', 'TEXTO'])) {
        throw new Exception('Tipo de archivo inválido');
    }
    
    // Procesar archivo si se subió uno
    $archivo_nombre = null;
    $archivo_contenido = null;
    
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['archivo'];
        
        // Validar tamaño (máximo 15MB para manuales)
        if ($file['size'] > 15 * 1024 * 1024) {
            throw new Exception('El archivo es demasiado grande. Máximo permitido: 15MB');
        }
        
        $file_extension = strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validar tipo de archivo
        $allowed_extensions = [
            'PDF' => ['PDF'],
            'WORD' => ['DOC', 'DOCX'],
            'HTML' => ['HTML', 'HTM'],
            'TEXTO' => ['TXT']
        ];
        
        if (!in_array($file_extension, $allowed_extensions[$archivo_tipo])) {
            throw new Exception("El archivo no corresponde al tipo seleccionado. Se esperaba: " . implode(', ', $allowed_extensions[$archivo_tipo]));
        }
        
        $archivo_nombre = $file['name'];
        $archivo_contenido = file_get_contents($file['tmp_name']);
        
        if ($archivo_contenido === false) {
            throw new Exception('Error al leer el archivo');
        }
    } elseif (!$id) {
        throw new Exception('Se requiere un archivo para crear un manual');
    }
    
    if ($id) {
        // Actualizar manual existente
        $check_sql = "SELECT id FROM Manuales WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows === 0) {
            throw new Exception("No se encontró el manual con ID: $id");
        }
        
        if ($archivo_contenido !== null) {
            // Actualizar con archivo nuevo
            $sql = "UPDATE Manuales SET titulo = ?, descripcion = ?, categoria = ?, version = ?, 
                    archivo_nombre = ?, archivo_tipo = ?, archivo_contenido = ?, estado = ?, 
                    fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssbii", $titulo, $descripcion, $categoria, $version, 
                            $archivo_nombre, $archivo_tipo, $archivo_contenido, $estado, $id);
        } else {
            // Actualizar sin archivo nuevo
            $sql = "UPDATE Manuales SET titulo = ?, descripcion = ?, categoria = ?, version = ?, 
                    archivo_tipo = ?, estado = ?, fecha_actualizacion = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssii", $titulo, $descripcion, $categoria, $version, 
                            $archivo_tipo, $estado, $id);
        }
        
        $mensaje = "Manual actualizado correctamente";
    } else {
        // Crear nuevo manual
        $sql = "INSERT INTO Manuales (titulo, descripcion, categoria, version, archivo_nombre, 
                archivo_tipo, archivo_contenido, estado, creado_por) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssbii", $titulo, $descripcion, $categoria, $version, 
                        $archivo_nombre, $archivo_tipo, $archivo_contenido, $estado, $creado_por);
        $mensaje = "Manual creado correctamente";
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => $mensaje]);
    } else {
        throw new Exception('Error al guardar el manual: ' . $stmt->error);
    }
}

// ========== ACTUALIZAR MANUAL (PUT) ==========
function handleUpdateManual() {
    // Para PUT, redirigir a la función POST que maneja ambos casos
    handleCreateOrUpdateManual();
}

// ========== ELIMINAR MANUAL ==========
function handleDeleteManual() {
    global $conn;
    
    // Obtener ID desde query string en lugar de JSON
    if (!isset($_GET['id'])) {
        throw new Exception('Se requiere el ID del manual');
    }
    
    $id = (int) $_GET['id'];
    
    // Verificar que existe
    $check_sql = "SELECT id FROM Manuales WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Manual no encontrado']);
        return;
    }
    
    // Eliminar manual
    $sql = "DELETE FROM Manuales WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Manual eliminado correctamente']);
    } else {
        throw new Exception('Error al eliminar el manual: ' . $stmt->error);
    }
}

// ========== FUNCIONES AUXILIARES ==========
function getContentType($archivo_tipo) {
    return match($archivo_tipo) {
        'PDF' => 'application/pdf',
        'WORD' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'HTML' => 'text/html',
        'TEXTO' => 'text/plain',
        default => 'application/octet-stream'
    };
}
?>
