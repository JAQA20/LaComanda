<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$archivo = __DIR__ . "/ordenes.json";
$contenido = file_get_contents($archivo);
$ordenes = json_decode($contenido, true);

if (!is_array($ordenes)) $ordenes = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mesa = $_POST["mesa"] ?? null;

    if (!$mesa) {
        echo json_encode(["status" => "ERROR", "mensaje" => "Mesa no recibida"]);
        exit;
    }

    foreach ($ordenes as &$orden) {
        if ($orden["mesa"] == $mesa && $orden["estado"] === "pendiente") {

            // Cambiar estado igual que cocina
            $orden["estado"] = "entregada";
            $orden["hora_entrega"] = date("H:i");
            break;
        }
    }
    unset($orden);

    file_put_contents($archivo, json_encode($ordenes, JSON_PRETTY_PRINT));

    echo json_encode(["status" => "OK"]);
    exit;
}

echo json_encode(["status" => "ERROR", "mensaje" => "Método no permitido"]);
