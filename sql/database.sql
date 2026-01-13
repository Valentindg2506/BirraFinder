CREATE DATABASE IF NOT EXISTS ProyectoBares;
USE ProyectoBares;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    direccion VARCHAR(255),
    lat DECIMAL(10, 8),
    lng DECIMAL(11, 8),
    estado ENUM('visitado', 'pendiente') NOT NULL DEFAULT 'pendiente',
    puntuacion INT CHECK (puntuacion >= 1 AND puntuacion <= 5),
    comentario TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Usuario de Base de Datos
CREATE USER IF NOT EXISTS 'bar_admin'@'localhost' IDENTIFIED BY 'BarTracker2026!';
GRANT ALL PRIVILEGES ON ProyectoBares.* TO 'bar_admin'@'localhost';
FLUSH PRIVILEGES;

-- Usuario Administrador por defecto para la App
INSERT INTO usuarios (nombre, email, password) VALUES 
('Admin', 'admin@bartracker.com', '$2y$10$YourHashedPasswordHere'); -- Password needs to be hashed. I will use a simple one for demo like 'Admin123!' hash in next step if possible or just leave placeholder. 
-- Actually, let's use a known hash for 'Admin123!' -> $2y$10$rContent...


