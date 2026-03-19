<?php
require_once __DIR__ . "/Conexion.php";


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");


header("Expires: 0");
$ordenes = [];
$total_ordenes = 0;
$total_vendido = 0;
$entregadas = 0;
$pendientes = 0;

try {

    $sql = "SELECT 
                id_orden,
                total,
                timestamp AS fecha,
                id_estado
            FROM ordenes
            ORDER BY timestamp DESC
            LIMIT 100";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $result = $stmt->get_result();
    $ordenes = $result->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    echo "<pre style='color:red'>";
    echo "ERROR SQL / MYSQLI:\n";
    echo $e->getMessage();
    echo "</pre>";
    exit;
}

//Para debug (ayuda a ver la estructura de datos que se obtiene de la BD)
// echo "<pre>";
// print_r($ordenes);
// echo "</pre>";
// exit;



$total_ordenes = count($ordenes);
$total_vendido = array_sum(array_column($ordenes, 'total'));
$entregadas = count(array_filter($ordenes, fn($o) => strpos(strtolower($o['estado']), 'entregada') !== false));
$pendientes = count(array_filter($ordenes, fn($o) => strpos(strtolower($o['estado']), 'pendiente') !== false));
