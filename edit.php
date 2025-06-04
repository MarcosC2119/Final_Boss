<?php
include 'auth.php';
include 'db.php';
include 'header.php';

$id = $_GET['id'];
$proyecto = $conn->query("SELECT * FROM proyectos WHERE id=$id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $titulo = $_POST['titulo'];
  $descripcion = $_POST['descripcion'];
  $url_github = $_POST['url_github'];
  $url_produccion = $_POST['url_produccion'];

  if ($_FILES['imagen']['name']) {
    $imagen = $_FILES['imagen']['name'];
    move_uploaded_file($_FILES['imagen']['tmp_name'], "uploads/$imagen");
    $img_sql = ", imagen='$imagen'";
  } else {
    $img_sql = "";
  }

  $sql = "UPDATE proyectos SET titulo='$titulo', descripcion='$descripcion', url_github='$url_github', url_produccion='$url_produccion' $img_sql WHERE id=$id";
  $conn->query($sql);
  header("Location: index.php");
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-extrabold text-white mb-8">Editar Proyecto</h1>
        
        <form method="post" enctype="multipart/form-data" class="bg-gray-800 shadow-xl rounded-lg p-8 border border-gray-700">
            <div class="space-y-6">
                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-300">Título</label>
                    <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($proyecto['titulo']) ?>" required
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 
                                  text-white placeholder-gray-400 focus:outline-none focus:ring-indigo-500 
                                  focus:border-indigo-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="descripcion" class="block text-sm font-medium text-gray-300">Descripción</label>
                    <textarea name="descripcion" id="descripcion" maxlength="200" required
                              class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 
                                     text-white placeholder-gray-400 focus:outline-none focus:ring-indigo-500 
                                     focus:border-indigo-500 sm:text-sm h-32"><?= htmlspecialchars($proyecto['descripcion']) ?></textarea>
                </div>
                
                <div>
                    <label for="url_github" class="block text-sm font-medium text-gray-300">URL GitHub</label>
                    <input type="url" name="url_github" id="url_github" value="<?= htmlspecialchars($proyecto['url_github']) ?>"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 
                                  text-white placeholder-gray-400 focus:outline-none focus:ring-indigo-500 
                                  focus:border-indigo-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="url_produccion" class="block text-sm font-medium text-gray-300">URL Producción</label>
                    <input type="url" name="url_produccion" id="url_produccion" value="<?= htmlspecialchars($proyecto['url_produccion']) ?>"
                           class="mt-1 block w-full bg-gray-700 border border-gray-600 rounded-md shadow-sm py-2 px-3 
                                  text-white placeholder-gray-400 focus:outline-none focus:ring-indigo-500 
                                  focus:border-indigo-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="imagen" class="block text-sm font-medium text-gray-300">Imagen</label>
                    <?php if($proyecto['imagen']): ?>
                        <div class="mb-2">
                            <img src="uploads/<?= htmlspecialchars($proyecto['imagen']) ?>" 
                                 alt="Imagen actual" 
                                 class="h-32 w-auto object-cover rounded-md">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="imagen" id="imagen"
                           class="mt-1 block w-full text-gray-300 file:mr-4 file:py-2 file:px-4 
                                  file:rounded-md file:border-0 file:text-sm file:font-semibold 
                                  file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                    <p class="mt-1 text-sm text-gray-400">Deja vacío para mantener la imagen actual</p>
                </div>
                
                <div class="flex items-center justify-between pt-4">
                    <a href="index.php" 
                       class="text-gray-300 hover:text-white transition duration-150 ease-in-out">
                        ← Volver al inicio
                    </a>
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md text-sm 
                                   font-medium transition duration-150 ease-in-out">
                        Actualizar Proyecto
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>