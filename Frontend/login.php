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
                        <!-- Logo y título -->
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
                        
                        <!-- Div para mensajes de error -->
                        <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5.33 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // ========== INICIALIZACIÓN OPTIMIZADA ==========
        document.addEventListener('DOMContentLoaded', function() {
            handleLogoutParam();
            handleActiveSession();
            initLoginForm();
        });

        // ========== MANEJO DE PARÁMETRO LOGOUT ==========
        function handleLogoutParam() {
            const urlParams = new URLSearchParams(window.location.search);
            const logoutParam = urlParams.get('logout');

            if (logoutParam === 'true') {
                localStorage.removeItem('userData');
                sessionStorage.clear();
                
                showMessage('Sesión cerrada correctamente', 'success');
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }

        // ========== VERIFICACIÓN DE SESIÓN ACTIVA ==========
        function handleActiveSession() {
            const userData = JSON.parse(localStorage.getItem('userData') || 'null');
            if (userData?.rol) {
                mostrarOpcionSesionActiva(userData);
            }
        }

        // ========== INICIALIZACIÓN DEL FORMULARIO ==========
        function initLoginForm() {
            const loginForm = document.getElementById('login-form');
            if (!loginForm) return;

            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                try {
                    const response = await fetch('../Backend/api/auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email, password })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const userData = {
                            id: data.user.id,
                            nombre: data.user.nombre,
                            email: data.user.email,
                            rol: data.user.rol,
                            token: data.token
                        };
                        localStorage.setItem('userData', JSON.stringify(userData));
                        redirectToDashboard(userData.rol);
                    } else {
                        mostrarError(data.error || 'Credenciales inválidas');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    mostrarError('Error al iniciar sesión. Por favor, intente nuevamente.');
                }
            });
        }

        // ========== FUNCIONES AUXILIARES OPTIMIZADAS ==========
        function redirectToDashboard(rol) {
            setTimeout(() => {
                const dashboards = {
                    'administrativo': 'pages/dashboard_admin.html',
                    'docente': 'pages/dashboard_usuario.php'
                };
                
                const dashboard = dashboards[rol];
                if (dashboard) {
                    window.location.replace(dashboard);
                } else {
                    mostrarError('Rol de usuario no válido');
                }
            }, 100);
        }

        function mostrarError(mensaje) {
            const errorDiv = document.getElementById('error-message');
            if (errorDiv) {
                errorDiv.textContent = mensaje;
                errorDiv.style.display = 'block';
                
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                }, 5000);
            }
        }

        function showMessage(mensaje, tipo = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle me-2"></i>${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                if (alertDiv?.parentNode) {
                    alertDiv.remove();
                }
            }, 4000);
        }

        function mostrarOpcionSesionActiva(userData) {
            const cardBody = document.querySelector('.card-body');
            cardBody.innerHTML = `
                <div class="text-center mb-4">
                    <div class="bg-success rounded-3 d-inline-flex p-3 mb-3">
                        <i class="bi bi-person-check text-white fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">Sesión Activa</h4>
                    <p class="text-muted">Hola, <strong>${userData.nombre}</strong></p>
                </div>
                
                <div class="d-grid gap-3">
                    <button type="button" class="btn btn-primary btn-lg rounded-3 fw-semibold" onclick="redirectToDashboard('${userData.rol}')">
                        <i class="bi bi-house-door me-2"></i>Ir al Dashboard
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-lg rounded-3 fw-semibold" onclick="cerrarSesion()">
                        <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                    </button>
                </div>
            `;
        }

        function cerrarSesion() {
            localStorage.removeItem('userData');
            sessionStorage.clear();
            window.location.reload();
        }
    </script>
</body>
</html>