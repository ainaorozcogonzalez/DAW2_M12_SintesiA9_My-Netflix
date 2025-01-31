 -- Creación de la base de datos
CREATE DATABASE MyNetflix;

-- Uso de la base de datos
USE MyNetflix;

-- Tabla de roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL
);

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    rol_id INT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE SET NULL
);

-- Tabla de contenidos (películas y series)
CREATE TABLE contenidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha_lanzamiento DATE,
    likes INT DEFAULT 0,
    tipo ENUM('pelicula', 'serie') NOT NULL, -- Campo para distinguir entre películas y series
    imagen VARCHAR(255) NOT NULL, -- Nuevo campo para la URL de la imagen
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de likes
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    contenido_id INT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (contenido_id) REFERENCES contenidos(id) ON DELETE CASCADE,
    UNIQUE (usuario_id, contenido_id) -- Un usuario solo puede dar like a un contenido una vez
);

-- Tabla de validaciones de usuarios
CREATE TABLE validaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    fecha_validacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

INSERT INTO contenidos (titulo, descripcion, fecha_lanzamiento, likes, tipo, imagen) VALUES
('Película 1', 'Descripción de la película 1', '2023-01-01', 100, 'pelicula', 'url_imagen_pelicula1.jpg'),
('Serie 1', 'Descripción de la serie 1', '2023-01-01', 200, 'serie', 'url_imagen_serie1.jpg'),
('Película 2', 'Descripción de la película 2', '2023-01-01', 150, 'pelicula', 'url_imagen_pelicula2.jpg'),
('Serie 2', 'Descripción de la serie 2', '2023-01-01', 250, 'serie', 'url_imagen_serie2.jpg'),
('Película 3', 'Descripción de la película 3', '2023-01-01', 300, 'pelicula', 'url_imagen_pelicula3.jpg');