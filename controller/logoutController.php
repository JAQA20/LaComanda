<?php
session_start();
require_once __DIR__ . "/../model/SesionesActivas.php";
SesionesActivas::cerrarSesionActual();
session_unset();
session_destroy();

require_once __DIR__ . "/../config/rutas.php";

header("Location: " . BASE_URL . "views/login.php");
exit;
