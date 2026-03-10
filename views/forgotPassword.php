<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// ini_set('display_errors', 1);
// error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - La Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        .coffee-pattern {
            background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(180, 140, 100, 0.05) 10px, rgba(180, 140, 100, 0.05) 20px);
        }

        .brand-brown {
            color: #362018;
        }

        .brand-green {
            background-color: #70A38F;
        }

        .brand-beige {
            background-color: #F5EDE1;
        }
    </style>
</head>

<body class="bg-brand-beige min-h-screen">

    <main id="forgot-main" class="flex items-center justify-center min-h-[calc(100vh-80px)] px-4 coffee-pattern">
        <div id="forgot-container" class="w-full max-w-md">

            <!-- Forgot Card -->
            <div id="forgot-card" class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">

                <!-- Card Header -->
                <div id="card-header" class="text-center mb-8">
                    <div class="w-40 h-40 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <img class="h-40 w-40 rounded-full flex items-center" src="../public/img/logotipo1.PNG" alt="Logo" />
                    </div>
                    <h2 class="text-2xl font-bold brand-brown mb-2">Recuperar Contraseña</h2>
                    <p class="text-gray-600 text-sm">Ingresa tu email y te enviaremos un enlace para cambiar tu contraseña</p>
                </div>

                <!-- Forgot Form -->
                <form id="forgot-form" class="space-y-6" method="POST" action="../controller/forgot_passwordController.php">

                    <!-- Email Field -->
                    <div id="email-field" class="space-y-2">
                        <label for="email" class="block text-sm font-medium brand-brown">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-gray-50 focus:bg-white"
                                placeholder="tu@email.com"
                                required>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div id="submit-button-section">
                        <button
                            type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white brand-green focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                            <i class="fas fa-envelope-open mr-2"></i>
                            Enviar enlace de recuperación
                        </button>
                    </div>

                    <div class="text-center text-sm">
                        <a href="./login.php" class="text-green-600 hover:underline font-medium">
                            Volver al login
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        document.getElementById('forgot-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const btn = document.querySelector('button[type="submit"]');

            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
            btn.disabled = true;

            try {
                const response = await fetch('../controller/forgot_passwordController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'email=' + encodeURIComponent(email)
                });

                const data = await response.json();

                if (data.status === 'OK') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Solicitud procesada',
                        html: data.message + '<br><br><small style="color:#666;">Revisa tu bandeja de entrada y spam.</small>',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = './login.php';
                    });
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error al procesar solicitud'
                });

                btn.innerHTML = '<i class="fas fa-envelope-open mr-2"></i>Enviar enlace de recuperación';
                btn.disabled = false;
            }
        });
    </script>

</body>

</html>