<?php
if (isset($_SESSION["usuario_id"])) {
    header("Location: " . BASE_URL . "views/index.php");
    exit;
}
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__ . '/../model/Conexion.php';
require_once __DIR__ . '/../controller/loginController.php';
require_once __DIR__ . '/../config/rutas.php';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/login.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-brand-beige min-h-screen">


    <!-- Main Login Container -->
    <main id="login-main" class="flex items-center justify-center min-h-[calc(100vh-80px)] px-4 coffee-pattern">
        <div id="login-container" class="w-full max-w-md">



            <!-- Login Card -->
            <div id="login-card" class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">

                <!-- Card Header -->
                <div id="card-header" class="text-center mb-8">
                    <div class="w-40 h-40 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <img class="h-40 w-40 rounded-full flex items-center" src="../public/img/logotipo1.PNG" alt="" />
                    </div>


                    <h2 class="text-2xl font-bold brand-brown mb-2">Iniciar Sesión</h2>
                    <p class="text-gray-600 text-sm">Accede al sistema La Comanda</p>
                </div>
                <?php if (isset($_GET["error"])): ?>
                    <div class="alert alert-danger fade show d-flex align-items-center rounded-4 border-0 shadow-sm mb-4 login-alert" role="alert">
                        <i class="fas fa-circle-exclamation me-2"></i>
                        <div>
                            <?php
                            if ($_GET["error"] === "credenciales") {
                                echo "Usuario o contraseña incorrectos";
                            } elseif ($_GET["error"] === "campos") {
                                echo "Debe completar todos los campos";
                            } elseif ($_GET["error"] === "rol") {
                                echo "El rol del usuario no es válido";
                            } elseif ($_GET["error"] === "inactivo") {
                                echo "Tu cuenta está inactiva. Contacta al administrador";
                            } else {
                                echo "Ocurrió un error al iniciar sesión";
                            }
                            ?>
                        </div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form id="login-form" class="space-y-6" method="POST" action="">

                    <!-- Username Field -->
                    <div id="username-field" class="space-y-2">
                        <label for="usuario" class="block text-sm font-medium brand-brown">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-green focus:border-brand-green transition-colors duration-200 bg-gray-50 focus:bg-white"
                                placeholder="Ingresa tu email">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div id="password-field" class="space-y-2">
                        <label for="password" class="block text-sm font-medium brand-brown">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-green focus:border-brand-green transition-colors duration-200 bg-gray-50 focus:bg-white"
                                placeholder="Ingresa tu contraseña">
                            <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <!-- <i class="fas fa-eye text-gray-400 hover:text-gray-600 transition-colors duration-200"></i> -->
                            </button>
                        </div>
                    </div>

                    <div id="remember-section" class="flex items-center justify-between">
                        <div class="flex items-center">

                        </div>
                        <div class="text-sm">
                            <a href="<?= BASE_URL ?>views/forgotPassword.php" class="font-medium text-brand-green hover:underline">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                    </div>

                    <!-- Login Button -->
                    <div id="login-button-section">
                        <button
                            name="btnlogin"
                            value="Submit"
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-brand-green hover-bg-brand-green focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-green transition-all duration-200 transform hover:scale-105">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Iniciar Sesión
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </main>



    <!-- Footer -->
    <?php
    require_once ROOT_PATH . '/views/layout/footer.php';
    ?>

    <script src="<?= BASE_URL ?>public/js/togglePassword.js"></script>
    <script src="<?= BASE_URL ?>public/js/session-sync.js"></script>
    <script>
        if (window.LaComandaSessionSync) {
            window.LaComandaSessionSync.clearLogout();
        }
    </script>
    <script>
        // Efectos de carga
        const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-brand-green');
            });

            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-brand-green');
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
