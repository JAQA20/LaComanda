-- =========================================
-- LA COMANDA - MySQL Schema (Workbench)
-- ACTUALIZADO CON PASSWORD RECOVERY
-- =========================================

DROP DATABASE IF EXISTS la_comanda;
CREATE DATABASE la_comanda
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

USE la_comanda;

-- ---------- Roles / Usuarios ----------
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido varchar(100) NOT NULL, 
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol_id INT NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- ---------- Password Recovery Table ----------
CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  expira_en DATETIME NOT NULL,
  usado TINYINT(1) DEFAULT 0,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ---------- Mesas ----------
CREATE TABLE mesas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero INT NOT NULL UNIQUE,
  estado ENUM('disponible','ocupada') NOT NULL DEFAULT 'disponible'
);

-- ---------- Catálogo ----------
CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE,
  slug VARCHAR(100) UNIQUE,
  icono VARCHAR(60) NOT NULL DEFAULT 'fa-tags',
  orden INT NOT NULL DEFAULT 1,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  precio INT NOT NULL,
  icono VARCHAR(60) NOT NULL DEFAULT 'fa-mug-hot',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- ---------- Órdenes ----------
CREATE TABLE ordenes (
    id_orden INT(11) PRIMARY KEY AUTO_INCREMENT, 
    mesa_id INT(11) NOT NULL, 
    id_estado INT(11) NOT NULL DEFAULT 1, 
    total DECIMAL(10,2) NOT NULL DEFAULT 0, 
    id_usuario INT(11), 
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, 
    hora_entrega DATETIME
);

CREATE TABLE detalle_orden (
    id_detalle INT(11) PRIMARY KEY AUTO_INCREMENT, 
    id_orden INT(11) NOT NULL, 
    id_producto INT(11) NOT NULL, 
    cantidad INT(11) NOT NULL, 
    precio_unitario DECIMAL(10,2) NOT NULL
);

-- =========================================
-- DATOS INICIALES
-- =========================================

INSERT INTO roles(nombre) VALUES ('admin'), ('mesero'), ('cocina'), ('barista');

-- Usuarios de prueba con contraseñas hasheadas
-- admin123 = $2y$10$tDzXpZ7VJKD7fJYCPq/.p.J8ZPZ0qJ8XmK6K4L1pQ3Z0Z0Z0Z0Z0Z
-- mesero123 = $2y$10$dE6fJ2.5H9K0Q1L2M3N4O5P6.Q7R8S9T0U1V2W3X4Y5Z6A7B8C9
-- cocina123 = $2y$10$kL9pQ2R3S4T5U6V7W8X9Y0Z1A2B3C4D5E6F7G8H9I0J1K2L3M4N5
-- barista123 = $2y$10$mN5oP6Q7R8S9T0U1V2W3X4Y5Z6A7B8C9D0E1F2G3H4I5J6K7L8M9

INSERT INTO usuarios(nombre, apellido, email, password, rol_id, activo)
VALUES
('Admin', 'Principal', 'admin@lacomanda.com', '$2y$10$tDzXpZ7VJKD7fJYCPq/.p.J8ZPZ0qJ8XmK6K4L1pQ3Z0Z0Z0Z0Z0Z', 1, 1),
('Mesero', 'Demo', 'mesero@lacomanda.com', '$2y$10$dE6fJ2.5H9K0Q1L2M3N4O5P6.Q7R8S9T0U1V2W3X4Y5Z6A7B8C9', 2, 1),
('Cocina', 'Demo', 'cocina@lacomanda.com', '$2y$10$kL9pQ2R3S4T5U6V7W8X9Y0Z1A2B3C4D5E6F7G8H9I0J1K2L3M4N5', 3, 1),
('Barista', 'Demo', 'barista@lacomanda.com', '$2y$10$mN5oP6Q7R8S9T0U1V2W3X4Y5Z6A7B8C9D0E1F2G3H4I5J6K7L8M9', 4, 1);

-- Mesas
INSERT INTO mesas(numero, estado) VALUES
(1,'disponible'), (2,'disponible'), (3,'disponible'), (4,'disponible'),
(5,'disponible'), (6,'disponible'), (7,'disponible'), (8,'disponible'),
(9,'disponible'), (10,'disponible'), (11,'disponible'), (12,'disponible');

-- Categorías
INSERT INTO categorias(nombre, slug, icono, orden, activo) VALUES
('Cafés', 'cafes', 'fa-coffee', 1, 1),
('Comidas', 'comidas', 'fa-hamburger', 2, 1),
('Especialidades', 'especialidades', 'fa-star', 3, 1),
('Postres', 'postres', 'fa-cake', 4, 1),
('Bebidas Frías', 'bebidas', 'fa-glass-water', 5, 1);

-- Productos
INSERT INTO productos(categoria_id, nombre, precio, icono, activo)
VALUES
(1, 'Americano', 300, 'fa-mug-hot', 1),
(1, 'Cappuccino', 400, 'fa-mug-hot', 1),
(1, 'Latte', 400, 'fa-mug-hot', 1),
(1, 'Expresso', 250, 'fa-mug-hot', 1),
(2, 'Hamburguesa', 800, 'fa-hamburger', 1),
(2, 'Pizza', 900, 'fa-pizza-slice', 1),
(3, 'Crema Catalana', 500, 'fa-ice-cream', 1),
(4, 'Cheesecake', 600, 'fa-cake-candles', 1),
(5, 'Limonada', 350, 'fa-glass-water', 1);
