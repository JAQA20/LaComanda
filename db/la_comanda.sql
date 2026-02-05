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
);

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido varchar(100) NOT NULL, 
  email VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol_id INT NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (rol_id) REFERENCES roles(id)
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
  nombre VARCHAR(80) NOT NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- ---------- Órdenes ----------
-- CREATE TABLE ordenes (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   mesa_id INT NOT NULL,
--   usuario_id INT NULL,
--   estado ENUM('pendiente','en_preparacion','entregada','cancelada') NOT NULL DEFAULT 'pendiente',
--   creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--   FOREIGN KEY (mesa_id) REFERENCES mesas(id),
--   FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
-- );

-- CREATE TABLE orden_items (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   orden_id INT NOT NULL,
--   producto_id INT NOT NULL,
--   cantidad INT NOT NULL,
--   precio_unitario DECIMAL(10,2) NOT NULL,
--   notas VARCHAR(255) NULL,
--   FOREIGN KEY (orden_id) REFERENCES ordenes(id) ON DELETE CASCADE,
--   FOREIGN KEY (producto_id) REFERENCES productos(id)
-- );

CREATE TABLE ordenes (
    id_orden INT(11) PRIMARY KEY AUTO_INCREMENT, 
    mesa_id INT(11) NOT NULL, id_estado INT(11) NOT NULL DEFAULT 1, 
    total DECIMAL(10,2) NOT NULL DEFAULT 0, id_usuario INT(11), 
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, 
    hora_entrega DATETIME);

    
CREATE TABLE detalle_orden (
    id_detalle INT(11) PRIMARY KEY AUTO_INCREMENT, 
    id_orden INT(11) NOT NULL, 
    id_producto INT(11) NOT NULL, 
    cantidad INT(11) NOT NULL, 
    precio_unitario DECIMAL(10,2) NOT NULL);




-- ---------- Datos iniciales ----------
INSERT INTO roles(nombre) VALUES ('admin'), ('mesero'), ('cocina');

INSERT INTO usuarios(nombre, apellido, email, password, rol_id)
VALUES
('Admin', 'admin', 'admin@proyecto.com', 'Admin123!', 1),
('Mesero', 'mesero', 'mesero@proyecto.com', 'Mesero123!', 2),
('Cocina', 'cocina', 'cocina@proyecto.com', 'Cocina123!', 3);

INSERT INTO mesas(numero, estado) VALUES
(1,'disponible'), (2,'disponible'), (3,'disponible'), (4,'disponible'),
(5,'disponible'), (6,'disponible'), (7,'disponible'), (8,'disponible'),
(9,'disponible'), (10,'disponible'), (11,'disponible'), (12,'disponible');


INSERT INTO categorias(nombre) VALUES
('Cafés'), ('Comidas'), ('Especialidades'), ('Postres'), ('Bebidas frías');

INSERT INTO productos(categoria_id, nombre, precio) VALUES
(1,'Espresso', 1200.00),
(1,'Cappuccino', 1800.00),
(4,'Cheesecake', 2200.00),
(5,'Iced Latte', 2000.00);

select * from roles; 
select * from usuarios; 



