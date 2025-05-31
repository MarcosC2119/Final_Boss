<?php
// ========== CONFIGURACIÓN DE HEADERS HTTP ==========
// Estos headers son necesarios para que el frontend pueda comunicarse con este archivo PHP

// Indica que la respuesta será en formato JSON
header('Content-Type: application/json');

// CORS (Cross-Origin Resource Sharing) - Permite peticiones desde cualquier dominio
// En producción debería ser más restrictivo, ej: 'Access-Control-Allow-Origin: https://tudominio.com'
header('Access-Control-Allow-Origin: *');

// Métodos HTTP permitidos para esta API
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Headers que el cliente puede enviar en las peticiones
header('Access-Control-Allow-Headers: Content-Type');

// ========== MANEJO DE PETICIONES OPTIONS ==========
// Las peticiones OPTIONS son enviadas automáticamente por el navegador antes de peticiones CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Responde OK
    exit(); // Termina la ejecución aquí para peticiones OPTIONS
}

// ========== CONEXIÓN A LA BASE DE DATOS ==========
// Incluye el archivo de configuración de la base de datos (debe estar en Backend/config/)
require_once '../config/db.php';

try {
    // ========== OBTENCIÓN Y VALIDACIÓN DE DATOS JSON ==========
    // Obtiene los datos JSON enviados desde el frontend (login.php línea 102)
    $json = file_get_contents('php://input'); // Lee el cuerpo de la petición HTTP
    $data = json_decode($json, true); // Convierte JSON a array asociativo PHP
    
    // ========== VALIDACIÓN DE CAMPOS REQUERIDOS ==========
    // Verifica que se hayan enviado email y password desde el formulario
    if (!$data || !isset($data['email']) || !isset($data['password'])) {
        // Si faltan datos, lanza excepción que será capturada más abajo
        throw new Exception('Email y contraseña son requeridos');
    }
    
    // ========== EXTRACCIÓN DE VARIABLES ==========
    // Extrae los valores enviados desde el frontend (login.php líneas 95-96)
    $email = $data['email'];       // Email ingresado en el formulario
    $password = $data['password']; // Contraseña ingresada en el formulario
    
    // ========== CONEXIÓN A BASE DE DATOS ==========
    // Utiliza el patrón Singleton para obtener la conexión (definido en db.php)
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // ========== CONSULTA SQL PARA VERIFICAR USUARIO ==========
    // Busca el usuario en la tabla 'usuarios' por email y que esté activo
    $sql = "SELECT id, nombre, email, password, rol FROM usuarios 
            WHERE email = ? AND estado = 'activo'";
    
    // ========== PREPARACIÓN Y EJECUCIÓN DE CONSULTA ==========
    // Usa prepared statement para prevenir inyección SQL
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email); // Vincula el parámetro email (s = string)
    
    // ========== EJECUCIÓN Y PROCESAMIENTO DE RESULTADOS ==========
    if ($stmt->execute()) {
        $result = $stmt->get_result(); // Obtiene el resultado de la consulta
        $user = $result->fetch_assoc(); // Convierte el resultado a array asociativo
        
        // ========== VERIFICACIÓN DE USUARIO Y CONTRASEÑA ==========
        // Verifica que el usuario existe y que la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            // password_verify() compara la contraseña plana con el hash almacenado en BD
            
            // ========== GENERACIÓN DE TOKEN DE SESIÓN ==========
            // Genera un token único para esta sesión (32 bytes = 64 caracteres hexadecimales)
            $token = bin2hex(random_bytes(32));
            
            // ========== ACTUALIZACIÓN DE ÚLTIMO ACCESO ==========
            // Registra cuando fue el último acceso del usuario
            $updateSql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $user['id']); // i = integer
            $updateStmt->execute();
            
            // ========== RESPUESTA EXITOSA ==========
            // Envía los datos del usuario de vuelta al frontend (recibido en login.php línea 104)
            echo json_encode([
                'success' => true, // Indica que el login fue exitoso
                'user' => [
                    // Datos del usuario que se guardarán en localStorage (login.php líneas 108-113)
                    'id' => $user['id'],
                    'nombre' => $user['nombre'], 
                    'email' => $user['email'],
                    'rol' => $user['rol'] // Usado para redirección (login.php línea 117)
                ],
                'token' => $token // Token para futuras peticiones autenticadas
            ]);
        } else {
            // ========== CREDENCIALES INVÁLIDAS ==========
            // Usuario no encontrado o contraseña incorrecta
            throw new Exception('Credenciales inválidas');
        }
    } else {
        // ========== ERROR EN LA CONSULTA SQL ==========
        throw new Exception('Error en la consulta');
    }
    
} catch (Exception $e) {
    // ========== MANEJO DE ERRORES ==========
    // Cualquier error en el proceso llega aquí
    
    // Establece código de respuesta HTTP 401 (No autorizado)
    http_response_code(401);
    
    // ========== RESPUESTA DE ERROR ==========
    // Envía el error de vuelta al frontend (recibido en login.php línea 120)
    echo json_encode([
        'success' => false,           // Indica que el login falló
        'error' => $e->getMessage()   // Mensaje de error específico
    ]);
}
?>
    

