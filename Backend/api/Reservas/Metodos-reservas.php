<?php
// ========== CRUD COMPLETO DE RESERVAS ==========
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
    $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
    $sala_id = isset($_GET['sala_id']) ? intval($_GET['sala_id']) : null;
    $usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;
    $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';
    $fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
    $fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 10;

    if ($id) {
        // ========== OBTENER RESERVA ESPECÍFICA ==========
        $sql = "SELECT r.*, u.nombre as usuario_nombre, u.email as usuario_email,
                       s.nombre as sala_nombre, s.tipo as sala_tipo
                FROM reservas r
                JOIN usuarios u ON r.usuario_id = u.id
                JOIN salas s ON r.sala_id = s.id
                WHERE r.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($reserva = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true,
                'data' => $reserva
            ]);
        } else {
            http_response_code(404);
            throw new Exception('Reserva no encontrada');
        }

    } elseif (!empty($search_query) || !empty($estado) || !empty($sala_id) || !empty($usuario_id) || !empty($fecha) || !empty($fecha_desde) || !empty($fecha_hasta)) {
        // ========== BÚSQUEDA Y FILTROS ==========
        $where_conditions = [];
        $params = [];
        $types = "";

        // Búsqueda por texto (propósito o nombre de usuario)
        if (!empty($search_query)) {
            $where_conditions[] = "(r.proposito LIKE ? OR u.nombre LIKE ? OR u.email LIKE ? OR s.nombre LIKE ?)";
            $search_term = "%" . $search_query . "%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= "ssss";
        }

        // Filtro por estado
        if (!empty($estado)) {
            $where_conditions[] = "r.estado = ?";
            $params[] = $estado;
            $types .= "s";
        }

        // Filtro por sala
        if (!empty($sala_id)) {
            $where_conditions[] = "r.sala_id = ?";
            $params[] = $sala_id;
            $types .= "i";
        }

        // Filtro por usuario
        if (!empty($usuario_id)) {
            $where_conditions[] = "r.usuario_id = ?";
            $params[] = $usuario_id;
            $types .= "i";
        }

        // Filtro por fecha específica
        if (!empty($fecha)) {
            $where_conditions[] = "r.fecha_reserva = ?";
            $params[] = $fecha;
            $types .= "s";
        }

        // Filtro por rango de fechas
        if (!empty($fecha_desde)) {
            $where_conditions[] = "r.fecha_reserva >= ?";
            $params[] = $fecha_desde;
            $types .= "s";
        }

        if (!empty($fecha_hasta)) {
            $where_conditions[] = "r.fecha_reserva <= ?";
            $params[] = $fecha_hasta;
            $types .= "s";
        }

        $where_clause = "WHERE " . implode(" AND ", $where_conditions);

        // Contar total
        $count_sql = "SELECT COUNT(*) as total 
                      FROM reservas r
                      JOIN usuarios u ON r.usuario_id = u.id
                      JOIN salas s ON r.sala_id = s.id
                      $where_clause";
        
        $count_stmt = $conn->prepare($count_sql);
        if (!empty($params)) {
            $count_stmt->bind_param($types, ...$params);
        }
        $count_stmt->execute();
        $total = $count_stmt->get_result()->fetch_assoc()['total'];

        // Obtener resultados con paginación
        $offset = ($page - 1) * $per_page;
        $sql = "SELECT r.*, u.nombre as usuario_nombre, u.email as usuario_email,
                       s.nombre as sala_nombre, s.tipo as sala_tipo
                FROM reservas r
                JOIN usuarios u ON r.usuario_id = u.id
                JOIN salas s ON r.sala_id = s.id
                $where_clause 
                ORDER BY r.fecha_reserva DESC, r.hora_inicio ASC
                LIMIT ? OFFSET ?";
        
        $params[] = $per_page;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $reservas,
            'total' => intval($total),
            'search' => [
                'query' => $search_query,
                'estado' => $estado,
                'sala_id' => $sala_id,
                'usuario_id' => $usuario_id,
                'fecha' => $fecha,
                'fecha_desde' => $fecha_desde,
                'fecha_hasta' => $fecha_hasta
            ],
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_pages' => ceil($total / $per_page),
                'total_items' => intval($total)
            ]
        ]);

    } else {
        // ========== LISTAR TODAS LAS RESERVAS ==========
        $offset = ($page - 1) * $per_page;

        // Contar total
        $total = $conn->query("SELECT COUNT(*) as total FROM reservas")->fetch_assoc()['total'];

        // Obtener reservas con JOIN
        $sql = "SELECT r.*, u.nombre as usuario_nombre, u.email as usuario_email,
                       s.nombre as sala_nombre, s.tipo as sala_tipo
                FROM reservas r
                JOIN usuarios u ON r.usuario_id = u.id
                JOIN salas s ON r.sala_id = s.id
                ORDER BY r.fecha_reserva DESC, r.hora_inicio ASC
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $per_page, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $reservas,
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

    // DEBUG: Log de los datos recibidos
    error_log("DEBUG - Datos recibidos en create reserva: " . print_r($data, true));

    if (!$data) {
        throw new Exception('No se recibieron datos válidos');
    }

    // Validaciones básicas
    if (empty($data['usuario_id']) || empty($data['sala_id']) || empty($data['fecha_reserva']) || 
        empty($data['hora_inicio']) || empty($data['hora_fin']) || empty($data['proposito'])) {
        throw new Exception('Faltan campos requeridos: usuario, sala, fecha, horarios y propósito');
    }

    $usuario_id = intval($data['usuario_id']);
    $sala_id = intval($data['sala_id']);
    $fecha_reserva = trim($data['fecha_reserva']);
    $hora_inicio = trim($data['hora_inicio']);
    $hora_fin = trim($data['hora_fin']);
    $proposito = trim($data['proposito']);
    $notas = isset($data['notas']) ? trim($data['notas']) : null;
    $estado = isset($data['estado']) ? trim($data['estado']) : 'confirmada';

    // DEBUG: Log de los datos procesados
    error_log("DEBUG - usuario_id procesado: " . $usuario_id);
    error_log("DEBUG - Datos procesados: usuario_id=$usuario_id, sala_id=$sala_id, fecha=$fecha_reserva");

    // Validar que el usuario_id sea válido
    if ($usuario_id <= 0) {
        throw new Exception('ID de usuario inválido: ' . $usuario_id);
    }

    // Validaciones específicas
    if (strlen($proposito) < 3 || strlen($proposito) > 255) {
        throw new Exception('El propósito debe tener entre 3 y 255 caracteres');
    }

    // Validar fecha (no debe ser pasada)
    $fecha_actual = date('Y-m-d');
    if ($fecha_reserva < $fecha_actual) {
        throw new Exception('No se pueden crear reservas para fechas pasadas');
    }

    // Validar horarios
    if ($hora_fin <= $hora_inicio) {
        throw new Exception('La hora de fin debe ser posterior a la hora de inicio');
    }

    // Validar duración mínima (30 minutos)
    $inicio_timestamp = strtotime("$fecha_reserva $hora_inicio");
    $fin_timestamp = strtotime("$fecha_reserva $hora_fin");
    $duracion_minutos = ($fin_timestamp - $inicio_timestamp) / 60;
    
    if ($duracion_minutos < 30) {
        throw new Exception('La reserva debe tener una duración mínima de 30 minutos');
    }

    // Validar duración máxima (8 horas)
    if ($duracion_minutos > 480) {
        throw new Exception('La reserva no puede durar más de 8 horas');
    }

    $estados_validos = ['confirmada', 'cancelada', 'completada'];
    if (!in_array($estado, $estados_validos)) {
        throw new Exception('Estado inválido. Debe ser: ' . implode(', ', $estados_validos));
    }

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que el usuario existe y está activo
    $user_check = "SELECT id, estado FROM usuarios WHERE id = ?";
    $user_stmt = $conn->prepare($user_check);
    $user_stmt->bind_param("i", $usuario_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result()->fetch_assoc();
    
    if (!$user_result) {
        throw new Exception('El usuario especificado no existe');
    }
    
    if ($user_result['estado'] !== 'activo') {
        throw new Exception('El usuario no está activo');
    }

    // Verificar que la sala existe y está disponible
    $sala_check = "SELECT id, nombre, estado FROM salas WHERE id = ?";
    $sala_stmt = $conn->prepare($sala_check);
    $sala_stmt->bind_param("i", $sala_id);
    $sala_stmt->execute();
    $sala_result = $sala_stmt->get_result()->fetch_assoc();
    
    if (!$sala_result) {
        throw new Exception('La sala especificada no existe');
    }
    
    if ($sala_result['estado'] === 'mantenimiento') {
        throw new Exception('La sala "' . $sala_result['nombre'] . '" está en mantenimiento');
    }

    // Verificar solapamientos de horario
    $overlap_check = "SELECT COUNT(*) as total FROM reservas 
                     WHERE sala_id = ? AND fecha_reserva = ? AND estado = 'confirmada'
                     AND (
                         (? >= hora_inicio AND ? < hora_fin) OR
                         (? > hora_inicio AND ? <= hora_fin) OR
                         (? <= hora_inicio AND ? >= hora_fin)
                     )";
    
    $overlap_stmt = $conn->prepare($overlap_check);
    $overlap_stmt->bind_param("isssssss", $sala_id, $fecha_reserva, 
                             $hora_inicio, $hora_inicio, $hora_fin, $hora_fin, 
                             $hora_inicio, $hora_fin);
    $overlap_stmt->execute();
    $overlap_count = $overlap_stmt->get_result()->fetch_assoc()['total'];
    
    if ($overlap_count > 0) {
        throw new Exception('Ya existe una reserva confirmada para esa sala en el horario especificado');
    }

    // Insertar nueva reserva
    $sql = "INSERT INTO reservas (usuario_id, sala_id, fecha_reserva, hora_inicio, hora_fin, 
                                 proposito, estado, notas, fecha_creacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssss", $usuario_id, $sala_id, $fecha_reserva, $hora_inicio, 
                      $hora_fin, $proposito, $estado, $notas);

    if ($stmt->execute()) {
        $reserva_id = $conn->insert_id;
        
        // Obtener la reserva creada con JOINs para respuesta completa
        $get_created = "SELECT r.*, u.nombre as usuario_nombre, u.email as usuario_email,
                               s.nombre as sala_nombre, s.tipo as sala_tipo
                        FROM reservas r
                        JOIN usuarios u ON r.usuario_id = u.id
                        JOIN salas s ON r.sala_id = s.id
                        WHERE r.id = ?";
        
        $get_stmt = $conn->prepare($get_created);
        $get_stmt->bind_param("i", $reserva_id);
        $get_stmt->execute();
        $created_reserva = $get_stmt->get_result()->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'Reserva creada exitosamente',
            'data' => $created_reserva
        ]);
    } else {
        throw new Exception('Error al crear la reserva');
    }
}

