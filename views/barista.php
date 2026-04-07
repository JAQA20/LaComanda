<?php
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";
require_once __DIR__ . "/../model/OrdenesSync.php";

verificarRol([1, 4]); // Admin(1) y Barista(4)

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
header("Content-Type: text/html; charset=UTF-8");

$conexion = Conexion::conectar();

// Query para bebidas pendientes
$sqlPendientes = "
    SELECT
        o.id_orden,
        o.mesa_id,
        o.id_estado,
        o.timestamp,
        p.nombre AS producto,
        d.cantidad,
        CASE 
            WHEN o.id_estado = 1 THEN 'pendiente'
            WHEN o.id_estado = 3 THEN 'en_preparacion'
            ELSE 'otro'
        END AS estado_texto
    FROM ordenes o
    JOIN detalle_orden d ON o.id_orden = d.id_orden
    JOIN productos p ON d.id_producto = p.id
    JOIN categorias c ON p.categoria_id = c.id
    WHERE o.id_estado IN (1, 3) AND c.slug IN ('cafes', 'bebidas')
    ORDER BY o.timestamp ASC
";

$resultPendientes = $conexion->query($sqlPendientes);
$bebidasPendientes = [];
while ($row = $resultPendientes->fetch_assoc()) {
    $id = $row['id_orden'];
    if (!isset($bebidasPendientes[$id])) {
        $bebidasPendientes[$id] = [
            'id_orden' => $row['id_orden'],
            'mesa_id' => $row['mesa_id'],
            'estado' => $row['estado_texto'],
            'timestamp' => $row['timestamp'],
            'productos' => []
        ];
    }
    $bebidasPendientes[$id]['productos'][] = [
        'nombre' => $row['producto'],
        'cantidad' => $row['cantidad']
    ];
}

// Query para bebidas listas
$sqlListas = "
    ORDER BY COALESCE(o.hora_lista, o.hora_entrega) DESC
    LIMIT 20
";

$resultListas = $conexion->query($sqlListas);
$bebidasListas = [];
while ($row = $resultListas->fetch_assoc()) {
    $id = $row['id_orden'];
    if (!isset($bebidasListas[$id])) {
        $bebidasListas[$id] = [
            'id_orden' => $row['id_orden'],
            'mesa_id' => $row['mesa_id'],
            'hora_entrega' => $row['hora_entrega'],
            'hora_lista' => $row['hora_lista'],
            'productos' => []
        ];
    }
    $bebidasListas[$id]['productos'][] = [
        'nombre' => $row['producto'],
        'cantidad' => $row['cantidad']
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/cocina.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .bg-brown { background-color: #362018; }
        .text-beige { color: #F5EDE1; }
        .bg-mint { background-color: #70A38F; }
        .bg-mint:hover { background-color: #5B8F7A; }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-brown">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <img class="me-2" src="<?= BASE_URL ?>public/img/logotipo2.PNG" alt="Logo" style="height: 40px;">
                ☕ Barista - Toscana
            </span>
            <a href="<?= BASE_URL ?>public/api/logout.php" class="btn btn-outline-light">Salir</a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-brown text-beige">
                        <h5 class="card-title mb-0">Bebidas listas</h5>
                        <small>Últimos 20 registros</small>
                    </div>
                    <div class="card-body" id="delivered-list" style="max-height: 600px; overflow-y: auto;">
                        <?php
                        foreach ($bebidasListas as $orden):
                        ?>
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>Orden #<?php echo htmlspecialchars($orden["id_orden"] ?? "N/A"); ?></strong>
                                        <i class="fas fa-check text-success"></i>
                                    </div>
                                    <div class="small text-muted">
                                        Mesa <?php echo htmlspecialchars($orden["mesa_id"] ?? "N/A"); ?> | 
                                        <?php
                                            $hora = isset($orden["hora_lista"]) && $orden["hora_lista"] !== null
                                                ? $orden["hora_lista"]
                                                : $orden["hora_entrega"];
                                            echo $hora ? date('H:i', strtotime((string)$hora)) : "--:--";
                                        ?>
                                    </div>
                                    <?php foreach ($orden['productos'] as $prod): ?>
                                        <div class="small"><?php echo htmlspecialchars($prod['nombre']); ?> x<?php echo $prod['cantidad']; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-brown text-beige">
                        <h5 class="card-title mb-0">Bebidas pendientes</h5>
                    </div>
                    <div class="card-body" id="pending-orders-grid" style="max-height: 600px; overflow-y: auto;">
                        <div class="row">
                            <?php foreach ($bebidasPendientes as $orden): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 <?= $orden['estado'] === 'en_preparacion' ? 'border-warning bg-light' : '' ?>">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                Orden #<?php echo htmlspecialchars($orden["id_orden"]); ?>
                                                <?php if ($orden['estado'] === 'en_preparacion'): ?>
                                                    <span class="badge bg-warning text-dark">En preparación</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="card-text small text-muted">Mesa <?php echo htmlspecialchars($orden["mesa_id"] ?? "N/A"); ?></p>
                                            <h6>Bebidas:</h6>
                                            <ul class="list-unstyled">
                                                <?php foreach ($orden['productos'] as $prod): ?>
                                                    <li><?php echo htmlspecialchars($prod['nombre']); ?> x<?php echo $prod['cantidad']; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <?php if ($orden['estado'] === 'pendiente'): ?>
                                                <form action="<?= BASE_URL ?>public/api/marcarEntrega.php" method="POST">
                                                    <input type="hidden" name="numero" value="<?php echo htmlspecialchars($orden['id_orden']); ?>">
                                                    <input type="hidden" name="accion" value="preparacion">
                                                    <button type="submit" class="btn btn-warning w-100">En preparación</button>
                                                </form>
                                            <?php else: ?>
                                                <form action="<?= BASE_URL ?>public/api/marcarLista.php" method="POST">
                                                    <input type="hidden" name="numero" value="<?php echo htmlspecialchars($orden['id_orden']); ?>">
                                                    <button type="submit" class="btn btn-success w-100">Lista</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let refreshInterval;

        document.addEventListener('submit', async function(e) {
            if (!e.target.action.includes('marcarEnPreparacion.php') && !e.target.action.includes('marcarEntrega.php') && !e.target.action.includes('marcarLista.php')) return;

            e.preventDefault();

            clearInterval(refreshInterval);

            const form = e.target;
            const numero = form.querySelector('input[name="numero"]').value;
            const btn = form.querySelector('button[type="submit"]');
            const card = form.closest('.card');

            btn.disabled = true;
            btn.textContent = 'Procesando...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });

                const text = await response.text();
                let data = {};
                try {
                    data = JSON.parse(text);
                } catch (_) {
                    data = { status: 'OK' };
                }

                if (data.status === 'OK' || response.ok) {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';

                    await Swal.fire({
                        icon: 'success',
                        title: 'Bebida lista',
                        text: 'Orden #' + numero + ' marcada como lista',
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
