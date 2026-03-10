<?php
require_once "../middleware/auth.php";
require_once "../middleware/roles.php";

verificarRol([1, 2]); // Admin(1) y Mesero(2)
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Comanda</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="custom-beige min-h-screen">

    <!-- Navbar -->
    <?php include './layout/navbar.php'; ?>

    <!-- Main Content -->
    <div class="pt-20 min-h-screen">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col xl:flex-row gap-6 items-start">

                <!-- Content Area -->
                <main id="content-area" class="w-full flex-1 min-w-0">

                    <!-- Mesas View -->
                    <div id="mesas-view" class="block">
                        <h1 class="text-brown text-3xl font-bold mb-8">Mesas disponibles</h1>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">
                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="1">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 1</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="2">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 2</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="3">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 3</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="4">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 4</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="5">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 5</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="6">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 6</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="7">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 7</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="8">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 8</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="9">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 9</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="10">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 10</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="11">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 11</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>

                            <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="12">
                                <div class="text-center">
                                    <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-utensils text-white text-xl"></i>
                                    </div>
                                    <h3 class="text-brown font-semibold text-lg">Mesa 12</h3>
                                    <p class="text-mint text-sm">Disponible</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Views -->
                    <div id="menu-view" class="hidden">
                        <h1 id="menu-title" class="text-brown text-3xl font-bold mb-8">Menú</h1>
                        <div id="productos-grid" class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-6">
                        </div>
                    </div>

                </main>

                <!-- Sidebar - Orden Actual -->
                <?php include './layout/ordenActual.php'; ?>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include './layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let mesaActual = null;
        let ordenActual = [];
        let totalOrden = 0;

        // Mesas que tienen orden, persistidas en localStorage
        let mesasConOrden = JSON.parse(localStorage.getItem('mesasConOrden') || '{}');

        function guardarEstadoMesas() {
            localStorage.setItem('mesasConOrden', JSON.stringify(mesasConOrden));
        }

        function seleccionarMesa(numeroMesa) {
            mesaActual = numeroMesa;

            const mesaActualSpan = document.getElementById('mesa-actual');
            if (mesaActualSpan) mesaActualSpan.textContent = `Mesa ${numeroMesa}`;

            document.querySelectorAll('.mesa-card').forEach(card => {
                card.style.border = "4px solid transparent";
            });

            const cardSeleccionada = document.querySelector(`.mesa-card[data-mesa="${numeroMesa}"]`);
            if (cardSeleccionada) {
                cardSeleccionada.style.border = "2px solid #70A38F";
            }

            actualizarBotones();
        }

        function actualizarEstadoMesa(numeroMesa) {
            const card = document.querySelector(`[data-mesa="${numeroMesa}"]`);
            if (!card) return;

            const tieneOrden = mesasConOrden[numeroMesa];

            if (tieneOrden) {
                card.innerHTML = `
                    <div class="text-center">
                        <div class="w-16 h-16 custom-brown rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-utensils text-beige text-xl"></i>
                        </div>
                        <h3 class="text-brown font-semibold text-lg">Mesa ${numeroMesa}</h3>
                        <p class="text-brown text-sm">Con orden</p>
                        <button class="btn-entregar w-full py-2 custom-mint text-white rounded-lg hover-mint-bg font-medium">Entregar orden</button>
                    </div>
                `;
            } else {
                card.innerHTML = `
                    <div class="text-center">
                        <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-utensils text-white text-xl"></i>
                        </div>
                        <h3 class="text-brown font-semibold text-lg">Mesa ${numeroMesa}</h3>
                        <p class="text-mint text-sm">Disponible</p>
                    </div>
                `;
            }
        }

        async function mostrarProductos(slug, nombreCategoria = "Menú") {
            const menuView = document.getElementById('menu-view');
            const mesasView = document.getElementById('mesas-view');
            const productosGrid = document.getElementById('productos-grid');
            const menuTitle = document.getElementById('menu-title');

            if (!menuView || !mesasView || !productosGrid || !menuTitle) return;

            mesasView.classList.add('hidden');
            menuView.classList.remove('hidden');
            menuTitle.textContent = nombreCategoria;

            productosGrid.innerHTML = `
                <div class="col-span-full text-center text-gray-500 py-10">
                    <i class="fas fa-circle-notch fa-spin text-2xl mb-3"></i>
                    <p>Cargando productos...</p>
                </div>
            `;

            try {
                const resp = await fetch(`../controller/listarProductosController.php?categoria=${encodeURIComponent(slug)}`);
                if (!resp.ok) throw new Error("HTTP " + resp.status);

                const json = await resp.json();
                if (json.status !== "OK") throw new Error(json.message || "Error API");

                const items = json.data || [];

                if (!items.length) {
                    renderPlaceholderSinProductos(nombreCategoria);
                    return;
                }

                productosGrid.innerHTML = items.map(p => {
                    const nombre = p.nombre ?? "";
                    const precio = Number(p.precio ?? 0);
                    const icono = p.icono ?? "fa-box";
                    const nombreSafe = nombre.replace(/'/g, "\\'");

                    return `
                        <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200">
                            <div class="text-center">
                                <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas ${icono} text-white text-xl"></i>
                                </div>
                                <h3 class="text-brown font-semibold text-lg mb-2">${nombre}</h3>
                                <p class="text-mint font-bold text-xl mb-4">₡${precio.toLocaleString()}</p>
                                <button onclick="agregarProducto('${nombreSafe}', ${precio})"
                                        class="w-full custom-mint text-white py-2 rounded-lg hover-mint-bg transition-all duration-200 flex items-center justify-center">
                                    <i class="fas fa-plus mr-2"></i>
                                    Agregar
                                </button>
                            </div>
                        </div>
                    `;
                }).join("");

            } catch (e) {
                console.error(e);
                productosGrid.innerHTML = `
                    <div class="col-span-full bg-white rounded-xl p-8 shadow-sm border border-red-100">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-triangle-exclamation text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-xl mb-2">No se pudieron cargar los productos</h3>
                            <p class="text-gray-500">Intenta nuevamente.</p>
                        </div>
                    </div>
                `;
            }
        }

        function renderPlaceholderSinProductos(nombreCategoria = "esta categoría") {
            const productosGrid = document.getElementById('productos-grid');
            if (!productosGrid) return;

            productosGrid.innerHTML = `
                <div class="col-span-full bg-white rounded-xl p-10 shadow-sm border border-gray-100">
                    <div class="text-center">
                        <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-box-open text-white text-2xl"></i>
                        </div>
                        <h3 class="text-brown font-semibold text-2xl mb-2">No hay productos registrados</h3>
                        <p class="text-gray-500">
                            Aún no existen productos para <strong>${nombreCategoria}</strong>.
                        </p>
                    </div>
                </div>
            `;
        }

        function mostrarMesas() {
            const menuView = document.getElementById('menu-view');
            const mesasView = document.getElementById('mesas-view');

            if (menuView) menuView.classList.add('hidden');
            if (mesasView) mesasView.classList.remove('hidden');
        }

        function agregarProducto(nombre, precio) {
            if (!mesaActual) {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Seleccione una mesa primero"
                });
                return;
            }

            const itemExistente = ordenActual.find(item => item.nombre === nombre);
            if (itemExistente) {
                itemExistente.cantidad++;
            } else {
                ordenActual.push({
                    nombre,
                    precio,
                    cantidad: 1
                });
            }

            actualizarOrden();
        }

        function actualizarOrden() {
            const ordenItems = document.getElementById('orden-items');
            const totalElement = document.getElementById('total-orden');

            if (!ordenItems || !totalElement) return;

            if (ordenActual.length === 0) {
                ordenItems.innerHTML = `
                    <div class="text-gray-500 text-center py-8">
                        <i class="fas fa-coffee text-4xl mb-2"></i>
                        <p>Selecciona una mesa y agrega productos</p>
                    </div>
                `;
            } else {
                ordenItems.innerHTML = ordenActual.map((item, index) => `
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                        <div class="flex-1">
                            <p class="text-brown font-medium">${item.nombre}</p>
                            <p class="text-mint text-sm">₡${item.precio.toLocaleString()} c/u</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="cambiarCantidad(${index}, -1)" class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center text-sm">-</button>
                            <span class="text-brown font-medium w-8 text-center">${item.cantidad}</span>
                            <button onclick="cambiarCantidad(${index}, 1)" class="w-6 h-6 custom-mint text-white rounded-full flex items-center justify-center text-sm">+</button>
                        </div>
                    </div>
                `).join('');
            }

            totalOrden = ordenActual.reduce((total, item) => total + (item.precio * item.cantidad), 0);
            totalElement.textContent = `₡${totalOrden.toLocaleString()}`;
            actualizarBotones();
        }

        async function entregarOrden(numeroMesa) {
            const {
                isConfirmed
            } = await Swal.fire({
                title: `¿Marcar la orden de la Mesa ${numeroMesa} como ENTREGADA?`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, entregar",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            });

            if (!isConfirmed) return;

            try {
                const respuesta = await fetch("../controller/entregarOrden.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `mesa=${encodeURIComponent(numeroMesa)}`
                });

                const result = await respuesta.json();

                if (result.status === "OK") {
                    mesasConOrden[numeroMesa] = false;
                    guardarEstadoMesas();
                    actualizarEstadoMesa(numeroMesa);

                    Swal.fire({
                        position: "center",
                        icon: "success",
                        html: `<strong>Mesa ${numeroMesa}</strong><br>Orden marcada como ENTREGADA`,
                        showConfirmButton: false,
                        timer: 2500
                    });

                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "No se pudo entregar la orden"
                    });
                }

            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Error al entregar la orden",
                });
            }
        }

        function cambiarCantidad(index, cambio) {
            ordenActual[index].cantidad += cambio;
            if (ordenActual[index].cantidad <= 0) {
                ordenActual.splice(index, 1);
            }
            actualizarOrden();
        }

        function actualizarBotones() {
            const eliminarBtn = document.getElementById('eliminar-orden');
            const enviarBtn = document.getElementById('enviar-cocina');

            const tieneOrden = ordenActual.length > 0 && mesaActual;
            if (eliminarBtn) eliminarBtn.disabled = !tieneOrden;
            if (enviarBtn) enviarBtn.disabled = !tieneOrden;
        }

        function eliminarOrden() {
            if (ordenActual.length === 0) return;

            Swal.fire({
                title: "¿Eliminar orden?",
                text: "¿Estás seguro de eliminar toda la orden?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#8B0000",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    ordenActual = [];
                    actualizarOrden();
                    actualizarBotones();

                    Swal.fire({
                        icon: "success",
                        title: "Orden eliminada",
                        text: "La orden fue eliminada correctamente",
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            });
        }

        async function enviarCocina() {
            if (!mesaActual) {
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Seleccione una mesa primero"
                });
                return;
            }

            if (ordenActual.length === 0) {
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Agrega productos antes de enviar la orden"
                });
                return;
            }

            const {
                isConfirmed
            } = await Swal.fire({
                title: `¿Enviar orden de la Mesa ${mesaActual} a cocina?`,
                text: "Esta acción enviará la orden a cocina.",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Enviar a cocina",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            });

            if (!isConfirmed) return;

            const listaProductos = ordenActual
                .map(item => `${item.nombre} x${item.cantidad}`)
                .join("\n");

            const data = {
                mesa: mesaActual,
                items: listaProductos
            };

            try {
                Swal.fire({
                    title: "Enviando orden...",
                    html: `Mesa <strong>${mesaActual}</strong>`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                const respuesta = await fetch("../controller/guardar_orden.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });

                if (!respuesta.ok) {
                    throw new Error(`HTTP ${respuesta.status}`);
                }

                const result = await respuesta.json();

                if (result.status === "OK") {
                    mesasConOrden[mesaActual] = true;
                    guardarEstadoMesas();

                    await Swal.fire({
                        position: "center",
                        icon: "success",
                        html: `Orden enviada a cocina para <strong>Mesa ${mesaActual}</strong> ✔`,
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: false
                    });

                    ordenActual = [];
                    mesaActual = null;

                    const mesaActualSpan = document.getElementById("mesa-actual");
                    if (mesaActualSpan) mesaActualSpan.textContent = "No seleccionada";

                    actualizarOrden();
                    actualizarBotones();

                    window.location.href = "index.php";

                } else {
                    await Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: result.message || "No se pudo enviar la orden a cocina"
                    });
                }

            } catch (error) {
                console.error("Error enviando orden:", error);

                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Error al enviar la orden"
                });
            }
        }

        function actualizarNavbar(activeBtn) {
            const navbar = document.getElementById('navbar');
            if (!navbar) return;

            navbar.querySelectorAll('button').forEach(btn => {
                btn.classList.remove('border-b-2', 'border-mint');
            });
            activeBtn.classList.add('border-b-2', 'border-mint');
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('#navbar button[data-slug]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const slug = btn.getAttribute('data-slug');
                    const nombre = btn.textContent.trim();

                    if (slug === "mesas") {
                        mostrarMesas();
                    } else {
                        await mostrarProductos(slug, nombre);
                    }

                    actualizarNavbar(btn);
                });
            });

            const mesasBtn = document.querySelector('#navbar button[data-slug="mesas"]');
            if (mesasBtn) actualizarNavbar(mesasBtn);

            document.addEventListener('click', (e) => {
                const card = e.target.closest('.mesa-card');
                if (!card) return;

                if (e.target.closest('.btn-entregar')) return;

                const mesa = card.getAttribute('data-mesa');
                if (!mesa) return;

                seleccionarMesa(mesa);
            });

            const eliminarBtn = document.getElementById('eliminar-orden');
            const enviarBtn = document.getElementById('enviar-cocina');

            if (eliminarBtn) eliminarBtn.addEventListener('click', eliminarOrden);
            if (enviarBtn) enviarBtn.addEventListener('click', enviarCocina);

            Object.keys(mesasConOrden).forEach(numMesa => {
                if (mesasConOrden[numMesa]) {
                    actualizarEstadoMesa(numMesa);
                }
            });

            document.addEventListener('click', (e) => {
                const boton = e.target.closest('.btn-entregar');
                const card = e.target.closest('.mesa-card');
                if (!boton || !card) return;

                const mesa = card.getAttribute('data-mesa');
                if (!mesa) return;

                entregarOrden(mesa);
            });
        });
    </script>

</body>

</html>