// ========== FUNCIÓN UPDATE (PUT) ==========
function handleUpdate() {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || empty($data['id'])) {
        throw new Exception('ID de reserva es requerido');
    }

    $id = intval($data['id']);

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que la reserva existe
    $check_sql = "SELECT * FROM reservas WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $existing_reserva = $check_stmt->get_result()->fetch_assoc();

    if (!$existing_reserva) {
        http_response_code(404);
        throw new Exception('Reserva no encontrada');
    }

    // Preparar campos a actualizar
    $update_fields = [];
    $params = [];
    $types = "";

    if (isset($data['usuario_id'])) {
        $usuario_id = intval($data['usuario_id']);
        
        // Verificar que el usuario existe y está activo
        $user_check = "SELECT id, estado FROM usuarios WHERE id = ?";
        $user_stmt = $conn->prepare($user_check);
        $user_stmt->bind_param("i", $usuario_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result()->fetch_assoc();
        
        if (!$user_result) {
            throw new Exception('El usuario especificado no existe');
        }
        
        if ($user_result['estado'] !== 'activo') {
            throw new Exception('El usuario no está activo');
        }

        $update_fields[] = "usuario_id = ?";
        $params[] = $usuario_id;
        $types .= "i";
    }

    if (isset($data['sala_id'])) {
        $sala_id = intval($data['sala_id']);
        
        // Verificar que la sala existe
        $sala_check = "SELECT id, nombre, estado FROM salas WHERE id = ?";
        $sala_stmt = $conn->prepare($sala_check);
        $sala_stmt->bind_param("i", $sala_id);
        $sala_stmt->execute();
        $sala_result = $sala_stmt->get_result()->fetch_assoc();
        
        if (!$sala_result) {
            throw new Exception('La sala especificada no existe');
        }

        $update_fields[] = "sala_id = ?";
        $params[] = $sala_id;
        $types .= "i";
    }

    if (isset($data['fecha_reserva']) && !empty($data['fecha_reserva'])) {
        $fecha_reserva = trim($data['fecha_reserva']);
        
        // No permitir cambio a fechas pasadas si la reserva está confirmada
        if ($existing_reserva['estado'] === 'confirmada') {
            $fecha_actual = date('Y-m-d');
            if ($fecha_reserva < $fecha_actual) {
                throw new Exception('No se puede cambiar a una fecha pasada');
            }
        }

        $update_fields[] = "fecha_reserva = ?";
        $params[] = $fecha_reserva;
        $types .= "s";
    }

    if (isset($data['hora_inicio']) && !empty($data['hora_inicio'])) {
        $update_fields[] = "hora_inicio = ?";
        $params[] = trim($data['hora_inicio']);
        $types .= "s";
    }

    if (isset($data['hora_fin']) && !empty($data['hora_fin'])) {
        $update_fields[] = "hora_fin = ?";
        $params[] = trim($data['hora_fin']);
        $types .= "s";
    }

    // Validar horarios si se están actualizando
    if (isset($data['hora_inicio']) && isset($data['hora_fin'])) {
        if ($data['hora_fin'] <= $data['hora_inicio']) {
            throw new Exception('La hora de fin debe ser posterior a la hora de inicio');
        }
    }

    if (isset($data['proposito']) && !empty(trim($data['proposito']))) {
        $proposito = trim($data['proposito']);
        
        if (strlen($proposito) < 3 || strlen($proposito) > 255) {
            throw new Exception('El propósito debe tener entre 3 y 255 caracteres');
        }

        $update_fields[] = "proposito = ?";
        $params[] = $proposito;
        $types .= "s";
    }

    if (isset($data['estado']) && !empty($data['estado'])) {
        $estados_validos = ['confirmada', 'cancelada', 'completada'];
        if (!in_array($data['estado'], $estados_validos)) {
            throw new Exception('Estado inválido. Debe ser: ' . implode(', ', $estados_validos));
        }

        $update_fields[] = "estado = ?";
        $params[] = $data['estado'];
        $types .= "s";
        
        // Si se está cancelando, agregar fecha de cancelación
        if ($data['estado'] === 'cancelada') {
            $update_fields[] = "fecha_cancelacion = NOW()";
        }
    }

    if (isset($data['notas'])) {
        $update_fields[] = "notas = ?";
        $params[] = trim($data['notas']) ?: null;
        $types .= "s";
    }

    if (empty($update_fields)) {
        throw new Exception('No hay campos para actualizar');
    }

    // Verificar solapamientos si se están cambiando horarios/sala/fecha
    $check_overlap = isset($data['sala_id']) || isset($data['fecha_reserva']) || 
                     isset($data['hora_inicio']) || isset($data['hora_fin']);
    
    if ($check_overlap && (!isset($data['estado']) || $data['estado'] === 'confirmada')) {
        $check_sala_id = isset($data['sala_id']) ? intval($data['sala_id']) : $existing_reserva['sala_id'];
        $check_fecha = isset($data['fecha_reserva']) ? trim($data['fecha_reserva']) : $existing_reserva['fecha_reserva'];
        $check_hora_inicio = isset($data['hora_inicio']) ? trim($data['hora_inicio']) : $existing_reserva['hora_inicio'];
        $check_hora_fin = isset($data['hora_fin']) ? trim($data['hora_fin']) : $existing_reserva['hora_fin'];
        
        $overlap_check = "SELECT COUNT(*) as total FROM reservas 
                         WHERE sala_id = ? AND fecha_reserva = ? AND estado = 'confirmada' AND id != ?
                         AND (
                             (? >= hora_inicio AND ? < hora_fin) OR
                             (? > hora_inicio AND ? <= hora_fin) OR
                             (? <= hora_inicio AND ? >= hora_fin)
                         )";
        
        $overlap_stmt = $conn->prepare($overlap_check);
        $overlap_stmt->bind_param("isisssssss", $check_sala_id, $check_fecha, $id,
                                 $check_hora_inicio, $check_hora_inicio, $check_hora_fin, $check_hora_fin,
                                 $check_hora_inicio, $check_hora_fin);
        $overlap_stmt->execute();
        $overlap_count = $overlap_stmt->get_result()->fetch_assoc()['total'];
        
        if ($overlap_count > 0) {
            throw new Exception('Ya existe una reserva confirmada para esa sala en el horario especificado');
        }
    }

    // Agregar ID al final
    $params[] = $id;
    $types .= "i";

    // Ejecutar actualización
    $sql = "UPDATE reservas SET " . implode(", ", $update_fields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        // Obtener datos actualizados con JOINs
        $get_updated = "SELECT r.*, u.nombre as usuario_nombre, u.email as usuario_email,
                               s.nombre as sala_nombre, s.tipo as sala_tipo
                        FROM reservas r
                        JOIN usuarios u ON r.usuario_id = u.id
                        JOIN salas s ON r.sala_id = s.id
                        WHERE r.id = ?";
        
        $get_stmt = $conn->prepare($get_updated);
        $get_stmt->bind_param("i", $id);
        $get_stmt->execute();
        $updated_reserva = $get_stmt->get_result()->fetch_assoc();

        echo json_encode([
            'success' => true,
            'message' => 'Reserva actualizada exitosamente',
            'data' => array_merge($updated_reserva, ['fecha_actualizacion' => date('Y-m-d H:i:s')])
        ]);
    } else {
        throw new Exception('Error al actualizar la reserva');
    }
}

