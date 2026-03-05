<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../model/MesaLayout.php";

// Si tenés sesión/roles, aquí validás que sea admin
// session_start();
// if (!isset($_SESSION["user"]) || $_SESSION["rol"] !== "Admin") { ... }

$raw = file_get_contents("php://input");
$body = json_decode($raw, true);

if (!$body || !isset($body["id"], $body["x"], $body["y"], $body["w"], $body["h"])) {
    http_response_code(400);
    echo json_encode(["status" => "ERROR", "message" => "Payload inválido"]);
    exit;
}

$id = (int)$body["id"];
$x  = (float)$body["x"];
$y  = (float)$body["y"];
$w  = (float)$body["w"];
$h  = (float)$body["h"];
$zona = $body["zona"] ?? "main";

// Validación básica (evita cosas raras)
$clamp = function ($v) {
    return max(0, min(100, $v));
};
$x = $clamp($x);
$y = $clamp($y);
$w = max(1, min(100, $w));
$h = max(1, min(100, $h));

try {
    $ok = MesaLayoutModel::guardarPosicion($id, $x, $y, $w, $h, $zona);
    echo json_encode($ok ? ["status" => "OK"] : ["status" => "ERROR", "message" => "No se pudo guardar"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "ERROR", "message" => "Error interno"]);
}
