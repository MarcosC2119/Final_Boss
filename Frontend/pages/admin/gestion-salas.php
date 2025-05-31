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
    <title>Gestión de Salas - RoomIT</title>
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
        .sala-icon {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        .tipo-aula { background: linear-gradient(45deg, #007bff, #0056b3); }
        .tipo-laboratorio { background: linear-gradient(45deg, #28a745, #1e7e34); }
        .tipo-auditorio { background: linear-gradient(45deg, #6610f2, #520dc2); }
        .badge-equipamiento {
            font-size: 0.7rem;
            margin: 1px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Gestión de Salas</h2>
                <p class="text-muted mb-0">Administra salas del sistema RoomIT</p>
            </div>
            <button id="btnNuevaSala" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearSalaModal">
                <i class="bi bi-plus-lg me-2"></i>Nueva Sala
            </button>
        </div>

        <!-- Search and Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre de sala...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="filtroTipo" class="form-select">
                            <option value="">Todos los tipos</option>
                            <option value="aula">Aula</option>
                            <option value="laboratorio">Laboratorio</option>
                            <option value="auditorio">Auditorio</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filtroEstado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="disponible">Disponible</option>
                            <option value="ocupada">Ocupada</option>
                            <option value="mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button id="btnFiltrar" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-funnel me-2"></i>Filtrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salas Table -->
        <div class="card">
            <div class="card-body table-container">
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando salas...</p>
                </div>
                
                <div id="tableContainer" class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Sala</th>
                                <th>Tipo</th>
                                <th>Capacidad</th>
                                <th>Equipamiento</th>
                                <th>Estado</th>
                                <th>Descripción</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="salasTableBody">
                            <!-- Las salas se cargarán dinámicamente aquí -->
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje cuando no hay salas -->
                <div id="noResultsMessage" class="text-center py-4" style="display: none;">
                    <i class="bi bi-building-x fs-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No se encontraron salas</h5>
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

    <!-- Create Sala Modal -->
    <div class="modal fade" id="crearSalaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nueva Sala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearSala">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombre de la sala <span class="text-danger">*</span></label>
                                    <input type="text" id="crear_nombre" class="form-control" placeholder="Ej: Aula 201" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Capacidad <span class="text-danger">*</span></label>
                                    <input type="number" id="crear_capacidad" class="form-control" min="1" max="500" placeholder="30" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                    </div>

                        <div class="row">
                            <div class="col-md-6">
                            <div class="mb-3">
                                    <label class="form-label">Tipo de sala <span class="text-danger">*</span></label>
                                    <select id="crear_tipo" class="form-select" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="aula">Aula</option>
                                        <option value="laboratorio">Laboratorio</option>
                                        <option value="auditorio">Auditorio</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                            <div class="mb-3">
                                    <label class="form-label">Estado inicial</label>
                                    <select id="crear_estado" class="form-select">
                                        <option value="disponible">Disponible</option>
                                        <option value="mantenimiento">Mantenimiento</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                </div>
                            </div>

                        <!-- Equipamiento -->
                            <div class="mb-3">
                            <label class="form-label">Equipamiento disponible</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="crear_proyector">
                                        <label class="form-check-label" for="crear_proyector">
                                            <i class="bi bi-projector me-1"></i>Proyector
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="crear_pizarra_digital">
                                        <label class="form-check-label" for="crear_pizarra_digital">
                                            <i class="bi bi-display me-1"></i>Pizarra Digital
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="crear_accesible">
                                        <label class="form-check-label" for="crear_accesible">
                                            <i class="bi bi-person-wheelchair me-1"></i>Accesible
                                        </label>
                                    </div>
                                </div>
                                </div>
                            </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea id="crear_descripcion" class="form-control" rows="3" placeholder="Descripción adicional de la sala..."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarSala" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        Crear Sala
                                </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Sala Modal -->
    <div class="modal fade" id="editarSalaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Sala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarSala">
                        <input type="hidden" id="editar_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombre de la sala <span class="text-danger">*</span></label>
                                    <input type="text" id="editar_nombre" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Capacidad <span class="text-danger">*</span></label>
                                    <input type="number" id="editar_capacidad" class="form-control" min="1" max="500" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tipo de sala <span class="text-danger">*</span></label>
                                    <select id="editar_tipo" class="form-select" required>
                                        <option value="aula">Aula</option>
                                        <option value="laboratorio">Laboratorio</option>
                                        <option value="auditorio">Auditorio</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select id="editar_estado" class="form-select" required>
                                        <option value="disponible">Disponible</option>
                                        <option value="ocupada">Ocupada</option>
                                        <option value="mantenimiento">Mantenimiento</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Equipamiento -->
                        <div class="mb-3">
                            <label class="form-label">Equipamiento disponible</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editar_proyector">
                                        <label class="form-check-label" for="editar_proyector">
                                            <i class="bi bi-projector me-1"></i>Proyector
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editar_pizarra_digital">
                                        <label class="form-check-label" for="editar_pizarra_digital">
                                            <i class="bi bi-display me-1"></i>Pizarra Digital
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editar_accesible">
                                        <label class="form-check-label" for="editar_accesible">
                                            <i class="bi bi-person-wheelchair me-1"></i>Accesible
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea id="editar_descripcion" class="form-control" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                            </div>
                        </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnActualizarSala" class="btn btn-primary">
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
        const filtroTipo = document.getElementById('filtroTipo');
        const filtroEstado = document.getElementById('filtroEstado');
        const btnFiltrar = document.getElementById('btnFiltrar');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const tableContainer = document.getElementById('tableContainer');
        const salasTableBody = document.getElementById('salasTableBody');
        const noResultsMessage = document.getElementById('noResultsMessage');
        const paginationContainer = document.getElementById('paginationContainer');

        // ========== FUNCIONES PRINCIPALES ==========
        
        // Cargar salas
        async function cargarSalas(page = 1, search = '', tipo = '', estado = '') {
            console.log('🔍 INICIANDO cargarSalas con parámetros:', { page, search, tipo, estado });
            
            if (isLoading) {
                console.log('⏳ Ya está cargando, saliendo...');
                return;
            }
            
            isLoading = true;
            mostrarLoading();

            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: itemsPerPage,
                    search: search,
                    tipo: tipo,
                    estado: estado
                });

                const url = `../../../Backend/api/Salas/Metodos-Salas.php?${params}`;
                console.log('🌐 URL construida:', url);

                const response = await fetch(url);
                console.log('📡 Respuesta recibida:', response);
                console.log('📊 Status:', response.status);
                console.log('✅ OK?:', response.ok);
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                }

                const data = await response.json();
                console.log('📦 Datos parseados:', data);
                
                if (data.success) {
                    console.log('✅ Data.success es true');
                    console.log('📋 Salas recibidas:', data.data);
                    console.log('📄 Paginación:', data.pagination);
                    
                    mostrarSalas(data.data);
                    actualizarPaginacion(data.pagination);
                } else {
                    console.error('❌ Data.success es false:', data);
                    throw new Error(data.message || 'Error al cargar las salas');
                }

            } catch (error) {
                console.error('💥 Error capturado:', error);
                mostrarError('Error al cargar las salas: ' + error.message);
                mostrarSinResultados();
            } finally {
                isLoading = false;
                ocultarLoading();
                console.log('�� cargarSalas terminado');
            }
        }

        // Mostrar salas en la tabla
        function mostrarSalas(salas) {
            if (!salas || salas.length === 0) {
                mostrarSinResultados();
                return;
            }

            salasTableBody.innerHTML = salas.map(sala => `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="sala-icon tipo-${sala.tipo} me-3">
                                <i class="bi ${getTipoIcon(sala.tipo)}"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">${sala.nombre}</h6>
                                <small class="text-muted">ID: ${sala.id}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-${getTipoColor(sala.tipo)} bg-opacity-10 text-${getTipoColor(sala.tipo)}">
                            <i class="bi ${getTipoIcon(sala.tipo)} me-1"></i>
                            ${sala.tipo.charAt(0).toUpperCase() + sala.tipo.slice(1)}
                        </span>
                    </td>
                    <td>
                        <span class="fw-bold">${sala.capacidad}</span>
                        <small class="text-muted"> personas</small>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            ${sala.tiene_proyector ? '<span class="badge badge-equipamiento bg-primary">Proyector</span>' : ''}
                            ${sala.tiene_pizarra_digital ? '<span class="badge badge-equipamiento bg-success">Pizarra Digital</span>' : ''}
                            ${sala.es_accesible ? '<span class="badge badge-equipamiento bg-info">Accesible</span>' : ''}
                            ${!sala.tiene_proyector && !sala.tiene_pizarra_digital && !sala.es_accesible ? '<span class="text-muted small">Sin equipamiento</span>' : ''}
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-${getEstadoColor(sala.estado)}">
                            <i class="bi ${getEstadoIcon(sala.estado)} me-1"></i>
                            ${sala.estado.charAt(0).toUpperCase() + sala.estado.slice(1)}
                        </span>
                    </td>
                    <td>
                        <span class="text-muted">${sala.descripcion || 'Sin descripción'}</span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="editarSala(${sala.id})" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="eliminarSala(${sala.id}, '${sala.nombre}')" title="Eliminar">
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
        function getTipoIcon(tipo) {
            const icons = {
                'aula': 'bi-door-open',
                'laboratorio': 'bi-pc-display',
                'auditorio': 'bi-building'
            };
            return icons[tipo] || 'bi-building';
        }

        function getTipoColor(tipo) {
            const colors = {
                'aula': 'primary',
                'laboratorio': 'success',
                'auditorio': 'secondary'
            };
            return colors[tipo] || 'secondary';
        }

        function getEstadoIcon(estado) {
            const icons = {
                'disponible': 'bi-check-circle',
                'ocupada': 'bi-person-fill',
                'mantenimiento': 'bi-tools'
            };
            return icons[estado] || 'bi-circle';
        }

        function getEstadoColor(estado) {
            const colors = {
                'disponible': 'success',
                'ocupada': 'warning',
                'mantenimiento': 'danger'
            };
            return colors[estado] || 'secondary';
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
            const tipo = filtroTipo.value;
            const estado = filtroEstado.value;
            
            cargarSalas(page, search, tipo, estado);
        }

        // ========== CREAR SALA ==========
        async function crearSala() {
            const formData = {
                nombre: document.getElementById('crear_nombre').value.trim(),
                capacidad: parseInt(document.getElementById('crear_capacidad').value),
                tipo: document.getElementById('crear_tipo').value,
                estado: document.getElementById('crear_estado').value,
                tiene_proyector: document.getElementById('crear_proyector').checked,
                tiene_pizarra_digital: document.getElementById('crear_pizarra_digital').checked,
                es_accesible: document.getElementById('crear_accesible').checked,
                descripcion: document.getElementById('crear_descripcion').value.trim()
            };

            // Validaciones
            if (!formData.nombre || !formData.tipo || !formData.capacidad) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor complete todos los campos obligatorios'
                });
                return;
            }

            const btnGuardar = document.getElementById('btnGuardarSala');
            const spinner = btnGuardar.querySelector('.spinner-border');

            try {
                btnGuardar.disabled = true;
                spinner.classList.remove('d-none');

                const response = await fetch('../../../Backend/api/Salas/Metodos-Salas.php', {
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
                        text: 'Sala creada exitosamente',
                        confirmButtonColor: '#198754'
                    });

                    // Cerrar modal y limpiar formulario
                    bootstrap.Modal.getInstance(document.getElementById('crearSalaModal')).hide();
                    document.getElementById('formCrearSala').reset();

                    // Recargar tabla
                    cargarSalas(1);
                } else {
                    throw new Error(data.message || 'Error al crear la sala');
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error al crear la sala'
                });
            } finally {
                btnGuardar.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // ========== EDITAR SALA ==========
        async function editarSala(id) {
            try {
                const response = await fetch(`../../../Backend/api/Salas/Metodos-Salas.php?id=${id}`);
                const data = await response.json();

                if (data.success && data.data) {
                    const sala = data.data;

                    // Llenar el formulario de edición
                    document.getElementById('editar_id').value = sala.id;
                    document.getElementById('editar_nombre').value = sala.nombre;
                    document.getElementById('editar_capacidad').value = sala.capacidad;
                    document.getElementById('editar_tipo').value = sala.tipo;
                    document.getElementById('editar_estado').value = sala.estado;
                    document.getElementById('editar_proyector').checked = Boolean(sala.tiene_proyector);
                    document.getElementById('editar_pizarra_digital').checked = Boolean(sala.tiene_pizarra_digital);
                    document.getElementById('editar_accesible').checked = Boolean(sala.es_accesible);
                    document.getElementById('editar_descripcion').value = sala.descripcion || '';

                    // Mostrar modal
                    new bootstrap.Modal(document.getElementById('editarSalaModal')).show();
                } else {
                    throw new Error('No se pudo cargar la información de la sala');
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error al cargar los datos de la sala');
            }
        }

        async function actualizarSala() {
            const id = document.getElementById('editar_id').value;
            const formData = {
                id: parseInt(id),
                nombre: document.getElementById('editar_nombre').value.trim(),
                capacidad: parseInt(document.getElementById('editar_capacidad').value),
                tipo: document.getElementById('editar_tipo').value,
                estado: document.getElementById('editar_estado').value,
                tiene_proyector: document.getElementById('editar_proyector').checked,
                tiene_pizarra_digital: document.getElementById('editar_pizarra_digital').checked,
                es_accesible: document.getElementById('editar_accesible').checked,
                descripcion: document.getElementById('editar_descripcion').value.trim()
            };

            const btnActualizar = document.getElementById('btnActualizarSala');
            const spinner = btnActualizar.querySelector('.spinner-border');

            try {
                btnActualizar.disabled = true;
                spinner.classList.remove('d-none');

                const response = await fetch('../../../Backend/api/Salas/Metodos-Salas.php', {
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
                        text: 'Sala actualizada exitosamente'
                    });

                    // Cerrar modal
                    bootstrap.Modal.getInstance(document.getElementById('editarSalaModal')).hide();

                    // Recargar tabla
                    cargarSalas(currentPage);
                } else {
                    throw new Error(data.message || 'Error al actualizar la sala');
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError(error.message || 'Error al actualizar la sala');
            } finally {
                btnActualizar.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // ========== ELIMINAR SALA ==========
        async function eliminarSala(id, nombre) {
            const result = await Swal.fire({
                title: '¿Estás seguro?',
                text: `Se eliminará la sala "${nombre}" permanentemente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch('../../../Backend/api/Salas/Metodos-Salas.php', {
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
                            text: 'La sala ha sido eliminada exitosamente'
                        });

                        // Recargar tabla
                        cargarSalas(currentPage);
                } else {
                        throw new Error(data.message || 'Error al eliminar la sala');
                    }

            } catch (error) {
                console.error('Error:', error);
                    mostrarError(error.message || 'Error al eliminar la sala');
                }
            }
        }

        // ========== EVENT LISTENERS ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar salas iniciales
            cargarSalas();

            // Búsqueda en tiempo real
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    cargarSalas(1, this.value.trim(), filtroTipo.value, filtroEstado.value);
                }, 500);
            });

            // Filtros
            btnFiltrar.addEventListener('click', function() {
                const search = searchInput.value.trim();
                const tipo = filtroTipo.value;
                const estado = filtroEstado.value;
                cargarSalas(1, search, tipo, estado);
            });

            // Enter en búsqueda
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const search = this.value.trim();
                    const tipo = filtroTipo.value;
                    const estado = filtroEstado.value;
                    cargarSalas(1, search, tipo, estado);
                }
            });

            // Botones de modales
            document.getElementById('btnGuardarSala').addEventListener('click', crearSala);
            document.getElementById('btnActualizarSala').addEventListener('click', actualizarSala);
        });
    </script>
</body>
</html>