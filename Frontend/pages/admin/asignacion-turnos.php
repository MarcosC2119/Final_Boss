<?php
// ================================================================
// ASIGNACION DE TURNOS - FRONTEND
// ================================================================
// Gestión de asignaciones de turnos a usuarios
// Diseño: Bootstrap 5.33 sin CSS personalizado
// ================================================================
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignación de Turnos - ROOMIT</title>
    
    <!-- Bootstrap 5.33 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Estilos mínimos para badges y elementos específicos */
        .turno-preview {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            color: white;
            font-weight: 500;
        }
        .asignacion-card {
            transition: all 0.2s ease;
        }
        .asignacion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        
        <!-- ========== HEADER PRINCIPAL ========== -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center bg-white rounded-3 p-4 shadow-sm border">
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-primary bg-gradient rounded-circle p-3 me-3">
                                <i class="bi bi-person-check text-white fs-4"></i>
                            </div>
                            <div>
                                <h1 class="h3 mb-0 text-dark fw-bold">Asignación de Turnos</h1>
                                <p class="text-muted mb-0">Gestiona las asignaciones de turnos a usuarios</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#crearAsignacionModal">
                            <i class="bi bi-plus-circle me-2"></i>Nueva Asignación
                        </button>
                        <button class="btn btn-outline-secondary btn-lg" onclick="cargarAsignaciones()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== FILTROS Y BÚSQUEDA ========== -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-white rounded-3 p-4 shadow-sm border">
                    <div class="row g-3">
                        <!-- Búsqueda -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-search me-1"></i>Buscar Usuario
                            </label>
                            <input type="text" id="buscarInput" class="form-control" placeholder="Nombre del usuario...">
                        </div>
                        
                        <!-- Filtro por Turno -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-clock me-1"></i>Turno
                            </label>
                            <select id="filtroTurno" class="form-select">
                                <option value="">Todos los turnos</option>
                            </select>
                        </div>
                        
                        <!-- Filtro por Estado -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-funnel me-1"></i>Estado
                            </label>
                            <select id="filtroEstado" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="activa">Activa</option>
                                <option value="suspendida">Suspendida</option>
                                <option value="finalizada">Finalizada</option>
                            </select>
                        </div>
                        
                        <!-- Botón Limpiar -->
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                <i class="bi bi-x-circle me-1"></i>Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== TABLA DE ASIGNACIONES ========== -->
        <div class="row">
            <div class="col-12">
                <div class="bg-white rounded-3 shadow-sm border">
                    <!-- Header de tabla -->
                    <div class="p-4 border-bottom bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 fw-bold">Lista de Asignaciones</h5>
                                <small class="text-muted">Total: <span id="totalAsignaciones">0</span> asignaciones</small>
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

                    <!-- Loading State -->
                    <div id="loadingTable" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-3 text-muted">Cargando asignaciones...</p>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3 border-0">
                                        <i class="bi bi-person me-2 text-muted"></i>Usuario
                                    </th>
                                    <th class="px-4 py-3 border-0">
                                        <i class="bi bi-clock me-2 text-muted"></i>Turno Asignado
                                    </th>
                                    <th class="px-4 py-3 border-0">
                                        <i class="bi bi-calendar me-2 text-muted"></i>Período
                                    </th>
                                    <th class="px-4 py-3 border-0">
                                        <i class="bi bi-calendar-week me-2 text-muted"></i>Días
                                    </th>
                                    <th class="px-4 py-3 border-0">
                                        <i class="bi bi-check-circle me-2 text-muted"></i>Estado
                                    </th>
                                    <th class="px-4 py-3 border-0 text-end">
                                        <i class="bi bi-gear me-2 text-muted"></i>Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="asignacionesTableBody">
                                <!-- Datos dinámicos aquí -->
                            </tbody>
                        </table>
                    </div>

                    <!-- No Results Message -->
                    <div id="noResultsMessage" class="text-center py-5" style="display: none;">
                        <div class="text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <h5>No se encontraron asignaciones</h5>
                            <p>No hay asignaciones que coincidan con los filtros aplicados.</p>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div class="p-4 border-top bg-light">
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

    <!-- ========== MODAL CREAR ASIGNACIÓN ========== -->
    <div class="modal fade" id="crearAsignacionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Nueva Asignación de Turno
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearAsignacion">
                        <div class="row g-3">
                            <!-- Usuario -->
                            <div class="col-md-6">
                                <label for="crear_usuario_id" class="form-label fw-semibold">
                                    <i class="bi bi-person me-1"></i>Usuario *
                                </label>
                                <select id="crear_usuario_id" class="form-select" required>
                                    <option value="">Seleccionar usuario...</option>
                                </select>
                            </div>
                            
                            <!-- Turno -->
                            <div class="col-md-6">
                                <label for="crear_turno_id" class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1"></i>Turno *
                                </label>
                                <select id="crear_turno_id" class="form-select" required>
                                    <option value="">Seleccionar turno...</option>
                                </select>
                            </div>
                            
                            <!-- Fecha Inicio -->
                            <div class="col-md-6">
                                <label for="crear_fecha_inicio" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-plus me-1"></i>Fecha de Inicio *
                                </label>
                                <input type="date" id="crear_fecha_inicio" class="form-control" required>
                            </div>
                            
                            <!-- Fecha Fin -->
                            <div class="col-md-6">
                                <label for="crear_fecha_fin" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-x me-1"></i>Fecha de Fin
                                </label>
                                <input type="date" id="crear_fecha_fin" class="form-control">
                                <div class="form-text">Dejar vacío para asignación indefinida</div>
                            </div>

                            <!-- Días Específicos -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-week me-1"></i>Días Específicos (opcional)
                                </label>
                                <div class="bg-light p-3 rounded">
                                    <div class="form-text mb-2">Si se seleccionan días específicos, se ignorarán los días del turno base</div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="crear_lunes" value="lunes">
                                                <label class="form-check-label" for="crear_lunes">Lunes</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="crear_martes" value="martes">
                                                <label class="form-check-label" for="crear_martes">Martes</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="crear_miercoles" value="miercoles">
                                                <label class="form-check-label" for="crear_miercoles">Miércoles</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="crear_jueves" value="jueves">
                                                <label class="form-check-label" for="crear_jueves">Jueves</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="crear_viernes" value="viernes">
                                                <label class="form-check-label" for="crear_viernes">Viernes</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="crear_sabado" value="sabado">
                                                <label class="form-check-label" for="crear_sabado">Sábado</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="crear_domingo" value="domingo">
                                                <label class="form-check-label" for="crear_domingo">Domingo</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Estado -->
                            <div class="col-md-6">
                                <label for="crear_estado" class="form-label fw-semibold">
                                    <i class="bi bi-check-circle me-1"></i>Estado *
                                </label>
                                <select id="crear_estado" class="form-select" required>
                                    <option value="activa">Activa</option>
                                    <option value="suspendida">Suspendida</option>
                                </select>
                            </div>
                            
                            <!-- Observaciones -->
                            <div class="col-12">
                                <label for="crear_observaciones" class="form-label fw-semibold">
                                    <i class="bi bi-chat-text me-1"></i>Observaciones
                                </label>
                                <textarea id="crear_observaciones" class="form-control" rows="3" placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarAsignacion" onclick="crearAsignacion()">
                        <i class="bi bi-check me-1"></i>Crear Asignación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== MODAL VER ASIGNACIÓN ========== -->
    <div class="modal fade" id="verAsignacionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-eye me-2"></i>Detalles de Asignación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detallesAsignacion">
                        <!-- Contenido dinámico -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== MODAL EDITAR ASIGNACIÓN ========== -->
    <div class="modal fade" id="editarAsignacionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i>Editar Asignación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarAsignacion">
                        <input type="hidden" id="editar_id">
                        <div class="row g-3">
                            <!-- Usuario -->
                            <div class="col-md-6">
                                <label for="editar_usuario_id" class="form-label fw-semibold">
                                    <i class="bi bi-person me-1"></i>Usuario *
                                </label>
                                <select id="editar_usuario_id" class="form-select" required>
                                    <option value="">Seleccionar usuario...</option>
                                </select>
                            </div>
                            
                            <!-- Turno -->
                            <div class="col-md-6">
                                <label for="editar_turno_id" class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1"></i>Turno *
                                </label>
                                <select id="editar_turno_id" class="form-select" required>
                                    <option value="">Seleccionar turno...</option>
                                </select>
                            </div>
                            
                            <!-- Fecha Inicio -->
                            <div class="col-md-6">
                                <label for="editar_fecha_inicio" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-plus me-1"></i>Fecha de Inicio *
                                </label>
                                <input type="date" id="editar_fecha_inicio" class="form-control" required>
                            </div>
                            
                            <!-- Fecha Fin -->
                            <div class="col-md-6">
                                <label for="editar_fecha_fin" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-x me-1"></i>Fecha de Fin
                                </label>
                                <input type="date" id="editar_fecha_fin" class="form-control">
                            </div>

                            <!-- Días Específicos -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-week me-1"></i>Días Específicos (opcional)
                                </label>
                                <div class="bg-light p-3 rounded">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="editar_lunes" value="lunes">
                                                <label class="form-check-label" for="editar_lunes">Lunes</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="editar_martes" value="martes">
                                                <label class="form-check-label" for="editar_martes">Martes</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="editar_miercoles" value="miercoles">
                                                <label class="form-check-label" for="editar_miercoles">Miércoles</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="editar_jueves" value="jueves">
                                                <label class="form-check-label" for="editar_jueves">Jueves</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="editar_viernes" value="viernes">
                                                <label class="form-check-label" for="editar_viernes">Viernes</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="editar_sabado" value="sabado">
                                                <label class="form-check-label" for="editar_sabado">Sábado</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="editar_domingo" value="domingo">
                                                <label class="form-check-label" for="editar_domingo">Domingo</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Estado -->
                            <div class="col-md-6">
                                <label for="editar_estado" class="form-label fw-semibold">
                                    <i class="bi bi-check-circle me-1"></i>Estado *
                                </label>
                                <select id="editar_estado" class="form-select" required>
                                    <option value="activa">Activa</option>
                                    <option value="suspendida">Suspendida</option>
                                    <option value="finalizada">Finalizada</option>
                                </select>
                            </div>
                            
                            <!-- Observaciones -->
                            <div class="col-12">
                                <label for="editar_observaciones" class="form-label fw-semibold">
                                    <i class="bi bi-chat-text me-1"></i>Observaciones
                                </label>
                                <textarea id="editar_observaciones" class="form-control" rows="3" placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="btnActualizarAsignacion" onclick="actualizarAsignacion()">
                        <i class="bi bi-check me-1"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ========== VARIABLES GLOBALES ==========
        let asignacionesData = [];
        let paginaActual = 1;
        let itemsPorPagina = 10;
        let filtrosActivos = {};

        // ========== INICIALIZACIÓN ==========
        document.addEventListener('DOMContentLoaded', function() {
            cargarAsignaciones();
            cargarUsuarios();
            cargarTurnos();
            inicializarEventListeners();
        });

        function inicializarEventListeners() {
            // Búsqueda en tiempo real
            document.getElementById('buscarInput').addEventListener('input', debounce(function() {
                aplicarFiltros();
            }, 500));

            // Filtros
            document.getElementById('filtroTurno').addEventListener('change', aplicarFiltros);
            document.getElementById('filtroEstado').addEventListener('change', aplicarFiltros);
            document.getElementById('itemsPorPagina').addEventListener('change', function() {
                itemsPorPagina = parseInt(this.value);
                paginaActual = 1;
                cargarAsignaciones();
            });
        }

        // ========== FUNCIONES DE CARGA DE DATOS ==========
        async function cargarAsignaciones(pagina = 1) {
            try {
                mostrarLoading(true);
                paginaActual = pagina;

                const params = new URLSearchParams({
                    page: paginaActual,
                    per_page: itemsPorPagina,
                    ...filtrosActivos
                });

                const response = await fetch(`../../../Backend/api/Asignaciones/Metodos-asignaciones.php?${params}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    asignacionesData = data.data;
                    mostrarAsignaciones(data.data);
                    generarPaginacion(data.pagination);
                    paginaActual = data.pagination.current_page;
                } else {
                    throw new Error(data.message || 'Error al cargar asignaciones');
                }
            } catch (error) {
                console.error('Error al cargar asignaciones:', error);
                mostrarError('Error al cargar asignaciones. Verifica tu conexión.');
            } finally {
                mostrarLoading(false);
            }
        }

        async function cargarUsuarios() {
            try {
                const response = await fetch('/Final_Boss/Backend/api/Usuarios/Metodos-Usuario.php');
                const data = await response.json();
                
                if (data.success) {
                    const selectores = ['crear_usuario_id', 'editar_usuario_id'];
                    selectores.forEach(selectorId => {
                        const select = document.getElementById(selectorId);
                        select.innerHTML = '<option value="">Seleccionar usuario...</option>';
                        data.data.forEach(usuario => {
                            select.innerHTML += `<option value="${usuario.id}">${usuario.nombre} (${usuario.email})</option>`;
                        });
                    });
                }
            } catch (error) {
                console.error('Error al cargar usuarios:', error);
            }
        }

        async function cargarTurnos() {
            try {
                const response = await fetch('/Final_Boss/Backend/api/Turnos/Metodos-turnos.php');
                const data = await response.json();
                
                if (data.success) {
                    // Llenar selectores de formularios
                    const selectores = ['crear_turno_id', 'editar_turno_id'];
                    selectores.forEach(selectorId => {
                        const select = document.getElementById(selectorId);
                        select.innerHTML = '<option value="">Seleccionar turno...</option>';
                        data.data.forEach(turno => {
                            select.innerHTML += `<option value="${turno.id}">${turno.nombre} (${turno.hora_inicio}-${turno.hora_fin})</option>`;
                        });
                    });

                    // Llenar filtro de turnos
                    const filtroTurno = document.getElementById('filtroTurno');
                    filtroTurno.innerHTML = '<option value="">Todos los turnos</option>';
                    data.data.forEach(turno => {
                        filtroTurno.innerHTML += `<option value="${turno.id}">${turno.nombre}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error al cargar turnos:', error);
            }
        }

        // ========== FUNCIONES DE VISUALIZACIÓN ==========
        function mostrarAsignaciones(asignaciones) {
            const tbody = document.getElementById('asignacionesTableBody');
            const noResults = document.getElementById('noResultsMessage');
            
            document.getElementById('totalAsignaciones').textContent = asignaciones.length;
            
            if (asignaciones.length === 0) {
                tbody.innerHTML = '';
                noResults.style.display = 'block';
                return;
            }
            
            noResults.style.display = 'none';
            
            tbody.innerHTML = asignaciones.map(asignacion => `
                <tr class="asignacion-card">
                    <td class="px-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-gradient rounded-circle p-2 me-3">
                                <i class="bi bi-person text-white"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">${asignacion.usuario_nombre || 'Usuario'}</div>
                                <small class="text-muted">${asignacion.usuario_email || ''}</small>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="turno-preview" style="background-color: ${asignacion.turno_color || '#007bff'}">
                            ${asignacion.turno_nombre || 'Turno'}
                        </div>
                        <div class="mt-1">
                            <small class="text-muted">${asignacion.turno_horario || ''}</small>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="fw-semibold">${formatearFecha(asignacion.fecha_inicio)}</div>
                        <small class="text-muted">
                            ${asignacion.fecha_fin ? 'hasta ' + formatearFecha(asignacion.fecha_fin) : 'Indefinido'}
                        </small>
                    </td>
                    <td class="px-4 py-3">
                        <div>${formatDiasAsignacion(asignacion.dias_especificos, asignacion.turno_dias)}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge ${getEstadoBadgeClass(asignacion.estado)}">${formatEstado(asignacion.estado)}</span>
                    </td>
                    <td class="px-4 py-3 text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info" onclick="verAsignacion(${asignacion.id})" data-bs-toggle="modal" data-bs-target="#verAsignacionModal">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-primary" onclick="editarAsignacion(${asignacion.id})" data-bs-toggle="modal" data-bs-target="#editarAsignacionModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="eliminarAsignacion(${asignacion.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // ========== FUNCIONES DE UTILIDAD ==========
        function formatDiasAsignacion(diasEspecificos, diasTurno) {
            let dias = [];
            
            if (diasEspecificos && diasEspecificos.length > 0) {
                dias = typeof diasEspecificos === 'string' ? JSON.parse(diasEspecificos) : diasEspecificos;
            } else if (diasTurno) {
                dias = typeof diasTurno === 'string' ? JSON.parse(diasTurno) : diasTurno;
            }
            
            const diasAbrev = {
                'lunes': 'L', 'martes': 'M', 'miercoles': 'X', 'jueves': 'J',
                'viernes': 'V', 'sabado': 'S', 'domingo': 'D'
            };
            
            return dias.map(dia => 
                `<span class="badge bg-primary me-1">${diasAbrev[dia] || dia}</span>`
            ).join('');
        }

        function getEstadoBadgeClass(estado) {
            switch(estado) {
                case 'activa': return 'bg-success';
                case 'suspendida': return 'bg-warning text-dark';
                case 'finalizada': return 'bg-secondary';
                default: return 'bg-secondary';
            }
        }

        function formatEstado(estado) {
            const estados = {
                'activa': 'Activa',
                'suspendida': 'Suspendida',
                'finalizada': 'Finalizada'
            };
            return estados[estado] || estado;
        }

        function formatearFecha(fecha) {
            if (!fecha) return '';
            const d = new Date(fecha);
            return d.toLocaleDateString('es-ES');
        }

        // ========== FUNCIONES CRUD ==========
        async function crearAsignacion() {
            try {
                const formData = obtenerDatosFormulario('crear');
                
                if (!validarFormulario(formData, 'crear')) {
                    return;
                }

                mostrarLoadingBoton('btnGuardarAsignacion', true);

                const response = await fetch('../../../Backend/api/Asignaciones/Metodos-asignaciones.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Asignación creada exitosamente',
                        icon: 'success'
                    });
                    bootstrap.Modal.getInstance(document.getElementById('crearAsignacionModal')).hide();
                    limpiarFormulario('crear');
                    cargarAsignaciones(paginaActual);
                } else {
                    mostrarError(data.message || 'Error al crear la asignación');
                }
            } catch (error) {
                console.error('Error al crear asignación:', error);
                mostrarError('Error al crear la asignación');
            } finally {
                mostrarLoadingBoton('btnGuardarAsignacion', false);
            }
        }

        function obtenerDatosFormulario(tipo) {
            const prefix = tipo === 'crear' ? 'crear' : 'editar';
            
            // Obtener días específicos seleccionados
            const diasEspecificos = [];
            const diasCheckboxes = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
            diasCheckboxes.forEach(dia => {
                if (document.getElementById(`${prefix}_${dia}`).checked) {
                    diasEspecificos.push(dia);
                }
            });

            const formData = {
                turno_id: document.getElementById(`${prefix}_turno_id`).value,
                usuario_id: document.getElementById(`${prefix}_usuario_id`).value,
                fecha_inicio: document.getElementById(`${prefix}_fecha_inicio`).value,
                fecha_fin: document.getElementById(`${prefix}_fecha_fin`).value || null,
                dias_especificos: diasEspecificos.length > 0 ? diasEspecificos : null,
                estado: document.getElementById(`${prefix}_estado`).value,
                observaciones: document.getElementById(`${prefix}_observaciones`).value
            };

            if (tipo === 'editar') {
                formData.id = document.getElementById('editar_id').value;
            }

            return formData;
        }

        function validarFormulario(formData, tipo) {
            if (!formData.usuario_id) {
                mostrarError('Por favor selecciona un usuario');
                return false;
            }
            if (!formData.turno_id) {
                mostrarError('Por favor selecciona un turno');
                return false;
            }
            if (!formData.fecha_inicio) {
                mostrarError('Por favor ingresa la fecha de inicio');
                return false;
            }
            if (formData.fecha_fin && formData.fecha_fin <= formData.fecha_inicio) {
                mostrarError('La fecha de fin debe ser posterior a la fecha de inicio');
                return false;
            }
            return true;
        }

        // ========== FUNCIONES DE APOYO ==========
        function mostrarLoading(show) {
            document.getElementById('loadingTable').style.display = show ? 'block' : 'none';
        }

        function mostrarLoadingBoton(btnId, loading) {
            const btn = document.getElementById(btnId);
            if (loading) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Procesando...';
            } else {
                btn.disabled = false;
                btn.innerHTML = btnId === 'btnGuardarAsignacion' ? 
                    '<i class="bi bi-check me-1"></i>Crear Asignación' : 
                    '<i class="bi bi-check me-1"></i>Actualizar';
            }
        }

        function mostrarError(mensaje) {
            Swal.fire({
                title: 'Error',
                text: mensaje,
                icon: 'error'
            });
        }

        function limpiarFormulario(tipo) {
            const prefix = tipo === 'crear' ? 'crear' : 'editar';
            document.getElementById(`form${tipo.charAt(0).toUpperCase() + tipo.slice(1)}Asignacion`).reset();
            
            // Limpiar checkboxes de días
            const diasCheckboxes = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
            diasCheckboxes.forEach(dia => {
                document.getElementById(`${prefix}_${dia}`).checked = false;
            });
        }

        function aplicarFiltros() {
            const busqueda = document.getElementById('buscarInput').value.trim();
            const turno = document.getElementById('filtroTurno').value;
            const estado = document.getElementById('filtroEstado').value;

            filtrosActivos = {};
            if (busqueda) filtrosActivos.search = busqueda;
            if (turno) filtrosActivos.turno_id = turno;
            if (estado) filtrosActivos.estado = estado;

            paginaActual = 1;
            cargarAsignaciones();
        }

        function limpiarFiltros() {
            document.getElementById('buscarInput').value = '';
            document.getElementById('filtroTurno').value = '';
            document.getElementById('filtroEstado').value = '';
            filtrosActivos = {};
            paginaActual = 1;
            cargarAsignaciones();
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

        function generarPaginacion(pagination) {
            const paginacionElement = document.getElementById('paginacion');
            const { current_page, total_pages, total_items } = pagination;
            
            // Actualizar información de resultados
            const desde = ((current_page - 1) * itemsPorPagina) + 1;
            const hasta = Math.min(current_page * itemsPorPagina, total_items);
            document.getElementById('resultadosInfo').textContent = `${desde}-${hasta} de ${total_items}`;

            let paginacionHTML = '';
            
            // Botón anterior
            paginacionHTML += `
                <li class="page-item ${current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="cargarAsignaciones(${current_page - 1})">Anterior</a>
                </li>
            `;

            // Números de página
            const startPage = Math.max(1, current_page - 2);
            const endPage = Math.min(total_pages, current_page + 2);

            for (let i = startPage; i <= endPage; i++) {
                paginacionHTML += `
                    <li class="page-item ${i === current_page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="cargarAsignaciones(${i})">${i}</a>
                    </li>
                `;
            }

            // Botón siguiente
            paginacionHTML += `
                <li class="page-item ${current_page === total_pages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="cargarAsignaciones(${current_page + 1})">Siguiente</a>
                </li>
            `;

            paginacionElement.innerHTML = paginacionHTML;
        }

        // Funciones placeholder para las acciones restantes
        function verAsignacion(id) {
            console.log('Ver asignación:', id);
            // Implementar funcionalidad de ver detalles
        }

        // Funciones restantes que faltaron
        function editarAsignacion(id) {
            try {
                const asignacion = asignacionesData.find(a => a.id == id);
                if (!asignacion) {
                    mostrarError('Asignación no encontrada');
                    return;
                }

                // Rellenar formulario
                document.getElementById('editar_id').value = asignacion.id;
                document.getElementById('editar_usuario_id').value = asignacion.usuario_id;
                document.getElementById('editar_turno_id').value = asignacion.turno_id;
                document.getElementById('editar_fecha_inicio').value = asignacion.fecha_inicio;
                document.getElementById('editar_fecha_fin').value = asignacion.fecha_fin || '';
                document.getElementById('editar_estado').value = asignacion.estado;
                document.getElementById('editar_observaciones').value = asignacion.observaciones || '';

                // Marcar días específicos si existen
                const diasCheckboxes = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                diasCheckboxes.forEach(dia => {
                    document.getElementById(`editar_${dia}`).checked = false;
                });

                if (asignacion.dias_especificos) {
                    const diasEspecificos = typeof asignacion.dias_especificos === 'string' ? 
                        JSON.parse(asignacion.dias_especificos) : asignacion.dias_especificos;
                    
                    diasEspecificos.forEach(dia => {
                        const checkbox = document.getElementById(`editar_${dia}`);
                        if (checkbox) checkbox.checked = true;
                    });
                }

            } catch (error) {
                console.error('Error al cargar asignación:', error);
                mostrarError('Error al cargar los datos de la asignación');
            }
        }

        async function actualizarAsignacion() {
            try {
                const formData = obtenerDatosFormulario('editar');
                
                if (!validarFormulario(formData, 'editar')) {
                    return;
                }

                mostrarLoadingBoton('btnActualizarAsignacion', true);

                const response = await fetch('../../../Backend/api/Asignaciones/Metodos-asignaciones.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Asignación actualizada exitosamente',
                        icon: 'success'
                    });
                    bootstrap.Modal.getInstance(document.getElementById('editarAsignacionModal')).hide();
                    cargarAsignaciones(paginaActual);
                } else {
                    mostrarError(data.message || 'Error al actualizar la asignación');
                }
            } catch (error) {
                console.error('Error al actualizar asignación:', error);
                mostrarError('Error al actualizar la asignación');
            } finally {
                mostrarLoadingBoton('btnActualizarAsignacion', false);
            }
        }

        async function eliminarAsignacion(id) {
            try {
                const asignacion = asignacionesData.find(a => a.id == id);
                if (!asignacion) {
                    mostrarError('Asignación no encontrada');
                    return;
                }

                const result = await Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Se eliminará la asignación de "${asignacion.usuario_nombre || 'Usuario'}" al turno "${asignacion.turno_nombre || 'Turno'}"`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                });

                if (result.isConfirmed) {
                    const response = await fetch('../../../Backend/api/Asignaciones/Metodos-asignaciones.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ id: id })
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'La asignación ha sido eliminada exitosamente',
                            icon: 'success'
                        });
                        cargarAsignaciones(paginaActual);
                    } else {
                        mostrarError(data.message || 'Error al eliminar la asignación');
                    }
                }
            } catch (error) {
                console.error('Error al eliminar asignación:', error);
                mostrarError('Error al eliminar la asignación');
            }
        }

        function verAsignacion(id) {
            try {
                const asignacion = asignacionesData.find(a => a.id == id);
                if (!asignacion) {
                    mostrarError('Asignación no encontrada');
                    return;
                }

                const detallesHTML = `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <i class="bi bi-person me-2"></i>Información del Usuario
                                </div>
                                <div class="card-body">
                                    <h6 class="fw-bold">${asignacion.usuario_nombre || 'Usuario'}</h6>
                                    <p class="text-muted mb-1">${asignacion.usuario_email || ''}</p>
                                    <small class="text-muted">Rol: ${asignacion.usuario_rol || ''}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header" style="background-color: ${asignacion.turno_color || '#007bff'}; color: white;">
                                    <i class="bi bi-clock me-2"></i>Información del Turno
                                </div>
                                <div class="card-body">
                                    <h6 class="fw-bold">${asignacion.turno_nombre || 'Turno'}</h6>
                                    <p class="mb-1"><i class="bi bi-clock me-1"></i> ${asignacion.turno_horario || ''}</p>
                                    <small class="text-muted">${asignacion.turno_descripcion || ''}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <i class="bi bi-calendar me-2"></i>Detalles de la Asignación
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Fecha de Inicio:</strong><br>
                                            <span class="text-muted">${formatearFecha(asignacion.fecha_inicio)}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Fecha de Fin:</strong><br>
                                            <span class="text-muted">${asignacion.fecha_fin ? formatearFecha(asignacion.fecha_fin) : 'Indefinido'}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Estado:</strong><br>
                                            <span class="badge ${getEstadoBadgeClass(asignacion.estado)}">${formatEstado(asignacion.estado)}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Días:</strong><br>
                                            ${formatDiasAsignacion(asignacion.dias_especificos, asignacion.turno_dias)}
                                        </div>
                                        ${asignacion.observaciones ? `
                                        <div class="col-12 mt-3">
                                            <strong>Observaciones:</strong><br>
                                            <span class="text-muted">${asignacion.observaciones}</span>
                                        </div>
                                        ` : ''}
                                        <div class="col-12 mt-3">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar-plus me-1"></i>Asignado el: ${formatearFecha(asignacion.fecha_asignacion)}
                                                ${asignacion.asignado_por_nombre ? ` por ${asignacion.asignado_por_nombre}` : ''}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('detallesAsignacion').innerHTML = detallesHTML;

            } catch (error) {
                console.error('Error al mostrar asignación:', error);
                mostrarError('Error al cargar los detalles de la asignación');
            }
        }
    </script>
</body>
</html>