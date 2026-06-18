<?php
session_start();
require_once __DIR__ . "/../model/SesionesActivas.php";
require_once __DIR__ . "/../config/rutas.php";

SesionesActivas::cerrarSesionActual();
session_unset();
session_destroy();

$loginUrl = BASE_URL . "views/login.php?logged_out=1";
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Cerrando sesión...</title>
    <meta http-equiv="refresh" content="1;url=<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>">
</head>

<body>
    <script src="<?= BASE_URL ?>public/js/session-sync.js"></script>
    <script>
        if (window.LaComandaSessionSync) {
            window.LaComandaSessionSync.notifyLogout();
        }
        window.location.replace(<?= json_encode($loginUrl) ?>);
    </script>
    <noscript>
        <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>">Continuar al inicio de sesión</a>
    </noscript>
</body>

</html>
