<?php
// ========== HEADERS OPTIMIZADOS ==========
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// ========== MANEJO RÁPIDO DE OPTIONS ==========
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

try {
    // ========== PROCESAR DATOS ==========
    $json = file_get_contents('php://input');
    $data = json_decode($json, true) ?? [];
    
    // ========== OBTENER TOKEN OPTIMIZADO ==========
    $token = $data['token'] ?? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
    
    // ========== LOG OPCIONAL ==========
    if (!empty($data['userId'])) {
        error_log("Logout - Usuario: {$data['userId']} - " . date('Y-m-d H:i:s'));
    }
    
    // ========== LIMPIAR SESIÓN OPTIMIZADO ==========
    $_SESSION = [];
    
    // ========== DESTRUIR COOKIE ==========
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    // ========== RESPUESTA EXITOSA ==========
    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada correctamente',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // ========== ERROR HANDLING ==========
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al cerrar sesión: ' . $e->getMessage()
    ]);
}
?> 