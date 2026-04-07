<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../config/text.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    $archivo = __DIR__ . "/ordenes.json";

    if (!file_exists($archivo)) {
        echo json_encode([
            "status" => "OK",
            "data" => []
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $contenido = file_get_contents($archivo);
    $ordenes = json_decode($contenido, true);

    if (!is_array($ordenes)) {
        $ordenes = [];
    }

    $ordenes = app_normalize_order_array($ordenes);

    $usuarioId = $_SESSION["usuario_id"] ?? null;

    $estadoPorMesa = [];

    foreach ($ordenes as $orden) {
        if (!isset($orden["mesa"], $orden["estado"])) {
            continue;
        }

        // Solo devolver órdenes del usuario actual
        if (($orden["usuario_id"] ?? null) != $usuarioId) {
            continue;
        }

        $mesa = (string)$orden["mesa"];
        $estado = trim((string)$orden["estado"]);

        if ($estado === "pendiente" || $estado === "lista") {
            $estadoPorMesa[$mesa] = $estado;
        }
    }

    echo json_encode([
        "status" => "OK",
        "data" => $estadoPorMesa
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    error_log("Error in estadoMesasController.php: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
    http_response_code(500);
    echo json_encode([
        "status" => "ERROR",
        "message" => $e->getMessage(),
        "line" => $e->getLine(),
        "file" => basename($e->getFile())
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
