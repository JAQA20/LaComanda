<?php
require_once "Conexion.php";

class MesaLayoutModel
{

    public static function obtenerPorZona($zona = "main")
    {
        $db = Conexion::conectar();
        $sql = "SELECT id, x, y, w, h, zona FROM mesas_layout WHERE zona = ? ORDER BY id";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $zona);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $row["id"] = (int)$row["id"];
            $row["x"]  = (float)$row["x"];
            $row["y"]  = (float)$row["y"];
            $row["w"]  = (float)$row["w"];
            $row["h"]  = (float)$row["h"];
            $data[] = $row;
        }

        return $data;
    }

    public static function guardarPosicion($id, $x, $y, $w, $h, $zona = "main")
    {
        $db = Conexion::conectar();
        $sql = "INSERT INTO mesas_layout (id, x, y, w, h, zona)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE x=VALUES(x), y=VALUES(y), w=VALUES(w), h=VALUES(h), zona=VALUES(zona)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("idddds", $id, $x, $y, $w, $h, $zona);
        return $stmt->execute();
    }
}
