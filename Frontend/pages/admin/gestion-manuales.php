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
    <title>Gestión de Manuales - ROOMIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .file-upload-area:hover {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }
        .file-upload-area.dragover {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Gestión de Manuales</h2>
                <p class="text-muted mb-0">Administra y controla los manuales del sistema</p>
            </div>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalManual" onclick="abrirModalNuevo()">
                <i class="bi bi-plus-circle me-2"></i>Nuevo Manual
            </button>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title" id="totalManuales">0</h3>
                                <p class="card-text"><i class="bi bi-book me-2"></i>Total Manuales</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-book fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title" id="manualesActivos">0</h3>
                                <p class="card-text"><i class="bi bi-check-circle me-2"></i>Activos</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-check-circle fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title" id="manualesPDF">0</h3>
                                <p class="card-text"><i class="bi bi-file-pdf me-2"></i>PDF</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-file-pdf fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title" id="manualesWord">0</h3>
                                <p class="card-text"><i class="bi bi-file-word me-2"></i>Word/Otros</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-file-word fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros y Búsqueda</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="searchInput" class="form-label">Buscar por título o descripción:</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Escriba para buscar...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="categoriaFilter" class="form-label">Categoría:</label>
                        <select class="form-select" id="categoriaFilter">
                            <option value="">Todas las categorías</option>
                            <option value="usuario">Usuario</option>
                            <option value="administrador">Administrador</option>
                            <option value="tecnico">Técnico</option>
                            <option value="general">General</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="estadoFilter" class="form-label">Estado:</label>
                        <select class="form-select" id="estadoFilter">
                            <option value="">Todos los estados</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">
                                <i class="bi bi-search me-1"></i>Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Manuales -->
        <div class="card">
            <div class="card-body">
                <div id="loadingTable" class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando manuales...</p>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tablaManuales">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Versión</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Creado por</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody">
                            <!-- Los datos se cargarán dinámicamente -->
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje cuando no hay manuales -->
                <div id="noResultsMessage" class="text-center py-4" style="display: none;">
                    <i class="bi bi-book fs-1 text-muted"></i>
                    <h5 class="text-muted mt-3">No se encontraron manuales</h5>
                    <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Manual -->
    <div class="modal fade" id="modalManual" tabindex="-1" aria-labelledby="modalManualLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalManualLabel">
                        <i class="bi bi-book me-2"></i>Nuevo Manual
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formManual" enctype="multipart/form-data">
                        <input type="hidden" id="manualId" name="id">
                        <input type="hidden" id="creadoPor" name="creado_por">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="titulo" class="form-label">Título *</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="version" class="form-label">Versión</label>
                                <input type="text" class="form-control" id="version" name="version" value="1.0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">Categoría *</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="">Seleccionar categoría</option>
                                    <option value="usuario">Usuario</option>
                                    <option value="administrador">Administrador</option>
                                    <option value="tecnico">Técnico</option>
                                    <option value="general">General</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipo_archivo" class="form-label">Tipo de Archivo</label>
                                <select class="form-select" id="tipo_archivo" name="tipo_archivo">
                                    <option value="PDF">PDF</option>
                                    <option value="WORD">Word</option>
                                    <option value="HTML">HTML</option>
                                    <option value="TEXTO">Texto</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Archivo del Manual</label>
                            <div class="file-upload-area" id="fileUploadArea">
                                <input type="file" class="d-none" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.html,.txt">
                                <i class="bi bi-cloud-upload fs-1 text-muted mb-3"></i>
                                <p class="text-muted mb-2">Haga clic aquí o arrastre su archivo</p>
                                <p class="small text-muted">Formatos: PDF, DOC, DOCX, HTML, TXT (Máx. 10MB)</p>
                            </div>
                            <div id="fileName" class="mt-2 text-success d-none"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="guardarManual()">
                        <i class="bi bi-floppy me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    
    <script>
        // ========== CONFIGURACIÓN GLOBAL ==========
        const API_BASE_URL = '../../../Backend/CRUD-admin/Crud-manuales.php';
        let manuales = [];
        let isEditing = false;
        let currentUser = null;

        // ========== FUNCIÓN PRINCIPAL QUE SE EJECUTA CUANDO CARGA LA PÁGINA ==========
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar autenticación
            if (!verificarAutenticacion()) {
                window.location.href = '/Final_Boss/Frontend/login.php';
                return;
            }
            
            // Inicializar la página
            inicializarEventos();
            cargarManuales();
        });

        // ========== VERIFICACIÓN DE AUTENTICACIÓN ==========
        function verificarAutenticacion() {
            const userData = localStorage.getItem('userData');
            if (!userData) return false;
            
            const user = JSON.parse(userData);
            if (user && user.rol === 'administrativo') {
                currentUser = user;
                document.getElementById('creadoPor').value = user.id || 1;
                return true;
            }
            return false;
        }

        // ========== INICIALIZACIÓN DE EVENTOS ==========
        function inicializarEventos() {
            // Búsqueda en tiempo real
            let searchTimeout;
            document.getElementById('searchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    aplicarFiltros();
                }, 500);
            });
            
            // Filtros
            document.getElementById('categoriaFilter').addEventListener('change', aplicarFiltros);
            document.getElementById('estadoFilter').addEventListener('change', aplicarFiltros);
            
            // Upload de archivos
            const fileUploadArea = document.getElementById('fileUploadArea');
            const fileInput = document.getElementById('archivo');
            
            fileUploadArea.addEventListener('click', () => fileInput.click());
            fileUploadArea.addEventListener('dragover', handleDragOver);
            fileUploadArea.addEventListener('dragleave', handleDragLeave);
            fileUploadArea.addEventListener('drop', handleDrop);
            
            fileInput.addEventListener('change', handleFileSelect);
        }

        // ========== CARGA DE DATOS ==========
        async function cargarManuales() {
            try {
                mostrarLoading(true);
                const response = await fetch(API_BASE_URL);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                manuales = Array.isArray(data) ? data : [];
                actualizarEstadisticas();
                renderizarTabla(manuales);
            } catch (error) {
                console.error('Error al cargar manuales:', error);
                mostrarError('Error al cargar manuales: ' + error.message);
                manuales = [];
                renderizarTabla(manuales);
            } finally {
                mostrarLoading(false);
            }
        }

        function actualizarEstadisticas() {
            const total = manuales.length;
            const activos = manuales.filter(m => m.estado == 1).length;
            const pdfs = manuales.filter(m => m.archivo_tipo === 'PDF').length;
            const otros = total - pdfs;
            
            document.getElementById('totalManuales').textContent = total;
            document.getElementById('manualesActivos').textContent = activos;
            document.getElementById('manualesPDF').textContent = pdfs;
            document.getElementById('manualesWord').textContent = otros;
        }

        function renderizarTabla(datos) {
            const tbody = document.getElementById('tablaBody');
            const noResults = document.getElementById('noResultsMessage');
            
            if (datos.length === 0) {
                tbody.innerHTML = '';
                noResults.style.display = 'block';
                return;
            }

            noResults.style.display = 'none';
            tbody.innerHTML = datos.map(manual => `
                <tr>
                    <td>${manual.id}</td>
                    <td><strong>${escapeHtml(manual.titulo)}</strong></td>
                    <td>${escapeHtml(manual.descripcion || 'Sin descripción')}</td>
                    <td><span class="badge bg-primary">${manual.categoria}</span></td>
                    <td>${manual.version}</td>
                    <td><span class="badge bg-success">${manual.archivo_tipo}</span></td>
                    <td>
                        <span class="badge ${manual.estado == 1 ? 'bg-success' : 'bg-danger'}">
                            ${manual.estado == 1 ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td>${escapeHtml(manual.creado_por_nombre || 'N/A')}</td>
                    <td>${formatearFecha(manual.fecha_creacion)}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="editarManual(${manual.id})" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-success" onclick="descargarManual(${manual.id})" title="Descargar">
                                <i class="bi bi-download"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="confirmarEliminarManual(${manual.id}, '${escapeHtml(manual.titulo)}')" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        // ========== FILTROS Y BÚSQUEDA ==========
        function aplicarFiltros() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const categoria = document.getElementById('categoriaFilter').value;
            const estado = document.getElementById('estadoFilter').value;
            
            let datosFiltrados = manuales;
            
            if (search) {
                datosFiltrados = datosFiltrados.filter(manual => 
                    manual.titulo.toLowerCase().includes(search) || 
                    (manual.descripcion && manual.descripcion.toLowerCase().includes(search))
                );
            }
            
            if (categoria) {
                datosFiltrados = datosFiltrados.filter(manual => manual.categoria === categoria);
            }
            
            if (estado !== '') {
                datosFiltrados = datosFiltrados.filter(manual => manual.estado == estado);
            }
            
            renderizarTabla(datosFiltrados);
        }

        // ========== MODAL DE MANUAL ==========
        function abrirModalNuevo() {
            isEditing = false;
            document.getElementById('modalManualLabel').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Nuevo Manual';
            document.getElementById('formManual').reset();
            document.getElementById('manualId').value = '';
            document.getElementById('creadoPor').value = currentUser.id || 1;
            document.getElementById('fileName').classList.add('d-none');
        }

        async function editarManual(id) {
            try {
                isEditing = true;
                document.getElementById('modalManualLabel').innerHTML = '<i class="bi bi-pencil me-2"></i>Editar Manual';
                
                const response = await fetch(`${API_BASE_URL}?id=${id}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const manual = await response.json();
                
                // Llenar el formulario
                document.getElementById('manualId').value = manual.id;
                document.getElementById('titulo').value = manual.titulo;
                document.getElementById('descripcion').value = manual.descripcion || '';
                document.getElementById('categoria').value = manual.categoria;
                document.getElementById('version').value = manual.version;
                document.getElementById('tipo_archivo').value = manual.archivo_tipo;
                document.getElementById('estado').value = manual.estado;
                
                // Mostrar nombre del archivo actual
                if (manual.archivo_nombre) {
                    document.getElementById('fileName').innerHTML = `
                        <i class="bi bi-file me-2"></i>Archivo actual: ${manual.archivo_nombre}
                    `;
                    document.getElementById('fileName').classList.remove('d-none');
                }
                
                new bootstrap.Modal(document.getElementById('modalManual')).show();
            } catch (error) {
                console.error('Error al cargar manual:', error);
                mostrarError('No se pudo cargar la información del manual: ' + error.message);
            }
        }

        async function guardarManual() {
            const form = document.getElementById('formManual');
            const formData = new FormData(form);
            
            // Validaciones
            if (!formData.get('titulo').trim()) {
                mostrarError('El título es requerido');
                return;
            }
            
            if (!formData.get('categoria')) {
                mostrarError('La categoría es requerida');
                return;
            }
            
            if (!isEditing && !formData.get('archivo').name) {
                mostrarError('Debe seleccionar un archivo para el manual');
                return;
            }
            
            try {
                const response = await fetch(API_BASE_URL, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.error);
                }
                
                mostrarExito(isEditing ? 'Manual actualizado correctamente' : 'Manual creado correctamente');
                bootstrap.Modal.getInstance(document.getElementById('modalManual')).hide();
                cargarManuales();
            } catch (error) {
                console.error('Error al guardar manual:', error);
                mostrarError('Error al guardar: ' + error.message);
            }
        }

        // ========== ACCIONES ==========
        async function descargarManual(id) {
            try {
                window.open(`${API_BASE_URL}?id=${id}&download=1`, '_blank');
            } catch (error) {
                console.error('Error al descargar manual:', error);
                mostrarError('No se pudo descargar el manual');
            }
        }

        async function confirmarEliminarManual(id, titulo) {
            const result = await Swal.fire({
                title: '¿Está seguro?',
                text: `Esta acción eliminará el manual "${titulo}" permanentemente`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            
            if (result.isConfirmed) {
                await eliminarManual(id);
            }
        }

        async function eliminarManual(id) {
            try {
                const response = await fetch(`${API_BASE_URL}?id=${id}`, {
                    method: 'DELETE'
                });
                
                if (response.ok) {
                    mostrarExito('Manual eliminado correctamente');
                    cargarManuales();
                } else {
                    throw new Error('Error al eliminar');
                }
            } catch (error) {
                console.error('Error al eliminar manual:', error);
                mostrarError('No se pudo eliminar el manual');
            }
        }

        // ========== MANEJO DE ARCHIVOS ==========
        function handleDragOver(e) {
            e.preventDefault();
            e.currentTarget.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
        }

        function handleDrop(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('archivo').files = files;
                handleFileSelect({ target: { files: files } });
            }
        }

        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamaño (10MB máximo)
                if (file.size > 10 * 1024 * 1024) {
                    mostrarError('El archivo no puede superar los 10MB');
                    document.getElementById('archivo').value = '';
                    return;
                }
                
                // Mostrar nombre del archivo
                document.getElementById('fileName').innerHTML = `
                    <i class="bi bi-file me-2"></i>Archivo seleccionado: ${file.name}
                `;
                document.getElementById('fileName').classList.remove('d-none');
            }
        }

        // ========== FUNCIONES AUXILIARES ==========
        function mostrarLoading(show) {
            document.getElementById('loadingTable').style.display = show ? 'block' : 'none';
            document.getElementById('tablaManuales').style.opacity = show ? '0.5' : '1';
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

        function formatearFecha(fecha) {
            if (!fecha) return 'N/A';
            return new Date(fecha).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>