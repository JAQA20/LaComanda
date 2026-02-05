<?php
// No cachear esta página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . "../model/Conexion.php";

$ordenes = [];

// Pendientes - SOLO BEBIDAS (tipo_preparacion='barista')
$stmt = $conexion->prepare("
    SELECT DISTINCT o.id, o.mesa_id, o.id_estado, o.creado_en 
    FROM ordenes o
    JOIN orden_items oi ON o.id = oi.orden_id
    JOIN productos p ON oi.producto_id = p.id
    WHERE o.id_estado = 1 AND p.tipo_preparacion = 'barista'
    ORDER BY o.creado_en ASC
");
$stmt->execute();
$pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pendientes as $o) {
    $items = [];
    // Solo traer items que sean bebidas (barista)
    $s2 = $conexion->prepare("
        SELECT oi.cantidad, oi.precio_unitario, p.nombre 
        FROM orden_items oi 
        JOIN productos p ON oi.producto_id = p.id 
        WHERE oi.orden_id = :id AND p.tipo_preparacion = 'barista'
    ");
    $s2->execute([':id' => $o['id']]);
    $rows = $s2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $items[] = ['producto_id' => $r['nombre'], 'cantidad' => $r['cantidad'], 'precio' => $r['precio_unitario']];
    }

    if (count($items) > 0) {
        $ordenes[] = [
            'id_orden' => $o['id'],
            'mesa_id' => $o['mesa_id'],
            'items' => $items,
            'estado' => 'pendiente',
            'creado_en' => $o['creado_en']
        ];
    }
}

