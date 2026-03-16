<?php
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../model/Productos.php";
verificarRol([1]); // solo Admin

header("Content-Type: application/json; charset=utf-8");

$slug = $_GET["categoria"] ?? "";
$slug = trim($slug);

if ($slug === "" || $slug === "mesas") {
    echo json_encode(["status" => "OK", "data" => []]);
    exit;
}

try {
    $productos = Productos::listarPorCategoriaSlug($slug);
    echo json_encode(["status" => "OK", "data" => $productos]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Error al cargar productos"]);
}
