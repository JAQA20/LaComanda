<?php
require_once __DIR__ . "/../../config/env.php";
app_configure_errors();
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";
require_once __DIR__ . "/../../model/Logs.php";

// Verificación estricta de seguridad
$isMainAdmin = (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == 1);
$isAuthorizedAdmin = (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 1 && isset($_SESSION['can_view_logs']) && $_SESSION['can_view_logs'] == 1);

if (!$isMainAdmin && !$isAuthorizedAdmin) {
    header("HTTP/1.1 403 Forbidden");
    echo "Acceso Denegado.";
    exit;
}

$logs = Logs::getLogs();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs Técnicos | La Comanda</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = { autoReplaceSvg: 'nest' };
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#F8F5F0',
                        beigeSoft: '#EDE3D6',
                        brownDark: '#4E342E',
                        brownSoft: '#8D6E63',
                        mintGreen: '#7FB69E',
                        goldSoft: '#D6B98C'
                    },
                    boxShadow: {
                        card: '0 10px 25px rgba(0,0,0,0.08)'
                    }
                }
            }
        }
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">

    <style>
        .dataTables_wrapper .row { margin-bottom: 1rem; align-items: center; }
        .dataTables_filter input { border-radius: 0.75rem !important; border: 1px solid #efe7db !important; padding: 0.4rem 1rem !important; }
        .dataTables_length select { border-radius: 0.5rem !important; border: 1px solid #efe7db !important; }
        table.dataTable>thead>tr>th { border-bottom: 1px solid #efe7db; color: #8D6E63; font-weight: 600; }
        table.dataTable>tbody>tr>td { border-color: #efe7db; }
        .modal-backdrop { z-index: 40 !important; }
        .modal { z-index: 50 !important; }
    </style>
</head>

<body class="custom-beige min-h-screen font-sans">
    
    <?php require_once ROOT_PATH . "/views/admin/adminNavbar.php"; ?>

    <div class="flex pt-16 min-h-screen">
        <main class="flex-1 p-6 w-full max-w-7xl mx-auto">
            
            <div class="bg-gradient-to-r from-[#8D6E63] to-[#4E342E] rounded-3xl shadow-card p-8 mb-8 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <p class="uppercase tracking-[0.2em] text-sm text-beigeSoft mb-2">Administración</p>
                    <h1 class="text-3xl md:text-4xl font-extrabold mb-2">Logs del Sistema</h1>
                    <p class="text-sm md:text-base text-white/85 max-w-2xl">
                        Registro de eventos, transacciones exitosas y errores técnicos atrapados. Esta vista es confidencial.
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-card p-6 md:p-8">
                <div class="table-responsive">
                    <table id="tablaLogs" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center w-16">ID</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Usuario</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-8">
                                        <i class="fa-solid fa-check-circle text-4xl mb-3 text-mintGreen block"></i>
                                        <p class="text-gray-500 font-medium">No hay registros en el sistema.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): 
                                    $logType = $log['type'] ?? 'error';
                                    
                                    if ($logType === 'success') {
                                        $rowClass = 'bg-emerald-50/50 hover:bg-emerald-50';
                                        $iconClass = 'fa-circle-check text-emerald-600';
                                        $badgeClass = 'bg-emerald-100 text-emerald-800';
                                        $textClass = 'text-emerald-700';
                                        $typeText = 'Éxito';
                                    } elseif ($logType === 'info') {
                                        $rowClass = 'bg-blue-50/50 hover:bg-blue-50';
                                        $iconClass = 'fa-circle-info text-blue-600';
                                        $badgeClass = 'bg-blue-100 text-blue-800';
                                        $textClass = 'text-blue-700';
                                        $typeText = 'Info';
                                    } else {
                                        $rowClass = 'bg-red-50/50 hover:bg-red-50';
                                        $iconClass = 'fa-circle-xmark text-red-600';
                                        $badgeClass = 'bg-red-100 text-red-800';
                                        $textClass = 'text-red-700';
                                        $typeText = 'Error';
                                    }
                                ?>
                                    <tr class="<?= $rowClass ?> transition-colors">
                                        <td class="text-center font-semibold text-gray-500">#<?= $log['id'] ?></td>
                                        <td class="whitespace-nowrap text-sm text-gray-600 font-medium">
                                            <?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($log['created_at']))) ?>
                                        </td>
                                        <td>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?= $badgeClass ?>">
                                                <i class="fa-solid <?= $iconClass ?> mr-1"></i>
                                                <?= $typeText ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-cream border border-[#efe7db] flex items-center justify-center text-brownDark font-bold text-xs">
                                                    <?php if ($log['user_id']): ?>
                                                        <?= strtoupper(substr($log['nombre'], 0, 1) . substr($log['apellido'], 0, 1)) ?>
                                                    <?php else: ?>
                                                        <i class="fa-solid fa-robot"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <?php if ($log['user_id']): ?>
                                                        <div class="font-bold text-brownDark text-sm"><?= htmlspecialchars($log['nombre'] . ' ' . $log['apellido']) ?></div>
                                                        <div class="text-xs text-brownSoft">ID: <?= $log['user_id'] ?></div>
                                                    <?php else: ?>
                                                        <div class="font-bold text-gray-600 text-sm">Sistema</div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="font-mono text-xs <?= $textClass ?> break-words max-w-xl">
                                            <?= htmlspecialchars($log['error_message']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tablaLogs').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                "order": [[ 1, "desc" ]],
                "pageLength": 25
            });
        });
    </script>
</body>
</html>
