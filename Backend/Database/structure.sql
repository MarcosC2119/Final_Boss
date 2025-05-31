CREATE DATABASE IF NOT EXISTS roomit;

USE roomit;


CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrativo', 'docente') NOT NULL,
    telefono VARCHAR(20),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

CREATE TABLE salas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    capacidad INT NOT NULL,
    tipo ENUM('aula', 'laboratorio', 'auditorio') NOT NULL,
    tiene_proyector BOOLEAN DEFAULT FALSE,
    tiene_pizarra_digital BOOLEAN DEFAULT FALSE,
    es_accesible BOOLEAN DEFAULT FALSE,
    estado ENUM('disponible', 'ocupada', 'mantenimiento') DEFAULT 'disponible',
    descripcion TEXT
);

CREATE TABLE reservas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sala_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    duracion INT NOT NULL,
    estado ENUM('activa', 'cancelada', 'finalizada') DEFAULT 'activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notas TEXT,
    FOREIGN KEY (sala_id) REFERENCES salas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE horarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sala_id INT NOT NULL,
    dia_semana ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    FOREIGN KEY (sala_id) REFERENCES salas(id)
);

CREATE TABLE mantenimiento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sala_id INT NOT NULL,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    descripcion TEXT,
    estado ENUM('programado', 'en_proceso', 'completado') DEFAULT 'programado',
    FOREIGN KEY (sala_id) REFERENCES salas(id)
);

CREATE INDEX idx_reservas_fecha ON reservas(fecha);
CREATE INDEX idx_reservas_estado ON reservas(estado);
CREATE INDEX idx_salas_estado ON salas(estado);
CREATE INDEX idx_usuarios_email ON usuarios(email);