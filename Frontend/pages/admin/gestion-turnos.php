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
    <title>Gestión de Turnos - RoomIT</title>
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
        .turno-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
        }
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #dee2e6;
            display: inline-block;
            vertical-align: middle;
        }
        .dias-badge {
            font-size: 10px;
            margin: 1px;
        }
        .turno-academico { background: linear-gradient(45deg, #28a745, #20c997); }
        .turno-laboral { background: linear-gradient(45deg, #fd7e14, #ffc107); }
        .turno-servicio { background: linear-gradient(45deg, #6f42c1, #e83e8c); }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Gestión de Turnos</h2>
                <p class="text-muted mb-0">Administra los turnos de trabajo del sistema RoomIT</p>
            </div>
            <button id="btnNuevoTurno" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearTurnoModal">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Turno
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
                            <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="filtroTipo" class="form-select">
                            <option value="">Todos los tipos</option>
                            <option value="academico">Académico</option>
                            <option value="laboral">Laboral</option>
                            <option value="servicio">Servicio</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filtroEstado" class="form-select">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
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

        <!-- Turnos Table -->
        <div class="card">
            <div class="card-body table-container">
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando turnos...</p>
                </div>
                
                <div id="tableContainer" class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Turno</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Horario</th>
                                <th>Días de semana</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="turnosTableBody">
                            <!-- Los turnos se cargarán dinámicamente aquí -->
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje cuando no hay turnos -->
                <div id="noResultsMessage" class="text-center py-4" style="display: none;">
                    <i class="bi bi-clock-history fs-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No se encontraron turnos</h5>
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

    <!-- Create Turno Modal -->
    <div class="modal fade" id="crearTurnoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Turno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearTurno">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Nombre del turno <span class="text-danger">*</span></label>
                                    <input type="text" id="crear_nombre" class="form-control" required placeholder="Ej: Turno Mañana, Guardia Nocturna">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Color <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" id="crear_color_hex" class="form-control form-control-color" value="#007bff" required>
                                        <input type="text" id="crear_color_text" class="form-control" value="#007bff" maxlength="7">
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Tipo de turno <span class="text-danger">*</span></label>
                                    <select id="crear_tipo" class="form-select" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="academico">Académico</option>
                                        <option value="laboral">Laboral</option>
                                        <option value="servicio">Servicio</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hora de inicio <span class="text-danger">*</span></label>
                                    <input type="time" id="crear_hora_inicio" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hora de fin <span class="text-danger">*</span></label>
                                    <input type="time" id="crear_hora_fin" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Días de la semana <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="crear_lunes" value="lunes">
                                            <label class="form-check-label" for="crear_lunes">Lunes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="crear_martes" value="martes">
                                            <label class="form-check-label" for="crear_martes">Martes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="crear_miercoles" value="miercoles">
                                            <label class="form-check-label" for="crear_miercoles">Miércoles</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="crear_jueves" value="jueves">
                                            <label class="form-check-label" for="crear_jueves">Jueves</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="crear_viernes" value="viernes">
                                            <label class="form-check-label" for="crear_viernes">Viernes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="crear_sabado" value="sabado">
                                            <label class="form-check-label" for="crear_sabado">Sábado</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="crear_domingo" value="domingo">
                                            <label class="form-check-label" for="crear_domingo">Domingo</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback" id="crear_dias_error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea id="crear_descripcion" class="form-control" rows="3" placeholder="Descripción opcional del turno..."></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado inicial</label>
                            <select id="crear_estado" class="form-select">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarTurno" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        Crear Turno
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Turno Modal -->
    <div class="modal fade" id="editarTurnoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Turno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarTurno">
                        <input type="hidden" id="editar_id">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Nombre del turno <span class="text-danger">*</span></label>
                                    <input type="text" id="editar_nombre" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Color <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="color" id="editar_color_hex" class="form-control form-control-color" required>
                                        <input type="text" id="editar_color_text" class="form-control" maxlength="7">
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Tipo de turno <span class="text-danger">*</span></label>
                                    <select id="editar_tipo" class="form-select" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="academico">Académico</option>
                                        <option value="laboral">Laboral</option>
                                        <option value="servicio">Servicio</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hora de inicio <span class="text-danger">*</span></label>
                                    <input type="time" id="editar_hora_inicio" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hora de fin <span class="text-danger">*</span></label>
                                    <input type="time" id="editar_hora_fin" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Días de la semana <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editar_lunes" value="lunes">
                                            <label class="form-check-label" for="editar_lunes">Lunes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editar_martes" value="martes">
                                            <label class="form-check-label" for="editar_martes">Martes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editar_miercoles" value="miercoles">
                                            <label class="form-check-label" for="editar_miercoles">Miércoles</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editar_jueves" value="jueves">
                                            <label class="form-check-label" for="editar_jueves">Jueves</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editar_viernes" value="viernes">
                                            <label class="form-check-label" for="editar_viernes">Viernes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editar_sabado" value="sabado">
                                            <label class="form-check-label" for="editar_sabado">Sábado</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editar_domingo" value="domingo">
                                            <label class="form-check-label" for="editar_domingo">Domingo</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback" id="editar_dias_error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea id="editar_descripcion" class="form-control" rows="3"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select id="editar_estado" class="form-select">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnActualizarTurno" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        Actualizar Turno
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Turno Modal -->
    <div class="modal fade" id="verTurnoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Turno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 text-center mb-3">
                            <div id="ver_turno_badge" class="turno-badge mx-auto mb-3"></div>
                            <h4 id="ver_nombre" class="mb-1"></h4>
                            <span id="ver_tipo_badge" class="badge"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Hora de inicio:</strong><br>
                            <span id="ver_hora_inicio"></span>
                        </div>
                        <div class="col-6">
                            <strong>Hora de fin:</strong><br>
                            <span id="ver_hora_fin"></span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Días de la semana:</strong><br>
                            <div id="ver_dias_semana" class="mt-2"></div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Estado:</strong><br>
                            <span id="ver_estado_badge" class="badge"></span>
                        </div>
                        <div class="col-6">
                            <strong>Color:</strong><br>
                            <span id="ver_color_preview" class="color-preview me-2"></span>
                            <span id="ver_color_hex" class="text-muted"></span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <strong>Creado:</strong><br>
                            <span id="ver_fecha_creacion"></span>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <strong>Descripción:</strong><br>
                        <span id="ver_descripcion" class="text-muted"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.9.0/dist/sweetalert2.all.min.js"></script>

    <script>
        // ========== VARIABLES GLOBALES ==========
        let paginaActual = 1;
        const itemsPorPagina = 10;
        let turnosData = [];
        let filtrosActivos = {};

        // ========== VERIFICACIÓN DE AUTENTICACIÓN ==========
        document.addEventListener('DOMContentLoaded', function() {
            verificarAutenticacion();
            inicializarEventos();
            cargarTurnos();
        });

        function verificarAutenticacion() {
            const userData = localStorage.getItem('userData');
            if (!userData) {
                window.location.href = '../../login.php';
                return;
            }

            try {
                const usuario = JSON.parse(userData);
                if (usuario.rol !== 'administrativo') {
                    Swal.fire({
                        title: 'Acceso denegado',
                        text: 'No tienes permisos para acceder a esta página',
                        icon: 'error'
                    }).then(() => {
                        window.location.href = '../../dashboard_usuario.php';
                    });
                }
            } catch (error) {
                console.error('Error al verificar autenticación:', error);
                window.location.href = '../../login.php';
            }
        }

        // ========== INICIALIZACIÓN DE EVENTOS ==========
        function inicializarEventos() {
            // Event listeners para búsqueda y filtros
            document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
            document.getElementById('filtroTipo').addEventListener('change', aplicarFiltros);
            document.getElementById('filtroEstado').addEventListener('change', aplicarFiltros);
            document.getElementById('btnFiltrar').addEventListener('click', aplicarFiltros);

            // Event listeners para modales
            document.getElementById('btnGuardarTurno').addEventListener('click', crearTurno);
            document.getElementById('btnActualizarTurno').addEventListener('click', actualizarTurno);

            // Event listeners para formularios
            document.getElementById('formCrearTurno').addEventListener('submit', function(e) {
                e.preventDefault();
                crearTurno();
            });

            document.getElementById('formEditarTurno').addEventListener('submit', function(e) {
                e.preventDefault();
                actualizarTurno();
            });

            // Sincronizar color picker con text input
            document.getElementById('crear_color_hex').addEventListener('change', function() {
                document.getElementById('crear_color_text').value = this.value;
            });

            document.getElementById('crear_color_text').addEventListener('change', function() {
                const color = this.value;
                if (/^#[0-9A-F]{6}$/i.test(color)) {
                    document.getElementById('crear_color_hex').value = color;
                }
            });

            document.getElementById('editar_color_hex').addEventListener('change', function() {
                document.getElementById('editar_color_text').value = this.value;
            });

            document.getElementById('editar_color_text').addEventListener('change', function() {
                const color = this.value;
                if (/^#[0-9A-F]{6}$/i.test(color)) {
                    document.getElementById('editar_color_hex').value = color;
                }
            });
        }

        // ========== FUNCIÓN PRINCIPAL DE CARGA ==========
        async function cargarTurnos(pagina = 1) {
            try {
                mostrarLoading(true);
                
                const params = new URLSearchParams({
                    page: pagina,
                    per_page: itemsPorPagina,
                    ...filtrosActivos
                });

                const response = await fetch(`../../../Backend/api/Turnos/Metodos-turnos.php?${params}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    turnosData = data.data;
                    mostrarTurnos(data.data);
                    generarPaginacion(data.pagination);
                    paginaActual = data.pagination.current_page;
                } else {
                    throw new Error(data.message || 'Error al cargar turnos');
                }
            } catch (error) {
                console.error('Error al cargar turnos:', error);
                mostrarError('Error al cargar turnos. Verifica tu conexión.');
            } finally {
                mostrarLoading(false);
            }
        }

        // ========== FUNCIONES DE VISUALIZACIÓN ==========
        function mostrarTurnos(turnos) {
            const tbody = document.getElementById('turnosTableBody');
            const noResults = document.getElementById('noResultsMessage');
            
            if (turnos.length === 0) {
                tbody.innerHTML = '';
                noResults.style.display = 'block';
                return;
            }
            
            noResults.style.display = 'none';
            
            tbody.innerHTML = turnos.map(turno => `
                <tr>
                    <td>
                        <div class="turno-badge" style="background-color: ${turno.color_hex || '#007bff'}">${turno.nombre.substring(0, 3).toUpperCase()}</div>
                    </td>
                    <td>
                        <div class="fw-semibold">${turno.nombre}</div>
                        <small class="text-muted">${turno.descripcion || 'Sin descripción'}</small>
                    </td>
                    <td>
                        <span class="badge ${getTipoBadgeClass(turno.tipo)}">${formatTipo(turno.tipo)}</span>
                    </td>
                    <td>
                        <div class="fw-semibold">${turno.hora_inicio} - ${turno.hora_fin}</div>
                        <small class="text-muted">${calcularDuracion(turno.hora_inicio, turno.hora_fin)} horas</small>
                    </td>
                    <td>
                        <div>${formatDiasSemana(turno.dias_semana)}</div>
                    </td>
                    <td>
                        <span class="badge ${getEstadoBadgeClass(turno.estado)}">${formatEstado(turno.estado)}</span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info" onclick="verTurno(${turno.id})" data-bs-toggle="modal" data-bs-target="#verTurnoModal">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-primary" onclick="editarTurno(${turno.id})" data-bs-toggle="modal" data-bs-target="#editarTurnoModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="eliminarTurno(${turno.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // ========== FUNCIONES DE UTILIDAD ==========
        function getTipoBadgeClass(tipo) {
            switch(tipo) {
                case 'academico': return 'bg-success';
                case 'laboral': return 'bg-warning text-dark';
                case 'servicio': return 'bg-info';
                default: return 'bg-secondary';
            }
        }

        function getEstadoBadgeClass(estado) {
            return estado === 'activo' ? 'bg-success' : 'bg-secondary';
        }

        function formatTipo(tipo) {
            const tipos = {
                'academico': 'Académico',
                'laboral': 'Laboral',
                'servicio': 'Servicio'
            };
            return tipos[tipo] || tipo;
        }

        function formatEstado(estado) {
            return estado === 'activo' ? 'Activo' : 'Inactivo';
        }

        function formatDiasSemana(diasJson) {
            if (!diasJson) return '<span class="text-muted">No definido</span>';
            
            try {
                const dias = typeof diasJson === 'string' ? JSON.parse(diasJson) : diasJson;
                const diasAbrev = {
                    'lunes': 'L',
                    'martes': 'M',
                    'miercoles': 'X',
                    'jueves': 'J',
                    'viernes': 'V',
                    'sabado': 'S',
                    'domingo': 'D'
                };
                
                return dias.map(dia => 
                    `<span class="badge bg-primary dias-badge">${diasAbrev[dia] || dia}</span>`
                ).join(' ');
            } catch (error) {
                return '<span class="text-muted">Error formato</span>';
            }
        }

        function calcularDuracion(inicio, fin) {
            const [horaInicio, minutoInicio] = inicio.split(':').map(Number);
            const [horaFin, minutoFin] = fin.split(':').map(Number);
            
            let minutosTotalesInicio = horaInicio * 60 + minutoInicio;
            let minutosTotalesFin = horaFin * 60 + minutoFin;
            
            // Si el fin es menor que el inicio, asumimos que cruza medianoche
            if (minutosTotalesFin < minutosTotalesInicio) {
                minutosTotalesFin += 24 * 60;
            }
            
            const diferencia = minutosTotalesFin - minutosTotalesInicio;
            return (diferencia / 60).toFixed(1);
        }

        // ========== FUNCIONES CRUD ==========
        async function crearTurno() {
            try {
                const formData = obtenerDatosFormulario('crear');
                
                if (!validarFormulario(formData, 'crear')) {
                    return;
                }

                mostrarLoadingBoton('btnGuardarTurno', true);

                const response = await fetch('../../../Backend/api/Turnos/Metodos-turnos.php', {
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
                        text: 'Turno creado exitosamente',
                        icon: 'success'
                    });
                    bootstrap.Modal.getInstance(document.getElementById('crearTurnoModal')).hide();
                    limpiarFormulario('crear');
                    cargarTurnos(paginaActual);
                } else {
                    mostrarError(data.message || 'Error al crear el turno');
                }
            } catch (error) {
                console.error('Error al crear turno:', error);
                mostrarError('Error al crear el turno');
            } finally {
                mostrarLoadingBoton('btnGuardarTurno', false);
            }
        }

        async function editarTurno(id) {
            try {
                const turno = turnosData.find(t => t.id == id);
                if (!turno) {
                    mostrarError('Turno no encontrado');
                    return;
                }

                // Rellenar formulario
                document.getElementById('editar_id').value = turno.id;
                document.getElementById('editar_nombre').value = turno.nombre;
                document.getElementById('editar_tipo').value = turno.tipo;
                document.getElementById('editar_hora_inicio').value = turno.hora_inicio;
                document.getElementById('editar_hora_fin').value = turno.hora_fin;
                document.getElementById('editar_color_hex').value = turno.color_hex || '#007bff';
                document.getElementById('editar_color_text').value = turno.color_hex || '#007bff';
                document.getElementById('editar_descripcion').value = turno.descripcion || '';
                document.getElementById('editar_estado').value = turno.estado;

                // Marcar días de la semana
                const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                dias.forEach(dia => {
                    document.getElementById(`editar_${dia}`).checked = false;
                });

                if (turno.dias_semana) {
                    try {
                        const diasSeleccionados = typeof turno.dias_semana === 'string' 
                            ? JSON.parse(turno.dias_semana) 
                            : turno.dias_semana;
                        
                        diasSeleccionados.forEach(dia => {
                            const checkbox = document.getElementById(`editar_${dia}`);
                            if (checkbox) checkbox.checked = true;
                        });
                    } catch (error) {
                        console.error('Error al parsear días de semana:', error);
                    }
                }
            } catch (error) {
                console.error('Error al cargar datos para editar:', error);
                mostrarError('Error al cargar los datos del turno');
            }
        }

        async function actualizarTurno() {
            try {
                const formData = obtenerDatosFormulario('editar');
                const id = document.getElementById('editar_id').value;
                
                if (!validarFormulario(formData, 'editar')) {
                    return;
                }

                mostrarLoadingBoton('btnActualizarTurno', true);

                const response = await fetch('../../../Backend/api/Turnos/Metodos-turnos.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({id: id, ...formData})
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Turno actualizado exitosamente',
                        icon: 'success'
                    });
                    bootstrap.Modal.getInstance(document.getElementById('editarTurnoModal')).hide();
                    cargarTurnos(paginaActual);
                } else {
                    mostrarError(data.message || 'Error al actualizar el turno');
                }
            } catch (error) {
                console.error('Error al actualizar turno:', error);
                mostrarError('Error al actualizar el turno');
            } finally {
                mostrarLoadingBoton('btnActualizarTurno', false);
            }
        }

        async function eliminarTurno(id) {
            try {
                const result = await Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                });

                if (result.isConfirmed) {
                    const response = await fetch('../../../Backend/api/Turnos/Metodos-turnos.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({id: id})
                    });

                    const data = await response.json();

                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'El turno ha sido eliminado exitosamente',
                            icon: 'success'
                        });
                        cargarTurnos(paginaActual);
                    } else {
                        mostrarError(data.message || 'Error al eliminar el turno');
                    }
                }
            } catch (error) {
                console.error('Error al eliminar turno:', error);
                mostrarError('Error al eliminar el turno');
            }
        }

        function verTurno(id) {
            try {
                const turno = turnosData.find(t => t.id == id);
                if (!turno) {
                    mostrarError('Turno no encontrado');
                    return;
                }

                // Rellenar modal de visualización
                document.getElementById('ver_nombre').textContent = turno.nombre;
                document.getElementById('ver_hora_inicio').textContent = turno.hora_inicio;
                document.getElementById('ver_hora_fin').textContent = turno.hora_fin;
                document.getElementById('ver_descripcion').textContent = turno.descripcion || 'Sin descripción';
                document.getElementById('ver_fecha_creacion').textContent = new Date(turno.fecha_creacion).toLocaleDateString();
                document.getElementById('ver_color_hex').textContent = turno.color_hex || '#007bff';

                // Configurar badge del turno
                const turnoBadge = document.getElementById('ver_turno_badge');
                turnoBadge.style.backgroundColor = turno.color_hex || '#007bff';
                turnoBadge.textContent = turno.nombre.substring(0, 3).toUpperCase();

                // Configurar badge de tipo
                const tipoBadge = document.getElementById('ver_tipo_badge');
                tipoBadge.className = `badge ${getTipoBadgeClass(turno.tipo)}`;
                tipoBadge.textContent = formatTipo(turno.tipo);

                // Configurar badge de estado
                const estadoBadge = document.getElementById('ver_estado_badge');
                estadoBadge.className = `badge ${getEstadoBadgeClass(turno.estado)}`;
                estadoBadge.textContent = formatEstado(turno.estado);

                // Configurar preview de color
                const colorPreview = document.getElementById('ver_color_preview');
                colorPreview.style.backgroundColor = turno.color_hex || '#007bff';

                // Mostrar días de la semana
                const diasContainer = document.getElementById('ver_dias_semana');
                if (turno.dias_semana) {
                    try {
                        const dias = typeof turno.dias_semana === 'string' 
                            ? JSON.parse(turno.dias_semana) 
                            : turno.dias_semana;
                        
                        const diasCompletos = {
                            'lunes': 'Lunes',
                            'martes': 'Martes',
                            'miercoles': 'Miércoles',
                            'jueves': 'Jueves',
                            'viernes': 'Viernes',
                            'sabado': 'Sábado',
                            'domingo': 'Domingo'
                        };
                        
                        diasContainer.innerHTML = dias.map(dia => 
                            `<span class="badge bg-primary me-1">${diasCompletos[dia] || dia}</span>`
                        ).join('');
                    } catch (error) {
                        diasContainer.innerHTML = '<span class="text-muted">Error al cargar días</span>';
                    }
                } else {
                    diasContainer.innerHTML = '<span class="text-muted">No definido</span>';
                }

            } catch (error) {
                console.error('Error al mostrar turno:', error);
                mostrarError('Error al cargar los detalles del turno');
            }
        }

        // ========== FUNCIONES DE APOYO ==========
        function obtenerDatosFormulario(tipo) {
            const prefix = tipo === 'crear' ? 'crear_' : 'editar_';
            
            // Obtener días seleccionados
            const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
            const diasSeleccionados = dias.filter(dia => 
                document.getElementById(prefix + dia).checked
            );
            
            return {
                nombre: document.getElementById(prefix + 'nombre').value.trim(),
                tipo: document.getElementById(prefix + 'tipo').value,
                hora_inicio: document.getElementById(prefix + 'hora_inicio').value,
                hora_fin: document.getElementById(prefix + 'hora_fin').value,
                dias_semana: JSON.stringify(diasSeleccionados),
                descripcion: document.getElementById(prefix + 'descripcion').value.trim(),
                estado: document.getElementById(prefix + 'estado').value,
                color_hex: document.getElementById(prefix + 'color_hex').value
            };
        }

        function validarFormulario(datos, tipo = 'crear') {
            const errores = [];

            if (!datos.nombre) errores.push('El nombre es requerido');
            if (!datos.tipo) errores.push('El tipo es requerido');
            if (!datos.hora_inicio) errores.push('La hora de inicio es requerida');
            if (!datos.hora_fin) errores.push('La hora de fin es requerida');
            if (!datos.color_hex || !/^#[0-9A-F]{6}$/i.test(datos.color_hex)) {
                errores.push('El color debe ser un código hexadecimal válido');
            }

            // Validar que hora_fin sea mayor que hora_inicio
            if (datos.hora_inicio && datos.hora_fin && datos.hora_fin <= datos.hora_inicio) {
                errores.push('La hora de fin debe ser posterior a la hora de inicio');
            }

            // Validar que al menos un día esté seleccionado
            try {
                const dias = JSON.parse(datos.dias_semana);
                if (!dias || dias.length === 0) {
                    errores.push('Debe seleccionar al menos un día de la semana');
                    
                    // Mostrar error específico para días
                    const errorElement = document.getElementById(tipo + '_dias_error');
                    if (errorElement) {
                        errorElement.textContent = 'Debe seleccionar al menos un día';
                        errorElement.style.display = 'block';
                    }
                }
            } catch (error) {
                errores.push('Error en la selección de días');
            }

            if (errores.length > 0) {
                mostrarError(errores.join('\n'));
                return false;
            }

            return true;
        }

        function limpiarFormulario(tipo) {
            const prefix = tipo === 'crear' ? 'crear_' : 'editar_';
            document.getElementById('form' + (tipo === 'crear' ? 'Crear' : 'Editar') + 'Turno').reset();
            
            // Limpiar checkboxes de días
            const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
            dias.forEach(dia => {
                document.getElementById(prefix + dia).checked = false;
            });
            
            // Resetear color
            document.getElementById(prefix + 'color_hex').value = '#007bff';
            document.getElementById(prefix + 'color_text').value = '#007bff';
            
            // Limpiar clases de validación
            document.querySelectorAll(`[id^="${prefix}"]`).forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
            });

            // Ocultar error de días
            const errorElement = document.getElementById(tipo + '_dias_error');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }

        function aplicarFiltros() {
            filtrosActivos = {};
            
            const search = document.getElementById('searchInput').value.trim();
            const tipo = document.getElementById('filtroTipo').value;
            const estado = document.getElementById('filtroEstado').value;

            if (search) filtrosActivos.search = search;
            if (tipo) filtrosActivos.tipo = tipo;
            if (estado) filtrosActivos.estado = estado;

            cargarTurnos(1); // Reiniciar a página 1
        }

        function generarPaginacion(pagination) {
            const container = document.getElementById('paginationList');
            
            if (pagination.total_pages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';
            
            // Botón anterior
            html += `
                <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="cargarTurnos(${pagination.current_page - 1})">Anterior</a>
                </li>
            `;

            // Páginas
            for (let i = 1; i <= pagination.total_pages; i++) {
                if (i === pagination.current_page || 
                    i === 1 || 
                    i === pagination.total_pages || 
                    (i >= pagination.current_page - 1 && i <= pagination.current_page + 1)) {
                    html += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="cargarTurnos(${i})">${i}</a>
                        </li>
                    `;
                } else if (i === pagination.current_page - 2 || i === pagination.current_page + 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            // Botón siguiente
            html += `
                <li class="page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="cargarTurnos(${pagination.current_page + 1})">Siguiente</a>
                </li>
            `;

            container.innerHTML = html;
        }

        // ========== FUNCIONES DE UI ==========
        function mostrarLoading(show) {
            const spinner = document.getElementById('loadingSpinner');
            const tableContainer = document.getElementById('tableContainer');
            
            if (show) {
                spinner.style.display = 'block';
                tableContainer.style.opacity = '0.5';
            } else {
                spinner.style.display = 'none';
                tableContainer.style.opacity = '1';
            }
        }

        function mostrarLoadingBoton(btnId, show) {
            const btn = document.getElementById(btnId);
            const spinner = btn.querySelector('.spinner-border');
            
            if (show) {
                btn.disabled = true;
                spinner.classList.remove('d-none');
            } else {
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        function mostrarError(mensaje) {
            Swal.fire({
                title: 'Error',
                text: mensaje,
                icon: 'error'
            });
        }
    </script>
</body>
</html>
