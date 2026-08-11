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

if (!isset($_SERVER['HTTP_X_BACKGROUND_REQUEST'])) {
    $limiteInactividad = 7 * 60 * 60; // 7 horas
    if (isset($_SESSION['ultima_actividad_php']) && (time() - $_SESSION['ultima_actividad_php'] > $limiteInactividad)) {
        SesionesActivas::cerrarSesionActual();
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "views/login.php?error=Sesión expirada por inactividad");
        exit;
    }
    $_SESSION['ultima_actividad_php'] = time();
    SesionesActivas::tocarSesionActual();
}
