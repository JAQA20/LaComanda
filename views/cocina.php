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
<html lang="es">

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
                <img class="h-10 w-10 object-contain mr-3" src="<?= BASE_URL ?>public/img/logotipo2.PNG" alt="Logo Cafetería Toscana" />
                <span class="text-beige-light text-xl font-semibold">Cafetería Toscana</span>
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
                            <div><?php echo isset($orden["hora_entrega"]) ? htmlspecialchars($orden["hora_entrega"]) : "--:--"; ?></div>
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

                                <div class="mb-6 w-full">
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
                            </div>

                            <div class="mb-4">
                                <h3 class="text-xl font-semibold text-brown-dark">
                                    Orden #<?php echo htmlspecialchars($orden["numero"]); ?>
                                </h3>
                                <p class="text-sm text-brown-dark opacity-70">
                                    Mesa <?php echo htmlspecialchars($orden["mesa"]); ?>
                                </p>
                            </div>

                            <!-- LISTA DE PRODUCTOS -->
                            <div class="products-list mb-6">
                                <h4 class="text-sm font-medium text-brown-dark mb-3">Productos:</h4>

                                <div class="space-y-2">
                                    <?php
                                    $lineas = explode("\n", trim($orden["items"] ?? ""));
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
                                            <span class="text-brown-dark font-medium">x<?php echo htmlspecialchars($cantidad); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- NOTAS ADICIONALES -->
                            <?php
                            $tieneNotas = isset($orden["notas"]) && !empty(trim($orden["notas"]));
                            ?>
                            <div class="notes-section mb-6 p-3 rounded-lg <?php echo $tieneNotas ? 'bg-yellow-50 border border-yellow-200' : 'bg-red-50 border border-red-200'; ?>">
                                <div class="flex items-start">
                                    <?php if ($tieneNotas): ?>
                                        <i class="fas fa-sticky-note text-yellow-600 mt-1 mr-2 flex-shrink-0"></i>
                                        <div class="flex-1">
                                            <h5 class="text-sm font-semibold text-yellow-900">Notas especiales:</h5>
                                            <p class="text-sm text-yellow-800 mt-1"><?php echo htmlspecialchars($orden["notas"]); ?></p>
                                        </div>
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle text-red-600 mt-1 mr-2 flex-shrink-0"></i>
                                        <div class="flex-1">
                                            <h5 class="text-sm font-semibold text-red-900">⚠ Sin notas especiales</h5>
                                            <p class="text-sm text-red-800 mt-1">Verificar con el cliente si hay requerimientos especiales</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- BOTÓN EXPANDIR PARA VER DETALLES COMPLETOS -->
                            <button
                                class="w-full mb-3 bg-brown-dark hover:bg-opacity-90 text-white font-medium py-2 rounded-xl transition-colors duration-200 flex items-center justify-center gap-2 expand-order-btn"
                                data-orden-numero="<?php echo htmlspecialchars($orden['numero']); ?>"
                                data-orden-mesa="<?php echo htmlspecialchars($orden['mesa']); ?>"
                                data-orden-items="<?php echo htmlspecialchars($orden['items'] ?? ''); ?>"
                                data-orden-notas="<?php echo htmlspecialchars($orden['notas'] ?? ''); ?>">
                                <i class="fas fa-expand"></i>
                                Ver detalles completos
                            </button>

                            <?php if (isset($_SESSION["rol_id"]) && in_array((int)$_SESSION["rol_id"], [1, 3]) && $orden["estado"] === "pendiente"): ?>
                                <form action="<?= BASE_URL ?>public/api/marcarLista.php" method="POST">
                                    <input type="hidden" name="numero" value="<?php echo htmlspecialchars($orden['numero']); ?>">
                                    <button
                                        type="submit"
                                        class="w-full bg-brown-dark hover:bg-[#4a2d22] text-white font-medium py-3 rounded-xl transition-colors duration-200">
                                        Marcar como lista
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if (isset($_SESSION["rol_id"]) && (int)$_SESSION["rol_id"] === 1 && $orden["estado"] === "lista"): ?>
                                <form action="<?= BASE_URL ?>public/api/marcarEntrega.php" method="POST">
                                    <input type="hidden" name="numero" value="<?php echo htmlspecialchars($orden['numero']); ?>">
                                    <button
                                        type="submit"
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

    <!-- MODAL PARA VER DETALLES COMPLETOS -->
    <div id="order-detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <!-- Encabezado del Modal -->
            <div class="sticky top-0 bg-brown-dark text-beige-light p-6 flex items-center justify-between z-10">
                <div>
                    <h2 class="text-2xl font-semibold" id="modal-orden-numero">Orden #</h2>
                    <p class="text-sm opacity-80" id="modal-orden-mesa">Mesa: </p>
                </div>
                <button id="close-modal-btn" class="text-beige-light hover:text-white text-2xl transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Contenido del Modal -->
            <div class="p-6">
                <!-- Productos Detallados -->
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-brown-dark mb-4 flex items-center">
                        <i class="fas fa-list mr-2"></i>
                        Productos a preparar
                    </h3>
                    <div id="modal-productos-list" class="space-y-3 bg-beige-light p-4 rounded-xl">
                        <!-- Se llenará con JavaScript -->
                    </div>
                </div>

                <!-- Notas Especiales -->
                <div id="modal-notas-section" class="mb-6">
                    <!-- Se llenará con JavaScript -->
                </div>

                <!-- Pie del Modal -->
                <div class="border-t pt-6">
                    <?php if (isset($_SESSION["rol_id"]) && (int)$_SESSION["rol_id"] === 1): ?>
                        <form id="deliver-form" action="<?= BASE_URL ?>public/api/marcarEntrega.php" method="POST" class="flex gap-3">
                            <input type="hidden" id="modal-orden-numero-input" name="numero">
                            <button type="submit" class="flex-1 bg-mint-green hover:bg-mint-hover text-white font-medium py-3 rounded-xl transition-colors duration-200">
                                <i class="fas fa-check mr-2"></i>
                                Marcar como entregada
                            </button>
                            <button type="button" id="close-modal-btn-bottom" class="flex-1 bg-gray-300 hover:bg-gray-400 text-brown-dark font-medium py-3 rounded-xl transition-colors duration-200">
                                Cerrar
                            </button>
                        </form>
                    <?php else: ?>
                        <button type="button" id="close-modal-btn-bottom" class="w-full bg-gray-300 hover:bg-gray-400 text-brown-dark font-medium py-3 rounded-xl transition-colors duration-200">
                            Cerrar
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function saveSelectedOrder(numeroOrden) {
            localStorage.setItem('selectedOrder', numeroOrden);
        }

        function getSelectedOrder() {
            return localStorage.getItem('selectedOrder');
        }

        function clearSelectedOrder() {
            localStorage.removeItem('selectedOrder');
        }

        function openOrderModal(numeroOrden, mesa, items, notas) {
            const modal = document.getElementById('order-detail-modal');

            document.getElementById('modal-orden-numero').textContent = `Orden #${numeroOrden}`;
            document.getElementById('modal-orden-mesa').textContent = `Mesa: ${mesa}`;

            const numeroInput = document.getElementById('modal-orden-numero-input');
            if (numeroInput) {
                numeroInput.value = numeroOrden;
            }

            const productosList = document.getElementById('modal-productos-list');
            productosList.innerHTML = '';

            const lineas = items.split('\n').filter(l => l.trim() !== '');
            lineas.forEach(linea => {
                linea = linea.trim();
                let producto, cantidad;

                const match = linea.match(/^(.*) x(\d+)$/);
                if (match) {
                    producto = match[1];
                    cantidad = match[2];
                } else {
                    producto = linea;
                    cantidad = '1';
                }

                const itemDiv = document.createElement('div');
                itemDiv.className = 'flex justify-between items-center py-2 px-3 bg-white rounded border border-beige-light';
                itemDiv.innerHTML = `
                    <span class="font-medium text-brown-dark">${producto}</span>
                    <span class="bg-mint-green text-white px-3 py-1 rounded-full font-semibold">x${cantidad}</span>
                `;
                productosList.appendChild(itemDiv);
            });

            const notasSection = document.getElementById('modal-notas-section');
            if (notas && notas.trim() !== '') {
                notasSection.innerHTML = `
                    <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-200">
                        <h3 class="text-lg font-semibold text-yellow-900 mb-2 flex items-center">
                            <i class="fas fa-sticky-note mr-2"></i>
                            Notas especiales
                        </h3>
                        <p class="text-yellow-800 whitespace-pre-wrap">${notas}</p>
                    </div>
                `;
            } else {
                notasSection.innerHTML = `
                    <div class="p-4 rounded-xl bg-orange-50 border border-orange-200">
                        <h3 class="text-lg font-semibold text-orange-900 mb-2 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            ⚠ Advertencia - Sin notas especiales
                        </h3>
                        <p class="text-orange-800">Verificar con el cliente si hay requerimientos especiales antes de preparar</p>
                    </div>
                `;
            }

            modal.classList.remove('hidden');
            saveSelectedOrder(numeroOrden);
        }

        function closeOrderModal() {
            const modal = document.getElementById('order-detail-modal');
            modal.classList.add('hidden');
        }

        document.querySelectorAll('.expand-order-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const numeroOrden = this.getAttribute('data-orden-numero');
                const mesa = this.getAttribute('data-orden-mesa');
                const items = this.getAttribute('data-orden-items');
                const notas = this.getAttribute('data-orden-notas');
                openOrderModal(numeroOrden, mesa, items, notas);
            });
        });

        document.querySelectorAll('#close-modal-btn, #close-modal-btn-bottom').forEach(btn => {
            btn.addEventListener('click', closeOrderModal);
        });

        document.getElementById('order-detail-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });

        setInterval(() => {
            location.reload();
        }, 10000);

        window.addEventListener('load', function() {
            const selectedOrderNo = getSelectedOrder();
            if (selectedOrderNo) {
                setTimeout(() => {
                    const expandBtn = document.querySelector(`[data-orden-numero="${selectedOrderNo}"]`);
                    if (expandBtn) {
                        expandBtn.click();
                    }
                }, 500);
            }
        });

        document.getElementById('deliver-form')?.addEventListener('submit', function() {
            clearSelectedOrder();
        });
    </script>
</body>

</html>