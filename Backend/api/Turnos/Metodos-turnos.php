<?php
// ========== CRUD COMPLETO DE TURNOS ==========
// Maneja todas las operaciones CRUD basándose en el método HTTP
// GET = Read/Search | POST = Create | PUT = Update | DELETE = Delete

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejo de peticiones OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/db.php';

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

try {
    // Enrutar según método HTTP
    switch ($method) {
        case 'GET':
            handleRead();
            break;
        case 'POST':
            handleCreate();
            break;
        case 'PUT':
            handleUpdate();
            break;
        case 'DELETE':
            handleDelete();
            break;
        default:
            throw new Exception('Método HTTP no soportado: ' . $method);
    }

} catch (Exception $e) {
    if (!http_response_code()) {
        http_response_code(400);
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'method' => $method
    ]);
}

// ========== FUNCIÓN READ (GET) ==========
function handleRead() {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar si es búsqueda/filtros o lectura normal
    $search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
    $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 10;

    // Si se solicita un turno específico por ID
    if ($id) {
        $sql = "SELECT id, nombre, tipo, hora_inicio, hora_fin, dias_semana, descripcion, 
                       estado, color_hex, fecha_creacion 
                FROM turnos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Turno no encontrado');
        }
        
        $turno = $result->fetch_assoc();
        
        // Decodificar JSON de días_semana
        if ($turno['dias_semana']) {
            $turno['dias_semana'] = json_decode($turno['dias_semana'], true);
        }
        
        echo json_encode([
            'success' => true,
            'data' => $turno
        ]);
        return;
    }

    // Búsqueda y filtros
    if ($search_query || $tipo || $estado) {
        $where_conditions = [];
        $params = [];
        $types = "";

        if ($search_query) {
            $where_conditions[] = "(nombre LIKE ? OR descripcion LIKE ?)";
            $search_param = "%{$search_query}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }

        if ($tipo) {
            $where_conditions[] = "tipo = ?";
            $params[] = $tipo;
            $types .= "s";
        }

        if ($estado) {
            $where_conditions[] = "estado = ?";
            $params[] = $estado;
            $types .= "s";
        }

        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

        // Contar total
        $count_sql = "SELECT COUNT(*) as total FROM turnos $where_clause";
        $count_stmt = $conn->prepare($count_sql);
        if (!empty($params)) {
            $count_stmt->bind_param($types, ...$params);
        }
        $count_stmt->execute();
        $total = $count_stmt->get_result()->fetch_assoc()['total'];

        // Obtener resultados con paginación
        $offset = ($page - 1) * $per_page;
        $sql = "SELECT id, nombre, tipo, hora_inicio, hora_fin, dias_semana, descripcion, 
                       estado, color_hex, fecha_creacion 
                FROM turnos $where_clause 
                ORDER BY fecha_creacion DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $per_page;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $turnos = [];
        while ($row = $result->fetch_assoc()) {
            // Decodificar JSON de días_semana
            if ($row['dias_semana']) {
                $row['dias_semana'] = json_decode($row['dias_semana'], true);
            }
            $turnos[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $turnos,
            'total' => intval($total),
            'search' => [
                'query' => $search_query,
                'tipo' => $tipo,
                'estado' => $estado
            ],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_pages' => ceil($total / $per_page),
                'total_items' => intval($total)
            ]
        ]);

    } else {
        // ========== LISTAR TODOS LOS TURNOS ==========
        $offset = ($page - 1) * $per_page;

        // Contar total
        $total = $conn->query("SELECT COUNT(*) as total FROM turnos")->fetch_assoc()['total'];

        // Obtener turnos
        $sql = "SELECT id, nombre, tipo, hora_inicio, hora_fin, dias_semana, descripcion, 
                       estado, color_hex, fecha_creacion 
                FROM turnos 
                ORDER BY fecha_creacion DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $per_page, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $turnos = [];
        while ($row = $result->fetch_assoc()) {
            // Decodificar JSON de días_semana
            if ($row['dias_semana']) {
                $row['dias_semana'] = json_decode($row['dias_semana'], true);
            }
            $turnos[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $turnos,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_pages' => ceil($total / $per_page),
                'total_items' => intval($total)
            ]
        ]);
    }
}

