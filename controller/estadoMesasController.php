<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../model/OrdenesSync.php";

// ========JARVIS UPDATE========
// Este controlador deja de leer controller/ordenes.json.
// Ahora calcula el estado de las mesas desde MySQL según las órdenes del usuario actual.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    $usuarioId = isset($_SESSION["usuario_id"]) ? (int)$_SESSION["usuario_id"] : null;
    $estadoPorMesa = OrdenesSync::obtenerEstadoMesasPorUsuario($usuarioId);

    echo json_encode([
        "status" => "OK",
        "data" => $estadoPorMesa
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "ERROR",
        "message" => $e->getMessage(),
        "line" => $e->getLine(),
        "file" => basename($e->getFile())
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
