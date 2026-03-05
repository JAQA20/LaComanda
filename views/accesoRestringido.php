<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <title>No autorizado</title>
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-50 p-6">
    <div class="bg-white p-8 rounded-2xl shadow-lg max-w-md w-full">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Acceso restringido</h1>
        <p class="text-gray-600 mb-6">No tienes permisos para acceder a esta sección.</p>

        <div class="flex gap-3">
            <!-- <a href="./index.php" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-semibold">Ir al inicio</a> -->
            <a href="../controller/logoutController.php" class="px-4 py-2 rounded-xl bg-gray-200 text-gray-800 font-semibold">Cerrar sesión</a>
        </div>
    </div>
</body>

</html>

<!-- <!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Acceso denegado</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-red-50 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-lg text-center">
        <h1 class="text-2xl font-bold text-red-600">🚫 Acceso denegado</h1>
        <p class="mt-4 text-gray-600">
            No tienes permisos para acceder a esta sección.
        </p>
        <a href="dashboard.php" class="mt-6 inline-block bg-red-600 text-white px-6 py-2 rounded-lg">
            Volver
        </a>
    </div>
</body>

</html> -->