// Entregadas (últimas 20) - SOLO BEBIDAS
$stmt = $conexion->prepare("
    SELECT DISTINCT o.id, o.mesa_id, o.id_estado, o.hora_entrega 
    FROM ordenes o
    JOIN orden_items oi ON o.id = oi.orden_id
    JOIN productos p ON oi.producto_id = p.id
    WHERE o.id_estado = 4 AND p.tipo_preparacion = 'barista'
    ORDER BY o.hora_entrega DESC LIMIT 20
");
$stmt->execute();
$entregadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($entregadas as $o) {
    $items = [];
    $s2 = $conexion->prepare("
        SELECT oi.cantidad, oi.precio_unitario, p.nombre 
        FROM orden_items oi 
        JOIN productos p ON oi.producto_id = p.id 
        WHERE oi.orden_id = :id AND p.tipo_preparacion = 'barista'
    ");
    $s2->execute([':id' => $o['id']]);
    $rows = $s2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        $items[] = ['producto_id' => $r['nombre'], 'cantidad' => $r['cantidad'], 'precio' => $r['precio_unitario']];
    }

    if (count($items) > 0) {
        $ordenes[] = [
            'id_orden' => $o['id'],
            'mesa_id' => $o['mesa_id'],
            'items' => $items,
            'estado' => 'entregada',
            'hora_entrega' => $o['hora_entrega']
        ];
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barista - La Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous"></script>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/cocina.css">
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
                <img class="h-10 w-10 object-contain mr-3" src="../public/img/logotipo2.PNG" alt="elegant coffee shop logo with toscana text, warm brown and mint colors, minimalist design" />
                <span class="text-beige text-xl font-semibold">☕ Barista - Toscana</span>
            </div>
        </div>

        <div class="flex items-center space-x-6 text-beige-light">
            <button class="hover:text-mint-green transition-colors">
                <a href="../views/login.php">
                    Salir
                </a>
            </button>
        </div>
    </nav>

    <div id="barista-main" class="flex h-screen">

        <!-- PANEL IZQUIERDO (ENTREGADAS) -->
        <div id="delivered-orders-panel" class="w-[30%] bg-beige-light p-6 overflow-y-auto">
            <div id="delivered-header" class="mb-6">
                <h2 class="text-2xl font-semibold text-brown-dark mb-2">Bebidas listas</h2>
                <p class="text-sm text-brown-dark opacity-70">Últimos 20 registros</p>
            </div>

            <div id="delivered-list" class="space-y-4">
                <?php
                $entregadas_filtered = array_filter($ordenes, fn($o) => isset($o['estado']) && $o['estado'] === 'entregada');
                $entregadas_filtered = array_slice($entregadas_filtered, -20);

                foreach ($entregadas_filtered as $orden):
                ?>
                    <div class="delivered-card bg-white rounded-xl p-4 custom-shadow order-card">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-brown-dark">
                                Orden #<?php echo htmlspecialchars($orden["id_orden"] ?? "N/A"); ?>
                            </span>
                            <i class="fa-solid fa-check text-mint-green"></i>
                        </div>
                        <div class="text-xs text-brown-dark opacity-70">
                            <div>Mesa <?php echo htmlspecialchars($orden["mesa_id"] ?? "N/A"); ?></div>
                            <div><?php echo isset($orden["hora_entrega"]) ? date('H:i', strtotime($orden["hora_entrega"])) : "--:--"; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PANEL DERECHO – BEBIDAS PENDIENTES -->
        <div id="pending-orders-panel" class="w-[70%] bg-white p-8 overflow-y-auto">

            <div id="pending-header" class="mb-8">
                <h1 class="text-3xl font-semibold text-brown-dark">Bebidas pendientes</h1>
            </div>

            <div id="pending-orders-grid" class="grid grid-cols-1 xl:grid-cols-2 gap-6">

                <?php foreach ($ordenes as $orden): ?>
                    <?php if (isset($orden["estado"]) && $orden["estado"] === "pendiente"): ?>

                        <div class="pending-order-card bg-white rounded-xl p-6 custom-shadow order-card border border-gray-100">

                            <!-- Encabezado -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-semibold text-brown-dark">
                                        Orden #<?php echo htmlspecialchars($orden["id_orden"] ?? "N/A"); ?>
                                    </h3>
                                    <p class="text-sm text-brown-dark opacity-70">
                                        Mesa <?php echo htmlspecialchars($orden["mesa_id"] ?? "N/A"); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- LISTA DE BEBIDAS -->
                            <div class="products-list mb-6">
                                <h4 class="text-sm font-medium text-brown-dark mb-3">Bebidas:</h4>

                                <div class="space-y-2">
                                    <?php
                                    $items = $orden["items"] ?? [];
                                    foreach ($items as $item):
                                        $cantidad = isset($item['cantidad']) ? $item['cantidad'] : 1;
                                        $nombre = isset($item['producto_id']) ? $item['producto_id'] : 'Desconocida';
                                    ?>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-brown-dark"><?php echo htmlspecialchars($nombre); ?></span>
                                            <span class="text-brown-dark font-medium">x<?php echo $cantidad; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- BOTÓN MARCAR COMO LISTA -->
                            <form action="../controller/marcarEntrega.php" method="POST">
                                <input type="hidden" name="id_orden" value="<?php echo htmlspecialchars($orden['id_orden'] ?? ''); ?>">
                                <button type="submit"
                                    class="w-full bg-mint-green hover:bg-mint-hover text-white font-medium py-3 rounded-xl transition-colors duration-200">
                                    ✓ Bebidas listas
                                </button>
                            </form>

                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>

            </div>

        </div>
    </div>

    <!-- AUTO REFRESH -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let refreshInterval;

        document.addEventListener('submit', async function(e) {
            if (!e.target.action.includes('marcarEntrega.php')) return;

            e.preventDefault();

            clearInterval(refreshInterval);

            const form = e.target;
            const id_orden = form.querySelector('input[name="id_orden"]').value;
            const btn = form.querySelector('button[type="submit"]');
            const card = form.closest('.pending-order-card');

            btn.disabled = true;
            btn.textContent = 'Procesando...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });

                const data = await response.json();

                if (data.status === 'OK') {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';

                    await Swal.fire({
                        icon: 'success',
                        title: 'Bebida lista',
                        text: 'Orden #' + id_orden + ' marcada como lista',
                        timer: 1000,
                        showConfirmButton: false
                    });

                    setTimeout(() => {
                        location.href = location.href.split('?')[0] + '?t=' + Date.now();
                    }, 500);
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            } catch (error) {
                btn.disabled = false;
                btn.textContent = '✓ Bebidas listas';

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error al marcar bebida como lista'
                });

                startAutoRefresh();
            }
        });

        function startAutoRefresh() {
            refreshInterval = setInterval(() => {
                location.href = location.href.split('?')[0] + '?t=' + Date.now();
            }, 5000);
        }

        startAutoRefresh();
    </script>

</body>

</html>