// ========== FUNCIÓN DELETE (DELETE) ==========
function handleDelete() {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || empty($data['id'])) {
        throw new Exception('ID de reserva es requerido');
    }

    $id = intval($data['id']);

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que la reserva existe
    $check_sql = "SELECT r.*, u.nombre as usuario_nombre, s.nombre as sala_nombre
                  FROM reservas r
                  JOIN usuarios u ON r.usuario_id = u.id
                  JOIN salas s ON r.sala_id = s.id
                  WHERE r.id = ?";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $reserva = $check_stmt->get_result()->fetch_assoc();

    if (!$reserva) {
        http_response_code(404);
        throw new Exception('Reserva no encontrada');
    }

    // Verificar si se puede eliminar (no permitir eliminar reservas completadas)
    if ($reserva['estado'] === 'completada') {
        http_response_code(400);
        throw new Exception('No se puede eliminar una reserva que ya fue completada');
    }

    // Eliminar reserva
    $delete_sql = "DELETE FROM reservas WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);

    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Reserva eliminada exitosamente',
                'deleted_reserva' => [
                    'id' => $reserva['id'],
                    'proposito' => $reserva['proposito'],
                    'usuario' => $reserva['usuario_nombre'],
                    'sala' => $reserva['sala_nombre'],
                    'fecha' => $reserva['fecha_reserva']
                ]
            ]);
        } else {
            throw new Exception('No se pudo eliminar la reserva');
        }
    } else {
        throw new Exception('Error al eliminar la reserva');
    }
}
?>
