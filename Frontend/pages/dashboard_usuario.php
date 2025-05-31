<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Estilos mínimos necesarios que no se pueden lograr con Bootstrap solo */
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e3f2fd 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .custom-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 20px rgba(25, 118, 210, 0.08);
        }
        .custom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(25, 118, 210, 0.15);
        }
        .btn-custom-primary {
            background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(25, 118, 210, 0.3);
        }
        .btn-custom-primary:hover {
            background: linear-gradient(135deg, #1565c0 0%, #1976d2 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 118, 210, 0.4);
        }
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1976d2, #2196f3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="min-vh-100">
    <!-- Container principal con efecto glassmorphism -->
    <div class="container main-container rounded-4 shadow-lg my-4 py-5 px-4">
        
        <!-- 1. Tarjetas de resumen estadísticas -->
        <div class="row mb-5 g-4">
            <div class="col-md-4">
                <div class="card custom-card rounded-4 h-100 text-center border-0">
                    <div class="card-body py-4">
                        <i class="bi bi-calendar-check text-primary fs-1 mb-3"></i>
                        <h6 class="card-title fw-bold text-primary mb-3" style="letter-spacing: 0.5px;">Reservas Activas</h6>
                        <div class="stat-number">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card rounded-4 h-100 text-center border-0">
                    <div class="card-body py-4">
                        <i class="bi bi-door-open text-success fs-1 mb-3"></i>
                        <h6 class="card-title fw-bold text-primary mb-3" style="letter-spacing: 0.5px;">Salas Disponibles</h6>
                        <div class="stat-number text-success">0</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card rounded-4 h-100 text-center border-0">
                    <div class="card-body py-4">
                        <i class="bi bi-exclamation-triangle text-warning fs-1 mb-3"></i>
                        <h6 class="card-title fw-bold text-primary mb-3" style="letter-spacing: 0.5px;">Problemas/Casos</h6>
                        <div class="stat-number text-warning">0</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Acciones Rápidas -->
        <div class="mb-5">
            <h5 class="fw-bold text-primary mb-4" style="letter-spacing: 0.5px;">
                <i class="bi bi-lightning-charge me-2"></i>Acciones Rápidas
            </h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <button class="btn btn-custom-primary btn-lg w-100 rounded-3 py-3 fw-semibold">
                        <i class="bi bi-search me-2"></i>Revisar Salas
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-custom-primary btn-lg w-100 rounded-3 py-3 fw-semibold">
                        <i class="bi bi-plus-circle me-2"></i>Asignar Sala
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-custom-primary btn-lg w-100 rounded-3 py-3 fw-semibold">
                        <i class="bi bi-qr-code me-2"></i>Ver QR
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. Mis Reservas Activas -->
        <div class="mb-5">
            <h5 class="fw-bold text-primary mb-4" style="letter-spacing: 0.5px;">
                <i class="bi bi-bookmark-check me-2"></i>Mis Reservas Activas
            </h5>
            <div class="card custom-card rounded-4 border-0" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                <div class="card-body p-4 position-relative">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="card-title fw-bold text-primary mb-3">
                                <i class="bi bi-door-closed me-2"></i>Sala 101
                            </h6>
                            <div class="d-flex flex-column gap-2">
                                <p class="mb-0 fw-semibold text-dark">
                                    <i class="bi bi-calendar-event text-primary me-2"></i>2024-03-20
                                </p>
                                <p class="mb-0 fw-semibold text-dark">
                                    <i class="bi bi-clock text-primary me-2"></i>09:00 - 11:00
                                </p>
                                <p class="mb-0 fw-semibold text-dark">
                                    <i class="bi bi-book text-primary me-2"></i>Matemáticas
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex flex-column gap-2 justify-content-center">
                            <button class="btn btn-primary rounded-3 fw-semibold">
                                <i class="bi bi-qr-code me-2"></i>Ver QR
                            </button>
                            <button class="btn btn-danger rounded-3 fw-semibold">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Recursos y Ayuda -->
        <div class="mb-5">
            <h5 class="fw-bold text-primary mb-4" style="letter-spacing: 0.5px;">
                <i class="bi bi-life-preserver me-2"></i>Recursos y Ayuda
            </h5>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card custom-card h-100 text-center border-0 rounded-4">
                        <div class="card-body d-flex flex-column justify-content-between p-4">
                            <div>
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-mortarboard fs-1 text-primary"></i>
                                </div>
                                <h6 class="fw-bold text-primary mb-3">Capacitaciones</h6>
                                <p class="text-muted small mb-4">
                                    Accede a videos y materiales de capacitación sobre el uso del sistema
                                </p>
                            </div>
                            <button class="btn btn-outline-primary rounded-pill fw-semibold px-4">
                                Ver Capacitaciones
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card custom-card h-100 text-center border-0 rounded-4">
                        <div class="card-body d-flex flex-column justify-content-between p-4">
                            <div>
                                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-book fs-1 text-success"></i>
                                </div>
                                <h6 class="fw-bold text-primary mb-3">Manual de Usuario</h6>
                                <p class="text-muted small mb-4">
                                    Consulta la guía completa de uso del sistema
                                </p>
                            </div>
                            <button class="btn btn-outline-success rounded-pill fw-semibold px-4">
                                Ver Manual
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card custom-card h-100 text-center border-0 rounded-4">
                        <div class="card-body d-flex flex-column justify-content-between p-4">
                            <div>
                                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                    <i class="bi bi-headset fs-1 text-warning"></i>
                                </div>
                                <h6 class="fw-bold text-primary mb-3">Soporte Técnico</h6>
                                <p class="text-muted small mb-4">
                                    ¿Necesitas ayuda? Contacta con nuestro equipo de soporte
                                </p>
                            </div>
                            <button class="btn btn-outline-warning rounded-pill fw-semibold px-4">
                                Solicitar Ayuda
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Notificaciones -->
        <div class="mb-4">
            <h5 class="fw-bold text-primary mb-4" style="letter-spacing: 0.5px;">
                <i class="bi bi-bell me-2"></i>Notificaciones
            </h5>
            <div class="alert alert-success border-0 rounded-4 d-flex align-items-start p-4" 
                 style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);">
                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                     style="width: 50px; height: 50px; min-width: 50px;">
                    <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="fw-bold text-success mb-2">Reserva Confirmada</h6>
                    <p class="mb-2 text-dark fw-medium">Su reserva para la Sala 101 ha sido confirmada</p>
                    <small class="text-muted">
                        <i class="bi bi-calendar-event me-1"></i>2024-05-19 18:03
                    </small>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS personalizado -->
    <script src="js/gestion-usuarios.js"></script>
</body>
</html>