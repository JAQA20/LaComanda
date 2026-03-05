<?php
require_once __DIR__ . "/Conexion.php";

class Categorias
{
    public static function listarActivas()
    {
        $cn = Conexion::conectar();
        $sql = "SELECT id, nombre, slug, icono
            FROM categorias
            WHERE activo = 1
            ORDER BY orden ASC, nombre ASC";
        $res = $cn->query($sql);
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        return $data;
    }

    public static function obtenerPorSlug($slug)
    {
        $cn = Conexion::conectar();
        $sql = "SELECT id, nombre, slug, icono
            FROM categorias
            WHERE slug = ?
            LIMIT 1";
        $stmt = $cn->prepare($sql);
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc() ?: null;
    }
}
