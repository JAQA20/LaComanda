<?php
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";

verificarRol([1, 3]); // Admin(1) y Cocina(3)

$archivo = __DIR__ . "/../controller/ordenes.json";
$ordenes = file_exists($archivo)
    ? json_decode(file_get_contents($archivo), true)
    : [];

if (!is_array($ordenes)) {
    $ordenes = [];
}

function stepCircleClass($activo, $completado = false)
{
    if ($completado) return "bg-mint-green text-white";
    if ($activo) return "bg-brown-dark text-white";
    return "bg-gray-300 text-white";
}

function stepTextClass($activo, $completado = false)
{
    if ($completado || $activo) return "text-brown-dark font-semibold";
    return "text-gray-400";
}

function stepLineClass($completado = false)
{
    return $completado ? "bg-mint-green" : "bg-gray-300";
}

function obtenerPasoOrden($estado)
{
    switch ($estado) {
        case "pendiente":
            return 2; // En cocina
        case "lista":
            return 3; // Lista
        case "entregada":
            return 4; // Entregada
        default:
            return 1; // Pedido
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cocina - La Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous"></script>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/cocina.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brown-dark': '#362018',
                        'mint-green': '#70A38F',
                        'mint-hover': '#5B8F7A',
                        'beige-light': '#F5EDE1'
                    },
                    fontFamily: {
                        'montserrat': ['Montserrat', 'sans-serif']
                    }
                }
            }
        }
    </script>

</head>

