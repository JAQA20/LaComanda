<?php
require_once __DIR__ . "/Conexion.php";
require_once __DIR__ . "/../config/text.php";

class Categorias
{
    private static function slugify($text): string
    {
        $text = app_normalize_text((string)$text);
        $text = mb_strtolower(trim($text), 'UTF-8');

        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if (is_string($ascii) && $ascii !== '') {
                $text = $ascii;
            }
        }

        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = trim((string)$text, '-');

        return $text !== '' ? $text : 'categoria';
    }

    public static function listarActivas()
    {
        $cn = Conexion::conectar();
        $sql = "SELECT id, nombre, slug, icono, orden, activo
            FROM categorias
            WHERE activo = 1
            ORDER BY orden ASC, nombre ASC";
        $res = $cn->query($sql);
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $row['nombre'] = app_normalize_text($row['nombre']);
            $data[] = $row;
        }
        return $data;
    }

    public static function listarTodas()
    {
        $cn = Conexion::conectar();
        $sql = "
            SELECT c.id, c.nombre, c.slug, c.icono, c.orden, c.activo,
                   COUNT(p.id) AS total_productos
            FROM categorias c
            LEFT JOIN productos p ON p.categoria_id = c.id
            GROUP BY c.id, c.nombre, c.slug, c.icono, c.orden, c.activo
            ORDER BY c.orden ASC, c.nombre ASC
        ";
        $res = $cn->query($sql);
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $row['nombre'] = app_normalize_text($row['nombre']);
            $data[] = $row;
        }
        return $data;
    }

    public static function obtenerPorId(int $id)
    {
        $cn = Conexion::conectar();
        $stmt = $cn->prepare("SELECT id, nombre, slug, icono, orden, activo FROM categorias WHERE id = ? LIMIT 1");
        if (!$stmt) throw new Exception("Error prepare obtenerPorId: " . $cn->error);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc() ?: null;
        if ($row) {
            $row['nombre'] = app_normalize_text($row['nombre']);
        }
        return $row;
    }

    public static function obtenerPorSlug($slug)
    {
        $cn = Conexion::conectar();
        $sql = "SELECT id, nombre, slug, icono, orden, activo
            FROM categorias
            WHERE slug = ?
            LIMIT 1";
        $stmt = $cn->prepare($sql);
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc() ?: null;
        if ($row) {
            $row['nombre'] = app_normalize_text($row['nombre']);
        }
        return $row;
    }

    private static function nombreExiste(string $nombre, ?int $exceptoId = null): bool
    {
        $cn = Conexion::conectar();
        if ($exceptoId !== null) {
            $stmt = $cn->prepare("SELECT id FROM categorias WHERE nombre = ? AND id <> ? LIMIT 1");
            if (!$stmt) throw new Exception("Error prepare nombreExiste: " . $cn->error);
            $stmt->bind_param("si", $nombre, $exceptoId);
        } else {
            $stmt = $cn->prepare("SELECT id FROM categorias WHERE nombre = ? LIMIT 1");
            if (!$stmt) throw new Exception("Error prepare nombreExiste: " . $cn->error);
            $stmt->bind_param("s", $nombre);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->num_rows > 0;
    }

    private static function slugExiste(string $slug, ?int $exceptoId = null): bool
    {
        $cn = Conexion::conectar();
        if ($exceptoId !== null) {
            $stmt = $cn->prepare("SELECT id FROM categorias WHERE slug = ? AND id <> ? LIMIT 1");
            if (!$stmt) throw new Exception("Error prepare slugExiste: " . $cn->error);
            $stmt->bind_param("si", $slug, $exceptoId);
        } else {
            $stmt = $cn->prepare("SELECT id FROM categorias WHERE slug = ? LIMIT 1");
            if (!$stmt) throw new Exception("Error prepare slugExiste: " . $cn->error);
            $stmt->bind_param("s", $slug);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->num_rows > 0;
    }

    public static function crear(string $nombre, string $slug, string $icono, int $orden, int $activo)
    {
        $cn = Conexion::conectar();
        $nombre = app_normalize_text(trim($nombre));
        $slug = trim($slug);
        $icono = trim($icono);
        $orden = $orden > 0 ? $orden : 1;
        $activo = $activo === 1 ? 1 : 0;

        if ($nombre === '') throw new Exception('El nombre de la categoría es obligatorio.');
        if ($icono === '') $icono = 'fa-tags';
        if ($slug === '') $slug = self::slugify($nombre);
        if (self::nombreExiste($nombre)) throw new Exception('Ya existe una categoría con ese nombre.');
        if (self::slugExiste($slug)) throw new Exception('Ya existe una categoría con ese slug.');

        $stmt = $cn->prepare("INSERT INTO categorias (nombre, slug, icono, orden, activo) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) throw new Exception("Error prepare crear: " . $cn->error);
        $stmt->bind_param("sssii", $nombre, $slug, $icono, $orden, $activo);
        if (!$stmt->execute()) throw new Exception("No se pudo crear la categoría: " . $stmt->error);
        return $stmt->insert_id;
    }

    public static function actualizar(int $id, string $nombre, string $slug, string $icono, int $orden, int $activo)
    {
        $cn = Conexion::conectar();
        $nombre = app_normalize_text(trim($nombre));
        $slug = trim($slug);
        $icono = trim($icono);
        $orden = $orden > 0 ? $orden : 1;
        $activo = $activo === 1 ? 1 : 0;

        if ($id <= 0) throw new Exception('ID inválido.');
        if ($nombre === '') throw new Exception('El nombre de la categoría es obligatorio.');
        if ($icono === '') $icono = 'fa-tags';
        if ($slug === '') $slug = self::slugify($nombre);
        if (self::nombreExiste($nombre, $id)) throw new Exception('Ya existe otra categoría con ese nombre.');
        if (self::slugExiste($slug, $id)) throw new Exception('Ya existe otra categoría con ese slug.');

        $stmt = $cn->prepare("UPDATE categorias SET nombre = ?, slug = ?, icono = ?, orden = ?, activo = ? WHERE id = ? LIMIT 1");
        if (!$stmt) throw new Exception("Error prepare actualizar: " . $cn->error);
        $stmt->bind_param("sssiii", $nombre, $slug, $icono, $orden, $activo, $id);
        if (!$stmt->execute()) throw new Exception("No se pudo actualizar la categoría: " . $stmt->error);
        return true;
    }

    public static function eliminar(int $id)
    {
        $cn = Conexion::conectar();
        if ($id <= 0) throw new Exception('ID inválido.');

        $stmtCheck = $cn->prepare("SELECT COUNT(*) AS total FROM productos WHERE categoria_id = ?");
        if (!$stmtCheck) throw new Exception("Error prepare validar eliminación: " . $cn->error);
        $stmtCheck->bind_param("i", $id);
        $stmtCheck->execute();
        $row = $stmtCheck->get_result()->fetch_assoc();
        if ((int)($row['total'] ?? 0) > 0) {
            throw new Exception('No puedes eliminar una categoría que todavía tiene productos asociados.');
        }

        $stmt = $cn->prepare("DELETE FROM categorias WHERE id = ? LIMIT 1");
        if (!$stmt) throw new Exception("Error prepare eliminar: " . $cn->error);
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) throw new Exception("No se pudo eliminar la categoría: " . $stmt->error);
        if ($stmt->affected_rows === 0) throw new Exception('No se encontró la categoría para eliminar.');
        return true;
    }
}
