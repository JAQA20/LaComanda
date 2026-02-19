<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../model/Categorias.php";

try {
    $cats = Categorias::listarActivas();
    echo json_encode(["status" => "OK", "data" => $cats]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Error al cargar categorías"]);
}