// ========== FUNCIÓN CREATE (POST) ==========
function handleCreate() {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('No se recibieron datos válidos');
    }

    // Validaciones básicas
    if (empty($data['nombre']) || empty($data['tipo']) || empty($data['hora_inicio']) || empty($data['hora_fin'])) {
        throw new Exception('Faltan campos requeridos: nombre, tipo, hora_inicio, hora_fin');
    }

    $nombre = trim($data['nombre']);
    $tipo = trim($data['tipo']);
    $hora_inicio = trim($data['hora_inicio']);
    $hora_fin = trim($data['hora_fin']);
    $dias_semana = isset($data['dias_semana']) ? $data['dias_semana'] : '[]';
    $descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : null;
    $estado = isset($data['estado']) ? trim($data['estado']) : 'activo';
    $color_hex = isset($data['color_hex']) ? trim($data['color_hex']) : '#007bff';

    // Validaciones específicas
    if (strlen($nombre) < 2 || strlen($nombre) > 100) {
        throw new Exception('El nombre debe tener entre 2 y 100 caracteres');
    }

    $tipos_validos = ['academico', 'laboral', 'servicio'];
    if (!in_array($tipo, $tipos_validos)) {
        throw new Exception('Tipo inválido. Debe ser: ' . implode(', ', $tipos_validos));
    }

    $estados_validos = ['activo', 'inactivo'];
    if (!in_array($estado, $estados_validos)) {
        throw new Exception('Estado inválido. Debe ser: ' . implode(', ', $estados_validos));
    }

    // Validar formato de hora
    if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora_inicio) || 
        !preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora_fin)) {
        throw new Exception('Formato de hora inválido (debe ser HH:MM)');
    }

    // Validar que hora_fin > hora_inicio
    if ($hora_fin <= $hora_inicio) {
        throw new Exception('La hora de fin debe ser posterior a la hora de inicio');
    }

    // Validar color hexadecimal
    if (!preg_match('/^#[0-9A-F]{6}$/i', $color_hex)) {
        throw new Exception('El color debe ser un código hexadecimal válido (#RRGGBB)');
    }

    // Validar y procesar días_semana
    if (is_string($dias_semana)) {
        $dias_array = json_decode($dias_semana, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Formato JSON inválido para días_semana');
        }
    } else {
        $dias_array = $dias_semana;
    }

    if (empty($dias_array)) {
        throw new Exception('Debe seleccionar al menos un día de la semana');
    }

    $dias_validos = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    foreach ($dias_array as $dia) {
        if (!in_array($dia, $dias_validos)) {
            throw new Exception("Día inválido: $dia");
        }
    }

    $dias_semana_json = json_encode($dias_array);

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que no exista un turno con el mismo nombre y tipo
    $check_sql = "SELECT id FROM turnos WHERE nombre = ? AND tipo = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $nombre, $tipo);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception('Ya existe un turno con ese nombre y tipo');
    }

    // Insertar nuevo turno
    $sql = "INSERT INTO turnos (nombre, tipo, hora_inicio, hora_fin, dias_semana, descripcion, estado, color_hex) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $nombre, $tipo, $hora_inicio, $hora_fin, $dias_semana_json, $descripcion, $estado, $color_hex);

    if ($stmt->execute()) {
        $nuevo_id = $conn->insert_id;
        
        // Obtener el turno recién creado
        $get_sql = "SELECT id, nombre, tipo, hora_inicio, hora_fin, dias_semana, descripcion, 
                           estado, color_hex, fecha_creacion 
                    FROM turnos WHERE id = ?";
        $get_stmt = $conn->prepare($get_sql);
        $get_stmt->bind_param("i", $nuevo_id);
        $get_stmt->execute();
        $nuevo_turno = $get_stmt->get_result()->fetch_assoc();
        
        // Decodificar JSON
        if ($nuevo_turno['dias_semana']) {
            $nuevo_turno['dias_semana'] = json_decode($nuevo_turno['dias_semana'], true);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Turno creado exitosamente',
            'data' => $nuevo_turno
        ]);
    } else {
        throw new Exception('Error al insertar el turno: ' . $conn->error);
    }
}

