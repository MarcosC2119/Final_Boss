<?php
// ================================================================
// API PARA GESTIÓN DE ASIGNACIONES DE TURNOS
// ================================================================
// Manejo CRUD de la tabla turno_asignaciones
// Métodos: GET, POST, PUT, DELETE
// ================================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Conexión PDO específica para esta API
function getPDOConnection() {
    try {
        $dsn = "mysql:host=localhost;dbname=roomit;charset=utf8";
        $pdo = new PDO($dsn, "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (Exception $e) {
        throw new Exception("Error de conexión: " . $e->getMessage());
    }
}

try {
    $conn = getPDOConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGet($conn);
            break;
            
        case 'POST':
            handlePost($conn);
            break;
            
        case 'PUT':
            handlePut($conn);
            break;
            
        case 'DELETE':
            handleDelete($conn);
            break;
            
        default:
            throw new Exception('Método no permitido');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}

// ================================================================
// FUNCIÓN GET - Obtener asignaciones
// ================================================================
function handleGet($conn) {
    try {
        // Parámetros de consulta
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $turno_id = isset($_GET['turno_id']) ? (int)$_GET['turno_id'] : null;
        $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
        
        // Calcular offset
        $offset = ($page - 1) * $per_page;
        
        // Construir WHERE clause
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(u.nombre LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($turno_id && $turno_id > 0) {
            $whereConditions[] = "ta.turno_id = ?";
            $params[] = $turno_id;
        }
        
        if (!empty($estado)) {
            $whereConditions[] = "ta.estado = ?";
            $params[] = $estado;
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Query para contar total de registros (SIN LIMIT)
        $countQuery = "
            SELECT COUNT(*) as total
            FROM turno_asignaciones ta
            LEFT JOIN usuarios u ON ta.usuario_id = u.id
            LEFT JOIN turnos t ON ta.turno_id = t.id
            $whereClause
        ";
        
        $countStmt = $conn->prepare($countQuery);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Query para obtener asignaciones (CON LIMIT)
        $query = "
            SELECT 
                ta.id,
                ta.turno_id,
                ta.usuario_id,
                ta.fecha_inicio,
                ta.fecha_fin,
                ta.dias_especificos,
                ta.estado,
                ta.observaciones,
                ta.fecha_asignacion,
                ta.asignado_por,
                
                -- Información del usuario
                u.nombre as usuario_nombre,
                u.email as usuario_email,
                u.rol as usuario_rol,
                
                -- Información del turno
                t.nombre as turno_nombre,
                t.tipo as turno_tipo,
                t.hora_inicio as turno_hora_inicio,
                t.hora_fin as turno_hora_fin,
                t.dias_semana as turno_dias,
                t.descripcion as turno_descripcion,
                t.color_hex as turno_color,
                
                -- Información de quien asignó
                up.nombre as asignado_por_nombre
                
            FROM turno_asignaciones ta
            LEFT JOIN usuarios u ON ta.usuario_id = u.id
            LEFT JOIN turnos t ON ta.turno_id = t.id
            LEFT JOIN usuarios up ON ta.asignado_por = up.id
            $whereClause
            ORDER BY ta.fecha_asignacion DESC
            LIMIT $per_page OFFSET $offset
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar datos para el frontend
        foreach ($asignaciones as &$asignacion) {
            // Formatear horario del turno
            if ($asignacion['turno_hora_inicio'] && $asignacion['turno_hora_fin']) {
                $asignacion['turno_horario'] = $asignacion['turno_hora_inicio'] . ' - ' . $asignacion['turno_hora_fin'];
            }
            
            // Decodificar JSON si es necesario
            if ($asignacion['dias_especificos']) {
                $asignacion['dias_especificos'] = json_decode($asignacion['dias_especificos'], true);
            }
            if ($asignacion['turno_dias']) {
                $asignacion['turno_dias'] = json_decode($asignacion['turno_dias'], true);
            }
        }
        
        // Calcular paginación
        $total_pages = ceil($total / $per_page);
        
        echo json_encode([
            'success' => true,
            'data' => $asignaciones,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages,
                'total_items' => (int)$total
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener asignaciones: ' . $e->getMessage()
        ]);
    }
}

// ================================================================
// FUNCIÓN POST - Crear nueva asignación
// ================================================================
function handlePost($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Datos JSON inválidos');
        }
        
        // Validaciones requeridas
        $required_fields = ['turno_id', 'usuario_id', 'fecha_inicio', 'estado'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                throw new Exception("El campo '$field' es requerido");
            }
        }
        
        // Validar que el turno existe
        $turnoStmt = $conn->prepare("SELECT id, nombre FROM turnos WHERE id = ? AND estado = 'activo'");
        $turnoStmt->execute([$input['turno_id']]);
        if (!$turnoStmt->fetch()) {
            throw new Exception('El turno seleccionado no existe o está inactivo');
        }
        
        // Validar que el usuario existe
        $usuarioStmt = $conn->prepare("SELECT id, nombre FROM usuarios WHERE id = ? AND estado = 'activo'");
        $usuarioStmt->execute([$input['usuario_id']]);
        if (!$usuarioStmt->fetch()) {
            throw new Exception('El usuario seleccionado no existe o está inactivo');
        }
        
        // Verificar conflictos de asignación (mismo usuario, turno y fechas que se solapan)
        $conflictQuery = "
            SELECT id FROM turno_asignaciones 
            WHERE usuario_id = ? 
            AND turno_id = ? 
            AND estado = 'activa'
            AND (
                (fecha_fin IS NULL) OR
                (? <= IFNULL(fecha_fin, '9999-12-31') AND IFNULL(?, '9999-12-31') >= fecha_inicio)
            )
        ";
        
        $conflictStmt = $conn->prepare($conflictQuery);
        $conflictStmt->execute([
            $input['usuario_id'],
            $input['turno_id'],
            $input['fecha_inicio'],
            $input['fecha_fin'] ?? null
        ]);
        
        if ($conflictStmt->fetch()) {
            throw new Exception('El usuario ya tiene una asignación activa para este turno en el período especificado');
        }
        
        // Preparar datos para inserción
        $diasEspecificos = null;
        if (isset($input['dias_especificos']) && is_array($input['dias_especificos']) && !empty($input['dias_especificos'])) {
            $diasEspecificos = json_encode($input['dias_especificos']);
        }
        
        // Insertar nueva asignación
        $insertQuery = "
            INSERT INTO turno_asignaciones (
                turno_id, 
                usuario_id, 
                fecha_inicio, 
                fecha_fin, 
                dias_especificos, 
                estado, 
                observaciones,
                asignado_por
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $insertStmt = $conn->prepare($insertQuery);
        $result = $insertStmt->execute([
            $input['turno_id'],
            $input['usuario_id'],
            $input['fecha_inicio'],
            $input['fecha_fin'] ?? null,
            $diasEspecificos,
            $input['estado'],
            $input['observaciones'] ?? null,
            null
        ]);
        
        if (!$result) {
            throw new Exception('Error al insertar la asignación en la base de datos');
        }
        
        $newId = $conn->lastInsertId();
        echo json_encode([
            'success' => true,
            'message' => 'Asignación creada exitosamente',
            'data' => ['id' => $newId]
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// ================================================================
// FUNCIÓN PUT - Actualizar asignación existente
// ================================================================
function handlePut($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Datos JSON inválidos');
        }
        
        // Validar ID
        if (!isset($input['id']) || empty($input['id'])) {
            throw new Exception('ID de asignación requerido');
        }
        
        // Verificar que la asignación existe
        $checkStmt = $conn->prepare("SELECT id FROM turno_asignaciones WHERE id = ?");
        $checkStmt->execute([$input['id']]);
        if (!$checkStmt->fetch()) {
            throw new Exception('Asignación no encontrada');
        }
        
        // Validaciones requeridas
        $required_fields = ['turno_id', 'usuario_id', 'fecha_inicio', 'estado'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                throw new Exception("El campo '$field' es requerido");
            }
        }
        
        // Verificar conflictos (excluyendo la asignación actual)
        $conflictQuery = "
            SELECT id FROM turno_asignaciones 
            WHERE usuario_id = ? 
            AND turno_id = ? 
            AND estado = 'activa'
            AND id != ?
            AND (
                (fecha_fin IS NULL) OR
                (? <= IFNULL(fecha_fin, '9999-12-31') AND IFNULL(?, '9999-12-31') >= fecha_inicio)
            )
        ";
        
        $conflictStmt = $conn->prepare($conflictQuery);
        $conflictStmt->execute([
            $input['usuario_id'],
            $input['turno_id'],
            $input['id'],
            $input['fecha_inicio'],
            $input['fecha_fin'] ?? null
        ]);
        
        if ($conflictStmt->fetch()) {
            throw new Exception('El usuario ya tiene otra asignación activa para este turno en el período especificado');
        }
        
        // Preparar datos para actualización
        $diasEspecificos = null;
        if (isset($input['dias_especificos']) && is_array($input['dias_especificos']) && !empty($input['dias_especificos'])) {
            $diasEspecificos = json_encode($input['dias_especificos']);
        }
        
        // Actualizar asignación
        $updateQuery = "
            UPDATE turno_asignaciones SET 
                turno_id = ?,
                usuario_id = ?,
                fecha_inicio = ?,
                fecha_fin = ?,
                dias_especificos = ?,
                estado = ?,
                observaciones = ?
            WHERE id = ?
        ";
        
        $updateStmt = $conn->prepare($updateQuery);
        $result = $updateStmt->execute([
            $input['turno_id'],
            $input['usuario_id'],
            $input['fecha_inicio'],
            $input['fecha_fin'] ?? null,
            $diasEspecificos,
            $input['estado'],
            $input['observaciones'] ?? null,
            $input['id']
        ]);
        
        if (!$result) {
            throw new Exception('Error al actualizar la asignación en la base de datos');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Asignación actualizada exitosamente'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// ================================================================
// FUNCIÓN DELETE - Eliminar asignación
// ================================================================
function handleDelete($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['id'])) {
            throw new Exception('ID de asignación requerido');
        }
        
        $id = $input['id'];
        
        // Verificar que la asignación existe
        $checkStmt = $conn->prepare("
            SELECT ta.id, u.nombre as usuario_nombre, t.nombre as turno_nombre
            FROM turno_asignaciones ta
            LEFT JOIN usuarios u ON ta.usuario_id = u.id
            LEFT JOIN turnos t ON ta.turno_id = t.id
            WHERE ta.id = ?
        ");
        $checkStmt->execute([$id]);
        $asignacion = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$asignacion) {
            throw new Exception('Asignación no encontrada');
        }
        
        // Verificar si tiene registros de asistencia asociados
        $registrosStmt = $conn->prepare("SELECT COUNT(*) as total FROM turno_registros WHERE asignacion_id = ?");
        $registrosStmt->execute([$id]);
        $totalRegistros = $registrosStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($totalRegistros > 0) {
            throw new Exception("No se puede eliminar la asignación porque tiene $totalRegistros registros de asistencia asociados. Cambia el estado a 'finalizada' en su lugar.");
        }
        
        // Eliminar asignación
        $deleteStmt = $conn->prepare("DELETE FROM turno_asignaciones WHERE id = ?");
        $result = $deleteStmt->execute([$id]);
        
        if (!$result) {
            throw new Exception('Error al eliminar la asignación de la base de datos');
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Asignación eliminada exitosamente'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>


