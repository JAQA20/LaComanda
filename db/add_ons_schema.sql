-- Tabla de grupos de opciones (Ej. Tipo de Leche, Tamaño)
CREATE TABLE IF NOT EXISTS grupos_opciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    requerido TINYINT(1) NOT NULL DEFAULT 0,
    seleccion_multiple TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Opciones específicas para cada grupo (Ej. Almendra, Deslactosada)
CREATE TABLE IF NOT EXISTS opciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    precio_adicional DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grupo_id) REFERENCES grupos_opciones(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Asignación de grupos de opciones a categorías enteras (Ej. Tipo de Leche -> Cafés)
CREATE TABLE IF NOT EXISTS categoria_grupos (
    categoria_id INT NOT NULL,
    grupo_id INT NOT NULL,
    PRIMARY KEY (categoria_id, grupo_id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE,
    FOREIGN KEY (grupo_id) REFERENCES grupos_opciones(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Asignación de grupos de opciones a productos individuales (Excepciones o específicos)
CREATE TABLE IF NOT EXISTS producto_grupos (
    producto_id INT NOT NULL,
    grupo_id INT NOT NULL,
    PRIMARY KEY (producto_id, grupo_id),
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (grupo_id) REFERENCES grupos_opciones(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE detalle_orden ADD COLUMN opciones_json JSON NULL AFTER precio_unitario;

-- Agregar JSON de opciones seleccionadas al detalle de la orden
ALTER TABLE detalle_orden ADD COLUMN opciones_json JSON NULL AFTER precio_unitario;
