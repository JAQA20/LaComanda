<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/Usuarios.php";

verificarRol([1]); // Solo Admin

// Variables para la vista
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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $old["nombre"] = $_POST["nombre"] ?? "";
    $old["apellido"] = $_POST["apellido"] ?? "";
    $old["email"] = $_POST["email"] ?? "";
    $old["rol_id"] = $_POST["rol_id"] ?? "";

    $password  = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";

    try {
        if ($password !== $password2) {
            throw new Exception("Las contraseñas no coinciden.");
        }
        if (strlen($password) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres.");
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
        $errors[] = $e->getMessage();
    }
}

// Renderizar la vista (solo HTML)
require_once ROOT_PATH . "/views/admin/nuevoUsuario.php";
