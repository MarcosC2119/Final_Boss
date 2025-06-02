<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reservas - Login</title>
    <!-- Bootstrap 5.33 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4 mx-auto">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-5">
                        <!-- Logo y título1 -->
                        <div class="text-center mb-4">
                            <div class="bg-primary rounded-3 d-inline-flex p-3 mb-3">
                                <i class="bi bi-building text-white fs-1"></i>
                            </div>
                            <h6 class="text-muted fw-bold mb-0">ROOMIT APP</h6>
                        </div>
                        
                        <!-- Título del sistema -->
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-dark mb-2">Sistema de Reservas</h2>
                            <p class="text-muted">Acceso al Sistema</p>
                        </div>
                        
                        <!-- Formulario de login -->
                        <form method="POST" action="" id="login-form">
                            <!-- Campo de correo electrónico -->
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">
                                    <i class="bi bi-envelope me-2"></i>Correo Electrónico
                                </label>
                                <input type="email" class="form-control form-control-lg rounded-3 border-2" 
                                       name="email" id="email" placeholder="ejemplo@institucion.edu" required>
                            </div>
                            
                            <!-- Campo de contraseña -->
                            <div class="mb-4">
                                <label class="form-label text-dark fw-semibold">
                                    <i class="bi bi-lock me-2"></i>Contraseña
                                </label>
                                <input type="password" class="form-control form-control-lg rounded-3 border-2" 
                                       name="password" id="password" placeholder="Ingrese su contraseña" required>
                            </div>
                            
                            <!-- Botón de iniciar sesión -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                                </button>
                            </div>
                            
                            <!-- Enlace de olvidó contraseña -->
                            <div class="text-center">
                                <a href="recuperar-password.php" class="text-primary text-decoration-none fw-semibold">
                                    ¿Olvidó su contraseña?
                                </a>
                            </div>
                        </form>
                        
                        <!-- Después del formulario -->
                        <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.33 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        
