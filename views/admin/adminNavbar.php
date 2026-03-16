<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";

verificarRol([1]); // solo Admin
$rutaActual = $_SERVER["REQUEST_URI"];
?>



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };
    </script>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS principal -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">


</head>

<body>
    <?php
    $rutaActual = $_SERVER["REQUEST_URI"];

    function isActive($ruta, $rutaActual)
    {
        return str_contains($rutaActual, $ruta)
            ? "border-b-2 border-mint"
            : "";
    }
    ?>

    <nav id="navbar" class="custom-brown fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6 shadow-lg">
        <div class="flex items-center">
            <img class="h-10 w-10 object-contain mr-3"
                src="<?= BASE_URL ?>public/img/logotipo2.PNG"
                alt="Cafetería Toscana" />
            <span class="text-beige text-xl font-semibold">Cafetería Toscana</span>
        </div>

        <div class="flex space-x-8 items-center">
            <a class="text-beige text-decoration-none hover-mint font-medium transition-all duration-200 <?= isActive('/views/admin/admin.php', $rutaActual) ?>"
                href="<?= BASE_URL ?>views/admin/admin.php">
                Dashboard
            </a>

            <a class="text-beige text-decoration-none hover-mint font-medium transition-all duration-200 <?= isActive('/views/index.php', $rutaActual) ?>"
                href="<?= BASE_URL ?>views/index.php">
                Tomar ordenes
            </a>

            <a class="text-beige text-decoration-none hover-mint font-medium transition-all duration-200 <?= isActive('/views/admin/usuarios.php', $rutaActual) || isActive('/controller/', $rutaActual) ? 'border-b-2 border-mint' : '' ?>"
                href="<?= BASE_URL ?>views/admin/usuarios.php">
                Usuarios
            </a>

            <a class="text-beige text-decoration-none hover-mint font-medium transition-all duration-200 <?= isActive('/views/admin/productos.php', $rutaActual) || isActive('/controller/', $rutaActual) ? 'border-b-2 border-mint' : '' ?>"
                href="<?= BASE_URL ?>views/admin/productos.php">
                Productos
            </a>

            <a class="text-beige text-decoration-none hover-mint font-medium transition-all duration-200 <?= isActive('/views/admin/ordenesAdmin.php', $rutaActual) ?>"
                href="<?= BASE_URL ?>views/admin/ordenesAdmin.php">
                Historial de ordenes
            </a>

            <div class="dropdown">
                <button class="btn dropdown-toggle hover-mint" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user text-white text-2xl"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2 text-sm text-gray-700 font-semibold border-b">
                        <?php if (isset($_SESSION["nombre"])): ?>
                            <?= htmlspecialchars($_SESSION["nombre"] . " " . ($_SESSION["apellido"] ?? "")) ?>
                        <?php else: ?>
                            Usuario
                        <?php endif; ?>
                    </li>

                    <!-- <li>
                        <a class="dropdown-item" href="<?= BASE_URL ?>views/perfil.php">
                            <i class="fas fa-id-badge me-2"></i> Mi perfil
                        </a>
                    </li> -->

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item text-danger" href="<?= BASE_URL ?>public/api/logout.php<?= "?redirect=" . urlencode($_SERVER["REQUEST_URI"]) ?>">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

</body>

</html>