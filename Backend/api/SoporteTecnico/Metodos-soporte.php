<?php
// ========== API PARA SOPORTE TÉCNICO ==========
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/db.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'POST':
            handleCrearTicket();
            break;
        case 'GET':
            handleObtenerTickets();
            break;
        case 'PUT':
            handleActualizarTicket();
            break;
        case 'DELETE':
            handleEliminarTicket();
            break;
        default:
            throw new Exception('Método no permitido');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// ========== CREAR TICKET DESDE FORMULARIO ==========
function handleCrearTicket() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validar datos requeridos
    if (!isset($data['email']) || !isset($data['motivo'])) {
        throw new Exception('Email y motivo son requeridos');
    }
    
    $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('Email inválido');
    }
    
    $motivo = trim($data['motivo']);
    if (strlen($motivo) < 10) {
        throw new Exception('El motivo debe tener al menos 10 caracteres');
    }
    
    $contraseña_temporal = isset($data['temp_password']) ? trim($data['temp_password']) : null;
    
    // Determinar tipo y prioridad
    $tipo = 'password_recovery';
    $prioridad = 'media';
    
    // Si menciona palabras urgentes, aumentar prioridad
    $palabras_urgentes = ['urgente', 'inmediato', 'emergencia', 'crítico'];
    foreach ($palabras_urgentes as $palabra) {
        if (stripos($motivo, $palabra) !== false) {
            $prioridad = 'alta';
            break;
        }
    }
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verificar si ya existe una solicitud pendiente para este email
    $check_sql = "SELECT id FROM tickets_soporte 
                  WHERE email_solicitante = ? 
                  AND tipo = 'password_recovery' 
                  AND estado IN ('pendiente', 'en_proceso')";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception('Ya tienes una solicitud de recuperación pendiente. Por favor espera la respuesta del administrador.');
    }
    
    // Insertar nuevo ticket
    $sql = "INSERT INTO tickets_soporte (
                tipo, email_solicitante, asunto, motivo_solicitud, 
                contraseña_temporal, prioridad, estado
            ) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";
    
    $asunto = "Solicitud de recuperación de contraseña - " . $email;
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", 
        $tipo, $email, $asunto, $motivo, $contraseña_temporal, $prioridad
    );
    
    if ($stmt->execute()) {
        $ticket_id = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Solicitud enviada exitosamente. Te contactaremos pronto.',
            'ticket_id' => $ticket_id,
            'data' => [
                'id' => $ticket_id,
                'email' => $email,
                'estado' => 'pendiente',
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Error al crear la solicitud');
    }
}

// ========== OBTENER TICKETS PARA EL PANEL ADMIN ==========
function handleObtenerTickets() {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Parámetros de filtrado
    $busqueda = isset($_GET['search']) ? trim($_GET['search']) : '';
    $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
    $tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
    $prioridad = isset($_GET['prioridad']) ? trim($_GET['prioridad']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 10;
    
    // Construir WHERE clause
    $where_conditions = [];
    $params = [];
    $types = "";
    
    if (!empty($busqueda)) {
        $where_conditions[] = "(email_solicitante LIKE ? OR asunto LIKE ? OR motivo_solicitud LIKE ?)";
        $search_param = "%{$busqueda}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= "sss";
    }
    
    if (!empty($estado)) {
        $where_conditions[] = "estado = ?";
        $params[] = $estado;
        $types .= "s";
    }
    
    if (!empty($tipo)) {
        $where_conditions[] = "tipo = ?";
        $params[] = $tipo;
        $types .= "s";
    }
    
    if (!empty($prioridad)) {
        $where_conditions[] = "prioridad = ?";
        $params[] = $prioridad;
        $types .= "s";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Contar total
    $count_sql = "SELECT COUNT(*) as total FROM tickets_soporte $where_clause";
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
    
    // Obtener tickets con paginación
    $offset = ($page - 1) * $per_page;
    $sql = "SELECT t.*, u.nombre as atendido_por_nombre 
            FROM tickets_soporte t
            LEFT JOIN usuarios u ON t.atendido_por = u.id
            $where_clause 
            ORDER BY 
                CASE WHEN t.estado = 'pendiente' THEN 1 
                     WHEN t.estado = 'en_proceso' THEN 2 
                     ELSE 3 END,
                CASE WHEN t.prioridad = 'urgente' THEN 1
                     WHEN t.prioridad = 'alta' THEN 2
                     WHEN t.prioridad = 'media' THEN 3
                     ELSE 4 END,
                t.fecha_creacion DESC
            LIMIT ? OFFSET ?";
    
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tickets = [];
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $tickets,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page),
            'total_items' => intval($total)
        ],
        'estadisticas' => obtenerEstadisticas($conn)
    ]);
}

