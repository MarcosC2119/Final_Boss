<?php
// Optimización: Simplificar obtención de datos de sesión
session_start();
$email_usuario = $_SESSION['email'] ?? 'usuario@ejemplo.com';
$usuario_nombre = $_SESSION['nombre'] ?? 'Docente';
$usuario_rol = $_SESSION['rol'] ?? 'docente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Docente - ROOMIT</title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* CSS Optimizado - Eliminando redundancias */
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e3f2fd 100%);
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        
        .sidebar-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .nav-link-custom {
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .nav-link-custom:hover, .nav-link-custom.active {
            background: rgba(13, 110, 253, 0.1);
            border-left-color: #0d6efd;
        }
        
        .nav-link-custom:hover { transform: translateX(5px); }
        .nav-link-custom.active { color: #0d6efd !important; }
        
        .user-avatar, .brand-logo {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        
        .brand-logo {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            font-size: 1.5rem;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-hover {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .main-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            min-height: 85vh;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .btn-custom-primary {
            background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
            border: none;
            transition: all 0.3s ease;
            border-radius: 10px;
        }
        
        .btn-custom-primary:hover {
            background: linear-gradient(135deg, #1565c0 0%, #1976d2 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 118, 210, 0.4);
        }
        
        .ticket-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .ticket-card:hover { transform: translateY(-2px); }
        
        /* Estados de tickets optimizados */
        .ticket-pendiente { border-left-color: #ffc107; }
        .ticket-en-proceso { border-left-color: #0d6efd; }
        .ticket-resuelto { border-left-color: #198754; }
        .ticket-cerrado { border-left-color: #6c757d; }
    </style>
</head>
<body class="bg-light">
    <!-- ========== NAVBAR SUPERIOR ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <!-- Brand -->
            <div class="d-flex align-items-center">
                <div class="brand-logo me-3">
                    <i class="bi bi-book"></i>
                </div>
                <div>
                    <span class="navbar-brand mb-0 h1 fw-bold">ROOMIT</span>
                    <small class="d-block text-light opacity-75">Dashboard Docente</small>
                </div>
            </div>

            <!-- Right Side Items -->
            <div class="d-flex align-items-center gap-3">
                <!-- Notifications -->
                <div class="position-relative">
                    <button class="btn btn-outline-light btn-sm rounded-pill" id="btn-notificaciones">
                        <i class="bi bi-bell"></i>
                        <span class="notification-badge" id="badge-respuestas-nuevas" style="display: none;">
                            <span id="count-respuestas-nuevas">0</span>
                        </span>
                    </button>
                </div>

                <!-- User Info -->
                <div class="dropdown">
                    <button class="btn btn-link text-white p-0 d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <?= strtoupper(substr($usuario_nombre, 0, 2)) ?>
                        </div>
                        <div class="text-start d-none d-md-block">
                            <div class="fw-semibold"><?= htmlspecialchars($usuario_nombre) ?></div>
                            <small class="opacity-75"><?= ucfirst($usuario_rol) ?></small>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><h6 class="dropdown-header">Mi Cuenta</h6></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="cerrarSesion()">
                            <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- ========== SIDEBAR OPTIMIZADO ========== -->
            <nav class="col-lg-2 col-md-3 d-md-block sidebar-glass position-sticky" style="top: 0; height: 100vh; overflow-y: auto;">
                <div class="position-sticky pt-4">
                    
                    <!-- Stats Cards en Sidebar -->
                    <div class="px-3 mb-4">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="card border-0 bg-primary bg-gradient text-white card-hover">
                                    <div class="card-body p-2 text-center">
                                        <i class="bi bi-calendar-check fs-4"></i>
                                        <div class="fw-bold" id="stat-reservas">0</div>
                                        <small>Reservas</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card border-0 bg-warning bg-gradient text-white card-hover">
                                    <div class="card-body p-2 text-center">
                                        <i class="bi bi-headset fs-4"></i>
                                        <div class="fw-bold" id="stat-tickets">0</div>
                                        <small>Tickets</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Menu Optimizado -->
                    <div class="px-3">
                        <!-- Acciones Principales -->
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted fw-bold mb-3 px-2" style="font-size: 0.75rem; letter-spacing: 1px;">
                                <i class="bi bi-lightning me-1"></i>Acciones Rápidas
                            </h6>
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom active d-flex align-items-center py-2 px-3 rounded-3 mb-1" 
                                       href="#reservas" onclick="mostrarSeccion('reservas')">
                                        <i class="bi bi-search me-3 fs-5"></i>
                                        <span class="fw-semibold">Revisar Salas</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom d-flex align-items-center py-2 px-3 rounded-3 mb-1" 
                                       href="#nueva-reserva" onclick="mostrarSeccion('nueva-reserva')">
                                        <i class="bi bi-plus-circle me-3 fs-5"></i>
                                        <span class="fw-semibold">Nueva Reserva</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom d-flex align-items-center py-2 px-3 rounded-3 mb-1" 
                                       href="#mis-qr" onclick="mostrarSeccion('mis-qr')">
                                        <i class="bi bi-qr-code me-3 fs-5"></i>
                                        <span class="fw-semibold">Mis Códigos QR</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Soporte y Ayuda -->
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted fw-bold mb-3 px-2" style="font-size: 0.75rem; letter-spacing: 1px;">
                                <i class="bi bi-life-preserver me-1"></i>Soporte
                            </h6>
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom d-flex align-items-center py-2 px-3 rounded-3 mb-1" 
                                       href="#soporte" onclick="mostrarSeccion('soporte')">
                                        <i class="bi bi-headset me-3 fs-5"></i>
                                        <span class="fw-semibold">Soporte Técnico</span>
                                        <span class="ms-auto badge bg-danger rounded-pill" 
                                              id="sidebar-badge-respuestas" style="display: none;">0</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom d-flex align-items-center py-2 px-3 rounded-3 mb-1" 
                                       href="#capacitaciones" onclick="mostrarSeccion('capacitaciones')">
                                        <i class="bi bi-mortarboard me-3 fs-5"></i>
                                        <span class="fw-semibold">Capacitaciones</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link nav-link-custom d-flex align-items-center py-2 px-3 rounded-3 mb-1" 
                                       href="#manual" onclick="mostrarSeccion('manual')">
                                        <i class="bi bi-book me-3 fs-5"></i>
                                        <span class="fw-semibold">Manual</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- ========== CONTENIDO PRINCIPAL ========== -->
            <main class="col-lg-10 col-md-9 ms-sm-auto px-md-4">
                <div class="main-content p-4 mt-4">
                    
                    <!-- ========== HEADER CON BREADCRUMB ========== -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold text-dark mb-1">Dashboard Docente</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Inicio</a></li>
                                    <li class="breadcrumb-item active" aria-current="page" id="breadcrumb-actual">Resumen</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2" id="header-actions">
                            <!-- Se actualizará dinámicamente según la sección -->
                            <button class="btn btn-outline-primary" onclick="cargarDashboard()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
                            </button>
                        </div>
                    </div>

                    <!-- ========== SECCIÓN: RESUMEN (Dashboard Principal) ========== -->
                    <div id="seccion-resumen" class="content-section">
                        <!-- Estadísticas Principales -->
                        <div class="row mb-4 g-4">
                            <div class="col-xl-3 col-md-6">
                                <div class="section-card h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                            <i class="bi bi-calendar-check text-primary fs-1"></i>
                                        </div>
                                        <h3 class="fw-bold text-primary mb-1" id="total-reservas-activas">0</h3>
                                        <p class="text-muted mb-0">Reservas Activas</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="section-card h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                            <i class="bi bi-door-open text-success fs-1"></i>
                                        </div>
                                        <h3 class="fw-bold text-success mb-1" id="salas-disponibles">0</h3>
                                        <p class="text-muted mb-0">Salas Disponibles</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="section-card h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                            <i class="bi bi-headset text-warning fs-1"></i>
                                        </div>
                                        <h3 class="fw-bold text-warning mb-1" id="tickets-pendientes">0</h3>
                                        <p class="text-muted mb-0">Tickets de Soporte</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="section-card h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                            <i class="bi bi-qr-code text-info fs-1"></i>
                                        </div>
                                        <h3 class="fw-bold text-info mb-1" id="qr-generados">0</h3>
                                        <p class="text-muted mb-0">Códigos QR</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mis Reservas Activas -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="section-card">
                                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="fw-bold text-primary mb-0">
                                                <i class="bi bi-bookmark-check me-2"></i>Mis Reservas Activas
                                            </h5>
                                            <button class="btn btn-outline-primary btn-sm" onclick="verTodasReservas()">
                                                <i class="bi bi-list me-1"></i>Ver Todas
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="mis-reservas-container">
                                            <!-- Se cargarán las reservas dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notificaciones Recientes -->
                        <div class="row">
                            <div class="col-12">
                                <div class="section-card">
                                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                        <h5 class="fw-bold text-primary mb-0">
                                            <i class="bi bi-bell me-2"></i>Notificaciones Recientes
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="notificaciones-container">
                                            <!-- Se cargarán las notificaciones dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== SECCIÓN: SOPORTE TÉCNICO ========== -->
                    <div id="seccion-soporte" class="content-section" style="display: none;">
                        <!-- Respuestas Pendientes (Alerta destacada) -->
                        <div id="seccion-respuestas-pendientes" class="mb-4" style="display: none;">
                            <div class="alert alert-success border-0 rounded-4 shadow-sm">
                                <div class="d-flex align-items-start">
                                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 50px; height: 50px; min-width: 50px;">
                                        <i class="bi bi-chat-dots-fill fs-4 text-success"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold text-success mb-2">
                                            <i class="bi bi-bell me-2"></i>¡Tienes respuestas del soporte técnico!
                                        </h6>
                                        <p class="mb-2">El administrador ha respondido a tus solicitudes:</p>
                                        <div id="lista-respuestas-pendientes" class="mb-3">
                                            <!-- Se llenará dinámicamente -->
                                        </div>
                                        <button class="btn btn-success btn-sm rounded-pill" onclick="verTodasRespuestas()">
                                            <i class="bi bi-eye me-1"></i>Ver Todas las Respuestas
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas de Soporte -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="section-card text-center" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
                                    <div class="card-body py-3">
                                        <i class="bi bi-clock-history text-warning fs-4 mb-2"></i>
                                        <h5 class="fw-bold mb-1" id="mis-tickets-pendientes">0</h5>
                                        <small class="text-muted">Pendientes</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="section-card text-center" style="background: linear-gradient(135deg, #cce5ff 0%, #74b9ff 100%);">
                                    <div class="card-body py-3">
                                        <i class="bi bi-gear text-primary fs-4 mb-2"></i>
                                        <h5 class="fw-bold mb-1" id="mis-tickets-proceso">0</h5>
                                        <small class="text-muted">En Proceso</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="section-card text-center" style="background: linear-gradient(135deg, #d4edda 0%, #00b894 100%);">
                                    <div class="card-body py-3">
                                        <i class="bi bi-check-circle text-success fs-4 mb-2"></i>
                                        <h5 class="fw-bold mb-1" id="mis-tickets-resueltos">0</h5>
                                        <small class="text-muted">Resueltos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="section-card text-center" style="background: linear-gradient(135deg, #f8d7da 0%, #e17055 100%);">
                                    <div class="card-body py-3">
                                        <i class="bi bi-key text-danger fs-4 mb-2"></i>
                                        <h5 class="fw-bold mb-1" id="passwords-generadas">0</h5>
                                        <small class="text-muted">Contraseñas</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Tickets -->
                        <div class="section-card">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold text-primary mb-0">
                                        <i class="bi bi-ticket-detailed me-2"></i>Mis Solicitudes de Soporte
                                    </h6>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary btn-sm" onclick="cargarMisTickets()">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                                        </button>
                                        <button class="btn btn-custom-primary btn-sm" onclick="nuevaSolicitudSoporte()">
                                            <i class="bi bi-plus-circle me-1"></i>Nueva Solicitud
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-3">
                                <!-- Loading -->
                                <div id="loading-soporte" class="text-center py-4" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="text-muted mt-2 mb-0">Cargando mis solicitudes...</p>
                                </div>

                                <!-- Sin tickets -->
                                <div id="sin-tickets" class="text-center py-5" style="display: none;">
                                    <i class="bi bi-inbox text-muted fs-1 mb-3"></i>
                                    <h6 class="text-muted mb-2">No tienes solicitudes de soporte</h6>
                                    <p class="text-muted small mb-3">Cuando necesites ayuda, puedes crear una nueva solicitud</p>
                                    <button class="btn btn-custom-primary btn-sm rounded-pill" onclick="nuevaSolicitudSoporte()">
                                        <i class="bi bi-plus-circle me-1"></i>Crear Primera Solicitud
                                    </button>
                                </div>

                                <!-- Lista de tickets -->
                                <div id="lista-mis-tickets">
                                    <!-- Los tickets se cargarán aquí dinámicamente -->
                                </div>

                                <!-- Botón Ver Más -->
                                <div id="boton-ver-mas" class="text-center mt-3" style="display: none;">
                                    <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="toggleMostrarTodos()">
                                        <i class="bi bi-chevron-down me-1" id="icon-toggle"></i>
                                        <span id="texto-toggle">Ver Todas las Solicitudes</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== OTRAS SECCIONES (Placeholder) ========== -->
                    <div id="seccion-reservas" class="content-section" style="display: none;">
                        <!-- Filtros de Búsqueda -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="section-card">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3"><i class="bi bi-filter me-2"></i>Filtros de Búsqueda</h6>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Fecha</label>
                                                <input type="date" class="form-control" id="filtro-fecha">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Hora Inicio</label>
                                                <input type="time" class="form-control" id="filtro-hora-inicio">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Duración</label>
                                                <select class="form-select" id="filtro-duracion">
                                                    <option value="">Cualquier duración</option>
                                                    <option value="1">1 hora</option>
                                                    <option value="2">2 horas</option>
                                                    <option value="3">3 horas</option>
                                                    <option value="4">4+ horas</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Capacidad</label>
                                                <select class="form-select" id="filtro-capacidad">
                                                    <option value="">Cualquier capacidad</option>
                                                    <option value="10">Hasta 10 personas</option>
                                                    <option value="20">Hasta 20 personas</option>
                                                    <option value="30">Hasta 30 personas</option>
                                                    <option value="50">50+ personas</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button class="btn btn-primary" onclick="buscarSalas()">
                                                <i class="bi bi-search me-2"></i>Buscar Salas
                                            </button>
                                            <button class="btn btn-outline-secondary" onclick="limpiarFiltros()">
                                                <i class="bi bi-arrow-clockwise me-2"></i>Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resultados de Salas -->
                        <div class="row">
                            <div class="col-12">
                                <div class="section-card">
                                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                        <h6 class="fw-bold text-primary mb-0">
                                            <i class="bi bi-door-open me-2"></i>Salas Disponibles
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="lista-salas-disponibles">
                                            <!-- Se cargarán las salas dinámicamente -->
                                            <div class="text-center text-muted py-5">
                                                <i class="bi bi-search fs-1 mb-3"></i>
                                                <p>Selecciona los filtros y busca salas disponibles</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="seccion-nueva-reserva" class="content-section" style="display: none;">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="section-card">
                                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                        <h6 class="fw-bold text-primary mb-0">
                                            <i class="bi bi-plus-circle me-2"></i>Crear Nueva Reserva
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form id="form-nueva-reserva">
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Fecha de Reserva</label>
                                                    <input type="date" class="form-control" id="fecha-reserva" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Sala</label>
                                                    <select class="form-select" id="sala-reserva" required>
                                                        <option value="">Selecciona una sala</option>
                                                        <option value="1">Sala A - Capacidad 20</option>
                                                        <option value="2">Sala B - Capacidad 30</option>
                                                        <option value="3">Sala C - Capacidad 50</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Hora Inicio</label>
                                                    <input type="time" class="form-control" id="hora-inicio" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Hora Fin</label>
                                                    <input type="time" class="form-control" id="hora-fin" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Propósito de la Reserva</label>
                                                <textarea class="form-control" id="proposito-reserva" rows="3" 
                                                          placeholder="Describe el propósito de la reserva..." required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Número de Asistentes</label>
                                                <input type="number" class="form-control" id="num-asistentes" min="1" max="100" required>
                                            </div>
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="bi bi-calendar-plus me-2"></i>Crear Reserva
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="section-card">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Información</h6>
                                        <div class="small text-muted">
                                            <p><strong>Horarios disponibles:</strong><br>
                                            Lunes a Viernes: 7:00 AM - 9:00 PM<br>
                                            Sábados: 8:00 AM - 6:00 PM</p>
                                            
                                            <p><strong>Tiempo mínimo:</strong> 1 hora<br>
                                            <strong>Tiempo máximo:</strong> 8 horas</p>
                                            
                                            <p><strong>Cancelación:</strong><br>
                                            Puedes cancelar hasta 2 horas antes del inicio.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="seccion-mis-qr" class="content-section" style="display: none;">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="section-card">
                                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="fw-bold text-primary mb-0">
                                                <i class="bi bi-qr-code me-2"></i>Mis Códigos QR de Reservas
                                            </h6>
                                            <button class="btn btn-outline-primary btn-sm" onclick="actualizarQRs()">
                                                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="lista-codigos-qr">
                                            <!-- Se cargarán los QRs dinámicamente -->
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="card border-primary">
                                                        <div class="card-body text-center">
                                                            <div class="mb-3">
                                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=RESERVA-001" 
                                                                     alt="QR Code" class="img-fluid">
                                                            </div>
                                                            <h6 class="fw-bold">Sala A - Hoy 14:00</h6>
                                                            <p class="text-muted small">Reserva #001</p>
                                                            <div class="d-grid gap-2">
                                                                <button class="btn btn-primary btn-sm">
                                                                    <i class="bi bi-download me-1"></i>Descargar
                                                                </button>
                                                                <button class="btn btn-outline-primary btn-sm">
                                                                    <i class="bi bi-share me-1"></i>Compartir
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card border-success">
                                                        <div class="card-body text-center">
                                                            <div class="mb-3">
                                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=RESERVA-002" 
                                                                     alt="QR Code" class="img-fluid">
                                                            </div>
                                                            <h6 class="fw-bold">Sala B - Mañana 10:00</h6>
                                                            <p class="text-muted small">Reserva #002</p>
                                                            <div class="d-grid gap-2">
                                                                <button class="btn btn-success btn-sm">
                                                                    <i class="bi bi-download me-1"></i>Descargar
                                                                </button>
                                                                <button class="btn btn-outline-success btn-sm">
                                                                    <i class="bi bi-share me-1"></i>Compartir
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información sobre QR -->
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info border-0 rounded-4">
                                    <h6 class="fw-bold"><i class="bi bi-lightbulb me-2"></i>¿Cómo usar los códigos QR?</h6>
                                    <ul class="mb-0">
                                        <li>Escanea el código QR al llegar a la sala reservada</li>
                                        <li>El código confirma tu asistencia automáticamente</li>
                                        <li>Puedes descargar o compartir los códigos</li>
                                        <li>Los códigos expiran después de la reserva</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="seccion-capacitaciones" class="content-section" style="display: none;">
                        <!-- Estadísticas de Capacitaciones -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card border-0 h-100" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                                    <div class="card-body text-center p-3">
                                        <i class="bi bi-mortarboard text-success fs-3 mb-2"></i>
                                        <h5 class="fw-bold mb-1" id="total-capacitaciones">0</h5>
                                        <small class="text-success">Total Disponibles</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 h-100" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
                                    <div class="card-body text-center p-3">
                                        <i class="bi bi-file-earmark-pdf text-warning fs-3 mb-2"></i>
                                        <h5 class="fw-bold mb-1" id="total-pdfs">0</h5>
                                        <small class="text-warning">Documentos PDF</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 h-100" style="background: linear-gradient(135deg, #cce5ff 0%, #74b9ff 100%);">
                                    <div class="card-body text-center p-3">
                                        <i class="bi bi-file-earmark-word text-primary fs-3 mb-2"></i>
                                        <h5 class="fw-bold mb-1" id="total-words">0</h5>
                                        <small class="text-primary">Documentos Word</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Buscador -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="bi bi-search text-muted"></i>
                                                    </span>
                                                    <input type="text" class="form-control border-start-0" 
                                                           id="buscar-capacitaciones" 
                                                           placeholder="Buscar por título o descripción...">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <select class="form-select" id="filtro-tipo-capacitaciones">
                                                    <option value="">Todos los tipos</option>
                                                    <option value="PDF">Solo PDF</option>
                                                    <option value="WORD">Solo Word</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Capacitaciones -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                        <h6 class="fw-bold text-primary mb-0">
                                            <i class="bi bi-collection me-2"></i>Material de Capacitación Disponible
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Loading -->
                                        <div id="loading-capacitaciones" class="text-center py-4" style="display: none;">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <p class="text-muted mt-2 mb-0">Cargando capacitaciones...</p>
                                        </div>

                                        <!-- Sin capacitaciones -->
                                        <div id="sin-capacitaciones" class="text-center py-5" style="display: none;">
                                            <i class="bi bi-inbox text-muted fs-1 mb-3"></i>
                                            <h6 class="text-muted mb-2">No hay capacitaciones disponibles</h6>
                                            <p class="text-muted small mb-0">Los materiales de capacitación aparecerán aquí cuando estén disponibles</p>
                                        </div>

                                        <!-- Lista de capacitaciones -->
                                        <div id="lista-capacitaciones">
                                            <!-- Las capacitaciones se cargarán aquí dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== SECCIÓN: MANUAL DE USUARIO ========== -->
                    <div id="seccion-manual" class="content-section" style="display: none;">
                        <div class="container-fluid p-4">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h2 class="text-dark mb-4">
                                        <i class="bi bi-book me-2"></i>Manuales de Usuario
                                    </h2>
                                </div>
                            </div>

                            <!-- Estadísticas -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card bg-primary text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="totalManualesUser">0</h4>
                                                    <small>Total Manuales</small>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="bi bi-book fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card bg-success text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="manualesPDFUser">0</h4>
                                                    <small>Archivos PDF</small>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="bi bi-file-pdf fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card bg-info text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="manualesWordUser">0</h4>
                                                    <small>Documentos Word</small>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="bi bi-file-word fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card bg-warning text-white h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h4 class="mb-0" id="manualesGeneralUser">0</h4>
                                                    <small>Categoría General</small>
                                                </div>
                                                <div class="align-self-center">
                                                    <i class="bi bi-globe fa-2x"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtros de búsqueda -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-filter me-2"></i>Búsqueda y Filtros</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="searchManuales" class="form-label">Buscar manuales:</label>
                                            <input type="text" class="form-control" id="searchManuales" placeholder="Buscar por título o descripción...">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="filterCategoria" class="form-label">Categoría:</label>
                                            <select class="form-select" id="filterCategoria">
                                                <option value="">Todas las categorías</option>
                                                <option value="usuario">Usuario</option>
                                                <option value="general">General</option>
                                                <option value="tecnico">Técnico</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="filterTipo" class="form-label">Tipo de archivo:</label>
                                            <select class="form-select" id="filterTipo">
                                                <option value="">Todos los tipos</option>
                                                <option value="PDF">PDF</option>
                                                <option value="WORD">Word</option>
                                                <option value="HTML">HTML</option>
                                                <option value="TEXTO">Texto</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de manuales -->
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="bi bi-list me-2"></i>Manuales Disponibles</h5>
                                </div>
                                <div class="card-body">
                                    <div id="loadingManuales" class="text-center p-4 d-none">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <p class="mt-2">Cargando manuales...</p>
                                    </div>
                                    <div id="manualesContainer" class="row">
                                        <!-- Los manuales se cargarán aquí dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- ========== MODAL: Nueva Solicitud de Soporte ========== -->
    <div class="modal fade" id="modalNuevaSolicitud" tabindex="-1" aria-labelledby="modalNuevaSolicitudLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header bg-primary bg-gradient text-white border-0 rounded-top-4">
                    <h5 class="modal-title fw-bold" id="modalNuevaSolicitudLabel">
                        <i class="bi bi-headset me-2"></i>Nueva Solicitud de Soporte
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formNuevaSolicitud">
                        <div class="mb-4">
                            <label for="tipoSolicitud" class="form-label fw-semibold">
                                <i class="bi bi-tag me-1"></i>Tipo de Solicitud
                            </label>
                            <select class="form-select rounded-3" id="tipoSolicitud" required>
                                <option value="">Selecciona el tipo de ayuda que necesitas</option>
                                <option value="password_recovery">🔑 Recuperar Contraseña</option>
                                <option value="technical_issue">⚙️ Problema Técnico</option>
                                <option value="general_support">💬 Consulta General</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="emailSolicitud" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1"></i>Tu Email
                            </label>
                            <div class="input-group">
                                <input type="email" class="form-control rounded-start" id="emailSolicitud" 
                                       value="<?= htmlspecialchars($email_usuario) ?>" required>
                                <button class="btn btn-outline-secondary" type="button" id="btn-editar-email" 
                                        onclick="toggleEditEmail()">
                                    <i class="bi bi-pencil me-1"></i>Cambiar
                                </button>
                            </div>
                            <div class="form-text" id="email-help">
                                <i class="bi bi-info-circle me-1"></i>
                                Este es tu email registrado. Haz clic en "Cambiar" si quieres usar otro email para la respuesta.
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="motivoSolicitud" class="form-label fw-semibold">
                                <i class="bi bi-chat-dots me-1"></i>Describe tu consulta o problema
                            </label>
                            <textarea class="form-control rounded-3" id="motivoSolicitud" rows="4" 
                                      placeholder="Explica detalladamente tu solicitud..." required></textarea>
                            <div class="form-text">Mínimo 10 caracteres. Mientras más detalles proporciones, mejor podremos ayudarte.</div>
                        </div>
                        <div class="mb-4">
                            <label for="prioridadSolicitud" class="form-label fw-semibold">
                                <i class="bi bi-exclamation-triangle me-1"></i>Prioridad
                            </label>
                            <select class="form-select rounded-3" id="prioridadSolicitud">
                                <option value="media">📋 Normal - Respuesta en 24-48 horas</option>
                                <option value="alta">⚡ Alta - Necesito respuesta pronto</option>
                                <option value="urgente">🚨 Urgente - Problema crítico</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-custom-primary rounded-pill" onclick="enviarSolicitudSoporte()">
                        <i class="bi bi-send me-1"></i>Enviar Solicitud
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== MODAL: Detalles del Ticket ========== -->
    <div class="modal fade" id="modalDetallesTicket" tabindex="-1" aria-labelledby="modalDetallesTicketLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header bg-primary bg-gradient text-white border-0 rounded-top-4" id="headerModalTicket">
                    <h5 class="modal-title fw-bold" id="modalDetallesTicketLabel">
                        <i class="bi bi-ticket-detailed me-2"></i>Detalles del Ticket
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4" id="contenidoModalTicket">
                    <!-- Se llenará dinámicamente -->
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        // ========== VARIABLES GLOBALES OPTIMIZADAS ==========
        const CONFIG = {
            emailUsuario: '<?= $email_usuario ?>',
            API_SOPORTE: '../../Backend/api/SoporteTecnico/Metodos-soporte.php',
            API_CAPACITACIONES: '../../Backend/CRUD-admin/crud-capacitaciones.php',
            API_BASE: '../../Backend',  // ← AGREGAR ESTA LÍNEA
            INACTIVIDAD_TIMEOUT: 30 * 60 * 1000,
            ACTUALIZACION_INTERVAL: 30000
        };
        
        let misTickets = [];
        let mostrandoTodos = false;
        let inactivityTimer;
        let updateInterval;

        // ========== INICIALIZACIÓN OPTIMIZADA ==========
        document.addEventListener('DOMContentLoaded', function() {
            cargarDashboard();
            mostrarSeccion('resumen');
            cargarMisTickets();
            initEventListeners();
        });

        // ========== GESTIÓN DE EVENT LISTENERS ==========
        function initEventListeners() {
            // Notificaciones
            document.getElementById('btn-notificaciones').addEventListener('click', function() {
                if (misTickets.some(t => t.respuesta_admin)) {
                    mostrarSeccion('soporte');
                }
            });

            // Actualización automática optimizada
            updateInterval = setInterval(() => {
                if (document.getElementById('seccion-soporte').style.display !== 'none') {
                    cargarMisTickets();
                }
            }, CONFIG.ACTUALIZACION_INTERVAL);
        }

        // ========== NAVEGACIÓN OPTIMIZADA ==========
        function mostrarSeccion(seccion) {
            // Ocultar todas las secciones de una vez
            document.querySelectorAll('.content-section').forEach(s => s.style.display = 'none');
            
            // Mostrar la sección seleccionada
            const seccionElement = document.getElementById(`seccion-${seccion}`);
            if (seccionElement) {
                seccionElement.style.display = 'block';
            }
            
            // Actualizar navegación activa con delegación de eventos optimizada
            document.querySelectorAll('.nav-link-custom').forEach(link => {
                link.classList.remove('active');
            });
            
            const activeLink = document.querySelector(`[href="#${seccion}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
            
            // Actualizar breadcrumb y acciones del header
            updatePageInfo(seccion);
            loadSectionData(seccion);
        }

        // ========== FUNCIÓN AUXILIAR PARA ACTUALIZAR INFO DE PÁGINA ==========
        function updatePageInfo(seccion) {
            const breadcrumbTextos = {
                'resumen': 'Resumen',
                'reservas': 'Revisar Salas',
                'nueva-reserva': 'Nueva Reserva',
                'mis-qr': 'Mis Códigos QR',
                'soporte': 'Soporte Técnico',
                'capacitaciones': 'Capacitaciones',
                'manual': 'Manual de Usuario'
            };
            
            document.getElementById('breadcrumb-actual').textContent = breadcrumbTextos[seccion] || 'Sección';
            actualizarHeaderActions(seccion);
        }

        // ========== CARGA DE DATOS ESPECÍFICOS POR SECCIÓN ==========
        function loadSectionData(seccion) {
            const loadHandlers = {
                'soporte': cargarMisTickets,
                'reservas': () => console.log('Cargar salas disponibles'),
                'mis-qr': () => console.log('Cargar códigos QR'),
                'nueva-reserva': () => console.log('Configurar formulario'),
                'capacitaciones': cargarCapacitaciones, // ← CAMBIAR ESTA LÍNEA
                'manual': loadManualesUsuario  // ← AGREGAR ESTA LÍNEA
            };
            
            const handler = loadHandlers[seccion];
            if (handler) handler();
        }

        // ========== HEADER ACTIONS OPTIMIZADO ==========
        function actualizarHeaderActions(seccion) {
            const headerActions = document.getElementById('header-actions');
            
            const actionTemplates = {
                'resumen': `<button class="btn btn-outline-primary" onclick="cargarDashboard()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Actualizar</button>`,
                'reservas': `<button class="btn btn-outline-primary" onclick="loadSectionData('reservas')">
                    <i class="bi bi-arrow-clockwise me-2"></i>Actualizar</button>
                    <button class="btn btn-primary" onclick="mostrarSeccion('nueva-reserva')">
                    <i class="bi bi-plus-circle me-2"></i>Nueva Reserva</button>`,
                'nueva-reserva': `<button class="btn btn-outline-secondary" onclick="limpiarFormulario()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Limpiar</button>`,
                'mis-qr': `<button class="btn btn-outline-primary" onclick="loadSectionData('mis-qr')">
                    <i class="bi bi-arrow-clockwise me-2"></i>Actualizar</button>
                    <button class="btn btn-primary" onclick="descargarTodosQR()">
                    <i class="bi bi-download me-2"></i>Descargar Todos</button>`,
                'soporte': `<button class="btn btn-outline-primary" onclick="cargarMisTickets()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Actualizar</button>
                    <button class="btn btn-primary" onclick="nuevaSolicitudSoporte()">
                    <i class="bi bi-plus-circle me-2"></i>Nueva Solicitud</button>`,
                'capacitaciones': `<button class="btn btn-outline-primary" onclick="cargarCapacitaciones()">
                    <i class="bi bi-arrow-clockwise me-2"></i>Actualizar</button>`,
                'manual': `<button class="btn btn-outline-primary" onclick="descargarManual()">
                    <i class="bi bi-download me-2"></i>Descargar PDF</button>`
            };
            
            headerActions.innerHTML = actionTemplates[seccion] || actionTemplates['resumen'];
        }

        // ========== CARGAR DASHBOARD OPTIMIZADO ==========
        async function cargarDashboard() {
            try {
                // Simulación optimizada de estadísticas
                const stats = {
                    'total-reservas-activas': '3',
                    'salas-disponibles': '12',
                    'tickets-pendientes': '1',
                    'qr-generados': '3',
                    'stat-reservas': '3',
                    'stat-tickets': '1'
                };
                
                // Actualización batch de elementos
                Object.entries(stats).forEach(([id, value]) => {
                    const element = document.getElementById(id);
                    if (element) element.textContent = value;
                });
                
                cargarMisReservas();
                cargarNotificaciones();
                
            } catch (error) {
                console.error('Error al cargar dashboard:', error);
            }
        }

        // ========== FUNCIONES DE CARGA OPTIMIZADAS ==========
        function cargarMisReservas() {
            const container = document.getElementById('mis-reservas-container');
            if (!container) return;
            
            // Template optimizado con menos HTML
            container.innerHTML = `
                <div class="card border-0 mb-3 shadow-sm rounded-3 card-hover" style="border-left: 4px solid #198754 !important;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="fw-bold text-primary mb-2">
                                    <i class="bi bi-door-closed me-2"></i>Sala 101 - Matemáticas
                                </h6>
                                <div class="d-flex flex-wrap gap-3">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-event text-primary me-1"></i>Hoy, 2024-03-20
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-clock text-primary me-1"></i>
                                        09:00 - 11:00
                                    </small>
                                    <span class="badge bg-success">Confirmada</span>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex flex-column gap-2">
                                    <button class="btn btn-primary btn-sm rounded-pill">
                                        <i class="bi bi-qr-code me-1"></i>Ver QR
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm rounded-pill">
                                        <i class="bi bi-x-circle me-1"></i>Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.innerHTML = reservasEjemplo;
        }

        // ========== CARGAR NOTIFICACIONES ==========
        function cargarNotificaciones() {
            const container = document.getElementById('notificaciones-container');
            
            const notificacionesEjemplo = `
                <div class="alert alert-success border-0 rounded-4 d-flex align-items-start p-3 mb-3" 
                     style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 40px; height: 40px; min-width: 40px;">
                        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-success mb-1">Reserva Confirmada</h6>
                        <p class="mb-1 text-dark small">Su reserva para la Sala 101 ha sido confirmada</p>
                        <small class="text-muted">
                            <i class="bi bi-calendar-event me-1"></i>Hace 2 horas
                        </small>
                    </div>
                </div>
            `;
            
            container.innerHTML = notificacionesEjemplo;
        }

        // ========== FUNCIONES DE SOPORTE (MANTENER TODAS LAS EXISTENTES) ==========
        
        async function cargarMisTickets() {
            const loadingElement = document.getElementById('loading-soporte');
            const sinTicketsElement = document.getElementById('sin-tickets');
            const listaElement = document.getElementById('lista-mis-tickets');
            
            try {
                loadingElement.style.display = 'block';
                sinTicketsElement.style.display = 'none';
                listaElement.style.display = 'none';

                const response = await fetch(CONFIG.API_SOPORTE, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    }
                });
                const data = await response.json();

                if (data.success && data.data) {
                    misTickets = data.data.filter(ticket => ticket.email_solicitante === CONFIG.emailUsuario);
                    
                    if (misTickets.length === 0) {
                        sinTicketsElement.style.display = 'block';
                    } else {
                        renderizarMisTickets(misTickets);
                        actualizarEstadisticasSoporte(misTickets);
                        verificarRespuestasPendientes(misTickets);
                    }
                } else {
                    console.error('Error en la respuesta:', data);
                    sinTicketsElement.style.display = 'block';
                }

            } catch (error) {
                console.error('Error al cargar tickets:', error);
                sinTicketsElement.style.display = 'block';
            } finally {
                loadingElement.style.display = 'none';
            }
        }

        // ========== VERIFICAR RESPUESTAS PENDIENTES ==========
        function verificarRespuestasPendientes(tickets) {
            const ticketsConRespuesta = tickets.filter(t => t.respuesta_admin && !t.respuesta_leida);
            
            if (ticketsConRespuesta.length > 0) {
                // Mostrar badge en navbar
                const badgeNavbar = document.getElementById('badge-respuestas-nuevas');
                const countNavbar = document.getElementById('count-respuestas-nuevas');
                const badgeSidebar = document.getElementById('sidebar-badge-respuestas');
                
                badgeNavbar.style.display = 'flex';
                countNavbar.textContent = ticketsConRespuesta.length;
                badgeSidebar.style.display = 'inline';
                badgeSidebar.textContent = ticketsConRespuesta.length;
                
                // Mostrar sección de respuestas pendientes
                const seccionRespuestas = document.getElementById('seccion-respuestas-pendientes');
                const listaRespuestas = document.getElementById('lista-respuestas-pendientes');
                
                listaRespuestas.innerHTML = ticketsConRespuesta.map(ticket => `
                    <div class="mb-2">
                        <strong>Ticket #${ticket.id}:</strong> 
                        <span class="text-truncate d-inline-block" style="max-width: 200px;">
                            ${ticket.respuesta_admin.substring(0, 50)}...
                        </span>
                        <button class="btn btn-sm btn-outline-success ms-2" onclick="verDetallesTicket(${ticket.id})">
                            Ver
                        </button>
                    </div>
                `).join('');
                
                seccionRespuestas.style.display = 'block';
            } else {
                // Ocultar badges y sección
                document.getElementById('badge-respuestas-nuevas').style.display = 'none';
                document.getElementById('sidebar-badge-respuestas').style.display = 'none';
                document.getElementById('seccion-respuestas-pendientes').style.display = 'none';
            }
        }

        // ========== RENDERIZAR TICKETS ==========
        function renderizarMisTickets(tickets) {
            const container = document.getElementById('lista-mis-tickets');
            
            if (tickets.length === 0) {
                document.getElementById('sin-tickets').style.display = 'block';
                return;
            }

            const hayMasTickets = tickets.length > 3;
            const ticketsAMostrar = mostrandoTodos ? tickets : tickets.slice(0, 3);

            const infoTotal = hayMasTickets && !mostrandoTodos ? 
                `<div class="alert alert-info alert-sm mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    Mostrando las 3 más recientes de <strong>${tickets.length} solicitudes</strong>
                </div>` : '';

            container.innerHTML = infoTotal + ticketsAMostrar.map(ticket => `
                <div class="card border-0 mb-3 shadow-sm rounded-3 ticket-card ${ticket.respuesta_admin ? 'border-success' : ''}" 
                     style="border-left: 4px solid ${getColorEstado(ticket.estado)} !important; 
                            ${ticket.respuesta_admin ? 'box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2) !important;' : ''}">
                    <div class="card-body p-3">
                        <!-- Alerta de respuesta en la parte superior -->
                        ${ticket.respuesta_admin ? `
                            <div class="alert alert-success py-2 px-3 mb-3 border-0 rounded-3" 
                                 style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-chat-quote-fill text-success fs-5 me-2"></i>
                                    <div class="flex-grow-1">
                                        <small class="fw-bold text-success">¡El administrador ha respondido!</small>
                                        <div class="text-success small text-truncate" style="max-width: 300px;">
                                            "${ticket.respuesta_admin.substring(0, 80)}${ticket.respuesta_admin.length > 80 ? '...' : ''}"
                                        </div>
                                    </div>
                                    <button class="btn btn-success btn-sm rounded-pill" onclick="verDetallesTicket(${ticket.id})">
                                        <i class="bi bi-eye me-1"></i>Leer Completa
                                    </button>
                                </div>
                            </div>
                        ` : ''}

                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-start mb-2">
                                    <div class="me-2">
                                        ${getIconoTipo(ticket.tipo)}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-0 fw-semibold">
                                                ${ticket.asunto || 'Solicitud de Soporte'}
                                                ${ticket.respuesta_admin ? '<i class="bi bi-chat-quote-fill text-success ms-2" title="Respondido"></i>' : ''}
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-hash"></i>${ticket.id}
                                            </small>
                                        </div>
                                        <p class="text-muted small mb-2 text-truncate" style="max-width: 300px;">
                                            ${ticket.motivo_solicitud}
                                        </p>
                                        <div class="d-flex gap-2 flex-wrap">
                                            ${getBadgeEstado(ticket.estado)}
                                            ${getBadgePrioridad(ticket.prioridad)}
                                            ${ticket.respuesta_admin ? '<span class="badge bg-success"><i class="bi bi-reply me-1"></i>Respondido</span>' : ''}
                                            <small class="text-muted align-self-center">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                ${formatearFecha(ticket.fecha_creacion)}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex flex-column gap-2">
                                    ${ticket.nueva_password_generada ? `
                                        <button class="btn btn-success btn-sm rounded-pill" onclick="mostrarPassword('${ticket.nueva_password_generada}')">
                                            <i class="bi bi-key me-1"></i>Ver Contraseña
                                        </button>
                                    ` : ''}
                                    ${ticket.respuesta_admin ? `
                                        <button class="btn btn-success btn-sm rounded-pill" onclick="verDetallesTicket(${ticket.id})">
                                            <i class="bi bi-chat-quote me-1"></i>Ver Respuesta
                                        </button>
                                    ` : `
                                        <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="verDetallesTicket(${ticket.id})">
                                            <i class="bi bi-eye me-1"></i>Ver Detalles
                                        </button>
                                    `}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            if (hayMasTickets) {
                document.getElementById('boton-ver-mas').style.display = 'block';
                actualizarBotonToggle();
            } else {
                document.getElementById('boton-ver-mas').style.display = 'none';
            }

            document.getElementById('lista-mis-tickets').style.display = 'block';
        }

        // ========== TOGGLE MOSTRAR TODOS/POCOS ==========
        function toggleMostrarTodos() {
            mostrandoTodos = !mostrandoTodos;
            renderizarMisTickets(misTickets);
        }

        function actualizarBotonToggle() {
            const icon = document.getElementById('icon-toggle');
            const texto = document.getElementById('texto-toggle');
            
            if (mostrandoTodos) {
                icon.className = 'bi bi-chevron-up me-1';
                texto.textContent = 'Mostrar Solo Recientes';
            } else {
                icon.className = 'bi bi-chevron-down me-1';
                texto.textContent = `Ver Todas las Solicitudes (${misTickets.length})`;
            }
        }

        // ========== NUEVA SOLICITUD ==========
        function nuevaSolicitudSoporte() {
            // Establecer email del usuario al abrir el modal
            const emailUsuarioActual = JSON.parse(localStorage.getItem('userData') || '{}').email || CONFIG.emailUsuario;
            document.getElementById('emailSolicitud').value = emailUsuarioActual;
            
            // Resetear el estado del campo email
            const emailInput = document.getElementById('emailSolicitud');
            const btnEditar = document.getElementById('btn-editar-email');
            const helpText = document.getElementById('email-help');
            
            if (btnEditar) { // Solo si usas la opción 2
                emailInput.setAttribute('readonly', true);
                btnEditar.innerHTML = '<i class="bi bi-pencil me-1"></i>Cambiar';
                btnEditar.className = 'btn btn-outline-secondary';
                helpText.innerHTML = '<i class="bi bi-info-circle me-1"></i>Este es tu email registrado. Haz clic en "Cambiar" si quieres usar otro email para la respuesta.';
            }
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('modalNuevaSolicitud'));
            modal.show();
        }

        function abrirSoporteTecnico() {
            nuevaSolicitudSoporte();
        }

        async function enviarSolicitudSoporte() {
            try {
                const solicitud = {
                    email: CONFIG.emailUsuario,
                    tipo: document.getElementById('tipoSolicitud').value,
                    motivo: document.getElementById('motivoSolicitud').value,
                    prioridad: document.getElementById('prioridadSolicitud').value
                };

                console.log('📤 Enviando solicitud:', solicitud);

                const response = await fetch(CONFIG.API_SOPORTE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('token')}`
                    },
                    body: JSON.stringify(solicitud)
                });

                const data = await response.json();
                console.log('✅ Respuesta del envío:', data);

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Solicitud Enviada!',
                        html: `
                            <p>Tu solicitud de soporte ha sido enviada exitosamente.</p>
                            <p><strong>Ticket #${data.ticket_id}</strong></p>
                            <p class="text-muted">Te contactaremos pronto a tu email.</p>
                        `,
                        confirmButtonText: 'Entendido'
                    });

                    bootstrap.Modal.getInstance(document.getElementById('modalNuevaSolicitud')).hide();
                    document.getElementById('formNuevaSolicitud').reset();
                    
                    setTimeout(() => {
                        cargarMisTickets();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Error al enviar solicitud');
                }

            } catch (error) {
                console.error('❌ Error al enviar:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo enviar tu solicitud: ' + error.message
                });
            }
        }

        // ========== ACTUALIZAR ESTADÍSTICAS DE SOPORTE ==========
        function actualizarEstadisticasSoporte(tickets) {
            const stats = {
                pendientes: tickets.filter(t => t.estado === 'pendiente').length,
                proceso: tickets.filter(t => t.estado === 'en_proceso').length,
                resueltos: tickets.filter(t => t.estado === 'resuelto').length,
                passwords: tickets.filter(t => t.nueva_password_generada).length
            };

            document.getElementById('mis-tickets-pendientes').textContent = stats.pendientes;
            document.getElementById('mis-tickets-proceso').textContent = stats.proceso;
            document.getElementById('mis-tickets-resueltos').textContent = stats.resueltos;
            document.getElementById('passwords-generadas').textContent = stats.passwords;
        }

        // ========== VER DETALLES DEL TICKET ==========
        function verDetallesTicket(ticketId) {
            const ticket = misTickets.find(t => t.id === ticketId);
            if (!ticket) return;

            const headerColor = ticket.respuesta_admin ? '#198754' : getColorEstado(ticket.estado);
            document.getElementById('headerModalTicket').style.background = `linear-gradient(135deg, ${headerColor}, ${headerColor}aa)`;

            document.getElementById('contenidoModalTicket').innerHTML = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-info-circle me-2"></i>Información del Ticket
                        </h6>
                        <div class="mb-3">
                            <strong>Ticket ID:</strong> #${ticket.id}
                        </div>
                        <div class="mb-3">
                            <strong>Tipo:</strong> ${formatearTipo(ticket.tipo)}
                        </div>
                        <div class="mb-3">
                            <strong>Estado:</strong> ${getBadgeEstado(ticket.estado)}
                        </div>
                        <div class="mb-3">
                            <strong>Prioridad:</strong> ${getBadgePrioridad(ticket.prioridad)}
                        </div>
                        <div class="mb-3">
                            <strong>Fecha:</strong> ${formatearFecha(ticket.fecha_creacion)}
                        </div>
                        ${ticket.fecha_respuesta ? `
                            <div class="mb-3">
                                <strong>Fecha respuesta:</strong> ${formatearFecha(ticket.fecha_respuesta)}
                            </div>
                        ` : ''}
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-chat-dots me-2"></i>Tu Consulta
                        </h6>
                        <div class="bg-light rounded-3 p-3 mb-3">
                            ${ticket.motivo_solicitud}
                        </div>
                        
                        ${ticket.respuesta_admin ? `
                            <div class="alert alert-success border-0 rounded-4 shadow-sm mb-3">
                                <h6 class="fw-bold text-success mb-3">
                                    <i class="bi bi-chat-quote-fill me-2"></i>Respuesta del Administrador
                                </h6>
                                <div class="bg-white rounded-3 p-3 border border-success border-opacity-25">
                                    ${ticket.respuesta_admin}
                                </div>
                            </div>
                        ` : `
                            <div class="alert alert-info border-0 rounded-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock-history text-info fs-5 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold text-info mb-1">En Espera</h6>
                                        <small class="text-info">El administrador revisará tu solicitud pronto</small>
                                    </div>
                                </div>
                            </div>
                        `}

                        ${ticket.nueva_password_generada ? `
                            <div class="alert alert-warning border-0 rounded-4 shadow-sm">
                                <h6 class="fw-bold text-warning mb-3">
                                    <i class="bi bi-key me-2"></i>Contraseña Temporal Generada
                                </h6>
                                <div class="bg-white rounded-3 p-3 border border-warning border-opacity-25">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <code class="fs-5 fw-bold text-danger">${ticket.nueva_password_generada}</code>
                                        <button class="btn btn-warning btn-sm" onclick="copiarPassword('${ticket.nueva_password_generada}')">
                                            <i class="bi bi-clipboard me-1"></i>Copiar
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Usa esta contraseña temporal para ingresar al sistema y cámbiala inmediatamente.
                                    </small>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;

            new bootstrap.Modal(document.getElementById('modalDetallesTicket')).show();
        }

        // ========== FUNCIONES AUXILIARES ==========
        function getColorEstado(estado) {
            const colores = {
                'pendiente': '#ffc107',
                'en_proceso': '#0d6efd',
                'resuelto': '#198754',
                'cerrado': '#6c757d'
            };
            return colores[estado] || '#6c757d';
        }

        function getBadgeEstado(estado) {
            const badges = {
                'pendiente': '<span class="badge bg-warning"><i class="bi bi-clock-history me-1"></i>Pendiente</span>',
                'en_proceso': '<span class="badge bg-primary"><i class="bi bi-gear me-1"></i>En Proceso</span>',
                'resuelto': '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Resuelto</span>',
                'cerrado': '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Cerrado</span>'
            };
            return badges[estado] || '<span class="badge bg-secondary">Desconocido</span>';
        }

        function getBadgePrioridad(prioridad) {
            const badges = {
                'baja': '<span class="badge bg-light text-dark">Baja</span>',
                'media': '<span class="badge bg-info">Media</span>',
                'alta': '<span class="badge bg-warning">Alta</span>',
                'urgente': '<span class="badge bg-danger">Urgente</span>'
            };
            return badges[prioridad] || '<span class="badge bg-light text-dark">Media</span>';
        }

        function getIconoTipo(tipo) {
            const iconos = {
                'password_recovery': '<div class="bg-warning bg-opacity-10 rounded-circle p-2"><i class="bi bi-key text-warning fs-5"></i></div>',
                'technical_issue': '<div class="bg-danger bg-opacity-10 rounded-circle p-2"><i class="bi bi-tools text-danger fs-5"></i></div>',
                'general_support': '<div class="bg-info bg-opacity-10 rounded-circle p-2"><i class="bi bi-chat-dots text-info fs-5"></i></div>'
            };
            return iconos[tipo] || '<div class="bg-secondary bg-opacity-10 rounded-circle p-2"><i class="bi bi-question text-secondary fs-5"></i></div>';
        }

        function formatearTipo(tipo) {
            const tipos = {
                'password_recovery': '🔑 Recuperar Contraseña',
                'technical_issue': '⚙️ Problema Técnico',
                'general_support': '💬 Consulta General'
            };
            return tipos[tipo] || 'Consulta General';
        }

        function formatearFecha(fechaString) {
            const fecha = new Date(fechaString);
            return fecha.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function mostrarPassword(password) {
            Swal.fire({
                title: 'Contraseña Temporal',
                html: `
                    <div class="p-3">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Esta es tu contraseña temporal
                        </div>
                        <div class="bg-light rounded p-3 mb-3">
                            <code class="fs-4 fw-bold text-danger">${password}</code>
                        </div>
                        <small class="text-muted">
                            Úsala para ingresar al sistema y cámbiala inmediatamente por seguridad.
                        </small>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Copiar Contraseña',
                cancelButtonText: 'Cerrar',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    copiarPassword(password);
                }
            });
        }

        function copiarPassword(password) {
            navigator.clipboard.writeText(password).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Copiado!',
                    text: 'La contraseña ha sido copiada al portapapeles',
                    timer: 2000,
                    showConfirmButton: false
                });
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo copiar la contraseña'
                });
            });
        }

        function verTodasRespuestas() {
            mostrandoTodos = true;
            renderizarMisTickets(misTickets);
            mostrarSeccion('soporte');
        }

        function verTodasSolicitudes() {
            mostrandoTodos = true;
            renderizarMisTickets(misTickets);
        }

        function verTodasReservas() {
            mostrarSeccion('reservas');
        }

        function cerrarSesion() {
            Swal.fire({
                title: '¿Cerrar Sesión?',
                text: 'Se cerrará tu sesión actual',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cerrar sesión',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Cerrando sesión...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        // Obtener datos del usuario
                        const userData = JSON.parse(localStorage.getItem('userData') || '{}');
                        
                        // Llamar al endpoint de logout
                        await fetch('../../Backend/api/logout.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${userData.token || ''}`
                            },
                            body: JSON.stringify({
                                token: userData.token || null,
                                userId: userData.id || null
                            })
                        });

                    } catch (error) {
                        console.error('Error al cerrar sesión:', error);
                        // Continuar con logout local aunque falle el servidor
                    } finally {
                        // Siempre limpiar datos locales
                        localStorage.removeItem('userData');
                        sessionStorage.clear();
                        
                        // Redirigir con parámetro de logout
                        window.location.replace('../../login.php?logout=true');
                    }
                }
            });
        }

        // ========== EVENT LISTENERS ADICIONALES ==========
        document.getElementById('btn-notificaciones').addEventListener('click', function() {
            if (misTickets.some(t => t.respuesta_admin)) {
                mostrarSeccion('soporte');
            }
        });

        // Actualizar tickets cada 30 segundos
        setInterval(() => {
            if (document.getElementById('seccion-soporte').style.display !== 'none') {
                cargarMisTickets();
            }
        }, 30000);

        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                cerrarSesion();
            }, 30 * 60 * 1000); // 30 minutos
        }

        // ========== FUNCIÓN PARA EDITAR EMAIL ==========
        function toggleEditEmail() {
            const emailInput = document.getElementById('emailSolicitud');
            const btnEditar = document.getElementById('btn-editar-email');
            const helpText = document.getElementById('email-help');
            
            if (emailInput.hasAttribute('readonly')) {
                // Habilitar edición
                emailInput.removeAttribute('readonly');
                emailInput.focus();
                emailInput.select();
                btnEditar.innerHTML = '<i class="bi bi-check me-1"></i>Confirmar';
                btnEditar.className = 'btn btn-success';
                helpText.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Modifica el email y haz clic en "Confirmar" para guardarlo.';
            } else {
                // Deshabilitar edición
                emailInput.setAttribute('readonly', true);
                btnEditar.innerHTML = '<i class="bi bi-pencil me-1"></i>Cambiar';
                btnEditar.className = 'btn btn-outline-secondary';
                helpText.innerHTML = '<i class="bi bi-check-circle me-1"></i>Email confirmado. Haz clic en "Cambiar" si necesitas modificarlo.';
            }
        }

        // ========== FUNCIONES DE CAPACITACIONES ==========
        let capacitacionesData = [];
        let capacitacionesFiltradas = [];

        async function cargarCapacitaciones() {
            const elements = {
                loading: document.getElementById('loading-capacitaciones'),
                sinCapacitaciones: document.getElementById('sin-capacitaciones'),
                lista: document.getElementById('lista-capacitaciones')
            };
            
            try {
                if (elements.loading) elements.loading.style.display = 'block';
                if (elements.sinCapacitaciones) elements.sinCapacitaciones.style.display = 'none';
                if (elements.lista) elements.lista.style.display = 'none';

                const response = await fetch(CONFIG.API_CAPACITACIONES);
                const data = await response.json();

                if (Array.isArray(data)) {
                    // Filtrar solo capacitaciones activas
                    capacitacionesData = data.filter(cap => cap.estado == 1);
                    capacitacionesFiltradas = [...capacitacionesData];
                    
                    if (capacitacionesData.length === 0) {
                        if (elements.sinCapacitaciones) elements.sinCapacitaciones.style.display = 'block';
                    } else {
                        renderizarCapacitaciones(capacitacionesFiltradas);
                        actualizarEstadisticasCapacitaciones(capacitacionesData);
                        initBuscadorCapacitaciones();
                    }
                } else {
                    console.error('Error en la respuesta:', data);
                    if (elements.sinCapacitaciones) elements.sinCapacitaciones.style.display = 'block';
                }

            } catch (error) {
                console.error('Error al cargar capacitaciones:', error);
                if (elements.sinCapacitaciones) elements.sinCapacitaciones.style.display = 'block';
            } finally {
                if (elements.loading) elements.loading.style.display = 'none';
            }
        }

        function renderizarCapacitaciones(capacitaciones) {
            const container = document.getElementById('lista-capacitaciones');
            if (!container) return;

            if (capacitaciones.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-search text-muted fs-3 mb-2"></i>
                        <p class="text-muted mb-0">No se encontraron capacitaciones con los filtros aplicados</p>
                    </div>
                `;
                container.style.display = 'block';
                return;
            }

            const capacitacionesHTML = capacitaciones.map(cap => createCapacitacionHTML(cap)).join('');
            container.innerHTML = capacitacionesHTML;
            container.style.display = 'block';
        }

        function createCapacitacionHTML(capacitacion) {
            const tipoIcon = capacitacion.archivo_tipo === 'PDF' ? 
                '<i class="bi bi-file-earmark-pdf text-danger fs-4"></i>' : 
                '<i class="bi bi-file-earmark-word text-primary fs-4"></i>';
            
            const tipoClass = capacitacion.archivo_tipo === 'PDF' ? 'border-danger' : 'border-primary';
            
            return `
                <div class="card border-0 mb-3 shadow-sm rounded-3 card-hover ${tipoClass}" 
                     style="border-left: 4px solid ${capacitacion.archivo_tipo === 'PDF' ? '#dc3545' : '#0d6efd'} !important;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-start mb-2">
                                    <div class="me-3">
                                        ${tipoIcon}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold text-dark">
                                            ${capacitacion.titulo}
                                        </h6>
                                        <p class="text-muted small mb-2">
                                            ${capacitacion.descripcion || 'Sin descripción disponible'}
                                        </p>
                                        <div class="d-flex gap-3 flex-wrap">
                                            <small class="text-muted">
                                                <i class="bi bi-file-earmark me-1"></i>
                                                ${capacitacion.archivo_nombre}
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                ${formatearFecha(capacitacion.fecha_creacion)}
                                            </small>
                                            <span class="badge ${capacitacion.archivo_tipo === 'PDF' ? 'bg-danger' : 'bg-primary'} bg-opacity-10 ${capacitacion.archivo_tipo === 'PDF' ? 'text-danger' : 'text-primary'}">
                                                ${capacitacion.archivo_tipo}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex flex-column gap-2">
                                    <button class="btn btn-${capacitacion.archivo_tipo === 'PDF' ? 'danger' : 'primary'} btn-sm rounded-pill" 
                                            onclick="descargarCapacitacion(${capacitacion.id}, '${capacitacion.archivo_nombre}')">
                                        <i class="bi bi-download me-1"></i>Descargar
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm rounded-pill" 
                                            onclick="verDetallesCapacitacion(${capacitacion.id})">
                                        <i class="bi bi-eye me-1"></i>Ver Detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function actualizarEstadisticasCapacitaciones(capacitaciones) {
            const stats = {
                total: capacitaciones.length,
                pdfs: capacitaciones.filter(cap => cap.archivo_tipo === 'PDF').length,
                words: capacitaciones.filter(cap => cap.archivo_tipo === 'WORD').length
            };

            const elements = {
                'total-capacitaciones': stats.total,
                'total-pdfs': stats.pdfs,
                'total-words': stats.words
            };

            Object.entries(elements).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) element.textContent = value;
            });
        }

        function initBuscadorCapacitaciones() {
            const buscador = document.getElementById('buscar-capacitaciones');
            const filtroTipo = document.getElementById('filtro-tipo-capacitaciones');
            
            if (buscador) {
                buscador.addEventListener('input', filtrarCapacitaciones);
            }
            
            if (filtroTipo) {
                filtroTipo.addEventListener('change', filtrarCapacitaciones);
            }
        }

        function filtrarCapacitaciones() {
            const busqueda = document.getElementById('buscar-capacitaciones')?.value.toLowerCase() || '';
            const tipoFiltro = document.getElementById('filtro-tipo-capacitaciones')?.value || '';
            
            capacitacionesFiltradas = capacitacionesData.filter(cap => {
                const matchBusqueda = !busqueda || 
                    cap.titulo.toLowerCase().includes(busqueda) ||
                    (cap.descripcion && cap.descripcion.toLowerCase().includes(busqueda)) ||
                    cap.archivo_nombre.toLowerCase().includes(busqueda);
                    
                const matchTipo = !tipoFiltro || cap.archivo_tipo === tipoFiltro;
                
                return matchBusqueda && matchTipo;
            });
            
            renderizarCapacitaciones(capacitacionesFiltradas);
        }

        async function descargarCapacitacion(id, nombre) {
            try {
                const response = await fetch(`${CONFIG.API_CAPACITACIONES}?id=${id}&download=1`);
                
                if (!response.ok) {
                    throw new Error('Error al descargar el archivo');
                }
                
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = nombre;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                // Mostrar mensaje de éxito
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                
                Toast.fire({
                    icon: 'success',
                    title: `${nombre} descargado correctamente`
                });
                
            } catch (error) {
                console.error('Error al descargar:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo descargar el archivo. Inténtalo de nuevo.'
                });
            }
        }

        function verDetallesCapacitacion(id) {
            const capacitacion = capacitacionesData.find(cap => cap.id == id);
            if (!capacitacion) return;
            
            Swal.fire({
                title: capacitacion.titulo,
                html: `
                    <div class="text-start">
                        <p><strong>Descripción:</strong></p>
                        <p class="text-muted">${capacitacion.descripcion || 'Sin descripción disponible'}</p>
                        
                        <p><strong>Archivo:</strong> ${capacitacion.archivo_nombre}</p>
                        <p><strong>Tipo:</strong> ${capacitacion.archivo_tipo}</p>
                        <p><strong>Fecha de creación:</strong> ${formatearFecha(capacitacion.fecha_creacion)}</p>
                        ${capacitacion.fecha_actualizacion ? 
                            `<p><strong>Última actualización:</strong> ${formatearFecha(capacitacion.fecha_actualizacion)}</p>` : 
                            ''}
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Descargar',
                cancelButtonText: 'Cerrar',
                confirmButtonColor: capacitacion.archivo_tipo === 'PDF' ? '#dc3545' : '#0d6efd'
            }).then((result) => {
                if (result.isConfirmed) {
                    descargarCapacitacion(capacitacion.id, capacitacion.archivo_nombre);
                }
            });
        }

        // ========== GESTIÓN DE MANUALES ==========
        async function loadManualesUsuario() {
            try {
                document.getElementById('loadingManuales').classList.remove('d-none');
                
                const response = await fetch(`${CONFIG.API_BASE}/CRUD-admin/Crud-manuales.php?estado=1`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const manuales = await response.json();
                
                // Actualizar estadísticas
                updateManualesStats(manuales);
                
                // Renderizar manuales
                renderManualesUsuario(manuales);
                
                // Configurar filtros
                setupManualesFilters(manuales);
                
            } catch (error) {
                console.error('Error al cargar manuales:', error);
                document.getElementById('manualesContainer').innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Error al cargar los manuales. Intente nuevamente.
                        </div>
                    </div>
                `;
            } finally {
                document.getElementById('loadingManuales').classList.add('d-none');
            }
        }

        function updateManualesStats(manuales) {
            const total = manuales.length;
            const pdfs = manuales.filter(m => m.archivo_tipo === 'PDF').length;
            const word = manuales.filter(m => m.archivo_tipo === 'WORD').length;
            const general = manuales.filter(m => m.categoria === 'general').length;
            
            document.getElementById('totalManualesUser').textContent = total;
            document.getElementById('manualesPDFUser').textContent = pdfs;
            document.getElementById('manualesWordUser').textContent = word;
            document.getElementById('manualesGeneralUser').textContent = general;
        }

        function renderManualesUsuario(manuales) {
            const container = document.getElementById('manualesContainer');
            
            if (manuales.length === 0) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="text-center p-5">
                            <i class="bi bi-book fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay manuales disponibles</h4>
                            <p class="text-muted">Aún no se han publicado manuales para consulta.</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = manuales.map(manual => `
                <div class="col-lg-4 col-md-6 mb-4 manual-card" 
                     data-categoria="${manual.categoria}" 
                     data-tipo="${manual.archivo_tipo}"
                     data-titulo="${manual.titulo.toLowerCase()}"
                     data-descripcion="${(manual.descripcion || '').toLowerCase()}">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">${manual.titulo}</h5>
                                <span class="badge bg-primary">${manual.categoria}</span>
                            </div>
                            
                            <p class="card-text text-muted mb-3">
                                ${manual.descripcion || 'Sin descripción disponible'}
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <small class="text-muted">
                                        <i class="bi bi-file me-1"></i>${manual.archivo_tipo}
                                    </small>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        <i class="bi bi-tag me-1"></i>v${manual.version}
                                    </small>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    ${formatearFecha(manual.fecha_creacion)}
                                </small>
                                <button class="btn btn-primary btn-sm" onclick="descargarManualUsuario(${manual.id})">
                                    <i class="bi bi-download me-1"></i>Descargar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function setupManualesFilters(manuales) {
            const searchInput = document.getElementById('searchManuales');
            const filterCategoria = document.getElementById('filterCategoria');
            const filterTipo = document.getElementById('filterTipo');
            
            function aplicarFiltrosManuales() {
                const searchTerm = searchInput.value.toLowerCase();
                const categoria = filterCategoria.value;
                const tipo = filterTipo.value;
                
                const cards = document.querySelectorAll('.manual-card');
                
                cards.forEach(card => {
                    const cardCategoria = card.dataset.categoria;
                    const cardTipo = card.dataset.tipo;
                    const cardTitulo = card.dataset.titulo;
                    const cardDescripcion = card.dataset.descripcion;
                    
                    const matchSearch = !searchTerm || 
                        cardTitulo.includes(searchTerm) || 
                        cardDescripcion.includes(searchTerm);
                    
                    const matchCategoria = !categoria || cardCategoria === categoria;
                    const matchTipo = !tipo || cardTipo === tipo;
                    
                    if (matchSearch && matchCategoria && matchTipo) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
            
            searchInput.addEventListener('input', aplicarFiltrosManuales);
            filterCategoria.addEventListener('change', aplicarFiltrosManuales);
            filterTipo.addEventListener('change', aplicarFiltrosManuales);
        }

        async function descargarManualUsuario(id) {
            try {
                // Mostrar indicador de descarga
                Swal.fire({
                    title: 'Descargando...',
                    text: 'Preparando el archivo para descarga',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Crear enlace de descarga
                const downloadUrl = `${CONFIG.API_BASE}/CRUD-admin/Crud-manuales.php?id=${id}&download=1`;
                
                // Crear elemento temporal para descarga
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Cerrar indicador de carga
                setTimeout(() => {
                    Swal.close();
                }, 1000);
                
            } catch (error) {
                console.error('Error al descargar manual:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de descarga',
                    text: 'No se pudo descargar el manual. Intente nuevamente.'
                });
            }
        }
    </script>
</body>
</html>