<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";

verificarRol([1]); // solo Admin
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };
    </script>

    <!-- Tailwind (porque tu navbar usa clases tipo flex, space-x, fixed, etc.) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- ✅ Tu CSS principal (ABSOLUTO) -->
    <link rel="stylesheet" href="/LaComanda-main/public/css/style.css">



</head>

<body class="custom-beige min-h-screen">

    <!-- Navbar -->
    <?php
    include './adminNavbar.php';
    ?>

    <!-- Main Content -->
    <div class="flex pt-16 min-h-screen">

        <!-- Content Area -->
        <main id="content-area" class="flex-1 p-6">
            <div id="mesas-view" class="block">
                <h1 class="text-brown text-3xl font-bold mb-8">Admin-Dashboard</h1>
                <div class="grid grid-cols-4 gap-6">
                    <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <a href="./usuarios.php"><i class="fas fa-user text-white text-xl"></i></a>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Usuarios</h3>
                        </div>
                    </div>
                    <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-chart-simple text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Reportes</h3>
                        </div>
                    </div>
                    <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-user text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Usuarios</h3>
                        </div>
                    </div>
                    <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-user text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Usuarios</h3>
                        </div>
                    </div>
                    <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-user text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Usuarios</h3>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php
    include '../layout/footer.php';
    ?>
</body>

</html>