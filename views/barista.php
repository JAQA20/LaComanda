<?php
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/Barista.php";

verificarRol([1, 4]); // Admin(1) y Barista(4)

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header("Content-Type: text/html; charset=UTF-8");

// La vista de barista ahora replica la estructura visual de cocina,
// pero mantiene su propio flujo y endpoints separados.
$baristaData = Barista::obtenerPanel();
$bebidasPendientes = $baristaData['pendientes'];
$bebidasEntregadas = $baristaData['listas'];
$ordenes = array_merge($bebidasPendientes, $bebidasEntregadas);

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
            return 1;
        case "en_preparacion":
            return 2;
        case "lista":
            return 3;
        case "entregada":
            return 4;
        default:
            return 1;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barista - La Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = { autoReplaceSvg: 'nest' };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <a href="<?= BASE_URL ?>public/api/logout.php" class="text-sm font-medium">Salir</a>
            </button>
        </div>
    </nav>

    <div id="kitchen-main" class="flex h-screen">
        <div id="delivered-orders-panel" class="w-[30%] bg-beige-light p-6 overflow-y-auto">
            <div id="delivered-header" class="mb-6">
                <h2 class="text-2xl font-semibold text-brown-dark mb-2">Órdenes entregadas</h2>
                <p class="text-sm text-brown-dark opacity-70">Últimos 20 registros</p>
            </div>
            <div id="delivered-list" class="space-y-4">
                <?php foreach ($bebidasEntregadas as $orden): ?>
                    <div class="delivered-card bg-white rounded-xl p-4 custom-shadow order-card cursor-pointer hover:bg-gray-50 transition-colors expand-order-btn" data-orden-numero="<?= htmlspecialchars((string)$orden['numero']) ?>">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-brown-dark">Orden #<?= htmlspecialchars((string)$orden['numero']) ?></span>
                            <i class="fa-solid fa-check text-mint-green"></i>
                        </div>
                        <div class="text-xs text-brown-dark opacity-70">
                            <div>Mesa <?= htmlspecialchars((string)$orden['mesa']) ?></div>
                            <div><?= htmlspecialchars((string)($orden['hora_lista'] ?? $orden['hora_entrega'] ?? '--:--')) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="pending-orders-panel" class="w-[70%] bg-white p-8 overflow-y-auto">
            <div id="pending-header" class="mb-8">
                <h1 class="text-3xl font-semibold text-brown-dark">Bebidas pendientes</h1>
            </div>

            <div id="pending-orders-grid" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <?php if (empty($bebidasPendientes)): ?>
                    <div class="xl:col-span-2">
                        <div class="kitchen-empty-state custom-shadow">
                            <div class="kitchen-empty-icon-wrap"><div class="kitchen-empty-icon"><i class="fa-solid fa-mug-hot"></i></div></div>
                            <h3 class="kitchen-empty-title">No hay bebidas nuevas</h3>
                            <p class="kitchen-empty-text">Todo al día en barista. Cuando entre una nueva bebida, aparecerá aquí automáticamente.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php foreach ($bebidasPendientes as $orden): ?>
                    <div class="pending-order-card bg-white rounded-xl p-6 custom-shadow order-card border border-black-100">
                        <?php if (($orden['estado'] ?? '') === 'lista'): ?>
                            <div class="bg-blue-50 text-blue-800 text-sm font-semibold px-4 py-2 rounded-lg mb-4 flex items-center gap-2">
                                <i class="fas fa-clock"></i> Esperando para su entrega
                            </div>
                        <?php elseif (($orden['estado'] ?? '') === 'en_preparacion'): ?>
                            <div class="bg-orange-50 text-orange-800 text-sm font-semibold px-4 py-2 rounded-lg mb-4 flex items-center gap-2">
                                <i class="fas fa-fire"></i> En preparación
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center justify-between mb-4">
                            <?php $pasoActual = obtenerPasoOrden($orden['estado'] ?? 'pendiente'); ?>
                            <div class="mb-6 w-full">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <div class="flex flex-col items-center min-w-[70px]"><div class="w-8 h-8 rounded-full flex items-center justify-center text-sm <?= stepCircleClass($pasoActual === 1, $pasoActual > 1) ?>"><?= $pasoActual > 1 ? '✓' : '1' ?></div><span class="mt-2 text-xs text-center <?= stepTextClass($pasoActual === 1, $pasoActual > 1) ?>">Pedido</span></div>
                                    <div class="flex-1 h-1 rounded <?= stepLineClass($pasoActual > 1) ?>"></div>
                                    <div class="flex flex-col items-center min-w-[70px]"><div class="w-8 h-8 rounded-full flex items-center justify-center text-sm <?= stepCircleClass($pasoActual === 2, $pasoActual > 2) ?>"><?= $pasoActual > 2 ? '✓' : '2' ?></div><span class="mt-2 text-xs text-center <?= stepTextClass($pasoActual === 2, $pasoActual > 2) ?>">En barista</span></div>
                                    <div class="flex-1 h-1 rounded <?= stepLineClass($pasoActual > 2) ?>"></div>
                                    <div class="flex flex-col items-center min-w-[70px]"><div class="w-8 h-8 rounded-full flex items-center justify-center text-sm <?= stepCircleClass($pasoActual === 3, $pasoActual > 3) ?>"><?= $pasoActual > 3 ? '✓' : '3' ?></div><span class="mt-2 text-xs text-center <?= stepTextClass($pasoActual === 3, $pasoActual > 3) ?>">Lista</span></div>
                                    <div class="flex-1 h-1 rounded <?= stepLineClass($pasoActual > 3) ?>"></div>
                                    <div class="flex flex-col items-center min-w-[70px]"><div class="w-8 h-8 rounded-full flex items-center justify-center text-sm <?= stepCircleClass($pasoActual === 4, false) ?>"><?= $pasoActual === 4 ? '✓' : '4' ?></div><span class="mt-2 text-xs text-center <?= stepTextClass($pasoActual === 4, false) ?>">Entregada</span></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h3 class="text-xl font-semibold text-brown-dark">Orden #<?= htmlspecialchars((string)$orden['numero']) ?></h3>
                            <p class="text-sm text-brown-dark opacity-70">Mesa <?= htmlspecialchars((string)$orden['mesa']) ?></p>
                        </div>

                        <div class="products-list mb-6">
                            <h4 class="text-sm font-medium text-brown-dark mb-3">Bebidas:</h4>
                            <div class="space-y-2">
                                <?php foreach (($orden['items'] ?? []) as $item): ?>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-brown-dark"><?= htmlspecialchars((string)$item['nombre']) ?></span>
                                        <span class="text-brown-dark font-medium">x<?= htmlspecialchars((string)$item['cantidad']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php $tieneNotas = !empty(trim((string)($orden['notas'] ?? ''))); ?>
                        <div class="notes-section mb-6 p-3 rounded-lg <?= $tieneNotas ? 'bg-yellow-50 border border-yellow-200' : 'bg-red-50 border border-red-200' ?>">
                            <div class="flex items-start">
                                <?php if ($tieneNotas): ?>
                                    <i class="fas fa-sticky-note text-yellow-600 mt-1 mr-2 flex-shrink-0"></i>
                                    <div class="flex-1"><h5 class="text-sm font-semibold text-yellow-900">Notas especiales:</h5><p class="text-sm text-yellow-800 mt-1"><?= htmlspecialchars((string)$orden['notas']) ?></p></div>
                                <?php else: ?>
                                    <i class="fas fa-exclamation-triangle text-red-600 mt-1 mr-2 flex-shrink-0"></i>
                                    <div class="flex-1"><h5 class="text-sm font-semibold text-red-900">⚠ Sin notas especiales</h5><p class="text-sm text-red-800 mt-1">Verificar si el cliente dejó instrucciones para sus bebidas</p></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button class="w-full mb-3 bg-brown-dark hover:bg-opacity-90 text-white font-medium py-2 rounded-xl transition-colors duration-200 flex items-center justify-center gap-2 expand-order-btn" data-orden-numero="<?= htmlspecialchars((string)$orden['numero']) ?>"><i class="fas fa-expand"></i>Ver detalles completos</button>

                        <?php if (($orden['estado'] ?? '') === 'pendiente'): ?>
                            <form action="<?= BASE_URL ?>public/api/baristaAccion.php" method="POST">
                                <input type="hidden" name="numero" value="<?= htmlspecialchars((string)$orden['numero']) ?>">
                                <input type="hidden" name="accion" value="preparacion">
                                <button type="submit" class="w-full bg-brown-dark hover:bg-[#4a2d22] text-white font-medium py-3 rounded-xl transition-colors duration-200">Marcar en preparación</button>
                            </form>
                        <?php elseif (($orden['estado'] ?? '') === 'en_preparacion'): ?>
                            <form action="<?= BASE_URL ?>public/api/baristaAccion.php" method="POST">
                                <input type="hidden" name="numero" value="<?= htmlspecialchars((string)$orden['numero']) ?>">
                                <input type="hidden" name="accion" value="lista">
                                <button type="submit" class="w-full bg-mint-green hover:bg-mint-hover text-white font-medium py-3 rounded-xl transition-colors duration-200">Marcar como lista</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="order-detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="sticky top-0 bg-brown-dark text-beige-light p-6 flex items-center justify-between z-10">
                <div>
                    <div class="flex items-center">
                        <h2 class="text-2xl font-semibold" id="modal-orden-numero">Orden #</h2>
                        <span id="modal-orden-estado" class="ml-3 text-xs px-2 py-1 rounded text-white font-medium"></span>
                    </div>
                    <p class="text-sm opacity-80 mt-1" id="modal-orden-mesa">Mesa: </p>
                </div>
                <button id="close-modal-btn" class="text-beige-light hover:text-white text-2xl transition-colors"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-brown-dark mb-4 flex items-center"><i class="fas fa-mug-hot mr-2"></i>Bebidas a preparar</h3>
                    <div id="modal-productos-list" class="space-y-3 bg-beige-light p-4 rounded-xl"></div>
                </div>
                <div id="modal-notas-section" class="mb-6"></div>
                <div class="border-t pt-6">
                    <button type="button" id="close-modal-btn-bottom" class="w-full bg-gray-300 hover:bg-gray-400 text-brown-dark font-medium py-3 rounded-xl transition-colors duration-200">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let modalOpen = false;
        let selectedOrderNumber = null;
        let currentPendientes = <?= json_encode($bebidasPendientes, JSON_UNESCAPED_UNICODE) ?>;
        let currentEntregadas = <?= json_encode($bebidasEntregadas, JSON_UNESCAPED_UNICODE) ?>;
        let refreshInterval = null;

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function obtenerPasoOrdenFrontend(estado) {
            switch (estado) {
                case 'pendiente': return 1;
                case 'en_preparacion': return 2;
                case 'lista': return 3;
                case 'entregada': return 4;
                default: return 1;
            }
        }

        function stepCircleClassJs(activo, completado = false) {
            if (completado) return "bg-mint-green text-white";
            if (activo) return "bg-brown-dark text-white";
            return "bg-gray-300 text-white";
        }

        function stepTextClassJs(activo, completado = false) {
            if (completado || activo) return "text-brown-dark font-semibold";
            return "text-gray-400";
        }

        function stepLineClassJs(completado = false) {
            return completado ? "bg-mint-green" : "bg-gray-300";
        }

        function renderListas(ordenes) {
            const container = document.getElementById('delivered-list');
            container.innerHTML = ordenes.map(orden => `
                <div class="delivered-card bg-white rounded-xl p-4 custom-shadow order-card cursor-pointer hover:bg-gray-50 transition-colors expand-order-btn" data-orden-numero="${escapeHtml(orden.numero)}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-brown-dark">Orden #${escapeHtml(orden.numero)}</span>
                        <i class="fa-solid fa-check text-mint-green"></i>
                    </div>
                    <div class="text-xs text-brown-dark opacity-70">
                        <div>Mesa ${escapeHtml(orden.mesa)}</div>
                        <div>${escapeHtml(orden.hora_lista || orden.hora_entrega || '--:--')}</div>
                    </div>
                </div>
            `).join('');
        }

        function renderNotas(notas) {
            const tieneNotas = notas && notas.trim() !== '';
            if (tieneNotas) {
                return `<div class="notes-section mb-6 p-3 rounded-lg bg-yellow-50 border border-yellow-200"><div class="flex items-start"><i class="fas fa-sticky-note text-yellow-600 mt-1 mr-2 flex-shrink-0"></i><div class="flex-1"><h5 class="text-sm font-semibold text-yellow-900">Notas especiales:</h5><p class="text-sm text-yellow-800 mt-1">${escapeHtml(notas)}</p></div></div></div>`;
            }
            return `<div class="notes-section mb-6 p-3 rounded-lg bg-red-50 border border-red-200"><div class="flex items-start"><i class="fas fa-exclamation-triangle text-red-600 mt-1 mr-2 flex-shrink-0"></i><div class="flex-1"><h5 class="text-sm font-semibold text-red-900">⚠ Sin notas especiales</h5><p class="text-sm text-red-800 mt-1">Verificar si el cliente dejó instrucciones para sus bebidas</p></div></div></div>`;
        }

        function renderPendientes(ordenes) {
            const grid = document.getElementById('pending-orders-grid');
            if (!ordenes.length) {
                grid.innerHTML = `<div class="xl:col-span-2"><div class="kitchen-empty-state custom-shadow"><div class="kitchen-empty-icon-wrap"><div class="kitchen-empty-icon"><i class="fa-solid fa-mug-hot"></i></div></div><h3 class="kitchen-empty-title">No hay bebidas nuevas</h3><p class="kitchen-empty-text">Todo al día en barista. Cuando entre una nueva bebida, aparecerá aquí automáticamente.</p></div></div>`;
                return;
            }

            grid.innerHTML = ordenes.map(orden => {
                const pasoActual = obtenerPasoOrdenFrontend(orden.estado || 'pendiente');
                return `
                    <div class="pending-order-card bg-white rounded-xl p-6 custom-shadow order-card border border-black-100">
                        ${orden.estado === 'lista' ? '<div class="bg-blue-50 text-blue-800 text-sm font-semibold px-4 py-2 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-clock"></i> Esperando para su entrega</div>' : ''}
                        ${orden.estado === 'en_preparacion' ? '<div class="bg-orange-50 text-orange-800 text-sm font-semibold px-4 py-2 rounded-lg mb-4 flex items-center gap-2"><i class="fas fa-fire"></i> En preparación</div>' : ''}
                        <div class="flex items-center justify-between mb-4"><div class="mb-6 w-full"><div class="flex items-center justify-between gap-2 flex-wrap">
                            <div class="flex flex-col items-center min-w-[70px]"><div class="w-8 h-8 rounded-full flex items-center justify-center text-sm ${stepCircleClassJs(pasoActual === 1, pasoActual > 1)}">${pasoActual > 1 ? '✓' : '1'}</div><span class="mt-2 text-xs text-center ${stepTextClassJs(pasoActual === 1, pasoActual > 1)}">Pedido</span></div>
                            <div class="flex-1 h-1 rounded ${stepLineClassJs(pasoActual > 1)}"></div>
                            <div class="flex flex-col items-center min-w-[70px]"><div class="w-8 h-8 rounded-full flex items-center justify-center text-sm ${stepCircleClassJs(pasoActual === 2, pasoActual > 2)}">${pasoActual > 2 ? '✓' : '2'}</div><span class="mt-2 text-xs text-center ${stepTextClassJs(pasoActual === 2, pasoActual > 2)}">En barista</span></div>
                            <div class="flex-1 h-1 rounded ${stepLineClassJs(pasoActual > 2)}"></div>
                            <div class="flex flex-col items-center min-w-[70px]"><div class="w-8 h-8 rounded-full flex items-center justify-center text-sm ${stepCircleClassJs(pasoActual === 3, pasoActual > 3)}">${pasoActual > 3 ? '✓' : '3'}</div><span class="mt-2 text-xs text-center ${stepTextClassJs(pasoActual === 3, pasoActual > 3)}">Lista</span></div>
                            <div class="flex-1 h-1 rounded ${stepLineClassJs(pasoActual > 3)}"></div>
                            <div class="flex flex-col items-center min-w-[70px]"><div class="w-8 h-8 rounded-full flex items-center justify-center text-sm ${stepCircleClassJs(pasoActual === 4, false)}">${pasoActual === 4 ? '✓' : '4'}</div><span class="mt-2 text-xs text-center ${stepTextClassJs(pasoActual === 4, false)}">Entregada</span></div>
                        </div></div></div>
                        <div class="mb-4"><h3 class="text-xl font-semibold text-brown-dark">Orden #${escapeHtml(orden.numero)}</h3><p class="text-sm text-brown-dark opacity-70">Mesa ${escapeHtml(orden.mesa)}</p></div>
                        <div class="products-list mb-6"><h4 class="text-sm font-medium text-brown-dark mb-3">Bebidas:</h4><div class="space-y-2 border-t border-gray-100 pt-2">${(orden.items || []).map(item => {
                            let opcHtml = '';
                            if (item.opciones_json && Array.isArray(item.opciones_json) && item.opciones_json.length > 0) {
                                opcHtml = `<div class="text-sm text-mint-green leading-tight mt-1">` + item.opciones_json.map(o => `+${escapeHtml(o.nombre)}`).join(', ') + `</div>`;
                            }
                            let notHtml = '';
                            if (item.notas_item && item.notas_item.trim() !== '') {
                                notHtml = `<div class="text-sm text-yellow-600 leading-tight italic mt-1">Nota: ${escapeHtml(item.notas_item)}</div>`;
                            }
                            return `<div class="flex justify-between items-start text-sm border-b border-gray-50 pb-1 mb-1"><div class="flex-1"><span class="text-brown-dark">${escapeHtml(item.nombre)}</span>${opcHtml}${notHtml}</div><span class="text-brown-dark font-medium ml-2">x${escapeHtml(item.cantidad)}</span></div>`;
                        }).join('')}</div></div>
                        ${renderNotas(orden.notas || '')}
                        <button class="w-full mb-3 bg-brown-dark hover:bg-opacity-90 text-white font-medium py-2 rounded-xl transition-colors duration-200 flex items-center justify-center gap-2 expand-order-btn" data-orden-numero="${escapeHtml(orden.numero)}"><i class="fas fa-expand"></i>Ver detalles completos</button>
                        ${orden.estado === 'pendiente' ? `<form action="<?= BASE_URL ?>public/api/baristaAccion.php" method="POST"><input type="hidden" name="numero" value="${escapeHtml(orden.numero)}"><input type="hidden" name="accion" value="preparacion"><button type="submit" class="w-full bg-brown-dark hover:bg-[#4a2d22] text-white font-medium py-3 rounded-xl transition-colors duration-200">Marcar en preparación</button></form>` : ''}
                        ${orden.estado === 'en_preparacion' ? `<form action="<?= BASE_URL ?>public/api/baristaAccion.php" method="POST"><input type="hidden" name="numero" value="${escapeHtml(orden.numero)}"><input type="hidden" name="accion" value="lista"><button type="submit" class="w-full bg-mint-green hover:bg-mint-hover text-white font-medium py-3 rounded-xl transition-colors duration-200">Marcar como lista</button></form>` : ''}
                    </div>`;
            }).join('');
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.expand-order-btn');
            if (btn) {
                const numeroOrden = btn.getAttribute('data-orden-numero');
                const orden = [...currentPendientes, ...currentEntregadas].find(o => String(o.numero) === String(numeroOrden));
                if (orden) {
                    openOrderModal(orden.numero, orden.mesa, orden.items || [], orden.notas || '', orden.estado);
                }
            }
        });

        function openOrderModal(numeroOrden, mesa, itemsDetalle, notas, estado = 'pendiente') {
            const modal = document.getElementById('order-detail-modal');
            modalOpen = true;
            selectedOrderNumber = numeroOrden;
            document.getElementById('modal-orden-numero').textContent = `Orden #${numeroOrden}`;
            document.getElementById('modal-orden-mesa').textContent = `Mesa: ${mesa}`;

            const estadoSpan = document.getElementById('modal-orden-estado');
            let estadoFormat = 'Pendiente';
            let bgClass = 'bg-gray-500';
            if (estado === 'en_preparacion') { estadoFormat = 'En preparación'; bgClass = 'bg-orange-500'; }
            else if (estado === 'lista') { estadoFormat = 'Lista'; bgClass = 'bg-blue-500'; }
            else if (estado === 'entregada') { estadoFormat = 'Entregada'; bgClass = 'bg-mint-green'; }
            
            estadoSpan.className = `ml-3 text-xs px-2 py-1 rounded text-white font-medium ${bgClass}`;
            estadoSpan.textContent = estadoFormat;
            const productosList = document.getElementById('modal-productos-list');
            productosList.innerHTML = '';
            (itemsDetalle || []).forEach(item => {
                let opcionesHtml = '';
                if (item.opciones_json && Array.isArray(item.opciones_json) && item.opciones_json.length > 0) {
                    opcionesHtml = `<div class="mt-1 text-sm text-mint-green font-medium">` +
                        item.opciones_json.map(opt => `<div class="flex items-center gap-1"><i class="fa-solid fa-plus text-xs"></i> ${escapeHtml(opt.nombre)}</div>`).join('') +
                    `</div>`;
                }
                let notasHtml = '';
                if (item.notas_item && item.notas_item.trim() !== '') {
                    notasHtml = `<div class="mt-1 text-sm text-yellow-600 italic">Nota: ${escapeHtml(item.notas_item)}</div>`;
                }

                const itemDiv = document.createElement('div');
                itemDiv.className = 'flex justify-between items-start py-2 px-3 bg-white rounded border border-beige-light mb-2';
                itemDiv.innerHTML = `
                    <div class="flex-1">
                        <span class="font-medium text-brown-dark">${escapeHtml(item.nombre)}</span>
                        ${opcionesHtml}
                        ${notasHtml}
                    </div>
                    <span class="bg-brown-dark text-white px-3 py-1 rounded-full font-semibold ml-2">x${escapeHtml(item.cantidad)}</span>
                `;
                productosList.appendChild(itemDiv);
            });
            const notasSection = document.getElementById('modal-notas-section');
            notasSection.innerHTML = notas && notas.trim() !== ''
                ? `<div class="p-4 rounded-xl bg-yellow-50 border border-yellow-200"><h3 class="text-lg font-semibold text-yellow-900 mb-2 flex items-center"><i class="fas fa-sticky-note mr-2"></i>Notas especiales</h3><p class="text-yellow-800 whitespace-pre-wrap">${escapeHtml(notas)}</p></div>`
                : `<div class="p-4 rounded-xl bg-orange-50 border border-orange-200"><h3 class="text-lg font-semibold text-orange-900 mb-2 flex items-center"><i class="fas fa-exclamation-triangle mr-2"></i>⚠ Sin notas especiales</h3><p class="text-orange-800">Verificar si el cliente dejó instrucciones para sus bebidas</p></div>`;
            modal.classList.remove('hidden');
        }

        function closeOrderModal() {
            document.getElementById('order-detail-modal').classList.add('hidden');
            modalOpen = false;
            selectedOrderNumber = null;
        }

        async function fetchBaristaEstado() {
            try {
                const response = await fetch('<?= BASE_URL ?>public/api/baristaEstado.php', { 
                    cache: 'no-store',
                    headers: { 'X-Background-Request': 'true' }
                });
                const data = await response.json();
                if (!data.ok) return;
                currentPendientes = data.pendientes || [];
                currentEntregadas = data.listas || [];
                renderPendientes(currentPendientes);
                renderListas(currentEntregadas);

                if (modalOpen && selectedOrderNumber !== null) {
                    const ordenActualizada = [...currentPendientes, ...currentEntregadas].find(o => String(o.numero) === String(selectedOrderNumber));
                    if (ordenActualizada) {
                        openOrderModal(ordenActualizada.numero, ordenActualizada.mesa, ordenActualizada.items || [], ordenActualizada.notas || '', ordenActualizada.estado);
                    } else {
                        closeOrderModal();
                    }
                }
            } catch (error) {
                console.error('Error actualizando barista:', error);
            }
        }

        document.addEventListener('submit', async function(e) {
            if (!e.target.action.includes('baristaAccion.php')) return;
            e.preventDefault();
            clearInterval(refreshInterval);
            const form = e.target;
            const numero = form.querySelector('input[name="numero"]').value;
            const accion = form.querySelector('input[name="accion"]').value;
            const btn = form.querySelector('button[type="submit"]');
            
            if (accion === 'lista') {
                btn.style.display = 'none';
            } else {
                btn.disabled = true;
                btn.textContent = 'Procesando...';
            }

            try {
                const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
                const data = await response.json();
                if (data.status === 'OK') {
                    await Swal.fire({ icon: 'success', title: accion === 'preparacion' ? 'Bebida en preparación' : 'Bebida lista', text: 'Orden #' + numero + ' actualizada correctamente', timer: 1000, showConfirmButton: false });
                    await fetchBaristaEstado();
                    startAutoRefresh();
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            } catch (error) {
                if (accion === 'lista') {
                    btn.style.display = '';
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Marcar en preparación';
                }
                Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Error al actualizar bebidas' });
                startAutoRefresh();
            }
        });

        function startAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            refreshInterval = setInterval(fetchBaristaEstado, 5000);
        }

        document.querySelectorAll('#close-modal-btn, #close-modal-btn-bottom').forEach(btn => btn.addEventListener('click', closeOrderModal));
        document.getElementById('order-detail-modal').addEventListener('click', function(e) { if (e.target === this) closeOrderModal(); });
        renderPendientes(currentPendientes);
        renderListas(currentEntregadas);
        startAutoRefresh();
    </script>
    <script src="<?= BASE_URL ?>public/js/session-sync.js"></script>
</body>
</html>
