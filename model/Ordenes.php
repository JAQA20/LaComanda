<?php
require_once __DIR__ . "/Conexion.php";

// Este modelo se ajusta al schema nuevo. Ya no depende de timestamp/id_estado
// del diseño viejo. El estado general de cada orden se calcula desde detalle_orden.

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$ordenes = [];
$total_ordenes = 0;
$total_vendido = 0;
$entregadas = 0;
$pendientes = 0;

try {
    $sql = "
        SELECT
            o.id_orden,
            o.numero_orden,
            o.total,
            o.fecha_creacion AS fecha,
            CASE
                WHEN COUNT(d.id_detalle) = 0 THEN 'sin_items'
                WHEN SUM(CASE WHEN d.estado_item = 'entregado' THEN 1 ELSE 0 END) = COUNT(d.id_detalle) THEN 'entregada'
                WHEN SUM(CASE WHEN d.estado_item = 'listo' THEN 1 ELSE 0 END) = COUNT(d.id_detalle) THEN 'lista'
                WHEN SUM(CASE WHEN d.estado_item = 'en_preparacion' THEN 1 ELSE 0 END) > 0 THEN 'en_preparacion'
                ELSE 'pendiente'
            END AS estado_general
        FROM ordenes o
        LEFT JOIN detalle_orden d ON d.id_orden = o.id_orden
        GROUP BY o.id_orden, o.numero_orden, o.total, o.fecha_creacion
        ORDER BY o.fecha_creacion DESC
        LIMIT 100
    ";

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

$total_ordenes = count($ordenes);
$total_vendido = array_sum(array_column($ordenes, 'total'));
$entregadas = count(array_filter($ordenes, fn($o) => ($o['estado_general'] ?? '') === 'entregada'));
$pendientes = count(array_filter($ordenes, fn($o) => in_array(($o['estado_general'] ?? ''), ['pendiente', 'en_preparacion', 'lista'], true)));
