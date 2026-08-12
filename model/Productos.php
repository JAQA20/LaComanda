<?php
require_once __DIR__ . "/Conexion.php";
require_once __DIR__ . "/../config/text.php";

class Productos
{
    // Para el CRUD admin (tabla/lista)
    public static function listar()
    {
        $conexion = Conexion::conectar();

        $sql = "
            SELECT p.id, p.nombre, p.precio, p.imagen, p.activo,
                   p.categoria_id, c.nombre AS categoria_nombre, c.slug AS categoria_slug
            FROM productos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            ORDER BY p.id DESC
        ";

        $result = $conexion->query($sql);
        if (!$result) throw new Exception("Error al listar productos: " . $conexion->error);

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            if (isset($row['nombre'])) {
                $row['nombre'] = app_normalize_text($row['nombre']);
            }
            if (isset($row['categoria_nombre'])) {
                $row['categoria_nombre'] = app_normalize_text($row['categoria_nombre']);
            }
            $productos[] = $row;
        }
        return $productos;
    }

    // Para llenar el <select> de categorías en crear/editar
    public static function listarCategoriasActivas()
    {
        $conexion = Conexion::conectar();
        $sql = "SELECT id, nombre, slug FROM categorias WHERE activo = 1 ORDER BY orden ASC, nombre ASC";
        $result = $conexion->query($sql);
        if (!$result) throw new Exception("Error al listar categorías: " . $conexion->error);

        $cats = [];
        while ($row = $result->fetch_assoc()) {
            if (isset($row['nombre'])) {
                $row['nombre'] = app_normalize_text($row['nombre']);
            }
            $cats[] = $row;
        }
        return $cats;
    }

    // Para el INDEX (productos por slug)
    public static function listarPorCategoriaSlug($slug)
    {
        $conexion = Conexion::conectar();
        $slug = trim($slug);

        if ($slug === "" || $slug === "mesas") return [];

        $stmt = $conexion->prepare("
            SELECT p.id, p.nombre, p.precio, p.imagen
            FROM productos p
            INNER JOIN categorias c ON c.id = p.categoria_id
            WHERE c.slug = ?
              AND c.activo = 1
              AND p.activo = 1
            ORDER BY p.nombre ASC
        ");
        if (!$stmt) throw new Exception("Error prepare listarPorCategoriaSlug: " . $conexion->error);

        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $res = $stmt->get_result();

        $items = [];
        while ($row = $res->fetch_assoc()) {
            if (isset($row['nombre'])) {
                $row['nombre'] = app_normalize_text($row['nombre']);
            }
            $items[] = $row;
        }
        return $items;
    }

    public static function obtenerPorId($id)
    {
        $conexion = Conexion::conectar();
        $id = (int)$id;

        $stmt = $conexion->prepare("
            SELECT id, categoria_id, nombre, precio, imagen, activo
            FROM productos
            WHERE id = ?
            LIMIT 1
        ");
        if (!$stmt) throw new Exception("Error prepare obtenerPorId: " . $conexion->error);

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();

        return $res->fetch_assoc() ?: null;
    }

    public static function nombreExisteEnCategoria($nombre, $categoria_id, $exceptoId = null)
    {
        $conexion = Conexion::conectar();
        $nombre = trim($nombre);
        $categoria_id = (int)$categoria_id;

        if ($exceptoId !== null) {
            $exceptoId = (int)$exceptoId;
            $stmt = $conexion->prepare("
                SELECT id
                FROM productos
                WHERE nombre = ?
                  AND categoria_id = ?
                  AND id <> ?
                LIMIT 1
            ");
            if (!$stmt) throw new Exception("Error prepare nombreExisteEnCategoria: " . $conexion->error);
            $stmt->bind_param("sii", $nombre, $categoria_id, $exceptoId);
        } else {
            $stmt = $conexion->prepare("
                SELECT id
                FROM productos
                WHERE nombre = ?
                  AND categoria_id = ?
                LIMIT 1
            ");
            if (!$stmt) throw new Exception("Error prepare nombreExisteEnCategoria: " . $conexion->error);
            $stmt->bind_param("si", $nombre, $categoria_id);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        return $res->num_rows > 0;
    }

    public static function crear($categoria_id, $nombre, $precio, $imagen, $activo)
    {
        $conexion = Conexion::conectar();

        $categoria_id = (int)$categoria_id;
        $nombre = trim((string)$nombre);
        $precio = (int)$precio;
        $imagen = trim((string)$imagen);
        $activo = (int)$activo;

        if ($categoria_id <= 0 || $nombre === "" || $precio <= 0) {
            throw new Exception("Debe completar todos los campos obligatorios.");
        }

        if ($imagen === "") $imagen = null;

        if (self::nombreExisteEnCategoria($nombre, $categoria_id)) {
            throw new Exception("Ya existe un producto con ese nombre en esta categoría.");
        }

        $stmt = $conexion->prepare("
            INSERT INTO productos (categoria_id, nombre, precio, imagen, activo)
            VALUES (?, ?, ?, ?, ?)
        ");
        if (!$stmt) throw new Exception("Error prepare crear: " . $conexion->error);

        $stmt->bind_param("isisi", $categoria_id, $nombre, $precio, $imagen, $activo);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo crear el producto: " . $stmt->error);
        }

        return $stmt->insert_id;
    }

    public static function actualizar($id, $categoria_id, $nombre, $precio, $imagen, $activo)
    {
        $conexion = Conexion::conectar();

        $id = (int)$id;
        $categoria_id = (int)$categoria_id;
        $nombre = trim((string)$nombre);
        $precio = (int)$precio;
        $imagen = trim((string)$imagen);
        $activo = (int)$activo;

        if ($id <= 0) throw new Exception("ID inválido.");
        if ($categoria_id <= 0 || $nombre === "" || $precio <= 0) {
            throw new Exception("Debe completar todos los campos obligatorios.");
        }
        if ($imagen === "") $imagen = null;

        if (self::nombreExisteEnCategoria($nombre, $categoria_id, $id)) {
            throw new Exception("Ya existe otro producto con ese nombre en esta categoría.");
        }

        $stmt = $conexion->prepare("
            UPDATE productos
            SET categoria_id = ?, nombre = ?, precio = ?, imagen = ?, activo = ?
            WHERE id = ?
            LIMIT 1
        ");
        if (!$stmt) throw new Exception("Error prepare actualizar: " . $conexion->error);

        $stmt->bind_param("isisii", $categoria_id, $nombre, $precio, $imagen, $activo, $id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo actualizar el producto: " . $stmt->error);
        }

        return true;
    }

    public static function eliminar($id)
    {
        $conexion = Conexion::conectar();
        $id = (int)$id;

        if ($id <= 0) throw new Exception("ID inválido.");

        $stmt = $conexion->prepare("DELETE FROM productos WHERE id = ? LIMIT 1");
        if (!$stmt) throw new Exception("Error prepare delete: " . $conexion->error);

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo eliminar el producto: " . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("No se encontró el producto para eliminar.");
        }

        return true;
    }
}
