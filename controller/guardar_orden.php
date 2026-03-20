<?php
require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../config/text.php";
app_configure_errors();

header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../model/OrdenesSync.php";

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
$data["items"] = isset($data["items"]) ? app_normalize_text((string)$data["items"]) : "";
$data["notas"] = isset($data["notas"]) ? app_normalize_text((string)$data["notas"]) : "";

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
$data["hora_entrega"] = $data["hora_entrega"] ?? null;
$data["usuario_id"] = $_SESSION["usuario_id"] ?? null;

// Agregar al arreglo
$ordenes[] = $data;

// Guardar archivo actualizado
file_put_contents(
    $archivo,
    json_encode($ordenes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

try {
    OrdenesSync::guardarEnBase($data);
} catch (Throwable $e) {
    error_log("Error sincronizando orden en MySQL: " . $e->getMessage());
}

// Respuesta final al fetch
echo json_encode([
    "status" => "OK",
    "numero" => $numero
]);
exit;
