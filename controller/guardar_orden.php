<?php
require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../config/text.php";
app_configure_errors();

header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../model/OrdenesSync.php";

// ========JARVIS UPDATE========
// Este controlador deja de trabajar con controller/ordenes.json.
// Ahora recibe la orden del frontend y la persiste directamente en MySQL.

set_exception_handler(function (Throwable $e) {
    error_log("guardar_orden EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    error_log($e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        "status" => "ERROR",
        "message" => "Error interno al guardar la orden"
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log("guardar_orden FATAL: " . ($error['message'] ?? 'sin mensaje') . " in " . ($error['file'] ?? 'desconocido') . ":" . ($error['line'] ?? 0));
    }
});

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        "status" => "ERROR",
        "message" => "Datos inválidos",
        "debug" => $input
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========JARVIS UPDATE========
// Se normaliza el payload esperado desde el frontend para insertarlo en el schema nuevo.
$data["mesa"] = isset($data["mesa"]) ? (string)$data["mesa"] : "0";
$data["items"] = isset($data["items"]) ? app_normalize_text((string)$data["items"]) : "";
$data["notas"] = isset($data["notas"]) ? app_normalize_text((string)$data["notas"]) : "";
$data["usuario_id"] = $_SESSION["usuario_id"] ?? null;

if ((int)$data["mesa"] <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "ERROR",
        "message" => "Mesa inválida"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (trim($data["items"]) === '') {
    http_response_code(400);
    echo json_encode([
        "status" => "ERROR",
        "message" => "La orden no tiene productos"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$numero = OrdenesSync::guardarEnBase($data);

echo json_encode([
    "status" => "OK",
    "numero" => $numero
], JSON_UNESCAPED_UNICODE);
exit;
