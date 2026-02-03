<?php

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// require_once "../model/conexion.php";

// try {
//     $conexion = Conexion::conectar();

//     if ($conexion) {
//         echo "✅ Conexión OK con la base de datos la_comanda";
//     }
// } catch (Exception $e) {
//     echo "❌ Error de conexión: " . $e->getMessage();
// }


//--------------------------------------------------------------------------------------------

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../model/conexion.php";

if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

echo "✅ Conexión OK con la base de datos la_comanda";
