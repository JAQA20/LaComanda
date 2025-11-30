<?php
$archivo = __DIR__ . "/../controller/ordenes.json";
$ordenes = file_exists($archivo)
    ? json_decode(file_get_contents($archivo), true)
    : [];
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

        <!-- PANEL IZQUIERDO (ENTREGADAS – por ahora estático) -->
        <div id="delivered-orders-panel" class="w-[30%] bg-beige-light p-6 overflow-y-auto">
            <div id="delivered-header" class="mb-6">
                <h2 class="text-2xl font-semibold text-brown-dark mb-2">Órdenes entregadas</h2>
                <p class="text-sm text-brown-dark opacity-70">Últimos 20 registros</p>
            </div>

            <div id="delivered-list" class="space-y-4">
                <!-- Contenido estático por ahora -->
            </div>
        </div>

        <!-- PANEL DERECHO – ÓRDENES PENDIENTES -->
        <div id="pending-orders-panel" class="w-[70%] bg-white p-8 overflow-y-auto">

            <div id="pending-header" class="mb-8">
                <h1 class="text-3xl font-semibold text-brown-dark">Órdenes pendientes</h1>
            </div>

            <div id="pending-orders-grid" class="grid grid-cols-1 xl:grid-cols-2 gap-6">

                <?php foreach ($ordenes as $orden): ?>
                    <?php if ($orden["estado"] === "pendiente"): ?>

                        <div class="pending-order-card bg-white rounded-xl p-6 custom-shadow order-card border border-gray-100">

                            <!-- Encabezado -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-brown-dark">
                                        Orden #<?php echo $orden["numero"]; ?>
                                    </h3>

                                    <p class="text-sm text-brown-dark opacity-70">
                                        Mesa <?php echo htmlspecialchars($orden["mesa"]); ?>
                                    </p>
                                </div>

                                <!-- Tiempo dinámico opcional 
                                <div class="text-right">
                                    <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-sm font-medium">
                                        <?php
                                        $mins = floor((time() - $orden["timestamp"]) / 60);
                                        echo $mins . " min";
                                        ?>
                                    </span>
                                </div>
                                -->
                            </div>

                            <!-- LISTA DE PRODUCTOS -->
                            <div class="products-list mb-6">
                                <h4 class="text-sm font-medium text-brown-dark mb-3">Productos:</h4>

                                <div class="space-y-2">
                                    <?php
                                    // Convertir el texto plano en líneas
                                    $lineas = explode("\n", trim($orden["items"]));
                                    foreach ($lineas as $linea):
                                        $linea = trim($linea);
                                        if ($linea === "") continue;

                                        // Detectar formato “Producto x2”
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