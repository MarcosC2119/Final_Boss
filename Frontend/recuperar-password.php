<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Sistema de Reservas</title>
    <!-- Bootstrap 5.33 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto">
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
                            <h2 class="fw-bold text-dark mb-3">Recuperar Contraseña</h2>
                            <p class="text-muted">¿Has olvidado tu contraseña? Solicita un cambio al administrador</p>
                        </div>
                        
                        <!-- Formulario de recuperación -->
                        <form method="POST" action="">
                            <!-- Campo de correo electrónico institucional -->
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">
                                    <i class="bi bi-envelope me-2"></i>Correo Electrónico Institucional
                                </label>
                                <input type="email" class="form-control form-control-lg rounded-3 border-2" 
                                       name="email" placeholder="ejemplo@institucion.edu" required>
                            </div>
                            
                            <!-- Motivo de la solicitud -->
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">
                                    <i class="bi bi-chat-text me-2"></i>Motivo de la Solicitud
                                </label>
                                <textarea class="form-control rounded-3 border-2" rows="4" 
                                          name="motivo" placeholder="Por favor, describe brevemente por qué necesitas cambiar tu contraseña" required></textarea>
                            </div>
                            
                            <!-- Contraseña temporal (opcional) -->
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">
                                    <i class="bi bi-key me-2"></i>Contraseña Temporal (opcional)
                                </label>
                                <input type="password" class="form-control form-control-lg rounded-3 border-2" 
                                       name="temp_password" placeholder="Si tienes una contraseña temporal, ingrésala aquí">
                                <div class="form-text text-muted mt-2">
                                    <small>La contraseña temporal es proporcionada por el administrador</small>
                                </div>
                            </div>
                            
                            <!-- Botón de enviar solicitud -->
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold">
                                    <i class="bi bi-send me-2"></i>Enviar Solicitud
                                </button>
                            </div>
                            
                            <!-- Enlace de volver al login -->
                            <div class="text-center">
                                <a href="login.php" class="text-primary text-decoration-none fw-semibold">
                                    <i class="bi bi-arrow-left me-2"></i>Volver al Inicio de Sesión
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
</body>
</html> 