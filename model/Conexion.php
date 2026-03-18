<?php
class Conexion
{
    public static function conectar()
    {

        $host = getenv('DB_HOST') ?: 'db'; // 'db' para que funcione con Docker
        // $host = getenv('DB_HOST') ?: '127.0.0.1'; // 'localhost/127.0.0.1 para desarrollo local sin Docker' 

        $user = getenv('DB_USER') ?: 'root';

        $password = getenv('DB_PASSWORD');
        if ($password === false) {
            $password = '12345678';
        }

        $database = getenv('DB_NAME') ?: 'la_comanda';
        $port = (int)(getenv('DB_PORT') ?: 3306);

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Anadimos para que mysqli lance excepciones en caso de error

        $conexion = new mysqli($host, $user, $password, $database, $port);
        $conexion->set_charset("utf8mb4");

        return $conexion;
    }
}
$conexion = Conexion::conectar();



// class Conexion
// {
//     public static function conectar()
//     {
//         $host = getenv('DB_HOST') ?: '127.0.0.1';
//         $user = getenv('DB_USER') ?: 'root';
//         $password = getenv('DB_PASSWORD');
//         if ($password === false) {
//             $password = '12345678';
//         }
//         $database = getenv('DB_NAME') ?: 'la_comanda';
//         $port = (int)(getenv('DB_PORT') ?: 3306);

//         $conexion = new mysqli($host, $user, $password, $database, $port);

//         if ($conexion->connect_error) {
//             die("Error de conexión: " . $conexion->connect_error); //Eliminamos el die() para evitar mostrar errores en producción, pero podríamos loguear el error aquí.
//         }

//         $conexion->set_charset("utf8");
//         return $conexion;
//     }
// }

// $conexion = Conexion::conectar();
