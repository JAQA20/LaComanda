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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style.css">

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
                                <i class="fas fa-user text-white text-xl"></i>
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