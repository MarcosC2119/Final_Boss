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

-- Eliminar tabla reservas actual (muy básica)
DROP TABLE IF EXISTS reservas;

-- Crear nueva tabla reservas con manejo de horarios
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    sala_id INT NOT NULL,
    fecha_reserva DATE NOT NULL,           -- Solo la fecha (2024-01-15)
    hora_inicio TIME NOT NULL,             -- Hora de inicio (08:00:00)
    hora_fin TIME NOT NULL,                -- Hora de fin (10:00:00)
    proposito VARCHAR(255) NOT NULL,       -- "Clase de Matemáticas", "Reunión de staff"
    estado ENUM('confirmada', 'cancelada', 'completada') DEFAULT 'confirmada',
    notas TEXT,                            -- Observaciones adicionales
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_cancelacion DATETIME NULL,       -- Cuándo fue cancelada (si aplica)
    
    -- Claves foráneas
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE CASCADE,
    
    -- Índices para optimizar consultas
    INDEX idx_fecha_sala (fecha_reserva, sala_id),
    INDEX idx_usuario_fecha (usuario_id, fecha_reserva),
    INDEX idx_horario (fecha_reserva, hora_inicio, hora_fin)
);



CREATE TABLE salas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,                    -- Ej: "Aula 201", "Lab Sistemas"
    capacidad INT NOT NULL,                         -- Número de personas
    tipo ENUM('aula', 'laboratorio', 'auditorio'),  -- Tipo de sala
    tiene_proyector BOOLEAN DEFAULT FALSE,          -- Equipamiento
    tiene_pizarra_digital BOOLEAN DEFAULT FALSE,    -- Equipamiento
    es_accesible BOOLEAN DEFAULT FALSE,             -- Accesibilidad
    estado ENUM('disponible', 'ocupada', 'mantenimiento'), -- Estado actual
    descripcion TEXT                                -- Descripción adicional
);

ALTER TABLE salas DROP COLUMN descripcion;
ALTER TABLE salas ADD COLUMN descripcion TEXT;

-- Primero arreglar el error de sintaxis
ALTER TABLE salas DROP COLUMN descripcion;
ALTER TABLE salas ADD COLUMN descripcion TEXT;

-- Define los bloques de tiempo permitidos para reservas
CREATE TABLE horarios_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hora_inicio TIME NOT NULL,             -- 08:00:00
    hora_fin TIME NOT NULL,                -- 09:30:00
    nombre_bloque VARCHAR(50) NOT NULL,    -- "1er Período", "2do Período"
    activo BOOLEAN DEFAULT TRUE,
    
    -- Evitar solapamientos
    UNIQUE KEY unique_horario (hora_inicio, hora_fin)
);

-- Insertar bloques de horario típicos de institución educativa
INSERT INTO horarios_disponibles (hora_inicio, hora_fin, nombre_bloque) VALUES
('08:00:00', '09:30:00', '1er Período'),
('09:45:00', '11:15:00', '2do Período'),
('11:30:00', '13:00:00', '3er Período'),
('14:00:00', '15:30:00', '4to Período'),
('15:45:00', '17:15:00', '5to Período'),
('17:30:00', '19:00:00', '6to Período');

-- Trigger para evitar reservas solapadas en la misma sala
DELIMITER $$
CREATE TRIGGER prevent_overlap_reservations
BEFORE INSERT ON reservas
FOR EACH ROW
BEGIN
    DECLARE overlap_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO overlap_count
    FROM reservas 
    WHERE sala_id = NEW.sala_id 
    AND fecha_reserva = NEW.fecha_reserva
    AND estado = 'confirmada'
    AND (
        (NEW.hora_inicio >= hora_inicio AND NEW.hora_inicio < hora_fin) OR
        (NEW.hora_fin > hora_inicio AND NEW.hora_fin <= hora_fin) OR
        (NEW.hora_inicio <= hora_inicio AND NEW.hora_fin >= hora_fin)
    );
    
    IF overlap_count > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Conflicto de horario: La sala ya está reservada en ese horario';
    END IF;
END$$
DELIMITER ;

-- Encontrar salas disponibles para una fecha y horario específico
SELECT s.*, 
       CASE WHEN r.id IS NULL THEN 'Disponible' ELSE 'Ocupada' END as disponibilidad
FROM salas s
LEFT JOIN reservas r ON s.id = r.sala_id 
    AND r.fecha_reserva = '2024-01-15'
    AND r.estado = 'confirmada'
    AND (
        ('09:00:00' >= r.hora_inicio AND '09:00:00' < r.hora_fin) OR
        ('11:00:00' > r.hora_inicio AND '11:00:00' <= r.hora_fin) OR
        ('09:00:00' <= r.hora_inicio AND '11:00:00' >= r.hora_fin)
    )
