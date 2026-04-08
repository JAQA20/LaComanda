<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../model/OrdenesSync.php";

// ========JARVIS UPDATE========
// Este controlador deja de leer/escribir ordenes.json.
// Ahora entrega la última orden activa de la mesa para el usuario actual usando MySQL.

verificarRol([1, 2]);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "status" => "ERROR",
        "message" => "Método no permitido"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$mesa = isset($_POST["mesa"]) ? (int)$_POST["mesa"] : 0;
$area = isset($_POST["area"]) ? trim((string)$_POST["area"]) : '';
if ($mesa <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "ERROR",
        "message" => "Mesa requerida"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioId = isset($_SESSION["usuario_id"]) ? (int)$_SESSION["usuario_id"] : null;

try {
    OrdenesSync::entregarOrdenPorMesaUsuario($mesa, $usuarioId, $area);

    echo json_encode([
        "status" => "OK",
        "message" => "Sub-orden entregada correctamente",
        "mesa" => (string)$mesa,
        "area" => $area
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "ERROR",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
