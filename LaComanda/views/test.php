<?php
$archivo = __DIR__ . "/../controller/ordenes.json";
$ordenes = file_exists($archivo)
    ? json_decode(file_get_contents($archivo), true)
    : [];

if (!is_array($ordenes)) {
    $ordenes = [];
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
    <link rel="stylesheet" href="..//public/css/cocina.css">

    <style>
        ::-webkit-scrollbar {
            display: none;
        }

        body {
            font-family: 'Montserrat', sans-serif;
        }

        .custom-shadow {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .order-card {
            transition: all 0.2s ease-in-out;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }
    </style>

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
    <div id="kitchen-main" class="flex h-screen">

        <!-- PANEL IZQUIERDO (ENTREGADAS) -->
        <div id="delivered-orders-panel" class="w-[30%] bg-beige-light p-6 overflow-y-auto">
            <div id="delivered-header" class="mb-6">
                <h2 class="text-2xl font-semibold text-brown-dark mb-2">Órdenes entregadas</h2>
                <p class="text-sm text-brown-dark opacity-70">Últimos 20 registros</p>
            </div>

            <div id="delivered-list" class="space-y-4">
                <?php
                // Filtrar solo entregadas y opcionalmente limitar a 20
                $entregadas = array_filter($ordenes, fn($o) => isset($o['estado']) && $o['estado'] === 'entregada');
                // Si quieres limitar:
                // $entregadas = array_slice($entregadas, -20);

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
                    <?php if (isset($orden["estado"]) && $orden["estado"] === "pendiente"): ?>

                        <div class="pending-order-card bg-white rounded-xl p-6 custom-shadow order-card border border-gray-100">

                            <!-- Encabezado -->
                            <div class="flex items-center justify-between mb-4">
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

                            <!-- BOTÓN MARCAR COMO ENTREGADA (fuera del foreach de productos) -->
                            <form action="../controller/marcarEntrega.php" method="POST">
                                <input type="hidden" name="numero" value="<?php echo htmlspecialchars($orden['numero']); ?>">
                                <button
                                    class="w-full bg-mint-green hover:bg-mint-hover text-white font-medium py-3 rounded-xl transition-colors duration-200">
                                    Marcar como entregada
                                </button>
                            </form>

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
        }, 5000);
    </script>

</body>

</html>