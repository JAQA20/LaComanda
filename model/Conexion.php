<?php
class Conexion
{
    public static function conectar()
    {
        $conexion = new mysqli(
            "127.0.0.1",
            "root",
            "",
            "la_comanda",
            3306
        );

        if ($conexion->connect_error) {
            die("Error de conexión: " . $conexion->connect_error);
        }

        $conexion->set_charset("utf8");
        return $conexion;
    }
}

//--------------------------------------------------------------------------------------------

$conexion = new mysqli("127.0.0.1", "root", "", "la_comanda", "3306");
$conexion->set_charset("utf8");
