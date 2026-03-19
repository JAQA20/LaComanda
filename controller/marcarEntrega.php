<?php
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/OrdenesSync.php";

// require_once __DIR__ . "/../config/rutas.php";
// $archivo = __DIR__ . "/ordenes.json";

// // Leer ordenes
// $ordenes = file_exists($archivo)
//     ? json_decode(file_get_contents($archivo), true)
//     : [];

// if ($_SERVER["REQUEST_METHOD"] === "POST") {

//     $numero = intval($_POST["numero"]);

//     // Buscar la orden y marcarla como entregada
//     foreach ($ordenes as &$orden) {
//         if ($orden["numero"] == $numero) {
//             $orden["estado"] = "entregada";
//             $orden["hora_entrega"] = date("H:i");
//             break;
//         }
//     }

//     // Guardar cambios en el archivo
//     file_put_contents($archivo, json_encode($ordenes, JSON_PRETTY_PRINT));

//     // Volver a la vista de cocina
//     header("Location: " . BASE_URL . "views/cocina.php");
//     exit;
// }

require_once __DIR__ . "/../config/rutas.php";
$archivo = __DIR__ . "/ordenes.json";

// Leer órdenes
$ordenes = file_exists($archivo)
    ? json_decode(file_get_contents($archivo), true)
    : [];

if (!is_array($ordenes)) {
    $ordenes = [];
}

$ordenes = app_normalize_order_array($ordenes);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numero = intval($_POST["numero"] ?? 0);

    foreach ($ordenes as &$orden) {
        if (
            isset($orden["numero"], $orden["estado"]) &&
            (int)$orden["numero"] === $numero &&
            $orden["estado"] === "lista"
        ) {
            $orden["estado"] = "entregada";
            $orden["hora_entrega"] = date("H:i");
            $orden["timestamp_entrega"] = time();
            break;
        }
    }
    unset($orden);

    // Guardar cambios en el archivo
    file_put_contents($archivo, json_encode($ordenes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    try {
        OrdenesSync::marcarEntregadaPorNumero($numero, time());
    } catch (Throwable $e) {
        error_log("Error sincronizando entrega en MySQL (numero): " . $e->getMessage());
    }
    file_put_contents($archivo, json_encode($ordenes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header("Location: " . BASE_URL . "views/cocina.php");
    exit;
}
