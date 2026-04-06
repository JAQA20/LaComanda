<?php
ini_set('display_errors', 1);
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
    header("Location: " . BASE_URL . "views/admin/nuevoUsuario.php");
    exit;
}

$old = [
    "nombre" => trim($_POST["nombre"] ?? ""),
    "apellido" => trim($_POST["apellido"] ?? ""),
    "email" => trim($_POST["email"] ?? ""),
    "rol_id" => trim((string)($_POST["rol_id"] ?? ""))
];

$password  = $_POST["password"] ?? "";
$password2 = $_POST["password2"] ?? "";

try {
    if ($old["nombre"] === "" || $old["apellido"] === "" || $old["email"] === "" || $old["rol_id"] === "") {
        throw new Exception("Todos los campos obligatorios deben completarse.");
    }

    if ($password !== $password2) {
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

    $_SESSION["nuevo_usuario_success"] = "Usuario creado correctamente.";
    header("Location: " . BASE_URL . "views/admin/usuarios.php?created=1");
    exit;
} catch (Throwable $e) {
    $_SESSION["nuevo_usuario_errors"] = [$e->getMessage()];
    $_SESSION["nuevo_usuario_old"] = $old;
    header("Location: " . BASE_URL . "views/admin/nuevoUsuario.php");
    exit;
}
