<?php
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";

verificarRol([1, 3]);

$numero = $_POST["numero"] ?? null;

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
    if ((string)$orden["numero"] === (string)$numero && ($orden["estado"] ?? "") === "pendiente") {
        $orden["estado"] = "lista";
        $orden["hora_lista"] = date("H:i");
        break;
    }
}
unset($orden);

file_put_contents($archivo, json_encode($ordenes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header("Location: " . BASE_URL . "views/cocina.php");
exit;
