<?php
// ========== CRUD COMPLETO DE SALAS ==========
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

    if ($id) {
        // ========== OBTENER SALA ESPECÍFICA ==========
        $sql = "SELECT id, nombre, capacidad, tipo, tiene_proyector, tiene_pizarra_digital, 
                       es_accesible, estado, descripcion 
                FROM salas WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($sala = $result->fetch_assoc()) {
            // Convertir valores booleanos
            $sala['tiene_proyector'] = (bool) $sala['tiene_proyector'];
            $sala['tiene_pizarra_digital'] = (bool) $sala['tiene_pizarra_digital'];
            $sala['es_accesible'] = (bool) $sala['es_accesible'];
            
            echo json_encode([
                'success' => true,
                'data' => $sala
            ]);
        } else {
            http_response_code(404);
            throw new Exception('Sala no encontrada');
        }

    } elseif (!empty($search_query) || !empty($tipo) || !empty($estado)) {
        // ========== BÚSQUEDA Y FILTROS ==========
        $where_conditions = [];
        $params = [];
        $types = "";

        // Búsqueda por texto (nombre de sala)
        if (!empty($search_query)) {
            $where_conditions[] = "nombre LIKE ?";
            $search_term = "%" . $search_query . "%";
            $params[] = $search_term;
            $types .= "s";
        }

        // Filtro por tipo
        if (!empty($tipo)) {
            $where_conditions[] = "tipo = ?";
            $params[] = $tipo;
            $types .= "s";
        }

        // Filtro por estado
        if (!empty($estado)) {
            $where_conditions[] = "estado = ?";
            $params[] = $estado;
            $types .= "s";
        }

        $where_clause = "WHERE " . implode(" AND ", $where_conditions);

        // Contar total
        $count_sql = "SELECT COUNT(*) as total FROM salas $where_clause";
        $count_stmt = $conn->prepare($count_sql);
        if (!empty($params)) {
            $count_stmt->bind_param($types, ...$params);
        }
        $count_stmt->execute();
        $total = $count_stmt->get_result()->fetch_assoc()['total'];

        // Obtener resultados con paginación
        $offset = ($page - 1) * $per_page;
        $sql = "SELECT id, nombre, capacidad, tipo, tiene_proyector, tiene_pizarra_digital, 
                       es_accesible, estado, descripcion 
                FROM salas $where_clause 
                ORDER BY nombre ASC 
                LIMIT ? OFFSET ?";
        
        $params[] = $per_page;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $salas = [];
        while ($row = $result->fetch_assoc()) {
            // Convertir valores booleanos
            $row['tiene_proyector'] = (bool) $row['tiene_proyector'];
            $row['tiene_pizarra_digital'] = (bool) $row['tiene_pizarra_digital'];
            $row['es_accesible'] = (bool) $row['es_accesible'];
            $salas[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $salas,
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
        // ========== LISTAR TODAS LAS SALAS ==========
        $offset = ($page - 1) * $per_page;

        // Contar total
        $total = $conn->query("SELECT COUNT(*) as total FROM salas")->fetch_assoc()['total'];

        // Obtener salas
        $sql = "SELECT id, nombre, capacidad, tipo, tiene_proyector, tiene_pizarra_digital, 
                       es_accesible, estado, descripcion 
                FROM salas 
                ORDER BY nombre ASC 
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $per_page, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $salas = [];
        while ($row = $result->fetch_assoc()) {
            // Convertir valores booleanos
            $row['tiene_proyector'] = (bool) $row['tiene_proyector'];
            $row['tiene_pizarra_digital'] = (bool) $row['tiene_pizarra_digital'];
            $row['es_accesible'] = (bool) $row['es_accesible'];
            $salas[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $salas,
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
    if (empty($data['nombre']) || empty($data['tipo']) || empty($data['capacidad'])) {
        throw new Exception('Faltan campos requeridos: nombre, tipo, capacidad');
    }

    $nombre = trim($data['nombre']);
    $capacidad = intval($data['capacidad']);
    $tipo = trim($data['tipo']);
    $estado = isset($data['estado']) ? trim($data['estado']) : 'disponible';
    $tiene_proyector = isset($data['tiene_proyector']) ? (bool)$data['tiene_proyector'] : false;
    $tiene_pizarra_digital = isset($data['tiene_pizarra_digital']) ? (bool)$data['tiene_pizarra_digital'] : false;
    $es_accesible = isset($data['es_accesible']) ? (bool)$data['es_accesible'] : false;
    $descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : null;

    // Validaciones específicas
    if (strlen($nombre) < 2 || strlen($nombre) > 50) {
        throw new Exception('El nombre debe tener entre 2 y 50 caracteres');
    }

    if ($capacidad < 1 || $capacidad > 500) {
        throw new Exception('La capacidad debe estar entre 1 y 500 personas');
    }

    $tipos_validos = ['aula', 'laboratorio', 'auditorio'];
    if (!in_array($tipo, $tipos_validos)) {
        throw new Exception('Tipo inválido. Debe ser: ' . implode(', ', $tipos_validos));
    }

    $estados_validos = ['disponible', 'ocupada', 'mantenimiento'];
    if (!in_array($estado, $estados_validos)) {
        throw new Exception('Estado inválido. Debe ser: ' . implode(', ', $estados_validos));
    }

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que el nombre no esté duplicado
    $check_sql = "SELECT id FROM salas WHERE nombre = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $nombre);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception('Ya existe una sala con ese nombre');
    }

    // Insertar nueva sala
    $sql = "INSERT INTO salas (nombre, capacidad, tipo, tiene_proyector, tiene_pizarra_digital, 
                               es_accesible, estado, descripcion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisiiiss", $nombre, $capacidad, $tipo, $tiene_proyector, 
                      $tiene_pizarra_digital, $es_accesible, $estado, $descripcion);

    if ($stmt->execute()) {
        $sala_id = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Sala creada exitosamente',
            'data' => [
                'id' => $sala_id,
                'nombre' => $nombre,
                'capacidad' => $capacidad,
                'tipo' => $tipo,
                'tiene_proyector' => $tiene_proyector,
                'tiene_pizarra_digital' => $tiene_pizarra_digital,
                'es_accesible' => $es_accesible,
                'estado' => $estado,
                'descripcion' => $descripcion,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Error al crear la sala');
    }
}

// ========== FUNCIÓN UPDATE (PUT) ==========
function handleUpdate() {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || empty($data['id'])) {
        throw new Exception('ID de sala es requerido');
    }

    $id = intval($data['id']);

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que la sala existe
    $check_sql = "SELECT id, nombre FROM salas WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $existing_sala = $check_stmt->get_result()->fetch_assoc();

    if (!$existing_sala) {
        http_response_code(404);
        throw new Exception('Sala no encontrada');
    }

    // Preparar campos a actualizar
    $update_fields = [];
    $params = [];
    $types = "";

    if (isset($data['nombre']) && !empty(trim($data['nombre']))) {
        $nombre = trim($data['nombre']);
        
        if (strlen($nombre) < 2 || strlen($nombre) > 50) {
            throw new Exception('El nombre debe tener entre 2 y 50 caracteres');
        }

        // Verificar que el nombre no esté en uso por otra sala
        if ($nombre !== $existing_sala['nombre']) {
            $name_check = "SELECT id FROM salas WHERE nombre = ? AND id != ?";
            $name_stmt = $conn->prepare($name_check);
            $name_stmt->bind_param("si", $nombre, $id);
            $name_stmt->execute();
            
            if ($name_stmt->get_result()->num_rows > 0) {
                throw new Exception('Ya existe otra sala con ese nombre');
            }
        }

        $update_fields[] = "nombre = ?";
        $params[] = $nombre;
        $types .= "s";
    }

    if (isset($data['capacidad'])) {
        $capacidad = intval($data['capacidad']);
        
        if ($capacidad < 1 || $capacidad > 500) {
            throw new Exception('La capacidad debe estar entre 1 y 500 personas');
        }

        $update_fields[] = "capacidad = ?";
        $params[] = $capacidad;
        $types .= "i";
    }

    if (isset($data['tipo']) && !empty($data['tipo'])) {
        $tipos_validos = ['aula', 'laboratorio', 'auditorio'];
        if (!in_array($data['tipo'], $tipos_validos)) {
            throw new Exception('Tipo inválido. Debe ser: ' . implode(', ', $tipos_validos));
        }

        $update_fields[] = "tipo = ?";
        $params[] = $data['tipo'];
        $types .= "s";
    }

    if (isset($data['estado']) && !empty($data['estado'])) {
        $estados_validos = ['disponible', 'ocupada', 'mantenimiento'];
        if (!in_array($data['estado'], $estados_validos)) {
            throw new Exception('Estado inválido. Debe ser: ' . implode(', ', $estados_validos));
        }

        $update_fields[] = "estado = ?";
        $params[] = $data['estado'];
        $types .= "s";
    }

    if (isset($data['tiene_proyector'])) {
        $update_fields[] = "tiene_proyector = ?";
        $params[] = (bool)$data['tiene_proyector'];
        $types .= "i";
    }

    if (isset($data['tiene_pizarra_digital'])) {
        $update_fields[] = "tiene_pizarra_digital = ?";
        $params[] = (bool)$data['tiene_pizarra_digital'];
        $types .= "i";
    }

    if (isset($data['es_accesible'])) {
        $update_fields[] = "es_accesible = ?";
        $params[] = (bool)$data['es_accesible'];
        $types .= "i";
    }

    if (isset($data['descripcion'])) {
        $update_fields[] = "descripcion = ?";
        $params[] = trim($data['descripcion']) ?: null;
        $types .= "s";
    }

    if (empty($update_fields)) {
        throw new Exception('No hay campos para actualizar');
    }

    // Agregar ID al final
    $params[] = $id;
    $types .= "i";

    // Ejecutar actualización
    $sql = "UPDATE salas SET " . implode(", ", $update_fields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        // Obtener datos actualizados
        $get_updated = "SELECT id, nombre, capacidad, tipo, tiene_proyector, tiene_pizarra_digital, 
                               es_accesible, estado, descripcion FROM salas WHERE id = ?";
        $get_stmt = $conn->prepare($get_updated);
        $get_stmt->bind_param("i", $id);
        $get_stmt->execute();
        $updated_sala = $get_stmt->get_result()->fetch_assoc();

        // Convertir valores booleanos
        $updated_sala['tiene_proyector'] = (bool) $updated_sala['tiene_proyector'];
        $updated_sala['tiene_pizarra_digital'] = (bool) $updated_sala['tiene_pizarra_digital'];
        $updated_sala['es_accesible'] = (bool) $updated_sala['es_accesible'];

        echo json_encode([
            'success' => true,
            'message' => 'Sala actualizada exitosamente',
            'data' => array_merge($updated_sala, ['fecha_actualizacion' => date('Y-m-d H:i:s')])
        ]);
    } else {
        throw new Exception('Error al actualizar la sala');
    }
}

// ========== FUNCIÓN DELETE (DELETE) ==========
function handleDelete() {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || empty($data['id'])) {
        throw new Exception('ID de sala es requerido');
    }

    $id = intval($data['id']);

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que la sala existe
    $check_sql = "SELECT id, nombre FROM salas WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $sala = $check_stmt->get_result()->fetch_assoc();

    if (!$sala) {
        http_response_code(404);
        throw new Exception('Sala no encontrada');
    }

    // Verificar si tiene reservas activas (comentado por ahora hasta crear sistema de reservas)
    /*
    $reservas_check = "SELECT COUNT(*) as total FROM reservas WHERE sala_id = ? AND estado = 'confirmada'";
    $reservas_stmt = $conn->prepare($reservas_check);
    $reservas_stmt->bind_param("i", $id);
    $reservas_stmt->execute();
    $reservas_activas = $reservas_stmt->get_result()->fetch_assoc()['total'];

    if ($reservas_activas > 0) {
        http_response_code(400);
        throw new Exception('No se puede eliminar: la sala tiene ' . $reservas_activas . ' reservas activas');
    }
    */

    // Eliminar sala
    $delete_sql = "DELETE FROM salas WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);

    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Sala eliminada exitosamente',
                'deleted_sala' => [
                    'id' => $sala['id'],
                    'nombre' => $sala['nombre']
                ]
            ]);
        } else {
            throw new Exception('No se pudo eliminar la sala');
        }
    } else {
        throw new Exception('Error al eliminar la sala');
    }
}
?>