<?php
require_once __DIR__ . "/../../config/env.php";
app_configure_errors();
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../config/rutas.php";

verificarRol([1]); // solo Admin
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };

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


    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tu CSS principal -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="custom-beige min-h-screen">

    <!-- Navbar -->
    <?php
    require_once ROOT_PATH . "/views/admin/adminNavbar.php";
    ?>

    <!-- Main Content -->
    <div class="flex pt-16 min-h-screen">
        <!-- Content Area -->
        <main id="content-area" class="flex-1 p-6 w-full">
            <div id="mesas-view" class="block w-full">
                <div class="bg-gradient-to-r from-mintGreen to-[#6a9e7a] rounded-3xl shadow-card p-8 mb-8 text-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <p class="uppercase tracking-[0.2em] text-sm text-beigeSoft mb-2">Panel administrativo</p>
                            <h1 class="text-3xl md:text-4xl font-extrabold mb-2">Dashboard administrativo</h1>
                            <p class="text-sm md:text-base text-white/85 max-w-2xl">
                                Consulta el resumen de ventas y el estado de las órdenes registradas en La Comanda.
                            </p>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-5 py-4 min-w-[220px] border border-white/10">
                            <div class="text-sm text-beigeSoft mb-1">Resumen general</div>
                            <div id="stat-total-header" class="text-3xl font-bold">0</div>
                            <div class="text-sm text-white/80">órdenes registradas</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3 mb-8">
                    <div class="bg-white rounded-2xl shadow-card px-6 py-4 border border-[#efe7db] min-h-[140px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-brownSoft">Total órdenes</span>
                            <div class="w-11 h-11 rounded-xl bg-[#F5EEE5] flex items-center justify-center text-brownDark">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                        </div>
                        <div id="stat-total-ordenes" class="text-3xl font-extrabold text-brownDark">0</div>
                        <p class="text-sm text-brownSoft mt-1">Histórico cargado</p>
                    </div>

                    <div class="bg-white rounded-2xl shadow-card px-6 py-4 border border-[#efe7db] min-h-[140px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-brownSoft">Entregadas</span>
                            <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>
                        <div id="stat-entregadas" class="text-3xl font-extrabold text-emerald-600">0</div>
                        <p class="text-sm text-brownSoft mt-1">Órdenes finalizadas</p>
                    </div>

                    <div class="bg-white rounded-2xl shadow-card px-6 py-4 border border-[#efe7db] min-h-[140px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-brownSoft">Pendientes</span>
                            <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                                <i class="fa-solid fa-hourglass-half"></i>
                            </div>
                        </div>
                        <div id="stat-pendientes" class="text-3xl font-extrabold text-amber-600">0</div>
                        <p class="text-sm text-brownSoft mt-1">Esperando atención</p>
                    </div>

                    <div class="bg-white rounded-2xl shadow-card px-6 py-4 border border-[#efe7db] min-h-[140px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-brownSoft">Listas para entrega</span>
                            <div class="w-11 h-11 rounded-xl bg-sky-50 flex items-center justify-center text-sky-600">
                                <i class="fa-solid fa-kitchen-set"></i>
                            </div>
                        </div>
                        <div id="stat-en-proceso" class="text-3xl font-extrabold text-sky-600">0</div>
                        <p class="text-sm text-brownSoft mt-1">Listas</p>
                    </div>

                    <div class="bg-white rounded-2xl shadow-card px-6 py-4 border border-[#efe7db] min-h-[140px]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-semibold text-brownSoft">Total vendido</span>
                            <div class="w-11 h-11 rounded-xl bg-mintGreen/10 flex items-center justify-center text-mintGreen">
                                <i class="fa-solid fa-colon-sign"></i>
                            </div>
                        </div>
                        <div id="stat-total-vendido" class="text-2xl md:text-3xl font-extrabold text-mintGreen">₡0.00</div>
                        <p class="text-sm text-brownSoft mt-1">Monto acumulado</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-6">
                    <a href="<?= BASE_URL ?>views/admin/usuarios.php" class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer block text-decoration-none">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-user text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Usuarios</h3>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>views/admin/productos.php" class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer block text-decoration-none">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-box text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Productos</h3>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>views/admin/ordenesAdmin.php" class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer block text-decoration-none">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-receipt text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Historial de órdenes</h3>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>views/index.php" class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer block text-decoration-none">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-utensils text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Tomar órdenes</h3>
                        </div>
                    </a>
                </div>

                <!-- Gráficos y Sesiones -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                    <div class="bg-white rounded-2xl shadow-card p-6 border border-[#efe7db]">
                        <h2 class="text-lg font-bold text-brownDark mb-4">Productos más vendidos</h2>
                        <div style="position: relative; height: 300px; width: 100%;">
                            <canvas id="chartProductos"></canvas>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow-card p-6 border border-[#efe7db]">
                        <h2 class="text-lg font-bold text-brownDark mb-4">Historial de ventas (6 meses)</h2>
                        <div style="position: relative; height: 300px; width: 100%;">
                            <canvas id="chartVentas"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-card p-6 border border-[#efe7db] mt-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-brownDark">Usuarios conectados</h2>
                        <span id="contador-usuarios-activos" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                            0 activos
                        </span>
                    </div>
                    <div id="contenedor-sesiones" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <!-- Tarjetas de sesión generadas por JS -->
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php
    require_once ROOT_PATH . '/views/layout/footer.php';
    ?>
    <script>
        const API_ORDENES_ADMIN = "<?= BASE_URL ?>public/api/ordenesAdminData.php";

        const statTotalOrdenes = document.getElementById("stat-total-ordenes");
        const statEntregadas = document.getElementById("stat-entregadas");
        const statPendientes = document.getElementById("stat-pendientes");
        const statEnProceso = document.getElementById("stat-en-proceso");
        const statTotalVendido = document.getElementById("stat-total-vendido");
        const statTotalHeader = document.getElementById("stat-total-header");

        function formatearColones(valor) {
            return "₡" + Number(valor || 0).toLocaleString("es-CR", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        async function cargarResumenOrdenes() {
            try {
                const response = await fetch(API_ORDENES_ADMIN, {
                    method: "GET",
                    headers: {
                        "Accept": "application/json",
                        "X-Background-Request": "true"
                    },
                    cache: "no-store"
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                const stats = data.stats || {};

                statTotalOrdenes.textContent = stats.total_ordenes ?? 0;
                statEntregadas.textContent = stats.entregadas ?? 0;
                statPendientes.textContent = stats.pendientes ?? 0;
                statEnProceso.textContent = stats.en_proceso ?? 0;
                statTotalVendido.textContent = formatearColones(stats.total_vendido ?? 0);
                statTotalHeader.textContent = stats.total_ordenes ?? 0;
            } catch (error) {
                console.error("Error cargando resumen de órdenes:", error);
            }
        }

        cargarResumenOrdenes();
        setInterval(cargarResumenOrdenes, 5000);

        // Chart.js e integracion
        const API_DASHBOARD_CHARTS = "<?= BASE_URL ?>public/api/dashboardCharts.php";
        const API_USUARIOS_CONECTADOS = "<?= BASE_URL ?>public/api/usuariosConectados.php";

        async function cargarGraficos() {
            try {
                const response = await fetch(API_DASHBOARD_CHARTS, { 
                    cache: 'no-store',
                    headers: { 'X-Background-Request': 'true' }
                });
                if (!response.ok) throw new Error("Error cargando gráficos");
                const data = await response.json();

                if (data.ok) {
                    new Chart(document.getElementById('chartProductos'), {
                        type: 'bar',
                        data: {
                            labels: data.top_productos.map(p => p.nombre),
                            datasets: [{
                                label: 'Cantidad vendida',
                                data: data.top_productos.map(p => p.total),
                                backgroundColor: '#7FB69E'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });

                    new Chart(document.getElementById('chartVentas'), {
                        type: 'line',
                        data: {
                            labels: data.ventas_historial.map(v => v.fecha),
                            datasets: [{
                                label: 'Ventas ₡',
                                data: data.ventas_historial.map(v => v.total_ventas),
                                borderColor: '#8D6E63',
                                backgroundColor: 'rgba(141, 110, 99, 0.2)',
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                }
            } catch (error) {
                console.error(error);
            }
        }
        cargarGraficos();

        // Sesiones activas
        const contenedorSesiones = document.getElementById('contenedor-sesiones');
        const contadorUsuariosActivos = document.getElementById('contador-usuarios-activos');

        async function cargarUsuariosConectados() {
            try {
                const response = await fetch(API_USUARIOS_CONECTADOS, {
                    headers: { 'X-Background-Request': 'true' }
                });
                if (!response.ok) throw new Error("Error cargando usuarios conectados");
                const data = await response.json();

                if (data.ok && data.sesiones) {
                    contadorUsuariosActivos.textContent = `${data.sesiones.length} activos`;
                    
                    if (data.sesiones.length === 0) {
                        contenedorSesiones.innerHTML = `<div class="col-span-full text-center text-muted py-4">No hay usuarios conectados.</div>`;
                        return;
                    }

                    contenedorSesiones.innerHTML = data.sesiones.map(s => `
                        <div class="flex items-start gap-4 p-4 border border-[#efe7db] rounded-xl bg-[#F8F5F0]">
                            <div class="w-12 h-12 rounded-full bg-mintGreen text-white flex items-center justify-center text-xl font-bold flex-shrink-0 mt-1">
                                ${s.nombre.charAt(0).toUpperCase()}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-1">
                                    <h3 class="text-brownDark font-bold truncate">${s.nombre}</h3>
                                    <span class="badge ${s.estado_class} text-[10px] shadow-sm ml-2">${s.estado_label}</span>
                                </div>
                                <p class="text-xs text-brownSoft truncate mb-2">${s.rol}</p>
                                <div class="text-[11px] text-muted space-y-1">
                                    <p><i class="fa-solid fa-arrow-right-to-bracket text-success w-3"></i> ${s.login_at}</p>
                                    <p><i class="fa-solid fa-arrow-right-from-bracket text-danger w-3"></i> ${s.logout_at}</p>
                                    <p><i class="fa-regular fa-hand-pointer text-primary w-3"></i> ${s.ultima_actividad}</p>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
            } catch(e) {
                console.error(e);
            }
        }
        cargarUsuariosConectados();
        setInterval(cargarUsuariosConectados, 3000);

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>