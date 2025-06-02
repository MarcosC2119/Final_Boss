<?php
// Agregar al inicio del archivo para obtener el email real del usuario
session_start();
// Asumir que el email está en la sesión (ajustar según tu sistema de auth)
$email_usuario = isset($_SESSION['email']) ? $_SESSION['email'] : 'usuario@ejemplo.com';
$usuario_nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Docente';
$usuario_rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : 'docente';
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
        /* Estilos profesionales similares al dashboard admin */
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
        
        .nav-link-custom:hover {
            background: rgba(13, 110, 253, 0.1);
            border-left-color: #0d6efd;
            transform: translateX(5px);
        }
        
        .nav-link-custom.active {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(13, 110, 253, 0.05));
            border-left-color: #0d6efd;
            color: #0d6efd !important;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
            font-weight: bold;
        }
        
        .brand-logo {
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
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
        
        .stats-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }
        
        .main-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            min-height: 85vh;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        
        .section-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .section-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
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
        
        .ticket-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
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
            <!-- ========== SIDEBAR ========== -->
            <nav class="col-lg-2 col-md-3 d-md-block sidebar-glass position-sticky" style="top: 0; height: 100vh; overflow-y: auto;">
                <div class="position-sticky pt-4">
                    
                    <!-- Stats Cards en Sidebar -->
                    <div class="px-3 mb-4">
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="card border-0 bg-primary bg-gradient text-white stats-card">
                                    <div class="card-body p-2 text-center">
                                        <i class="bi bi-calendar-check fs-4"></i>
                                        <div class="fw-bold" id="stat-reservas">0</div>
                                        <small>Reservas</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card border-0 bg-warning bg-gradient text-white stats-card">
                                    <div class="card-body p-2 text-center">
                                        <i class="bi bi-headset fs-4"></i>
                                        <div class="fw-bold" id="stat-tickets">0</div>
                                        <small>Tickets</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Menu -->
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
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="cargarDashboard()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
                            </button>
                            <button class="btn btn-custom-primary" onclick="nuevaSolicitudSoporte()">
                                <i class="bi bi-plus-circle me-2"></i>Nueva Solicitud
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
                        <div class="section-card">
                            <div class="card-body p-5 text-center">
                                <i class="bi bi-search text-muted fs-1 mb-3"></i>
                                <h5 class="text-muted">Revisión de Salas</h5>
                                <p class="text-muted">Esta sección mostrará las salas disponibles para reservar.</p>
                                <button class="btn btn-custom-primary">Buscar Salas Disponibles</button>
                            </div>
                        </div>
                    </div>

                    <div id="seccion-nueva-reserva" class="content-section" style="display: none;">
                        <div class="section-card">
                            <div class="card-body p-5 text-center">
                                <i class="bi bi-plus-circle text-muted fs-1 mb-3"></i>
                                <h5 class="text-muted">Nueva Reserva</h5>
                                <p class="text-muted">Formulario para crear una nueva reserva de sala.</p>
                                <button class="btn btn-custom-primary">Crear Reserva</button>
                            </div>
                        </div>
                    </div>

                    <div id="seccion-mis-qr" class="content-section" style="display: none;">
                        <div class="section-card">
                            <div class="card-body p-5 text-center">
                                <i class="bi bi-qr-code text-muted fs-1 mb-3"></i>
                                <h5 class="text-muted">Mis Códigos QR</h5>
                                <p class="text-muted">Visualiza y gestiona tus códigos QR de reservas.</p>
                                <button class="btn btn-custom-primary">Ver Códigos QR</button>
                            </div>
                        </div>
                    </div>

                    <div id="seccion-capacitaciones" class="content-section" style="display: none;">
                        <div class="section-card">
                            <div class="card-body p-5 text-center">
                                <i class="bi bi-mortarboard text-muted fs-1 mb-3"></i>
                                <h5 class="text-muted">Capacitaciones</h5>
                                <p class="text-muted">Materiales de capacitación y documentos de entrenamiento.</p>
                                <button class="btn btn-custom-primary">Ver Capacitaciones</button>
                            </div>
                        </div>
                    </div>

                    <div id="seccion-manual" class="content-section" style="display: none;">
                        <div class="section-card">
                            <div class="card-body p-5 text-center">
                                <i class="bi bi-book text-muted fs-1 mb-3"></i>
                                <h5 class="text-muted">Manual de Usuario</h5>
                                <p class="text-muted">Guía completa de uso del sistema ROOMIT.</p>
                                <button class="btn btn-custom-primary">Abrir Manual</button>
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
                            <input type="email" class="form-control rounded-3" id="emailSolicitud" 
                                   value="<?= htmlspecialchars($email_usuario) ?>" required>
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
        // ========== VARIABLES GLOBALES ==========
        const emailUsuario = '<?= $email_usuario ?>';
        const API_SOPORTE = '../../Backend/api/SoporteTecnico/Metodos-soporte.php';
        let misTickets = [];
        let mostrandoTodos = false;

        // ========== INICIALIZACIÓN ==========
        document.addEventListener('DOMContentLoaded', function() {
            cargarDashboard();
            mostrarSeccion('resumen');
            cargarMisTickets();
        });

        // ========== NAVEGACIÓN ENTRE SECCIONES ==========
        function mostrarSeccion(seccion) {
            // Ocultar todas las secciones
            document.querySelectorAll('.content-section').forEach(s => s.style.display = 'none');
            
            // Mostrar la sección seleccionada
            document.getElementById(`seccion-${seccion}`).style.display = 'block';
            
            // Actualizar navegación activa
            document.querySelectorAll('.nav-link-custom').forEach(link => {
                link.classList.remove('active');
            });
            
            // Activar el enlace correspondiente
            const activeLink = document.querySelector(`[href="#${seccion}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
            
            // Actualizar breadcrumb
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
            
            // Cargar datos específicos según la sección
            if (seccion === 'soporte') {
                cargarMisTickets();
            }
        }

        // ========== CARGAR DASHBOARD ==========
        async function cargarDashboard() {
            try {
                // Simular carga de estadísticas (aquí conectarías con tus APIs reales)
                document.getElementById('total-reservas-activas').textContent = '3';
                document.getElementById('salas-disponibles').textContent = '12';
                document.getElementById('tickets-pendientes').textContent = '1';
                document.getElementById('qr-generados').textContent = '3';
                
                // Actualizar stats del sidebar
                document.getElementById('stat-reservas').textContent = '3';
                document.getElementById('stat-tickets').textContent = '1';
                
                cargarMisReservas();
                cargarNotificaciones();
                
            } catch (error) {
                console.error('Error al cargar dashboard:', error);
            }
        }

        // ========== CARGAR MIS RESERVAS ==========
        function cargarMisReservas() {
            const container = document.getElementById('mis-reservas-container');
            
            // Datos de ejemplo (reemplazar con llamada a API real)
            const reservasEjemplo = `
                <div class="card border-0 mb-3 shadow-sm rounded-3" style="border-left: 4px solid #198754 !important;">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="card-title fw-bold text-primary mb-2">
                                    <i class="bi bi-door-closed me-2"></i>Sala 101 - Matemáticas
                                </h6>
                                <div class="d-flex flex-wrap gap-3">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar-event text-primary me-1"></i>
                                        Hoy, 2024-03-20
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

                const response = await fetch(`${API_SOPORTE}?email_solicitante=${encodeURIComponent(emailUsuario)}`);
                const data = await response.json();

                if (data.success && data.data) {
                    misTickets = data.data.filter(ticket => ticket.email_solicitante === emailUsuario);
                    
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
            document.getElementById('emailSolicitud').value = emailUsuario;
            new bootstrap.Modal(document.getElementById('modalNuevaSolicitud')).show();
        }

        function abrirSoporteTecnico() {
            nuevaSolicitudSoporte();
        }

        async function enviarSolicitudSoporte() {
            try {
                const solicitud = {
                    email: emailUsuario,
                    tipo: document.getElementById('tipoSolicitud').value,
                    motivo: document.getElementById('motivoSolicitud').value,
                    prioridad: document.getElementById('prioridadSolicitud').value
                };

                console.log('📤 Enviando solicitud:', solicitud);

                const response = await fetch(API_SOPORTE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
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
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir a logout
                    window.location.href = '../../login.php';
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
    </script>
</body>
</html>