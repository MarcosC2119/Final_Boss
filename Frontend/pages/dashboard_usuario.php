<?php
// Agregar al inicio del archivo para obtener el email real del usuario
session_start();
// Asumir que el email está en la sesión (ajustar según tu sistema de auth)
$email_usuario = isset($_SESSION['email']) ? $_SESSION['email'] : 'usuario@ejemplo.com';
?>
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                            <button class="btn btn-outline-warning rounded-pill fw-semibold px-4" onclick="abrirSoporteTecnico()">
                                Solicitar Ayuda
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. MI SOPORTE TÉCNICO (NUEVA SECCIÓN) -->
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-primary mb-0" style="letter-spacing: 0.5px;">
                    <i class="bi bi-headset me-2"></i>Mi Soporte Técnico
                </h5>
                <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="nuevaSolicitudSoporte()">
                    <i class="bi bi-plus-circle me-1"></i>Nueva Solicitud
                </button>
            </div>

            <!-- Estadísticas Rápidas de Soporte -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 text-center" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
                        <div class="card-body py-3">
                            <i class="bi bi-clock-history text-warning fs-4 mb-2"></i>
                            <h6 class="fw-bold mb-1" id="mis-tickets-pendientes">0</h6>
                            <small class="text-muted">Pendientes</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 text-center" style="background: linear-gradient(135deg, #cce5ff 0%, #74b9ff 100%);">
                        <div class="card-body py-3">
                            <i class="bi bi-gear text-primary fs-4 mb-2"></i>
                            <h6 class="fw-bold mb-1" id="mis-tickets-proceso">0</h6>
                            <small class="text-muted">En Proceso</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 text-center" style="background: linear-gradient(135deg, #d4edda 0%, #00b894 100%);">
                        <div class="card-body py-3">
                            <i class="bi bi-check-circle text-success fs-4 mb-2"></i>
                            <h6 class="fw-bold mb-1" id="mis-tickets-resueltos">0</h6>
                            <small class="text-muted">Resueltos</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 rounded-4 text-center" style="background: linear-gradient(135deg, #f8d7da 0%, #e17055 100%);">
                        <div class="card-body py-3">
                            <i class="bi bi-key text-danger fs-4 mb-2"></i>
                            <h6 class="fw-bold mb-1" id="passwords-generadas">0</h6>
                            <small class="text-muted">Contraseñas</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Mis Tickets -->
            <div class="card custom-card rounded-4 border-0">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-primary mb-0">
                            <i class="bi bi-ticket-detailed me-2"></i>Mis Solicitudes de Soporte
                        </h6>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary btn-sm" onclick="cargarMisTickets()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="verTodasSolicitudes()">
                                <i class="bi bi-list me-1"></i>Ver Todas
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
                        <button class="btn btn-primary btn-sm rounded-pill" onclick="nuevaSolicitudSoporte()">
                            <i class="bi bi-plus-circle me-1"></i>Crear Primera Solicitud
                        </button>
                    </div>

                    <!-- Lista de tickets -->
                    <div id="lista-mis-tickets">
                        <!-- Los tickets se cargarán aquí dinámicamente -->
                    </div>

                    <!-- Botón Ver Más (cuando hay más de 3 tickets) -->
                    <div id="boton-ver-mas" class="text-center mt-3" style="display: none;">
                        <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="toggleMostrarTodos()">
                            <i class="bi bi-chevron-down me-1" id="icon-toggle"></i>
                            <span id="texto-toggle">Ver Todas las Solicitudes</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Notificaciones -->
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

    <!-- MODAL: Nueva Solicitud de Soporte -->
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
                                <option value="training_request">📚 Solicitar Capacitación</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="emailSolicitud" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-1"></i>Tu Email
                            </label>
                            <input type="email" class="form-control rounded-3" id="emailSolicitud" 
                                   placeholder="tu.email@ejemplo.com" required>
                        </div>
                        <div class="mb-4">
                            <label for="motivoSolicitud" class="form-label fw-semibold">
                                <i class="bi bi-chat-dots me-1"></i>Describe tu problema o consulta
                            </label>
                            <textarea class="form-control rounded-3" id="motivoSolicitud" rows="6" 
                                      placeholder="Explica detalladamente qué necesitas o qué problema tienes..." required></textarea>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Proporciona toda la información posible para ayudarte mejor
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="prioridadSolicitud" class="form-label fw-semibold">
                                <i class="bi bi-speedometer2 me-1"></i>Urgencia
                            </label>
                            <select class="form-select rounded-3" id="prioridadSolicitud" required>
                                <option value="baja">🟢 Baja - Puedo esperar varios días</option>
                                <option value="media" selected>🟡 Media - Necesito ayuda en 1-2 días</option>
                                <option value="alta">🟠 Alta - Es importante, necesito ayuda hoy</option>
                                <option value="urgente">🔴 Urgente - No puedo trabajar sin esto</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light border-0 rounded-bottom-4 p-4">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary px-4 rounded-3" onclick="enviarSolicitudSoporte()">
                        <i class="bi bi-send me-2"></i>Enviar Solicitud
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Ver Detalles del Ticket -->
    <div class="modal fade" id="modalVerTicket" tabindex="-1" aria-labelledby="modalVerTicketLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0 rounded-top-4" id="headerModalTicket">
                    <h5 class="modal-title fw-bold text-white" id="modalVerTicketLabel">
                        <i class="bi bi-ticket-detailed me-2"></i>Detalles del Ticket
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4" id="contenidoModalTicket">
                    <!-- El contenido se carga dinámicamente -->
                </div>
                <div class="modal-footer bg-light border-0 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ========== VARIABLES GLOBALES CORREGIDAS ==========
        const API_SOPORTE = '../../Backend/api/SoporteTecnico/Metodos-soporte.php';
        let misTickets = [];
        let emailUsuario = '<?php echo $email_usuario; ?>'; // ✅ OBTENER EMAIL REAL DE PHP
        let mostrandoTodos = false;

        // ========== CARGAR AL INICIALIZAR ==========
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📧 Email del usuario:', emailUsuario);
            cargarMisTickets();
        });

        // ========== CARGAR MIS TICKETS CORREGIDO ==========
        async function cargarMisTickets() {
            try {
                document.getElementById('loading-soporte').style.display = 'block';
                document.getElementById('sin-tickets').style.display = 'none';
                document.getElementById('lista-mis-tickets').style.display = 'none';
                document.getElementById('boton-ver-mas').style.display = 'none';

                console.log('🔍 Buscando tickets para:', emailUsuario);

                // ✅ CONSULTA MEJORADA: Obtener TODOS los tickets
                const response = await fetch(`${API_SOPORTE}`);
                const data = await response.json();

                console.log('📊 Respuesta del servidor:', data);

                if (data.success && data.data && data.data.length > 0) {
                    // ✅ FILTRAR CORRECTAMENTE por email del usuario
                    misTickets = data.data.filter(ticket => 
                        ticket.email_solicitante && 
                        ticket.email_solicitante.toLowerCase() === emailUsuario.toLowerCase()
                    );

                    console.log('🎫 Mis tickets filtrados:', misTickets);

                    if (misTickets.length > 0) {
                        renderizarMisTickets(misTickets);
                        actualizarEstadisticasSoporte(misTickets);
                    } else {
                        console.log('⚠️ No se encontraron tickets para este email');
                        mostrarSinTickets();
                    }
                } else {
                    console.log('📭 No hay tickets en el sistema');
                    mostrarSinTickets();
                }

            } catch (error) {
                console.error('❌ Error al cargar tickets:', error);
                mostrarSinTickets();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudieron cargar tus solicitudes. Verifica tu conexión.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } finally {
                document.getElementById('loading-soporte').style.display = 'none';
            }
        }

        // ========== RENDERIZAR MIS TICKETS CORREGIDO ==========
        function renderizarMisTickets(tickets) {
            const container = document.getElementById('lista-mis-tickets');
            
            if (tickets.length === 0) {
                mostrarSinTickets();
                return;
            }

            // ✅ DECIDIR CUÁNTOS MOSTRAR
            const ticketsAMostrar = mostrandoTodos ? tickets : tickets.slice(0, 3);
            const hayMasTickets = tickets.length > 3;

            // ✅ MOSTRAR INFORMACIÓN DEL TOTAL
            const infoTotal = hayMasTickets && !mostrandoTodos ? 
                `<div class="alert alert-info alert-sm mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    Mostrando las 3 más recientes de <strong>${tickets.length} solicitudes</strong>
                </div>` : '';

            container.innerHTML = infoTotal + ticketsAMostrar.map((ticket, index) => `
                <div class="card border-0 mb-3 shadow-sm rounded-3" style="border-left: 4px solid ${getColorEstado(ticket.estado)} !important;">
                    <div class="card-body p-3">
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
                                    <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="verDetallesTicket(${ticket.id})">
                                        <i class="bi bi-eye me-1"></i>Ver Detalles
                                    </button>
                                    ${ticket.respuesta_admin ? `
                                        <span class="badge bg-success">
                                            <i class="bi bi-reply me-1"></i>Respondido
                                        </span>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            // ✅ MOSTRAR/OCULTAR BOTÓN "VER MÁS"
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

        // ========== VER TODAS LAS SOLICITUDES ==========
        function verTodasSolicitudes() {
            mostrandoTodos = true;
            renderizarMisTickets(misTickets);
        }

        // ========== NUEVA SOLICITUD CORREGIDA ==========
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
                    email: emailUsuario, // ✅ USAR EMAIL REAL
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
                    
                    // ✅ RECARGAR TICKETS DESPUÉS DE CREAR UNO NUEVO
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

        // ========== RESTO DE FUNCIONES (mantener las existentes) ==========
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

            const headerColor = getColorEstado(ticket.estado);
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
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-chat-dots me-2"></i>Descripción
                        </h6>
                        <div class="bg-light rounded-3 p-3 mb-3">
                            ${ticket.motivo_solicitud}
                        </div>
                        ${ticket.respuesta_admin ? `
                            <h6 class="fw-bold text-success mb-2">
                                <i class="bi bi-reply me-2"></i>Respuesta del Administrador
                            </h6>
                            <div class="alert alert-success">
                                ${ticket.respuesta_admin}
                            </div>
                        ` : ''}
                        ${ticket.nueva_password_generada ? `
                            <div class="alert alert-info">
                                <h6 class="fw-bold mb-2">
                                    <i class="bi bi-key me-2"></i>Contraseña Temporal Generada
                                </h6>
                                <div class="input-group">
                                    <input type="password" class="form-control" value="${ticket.nueva_password_generada}" id="passwordTemp">
                                    <button class="btn btn-outline-primary" onclick="togglePassword()">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary" onclick="copiarPassword('${ticket.nueva_password_generada}')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;

            new bootstrap.Modal(document.getElementById('modalVerTicket')).show();
        }

        // ========== FUNCIONES AUXILIARES ==========
        function mostrarSinTickets() {
            document.getElementById('sin-tickets').style.display = 'block';
            document.getElementById('lista-mis-tickets').style.display = 'none';
            document.getElementById('boton-ver-mas').style.display = 'none';
            actualizarEstadisticasSoporte([]);
        }

        function mostrarPassword(password) {
            Swal.fire({
                title: 'Tu Contraseña Temporal',
                html: `
                    <div class="input-group">
                        <input type="text" class="form-control text-center fs-5 fw-bold" value="${password}" readonly>
                        <button class="btn btn-outline-primary" onclick="navigator.clipboard.writeText('${password}')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <p class="text-muted mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Usa esta contraseña para acceder al sistema
                    </p>
                `,
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
        }

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
                'pendiente': '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pendiente</span>',
                'en_proceso': '<span class="badge bg-primary"><i class="bi bi-gear me-1"></i>En Proceso</span>',
                'resuelto': '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Resuelto</span>',
                'cerrado': '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i>Cerrado</span>'
            };
            return badges[estado] || badges['pendiente'];
        }

        function getBadgePrioridad(prioridad) {
            const badges = {
                'baja': '<span class="badge bg-light text-dark border">Baja</span>',
                'media': '<span class="badge bg-info">Media</span>',
                'alta': '<span class="badge bg-warning text-dark">Alta</span>',
                'urgente': '<span class="badge bg-danger">Urgente</span>'
            };
            return badges[prioridad] || badges['media'];
        }

        function getIconoTipo(tipo) {
            const iconos = {
                'password_recovery': '<i class="bi bi-key-fill text-warning fs-5"></i>',
                'technical_issue': '<i class="bi bi-gear-fill text-danger fs-5"></i>',
                'general_support': '<i class="bi bi-chat-dots-fill text-primary fs-5"></i>',
                'training_request': '<i class="bi bi-mortarboard-fill text-success fs-5"></i>'
            };
            return iconos[tipo] || iconos['general_support'];
        }

        function formatearTipo(tipo) {
            const tipos = {
                'password_recovery': 'Recuperar Contraseña',
                'technical_issue': 'Problema Técnico', 
                'general_support': 'Consulta General',
                'training_request': 'Solicitar Capacitación'
            };
            return tipos[tipo] || 'Consulta General';
        }

        function formatearFecha(fecha) {
            return new Date(fecha).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function togglePassword() {
            const input = document.getElementById('passwordTemp');
            const icon = document.getElementById('eyeIcon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        function copiarPassword(password) {
            navigator.clipboard.writeText(password).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Copiado!',
                    text: 'Contraseña copiada al portapapeles',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }
    </script>

    <!-- JS personalizado -->
    <script src="js/gestion-usuarios.js"></script>
</body>
</html>