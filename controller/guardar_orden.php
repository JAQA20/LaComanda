<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=utf-8");

// Ruta del archivo de órdenes
$archivo = __DIR__ . "/ordenes.json";

// Crear archivo si no existe
if (!file_exists($archivo)) {
    file_put_contents($archivo, json_encode([]));
}

// Leer JSON actual
$contenido = file_get_contents($archivo);
$ordenes = json_decode($contenido, true);

// Si json_decode falla, reiniciar arreglo
if (!is_array($ordenes)) {
    $ordenes = [];
}

// Recibir JSON del fetch()
$input = file_get_contents("php://input");
//file_put_contents(__DIR__ . "/input_debug.txt", $input);

$data = json_decode($input, true);

// Validar entrada
if (!is_array($data)) {
    echo json_encode([
        "status" => "ERROR",
        "message" => "Datos inválidos",
        "debug" => $input
    ]);
    exit;
}

// Normalizar valores
$data["mesa"]  = isset($data["mesa"])  ? (string)$data["mesa"]  : "N/A";
$data["items"] = isset($data["items"]) ? (string)$data["items"] : "";

// Generar número de orden consecutivo
$ultimoNumero = 0;
foreach ($ordenes as $o) {
    if (isset($o["numero"]) && is_numeric($o["numero"]) && $o["numero"] > $ultimoNumero) {
        $ultimoNumero = (int)$o["numero"];
    }
}
$numero = $ultimoNumero + 1;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Completar orden
$data["numero"]    = $numero;
$data["estado"]    = "pendiente";
$data["timestamp"] = time();
$data["usuario_id"] = $_SESSION["usuario_id"] ?? null;

// Agregar al arreglo
$ordenes[] = $data;

// Guardar archivo actualizado
file_put_contents(
    $archivo,
    json_encode($ordenes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// Respuesta final al fetch
echo json_encode([
    "status" => "OK",
    "numero" => $numero
]);
exit;
