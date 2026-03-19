<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";

verificarRol([1, 2]);

$archivo = __DIR__ . "/ordenes.json";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "status" => "ERROR",
        "message" => "Método no permitido"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$mesa = $_POST["mesa"] ?? null;

if (!$mesa) {
    http_response_code(400);
    echo json_encode([
        "status" => "ERROR",
        "message" => "Mesa requerida"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!file_exists($archivo)) {
    echo json_encode([
        "status" => "ERROR",
        "message" => "No existe el archivo de órdenes"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$ordenes = json_decode(file_get_contents($archivo), true);

if (!is_array($ordenes)) {
    $ordenes = [];
}

$ordenes = app_normalize_order_array($ordenes);

$usuarioId = $_SESSION["usuario_id"] ?? null;
$actualizada = false;

// Buscar la última orden de ESA mesa, de ESE usuario, que esté en lista
for ($i = count($ordenes) - 1; $i >= 0; $i--) {
    $orden = $ordenes[$i];

    if (
        isset($orden["mesa"], $orden["estado"]) &&
        (string)$orden["mesa"] === (string)$mesa &&
        (string)$orden["estado"] === "lista" &&
        (($orden["usuario_id"] ?? null) == $usuarioId)
    ) {
        $ordenes[$i]["estado"] = "entregada";
        $ordenes[$i]["hora_entrega"] = date("H:i");
        $actualizada = true;
        break;
    }
}

if (!$actualizada) {
    echo json_encode([
        "status" => "ERROR",
        "message" => "No se encontró una orden en estado lista para esa mesa"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

file_put_contents(
    $archivo,
    json_encode($ordenes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo json_encode([
    "status" => "OK",
    "message" => "Orden entregada correctamente",
    "mesa" => (string)$mesa
], JSON_UNESCAPED_UNICODE);
exit;
