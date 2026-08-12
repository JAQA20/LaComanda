<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../../model/Conexion.php";

$producto_id = intval($_GET['producto_id'] ?? 0);
if ($producto_id <= 0) {
    echo json_encode(["status" => "ERROR", "message" => "Producto ID inválido"]);
    exit;
}

$conexion = Conexion::conectar();

// Obtener los grupos asignados a este producto O a su categoría
$sql = "
    SELECT DISTINCT g.id, g.nombre, g.requerido, g.seleccion_multiple 
    FROM grupos_opciones g
    LEFT JOIN categoria_grupos cg ON cg.grupo_id = g.id
    LEFT JOIN producto_grupos pg ON pg.grupo_id = g.id
    INNER JOIN productos p ON p.id = ? 
    WHERE (cg.categoria_id = p.categoria_id OR pg.producto_id = p.id)
      AND g.activo = 1
    ORDER BY g.id ASC
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$resGrupos = $stmt->get_result();

$grupos = [];
while ($grupo = $resGrupos->fetch_assoc()) {
    // Obtener las opciones para este grupo
    $stmtOpt = $conexion->prepare("SELECT id, nombre, precio_adicional FROM opciones WHERE grupo_id = ? AND activo = 1 ORDER BY id ASC");
    $stmtOpt->bind_param("i", $grupo['id']);
    $stmtOpt->execute();
    $resOpt = $stmtOpt->get_result();
    
    $opciones = [];
    while ($opt = $resOpt->fetch_assoc()) {
        $opciones[] = $opt;
    }
    
    $grupo['opciones'] = $opciones;
    $grupos[] = $grupo;
}

echo json_encode(["status" => "OK", "data" => $grupos]);
