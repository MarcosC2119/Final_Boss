# 🏢 RoomIT - Sistema de Gestión de Salas y Reservas

Sistema web para la gestión y reserva de salas, turnos y capacitaciones en instituciones educativas y empresariales.

## 📋 Tabla de Contenidos
- [Características](#-características)
- [Tecnologías](#-tecnologías)
- [Requisitos Previos](#-requisitos-previos)
- [Instalación](#-instalación)
- [Configuración de Base de Datos](#-configuración-de-base-de-datos)
- [Uso del Sistema](#-uso-del-sistema)
- [Usuarios de Prueba](#-usuarios-de-prueba)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [API Endpoints](#-api-endpoints)
- [Solución de Problemas](#-solución-de-problemas)

## ✨ Características

### Para Usuarios
- 🔐 **Sistema de Autenticación**: Login seguro con roles (Administrativo/Docente)
- 📅 **Gestión de Reservas**: Reserva de salas por bloques horarios
- 🏢 **Información de Salas**: Capacidad, equipamiento y disponibilidad
- ⏰ **Control de Turnos**: Asignación y registro de turnos laborales
- 📚 **Capacitaciones**: Gestión de cursos y capacitaciones
- 🎫 **Soporte Técnico**: Sistema de tickets para reportar incidencias
- 📖 **Manuales**: Acceso a documentación y guías

### Para Administradores
- 👥 **Gestión de Usuarios**: CRUD completo de usuarios del sistema
- 🏢 **Gestión de Salas**: Administración de salas y equipamiento
- 📊 **Dashboard**: Panel de control con estadísticas
- ⚙️ **Configuración**: Gestión de horarios y parámetros del sistema

## 🛠 Tecnologías

**Backend:**
- PHP 7.4+
- MariaDB/MySQL
- MySQLi para conexión a BD

**Frontend:**
- HTML5
- Bootstrap 5
- JavaScript/jQuery
- CSS3

**Servidor:**
- Apache (XAMPP/WAMP/LAMP)

## 📋 Requisitos Previos

Antes de instalar RoomIT, asegúrate de tener instalado:

1. **XAMPP** (recomendado) o **WAMP/LAMP**
   - PHP 7.4 o superior
   - Apache 2.4+
   - MariaDB 10.4+ o MySQL 5.7+
   - phpMyAdmin

2. **Navegador Web** moderno (Chrome, Firefox, Edge, Safari)

## 🚀 Instalación

### Paso 1: Clonar el Repositorio
```bash
git clone [URL_DEL_REPOSITORIO]
cd Final_Boss
```

### Paso 2: Mover a Directorio Web
Copia todo el contenido del proyecto a la carpeta `htdocs` de XAMPP:
```
C:\xampp\htdocs\Final_Boss\
```

### Paso 3: Iniciar Servicios
1. Abre el **Panel de Control de XAMPP**
2. Inicia los servicios:
   - ✅ **Apache**
   - ✅ **MySQL**

## 🗄 Configuración de Base de Datos

### Método 1: Usando phpMyAdmin (Recomendado)

1. **Acceder a phpMyAdmin**
   - Abre tu navegador
   - Ve a: `http://localhost/phpmyadmin`

2. **Crear la Base de Datos**
   - Haz clic en "**Nuevo**" en el panel izquierdo
   - O puedes usar el archivo SQL directamente

3. **Importar Estructura**
   - Haz clic en la pestaña "**Importar**"
   - Selecciona "**Elegir archivo**"
   - Navega a: `Final_Boss/Backend/Database/structure.sql`
   - Haz clic en "**Continuar**"

4. **Insertar Usuarios de Prueba** (Opcional)
   - Repite el proceso de importación
   - Selecciona: `Final_Boss/Backend/Database/insert_test_users.sql`

### Método 2: Desde Línea de Comandos
```bash
# Acceder a MySQL
mysql -u root -p

# Crear y usar la base de datos
CREATE DATABASE roomit;
USE roomit;

# Importar estructura
source C:/xampp/htdocs/Final_Boss/Backend/Database/structure.sql;

# Importar datos de prueba (opcional)
source C:/xampp/htdocs/Final_Boss/Backend/Database/insert_test_users.sql;
```

### Verificación de la Instalación
Ejecuta esta consulta en phpMyAdmin para verificar:
```sql
USE roomit;
SHOW TABLES;
SELECT * FROM usuarios;
```

Deberías ver las siguientes tablas:
- `usuarios`
- `salas`
- `reservas`
- `turnos`
- `turno_asignaciones`
- `turno_registros`
- `capacitaciones`
- `tickets_soporte`
- `manuales`
- `horarios_disponibles`

## 🎯 Uso del Sistema

### Acceso al Sistema
1. Abre tu navegador
2. Ve a: `http://localhost/Final_Boss`
3. Serás redirigido automáticamente al login

### Primera Configuración
1. **Inicia sesión** con las credenciales de prueba
2. **Configura las salas** disponibles
3. **Define los horarios** de funcionamiento
4. **Crea usuarios** adicionales según sea necesario

## 👤 Usuarios de Prueba

Después de importar `insert_test_users.sql`, tendrás acceso a:

### Administrador
- **Email**: `admin@roomit.com`
- **Contraseña**: `admin123`
- **Rol**: Administrativo
- **Permisos**: Acceso completo al sistema

### Docente
- **Email**: `docente@roomit.com`
- **Contraseña**: `docente123`
- **Rol**: Docente
- **Permisos**: Reservas y consultas básicas

## 📁 Estructura del Proyecto

```
Final_Boss/
├── 📄 index.php                    # Punto de entrada (redirecciona al login)
├── 📄 login.php                    # Login principal (legacy)
├── 📄 registrar_usuario.php        # Registro de usuarios
├── 📁 Frontend/                    # Interfaz de usuario
│   ├── 📄 login.php                # Página de login principal
│   ├── 📄 recuperar-password.php   # Recuperación de contraseña
│   └── 📁 pages/                   # Páginas principales
│       ├── 📄 dashboard_usuario.php # Dashboard principal de usuarios
│       ├── 📄 dashboard_admin.html  # Dashboard de administrador
│       ├── 📄 sala-info.html       # Información detallada de salas
│       └── 📁 admin/               # Páginas administrativas
├── 📁 Backend/                     # Lógica del servidor
│   ├── 📁 config/                  # Configuración
│   │   └── 📄 db.php               # Conexión a base de datos
│   ├── 📁 Database/                # Scripts de base de datos
│   │   ├── 📄 structure.sql        # Estructura completa de la BD
│   │   └── 📄 insert_test_users.sql # Usuarios de prueba
│   ├── 📁 api/                     # API REST
│   │   ├── 📄 auth.php             # Autenticación
│   │   ├── 📄 logout.php           # Cierre de sesión
│   │   ├── 📁 Usuario/             # Gestión de usuarios
│   │   ├── 📁 Salas/               # Gestión de salas
│   │   ├── 📁 Reservas/            # Gestión de reservas
│   │   ├── 📁 Turnos/              # Gestión de turnos
│   │   ├── 📁 Asignaciones/        # Asignación de turnos
│   │   └── 📁 SoporteTecnico/      # Sistema de tickets
│   └── 📁 CRUD-admin/              # Operaciones CRUD administrativas
│       ├── 📄 crud-capacitaciones.php
│       └── 📄 Crud-manuales.php
└── 📄 README.md                    # Este archivo
```

## 🔌 API Endpoints

### Autenticación
- `POST /Backend/api/auth.php` - Login de usuario
- `POST /Backend/api/logout.php` - Cierre de sesión
- `GET /Backend/api/verify-session.php` - Verificar sesión activa

### Usuarios
- `GET /Backend/api/Usuarios/` - Listar usuarios
- `POST /Backend/api/Usuarios/` - Crear usuario
- `PUT /Backend/api/Usuarios/` - Actualizar usuario
- `DELETE /Backend/api/Usuarios/` - Eliminar usuario

### Salas
- `GET /Backend/api/Salas/` - Listar salas disponibles
- `POST /Backend/api/Salas/` - Crear nueva sala

### Reservas
- `GET /Backend/api/Reservas/` - Consultar reservas
- `POST /Backend/api/Reservas/` - Crear reserva
- `PUT /Backend/api/Reservas/` - Modificar reserva
- `DELETE /Backend/api/Reservas/` - Cancelar reserva

## 🔧 Solución de Problemas

### Error de Conexión a Base de Datos
**Problema**: No se puede conectar a la base de datos
**Solución**:
1. Verifica que MySQL esté ejecutándose en XAMPP
2. Confirma que la base de datos `roomit` existe
3. Revisa las credenciales en `Backend/config/db.php`

### Página en Blanco o Error 500
**Problema**: La página no carga correctamente
**Solución**:
1. Revisa los logs de Apache: `C:\xampp\apache\logs\error.log`
2. Verifica que PHP esté habilitado
3. Confirma que todos los archivos están en la ubicación correcta

### Problemas de Permisos
**Problema**: Error de permisos de escritura
**Solución**:
1. En Windows: Ejecuta XAMPP como administrador
2. En Linux/Mac: Ajusta permisos con `chmod -R 755`

### Error al Importar SQL
**Problema**: Error al ejecutar `structure.sql`
**Solución**:
1. Asegúrate de que no exista una BD `roomit` previa
2. Ejecuta: `DROP DATABASE IF EXISTS roomit;` antes de importar
3. Verifica que el archivo SQL no esté dañado

## 📞 Soporte

Si encuentras problemas durante la instalación o uso:

1. **Revisa los logs** de Apache y PHP
2. **Verifica la configuración** de la base de datos
3. **Confirma que todos los servicios** estén ejecutándose
4. **Consulta la documentación** de XAMPP si es necesario

## 📝 Notas Adicionales

- **Seguridad**: Cambia las contraseñas por defecto en producción
- **Performance**: Considera usar índices adicionales en la BD para consultas frecuentes
- **Backup**: Realiza respaldos regulares de la base de datos
- **Logs**: Revisa los logs regularmente para identificar problemas

---

### 🚀 ¡Listo para usar RoomIT!

Una vez completada la instalación, tendrás un sistema completo de gestión de salas y reservas funcionando en tu entorno local.
