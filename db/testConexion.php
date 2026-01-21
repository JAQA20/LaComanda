<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "conexion.php";

if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

echo "✅ Conexión OK con la base de datos la_comanda";
