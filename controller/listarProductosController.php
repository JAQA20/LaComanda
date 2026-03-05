<?php
require_once __DIR__ . "/../middleware/auth.php"; // logueado
require_once __DIR__ . "/../model/Productos.php";

header("Content-Type: application/json; charset=utf-8");

try {
    $slug = $_GET["categoria"] ?? "";
    $data = Productos::listarPorCategoriaSlug($slug);
    echo json_encode(["status" => "OK", "data" => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => $e->getMessage()]);
}
exit;
