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
                        <form method="POST" action="">
                            <!-- Campo de correo electrónico -->
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">
                                    <i class="bi bi-envelope me-2"></i>Correo Electrónico
                                </label>
                                <input type="email" class="form-control form-control-lg rounded-3 border-2" 
                                       name="email" placeholder="ejemplo@institucion.edu" required>
                            </div>
                            
                            <!-- Campo de contraseña -->
                            <div class="mb-4">
                                <label class="form-label text-dark fw-semibold">
                                    <i class="bi bi-lock me-2"></i>Contraseña
                                </label>
                                <input type="password" class="form-control form-control-lg rounded-3 border-2" 
                                       name="password" placeholder="Ingrese su contraseña" required>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.33 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        
 document.addEventListener('DOMContentLoaded', function() {
    // Si ya hay una sesión activa, redirigir al dashboard correspondiente
    const userData = JSON.parse(localStorage.getItem('userData'));
    if (userData && userData.rol) {
        redirectToDashboard(userData.rol);
        return;
    }

    const loginForm = document.getElementById('login-form');
    const errorMessage = document.getElementById('error-message');
    
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        try {
            const response = await fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Guardar datos del usuario en localStorage
                const userData = {
                    id: data.user.id,
                    nombre: data.user.nombre,
                    email: data.user.email,
                    rol: data.user.rol,
                    token: data.token
                };
                localStorage.setItem('userData', JSON.stringify(userData));
                
                // Redirigir según el rol
                redirectToDashboard(userData.rol);
            } else {
                mostrarError(data.error || 'Credenciales inválidas');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error al iniciar sesión. Por favor, intente nuevamente.');
        }
    });
});

function redirectToDashboard(rol) {
    // Pequeña pausa para asegurar que localStorage se haya actualizado
    setTimeout(() => {
        if (rol === 'administrativo') {
            window.location.replace('dashboard-admin.html');
        } else if (rol === 'docente') {
            window.location.replace('dashboard-docente.html');
        }
    }, 100);
}

function mostrarError(mensaje) {
    const errorMessage = document.getElementById('error-message');
    errorMessage.textContent = mensaje;
    errorMessage.style.display = 'block';
    
    setTimeout(() => {
        errorMessage.style.display = 'none';
    }, 3000);
}
    <script>
</body>
</html>