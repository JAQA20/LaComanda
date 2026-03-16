<?php
session_start();
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/Conexion.php";

if (!empty($_POST["btnlogin"])) {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        header("Location: " . BASE_URL . "views/login.php?error=campos");
        exit;
    }

    $stmt = $conexion->prepare("
        SELECT id, nombre, apellido, email, password, rol_id, activo
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ");

    if (!$stmt) {
        header("Location: " . BASE_URL . "views/login.php?error=general");
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {

        if (isset($user["activo"]) && (int)$user["activo"] !== 1) {
            header("Location: " . BASE_URL . "views/login.php?error=inactivo");
            exit;
        }

        if (password_verify($password, $user["password"])) {

            $_SESSION["usuario_id"] = $user["id"];
            $_SESSION["nombre"] = $user["nombre"];
            $_SESSION["apellido"] = $user["apellido"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["rol_id"] = (int)$user["rol_id"];

            switch ($_SESSION["rol_id"]) {
                case 1:
                    header("Location: " . BASE_URL . "views/admin/admin.php");
                    exit;

                case 2:
                    header("Location: " . BASE_URL . "views/index.php");
                    exit;

                case 3:
                    header("Location: " . BASE_URL . "views/cocina.php");
                    exit;

                case 4:
                    header("Location: " . BASE_URL . "views/barista.php");
                    exit;

                default:
                    session_unset();
                    session_destroy();
                    header("Location: " . BASE_URL . "views/login.php?error=rol");
                    exit;
            }
        }
    }

    header("Location: " . BASE_URL . "views/login.php?error=credenciales");
    exit;
}
