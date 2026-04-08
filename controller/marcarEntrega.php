<?php
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../model/OrdenesSync.php";

// Este controlador deja de tocar el archivo JSON.
// Ahora marca la orden completa como entregada en MySQL y libera la mesa.
// Se mantiene desde cocina/admin para no romper el flujo actual mientras
// migramos barista y la entrega final completa.

verificarRol([1, 3]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . BASE_URL . "views/cocina.php");
    exit;
}

$numero = isset($_POST["numero"]) ? (int)$_POST["numero"] : 0;

if ($numero <= 0) {
    header("Location: " . BASE_URL . "views/cocina.php");
    exit;
}

try {
    OrdenesSync::marcarOrdenEntregada($numero);
} catch (Throwable $e) {
    error_log("marcarEntrega ERROR: " . $e->getMessage());
}

header("Location: " . BASE_URL . "views/cocina.php");
exit;
