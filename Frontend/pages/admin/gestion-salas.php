z<?php
include '../../Backend/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Salas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-building"></i> Sistema de Gestión de Salas
            </a>
        </div>
    </nav>

    <section class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-plus-circle me-2"></i>Agregar Nueva Sala
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="form-agregar-sala" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre de la Sala</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-door-open"></i></span>
                                    <input type="text" class="form-control" id="nombre" placeholder="Ingrese el nombre de la sala" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="ubicacion" class="form-label">Ubicación</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" class="form-control" id="ubicacion" placeholder="Ingrese la ubicación" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="capacidad" class="form-label">Capacidad</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-people"></i></span>
                                    <input type="number" class="form-control" id="capacidad" placeholder="Ingrese la capacidad" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" id="btn-agregar-sala">
                                    <i class="bi bi-plus-circle me-2"></i>Agregar Sala
                                </button>
                            </div>
                        </form>
                        <div id="mensaje" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const formAgregarSala = document.getElementById('form-agregar-sala');
        const mensajeDiv = document.getElementById('mensaje');

        formAgregarSala.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const nombre = document.getElementById('nombre').value;
            const ubicacion = document.getElementById('ubicacion').value;
            const capacidad = document.getElementById('capacidad').value;

            try {
                const response = await fetch('../../Backend/api/Create.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        nombre: nombre,
                        ubicacion: ubicacion,
                        capacidad: capacidad
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    mensajeDiv.innerHTML = `
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div>Sala agregada exitosamente</div>
                        </div>`;
                    formAgregarSala.reset();
                } else {
                    mensajeDiv.innerHTML = `
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div>Error al agregar la sala: ${data.message}</div>
                        </div>`;
                }
            } catch (error) {
                mensajeDiv.innerHTML = `
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>Error al conectar con el servidor</div>
                    </div>`;
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>