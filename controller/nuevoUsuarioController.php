<?php
// ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/Usuarios.php";

verificarRol([1]); // Solo Admin

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . BASE_URL . "views/admin/usuarios.php");
    exit;
}

$old = [
    "nombre" => trim($_POST["nombre"] ?? ""),
    "apellido" => trim($_POST["apellido"] ?? ""),
    "email" => trim($_POST["email"] ?? ""),
    "rol_id" => trim((string)($_POST["rol_id"] ?? ""))
];

$password  = $_POST["password"] ?? "";
$password_confirm = $_POST["password_confirm"] ?? "";

try {
    if ($old["nombre"] === "" || $old["apellido"] === "" || $old["email"] === "" || $old["rol_id"] === "") {
        throw new Exception("Todos los campos obligatorios deben completarse.");
    }

    if ($password !== $password_confirm) {
        throw new Exception("Las contraseñas no coinciden.");
    }

    if (strlen($password) < 8) {
        throw new Exception("La contraseña debe tener al menos 8 caracteres.");
    }

    Usuarios::crear(
        $old["nombre"],
        $old["apellido"],
        $old["email"],
        $password,
        (int)$old["rol_id"]
    );

    header("Location: " . BASE_URL . "views/admin/usuarios.php?created=1");
    exit;
} catch (Throwable $e) {
    header("Location: " . BASE_URL . "views/admin/usuarios.php?error=" . urlencode($e->getMessage()));
    exit;
}
