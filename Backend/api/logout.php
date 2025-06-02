<?php
// ========== CONFIGURACIÓN DE HEADERS HTTP ==========
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// ========== MANEJO DE PETICIONES OPTIONS ==========
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ========== INICIAR SESIÓN PHP ==========
session_start();

try {
    // ========== OBTENER DATOS DE LA PETICIÓN ==========
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // ========== OBTENER TOKEN DEL USUARIO ==========
    $token = null;
    if (isset($data['token'])) {
        $token = $data['token'];
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
    }
    
    // ========== REGISTRAR LOGOUT EN LOGS (OPCIONAL) ==========
    if (isset($data['userId'])) {
        error_log("Usuario {$data['userId']} cerró sesión - " . date('Y-m-d H:i:s'));
    }
    
    // ========== CONEXIÓN A BASE DE DATOS (OPCIONAL) ==========
    // Si quieres invalidar tokens en BD, descomenta estas líneas:
    /*
    require_once '../config/db.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if ($token && isset($data['userId'])) {
        // Actualizar último logout del usuario
        $sql = "UPDATE usuarios SET ultimo_logout = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $data['userId']);
        $stmt->execute();
    }
    */
    
    // ========== LIMPIAR SESIÓN PHP ==========
    $_SESSION = array();
    
    // ========== DESTRUIR COOKIE DE SESIÓN ==========
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // ========== DESTRUIR SESIÓN ==========
    session_destroy();
    
    // ========== RESPUESTA EXITOSA ==========
    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada correctamente',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // ========== MANEJO DE ERRORES ==========
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al cerrar sesión: ' . $e->getMessage()
    ]);
}
?> 