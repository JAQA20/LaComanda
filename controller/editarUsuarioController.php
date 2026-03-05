<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
verificarRol([1]); // Solo Admin

require_once __DIR__ . "/../model/Usuarios.php";

$errors = [];
$roles = [];
$usuario = null;

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($id <= 0) {
    header("Location: /LaComanda-main/views/admin/usuarios.php");
    exit;
}

// Cargar roles y usuario
try {
    $roles = Usuarios::listarRoles();
    $usuario = Usuarios::obtenerPorId($id);
    if (!$usuario) {
        header("Location: /LaComanda-main/views/admin/usuarios.php");
        exit;
    }
} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}

// POST: actualizar
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"] ?? "";
    $apellido = $_POST["apellido"] ?? "";
    $email = $_POST["email"] ?? "";
    $rol_id = $_POST["rol_id"] ?? "";

    $password  = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";

    try {
        $passToUpdate = null;
        if (trim($password) !== "" || trim($password2) !== "") {
            if ($password !== $password2) {
                throw new Exception("Las contraseñas no coinciden.");
            }
            $passToUpdate = $password;
        }

        Usuarios::actualizar($id, $nombre, $apellido, $email, (int)$rol_id, $passToUpdate);

        header("Location: /LaComanda-main/views/admin/usuarios.php");
        exit;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();

        // Mantener lo que el usuario escribió
        $usuario["nombre"] = $nombre;
        $usuario["apellido"] = $apellido;
        $usuario["email"] = $email;
        $usuario["rol_id"] = (int)$rol_id;
    }
}

require_once __DIR__ . "/../views/admin/editarUsuario.php";
