<?php
session_start();

//validacion basica de login 

// if (!empty($_POST["btnlogin"])) {
//     if (!empty($_POST["email"]) and !empty($_POST["password"])) {
//         $email = $_POST["email"];
//         //$password = md5($_POST["password"]); // md5 to enctrypt pwds
//         $password = md5($_POST["password"]);
//         echo $email;
//         echo $password;
//         $sql = $conexion->query("select * from usuarios where email='$email' and password='$password' limit 1");
//         if ($data = $sql->fetch_object()) {
//             $_SESSION["nombre"] = $data->nombre;
//             $_SESSION["apellido"] = $data->apellido;
//             header("location:../views/index.php");
//             exit;
//         } else {
//             echo "<div class='alert alert-danger'> Incorrect email or password </div>";
//         }
//     } else {
//         echo "<div class='alert alert-danger'> Please enter an email and password </div>";
//     }
// }

//--------------------------------------------------------------------------------------------

//controller con manejo de roles y seguridad mejorada

if (!empty($_POST["btnlogin"])) {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Validación básica
    if ($email === "" || $password === "") {

        echo $email;
        echo $password;
        header("Location: ../views/login.php?error=campos");
        exit;
    }

    // Buscar usuario por email
    $stmt = $conexion->prepare("
        SELECT id, nombre, apellido, email, password, rol_id
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ");

    if (!$stmt) {
        // Debug opcional en desarrollo
        die("Error prepare(): " . $conexion->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {

        // Verificar password (si se usa password_hash en BD)
        //if (password_verify($password, $user["password"])) 
        if ($password === $user["password"]) { // comparación simple, no segura


            // Guardar sesión
            $_SESSION["user_id"]  = $user["id"];
            $_SESSION["nombre"]   = $user["nombre"];
            $_SESSION["apellido"] = $user["apellido"];
            $_SESSION["email"]    = $user["email"];
            $_SESSION["rol_id"]   = (int)$user["rol_id"];

            // Redirección por rol
            switch ($_SESSION["rol_id"]) {
                case 1: // Admin
                    header("Location: ../views/admin.php");
                    exit;

                case 2: // Mesero
                    header("Location: ../views/index.php");
                    exit;

                case 3: // Cocina
                    header("Location: ../views/cocina.php");
                    exit;

                default:
                    // Si el rol no es válido, cierras sesión y mandas error
                    session_unset();
                    session_destroy();
                    header("Location: ../views/login.php?error=rol");
                    echo "Rol de usuario no válido.";
                    exit;
            }
        }
    }

    // Si llegó aquí: email no existe o password incorrecto
    header("Location: ../views/login.php?error=credenciales");
    exit;
}