<body class="bg-white font-montserrat">

    <nav class="w-full bg-brown-dark text-beige-light px-6 py-4 flex items-center justify-between shadow-md">
        <div class="text-xl font-semibold tracking-wide">
            <div class="flex items-center">
                <img class="h-10 w-10 object-contain mr-3" src="<?= BASE_URL ?>public/img/logotipo2.PNG" alt="elegant coffee shop logo with toscana text, warm brown and mint colors, minimalist design" />
                <span class="text-beige text-xl font-semibold">Cafetería Toscana</span>
            </div>
        </div>

        <div class="flex items-center space-x-6 text-beige-light">
            <button class="hover:text-mint-green transition-colors">
                <a href="<?= BASE_URL ?>public/api/logout.php" class="text-sm font-medium">
                    Salir
                </a>

            </button>
        </div>
    </nav>

    <div id="kitchen-main" class="flex h-screen">

        <!-- PANEL IZQUIERDO (ENTREGADAS) -->
        <div id="delivered-orders-panel" class="w-[30%] bg-beige-light p-6 overflow-y-auto">
            <div id="delivered-header" class="mb-6">
                <h2 class="text-2xl font-semibold text-brown-dark mb-2">Órdenes entregadas</h2>
                <p class="text-sm text-brown-dark opacity-70">Últimos 20 registros</p>
            </div>

            <div id="delivered-list" class="space-y-4">
                <?php
                // Filtrar solo entregadas y limitar a 20
                $entregadas = array_filter($ordenes, fn($o) => isset($o['estado']) && $o['estado'] === 'entregada');
                $entregadas = array_slice($entregadas, -20);

                foreach ($entregadas as $orden):
                ?>
                    <div class="delivered-card bg-white rounded-xl p-4 custom-shadow order-card">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-brown-dark">
                                Orden #<?php echo htmlspecialchars($orden["numero"]); ?>
                            </span>
                            <i class="fa-solid fa-check text-mint-green"></i>
                        </div>
                        <div class="text-xs text-brown-dark opacity-70">
                            <div>Mesa <?php echo htmlspecialchars($orden["mesa"]); ?></div>
                            <div><?php echo isset($orden["hora_entrega"]) ? $orden["hora_entrega"] : "--:--"; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PANEL DERECHO – ÓRDENES PENDIENTES -->
        <div id="pending-orders-panel" class="w-[70%] bg-white p-8 overflow-y-auto">

            <div id="pending-header" class="mb-8">
                <h1 class="text-3xl font-semibold text-brown-dark">Órdenes pendientes</h1>
            </div>

            <div id="pending-orders-grid" class="grid grid-cols-1 xl:grid-cols-2 gap-6">

                <?php foreach ($ordenes as $orden): ?>
                    <?php if (isset($orden["estado"]) && in_array($orden["estado"], ["pendiente", "lista"])): ?>

                        <div class="pending-order-card bg-white rounded-xl p-6 custom-shadow order-card border border-black-100">

                            <!-- Encabezado -->
                            <div class="flex items-center justify-between mb-4">
                                <?php $pasoActual = obtenerPasoOrden($orden["estado"] ?? "pendiente"); ?>

                                <div class="mb-6">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">

                                        <!-- Pedido -->
                                        <div class="flex flex-col items-center min-w-[70px]">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm <?= stepCircleClass($pasoActual === 1, $pasoActual > 1) ?>">
                                                <?= $pasoActual > 1 ? "✓" : "1" ?>
                                            </div>
                                            <span class="mt-2 text-xs text-center <?= stepTextClass($pasoActual === 1, $pasoActual > 1) ?>">
                                                Pedido
                                            </span>
                                        </div>

                                        <div class="flex-1 h-1 rounded <?= stepLineClass($pasoActual > 1) ?>"></div>

                                        <!-- En cocina -->
                                        <div class="flex flex-col items-center min-w-[70px]">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm <?= stepCircleClass($pasoActual === 2, $pasoActual > 2) ?>">
                                                <?= $pasoActual > 2 ? "✓" : "2" ?>
                                            </div>
                                            <span class="mt-2 text-xs text-center <?= stepTextClass($pasoActual === 2, $pasoActual > 2) ?>">
                                                En cocina
                                            </span>
                                        </div>

                                        <div class="flex-1 h-1 rounded <?= stepLineClass($pasoActual > 2) ?>"></div>

                                        <!-- Lista -->
                                        <div class="flex flex-col items-center min-w-[70px]">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm <?= stepCircleClass($pasoActual === 3, $pasoActual > 3) ?>">
                                                <?= $pasoActual > 3 ? "✓" : "3" ?>
                                            </div>
                                            <span class="mt-2 text-xs text-center <?= stepTextClass($pasoActual === 3, $pasoActual > 3) ?>">
                                                Lista
                                            </span>
                                        </div>

                                        <div class="flex-1 h-1 rounded <?= stepLineClass($pasoActual > 3) ?>"></div>

                                        <!-- Entregada -->
                                        <div class="flex flex-col items-center min-w-[70px]">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm <?= stepCircleClass($pasoActual === 4, false) ?>">
                                                <?= $pasoActual === 4 ? "✓" : "4" ?>
                                            </div>
                                            <span class="mt-2 text-xs text-center <?= stepTextClass($pasoActual === 4, false) ?>">
                                                Entregada
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-brown-dark">
                                        Orden #<?php echo htmlspecialchars($orden["numero"]); ?>
                                    </h3>
                                    <p class="text-sm text-brown-dark opacity-70">
                                        Mesa <?php echo htmlspecialchars($orden["mesa"]); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- LISTA DE PRODUCTOS -->
                            <div class="products-list mb-6">
                                <h4 class="text-sm font-medium text-brown-dark mb-3">Productos:</h4>

                                <div class="space-y-2">
                                    <?php
                                    $lineas = explode("\n", trim($orden["items"]));
                                    foreach ($lineas as $linea):
                                        $linea = trim($linea);
                                        if ($linea === "") continue;

                                        if (preg_match('/^(.*) x(\d+)$/', $linea, $match)) {
                                            $producto = $match[1];
                                            $cantidad = $match[2];
                                        } else {
                                            $producto = $linea;
                                            $cantidad = "1";
                                        }
                                    ?>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-brown-dark"><?php echo htmlspecialchars($producto); ?></span>
                                            <span class="text-brown-dark font-medium">x<?php echo $cantidad; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <?php if (isset($_SESSION["rol_id"]) && in_array((int)$_SESSION["rol_id"], [1, 3]) && $orden["estado"] === "pendiente"): ?>
                                <form action="<?= BASE_URL ?>public/api/marcarLista.php" method="POST">
                                    <input type="hidden" name="numero" value="<?php echo htmlspecialchars($orden['numero']); ?>">
                                    <button
                                        class="w-full bg-brown-dark hover:bg-[#4a2d22] text-white font-medium py-3 rounded-xl transition-colors duration-200">
                                        Marcar como lista
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if (isset($_SESSION["rol_id"]) && (int)$_SESSION["rol_id"] === 1 && $orden["estado"] === "lista"): ?>
                                <form action="<?= BASE_URL ?>public/api/marcarEntrega.php" method="POST">
                                    <input type="hidden" name="numero" value="<?php echo htmlspecialchars($orden['numero']); ?>">
                                    <button
                                        class="w-full bg-mint-green hover:bg-mint-hover text-white font-medium py-3 rounded-xl transition-colors duration-200">
                                        Marcar como entregada
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>

            </div>

        </div>
    </div>

    <!-- AUTO REFRESH -->
    <script>
        setInterval(() => {
            location.reload();
        }, 10000);
    </script>

</body>

</html>