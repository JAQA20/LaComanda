<?php
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";

verificarRol([1]); // solo Admin

$archivo = __DIR__ . "/../../controller/ordenes.json";
$ordenes = file_exists($archivo)
    ? json_decode(file_get_contents($archivo), true)
    : [];

if (!is_array($ordenes)) {
    $ordenes = [];
}

$ordenes = app_normalize_order_array($ordenes);

$total_ordenes = count($ordenes);
$total_vendido = 0;
$entregadas = 0;
$pendientes = 0;

foreach ($ordenes as $orden) {
    if (isset($orden['estado'])) {
        if ($orden['estado'] === 'entregada') {
            $entregadas++;
        } elseif ($orden['estado'] === 'pendiente') {
            $pendientes++;
        }
    }
}

usort($ordenes, function ($a, $b) {
    return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
});
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes - La Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>

<body class="bg-gray-100 font-montserrat">
    <?php
    require_once ROOT_PATH . "/views/admin/adminNavbar.php";
    ?>

    <div class="pt-20 p-8">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-4xl font-bold text-gray-800 mb-8">Historial de Órdenes</h1>

            <div class="grid grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-600 text-sm mb-2">Total Órdenes</div>
                    <div class="text-3xl font-bold text-gray-800"><?php echo $total_ordenes; ?></div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-600 text-sm mb-2">Entregadas</div>
                    <div class="text-3xl font-bold text-green-600"><?php echo $entregadas; ?></div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-600 text-sm mb-2">Pendientes</div>
                    <div class="text-3xl font-bold text-yellow-600"><?php echo $pendientes; ?></div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-600 text-sm mb-2">Total Vendido</div>
                    <div class="text-3xl font-bold text-blue-600"><?php echo number_format($total_vendido, 2, ',', '.'); ?></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">ID Orden</th>
                            <th class="px-6 py-3 text-left">Mesa</th>
                            <th class="px-6 py-3 text-left">Productos</th>
                            <th class="px-6 py-3 text-left">Notas</th>
                            <th class="px-6 py-3 text-left">Estado</th>
                            <th class="px-6 py-3 text-left">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($ordenes) > 0): ?>
                            <?php foreach ($ordenes as $orden): ?>
                                <tr class="border-b hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-semibold">#<?php echo htmlspecialchars($orden['numero'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($orden['mesa'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars(str_replace("\n", " | ", $orden['items'] ?? 'N/A')); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <?php
                                        $notas = trim((string)($orden['notas'] ?? ''));
                                        echo $notas !== '' ? htmlspecialchars($notas) : '<span class="text-gray-400">Sin notas</span>';
                                        ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-white text-sm font-medium
                                            <?php
                                            $estado = strtolower($orden['estado'] ?? 'pendiente');
                                            if ($estado === 'entregada') echo 'bg-green-500';
                                            elseif ($estado === 'pendiente') echo 'bg-yellow-500';
                                            else echo 'bg-gray-500';
                                            ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $orden['estado'] ?? 'pendiente')); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 text-sm">
                                        <?php
                                        if (isset($orden['timestamp'])) {
                                            $ts = (int)$orden['timestamp'];
                                            echo date('d/m/Y H:i', $ts);
                                            if (!empty($orden['hora_entrega'])) {
                                                echo '<br><span class="text-xs text-green-700">Entregada: ' . htmlspecialchars((string)$orden['hora_entrega']) . '</span>';
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <p>No hay órdenes registradas</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>


</html>