<?php
require_once '../db.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <section class="container">
        <h1>Gestion de salas</h1>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <li>
                        <a href="">Agregar sala</a>
                    </li>
                    <form id="form-agregar-sala">
                        <input type="text" id="nombre" placeholder="Nombre">
                        <input type="text" id="ubicacion" placeholder="Ubicacion">
                        <input type="number" id="capacidad" placeholder="Capacidad">
                        <button id="btn-agregar-sala">Agregar sala</button>
                    </form>
                    
                </div>
            </div>
        </div>
    </section>


</body>
</html>

<script>
    const btnAgregarSala = document.getElementById('btn-agregar-sala');
    btnAgregarSala.addEventListener('click', () => {
        const nombre = document.getElementById('nombre').value;
        const ubicacion = document.getElementById('ubicacion').value;
        const capacidad = document.getElementById('capacidad').value;
        createSala(nombre, ubicacion, capacidad);
        console.log('Agregar sala');
    });
</script>