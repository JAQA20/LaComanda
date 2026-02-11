<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * verificarRol([1,2]) -> permite Admin(1) y Mesero(2)
 * Admin (1) siempre entra a todo.
 */
function verificarRol(array $rolesPermitidos = [])
{
    if (!isset($_SESSION["rol_id"])) {
        header("Location: /LaComanda/login.php");
        exit;
    }

    $rol = (int)$_SESSION["rol_id"];

    // Admin puede todo
    if ($rol === 1) return;

    if (!in_array($rol, $rolesPermitidos, true)) {
        header("Location: /LaComanda/views/accesoRestringido.php");
        exit;
    }
}
