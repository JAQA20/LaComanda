<?php
require_once __DIR__ . "/../config/text.php";
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";

verificarRol([1, 3, 4]); // Admin, Cocina y Barista

$numero = $_POST["numero"] ?? null;

require_once __DIR__ . "/../model/OrdenesSync.php";

if (!$numero) {
    header("Location: " . BASE_URL . "views/cocina.php");
    exit;
}

$archivo = ROOT_PATH . "/controller/ordenes.json";

if (!file_exists($archivo)) {
    header("Location: " . BASE_URL . "views/cocina.php");
    exit;
}

$ordenes = json_decode(file_get_contents($archivo), true);

if (!is_array($ordenes)) {
    $ordenes = [];
}

foreach ($ordenes as &$orden) {
    if ((string)$orden["numero"] === (string)$numero && in_array($orden["estado"] ?? "", ["pendiente", "en_preparacion"], true)) {
        $orden["estado"] = "lista";
        $orden["hora_lista"] = date("H:i");
        break;
    }
}
unset($orden);

file_put_contents($archivo, json_encode($ordenes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

try {
    OrdenesSync::marcarListaPorNumero($numero, time());
} catch (Throwable $e) {
    error_log("Error sincronizando lista de orden en MySQL: " . $e->getMessage());
}

if (isset($_SESSION["rol_id"]) && (int)$_SESSION["rol_id"] === 4) {
    header("Location: " . BASE_URL . "views/barista.php");
} else {
    header("Location: " . BASE_URL . "views/cocina.php");
}
exit;
