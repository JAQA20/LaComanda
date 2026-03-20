<?php
// require_once __DIR__ . "/../config/rutas.php";
// if (session_status() === PHP_SESSION_NONE) session_start();

// if (!isset($_SESSION["usuario_id"])) {
//     header("Location: " . BASE_URL . "/views/login.php");
//     exit;
// }


require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/SesionesActivas.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

SesionesActivas::tocarSesionActual();
