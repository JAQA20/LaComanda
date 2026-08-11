<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . "/../config/env.php";
app_configure_errors();
require_once __DIR__ . "/../config/rutas.php";
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
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/login.css">
</head>

<body class="bg-brand-beige min-h-screen">

    <main id="forgot-main" class="flex items-center justify-center min-h-[calc(100vh-80px)] px-4 coffee-pattern">
        <div id="forgot-container" class="w-full max-w-md">
            <div id="forgot-card" class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
                <div id="card-header" class="text-center mb-8">
                    <div class="w-40 h-40 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <img class="h-40 w-40 rounded-full flex items-center" src="<?= BASE_URL ?>public/img/logotipo1.PNG" alt="Logo" />
                    </div>

                    <h2 class="text-2xl font-bold brand-brown mb-2">Recuperar contraseña</h2>
                    <p class="text-gray-600 text-sm">Te enviaremos instrucciones para restablecer tu acceso</p>
                </div>

                <form id="forgot-form" class="space-y-6" method="POST" action="<?= BASE_URL ?>public/api/forgotPassword.php">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Correo electrónico</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            class="w-full px-4 py-3 rounded-2xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="ejemplo@correo.com">
                    </div>

                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-[#70A38F] hover:bg-[#5B8F7A] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#70A38F] transition-all duration-200 transform hover:scale-105">
                        Enviar enlace
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="<?= BASE_URL ?>views/login.php" class="font-medium text-brand-green hover:underline">
                        Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        const form = document.getElementById('forgot-form');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'Enviando...';

            try {
                const response = await fetch('<?= BASE_URL ?>public/api/forgotPassword.php', {
                    method: 'POST',
                    body: new FormData(form)
                });

                const data = await response.json();

                await Swal.fire({
                    icon: data.status === 'OK' ? 'success' : 'error',
                    title: data.status === 'OK' ? 'Listo' : 'Error',
                    text: data.message || 'No se pudo procesar la solicitud'
                });

                if (data.status === 'OK') {
                    window.location.href = '<?= BASE_URL ?>views/login.php';
                }
            } catch (error) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Ocurrió un error inesperado'
                });
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Enviar enlace';
            }
        });
    </script>

</body>

</html>