// ========== FUNCIÓN UPDATE (PUT) ==========
function handleUpdate() {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('No se recibieron datos válidos');
    }

    if (!isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception('ID de turno requerido');
    }

    $id = intval($data['id']);

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que el turno existe
    $check_sql = "SELECT id FROM turnos WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        throw new Exception('Turno no encontrado');
    }

    // Construir query de actualización dinámicamente
    $update_fields = [];
    $params = [];
    $types = "";

    if (isset($data['nombre']) && !empty(trim($data['nombre']))) {
        $nombre = trim($data['nombre']);
        
        if (strlen($nombre) < 2 || strlen($nombre) > 100) {
            throw new Exception('El nombre debe tener entre 2 y 100 caracteres');
        }

        // Verificar duplicados (excluyendo el registro actual)
        $check_duplicate = "SELECT id FROM turnos WHERE nombre = ? AND tipo = ? AND id != ?";
        $tipo_actual = isset($data['tipo']) ? $data['tipo'] : '';
        
        if ($tipo_actual) {
            $dup_stmt = $conn->prepare($check_duplicate);
            $dup_stmt->bind_param("ssi", $nombre, $tipo_actual, $id);
            $dup_stmt->execute();
            if ($dup_stmt->get_result()->num_rows > 0) {
                throw new Exception('Ya existe otro turno con ese nombre y tipo');
            }
        }

        $update_fields[] = "nombre = ?";
        $params[] = $nombre;
        $types .= "s";
    }

    if (isset($data['tipo']) && !empty($data['tipo'])) {
        $tipos_validos = ['academico', 'laboral', 'servicio'];
        if (!in_array($data['tipo'], $tipos_validos)) {
            throw new Exception('Tipo inválido. Debe ser: ' . implode(', ', $tipos_validos));
        }

        $update_fields[] = "tipo = ?";
        $params[] = $data['tipo'];
        $types .= "s";
    }

    if (isset($data['hora_inicio']) && !empty($data['hora_inicio'])) {
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['hora_inicio'])) {
            throw new Exception('Formato de hora de inicio inválido (debe ser HH:MM)');
        }

        $update_fields[] = "hora_inicio = ?";
        $params[] = $data['hora_inicio'];
        $types .= "s";
    }

    if (isset($data['hora_fin']) && !empty($data['hora_fin'])) {
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['hora_fin'])) {
            throw new Exception('Formato de hora de fin inválido (debe ser HH:MM)');
        }

        $update_fields[] = "hora_fin = ?";
        $params[] = $data['hora_fin'];
        $types .= "s";
    }

    // Validar horarios si se están actualizando ambos
    if (isset($data['hora_inicio']) && isset($data['hora_fin'])) {
        if ($data['hora_fin'] <= $data['hora_inicio']) {
            throw new Exception('La hora de fin debe ser posterior a la hora de inicio');
        }
    }

    if (isset($data['dias_semana'])) {
        $dias_semana = $data['dias_semana'];
        
        if (is_string($dias_semana)) {
            $dias_array = json_decode($dias_semana, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Formato JSON inválido para días_semana');
            }
        } else {
            $dias_array = $dias_semana;
        }

        if (empty($dias_array)) {
            throw new Exception('Debe seleccionar al menos un día de la semana');
        }

        $dias_validos = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        foreach ($dias_array as $dia) {
            if (!in_array($dia, $dias_validos)) {
                throw new Exception("Día inválido: $dia");
            }
        }

        $update_fields[] = "dias_semana = ?";
        $params[] = json_encode($dias_array);
        $types .= "s";
    }

    if (isset($data['descripcion'])) {
        $update_fields[] = "descripcion = ?";
        $params[] = trim($data['descripcion']) ?: null;
        $types .= "s";
    }

    if (isset($data['estado']) && !empty($data['estado'])) {
        $estados_validos = ['activo', 'inactivo'];
        if (!in_array($data['estado'], $estados_validos)) {
            throw new Exception('Estado inválido. Debe ser: ' . implode(', ', $estados_validos));
        }

        $update_fields[] = "estado = ?";
        $params[] = $data['estado'];
        $types .= "s";
    }

    if (isset($data['color_hex']) && !empty($data['color_hex'])) {
        if (!preg_match('/^#[0-9A-F]{6}$/i', $data['color_hex'])) {
            throw new Exception('El color debe ser un código hexadecimal válido (#RRGGBB)');
        }

        $update_fields[] = "color_hex = ?";
        $params[] = $data['color_hex'];
        $types .= "s";
    }

    if (empty($update_fields)) {
        throw new Exception('No hay campos para actualizar');
    }

    // Agregar ID al final
    $params[] = $id;
    $types .= "i";

    // Ejecutar actualización
    $sql = "UPDATE turnos SET " . implode(", ", $update_fields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        // Obtener datos actualizados
        $get_updated = "SELECT id, nombre, tipo, hora_inicio, hora_fin, dias_semana, descripcion, 
                               estado, color_hex, fecha_creacion 
                        FROM turnos WHERE id = ?";
        $get_stmt = $conn->prepare($get_updated);
        $get_stmt->bind_param("i", $id);
        $get_stmt->execute();
        $updated_turno = $get_stmt->get_result()->fetch_assoc();
        
        // Decodificar JSON
        if ($updated_turno['dias_semana']) {
            $updated_turno['dias_semana'] = json_decode($updated_turno['dias_semana'], true);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Turno actualizado exitosamente',
            'data' => $updated_turno
        ]);
    } else {
        throw new Exception('Error al actualizar el turno: ' . $conn->error);
    }
}

// ========== FUNCIÓN DELETE (DELETE) ==========
function handleDelete() {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || !isset($data['id']) || !is_numeric($data['id'])) {
        throw new Exception('ID de turno requerido');
    }

    $id = intval($data['id']);

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que el turno existe
    $check_sql = "SELECT nombre FROM turnos WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Turno no encontrado');
    }
    
    $turno = $result->fetch_assoc();

    // Verificar si el turno tiene asignaciones activas
    $check_asignaciones = "SELECT COUNT(*) as total FROM turno_asignaciones 
                          WHERE turno_id = ? AND estado = 'activa'";
    $asig_stmt = $conn->prepare($check_asignaciones);
    $asig_stmt->bind_param("i", $id);
    $asig_stmt->execute();
    $asignaciones_activas = $asig_stmt->get_result()->fetch_assoc()['total'];

    if ($asignaciones_activas > 0) {
        throw new Exception('No se puede eliminar el turno porque tiene ' . $asignaciones_activas . ' asignación(es) activa(s)');
    }

    // Eliminar el turno (las asignaciones se eliminarán por CASCADE)
    $delete_sql = "DELETE FROM turnos WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);

    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Turno '{$turno['nombre']}' eliminado exitosamente"
            ]);
        } else {
            throw new Exception('No se pudo eliminar el turno');
        }
    } else {
        throw new Exception('Error al eliminar el turno: ' . $conn->error);
    }
}
?>
