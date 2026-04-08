-- =========================================
-- LA COMANDA - MySQL Schema limpio para migración 100% DB
-- =========================================
-- ========JARVIS UPDATE========
-- Archivo limpiado para servir como nueva base oficial de MySQL.
-- Eliminé rastros del flujo híbrido con JSON y preparé el esquema
-- para trabajar órdenes 100% desde base de datos.
--
-- ========JARVIS UPDATE========
-- Se fuerza utf8mb4 desde el inicio del script porque en Docker/MySQL
-- la importación automática del seed estaba entrando con latin1 y eso
-- corrompía tildes/acentos (ej: CafÃ©) cada vez que se recreaba la base.
-- Esto ataca el problema en la importación, no en la vista.
-- =========================================

/*!40101 SET NAMES utf8mb4 */;
SET CHARACTER SET utf8mb4;

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
  apellido VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  password VARCHAR(255) NOT NULL,
  rol_id INT NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (rol_id) REFERENCES roles(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

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
-- ========JARVIS UPDATE========
-- Se mantiene el catálogo actual, pero ahora queda como base del ruteo
-- cocina/barista por categoría. La regla acordada es:
--   - barista: 'cafes' y 'bebidas'
--   - cocina: todo lo demás excepto 'mesas'
CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE,
  slug VARCHAR(100) NOT NULL UNIQUE,
  icono VARCHAR(60) NOT NULL DEFAULT 'fa-tags',
  orden INT NOT NULL DEFAULT 1,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  icono VARCHAR(60) NOT NULL DEFAULT 'fa-mug-hot',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_producto_categoria_nombre (categoria_id, nombre),
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------- Órdenes (cabecera) ----------
-- ========JARVIS UPDATE========
-- Cambios principales sobre la tabla ordenes:
-- 1) Eliminé columnas heredadas del flujo con JSON.
-- 2) numero_json fue reemplazado por numero_orden.
-- 3) id_estado deja de ser la fuente principal del flujo.
-- 4) La orden ahora funciona como cabecera; el estado real vive en detalle_orden.
-- 5) Se agregaron timestamps más claros para lista/entrega a nivel de orden.
CREATE TABLE ordenes (
  id_orden INT AUTO_INCREMENT PRIMARY KEY,
  numero_orden INT NOT NULL UNIQUE,
  mesa_id INT NOT NULL,
  id_usuario INT NULL,
  notas TEXT NULL,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_lista DATETIME NULL,
  fecha_entrega DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (mesa_id) REFERENCES mesas(id),
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_ordenes_mesa (mesa_id),
  INDEX idx_ordenes_usuario (id_usuario),
  INDEX idx_ordenes_fecha_creacion (fecha_creacion)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------- Detalle de órdenes (estado por ítem) ----------
-- ========JARVIS UPDATE========
-- Esta tabla se convirtió en el centro del flujo operativo.
-- Cambios principales:
-- 1) Se agregó estado_item para manejar el avance por producto.
-- 2) Se agregaron fechas por ítem (inicio, listo, entrega).
-- 3) Se agregaron índices para consultas de cocina/barista/historial.
-- 4) Aquí vive ahora la lógica real del proceso.
-- Barista:
--   categorias.slug IN ('cafes', 'bebidas')
-- Cocina:
--   categorias.slug NOT IN ('cafes', 'bebidas', 'mesas')
CREATE TABLE detalle_orden (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_orden INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  estado_item ENUM('pendiente', 'en_preparacion', 'listo', 'entregado') NOT NULL DEFAULT 'pendiente',
  fecha_inicio_preparacion DATETIME NULL,
  fecha_lista DATETIME NULL,
  fecha_entrega DATETIME NULL,
  observaciones TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES productos(id),
  INDEX idx_detalle_orden (id_orden),
  INDEX idx_detalle_producto (id_producto),
  INDEX idx_detalle_estado_item (estado_item)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------- Vista auxiliar para ruteo de preparación ----------
-- ========JARVIS UPDATE========
-- Agregué esta vista para simplificar consultas futuras.
-- Sirve para que cocina y barista no tengan que repetir joins grandes.
-- También deja calculada el área de preparación según categoría.
CREATE VIEW vw_detalle_preparacion AS
SELECT
  d.id_detalle,
  d.id_orden,
  o.numero_orden,
  o.mesa_id,
  o.id_usuario,
  o.notas AS notas_orden,
  o.fecha_creacion,
  d.id_producto,
  p.nombre AS producto_nombre,
  c.id AS categoria_id,
  c.nombre AS categoria_nombre,
  c.slug AS categoria_slug,
  d.cantidad,
  d.precio_unitario,
  d.estado_item,
  d.fecha_inicio_preparacion,
  d.fecha_lista,
  d.fecha_entrega,
  CASE
    WHEN c.slug IN ('cafes', 'bebidas') THEN 'barista'
    WHEN c.slug = 'mesas' THEN 'ignorar'
    ELSE 'cocina'
  END AS area_preparacion
FROM detalle_orden d
INNER JOIN ordenes o ON o.id_orden = d.id_orden
INNER JOIN productos p ON p.id = d.id_producto
INNER JOIN categorias c ON c.id = p.categoria_id;

-- ---------- Datos iniciales ----------
-- ========JARVIS UPDATE========
-- Limpié los seeds duplicados que venían en el archivo original.
-- Dejé una sola carga coherente de usuarios, categorías y productos.
INSERT INTO roles(nombre) VALUES
('admin'), ('mesero'), ('cocina'), ('barista');

-- -----------------------------------Usuarios de sistema-----------------------------------
INSERT INTO usuarios(nombre, apellido, email, password, rol_id, activo) VALUES
('Admin', 'admin', 'admin@proyecto.com', '$2y$10$GKsD.e.cZoSYxMDBbMBHFOgo.Y30c/q2EZ2dIsawRMwe/JVp5NrJy', 1, 1),
('Mesero', 'mesero', 'mesero@proyecto.com', '$2y$10$SoHgtdyHv03k8vJGiG8bAe/y58gdq0Jok0F4ODrULH4ZCRdv87UJe', 2, 1),
('Cocina', 'cocina', 'cocina@proyecto.com', '$2y$10$5mBtWhzM51hYAcjUFNPC6.am2VvqLeu4h78Q3iJJIY.NRyiVTmo1G', 3, 1),
('Barista', 'barista', 'barista@lacomanda.com', '$2y$10$esYITJaGc.AvG18WlMi8ROwwiB/64QRH8ieUl9.wlgYNdnYX1OtLC', 4, 1),
('Carlos', 'Ramírez', 'carlos.ramirez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Ana', 'Gómez', 'ana.gomez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Luis', 'Fernández', 'luis.fernandez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('María', 'Vargas', 'maria.vargas@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('José', 'Soto', 'jose.soto@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Pedro', 'Mora', 'pedro.mora@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Laura', 'Jiménez', 'laura.jimenez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Andrés', 'Rojas', 'andres.rojas@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Paola', 'Castro', 'paola.castro@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Daniel', 'Alvarado', 'daniel.alvarado@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Sofía', 'Navarro', 'sofia.navarro@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Kevin', 'Chaves', 'kevin.chaves@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Valeria', 'Cordero', 'valeria.cordero@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Ricardo', 'Pérez', 'ricardo.perez@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Natalia', 'Salas', 'natalia.salas@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1),
('Esteban', 'León', 'esteban.leon@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 4, 1),
('Mónica', 'Araya', 'monica.araya@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 2, 1),
('Fernando', 'Solís', 'fernando.solis@lacomanda.com', '$2y$10$8s2g8uK6qH6y0vK6zJ6r0eQ0X9Ckz3VZKk1E3M0YQ1E9J6mZb0F0a', 3, 1);

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

INSERT INTO productos (categoria_id, nombre, precio, icono, activo)
SELECT c.id, v.nombre, v.precio, v.icono, 1
FROM categorias c
JOIN (
    -- CAFES / BARISTA
    SELECT 'cafes' AS slug, 'Espresso' AS nombre, 1800.00 AS precio, 'fa-mug-hot' AS icono
    UNION ALL SELECT 'cafes', 'Capuccino', 2400.00, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Latte', 2600.00, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Americano', 2000.00, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Mocha', 2800.00, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Macchiato', 2500.00, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Café Negro', 1700.00, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Café con Leche', 2200.00, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Café Vainilla', 2900.00, 'fa-mug-hot'
    UNION ALL SELECT 'cafes', 'Flat White', 2700.00, 'fa-mug-hot'

    -- COMIDAS / COCINA
    UNION ALL SELECT 'comidas', 'Sandwich de Pollo', 4200.00, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Panini Toscana', 4800.00, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Croissant de Jamón y Queso', 3500.00, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Wrap de Pavo', 3900.00, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Bagel de Salmón', 5200.00, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Quiche Lorena', 4100.00, 'fa-cheese'
    UNION ALL SELECT 'comidas', 'Empanada de Carne', 2200.00, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Empanada de Queso', 2100.00, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Tostada de Aguacate', 4300.00, 'fa-bread-slice'
    UNION ALL SELECT 'comidas', 'Bowl de Yogurt y Frutas', 3600.00, 'fa-bowl-food'

    -- ========JARVIS UPDATE========
    -- Especialidades se deja yendo a cocina porque la regla acordada fue:
    -- solo 'cafes' y 'bebidas' van a barista.
    -- Si después quieren refinar esto, aquí es uno de los puntos a revisar.
    -- ESPECIALIDADES / COCINA SEGÚN REGLA ACTUAL
    UNION ALL SELECT 'especialidades', 'Frappé Caramelo', 3200.00, 'fa-blender'
    UNION ALL SELECT 'especialidades', 'Mocaccino Especial', 3500.00, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Chocolate Caliente Premium', 3000.00, 'fa-mug-hot'
    UNION ALL SELECT 'especialidades', 'Latte Lavanda', 3400.00, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Café Bombón', 3100.00, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Frappé Mocha', 3300.00, 'fa-blender'
    UNION ALL SELECT 'especialidades', 'Matcha Latte', 3600.00, 'fa-leaf'
    UNION ALL SELECT 'especialidades', 'Golden Milk', 3400.00, 'fa-mug-hot'
    UNION ALL SELECT 'especialidades', 'Taro Latte', 3700.00, 'fa-star'
    UNION ALL SELECT 'especialidades', 'Affogato', 3900.00, 'fa-ice-cream'

    -- POSTRES / COCINA
    UNION ALL SELECT 'postres', 'Cheesecake', 2800.00, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Brownie con Helado', 3000.00, 'fa-ice-cream'
    UNION ALL SELECT 'postres', 'Tiramisú', 3200.00, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Croissant de Almendra', 2600.00, 'fa-bread-slice'
    UNION ALL SELECT 'postres', 'Galleta de Chispas', 1800.00, 'fa-cookie'
    UNION ALL SELECT 'postres', 'Queque de Zanahoria', 2700.00, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Muffin de Arándanos', 2300.00, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Pie de Limón', 2900.00, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Tres Leches', 3100.00, 'fa-cake-candles'
    UNION ALL SELECT 'postres', 'Roll de Canela', 2500.00, 'fa-bread-slice'

    -- BEBIDAS / BARISTA
    UNION ALL SELECT 'bebidas', 'Iced Latte', 2900.00, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Té Frío Durazno', 2500.00, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Limonada Natural', 2200.00, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Limonada con Hierbabuena', 2400.00, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Smoothie de Fresa', 3300.00, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Smoothie de Mango', 3300.00, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Chocolate Frío', 2800.00, 'fa-glass-water'
    UNION ALL SELECT 'bebidas', 'Milkshake Vainilla', 3500.00, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Milkshake Chocolate', 3500.00, 'fa-blender'
    UNION ALL SELECT 'bebidas', 'Agua Mineral', 1500.00, 'fa-bottle-water'
) v ON v.slug = c.slug;
