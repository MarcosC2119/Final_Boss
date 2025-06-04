<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portafolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900 text-gray-100">
    <!-- Navbar -->
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-bold text-white">
                        Portafolio
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user'])): ?>
                        <a href="add.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                            + Nuevo Proyecto
                        </a>
                        <a href="logout.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                            Cerrar sesión
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</body>
</html> 