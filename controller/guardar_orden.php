<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../model/OrdenesSync.php";

function normalizarTextoOrden($texto)
{
    if (!is_string($texto)) {
        return '';
    }

    $texto = trim($texto);
    if ($texto === '') {
        return '';
    }

    for ($i = 0; $i < 3; $i++) {
        if (preg_match('/Ã.|Â.|â./u', $texto) !== 1 && preg_match('//u', $texto) === 1) {
            break;
        }

        $convertido = @mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
        if (!is_string($convertido) || $convertido === '' || $convertido === $texto) {
            break;
        }

        $texto = $convertido;
    }

    return $texto;
}

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
$data["items"] = isset($data["items"]) ? normalizarTextoOrden((string)$data["items"]) : "";
$data["notas"] = isset($data["notas"]) ? normalizarTextoOrden((string)$data["notas"]) : "";

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
