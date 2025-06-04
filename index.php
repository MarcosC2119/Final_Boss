<?php
include 'auth.php';
include 'db.php';
$result = $conn->query("SELECT * FROM proyectos ORDER BY created_at DESC");
include 'header.php';
?>

<!-- Hero Section -->
<div class="bg-gray-800 border-b border-gray-700">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-white sm:text-4xl">
            Mis Proyectos
        </h1>
        <p class="mt-4 text-lg text-gray-300">
            Explora mi colección de proyectos y trabajos más recientes
        </p>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Grid de Proyectos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php while($row = $result->fetch_assoc()): ?>
            <article class="bg-gray-800 rounded-lg shadow-xl overflow-hidden border border-gray-700 transition duration-300 hover:transform hover:scale-105">
                <!-- Imagen del Proyecto -->
                <div class="relative h-48 overflow-hidden">
                    <img src="uploads/<?= $row['imagen'] ?>" 
                         alt="<?= htmlspecialchars($row['titulo']) ?>" 
                         class="w-full h-full object-cover">
                </div>
                
                <!-- Contenido del Proyecto -->
                <div class="p-6">
                    <h3 class="text-xl font-bold text-white mb-2">
                        <?= htmlspecialchars($row['titulo']) ?>
                    </h3>
                    <p class="text-gray-300 mb-4">
                        <?= htmlspecialchars($row['descripcion']) ?>
                    </p>
                    
                    <!-- Enlaces -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <?php if($row['url_github']): ?>
                            <a href="<?= $row['url_github'] ?>" 
                               target="_blank"
                               class="inline-flex items-center px-3 py-1 bg-gray-700 text-white text-sm rounded-md hover:bg-gray-600 transition duration-300">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                                GitHub
                            </a>
                        <?php endif; ?>
                        
                        <?php if($row['url_produccion']): ?>
                            <a href="<?= $row['url_produccion'] ?>" 
                               target="_blank"
                               class="inline-flex items-center px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition duration-300">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Ver Demo
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div class="flex justify-end space-x-2">
                        <a href="edit.php?id=<?= $row['id'] ?>" 
                           class="text-indigo-400 hover:text-indigo-300 transition duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <a href="delete.php?id=<?= $row['id'] ?>" 
                           onclick="return confirm('¿Estás seguro de que deseas eliminar este proyecto?')"
                           class="text-red-400 hover:text-red-300 transition duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'footer.php'; ?>