<?php
// ========== CRUD COMPLETO DE USUARIOS ==========
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
    $search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $rol = isset($_GET['rol']) ? trim($_GET['rol']) : '';
    $estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;

    if ($id) {
        // ========== OBTENER USUARIO ESPECÍFICO ==========
        $sql = "SELECT id, nombre, email, rol, telefono, estado, fecha_registro, ultimo_acceso 
                FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true,
                'data' => $user
            ]);
        } else {
            http_response_code(404);
            throw new Exception('Usuario no encontrado');
        }

    } elseif (!empty($search_query) || !empty($rol) || !empty($estado)) {
        // ========== BÚSQUEDA Y FILTROS ==========
        $where_conditions = [];
        $params = [];
        $types = "";

        // Búsqueda por texto (nombre o email)
        if (!empty($search_query)) {
            $where_conditions[] = "(nombre LIKE ? OR email LIKE ?)";
            $search_term = "%" . $search_query . "%";
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= "ss";
        }

        // Filtro por rol
        if (!empty($rol)) {
            $where_conditions[] = "rol = ?";
            $params[] = $rol;
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
        $count_sql = "SELECT COUNT(*) as total FROM usuarios $where_clause";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param($types, ...$params);
        $count_stmt->execute();
        $total = $count_stmt->get_result()->fetch_assoc()['total'];

        // Obtener resultados con paginación
        $offset = ($page - 1) * $limit;
        $sql = "SELECT id, nombre, email, rol, telefono, estado, fecha_registro, ultimo_acceso 
                FROM usuarios $where_clause 
                ORDER BY fecha_registro DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $usuarios,
            'total' => intval($total),
            'search' => [
                'query' => $search_query,
                'rol' => $rol,
                'estado' => $estado
            ],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);

    } else {
        // ========== LISTAR TODOS LOS USUARIOS ==========
        $offset = ($page - 1) * $limit;

        // Contar total
        $total = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];

        // Obtener usuarios
        $sql = "SELECT id, nombre, email, rol, telefono, estado, fecha_registro, ultimo_acceso 
                FROM usuarios 
                ORDER BY fecha_registro DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $usuarios,
            'pagination' => [
                'total' => intval($total),
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
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
        throw new Exception('Datos JSON inválidos');
    }

    // Validar campos requeridos
    $required = ['nombre', 'email', 'password', 'rol'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo '$field' es requerido");
        }
    }

    // Validaciones específicas
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    if (strlen($data['password']) < 6) {
        throw new Exception('La contraseña debe tener al menos 6 caracteres');
    }

    // Validar confirmación de contraseña
    if (isset($data['confirmar_password']) && $data['password'] !== $data['confirmar_password']) {
        throw new Exception('Las contraseñas no coinciden');
    }

    // Validar rol
    $roles_validos = ['administrativo', 'docente'];
    if (!in_array($data['rol'], $roles_validos)) {
        throw new Exception('Rol inválido. Debe ser: ' . implode(', ', $roles_validos));
    }

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar email único
    $check_email = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('El email ya está registrado');
    }

    // Preparar datos
    $nombre = trim($data['nombre']);
    $email = trim(strtolower($data['email']));
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $rol = $data['rol'];
    $telefono = isset($data['telefono']) ? trim($data['telefono']) : null;
    $estado = 'activo';

    // Insertar usuario
    $sql = "INSERT INTO usuarios (nombre, email, password, rol, telefono, estado) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nombre, $email, $password, $rol, $telefono, $estado);

    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => [
                'id' => $user_id,
                'nombre' => $nombre,
                'email' => $email,
                'rol' => $rol,
                'telefono' => $telefono,
                'estado' => $estado,
                'fecha_registro' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Error al crear el usuario');
    }
}

