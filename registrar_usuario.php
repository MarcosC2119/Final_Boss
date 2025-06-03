<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Registrar Nuevo Usuario</h4>
                    </div>
                    <div class="card-body">
                        <form id="registro-form">
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rol</label>
                                <select class="form-control" id="rol" required>
                                    <option value="docente">Docente</option>
                                    <option value="administrativo">Administrativo</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Registrar Usuario</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registro-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                nombre: document.getElementById('nombre').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                rol: document.getElementById('rol').value
            };
            
            try {
                const response = await fetch('Backend/api/registrar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Usuario registrado exitosamente');
                    document.getElementById('registro-form').reset();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Error al registrar usuario');
            }
        });
    </script>
</body>
</html> 