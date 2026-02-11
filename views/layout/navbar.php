<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="../../public/css/style.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Navbar</title>
</head>

<body>
    <!-- Navbar -->
    <nav id="navbar" class="custom-brown fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6 shadow-lg">
        <div class="flex items-center">
            <img class="h-10 w-10 object-contain mr-3" src="../public/img/logotipo2.PNG" alt="elegant coffee shop logo with toscana text, warm brown and mint colors, minimalist design" />
            <span class="text-beige text-xl font-semibold">Cafetería Toscana</span>
        </div>
        <div class="flex space-x-8">

            <button id="mesas-btn" class="text-beige hover-mint font-medium transition-all duration-200 border-b-2 border-mint">Mesas</button>
            <button id="cafes-btn" class="text-beige hover-mint font-medium transition-all duration-200">Cafés</button>
            <button id="comidas-btn" class="text-beige hover-mint font-medium transition-all duration-200">Comidas</button>
            <button id="especialidades-btn" class="text-beige hover-mint font-medium transition-all duration-200">Especialidades</button>
            <button id="postres-btn" class="text-beige hover-mint font-medium transition-all duration-200">Postres</button>
            <button id="bebidas-btn" class="text-beige hover-mint font-medium transition-all duration-200">Bebidas Frías</button>
            <?php if (isset($_SESSION["rol_id"]) && $_SESSION["rol_id"] == 1): ?>
                <button class="text-beige hover-mint font-medium transition-all duration-200  border-mint">
                    <a href="../views/admin/admin.php" class="text-decoration-none text-beige hover-mint font-medium transition-all duration-200 border-mint">Admin</a>
                </button>
            <?php endif; ?>
            <div class="dropdown">
                <button class="btn dropdown-toggle hover-mint" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user text-white text-2xl"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">

                    <!-- Nombre del usuario -->
                    <li class="px-3 py-2 text-sm text-gray-700 font-semibold border-b">
                        <?php if (isset($_SESSION["nombre"])): ?>
                            <?= htmlspecialchars($_SESSION["nombre"] . " " . $_SESSION["apellido"]) ?>
                        <?php else: ?>
                            Usuario
                        <?php endif; ?>
                    </li>

                    <li>
                        <a class="dropdown-item" href="../views/perfil.php">
                            <i class="fas fa-id-badge me-2"></i> Mi perfil
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item text-danger" href="../controller/logoutController.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                        </a>
                    </li>

                    <?php if (isset($_SESSION["rol_id"]) && $_SESSION["rol_id"] == 3): ?>
                        <li>
                            <a class="dropdown-item" href="../views/cocina.php">
                                <i class="fas fa-fire me-2"></i> Cocina
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>

            </div>
        </div>
    </nav>

</body>

</html>