// ========== FUNCIÓN UPDATE (PUT) ==========
function handleUpdate() {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || empty($data['id'])) {
        throw new Exception('ID de usuario es requerido');
    }

    $id = intval($data['id']);

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que el usuario existe
    $check_sql = "SELECT id, email FROM usuarios WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $existing_user = $check_stmt->get_result()->fetch_assoc();

    if (!$existing_user) {
        http_response_code(404);
        throw new Exception('Usuario no encontrado');
    }

    // Preparar campos a actualizar
    $update_fields = [];
    $params = [];
    $types = "";

    if (isset($data['nombre']) && !empty(trim($data['nombre']))) {
        $update_fields[] = "nombre = ?";
        $params[] = trim($data['nombre']);
        $types .= "s";
    }

    if (isset($data['email']) && !empty(trim($data['email']))) {
        $email = trim(strtolower($data['email']));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }

        // Verificar que el email no esté en uso por otro usuario
        if ($email !== $existing_user['email']) {
            $email_check = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
            $email_stmt = $conn->prepare($email_check);
            $email_stmt->bind_param("si", $email, $id);
            $email_stmt->execute();
            
            if ($email_stmt->get_result()->num_rows > 0) {
                throw new Exception('El email ya está en uso por otro usuario');
            }
        }

        $update_fields[] = "email = ?";
        $params[] = $email;
        $types .= "s";
    }

    if (isset($data['rol']) && !empty($data['rol'])) {
        $roles_validos = ['administrativo', 'docente'];
        if (!in_array($data['rol'], $roles_validos)) {
            throw new Exception('Rol inválido. Debe ser: ' . implode(', ', $roles_validos));
        }

        $update_fields[] = "rol = ?";
        $params[] = $data['rol'];
        $types .= "s";
    }

    if (isset($data['telefono'])) {
        $update_fields[] = "telefono = ?";
        $params[] = trim($data['telefono']) ?: null;
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

    if (empty($update_fields)) {
        throw new Exception('No hay campos para actualizar');
    }

    // Agregar ID al final
    $params[] = $id;
    $types .= "i";

    // Ejecutar actualización
    $sql = "UPDATE usuarios SET " . implode(", ", $update_fields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        // Obtener datos actualizados
        $get_updated = "SELECT id, nombre, email, rol, telefono, estado, fecha_registro FROM usuarios WHERE id = ?";
        $get_stmt = $conn->prepare($get_updated);
        $get_stmt->bind_param("i", $id);
        $get_stmt->execute();
        $updated_user = $get_stmt->get_result()->fetch_assoc();

        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente',
            'data' => array_merge($updated_user, ['fecha_actualizacion' => date('Y-m-d H:i:s')])
        ]);
    } else {
        throw new Exception('Error al actualizar el usuario');
    }
}

// ========== FUNCIÓN DELETE (DELETE) ==========
function handleDelete() {
    // Obtener datos JSON
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || empty($data['id'])) {
        throw new Exception('ID de usuario es requerido');
    }

    $id = intval($data['id']);

    // Conexión a BD
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Verificar que el usuario existe
    $check_sql = "SELECT id, nombre, email FROM usuarios WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $user = $check_stmt->get_result()->fetch_assoc();

    if (!$user) {
        http_response_code(404);
        throw new Exception('Usuario no encontrado');
    }

    // Verificar si tiene reservas activas
    $reservas_check = "SELECT COUNT(*) as total FROM reservas WHERE usuario_id = ? AND estado = 'activa'";
    $reservas_stmt = $conn->prepare($reservas_check);
    $reservas_stmt->bind_param("i", $id);
    $reservas_stmt->execute();
    $reservas_activas = $reservas_stmt->get_result()->fetch_assoc()['total'];

    if ($reservas_activas > 0) {
        http_response_code(400);
        throw new Exception('No se puede eliminar: el usuario tiene ' . $reservas_activas . ' reservas activas');
    }

    // Eliminar usuario
    $delete_sql = "DELETE FROM usuarios WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);

    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente',
                'deleted_user' => [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'email' => $user['email']
                ]
            ]);
        } else {
            throw new Exception('No se pudo eliminar el usuario');
        }
    } else {
        throw new Exception('Error al eliminar el usuario');
    }
}
?>