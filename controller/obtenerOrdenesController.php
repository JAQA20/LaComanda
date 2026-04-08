<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../model/OrdenesSync.php";

// Este endpoint ya no lee controller/ordenes.json.
// Ahora obtiene las órdenes de cocina desde MySQL usando el modelo nuevo.

verificarRol([1, 3]);

try {
    $ordenes = OrdenesSync::obtenerOrdenesCocina();

    echo json_encode([
        "ok" => true,
        "ordenes" => $ordenes
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