// ========== FUNCIÓN PRINCIPAL QUE SE EJECUTA CUANDO CARGA LA PÁGINA ==========
 document.addEventListener('DOMContentLoaded', function() {
    // Este event listener se ejecuta cuando el DOM está completamente cargado
    
    // ========== VERIFICAR SI SE SOLICITA LOGOUT ==========
    // Revisar si la URL contiene parámetro ?logout=true
    const urlParams = new URLSearchParams(window.location.search);
    const logoutParam = urlParams.get('logout');

    if (logoutParam === 'true') {
        // Limpiar datos locales
        localStorage.removeItem('userData');
        sessionStorage.clear();
        
        // Mostrar mensaje de logout exitoso
        setTimeout(() => {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>Sesión cerrada correctamente
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto-ocultar después de 4 segundos
            setTimeout(() => {
                if (alertDiv && alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 4000);
        }, 100);
        
        // Limpiar URL sin recargar página
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    // ========== VERIFICACIÓN DE SESIÓN ACTIVA ==========
    // Revisa si ya hay datos de usuario guardados en localStorage del navegador
    const userData = JSON.parse(localStorage.getItem('userData'));
    if (userData && userData.rol) {
        // Si existe una sesión activa, mostrar opción de continuar o hacer logout
        mostrarOpcionSesionActiva(userData);
        return; // Sale de la función para evitar configurar el formulario
    }

    // ========== OBTENCIÓN DE ELEMENTOS DEL DOM ==========
    // Obtiene referencias a los elementos HTML necesarios
    const loginForm = document.getElementById('login-form'); // Formulario HTML (línea 30)
    const errorMessage = document.getElementById('error-message'); // Div para mostrar errores (línea 59)
    
    // ========== CONFIGURACIÓN DEL EVENT LISTENER DEL FORMULARIO ==========
    loginForm.addEventListener('submit', async function(e) {
        // Este event listener se ejecuta cuando el usuario envía el formulario (click en botón o Enter)
        
        e.preventDefault(); // Previene el envío tradicional del formulario (sin AJAX)
        
        // ========== OBTENCIÓN DE DATOS DEL FORMULARIO ==========
        // Obtiene los valores de los campos de entrada del HTML
        const email = document.getElementById('email').value; // Campo email (línea 38)
        const password = document.getElementById('password').value; // Campo password (línea 46)
        
        try {
            // ========== PETICIÓN AJAX AL BACKEND ==========
            // Realiza una petición HTTP POST al archivo auth.php en Backend/api/
            const response = await fetch('../Backend/api/auth.php', {
                method: 'POST', // Método HTTP POST
                headers: {
                    'Content-Type': 'application/json' // Indica que enviamos datos JSON
                },
                // Convierte los datos JavaScript a formato JSON para enviar al servidor
                body: JSON.stringify({ email, password })
            });
            
            // ========== PROCESAMIENTO DE LA RESPUESTA ==========
            // Convierte la respuesta del servidor de JSON a objeto JavaScript
            const data = await response.json();
            
            // ========== VERIFICACIÓN DE ÉXITO EN LA AUTENTICACIÓN ==========
            if (data.success) {
                // Si el login fue exitoso (respuesta de auth.php línea 48-58)
                
                // ========== ALMACENAMIENTO DE DATOS DE USUARIO ==========
                // Crea objeto con los datos del usuario recibidos del servidor
                const userData = {
                    id: data.user.id,           // ID del usuario desde la BD
                    nombre: data.user.nombre,   // Nombre del usuario desde la BD
                    email: data.user.email,     // Email del usuario desde la BD
                    rol: data.user.rol,         // Rol del usuario desde la BD (administrativo/docente)
                    token: data.token           // Token de sesión generado en auth.php
                };
                // Guarda los datos en localStorage del navegador para persistencia
                localStorage.setItem('userData', JSON.stringify(userData));
                
                // ========== REDIRECCIÓN SEGÚN ROL ==========
                // Llama a función para redirigir según el rol del usuario
                redirectToDashboard(userData.rol); // Función definida en línea 137
            } else {
                // Si el login falló (respuesta de auth.php línea 64-68)
                // Muestra el mensaje de error recibido del servidor
                mostrarError(data.error || 'Credenciales inválidas'); // Función definida en línea 148
            }
        } catch (error) {
            // ========== MANEJO DE ERRORES DE CONEXIÓN ==========
            // Se ejecuta si hay problemas de red, servidor caído, etc.
            console.error('Error:', error); // Log para debugging
            mostrarError('Error al iniciar sesión. Por favor, intente nuevamente.'); // Función definida en línea 148
        }
    });
});

// ========== FUNCIÓN DE REDIRECCIÓN SEGÚN ROL ==========
// Esta función se llama desde: línea 78 (sesión activa) y línea 123 (login exitoso)
function redirectToDashboard(rol) {
    // Pequeña pausa para asegurar que localStorage se haya actualizado correctamente
    setTimeout(() => {
        // Redirige según el rol del usuario recibido de la base de datos
        if (rol === 'administrativo') {
            // Redirige a dashboard de administrador (archivo HTML que debe existir)
            window.location.replace('pages/dashboard_admin.html');
        } else if (rol === 'docente') {
            // Redirige a dashboard de docente (archivo PHP que debe existir)
            window.location.replace('pages/dashboard_usuario.php');
        }
        // Nota: usa replace() en lugar de href para evitar que el usuario regrese con botón atrás
    }, 100); // Espera 100ms
}

// ========== FUNCIÓN PARA MOSTRAR MENSAJES DE ERROR ==========
// Esta función se llama desde: línea 125 (login fallido) y línea 129 (error de conexión)
function mostrarError(mensaje) {
    // Obtiene el elemento HTML donde mostrar el error (div línea 59)
    const errorMessage = document.getElementById('error-message');
    errorMessage.textContent = mensaje; // Establece el texto del mensaje
    errorMessage.style.display = 'block'; // Hace visible el div de error
    
    // Oculta automáticamente el mensaje después de 3 segundos
    setTimeout(() => {
        errorMessage.style.display = 'none'; // Oculta el div de error
    }, 3000); // 3000ms = 3 segundos
}

// ========== FUNCIÓN PARA MOSTRAR OPCIONES CUANDO HAY SESIÓN ACTIVA ==========
function mostrarOpcionSesionActiva(userData) {
    const container = document.querySelector('.card-body');
    container.innerHTML = `
        <div class="text-center mb-4">
            <div class="bg-success rounded-3 d-inline-flex p-3 mb-3">
                <i class="bi bi-person-check text-white fs-1"></i>
            </div>
            <h6 class="text-muted fw-bold mb-0">SESIÓN ACTIVA</h6>
        </div>
        
        <div class="text-center mb-4">
            <h4 class="fw-bold text-dark mb-2">¡Hola ${userData.nombre}!</h4>
            <p class="text-muted">Ya tienes una sesión activa</p>
            <p class="text-primary fw-semibold">${userData.email}</p>
        </div>
        
        <div class="d-grid gap-3">
            <button onclick="redirectToDashboard('${userData.rol}')" class="btn btn-primary btn-lg rounded-3 fw-semibold">
                <i class="bi bi-box-arrow-right me-2"></i>Continuar al Dashboard
            </button>
            <button onclick="cerrarSesion()" class="btn btn-outline-danger btn-lg rounded-3 fw-semibold">
                <i class="bi bi-box-arrow-left me-2"></i>Cerrar Sesión
            </button>
        </div>
    `;
}

// ========== FUNCIÓN PARA CERRAR SESIÓN ==========
function cerrarSesion() {
    localStorage.removeItem('userData');
    location.reload();
}
    </script>
</body>
</html>