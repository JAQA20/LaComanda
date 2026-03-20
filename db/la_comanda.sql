-- =========================================
-- LA COMANDA - MySQL Schema (Workbench)
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
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido varchar(100) NOT NULL, 
  email VARCHAR(50) NOT NULL UNIQUE,
  activo INT NOT NULL DEFAULT 1,
  password VARCHAR(255) NOT NULL,
  rol_id INT NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (rol_id) REFERENCES roles(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- -- ---------- Password Recovery Table ----------
CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  token VARCHAR(255) NOT NULL UNIQUE,
  expira_en DATETIME NOT NULL,
  usado TINYINT(1) DEFAULT 0,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------- Mesas ----------
CREATE TABLE mesas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero INT NOT NULL UNIQUE,
  estado ENUM('disponible','ocupada') NOT NULL DEFAULT 'disponible'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------- Layout compartido del croquis ----------
CREATE TABLE layout_configs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  layout_key VARCHAR(100) NOT NULL UNIQUE,
  payload_json LONGTEXT NULL,
  actualizado_por INT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------- Catálogo ----------
CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE,
  slug VARCHAR(100) UNIQUE,
  icono VARCHAR(60) NOT NULL DEFAULT 'fa-tags',
  orden INT NOT NULL DEFAULT 1,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  precio INT NOT NULL,
  icono VARCHAR(60) NOT NULL DEFAULT 'fa-mug-hot',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;



CREATE TABLE ordenes (
    id_orden INT(11) PRIMARY KEY AUTO_INCREMENT, 
  numero_json INT(11) NULL,
    mesa_id INT(11) NOT NULL, id_estado INT(11) NOT NULL DEFAULT 1, 
    total DECIMAL(10,2) NOT NULL DEFAULT 0, id_usuario INT(11), 
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, 
  hora_entrega DATETIME,
  notas TEXT NULL,
  items_text TEXT NULL,
  timestamp_unix BIGINT NULL,
  UNIQUE KEY uniq_numero_json (numero_json)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

    
CREATE TABLE detalle_orden (
    id_detalle INT(11) PRIMARY KEY AUTO_INCREMENT, 
    id_orden INT(11) NOT NULL, 
    id_producto INT(11) NOT NULL, 
    cantidad INT(11) NOT NULL, 
    precio_unitario DECIMAL(10,2) NOT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- ---------- Datos iniciales ----------
INSERT INTO roles(nombre) VALUES ('admin'), ('mesero'), ('cocina'), ('barista');

INSERT INTO usuarios(nombre, apellido, email, password, rol_id, activo)
VALUES
('Admin', 'admin', 'admin@proyecto.com', '$2y$10$GKsD.e.cZoSYxMDBbMBHFOgo.Y30c/q2EZ2dIsawRMwe/JVp5NrJy', 1, 1),
('Mesero', 'mesero', 'mesero@proyecto.com', '$2y$10$SoHgtdyHv03k8vJGiG8bAe/y58gdq0Jok0F4ODrULH4ZCRdv87UJe', 2, 1),
('Cocina', 'cocina', 'cocina@proyecto.com', '$2y$10$5mBtWhzM51hYAcjUFNPC6.am2VvqLeu4h78Q3iJJIY.NRyiVTmo1G', 3, 1),
('Barista', 'barista','barista@lacomanda.com','$2y$10$esYITJaGc.AvG18WlMi8ROwwiB/64QRH8ieUl9.wlgYNdnYX1OtLC',4, 1);

INSERT INTO mesas(numero, estado) VALUES 
(1,'disponible'), (2,'disponible'), (3,'disponible'), (4,'disponible'),
(5,'disponible'), (6,'disponible'), (7,'disponible'), (8,'disponible'),
(9,'disponible'), (10,'disponible'), (11,'disponible'), (12,'disponible');

INSERT INTO categorias(nombre, slug, icono, orden, activo) VALUES
('Mesas', 'mesas', 'fa-table', 1, 1),
('Cafés', 'cafes', 'fa-coffee', 2, 1),
('Comidas', 'comidas', 'fa-hamburger', 3, 1),
('Especialidades', 'especialidades', 'fa-star', 4, 1),
('Postres', 'postres', 'fa-cake', 5, 1),
('Bebidas Frías', 'bebidas', 'fa-glass-water', 6, 1);


INSERT INTO usuarios (nombre, apellido, email, password, rol_id, activo) VALUES
-- Meseros
('Carlos', 'Ramírez', 'carlos.ramirez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1) ,
('Ana', 'Gómez', 'ana.gomez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Luis', 'Fernández', 'luis.fernandez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('María', 'Vargas', 'maria.vargas@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('José', 'Soto', 'jose.soto@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),

-- Cocina
('Pedro', 'Mora', 'pedro.mora@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Laura', 'Jiménez', 'laura.jimenez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Andrés', 'Rojas', 'andres.rojas@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Paola', 'Castro', 'paola.castro@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),

-- Baristas
('Daniel', 'Alvarado', 'daniel.alvarado@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Sofía', 'Navarro', 'sofia.navarro@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4,1 ),
('Kevin', 'Chaves', 'kevin.chaves@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Valeria', 'Cordero', 'valeria.cordero@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),

-- Usuarios mixtos
('Ricardo', 'Pérez', 'ricardo.perez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Natalia', 'Salas', 'natalia.salas@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Esteban', 'León', 'esteban.leon@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Mónica', 'Araya', 'monica.araya@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Fernando', 'Solís', 'fernando.solis@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1);

INSERT INTO productos (categoria_id, nombre, precio, icono, activo)
SELECT c.id, v.nombre, v.precio, v.icono, 1
FROM categorias c
JOIN (
    SELECT 'cafes' AS slug, 'Espresso' AS nombre, 1800 AS precio, 'fa-mug-hot' AS icono
    UNION ALL SELECT 'cafes', 'Capuccino', 2400, 'fa-mug-hot'
    UNION ALL SELECT 'comidas', 'Sandwich de Pollo', 4200, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Panini Toscana', 4800, 'fa-bread-slice'
    UNION ALL SELECT 'especialidades', 'Frappé Caramelo', 3200, 'fa-blender'
    UNION ALL SELECT 'especialidades', 'Mocaccino Especial', 3500, 'fa-star'
    UNION ALL SELECT 'postres', 'Cheesecake', 2800, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Brownie con Helado', 3000, 'fa-ice-cream'
    UNION ALL SELECT 'bebidas', 'Iced Latte', 2900, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Té Frío Durazno', 2500, 'fa-glass-water'
) v ON v.slug = c.slug
LEFT JOIN productos p ON p.categoria_id = c.id AND p.nombre = v.nombre
WHERE p.id IS NULL;

INSERT INTO productos (categoria_id, nombre, precio, icono, activo)
SELECT c.id, v.nombre, v.precio, v.icono, 1
FROM categorias c
JOIN (
    -- CAFES
    SELECT 'cafes' AS slug, 'Espresso' AS nombre, 1800 AS precio, 'fa-mug-hot' AS icono
    UNION ALL SELECT 'cafes', 'Capuccino', 2400, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Latte', 2600, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Americano', 2000, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Mocha', 2800, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Macchiato', 2500, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Café Negro', 1700, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Café con Leche', 2200, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Café Vainilla', 2900, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Flat White', 2700, 'fa-mug-hot'

    -- COMIDAS
    UNION ALL SELECT 'comidas', 'Sandwich de Pollo', 4200, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Panini Toscana', 4800, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Croissant de Jamón y Queso', 3500, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Wrap de Pavo', 3900, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Bagel de Salmón', 5200, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Quiche Lorena', 4100, 'fa-cheese'
    UNION ALL SELECT 'comidas', 'Empanada de Carne', 2200, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Empanada de Queso', 2100, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Tostada de Aguacate', 4300, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Bowl de Yogurt y Frutas', 3600, 'fa-bowl-food'

    -- ESPECIALIDADES
    UNION ALL SELECT 'especialidades', 'Frappé Caramelo', 3200, 'fa-blender'
    UNION ALL SELECT 'especialidades', 'Mocaccino Especial', 3500, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Chocolate Caliente Premium', 3000, 'fa-mug-hot'
    UNION ALL SELECT 'especialidades', 'Latte Lavanda', 3400, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Café Bombón', 3100, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Frappé Mocha', 3300, 'fa-blender'
    UNION ALL SELECT 'especialidades', 'Matcha Latte', 3600, 'fa-leaf'
    UNION ALL SELECT 'especialidades', 'Golden Milk', 3400, 'fa-mug-hot'
    UNION ALL SELECT 'especialidades', 'Taro Latte', 3700, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Affogato', 3900, 'fa-ice-cream'

    -- POSTRES
    UNION ALL SELECT 'postres', 'Cheesecake', 2800, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Brownie con Helado', 3000, 'fa-ice-cream'
    UNION ALL SELECT 'postres', 'Tiramisú', 3200, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Croissant de Almendra', 2600, 'fa-bread-slice'
    UNION ALL SELECT 'postres', 'Galleta de Chispas', 1800, 'fa-cookie'
    UNION ALL SELECT 'postres', 'Queque de Zanahoria', 2700, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Muffin de Arándanos', 2300, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Pie de Limón', 2900, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Tres Leches', 3100, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Roll de Canela', 2500, 'fa-bread-slice'

    -- BEBIDAS
    UNION ALL SELECT 'bebidas', 'Iced Latte', 2900, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Té Frío Durazno', 2500, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Limonada Natural', 2200, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Limonada con Hierbabuena', 2400, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Smoothie de Fresa', 3300, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Smoothie de Mango', 3300, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Chocolate Frío', 2800, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Milkshake Vainilla', 3500, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Milkshake Chocolate', 3500, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Agua Mineral', 1500, 'fa-bottle-water'
) v ON v.slug = c.slug
LEFT JOIN productos p ON p.categoria_id = c.id AND p.nombre = v.nombre
WHERE p.id IS NULL;


INSERT INTO usuarios (nombre, apellido, email, password, rol_id, activo) VALUES
-- Meseros
('Carlos', 'Ramírez', 'carlos.ramirez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1) ,
('Ana', 'Gómez', 'ana.gomez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Luis', 'Fernández', 'luis.fernandez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('María', 'Vargas', 'maria.vargas@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('José', 'Soto', 'jose.soto@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),

-- Cocina
('Pedro', 'Mora', 'pedro.mora@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Laura', 'Jiménez', 'laura.jimenez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Andrés', 'Rojas', 'andres.rojas@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Paola', 'Castro', 'paola.castro@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),

-- Baristas
('Daniel', 'Alvarado', 'daniel.alvarado@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Sofía', 'Navarro', 'sofia.navarro@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4,1 ),
('Kevin', 'Chaves', 'kevin.chaves@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Valeria', 'Cordero', 'valeria.cordero@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),

-- Usuarios mixtos
('Ricardo', 'Pérez', 'ricardo.perez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Natalia', 'Salas', 'natalia.salas@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Esteban', 'León', 'esteban.leon@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Mónica', 'Araya', 'monica.araya@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Fernando', 'Solís', 'fernando.solis@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1);

INSERT INTO productos (categoria_id, nombre, precio, icono, activo)
SELECT c.id, v.nombre, v.precio, v.icono, 1
FROM categorias c
JOIN (
    SELECT 'cafes' AS slug, 'Espresso' AS nombre, 1800 AS precio, 'fa-mug-hot' AS icono
    UNION ALL SELECT 'cafes', 'Capuccino', 2400, 'fa-mug-hot'
    UNION ALL SELECT 'comidas', 'Sandwich de Pollo', 4200, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Panini Toscana', 4800, 'fa-bread-slice'
    UNION ALL SELECT 'especialidades', 'Frappé Caramelo', 3200, 'fa-blender'
    UNION ALL SELECT 'especialidades', 'Mocaccino Especial', 3500, 'fa-star'
    UNION ALL SELECT 'postres', 'Cheesecake', 2800, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Brownie con Helado', 3000, 'fa-ice-cream'
    UNION ALL SELECT 'bebidas', 'Iced Latte', 2900, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Té Frío Durazno', 2500, 'fa-glass-water'
) v ON v.slug = c.slug
LEFT JOIN productos p ON p.categoria_id = c.id AND p.nombre = v.nombre
WHERE p.id IS NULL;

INSERT INTO productos (categoria_id, nombre, precio, icono, activo)
SELECT c.id, v.nombre, v.precio, v.icono, 1
FROM categorias c
JOIN (
    -- CAFES
    SELECT 'cafes' AS slug, 'Espresso' AS nombre, 1800 AS precio, 'fa-mug-hot' AS icono
    UNION ALL SELECT 'cafes', 'Capuccino', 2400, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Latte', 2600, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Americano', 2000, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Mocha', 2800, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Macchiato', 2500, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Café Negro', 1700, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Café con Leche', 2200, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Café Vainilla', 2900, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Flat White', 2700, 'fa-mug-hot'

    -- COMIDAS
    UNION ALL SELECT 'comidas', 'Sandwich de Pollo', 4200, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Panini Toscana', 4800, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Croissant de Jamón y Queso', 3500, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Wrap de Pavo', 3900, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Bagel de Salmón', 5200, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Quiche Lorena', 4100, 'fa-cheese'
    UNION ALL SELECT 'comidas', 'Empanada de Carne', 2200, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Empanada de Queso', 2100, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Tostada de Aguacate', 4300, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Bowl de Yogurt y Frutas', 3600, 'fa-bowl-food'

    -- ESPECIALIDADES
    UNION ALL SELECT 'especialidades', 'Frappé Caramelo', 3200, 'fa-blender'
    UNION ALL SELECT 'especialidades', 'Mocaccino Especial', 3500, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Chocolate Caliente Premium', 3000, 'fa-mug-hot'
    UNION ALL SELECT 'especialidades', 'Latte Lavanda', 3400, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Café Bombón', 3100, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Frappé Mocha', 3300, 'fa-blender'
    UNION ALL SELECT 'especialidades', 'Matcha Latte', 3600, 'fa-leaf'
    UNION ALL SELECT 'especialidades', 'Golden Milk', 3400, 'fa-mug-hot'
    UNION ALL SELECT 'especialidades', 'Taro Latte', 3700, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Affogato', 3900, 'fa-ice-cream'

    -- POSTRES
    UNION ALL SELECT 'postres', 'Cheesecake', 2800, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Brownie con Helado', 3000, 'fa-ice-cream'
    UNION ALL SELECT 'postres', 'Tiramisú', 3200, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Croissant de Almendra', 2600, 'fa-bread-slice'
    UNION ALL SELECT 'postres', 'Galleta de Chispas', 1800, 'fa-cookie'
    UNION ALL SELECT 'postres', 'Queque de Zanahoria', 2700, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Muffin de Arándanos', 2300, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Pie de Limón', 2900, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Tres Leches', 3100, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Roll de Canela', 2500, 'fa-bread-slice'

    -- BEBIDAS
    UNION ALL SELECT 'bebidas', 'Iced Latte', 2900, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Té Frío Durazno', 2500, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Limonada Natural', 2200, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Limonada con Hierbabuena', 2400, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Smoothie de Fresa', 3300, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Smoothie de Mango', 3300, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Chocolate Frío', 2800, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Milkshake Vainilla', 3500, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Milkshake Chocolate', 3500, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Agua Mineral', 1500, 'fa-bottle-water'
) v ON v.slug = c.slug
LEFT JOIN productos p ON p.categoria_id = c.id AND p.nombre = v.nombre
WHERE p.id IS NULL;