function obtenerEstadisticas($conn) {
    $sql = "SELECT 
                COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                COUNT(CASE WHEN estado = 'en_proceso' THEN 1 END) as en_proceso,
                COUNT(CASE WHEN estado = 'resuelto' AND DATE(fecha_actualizacion) = CURDATE() THEN 1 END) as resueltos_hoy,
                COUNT(CASE WHEN prioridad = 'urgente' AND estado NOT IN ('resuelto', 'cerrado') THEN 1 END) as urgentes
            FROM tickets_soporte";
    
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

// ========== ELIMINAR TICKET ==========
function handleEliminarTicket() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!isset($data['id'])) {
        throw new Exception('ID de ticket requerido');
    }
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verificar si el ticket existe
    $check_sql = "SELECT id FROM tickets_soporte WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $data['id']);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        throw new Exception('Ticket no encontrado');
    }
    
    // Eliminar ticket
    $sql = "DELETE FROM tickets_soporte WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Ticket eliminado exitosamente'
        ]);
    } else {
        throw new Exception('Error al eliminar el ticket');
    }
}

// ========== ACTUALIZAR TICKET (MEJORADO) ==========
function handleActualizarTicket() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!isset($data['id'])) {
        throw new Exception('ID de ticket requerido');
    }
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // ✅ VERIFICAR QUE EL TICKET EXISTE
    $check_sql = "SELECT id FROM tickets_soporte WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $data['id']);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        throw new Exception('Ticket no encontrado');
    }
    
    $update_fields = [];
    $params = [];
    $types = "";
    
    // ✅ MANEJAR ACCIÓN ESPECIAL PARA GENERAR PASSWORD
    if (isset($data['accion']) && $data['accion'] === 'generar_password') {
        if (!isset($data['email']) || !isset($data['nueva_password_generada'])) {
            throw new Exception('Email y nueva contraseña requeridos');
        }
        
        // Encontrar el ticket por email y actualizar
        $sql = "UPDATE tickets_soporte SET 
                nueva_password_generada = ?, 
                estado = 'resuelto',
                fecha_actualizacion = NOW()
                WHERE email_solicitante = ? AND tipo = 'password_recovery'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $data['nueva_password_generada'], $data['email']);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Contraseña temporal generada exitosamente'
            ]);
        } else {
            throw new Exception('Error al guardar la contraseña temporal');
        }
        return;
    }
    
    // ✅ ACTUALIZACIONES NORMALES DE ESTADO
    if (isset($data['estado'])) {
        $update_fields[] = "estado = ?";
        $params[] = $data['estado'];
        $types .= "s";
    }
    
    if (isset($data['respuesta_admin'])) {
        $update_fields[] = "respuesta_admin = ?, fecha_respuesta = NOW()";
        $params[] = $data['respuesta_admin'];
        $types .= "s";
    }
    
    if (isset($data['nueva_password_generada'])) {
        $update_fields[] = "nueva_password_generada = ?";
        $params[] = $data['nueva_password_generada'];
        $types .= "s";
    }
    
    // ✅ MANEJAR atendido_por SIN FOREIGN KEY ERROR
    if (isset($data['atendido_por'])) {
        // Verificar que el usuario existe
        $user_check = "SELECT id FROM usuarios WHERE id = ?";
        $user_stmt = $conn->prepare($user_check);
        $user_stmt->bind_param("i", $data['atendido_por']);
        $user_stmt->execute();
        
        if ($user_stmt->get_result()->num_rows > 0) {
            $update_fields[] = "atendido_por = ?";
            $params[] = $data['atendido_por'];
            $types .= "i";
        }
        // Si el usuario no existe, simplemente no actualizar este campo
    }
    
    if (empty($update_fields)) {
        throw new Exception('No hay campos para actualizar');
    }
    
    // Agregar fecha de actualización
    $update_fields[] = "fecha_actualizacion = NOW()";
    
    $params[] = $data['id'];
    $types .= "i";
    
    $sql = "UPDATE tickets_soporte SET " . implode(", ", $update_fields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Ticket actualizado exitosamente'
        ]);
    } else {
        throw new Exception('Error al actualizar el ticket: ' . $conn->error);
    }
}
?>