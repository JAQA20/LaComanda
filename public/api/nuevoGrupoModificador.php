<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../model/Modificadores.php";

try {
    verificarRol([1]); // Solo admins
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Method not allowed");
    }

    $nombre = $_POST['nombre'] ?? '';
    if (trim($nombre) === '') {
        throw new Exception("El nombre del grupo es obligatorio.");
    }

    $requerido = isset($_POST['requerido']) && $_POST['requerido'] == 1 ? 1 : 0;
    $seleccion_multiple = isset($_POST['seleccion_multiple']) && $_POST['seleccion_multiple'] == 1 ? 1 : 0;
    $activo = 1;

    $nombres_opciones = $_POST['opciones_nombre'] ?? [];
    $precios_opciones = $_POST['opciones_precio'] ?? [];
    
    $opciones = [];
    for ($i = 0; $i < count($nombres_opciones); $i++) {
        $n = trim($nombres_opciones[$i]);
        $p = floatval($precios_opciones[$i] ?? 0);
        if ($n !== '') {
            $opciones[] = ['nombre' => $n, 'precio_adicional' => $p];
        }
    }

    if (empty($opciones)) {
        throw new Exception("Debes agregar al menos una opción al grupo.");
    }

    $categorias = $_POST['categorias'] ?? [];
    $productos = $_POST['productos'] ?? []; // Por ahora vacío desde la vista principal

    $id = Modificadores::crearGrupo($nombre, $requerido, $seleccion_multiple, $activo, $opciones, $categorias, $productos);

    echo json_encode(['success' => true, 'id' => $id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
