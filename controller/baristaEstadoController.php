<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../model/Barista.php";

// ========JARVIS UPDATE========
// Endpoint de lectura para barista.
// Ahora depende del modelo Barista.php para dejar este módulo completamente separado
// de cocina y del resto del flujo operativo.

verificarRol([1, 4]);

try {
    $payload = Barista::obtenerPanel();
    echo json_encode([
        "ok" => true,
        "pendientes" => $payload['pendientes'],
        "listas" => $payload['listas']
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
