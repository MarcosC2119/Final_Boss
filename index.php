<?php
// ========== REDIRECCIÓN AUTOMÁTICA AL LOGIN ==========
// Este archivo redirige automáticamente a la página de login del sistema

// Establecer la ruta del login
$login_path = 'Frontend/login.php';

// Verificar que el archivo existe
if (file_exists($login_path)) {
    // Redirigir al login
    header("Location: $login_path");
    exit();
} else {
    // Si no existe el archivo, mostrar error
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Sistema de Reservas</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center vh-100">
        <div class="text-center">
            <div class="alert alert-danger">
                <h4>❌ Error del Sistema</h4>
                <p>No se pudo encontrar el archivo de login.</p>
                <p><strong>Ruta esperada:</strong> ' . $login_path . '</p>
            </div>
        </div>
    </body>
    </html>';
}
?> 