<?php
// ========== VERIFICACIÓN DE AUTENTICACIÓN ==========
session_start();

// Verificar si hay datos de usuario en localStorage (será manejado por JavaScript)
// El PHP servirá la página y el JavaScript verificará la autenticación
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservas - RoomIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.9.0/dist/sweetalert2.min.css">
    <style>
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .table-container {
            min-height: 400px;
        }
        .reserva-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(45deg, #17a2b8, #138496);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .horario-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
        }
        .fecha-badge {
            background: linear-gradient(45deg, #6f42c1, #5a2d91);
            color: white;
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Gestión de Reservas</h2>
                <p class="text-muted mb-0">Administra reservas de salas del sistema RoomIT</p>
            </div>
            <button id="btnNuevaReserva" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearReservaModal">
                <i class="bi bi-plus-lg me-2"></i>Nueva Reserva
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Buscar reservas</label>
                        <input type="text" 
                               id="searchInput" 
                               class="form-control" 
                               placeholder="Buscar por usuario, email, sala, propósito...">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Busca por nombre de usuario, email, sala o propósito de la reserva
                        </small>
                    </div>
                    <div class="col-md-2">
                        <select id="filtroEstado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="cancelada">Cancelada</option>
                            <option value="completada">Completada</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="filtroSala" class="form-select">
                            <option value="">Todas las salas</option>
                            <!-- Se llenarán dinámicamente -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" id="filtroFecha" class="form-control" title="Filtrar por fecha">
                    </div>
                    <div class="col-md-2">
                        <button id="btnFiltrar" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-funnel me-2"></i>Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservas Table -->
        <div class="card">
            <div class="card-body table-container">
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando reservas...</p>
                </div>
                
                <div id="tableContainer" class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Sala</th>
                                <th>Fecha & Horario</th>
                                <th>Propósito</th>
                                <th>Estado</th>
                                <th>Notas</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="reservasTableBody">
                            <!-- Las reservas se cargarán dinámicamente aquí -->
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje cuando no hay reservas -->
                <div id="noResultsMessage" class="text-center py-4" style="display: none;">
                    <i class="bi bi-calendar-x fs-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No se encontraron reservas</h5>
                    <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                </div>

                <!-- Pagination -->
                <nav id="paginationContainer" class="mt-4">
                    <ul class="pagination justify-content-center" id="paginationList">
                        <!-- La paginación se generará dinámicamente -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Create Reserva Modal -->
    <div class="modal fade" id="crearReservaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nueva Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearReserva">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Usuario <span class="text-danger">*</span></label>
                                    <select id="crear_usuario_id" class="form-select" required>
                                        <option value="">Seleccionar usuario</option>
                                        <!-- Se llenarán dinámicamente -->
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Sala <span class="text-danger">*</span></label>
                                    <select id="crear_sala_id" class="form-select" required>
                                        <option value="">Seleccionar sala</option>
                                        <!-- Se llenarán dinámicamente -->
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Fecha de reserva <span class="text-danger">*</span></label>
                                    <input type="date" id="crear_fecha_reserva" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hora inicio <span class="text-danger">*</span></label>
                                    <input type="time" id="crear_hora_inicio" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hora fin <span class="text-danger">*</span></label>
                                    <input type="time" id="crear_hora_fin" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Propósito de la reserva <span class="text-danger">*</span></label>
                            <input type="text" id="crear_proposito" class="form-control" placeholder="Ej: Clase de Matemáticas, Reunión de departamento..." required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notas adicionales</label>
                            <textarea id="crear_notas" class="form-control" rows="3" placeholder="Observaciones, requerimientos especiales, etc."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado inicial</label>
                            <select id="crear_estado" class="form-select">
                                <option value="confirmada">Confirmada</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarReserva" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        Crear Reserva
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Reserva Modal -->
    <div class="modal fade" id="editarReservaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarReserva">
                        <input type="hidden" id="editar_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Usuario <span class="text-danger">*</span></label>
                                    <select id="editar_usuario_id" class="form-select" required>
                                        <option value="">Seleccionar usuario</option>
                                        <!-- Se llenarán dinámicamente -->
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Sala <span class="text-danger">*</span></label>
                                    <select id="editar_sala_id" class="form-select" required>
                                        <option value="">Seleccionar sala</option>
                                        <!-- Se llenarán dinámicamente -->
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Fecha de reserva <span class="text-danger">*</span></label>
                                    <input type="date" id="editar_fecha_reserva" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hora inicio <span class="text-danger">*</span></label>
                                    <input type="time" id="editar_hora_inicio" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hora fin <span class="text-danger">*</span></label>
                                    <input type="time" id="editar_hora_fin" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Propósito de la reserva <span class="text-danger">*</span></label>
                            <input type="text" id="editar_proposito" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notas adicionales</label>
                            <textarea id="editar_notas" class="form-control" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado <span class="text-danger">*</span></label>
                            <select id="editar_estado" class="form-select" required>
                                <option value="confirmada">Confirmada</option>
                                <option value="cancelada">Cancelada</option>
                                <option value="completada">Completada</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnActualizarReserva" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.9.0/dist/sweetalert2.all.min.js"></script>
    
    <script>
        // ========== VARIABLES GLOBALES ==========
        let currentPage = 1;
        let totalPages = 1;
        let isLoading = false;
        const itemsPerPage = 10;

        // ========== ELEMENTOS DOM ==========
        const searchInput = document.getElementById('searchInput');
        const filtroEstado = document.getElementById('filtroEstado');
        const filtroSala = document.getElementById('filtroSala');
        const filtroFecha = document.getElementById('filtroFecha');
        const btnFiltrar = document.getElementById('btnFiltrar');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const tableContainer = document.getElementById('tableContainer');
        const reservasTableBody = document.getElementById('reservasTableBody');
        const noResultsMessage = document.getElementById('noResultsMessage');
        const paginationContainer = document.getElementById('paginationContainer');

        // ========== FUNCIONES PRINCIPALES ==========
        
        // Cargar reservas
        async function cargarReservas(page = 1, search = '', estado = '', sala_id = '', fecha = '') {
            if (isLoading) return;
            
            isLoading = true;
            mostrarLoading();

            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: itemsPerPage,
                    search: search,
                    estado: estado,
                    sala_id: sala_id,
                    fecha: fecha
                });

                const response = await fetch(`../../../Backend/api/reservas/Metodos-Reservas.php?${params}`);
                
                if (!response.ok) {
                    throw new Error('Error al cargar las reservas');
                }

                const data = await response.json();
                
                if (data.success) {
                    mostrarReservas(data.data);
                    actualizarPaginacion(data.pagination);
                    
                    // Actualizar contador de resultados
                    actualizarContadorResultados(data.total || 0, data.data?.length || 0);
                } else {
                    throw new Error(data.message || 'Error al cargar las reservas');
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error al cargar las reservas');
                mostrarSinResultados();
            } finally {
                isLoading = false;
                ocultarLoading();
            }
        }

        // Mostrar reservas en la tabla
        function mostrarReservas(reservas) {
            if (!reservas || reservas.length === 0) {
                mostrarSinResultados();
                return;
            }

            reservasTableBody.innerHTML = reservas.map(reserva => `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="reserva-avatar me-3" style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #007bff, #0056b3); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                                ${reserva.usuario_nombre.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark">${reserva.usuario_nombre}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-envelope me-1"></i>${reserva.usuario_email}
                                </small>
                                <div class="mt-1">
                                    <span class="badge bg-light text-dark small">
                                        <i class="bi bi-person me-1"></i>Usuario ID: ${reserva.usuario_id}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <span class="fw-bold text-primary">${reserva.sala_nombre}</span>
                            <br>
                            <small class="text-muted">
                                <i class="bi ${getSalaTipoIcon(reserva.sala_tipo)} me-1"></i>
                                ${reserva.sala_tipo.charAt(0).toUpperCase() + reserva.sala_tipo.slice(1)}
                            </small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <span class="fecha-badge fw-semibold text-dark">${formatearFecha(reserva.fecha_reserva)}</span>
                            <br>
                            <small class="horario-badge bg-light text-dark mt-1 px-2 py-1 rounded">
                                <i class="bi bi-clock me-1"></i>
                                ${reserva.hora_inicio} - ${reserva.hora_fin}
                            </small>
                        </div>
                    </td>
                    <td>
                        <span class="text-dark fw-semibold">${reserva.proposito}</span>
                        ${reserva.fecha_creacion ? `
                            <br><small class="text-muted">
                                <i class="bi bi-calendar-plus me-1"></i>
                                Creada: ${new Date(reserva.fecha_creacion).toLocaleDateString('es-ES')}
                            </small>
                        ` : ''}
                    </td>
                    <td>
                        <span class="badge bg-${getEstadoColor(reserva.estado)} px-3 py-2">
                            <i class="bi ${getEstadoIcon(reserva.estado)} me-1"></i>
                            ${reserva.estado.charAt(0).toUpperCase() + reserva.estado.slice(1)}
                        </span>
                    </td>
                    <td>
                        <span class="text-muted small">${reserva.notas || 'Sin notas adicionales'}</span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="editarReserva(${reserva.id})" title="Editar reserva">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="verDetallesReservaAdmin(${reserva.id})" title="Ver detalles completos">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="eliminarReserva(${reserva.id}, '${reserva.proposito}')" title="Eliminar reserva">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            tableContainer.style.display = 'block';
            noResultsMessage.style.display = 'none';
        }

        // Funciones auxiliares para iconos y colores
        function getSalaTipoIcon(tipo) {
            const icons = {
                'aula': 'bi-door-open',
                'laboratorio': 'bi-pc-display',
                'auditorio': 'bi-building'
            };
            return icons[tipo] || 'bi-building';
        }

        function getEstadoIcon(estado) {
            const icons = {
                'confirmada': 'bi-check-circle',
                'cancelada': 'bi-x-circle',
                'completada': 'bi-check-circle-fill'
            };
            return icons[estado] || 'bi-circle';
        }

        function getEstadoColor(estado) {
            const colors = {
                'confirmada': 'success',
                'cancelada': 'danger',
                'completada': 'primary'
            };
            return colors[estado] || 'secondary';
        }

        function formatearFecha(fecha) {
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        // Mostrar/ocultar loading
        function mostrarLoading() {
            loadingSpinner.style.display = 'block';
            tableContainer.style.display = 'none';
            noResultsMessage.style.display = 'none';
        }

        function ocultarLoading() {
            loadingSpinner.style.display = 'none';
        }

        function mostrarSinResultados() {
            tableContainer.style.display = 'none';
            noResultsMessage.style.display = 'block';
        }

        function mostrarError(mensaje) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                confirmButtonColor: '#dc3545'
            });
        }

        // Paginación
        function actualizarPaginacion(pagination) {
            if (!pagination) return;

            currentPage = pagination.current_page;
            totalPages = pagination.total_pages;

            const paginationList = document.getElementById('paginationList');
            
            if (totalPages <= 1) {
                paginationContainer.style.display = 'none';
                return;
            }

            paginationContainer.style.display = 'block';
            
            let paginationHTML = '';

            // Botón anterior
            paginationHTML += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPagina(${currentPage - 1})">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
            `;

            // Números de página
            for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                paginationHTML += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>
                    </li>
                `;
            }

            // Botón siguiente
            paginationHTML += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPagina(${currentPage + 1})">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            `;

            paginationList.innerHTML = paginationHTML;
        }

        function cambiarPagina(page) {
            if (page < 1 || page > totalPages || page === currentPage) return;
            
            const search = searchInput.value.trim();
            const estado = filtroEstado.value;
            const sala_id = filtroSala.value;
            const fecha = filtroFecha.value;
            
            cargarReservas(page, search, estado, sala_id, fecha);
        }

        // ========== CARGAR DATOS PARA SELECTS ==========
        async function cargarUsuarios() {
            try {
                const response = await fetch('../../../Backend/api/Usuarios/Metodos-Usuario.php');
                const data = await response.json();
                
                if (data.success && data.data) {
                    const usuarios = data.data;
                    const selectsUsuarios = ['crear_usuario_id', 'editar_usuario_id'];
                    
                    selectsUsuarios.forEach(selectId => {
                        const select = document.getElementById(selectId);
                        select.innerHTML = '<option value="">Seleccionar usuario</option>';
                        usuarios.forEach(usuario => {
                            select.innerHTML += `<option value="${usuario.id}">${usuario.nombre} (${usuario.email})</option>`;
                        });
                    });
                }
            } catch (error) {
                console.error('Error al cargar usuarios:', error);
            }
        }

        async function cargarSalas() {
            try {
                const response = await fetch('../../../Backend/api/Salas/Metodos-Salas.php');
                const data = await response.json();
                
                if (data.success && data.data) {
                    const salas = data.data;
                    const selectsSalas = ['crear_sala_id', 'editar_sala_id', 'filtroSala'];
                    
                    selectsSalas.forEach(selectId => {
                        const select = document.getElementById(selectId);
                        const isFilter = selectId === 'filtroSala';
                        select.innerHTML = isFilter ? '<option value="">Todas las salas</option>' : '<option value="">Seleccionar sala</option>';
                        
                        salas.forEach(sala => {
                            select.innerHTML += `<option value="${sala.id}">${sala.nombre} (${sala.tipo})</option>`;
                        });
                    });
                }
            } catch (error) {
                console.error('Error al cargar salas:', error);
            }
        }

        // ========== CREAR RESERVA ==========
        async function crearReserva() {
            const formData = {
                usuario_id: parseInt(document.getElementById('crear_usuario_id').value),
                sala_id: parseInt(document.getElementById('crear_sala_id').value),
                fecha_reserva: document.getElementById('crear_fecha_reserva').value,
                hora_inicio: document.getElementById('crear_hora_inicio').value,
                hora_fin: document.getElementById('crear_hora_fin').value,
                proposito: document.getElementById('crear_proposito').value.trim(),
                notas: document.getElementById('crear_notas').value.trim(),
                estado: document.getElementById('crear_estado').value
            };

            // Validaciones básicas
            if (!formData.usuario_id || !formData.sala_id || !formData.fecha_reserva || 
                !formData.hora_inicio || !formData.hora_fin || !formData.proposito) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor complete todos los campos obligatorios'
                });
                return;
            }

            // Validar que hora fin sea mayor que hora inicio
            if (formData.hora_fin <= formData.hora_inicio) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Horario inválido',
                    text: 'La hora de fin debe ser posterior a la hora de inicio'
                });
                return;
            }

            const btnGuardar = document.getElementById('btnGuardarReserva');
            const spinner = btnGuardar.querySelector('.spinner-border');

            try {
                btnGuardar.disabled = true;
                spinner.classList.remove('d-none');

                const response = await fetch('../../../Backend/api/reservas/Metodos-Reservas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Reserva creada exitosamente',
                        confirmButtonColor: '#198754'
                    });

                    // Cerrar modal y limpiar formulario
                    bootstrap.Modal.getInstance(document.getElementById('crearReservaModal')).hide();
                    document.getElementById('formCrearReserva').reset();

                    // Recargar tabla
                    cargarReservas(1);
                } else {
                    throw new Error(data.message || 'Error al crear la reserva');
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error al crear la reserva'
                });
            } finally {
                btnGuardar.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // ========== EDITAR RESERVA ==========
        async function editarReserva(id) {
            try {
                const response = await fetch(`../../../Backend/api/reservas/Metodos-Reservas.php?id=${id}`);
                const data = await response.json();

                if (data.success && data.data) {
                    const reserva = data.data;

                    // Llenar el formulario de edición
                    document.getElementById('editar_id').value = reserva.id;
                    document.getElementById('editar_usuario_id').value = reserva.usuario_id;
                    document.getElementById('editar_sala_id').value = reserva.sala_id;
                    document.getElementById('editar_fecha_reserva').value = reserva.fecha_reserva;
                    document.getElementById('editar_hora_inicio').value = reserva.hora_inicio;
                    document.getElementById('editar_hora_fin').value = reserva.hora_fin;
                    document.getElementById('editar_proposito').value = reserva.proposito;
                    document.getElementById('editar_notas').value = reserva.notas || '';
                    document.getElementById('editar_estado').value = reserva.estado;

                    // Mostrar modal
                    new bootstrap.Modal(document.getElementById('editarReservaModal')).show();
                } else {
                    throw new Error('No se pudo cargar la información de la reserva');
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error al cargar los datos de la reserva');
            }
        }

        async function actualizarReserva() {
            const id = document.getElementById('editar_id').value;
            const formData = {
                id: parseInt(id),
                usuario_id: parseInt(document.getElementById('editar_usuario_id').value),
                sala_id: parseInt(document.getElementById('editar_sala_id').value),
                fecha_reserva: document.getElementById('editar_fecha_reserva').value,
                hora_inicio: document.getElementById('editar_hora_inicio').value,
                hora_fin: document.getElementById('editar_hora_fin').value,
                proposito: document.getElementById('editar_proposito').value.trim(),
                notas: document.getElementById('editar_notas').value.trim(),
                estado: document.getElementById('editar_estado').value
            };

            // Validar que hora fin sea mayor que hora inicio
            if (formData.hora_fin <= formData.hora_inicio) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Horario inválido',
                    text: 'La hora de fin debe ser posterior a la hora de inicio'
                });
                return;
            }

            const btnActualizar = document.getElementById('btnActualizarReserva');
            const spinner = btnActualizar.querySelector('.spinner-border');

            try {
                btnActualizar.disabled = true;
                spinner.classList.remove('d-none');

                const response = await fetch('../../../Backend/api/reservas/Metodos-Reservas.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Reserva actualizada exitosamente'
                    });

                    // Cerrar modal
                    bootstrap.Modal.getInstance(document.getElementById('editarReservaModal')).hide();

                    // Recargar tabla
                    cargarReservas(currentPage);
                } else {
                    throw new Error(data.message || 'Error al actualizar la reserva');
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError(error.message || 'Error al actualizar la reserva');
            } finally {
                btnActualizar.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // ========== ELIMINAR RESERVA ==========
        async function eliminarReserva(id, proposito) {
            const result = await Swal.fire({
                title: '¿Estás seguro?',
                text: `Se eliminará la reserva "${proposito}" permanentemente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('../../../Backend/api/reservas/Metodos-Reservas.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: id })
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: 'La reserva ha sido eliminada exitosamente'
                        });

                        // Recargar tabla
                        cargarReservas(currentPage);
                    } else {
                        throw new Error(data.message || 'Error al eliminar la reserva');
                    }

                } catch (error) {
                    console.error('Error:', error);
                    mostrarError(error.message || 'Error al eliminar la reserva');
                }
            }
        }

        // ========== VER DETALLES COMPLETOS DE RESERVA (ADMIN) ==========
        async function verDetallesReservaAdmin(reservaId) {
            try {
                const response = await fetch(`../../../Backend/api/reservas/Metodos-Reservas.php?id=${reservaId}`);
                const data = await response.json();

                if (data.success && data.data) {
                    const reserva = data.data;
                    
                    Swal.fire({
                        title: 'Detalles Completos de la Reserva',
                        html: `
                            <div class="text-start">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card bg-light border-0">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary mb-3">
                                                    <i class="bi bi-person-circle me-2"></i>Información del Usuario
                                                </h6>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <strong>Nombre:</strong><br>
                                                        <span class="text-dark">${reserva.usuario_nombre}</span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <strong>Email:</strong><br>
                                                        <span class="text-dark">${reserva.usuario_email}</span>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-sm-6">
                                                        <strong>ID Usuario:</strong><br>
                                                        <span class="badge bg-secondary">${reserva.usuario_id}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <strong>ID Reserva:</strong><br>
                                        <span class="badge bg-primary">#${reserva.id}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Estado:</strong><br>
                                        <span class="badge bg-${getEstadoColor(reserva.estado)}">
                                            <i class="bi ${getEstadoIcon(reserva.estado)} me-1"></i>
                                            ${reserva.estado.charAt(0).toUpperCase() + reserva.estado.slice(1)}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <strong>Sala:</strong><br>
                                        <span class="text-dark">${reserva.sala_nombre}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Tipo de Sala:</strong><br>
                                        <span class="text-dark">${reserva.sala_tipo}</span>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <strong>Fecha:</strong><br>
                                        <span class="text-dark">${formatearFecha(reserva.fecha_reserva)}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Horario:</strong><br>
                                        <span class="text-dark">${reserva.hora_inicio} - ${reserva.hora_fin}</span>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <strong>Propósito de la Reserva:</strong><br>
                                        <div class="bg-light p-2 rounded mt-1">
                                            ${reserva.proposito}
                                        </div>
                                    </div>
                                </div>
                                
                                ${reserva.notas ? `
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <strong>Notas Adicionales:</strong><br>
                                            <div class="bg-light p-2 rounded mt-1">
                                                ${reserva.notas}
                                            </div>
                                        </div>
                                    </div>
                                ` : ''}
                                
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <strong>Fecha de Creación:</strong><br>
                                        <span class="text-muted small">
                                            ${reserva.fecha_creacion ? new Date(reserva.fecha_creacion).toLocaleString('es-ES') : 'No disponible'}
                                        </span>
                                    </div>
                                    ${reserva.fecha_actualizacion ? `
                                        <div class="col-sm-6">
                                            <strong>Última Actualización:</strong><br>
                                            <span class="text-muted small">
                                                ${new Date(reserva.fecha_actualizacion).toLocaleString('es-ES')}
                                            </span>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        `,
                        width: 700,
                        confirmButtonText: 'Cerrar',
                        showCancelButton: true,
                        cancelButtonText: 'Editar Reserva',
                        cancelButtonColor: '#0d6efd',
                        confirmButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.cancel) {
                            // Si hace clic en "Editar Reserva"
                            editarReserva(reservaId);
                        }
                    });
                } else {
                    throw new Error('No se pudo cargar la información de la reserva');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los detalles de la reserva: ' + error.message
                });
            }
        }

        function actualizarContadorResultados(total, mostrados) {
            const contador = document.getElementById('contador-resultados');
            if (contador) {
                contador.textContent = `Mostrando ${mostrados} de ${total} reservas`;
            }
        }

        // ========== EVENT LISTENERS ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar datos iniciales
            cargarReservas();
            cargarUsuarios();
            cargarSalas();

            // Búsqueda en tiempo real
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    cargarReservas(1, this.value.trim(), filtroEstado.value, filtroSala.value, filtroFecha.value);
                }, 500);
            });

            // Filtros
            btnFiltrar.addEventListener('click', function() {
                const search = searchInput.value.trim();
                const estado = filtroEstado.value;
                const sala_id = filtroSala.value;
                const fecha = filtroFecha.value;
                cargarReservas(1, search, estado, sala_id, fecha);
            });

            // Enter en búsqueda
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const search = this.value.trim();
                    const estado = filtroEstado.value;
                    const sala_id = filtroSala.value;
                    const fecha = filtroFecha.value;
                    cargarReservas(1, search, estado, sala_id, fecha);
                }
            });

            // Cambio en filtros
            [filtroEstado, filtroSala, filtroFecha].forEach(filter => {
                filter.addEventListener('change', function() {
                    const search = searchInput.value.trim();
                    const estado = filtroEstado.value;
                    const sala_id = filtroSala.value;
                    const fecha = filtroFecha.value;
                    cargarReservas(1, search, estado, sala_id, fecha);
                });
            });

            // Botones de modales
            document.getElementById('btnGuardarReserva').addEventListener('click', crearReserva);
            document.getElementById('btnActualizarReserva').addEventListener('click', actualizarReserva);
        });
    </script>
</body>
</html>