WHERE s.estado = 'disponible' 
AND r.id IS NULL;

-- Agenda completa de una sala para una fecha
SELECT r.*, u.nombre as usuario_nombre, u.email
FROM reservas r
JOIN usuarios u ON r.usuario_id = u.id
WHERE r.sala_id = 1 
AND r.fecha_reserva = '2024-01-15'
AND r.estado = 'confirmada'
ORDER BY r.hora_inicio;


-- ========== PASO 1: Eliminar tablas en orden de dependencia ==========
DROP TABLE IF EXISTS turno_registros;
DROP TABLE IF EXISTS turno_asignaciones; 
DROP TABLE IF EXISTS turnos;

-- ========== PASO 2: Recrear tabla turnos SIN restricciones ==========
CREATE TABLE turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('academico', 'laboral', 'servicio') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    dias_semana JSON,
    descripcion TEXT,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    color_hex VARCHAR(7) DEFAULT '#007bff',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_turno (nombre, tipo)
);

-- ========== PASO 3: Recrear turno_asignaciones ==========
CREATE TABLE turno_asignaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    turno_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NULL,
    dias_especificos JSON NULL,
    estado ENUM('activa', 'suspendida', 'finalizada') DEFAULT 'activa',
    observaciones TEXT,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    asignado_por INT,
    
    FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (asignado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    INDEX idx_turno_usuario (turno_id, usuario_id),
    INDEX idx_usuario_fecha (usuario_id, fecha_inicio, fecha_fin),
    INDEX idx_turno_fecha (turno_id, fecha_inicio)
);

-- ========== PASO 4: Recrear turno_registros ==========
CREATE TABLE turno_registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asignacion_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_entrada TIME,
    hora_salida TIME,
    minutos_trabajados INT GENERATED ALWAYS AS (
        CASE 
            WHEN hora_entrada IS NOT NULL AND hora_salida IS NOT NULL 
            THEN TIMESTAMPDIFF(MINUTE, 
                CONCAT(fecha, ' ', hora_entrada), 
                CONCAT(fecha, ' ', hora_salida))
            ELSE NULL 
        END
    ) STORED,
    estado ENUM('presente', 'ausente', 'tardanza', 'justificado') DEFAULT 'presente',
    observaciones TEXT,
    registrado_por INT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (asignacion_id) REFERENCES turno_asignaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_asignacion_fecha (asignacion_id, fecha),
    INDEX idx_fecha_estado (fecha, estado)
);

-- ========== PASO 5: Insertar datos de prueba ==========
INSERT INTO turnos (nombre, tipo, hora_inicio, hora_fin, dias_semana, descripcion, estado, color_hex) VALUES
('Turno Mañana', 'academico', '08:00:00', '14:00:00', '["lunes", "martes", "miercoles", "jueves", "viernes"]', 'Turno académico matutino', 'activo', '#28a745'),
('Turno Tarde', 'academico', '14:00:00', '20:00:00', '["lunes", "martes", "miercoles", "jueves", "viernes"]', 'Turno académico vespertino', 'activo', '#fd7e14'),
('Guardia Nocturna', 'servicio', '22:00:00', '06:00:00', '["lunes", "martes", "miercoles", "jueves", "viernes"]', 'Turno de seguridad nocturno', 'activo', '#6c757d'),
('Turno Laboral', 'laboral', '09:00:00', '17:00:00', '["lunes", "martes", "miercoles", "jueves", "viernes"]', 'Horario laboral estándar', 'activo', '#007bff');

CREATE TABLE Capacitaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    archivo_nombre VARCHAR(255) NOT NULL,
    archivo_tipo ENUM('PDF', 'WORD') NOT NULL,
    archivo_contenido LONGBLOB NOT NULL,  -- Aquí se guarda el archivo
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado BOOLEAN DEFAULT TRUE
);




CREATE TABLE tickets_soporte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('password_recovery', 'technical_issue', 'general_support') DEFAULT 'password_recovery',
    email_solicitante VARCHAR(255) NOT NULL,
    asunto VARCHAR(255) NOT NULL DEFAULT 'Solicitud de recuperación de contraseña',
    motivo_solicitud TEXT NOT NULL,  -- Campo del formulario
    contraseña_temporal VARCHAR(255) NULL,  -- Campo del formulario
    estado ENUM('pendiente', 'en_proceso', 'resuelto', 'cerrado') DEFAULT 'pendiente',
    prioridad ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atendido_por INT NULL,
    respuesta_admin TEXT NULL,
    nueva_password_generada VARCHAR(255) NULL,  -- Para almacenar la nueva contraseña generada
    fecha_respuesta DATETIME NULL,
    
    FOREIGN KEY (atendido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_estado_fecha (estado, fecha_creacion),
    INDEX idx_email (email_solicitante),
    INDEX idx_tipo (tipo)
);
