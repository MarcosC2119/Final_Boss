-- Script para insertar usuarios de prueba en la base de datos roomit
USE roomit;

-- Insertar usuario administrador
INSERT INTO usuarios (nombre, email, password, rol, telefono, estado) VALUES 
('Administrador Sistema', 'admin@roomit.com', '$2y$10$2ynGa2nca2I8mkKq5GsS6ucd7N7nLgyFT6O2e23dgCpPKVaHJMffq', 'administrativo', '555-0001', 'activo');

-- Insertar usuario docente
INSERT INTO usuarios (nombre, email, password, rol, telefono, estado) VALUES 
('Profesor Docente', 'docente@roomit.com', '$2y$10$LNeSMPMKeQCvZkHWyCtZXuh8ke1.GfH8U7ODAsABs9Z2R0/9vWOVS', 'docente', '555-0002', 'activo');

-- Verificar que los usuarios se insertaron correctamente
SELECT id, nombre, email, rol, estado FROM usuarios; 