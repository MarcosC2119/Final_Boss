<?php
// ========== SOPORTE TÉCNICO - PANEL ADMINISTRATIVO ==========
// Gestión de tickets de soporte, incluyendo recuperación de contraseñas
// ========================================================

// Verificación de autenticación
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte Técnico - ROOMIT</title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
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
        
        .prioridad-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .stats-card {
            background: linear-gradient(135deg, var(--bs-primary), var(--bs-primary-rgb));
            border: none;
            color: white;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        
        <!-- ========== HEADER PRINCIPAL ========== -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center bg-white rounded-4 p-4 shadow-sm">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-primary bg-gradient rounded-circle p-3 me-3">
                                <i class="bi bi-headset text-white fs-4"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-dark fw-bold">Soporte Técnico</h1>
                                <p class="text-muted mb-0">Gestión de tickets y solicitudes de soporte</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="cargarTickets()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Actualizar
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoTicketModal">
                            <i class="bi bi-plus-circle me-2"></i>Nuevo Ticket
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== ESTADÍSTICAS ========== -->
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card stats-card text-white h-100" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                    <div class="card-body text-center">
                        <i class="bi bi-clock-history fs-1 mb-2"></i>
                        <h4 id="stat-pendientes" class="fw-bold mb-1">0</h4>
                        <small class="opacity-75">Tickets Pendientes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-white h-100" style="background: linear-gradient(135deg, #0d6efd, #6610f2);">
                    <div class="card-body text-center">
                        <i class="bi bi-gear fs-1 mb-2"></i>
                        <h4 id="stat-proceso" class="fw-bold mb-1">0</h4>
                        <small class="opacity-75">En Proceso</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-white h-100" style="background: linear-gradient(135deg, #198754, #20c997);">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle fs-1 mb-2"></i>
                        <h4 id="stat-resueltos" class="fw-bold mb-1">0</h4>
                        <small class="opacity-75">Resueltos Hoy</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-white h-100" style="background: linear-gradient(135deg, #dc3545, #e83e8c);">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                        <h4 id="stat-urgentes" class="fw-bold mb-1">0</h4>
                        <small class="opacity-75">Urgentes</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== FILTROS ========== -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-white rounded-4 p-4 shadow-sm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search me-1"></i>Buscar Ticket
                            </label>
                            <input type="text" id="buscarTicket" class="form-control" placeholder="Email, asunto, descripción...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Estado</label>
                            <select id="filtroEstado" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendientes</option>
                                <option value="en_proceso">En Proceso</option>
                                <option value="resuelto">Resueltos</option>
                                <option value="cerrado">Cerrados</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tipo</label>
                            <select id="filtroTipo" class="form-select">
                                <option value="">Todos</option>
                                <option value="password_recovery">Recuperar Contraseña</option>
                                <option value="technical_issue">Problema Técnico</option>
                                <option value="general_support">Soporte General</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Prioridad</label>
                            <select id="filtroPrioridad" class="form-select">
                                <option value="">Todas</option>
                                <option value="urgente">Urgente</option>
                                <option value="alta">Alta</option>
                                <option value="media">Media</option>
                                <option value="baja">Baja</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary flex-fill" onclick="limpiarFiltros()">
                                    <i class="bi bi-x-circle me-1"></i>Limpiar
                                </button>
                                <button class="btn btn-primary flex-fill" onclick="aplicarFiltros()">
                                    <i class="bi bi-funnel me-1"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== LISTA DE TICKETS ========== -->
        <div class="row">
            <div class="col-12">
                <div class="bg-white rounded-4 shadow-sm">
                    <!-- Header -->
                    <div class="p-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 fw-bold">Lista de Tickets</h5>
                                <small class="text-muted">Total: <span id="totalTickets">0</span> tickets</small>
                            </div>
                            <div class="d-flex gap-2">
                                <select id="itemsPorPagina" class="form-select form-select-sm" style="width: auto;">
                                    <option value="10">10 por página</option>
                                    <option value="25">25 por página</option>
                                    <option value="50">50 por página</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div id="loadingTickets" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3 text-muted">Cargando tickets...</p>
                    </div>

                    <!-- Tickets Container -->
                    <div id="ticketsContainer" class="p-4">
                        <!-- Los tickets se cargarán dinámicamente aquí -->
                    </div>

                    <!-- No Results -->
                    <div id="noTicketsMessage" class="text-center py-5" style="display: none;">
                        <div class="text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <h5>No se encontraron tickets</h5>
                            <p>No hay tickets que coincidan con los filtros aplicados.</p>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div class="p-4 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    Mostrando <span id="resultadosInfo">0-0 de 0</span> resultados
                                </small>
                            </div>
                            <nav>
                                <ul id="paginacion" class="pagination pagination-sm mb-0">
                                    <!-- Paginación dinámica -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== MODAL NUEVO TICKET ========== -->
    <div class="modal fade" id="nuevoTicketModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Crear Nuevo Ticket
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoTicket">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Email del Usuario <span class="text-danger">*</span></label>
                                    <input type="email" id="nuevo_email" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Tipo de Ticket <span class="text-danger">*</span></label>
                                    <select id="nuevo_tipo" class="form-select" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="password_recovery">Recuperar Contraseña</option>
                                        <option value="technical_issue">Problema Técnico</option>
                                        <option value="general_support">Soporte General</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Asunto <span class="text-danger">*</span></label>
                                    <input type="text" id="nuevo_asunto" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Prioridad</label>
                                    <select id="nuevo_prioridad" class="form-select">
                                        <option value="media">Media</option>
                                        <option value="baja">Baja</option>
                                        <option value="alta">Alta</option>
                                        <option value="urgente">Urgente</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descripción <span class="text-danger">*</span></label>
                            <textarea id="nuevo_descripcion" class="form-control" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="crearTicket()">
                        <i class="bi bi-check me-2"></i>Crear Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== MODAL VER/RESPONDER TICKET ========== -->
    <div class="modal fade" id="verTicketModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-eye me-2"></i>Detalles del Ticket
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detallesTicket">
                        <!-- Contenido dinámico del ticket -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="responderTicket()">
                        <i class="bi bi-reply me-2"></i>Responder
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
        // ========== VARIABLES GLOBALES ==========
        let ticketsData = [];
        let paginaActual = 1;
        let itemsPorPagina = 10;

        // ========== INICIALIZACIÓN ==========
        document.addEventListener('DOMContentLoaded', function() {
            cargarTickets();
            configurarEventListeners();
        });

        function configurarEventListeners() {
            // Filtros en tiempo real
            document.getElementById('buscarTicket').addEventListener('input', debounce(aplicarFiltros, 300));
            document.getElementById('filtroEstado').addEventListener('change', aplicarFiltros);
            document.getElementById('filtroTipo').addEventListener('change', aplicarFiltros);
            document.getElementById('filtroPrioridad').addEventListener('change', aplicarFiltros);
            document.getElementById('itemsPorPagina').addEventListener('change', function() {
                itemsPorPagina = parseInt(this.value);
                paginaActual = 1;
                aplicarFiltros();
            });
        }

        // ========== FUNCIONES PRINCIPALES ==========
        async function cargarTickets() {
            try {
                mostrarLoading(true);
                
                // Construir parámetros de la consulta
                const params = new URLSearchParams({
                    search: document.getElementById('buscarTicket').value || '',
                    estado: document.getElementById('filtroEstado').value || '',
                    tipo: document.getElementById('filtroTipo').value || '',
                    prioridad: document.getElementById('filtroPrioridad').value || '',
                    page: paginaActual,
                    per_page: itemsPorPagina
                });
                
                // ✅ LLAMADA REAL A LA API
                const response = await fetch(`../../../Backend/api/SoporteTecnico/Metodos-soporte.php?${params}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // ✅ Usar datos REALES de la API
                    ticketsData = result.data || [];
                    
                    // ✅ Actualizar estadísticas con datos reales
                    if (result.estadisticas) {
                        document.getElementById('stat-pendientes').textContent = result.estadisticas.pendientes || 0;
                        document.getElementById('stat-proceso').textContent = result.estadisticas.en_proceso || 0;
                        document.getElementById('stat-resueltos').textContent = result.estadisticas.resueltos_hoy || 0;
                        document.getElementById('stat-urgentes').textContent = result.estadisticas.urgentes || 0;
                    }
                    
                    // ✅ Mostrar tickets reales
                    aplicarFiltros();
                    
                    // ✅ Actualizar paginación con datos reales
                    if (result.pagination) {
                        actualizarPaginacion(result.pagination.total_items);
                    }
                    
                } else {
                    throw new Error(result.message || 'Error al obtener tickets');
                }

            } catch (error) {
                console.error('Error al cargar tickets:', error);
                
                // Mostrar error específico para debugging
                Swal.fire({
                    title: 'Error al cargar tickets',
                    text: `Error: ${error.message}`,
                    icon: 'error',
                    footer: 'Revisa la consola para más detalles'
                });
                
                // Mostrar tickets vacíos en caso de error
                ticketsData = [];
                aplicarFiltros();
                
            } finally {
                mostrarLoading(false);
            }
        }

        function aplicarFiltros() {
            const busqueda = document.getElementById('buscarTicket').value.toLowerCase();
            const estado = document.getElementById('filtroEstado').value;
            const tipo = document.getElementById('filtroTipo').value;
            const prioridad = document.getElementById('filtroPrioridad').value;

            let ticketsFiltrados = ticketsData.filter(ticket => {
                const matchBusqueda = !busqueda || 
                    ticket.email_solicitante.toLowerCase().includes(busqueda) ||
                    ticket.asunto.toLowerCase().includes(busqueda) ||
                    ticket.descripcion.toLowerCase().includes(busqueda);
                
                const matchEstado = !estado || ticket.estado === estado;
                const matchTipo = !tipo || ticket.tipo === tipo;
                const matchPrioridad = !prioridad || ticket.prioridad === prioridad;

                return matchBusqueda && matchEstado && matchTipo && matchPrioridad;
            });

            mostrarTickets(ticketsFiltrados);
        }

        function mostrarTickets(tickets) {
            const container = document.getElementById('ticketsContainer');
            const noResults = document.getElementById('noTicketsMessage');
            
            document.getElementById('totalTickets').textContent = tickets.length;

            if (tickets.length === 0) {
                container.innerHTML = '';
                noResults.style.display = 'block';
                return;
            }

            noResults.style.display = 'none';

            // Paginación
            const inicio = (paginaActual - 1) * itemsPorPagina;
            const fin = inicio + itemsPorPagina;
            const ticketsPagina = tickets.slice(inicio, fin);

            container.innerHTML = ticketsPagina.map(ticket => `
                <div class="card ticket-card ticket-${ticket.estado} mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-1">
                                <div class="text-center">
                                    <div class="badge bg-secondary rounded-pill">#${ticket.id}</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi ${getTipoIcon(ticket.tipo)} me-2 fs-5"></i>
                                    <div>
                                        <small class="text-muted d-block">${formatTipo(ticket.tipo)}</small>
                                        <span class="badge prioridad-badge ${getPrioridadClass(ticket.prioridad)}">${formatPrioridad(ticket.prioridad)}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div>
                                    <div class="fw-semibold">${ticket.email_solicitante}</div>
                                    <small class="text-muted">${ticket.asunto}</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <span class="badge ${getEstadoBadgeClass(ticket.estado)}">${formatEstado(ticket.estado)}</span>
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted">${formatFecha(ticket.fecha_creacion)}</small>
                            </div>
                            <div class="col-md-2 text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="verTicket(${ticket.id})" data-bs-toggle="modal" data-bs-target="#verTicketModal">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success" onclick="cambiarEstado(${ticket.id}, 'resuelto')">
                                        <i class="bi bi-check"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="eliminarTicket(${ticket.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            // Actualizar paginación
            actualizarPaginacion(tickets.length);
        }

        // ========== FUNCIONES DE UTILIDAD ==========
        function getTipoIcon(tipo) {
            const icons = {
                'password_recovery': 'bi-key',
                'technical_issue': 'bi-exclamation-triangle',
                'general_support': 'bi-question-circle'
            };
            return icons[tipo] || 'bi-info-circle';
        }

        function formatTipo(tipo) {
            const tipos = {
                'password_recovery': 'Recuperar Contraseña',
                'technical_issue': 'Problema Técnico',
                'general_support': 'Soporte General'
            };
            return tipos[tipo] || tipo;
        }

        function getPrioridadClass(prioridad) {
            const classes = {
                'baja': 'bg-secondary',
                'media': 'bg-primary',
                'alta': 'bg-warning text-dark',
                'urgente': 'bg-danger'
            };
            return classes[prioridad] || 'bg-secondary';
        }

        function formatPrioridad(prioridad) {
            const prioridades = {
                'baja': 'Baja',
                'media': 'Media',
                'alta': 'Alta',
                'urgente': 'Urgente'
            };
            return prioridades[prioridad] || prioridad;
        }

        function getEstadoBadgeClass(estado) {
            const classes = {
                'pendiente': 'bg-warning text-dark',
                'en_proceso': 'bg-primary',
                'resuelto': 'bg-success',
                'cerrado': 'bg-secondary'
            };
            return classes[estado] || 'bg-secondary';
        }

        function formatEstado(estado) {
            const estados = {
                'pendiente': 'Pendiente',
                'en_proceso': 'En Proceso',
                'resuelto': 'Resuelto',
                'cerrado': 'Cerrado'
            };
            return estados[estado] || estado;
        }

        function formatFecha(fecha) {
            return new Date(fecha).toLocaleString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function actualizarEstadisticas() {
            const stats = {
                pendientes: ticketsData.filter(t => t.estado === 'pendiente').length,
                proceso: ticketsData.filter(t => t.estado === 'en_proceso').length,
                resueltos: ticketsData.filter(t => t.estado === 'resuelto' && isToday(t.fecha_actualizacion)).length,
                urgentes: ticketsData.filter(t => t.prioridad === 'urgente' && t.estado !== 'cerrado').length
            };

            document.getElementById('stat-pendientes').textContent = stats.pendientes;
            document.getElementById('stat-proceso').textContent = stats.proceso;
            document.getElementById('stat-resueltos').textContent = stats.resueltos;
            document.getElementById('stat-urgentes').textContent = stats.urgentes;
        }

        function isToday(dateString) {
            if (!dateString) return false;
            const today = new Date();
            const date = new Date(dateString);
            return date.toDateString() === today.toDateString();
        }

        function mostrarLoading(show) {
            document.getElementById('loadingTickets').style.display = show ? 'block' : 'none';
            document.getElementById('ticketsContainer').style.display = show ? 'none' : 'block';
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function limpiarFiltros() {
            document.getElementById('buscarTicket').value = '';
            document.getElementById('filtroEstado').value = '';
            document.getElementById('filtroTipo').value = '';
            document.getElementById('filtroPrioridad').value = '';
            aplicarFiltros();
        }

        // ========== FUNCIONES CRUD ==========
        async function crearTicket() {
            try {
                // Obtener datos del formulario
                const email = document.getElementById('nuevo_email').value.trim();
                const tipo = document.getElementById('nuevo_tipo').value;
                const asunto = document.getElementById('nuevo_asunto').value.trim();
                const descripcion = document.getElementById('nuevo_descripcion').value.trim();
                const prioridad = document.getElementById('nuevo_prioridad').value;

                // Validaciones
                if (!email || !tipo || !asunto || !descripcion) {
                    Swal.fire('Error', 'Todos los campos obligatorios deben estar completos', 'error');
                    return;
                }

                if (!email.includes('@')) {
                    Swal.fire('Error', 'Email inválido', 'error');
                    return;
                }

                // Deshabilitar botón
                const btnCrear = document.querySelector('#nuevoTicketModal .btn-primary');
                const originalText = btnCrear.innerHTML;
                btnCrear.disabled = true;
                btnCrear.innerHTML = '<i class="bi bi-spinner-border me-2"></i>Creando...';

                const response = await fetch('../../../Backend/api/SoporteTecnico/Metodos-soporte.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        motivo: descripcion,
                        tipo: tipo,
                        asunto: asunto,
                        prioridad: prioridad
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('¡Éxito!', 'Ticket creado exitosamente', 'success');
                    
                    // Cerrar modal y limpiar formulario
                    bootstrap.Modal.getInstance(document.getElementById('nuevoTicketModal')).hide();
                    document.getElementById('formNuevoTicket').reset();
                    
                    cargarTickets(); // Recargar lista
                } else {
                    throw new Error(data.message || 'Error al crear ticket');
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo crear el ticket: ' + error.message, 'error');
            } finally {
                // Restaurar botón
                const btnCrear = document.querySelector('#nuevoTicketModal .btn-primary');
                btnCrear.disabled = false;
                btnCrear.innerHTML = '<i class="bi bi-check me-2"></i>Crear Ticket';
            }
        }

        function verTicket(id) {
            const ticket = ticketsData.find(t => t.id === id);
            if (!ticket) return;

            // ✅ AGREGAR ID AL MODAL para uso en responderTicket()
            const modal = document.getElementById('verTicketModal');
            modal.dataset.ticketId = id;

            // Mostrar detalles del ticket en el modal
            document.getElementById('detallesTicket').innerHTML = `
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-info-circle me-2"></i>Información del Ticket #${ticket.id}
                        </h6>
                        <div class="card border-0 bg-light mb-3">
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <strong>📧 Email:</strong><br>
                                        <span class="text-muted">${ticket.email_solicitante}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>📋 Tipo:</strong><br>
                                        <span class="badge bg-info">${formatTipo(ticket.tipo)}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>⚡ Prioridad:</strong><br>
                                        <span class="badge ${getPrioridadClass(ticket.prioridad)}">${formatPrioridad(ticket.prioridad)}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>📊 Estado:</strong><br>
                                        <span class="badge ${getEstadoBadgeClass(ticket.estado)}">${formatEstado(ticket.estado)}</span>
                                    </div>
                                    <div class="col-12">
                                        <strong>📅 Fecha de creación:</strong><br>
                                        <span class="text-muted">${formatFecha(ticket.fecha_creacion)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-chat-dots me-2"></i>Consulta del Usuario
                        </h6>
                        <div class="alert alert-light border">
                            <p class="mb-0">${ticket.motivo_solicitud || ticket.descripcion || 'Sin descripción'}</p>
                        </div>
                        
                        ${ticket.respuesta_admin ? `
                            <h6 class="fw-bold text-success mb-3">
                                <i class="bi bi-reply me-2"></i>Tu Respuesta Anterior
                            </h6>
                            <div class="alert alert-success border-success">
                                <p class="mb-0">${ticket.respuesta_admin}</p>
                                ${ticket.fecha_respuesta ? `
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        Respondido el ${formatFecha(ticket.fecha_respuesta)}
                                    </small>
                                ` : ''}
                            </div>
                        ` : `
                            <div class="alert alert-warning border-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Este ticket aún no ha sido respondido</strong>
                            </div>
                        `}
                        
                        ${ticket.nueva_password_generada ? `
                            <h6 class="fw-bold text-warning mb-3">
                                <i class="bi bi-key me-2"></i>Contraseña Temporal Generada
                            </h6>
                            <div class="alert alert-warning border-warning">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Se ha generado una contraseña temporal para este usuario
                                <div class="mt-2">
                                    <code>${ticket.nueva_password_generada}</code>
                                    <button class="btn btn-sm btn-outline-warning ms-2" onclick="copiarTexto('${ticket.nueva_password_generada}')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-tools me-2"></i>Acciones Disponibles
                        </h6>
                        <div class="d-grid gap-2">
                            
                            <!-- ✅ BOTÓN RESPONDER MÁS VISIBLE -->
                            <button class="btn btn-primary btn-lg" onclick="responderTicket()" 
                                    ${ticket.estado === 'cerrado' ? 'disabled' : ''}>
                                <i class="bi bi-reply me-2"></i>
                                ${ticket.respuesta_admin ? 'Responder Nuevamente' : 'Responder al Usuario'}
                            </button>
                            
                            <hr class="my-2">
                            
                            ${ticket.tipo === 'password_recovery' && !ticket.nueva_password_generada ? `
                                <button class="btn btn-warning" onclick="generarPasswordTemporal('${ticket.email_solicitante}')">
                                    <i class="bi bi-key me-2"></i>Generar Contraseña
                                </button>
                            ` : ''}
                            
                            ${ticket.estado !== 'en_proceso' ? `
                                <button class="btn btn-info" onclick="cambiarEstado(${ticket.id}, 'en_proceso')">
                                    <i class="bi bi-gear me-2"></i>Marcar En Proceso
                                </button>
                            ` : ''}
                            
                            ${ticket.estado !== 'resuelto' ? `
                                <button class="btn btn-success" onclick="cambiarEstado(${ticket.id}, 'resuelto')">
                                    <i class="bi bi-check me-2"></i>Marcar como Resuelto
                                </button>
                            ` : ''}
                            
                            ${ticket.estado !== 'cerrado' ? `
                                <button class="btn btn-secondary" onclick="cambiarEstado(${ticket.id}, 'cerrado')">
                                    <i class="bi bi-x me-2"></i>Cerrar Ticket
                                </button>
                            ` : ''}
                            
                            <hr class="my-2">
                            
                            <button class="btn btn-outline-danger" onclick="eliminarTicket(${ticket.id})">
                                <i class="bi bi-trash me-2"></i>Eliminar Ticket
                            </button>
                        </div>
                        
                        <!-- ✅ NUEVA: Información del usuario -->
                        <div class="mt-4">
                            <h6 class="fw-bold text-muted mb-2">
                                <i class="bi bi-person me-2"></i>Información del Usuario
                            </h6>
                            <div class="card border-0 bg-light">
                                <div class="card-body p-2">
                                    <small class="text-muted">
                                        <strong>Email:</strong> ${ticket.email_solicitante}<br>
                                        <strong>Estado del ticket:</strong> ${formatEstado(ticket.estado)}<br>
                                        <strong>Prioridad:</strong> ${formatPrioridad(ticket.prioridad)}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        async function cambiarEstado(id, nuevoEstado) {
            try {
                const result = await Swal.fire({
                    title: '¿Cambiar Estado?',
                    text: `¿Estás seguro de cambiar el estado a "${formatEstado(nuevoEstado)}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar'
                });

                if (!result.isConfirmed) return;

                // Llamada real a la API
                const response = await fetch('../../../Backend/api/SoporteTecnico/Metodos-soporte.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: id,
                        estado: nuevoEstado,
                        atendido_por: 1 // ID del admin (obtener del localStorage)
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('¡Éxito!', `Estado cambiado a ${formatEstado(nuevoEstado)}`, 'success');
                    cargarTickets(); // Recargar lista
                } else {
                    throw new Error(data.message || 'Error al cambiar estado');
                }
                
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cambiar el estado: ' + error.message, 'error');
            }
        }

        async function generarPasswordTemporal(email) {
            try {
                // Generar contraseña temporal segura
                const passwordTemporal = generateSecurePassword();
                
                const result = await Swal.fire({
                    title: 'Generar Contraseña Temporal',
                    html: `
                        <div class="text-start">
                            <p><strong>Usuario:</strong> ${email}</p>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="tempPassword" value="${passwordTemporal}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                Esta contraseña se guardará en el sistema y se puede enviar al usuario
                            </small>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar y Generar',
                    cancelButtonText: 'Cancelar',
                    width: 500
                });

                if (result.isConfirmed) {
                    // Actualizar contraseña en la base de datos
                    const response = await fetch('../../../Backend/api/SoporteTecnico/Metodos-soporte.php', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: email,
                            nueva_password_generada: passwordTemporal,
                            accion: 'generar_password'
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            title: '¡Contraseña Generada!',
                            html: `
                                <div class="alert alert-success">
                                    <h6>Contraseña temporal creada exitosamente</h6>
                                    <p><strong>Usuario:</strong> ${email}</p>
                                    <p><strong>Nueva contraseña:</strong> <code>${passwordTemporal}</code></p>
                                    <hr>
                                    <small>La contraseña ha sido guardada en el sistema. Compártela de forma segura con el usuario.</small>
                                </div>
                            `,
                            icon: 'success'
                        });
                        
                        cargarTickets(); // Recargar para actualizar el estado
                    } else {
                        throw new Error(data.message || 'Error al guardar la contraseña');
                    }
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo generar la contraseña: ' + error.message, 'error');
            }
        }

        // Función auxiliar para generar contraseña segura
        function generateSecurePassword() {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            let password = '';
            for (let i = 0; i < 8; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return password;
        }

        // Función auxiliar para copiar al portapapeles
        function copyToClipboard() {
            const input = document.getElementById('tempPassword');
            input.select();
            document.execCommand('copy');
            
            // Mostrar feedback
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check text-success"></i>';
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 1500);
        }

        function actualizarPaginacion(totalItems) {
            const totalPaginas = Math.ceil(totalItems / itemsPorPagina);
            const paginacionContainer = document.getElementById('paginacion');
            
            // Actualizar información de resultados
            const inicio = (paginaActual - 1) * itemsPorPagina + 1;
            const fin = Math.min(paginaActual * itemsPorPagina, totalItems);
            document.getElementById('resultadosInfo').textContent = `${inicio}-${fin} de ${totalItems}`;
            
            // Limpiar paginación existente
            paginacionContainer.innerHTML = '';
            
            if (totalPaginas <= 1) return; // No mostrar paginación si solo hay 1 página
            
            // Botón anterior
            const prevLi = document.createElement('li');
            prevLi.className = `page-item ${paginaActual === 1 ? 'disabled' : ''}`;
            prevLi.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1})">
                <i class="bi bi-chevron-left"></i>
            </a>`;
            paginacionContainer.appendChild(prevLi);
            
            // Páginas
            const startPage = Math.max(1, paginaActual - 2);
            const endPage = Math.min(totalPaginas, paginaActual + 2);
            
            if (startPage > 1) {
                // Primera página
                const firstLi = document.createElement('li');
                firstLi.className = 'page-item';
                firstLi.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(1)">1</a>`;
                paginacionContainer.appendChild(firstLi);
                
                if (startPage > 2) {
                    // Puntos suspensivos
                    const dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = `<span class="page-link">...</span>`;
                    paginacionContainer.appendChild(dotsLi);
                }
            }
            
            // Páginas del rango
            for (let i = startPage; i <= endPage; i++) {
                const li = document.createElement('li');
                li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
                li.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>`;
                paginacionContainer.appendChild(li);
            }
            
            if (endPage < totalPaginas) {
                if (endPage < totalPaginas - 1) {
                    // Puntos suspensivos
                    const dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = `<span class="page-link">...</span>`;
                    paginacionContainer.appendChild(dotsLi);
                }
                
                // Última página
                const lastLi = document.createElement('li');
                lastLi.className = 'page-item';
                lastLi.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${totalPaginas})">${totalPaginas}</a>`;
                paginacionContainer.appendChild(lastLi);
            }
            
            // Botón siguiente
            const nextLi = document.createElement('li');
            nextLi.className = `page-item ${paginaActual === totalPaginas ? 'disabled' : ''}`;
            nextLi.innerHTML = `<a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1})">
                <i class="bi bi-chevron-right"></i>
            </a>`;
            paginacionContainer.appendChild(nextLi);
        }

        function cambiarPagina(nuevaPagina) {
            if (nuevaPagina < 1 || nuevaPagina === paginaActual) return;
            
            paginaActual = nuevaPagina;
            cargarTickets();
        }

        async function eliminarTicket(id) {
            try {
                const result = await Swal.fire({
                    title: '¿Eliminar Ticket?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                });

                if (!result.isConfirmed) return;

                const response = await fetch('../../../Backend/api/SoporteTecnico/Metodos-soporte.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('¡Eliminado!', 'Ticket eliminado exitosamente', 'success');
                    cargarTickets(); // Recargar lista
                } else {
                    throw new Error(data.message || 'Error al eliminar ticket');
                }
                
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo eliminar el ticket: ' + error.message, 'error');
            }
        }

        async function responderTicket() {
            try {
                // Obtener información del ticket
                const modal = document.getElementById('verTicketModal');
                const ticketId = modal.dataset.ticketId;
                
                if (!ticketId) {
                    Swal.fire('Error', 'No se pudo identificar el ticket', 'error');
                    return;
                }

                const ticket = ticketsData.find(t => t.id == ticketId);
                if (!ticket) {
                    Swal.fire('Error', 'Ticket no encontrado', 'error');
                    return;
                }

                console.log('📝 Respondiendo al ticket:', ticketId, ticket);

                // ✅ MODAL MEJORADO PARA RESPONDER
                const { value: formValues } = await Swal.fire({
                    title: `<i class="bi bi-reply me-2"></i>Responder al Usuario`,
                    html: `
                        <div class="text-start">
                            <div class="alert alert-info border-0 mb-3">
                                <small>
                                    <strong>📧 Para:</strong> ${ticket.email_solicitante}<br>
                                    <strong>📋 Ticket:</strong> #${ticket.id} - ${formatTipo(ticket.tipo)}<br>
                                    <strong>💬 Consulta:</strong> ${ticket.motivo_solicitud.substring(0, 100)}...
                                </small>
                            </div>
                            
                            ${ticket.respuesta_admin ? `
                                <div class="alert alert-warning border-0 mb-3">
                                    <small>
                                        <strong>⚠️ Respuesta anterior:</strong><br>
                                        "${ticket.respuesta_admin.substring(0, 150)}..."
                                    </small>
                                </div>
                            ` : ''}
                            
                            <label for="respuestaAdmin" class="form-label fw-bold">
                                <i class="bi bi-chat-dots me-1"></i>Tu Respuesta:
                            </label>
                            <textarea id="respuestaAdmin" class="form-control mb-3" rows="6" 
                                      placeholder="Escribe una respuesta clara y completa para ayudar al usuario..."></textarea>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="" id="marcarResuelto">
                                <label class="form-check-label" for="marcarResuelto">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Marcar como resuelto después de responder
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="enviarNotificacion" checked>
                                <label class="form-check-label" for="enviarNotificacion">
                                    <i class="bi bi-bell me-1"></i>
                                    Notificar al usuario por email (simulado)
                                </label>
                            </div>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-send me-2"></i>Enviar Respuesta',
                    cancelButtonText: '<i class="bi bi-x-lg me-2"></i>Cancelar',
                    width: 600,
                    preConfirm: () => {
                        const respuesta = document.getElementById('respuestaAdmin').value.trim();
                        const marcarResuelto = document.getElementById('marcarResuelto').checked;
                        const enviarNotificacion = document.getElementById('enviarNotificacion').checked;
                        
                        if (!respuesta) {
                            Swal.showValidationMessage('Por favor escribe una respuesta');
                            return false;
                        }
                        
                        if (respuesta.length < 10) {
                            Swal.showValidationMessage('La respuesta debe tener al menos 10 caracteres');
                            return false;
                        }
                        
                        return {
                            respuesta: respuesta,
                            marcarResuelto: marcarResuelto,
                            enviarNotificacion: enviarNotificacion
                        };
                    }
                });

                if (!formValues) return;

                console.log('📤 Enviando respuesta:', formValues);

                // ✅ ENVIAR RESPUESTA AL BACKEND
                const requestData = {
                    id: parseInt(ticketId),
                    respuesta_admin: formValues.respuesta,
                    estado: formValues.marcarResuelto ? 'resuelto' : 'en_proceso',
                    atendido_por: 1 // ID del admin (ajustar según tu sistema)
                };

                console.log('📡 Datos a enviar:', requestData);

                const response = await fetch('../../../Backend/api/SoporteTecnico/Metodos-soporte.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestData)
                });

                const data = await response.json();
                console.log('✅ Respuesta del servidor:', data);

                if (data.success) {
                    // ✅ ÉXITO CON MEJOR FEEDBACK
                    await Swal.fire({
                        icon: 'success',
                        title: '¡Respuesta Enviada!',
                        html: `
                            <div class="alert alert-success border-0">
                                <h6 class="mb-2">✅ Tu respuesta ha sido guardada exitosamente</h6>
                                <div class="text-start">
                                    <p class="mb-1"><strong>📧 Para:</strong> ${ticket.email_solicitante}</p>
                                    <p class="mb-1"><strong>📋 Ticket:</strong> #${ticket.id}</p>
                                    <p class="mb-1"><strong>📊 Nuevo estado:</strong> 
                                        <span class="badge ${formValues.marcarResuelto ? 'bg-success' : 'bg-primary'}">
                                            ${formValues.marcarResuelto ? 'Resuelto' : 'En Proceso'}
                                        </span>
                                    </p>
                                    ${formValues.enviarNotificacion ? 
                                        '<p class="mb-0"><strong>🔔 Notificación:</strong> El usuario será notificado</p>' : ''
                                    }
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'Entendido'
                    });

                    // Cerrar modal y recargar tickets
                    bootstrap.Modal.getInstance(modal).hide();
                    cargarTickets(); // Recargar lista para mostrar cambios
                    
                } else {
                    throw new Error(data.message || 'Error al enviar respuesta');
                }

            } catch (error) {
                console.error('❌ Error completo:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Enviar Respuesta',
                    text: 'No se pudo enviar la respuesta: ' + error.message,
                    footer: 'Verifica tu conexión y vuelve a intentarlo'
                });
            }
        }

        // ✅ FUNCIÓN AUXILIAR PARA COPIAR TEXTO
        function copiarTexto(texto) {
            navigator.clipboard.writeText(texto).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Copiado!',
                    text: 'Texto copiado al portapapeles',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }).catch(() => {
                // Fallback para navegadores más antiguos
                const input = document.createElement('input');
                input.value = texto;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                
                Swal.fire({
                    icon: 'success',
                    title: '¡Copiado!',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            });
        }
    </script>
</body>
</html>