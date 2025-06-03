<?php
// ========== HEADERS OPTIMIZADOS ==========
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// ========== MANEJO RÁPIDO DE OPTIONS ==========
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/db.php';

try {
    // ========== PROCESAR DATOS ==========
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // ========== VALIDACIÓN ==========
    if (!$data || empty($data['email']) || empty($data['password'])) {
        throw new Exception('Email y contraseña son requeridos');
    }
    
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $password = $data['password'];
    
    // ========== CONSULTA OPTIMIZADA ==========
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    $sql = "SELECT id, nombre, email, password, rol FROM usuarios 
            WHERE email = ? AND estado = 'activo' LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // ========== VERIFICACIÓN ==========
        if ($user && password_verify($password, $user['password'])) {
            
            // ========== TOKEN Y ACTUALIZACIÓN ==========
            $token = bin2hex(random_bytes(32));
            
            $updateSql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            
            // ========== RESPUESTA EXITOSA ==========
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => (int)$user['id'],
                    'nombre' => $user['nombre'],
                    'email' => $user['email'],
                    'rol' => $user['rol']
                ],
                'token' => $token
            ]);
        } else {
            throw new Exception('Credenciales inválidas');
        }
    } else {
        throw new Exception('Error en la consulta');
    }
    
} catch (Exception $e) {
    // ========== ERROR HANDLING ==========
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
    

