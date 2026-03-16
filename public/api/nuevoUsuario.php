<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../../controller/nuevoUsuarioController.php";
$errors = [];
$old = [
    "nombre" => "",
    "apellido" => "",
    "email" => "",
    "rol_id" => ""
];

try {
    $roles = Usuarios::listarRoles();
} catch (Throwable $e) {
    $roles = [];
    $errors[] = "Error cargando roles: " . $e->getMessage();
}
