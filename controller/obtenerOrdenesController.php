<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";

verificarRol([1, 3]);

$archivo = __DIR__ . "/ordenes.json";

$ordenes = file_exists($archivo)
    ? json_decode(file_get_contents($archivo), true)
    : [];

if (!is_array($ordenes)) {
    $ordenes = [];
}

echo json_encode([
    "ok" => true,
    "ordenes" => $ordenes
], JSON_UNESCAPED_UNICODE);
