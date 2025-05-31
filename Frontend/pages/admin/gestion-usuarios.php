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
    <title>Gestión de Usuarios - RoomIT</title>
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
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(45deg, #007bff, #6610f2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
            <h2 class="mb-0">Gestión de Usuarios</h2>
                <p class="text-muted mb-0">Administra usuarios del sistema RoomIT</p>
            </div>
            <button id="btnNuevoUsuario" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
                <i class="bi bi-plus-lg me-2"></i>Nuevo Usuario
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
                            <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre o email...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="filtroRol" class="form-select">
                            <option value="">Todos los roles</option>
                            <option value="administrativo">Administrativo</option>
                            <option value="docente">Docente</option>
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

        <!-- Users Table -->
        <div class="card">
            <div class="card-body table-container">
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando usuarios...</p>
                </div>
                
                <div id="tableContainer" class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Último acceso</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usuariosTableBody">
                            <!-- Los usuarios se cargarán dinámicamente aquí -->
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje cuando no hay usuarios -->
                <div id="noResultsMessage" class="text-center py-4" style="display: none;">
                    <i class="bi bi-person-x fs-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No se encontraron usuarios</h5>
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

    <!-- Create User Modal -->
    <div class="modal fade" id="crearUsuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearUsuario">
                        <div class="mb-3">
                            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" id="crear_nombre" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="crear_email" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" id="crear_password" class="form-control" required minlength="6">
                            <div class="form-text">Mínimo 6 caracteres</div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar contraseña <span class="text-danger">*</span></label>
                            <input type="password" id="crear_confirmar_password" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol <span class="text-danger">*</span></label>
                            <select id="crear_rol" class="form-select" required>
                                <option value="">Seleccionar rol</option>
                                <option value="administrativo">Administrativo</option>
                                <option value="docente">Docente</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" id="crear_telefono" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnGuardarUsuario" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        Crear Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarUsuario">
                        <input type="hidden" id="editar_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" id="editar_nombre" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="editar_email" class="form-control" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol <span class="text-danger">*</span></label>
                            <select id="editar_rol" class="form-select" required>
                                <option value="administrativo">Administrativo</option>
                                <option value="docente">Docente</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" id="editar_telefono" class="form-control">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado <span class="text-danger">*</span></label>
                            <select id="editar_estado" class="form-select" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnActualizarUsuario" class="btn btn-primary">
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
        // ========== CONFIGURACIÓN Y VARIABLES GLOBALES ==========
        const API_BASE_URL = '/Final_Boss/Backend/api/Usuarios/Metodos-Usuario.php';
        let currentPage = 1;
        let currentLimit = 10;
        let currentFilters = {
            search: '',
            rol: '',
            estado: ''
        };

        // ========== FUNCIÓN PRINCIPAL QUE SE EJECUTA CUANDO CARGA LA PÁGINA ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar autenticación
            if (!verificarAutenticacion()) {
                window.location.href = '/Final_Boss/Frontend/login.php';
                return;
            }
            
            // Inicializar la página
            inicializarEventos();
            cargarUsuarios();
        });

        // ========== VERIFICACIÓN DE AUTENTICACIÓN ==========
        function verificarAutenticacion() {
            const userData = localStorage.getItem('userData');
            if (!userData) return false;
            
            const user = JSON.parse(userData);
            return user && user.rol === 'administrativo';
        }

        // ========== INICIALIZACIÓN DE EVENTOS ==========
        function inicializarEventos() {
            // Evento de búsqueda con debounce
            let searchTimeout;
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentFilters.search = this.value.trim();
                    currentPage = 1;
                    cargarUsuarios();
                }, 500);
            });

            // Eventos de filtros
            document.getElementById('filtroRol').addEventListener('change', function() {
                currentFilters.rol = this.value;
                currentPage = 1;
                cargarUsuarios();
            });

            document.getElementById('filtroEstado').addEventListener('change', function() {
                currentFilters.estado = this.value;
                currentPage = 1;
                cargarUsuarios();
            });

            // Evento del botón filtrar
            document.getElementById('btnFiltrar').addEventListener('click', function() {
                cargarUsuarios();
            });

            // Eventos de modales
            document.getElementById('btnGuardarUsuario').addEventListener('click', crearUsuario);
            document.getElementById('btnActualizarUsuario').addEventListener('click', actualizarUsuario);

            // Validación en tiempo real de confirmación de contraseña
            document.getElementById('crear_confirmar_password').addEventListener('input', function() {
                const password = document.getElementById('crear_password').value;
                const confirmar = this.value;
                
                if (confirmar && password !== confirmar) {
                    this.setCustomValidity('Las contraseñas no coinciden');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });
        }

        // ========== FUNCIÓN PRINCIPAL PARA CARGAR USUARIOS ==========
        async function cargarUsuarios() {
            try {
                mostrarLoading(true);
                
                // Construir URL con parámetros
                const params = new URLSearchParams({
                    page: currentPage,
                    limit: currentLimit
                });

                if (currentFilters.search) params.append('q', currentFilters.search);
                if (currentFilters.rol) params.append('rol', currentFilters.rol);
                if (currentFilters.estado) params.append('estado', currentFilters.estado);

                const response = await fetch(`${API_BASE_URL}?${params}`);
                const data = await response.json();

                if (data.success) {
                    renderizarTablaUsuarios(data.data);
                    renderizarPaginacion(data.pagination || { page: 1, total_pages: 1, total: data.data.length });
                } else {
                    throw new Error(data.error || 'Error al cargar usuarios');
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error al cargar usuarios: ' + error.message);
                mostrarTablaVacia();
            } finally {
                mostrarLoading(false);
            }
        }

        // ========== RENDERIZADO DE TABLA ==========
        function renderizarTablaUsuarios(usuarios) {
            const tbody = document.getElementById('usuariosTableBody');
            const noResults = document.getElementById('noResultsMessage');
            
            if (!usuarios || usuarios.length === 0) {
                mostrarTablaVacia();
                return;
            }

            tbody.innerHTML = usuarios.map(usuario => `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3">
                                ${obtenerIniciales(usuario.nombre)}
                </div>
                            <div>
                                <div class="fw-semibold">${escapeHtml(usuario.nombre)}</div>
                                <small class="text-muted">ID: ${usuario.id}</small>
                </div>
            </div>
                    </td>
                    <td>${escapeHtml(usuario.email)}</td>
                    <td>
                        <span class="badge ${obtenerColorRol(usuario.rol)}">
                            ${capitalizar(usuario.rol)}
                        </span>
                    </td>
                    <td>${usuario.telefono || '<span class="text-muted">No especificado</span>'}</td>
                    <td>
                        <span class="badge ${obtenerColorEstado(usuario.estado)}">
                            ${capitalizar(usuario.estado)}
                        </span>
                    </td>
                    <td>
                        <small class="text-muted">
                            ${usuario.ultimo_acceso ? formatearFecha(usuario.ultimo_acceso) : 'Nunca'}
                        </small>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-1" 
                                onclick="editarUsuario(${usuario.id})" 
                                title="Editar usuario">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" 
                                onclick="confirmarEliminarUsuario(${usuario.id}, '${escapeHtml(usuario.nombre)}')" 
                                title="Eliminar usuario">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');

            noResults.style.display = 'none';
        }

        function mostrarTablaVacia() {
            document.getElementById('usuariosTableBody').innerHTML = '';
            document.getElementById('noResultsMessage').style.display = 'block';
            document.getElementById('paginationList').innerHTML = '';
        }

        // ========== RENDERIZADO DE PAGINACIÓN ==========
        function renderizarPaginacion(pagination) {
            const paginationList = document.getElementById('paginationList');
            
            if (!pagination || pagination.total_pages <= 1) {
                paginationList.innerHTML = '';
                return;
            }

            let html = '';
            
            // Botón anterior
            html += `
                <li class="page-item ${pagination.page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPagina(${pagination.page - 1})" tabindex="-1">
                        Anterior
                    </a>
                </li>
            `;

            // Números de página
            const startPage = Math.max(1, pagination.page - 2);
            const endPage = Math.min(pagination.total_pages, pagination.page + 2);

            if (startPage > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(1)">1</a></li>`;
                if (startPage > 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                html += `
                    <li class="page-item ${i === pagination.page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>
                    </li>
                `;
            }

            if (endPage < pagination.total_pages) {
                if (endPage < pagination.total_pages - 1) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                html += `<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(${pagination.total_pages})">${pagination.total_pages}</a></li>`;
            }

            // Botón siguiente
            html += `
                <li class="page-item ${pagination.page === pagination.total_pages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPagina(${pagination.page + 1})">
                        Siguiente
                    </a>
                </li>
            `;

            paginationList.innerHTML = html;
        }

        function cambiarPagina(page) {
            if (page < 1) return;
            currentPage = page;
            cargarUsuarios();
        }

        // ========== CREAR USUARIO ==========
        async function crearUsuario() {
            const form = document.getElementById('formCrearUsuario');
            const btn = document.getElementById('btnGuardarUsuario');
            const spinner = btn.querySelector('.spinner-border');

            // Validar formulario
            if (!validarFormularioCrear()) return;

            try {
                // Mostrar loading
                btn.disabled = true;
                spinner.classList.remove('d-none');

                // Obtener datos del formulario
                const datos = {
                    nombre: document.getElementById('crear_nombre').value.trim(),
                    email: document.getElementById('crear_email').value.trim(),
                    password: document.getElementById('crear_password').value,
                    confirmar_password: document.getElementById('crear_confirmar_password').value,
                    rol: document.getElementById('crear_rol').value,
                    telefono: document.getElementById('crear_telefono').value.trim()
                };

                // Realizar petición
                const response = await fetch(API_BASE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });

                const data = await response.json();

                if (data.success) {
                    // Éxito
                    mostrarExito('Usuario creado exitosamente');
                    cerrarModal('crearUsuarioModal');
                    limpiarFormulario('formCrearUsuario');
                    cargarUsuarios();
                } else {
                    throw new Error(data.error || 'Error al crear usuario');
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error al crear usuario: ' + error.message);
            } finally {
                // Ocultar loading
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // ========== EDITAR USUARIO ==========
        async function editarUsuario(id) {
            try {
                // Cargar datos del usuario
                const response = await fetch(`${API_BASE_URL}?id=${id}`);
                const data = await response.json();

                if (data.success) {
                    const usuario = data.data;
                    
                    // Llenar formulario
                    document.getElementById('editar_id').value = usuario.id;
                    document.getElementById('editar_nombre').value = usuario.nombre;
                    document.getElementById('editar_email').value = usuario.email;
                    document.getElementById('editar_rol').value = usuario.rol;
                    document.getElementById('editar_telefono').value = usuario.telefono || '';
                    document.getElementById('editar_estado').value = usuario.estado;

                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
                    modal.show();
                } else {
                    throw new Error(data.error || 'Error al cargar usuario');
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error al cargar usuario: ' + error.message);
            }
        }

        async function actualizarUsuario() {
            const form = document.getElementById('formEditarUsuario');
            const btn = document.getElementById('btnActualizarUsuario');
            const spinner = btn.querySelector('.spinner-border');

            // Validar formulario
            if (!validarFormularioEditar()) return;

            try {
                // Mostrar loading
                btn.disabled = true;
                spinner.classList.remove('d-none');

                // Obtener datos del formulario
                const datos = {
                    id: parseInt(document.getElementById('editar_id').value),
                    nombre: document.getElementById('editar_nombre').value.trim(),
                    email: document.getElementById('editar_email').value.trim(),
                    rol: document.getElementById('editar_rol').value,
                    telefono: document.getElementById('editar_telefono').value.trim(),
                    estado: document.getElementById('editar_estado').value
                };

                // Realizar petición
                const response = await fetch(API_BASE_URL, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });

                const data = await response.json();

                if (data.success) {
                    // Éxito
                    mostrarExito('Usuario actualizado exitosamente');
                    cerrarModal('editarUsuarioModal');
                    cargarUsuarios();
                } else {
                    throw new Error(data.error || 'Error al actualizar usuario');
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error al actualizar usuario: ' + error.message);
            } finally {
                // Ocultar loading
                btn.disabled = false;
                spinner.classList.add('d-none');
            }
        }

        // ========== ELIMINAR USUARIO ==========
        async function confirmarEliminarUsuario(id, nombre) {
            const result = await Swal.fire({
                title: '¿Eliminar usuario?',
                text: `¿Está seguro que desea eliminar a "${nombre}"? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                await eliminarUsuario(id);
            }
        }

        async function eliminarUsuario(id) {
            try {
                const response = await fetch(API_BASE_URL, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });

                const data = await response.json();

                if (data.success) {
                    mostrarExito('Usuario eliminado exitosamente');
                    cargarUsuarios();
                } else {
                    throw new Error(data.error || 'Error al eliminar usuario');
                }

            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error al eliminar usuario: ' + error.message);
            }
        }

        // ========== FUNCIONES DE VALIDACIÓN ==========
        function validarFormularioCrear() {
            const form = document.getElementById('formCrearUsuario');
            let valido = true;

            // Limpiar validaciones previas
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            // Validar nombre
            const nombre = document.getElementById('crear_nombre');
            if (!nombre.value.trim()) {
                mostrarErrorCampo(nombre, 'El nombre es requerido');
                valido = false;
            }

            // Validar email
            const email = document.getElementById('crear_email');
            if (!email.value.trim()) {
                mostrarErrorCampo(email, 'El email es requerido');
                valido = false;
            } else if (!validarEmail(email.value)) {
                mostrarErrorCampo(email, 'Email inválido');
                valido = false;
            }

            // Validar contraseña
            const password = document.getElementById('crear_password');
            if (!password.value) {
                mostrarErrorCampo(password, 'La contraseña es requerida');
                valido = false;
            } else if (password.value.length < 6) {
                mostrarErrorCampo(password, 'La contraseña debe tener al menos 6 caracteres');
                valido = false;
            }

            // Validar confirmación de contraseña
            const confirmar = document.getElementById('crear_confirmar_password');
            if (!confirmar.value) {
                mostrarErrorCampo(confirmar, 'Debe confirmar la contraseña');
                valido = false;
            } else if (password.value !== confirmar.value) {
                mostrarErrorCampo(confirmar, 'Las contraseñas no coinciden');
                valido = false;
            }

            // Validar rol
            const rol = document.getElementById('crear_rol');
            if (!rol.value) {
                mostrarErrorCampo(rol, 'Debe seleccionar un rol');
                valido = false;
            }

            return valido;
        }

        function validarFormularioEditar() {
            const form = document.getElementById('formEditarUsuario');
            let valido = true;

            // Limpiar validaciones previas
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            // Validar nombre
            const nombre = document.getElementById('editar_nombre');
            if (!nombre.value.trim()) {
                mostrarErrorCampo(nombre, 'El nombre es requerido');
                valido = false;
            }

            // Validar email
            const email = document.getElementById('editar_email');
            if (!email.value.trim()) {
                mostrarErrorCampo(email, 'El email es requerido');
                valido = false;
            } else if (!validarEmail(email.value)) {
                mostrarErrorCampo(email, 'Email inválido');
                valido = false;
            }

            return valido;
        }

        // ========== FUNCIONES AUXILIARES ==========
        function mostrarLoading(show) {
            const spinner = document.getElementById('loadingSpinner');
            const table = document.getElementById('tableContainer');
            
            if (show) {
                spinner.style.display = 'block';
                table.style.opacity = '0.5';
            } else {
                spinner.style.display = 'none';
                table.style.opacity = '1';
            }
        }

        function mostrarExito(mensaje) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: mensaje,
                timer: 3000,
                showConfirmButton: false
            });
        }

        function mostrarError(mensaje) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje
            });
        }

        function mostrarErrorCampo(campo, mensaje) {
            campo.classList.add('is-invalid');
            const feedback = campo.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = mensaje;
            }
        }

        function cerrarModal(modalId) {
            const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) modal.hide();
        }

        function limpiarFormulario(formId) {
            const form = document.getElementById(formId);
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        }

        function validarEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function obtenerIniciales(nombre) {
            return nombre.split(' ')
                .map(palabra => palabra.charAt(0))
                .join('')
                .toUpperCase()
                .substring(0, 2);
        }

        function capitalizar(texto) {
            return texto.charAt(0).toUpperCase() + texto.slice(1);
        }

        function obtenerColorRol(rol) {
            const colores = {
                'administrativo': 'bg-primary',
                'docente': 'bg-info'
            };
            return colores[rol] || 'bg-secondary';
        }

        function obtenerColorEstado(estado) {
            const colores = {
                'activo': 'bg-success',
                'inactivo': 'bg-danger'
            };
            return colores[estado] || 'bg-secondary';
        }

        function formatearFecha(fecha) {
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
</body>
</html>
