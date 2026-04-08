<?php
require_once __DIR__ . "/../config/env.php";

class Conexion
{
    public static function conectar()
    {
        app_configure_errors();

        $host = app_env('DB_HOST', app_env('MYSQLHOST', 'db'));
        $user = app_env('DB_USER', app_env('MYSQLUSER', 'root'));

        $password = app_env('DB_PASSWORD', app_env('MYSQLPASSWORD', '12345678'));
        $database = app_env('DB_NAME', app_env('MYSQLDATABASE', 'la_comanda'));
        $port = (int)app_env('DB_PORT', app_env('MYSQLPORT', 3306));

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $conexion = new mysqli($host, $user, $password, $database, $port);
        $conexion->set_charset("utf8");

        return $conexion;
    }
}

$conexion = Conexion::conectar();
