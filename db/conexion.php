<?php
$conexion = new mysqli("127.0.0.1", "root", "12345678", "la_comanda", "3306");
$conexion->set_charset("utf8");

if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");
