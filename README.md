# Portafolio Personal - Sistema de Gestión de Proyectos

Este es un sistema web para gestionar y mostrar un portafolio personal de proyectos. Permite a los administradores agregar, editar y eliminar proyectos, incluyendo detalles como título, descripción, enlaces a GitHub y producción, e imágenes.

## 🚀 Características

- Sistema de autenticación de usuarios
- Gestión completa de proyectos (CRUD)
- Almacenamiento de imágenes de proyectos
- Interfaz responsive con Tailwind CSS
- Panel de administración seguro
- Base de datos MySQL para almacenamiento persistente

## 📋 Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- XAMPP (recomendado para desarrollo local)

## 🛠️ Instalación

1. Clona este repositorio en tu servidor web:
   ```bash
   git clone [URL_DEL_REPOSITORIO]
   ```

2. Importa la base de datos:
   - Accede a phpMyAdmin (http://localhost/phpmyadmin)
   - Crea una nueva base de datos llamada `portafoliov2`
   - Importa el archivo `sql/portafolio.sql`

3. Configura la conexión a la base de datos:
   - Abre `db.php`
   - Verifica que los datos de conexión coincidan con tu configuración:
     ```php
     $host = "localhost";
     $db = "portafoliov2";
     $user = "root";
     $pass = "";
     ```

4. Accede al sistema:
   - URL: http://localhost/Portafolio
   - Usuario por defecto: `admin`
   - Contraseña por defecto: `admin123`

## 📁 Estructura del Proyecto

```
Portafolio/
├── sql/                    # Scripts de base de datos
│   └── portafolio.sql     # Esquema y datos iniciales
├── uploads/               # Directorio para imágenes de proyectos
├── add.php               # Agregar nuevos proyectos
├── auth.php              # Autenticación
├── db.php                # Configuración de base de datos
├── delete.php            # Eliminar proyectos
├── edit.php              # Editar proyectos existentes
├── footer.php            # Pie de página común
├── header.php            # Encabezado común
├── index.php             # Página principal
├── login.php             # Página de inicio de sesión
└── logout.php            # Cierre de sesión
```

## 🔒 Seguridad

- Las contraseñas se almacenan usando MD5 (se recomienda actualizar a un método más seguro en producción)
- Sistema de autenticación para proteger el panel de administración
- Validación de datos en formularios
- Protección contra inyección SQL usando mysqli

## 🎨 Tecnologías Utilizadas

- PHP (Backend)
- MySQL (Base de datos)
- HTML5
- CSS (Tailwind CSS)
- JavaScript
- XAMPP (Entorno de desarrollo)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE.md](LICENSE.md) para más detalles.

## ✨ Créditos

Desarrollado con asistencia de:
- GitHub Copilot