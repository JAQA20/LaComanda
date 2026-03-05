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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="../public/css/style.css"> -->
    <style>
        /* Mesa base */
        .mesa {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            cursor: pointer;
            user-select: none;
            transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
            border: 3px solid transparent;
            font-family: 'Montserrat', sans-serif;
        }

        .mesa:hover {
            transform: translateY(-2px);
        }

        /* Estados */
        .mesa.disponible {
            background: #22c55e;
            color: white;
        }

        .mesa.con-orden {
            background: #362018;
            color: #F5EDE1;
        }

        .mesa.seleccionada {
            border-color: #70A38F;
        }

        /* Etiqueta interna */
        .mesa .label {
            text-align: center;
            line-height: 1.1;
            font-weight: 700;
            font-size: 14px;
        }

        .mesa .sub {
            display: block;
            font-weight: 500;
            font-size: 11px;
            opacity: .9;
            margin-top: 2px;
        }

        /* Botón entregar mini */
        .mesa .btn-entregar {
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            padding: 6px 10px;
            border-radius: 10px;
            font-size: 11px;
            background: #70A38F;
            color: white;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
            white-space: nowrap;
        }
    </style>





</head>

<body class="custom-beige min-h-screen">

    <!-- Navbar -->
    <?php
    include './layout/navbar.php';
    ?>

    <!-- Main Content -->
    <div class="flex flex-row-reverse pt-16 min-h-screen">

        <!-- Sidebar - Orden Actual -->
        <?php
        include './layout/ordenActual.php';
        ?>
        <!-- Content Area -->
        <main id="content-area" class="flex-1 p-6">

            <!-- Mesas View -->

            <div id="mesas-view" class="block">
                <h1 class="text-brown text-3xl font-bold mb-8">Mesas disponibles</h1>
                <div class="bg-white rounded-2xl shadow-sm border p-4">
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-brown font-semibold">Croquis - Salón principal</p>
                        <div class="flex gap-3 text-sm">
                            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-emerald-500"></span>Disponible</span>
                            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-[#362018]"></span>Con orden</span>
                            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded border-2 border-[#70A38F]"></span>Seleccionada</span>
                        </div>
                    </div>

                    <!-- Área del croquis -->
                    <div id="croquis"
                        class="relative w-full max-w-5xl mx-auto aspect-[16/9] rounded-xl overflow-hidden border"
                        style="background: linear-gradient(180deg,#f7f4ef,#efe7db);">

                        <!-- “Elementos” decorativos opcionales -->
                        <div class="absolute left-4 top-4 text-xs text-brown/60">Entrada</div>

                        <!-- Mesas (se renderizan por JS) -->
                    </div>
                </div>

            </div>

            <!-- Menu Views -->
            <div id="menu-view" class="hidden">
                <h1 id="menu-title" class="text-brown text-3xl font-bold mb-8">Menú</h1>
                <div id="productos-grid" class="grid grid-cols-3 gap-6">
                </div>
            </div>

        </main>
    </div>

    <!-- Footer -->
    <?php
    include './layout/footer.php';
    ?>


    <script>
        let mesaActual = null;
        let ordenActual = [];
        let totalOrden = 0;

        // Mesas que tienen orden, persistidas en localStorage
        let mesasConOrden = JSON.parse(localStorage.getItem('mesasConOrden') || '{}');

        const productos = {
            cafes: [{
                    nombre: 'Espresso',
                    precio: 2500,
                    icono: 'fa-coffee'
                },
                {
                    nombre: 'Americano',
                    precio: 3000,
                    icono: 'fa-coffee'
                },
                {
                    nombre: 'Latte',
                    precio: 4500,
                    icono: 'fa-coffee'
                },
                {
                    nombre: 'Cappuccino',
                    precio: 4000,
                    icono: 'fa-coffee'
                },
                {
                    nombre: 'Flat White',
                    precio: 5000,
                    icono: 'fa-coffee'
                },
                {
                    nombre: 'Mocha',
                    precio: 5500,
                    icono: 'fa-coffee'
                }
            ],
            comidas: [{
                    nombre: 'Empanada de Carne',
                    precio: 3500,
                    icono: 'fa-bread-slice'
                },
                {
                    nombre: 'Bruschetta',
                    precio: 6500,
                    icono: 'fa-bread-slice'
                },
                {
                    nombre: 'Panini Italiano',
                    precio: 8500,
                    icono: 'fa-hamburger'
                },
                {
                    nombre: 'Ensalada César',
                    precio: 7500,
                    icono: 'fa-leaf'
                },
                {
                    nombre: 'Croissant Jamón y Queso',
                    precio: 5500,
                    icono: 'fa-bread-slice'
                },
                {
                    nombre: 'Tostadas Integrales',
                    precio: 4500,
                    icono: 'fa-bread-slice'
                }
            ],
            especialidades: [{
                    nombre: 'Tiramisú Latte',
                    precio: 6500,
                    icono: 'fa-star'
                },
                {
                    nombre: 'Affogato',
                    precio: 5500,
                    icono: 'fa-ice-cream'
                },
                {
                    nombre: 'Irish Coffee',
                    precio: 7000,
                    icono: 'fa-glass-whiskey'
                },
                {
                    nombre: 'Cannoli Siciliano',
                    precio: 4500,
                    icono: 'fa-cookie'
                },
                {
                    nombre: 'Gelato Artesanal',
                    precio: 5000,
                    icono: 'fa-ice-cream'
                },
                {
                    nombre: 'Espresso Romano',
                    precio: 6000,
                    icono: 'fa-lemon'
                }
            ],
            postres: [{
                    nombre: 'Tiramisú',
                    precio: 6500,
                    icono: 'fa-cake'
                },
                {
                    nombre: 'Panna Cotta',
                    precio: 5500,
                    icono: 'fa-birthday-cake'
                },
                {
                    nombre: 'Cannoli',
                    precio: 4500,
                    icono: 'fa-cookie'
                },
                {
                    nombre: 'Brownie',
                    precio: 4000,
                    icono: 'fa-cookie-bite'
                },
                {
                    nombre: 'Cheesecake',
                    precio: 6000,
                    icono: 'fa-cake'
                },
                {
                    nombre: 'Gelato',
                    precio: 3500,
                    icono: 'fa-ice-cream'
                }
            ],
            bebidas: [{
                    nombre: 'Limonada',
                    precio: 3500,
                    icono: 'fa-lemon'
                },
                {
                    nombre: 'Té Helado',
                    precio: 3000,
                    icono: 'fa-glass-water'
                },
                {
                    nombre: 'Smoothie Frutal',
                    precio: 5500,
                    icono: 'fa-blender'
                },
                {
                    nombre: 'Agua Saborizada',
                    precio: 2500,
                    icono: 'fa-bottle-water'
                },
                {
                    nombre: 'Jugo Natural',
                    precio: 4000,
                    icono: 'fa-apple-alt'
                },
                {
                    nombre: 'Frappé',
                    precio: 5000,
                    icono: 'fa-snowflake'
                }
            ]
        };

        function guardarEstadoMesas() {
            localStorage.setItem('mesasConOrden', JSON.stringify(mesasConOrden));
        }

        function seleccionarMesa(numeroMesa) {
            mesaActual = numeroMesa;

            const mesaActualSpan = document.getElementById('mesa-actual');
            if (mesaActualSpan) mesaActualSpan.textContent = `Mesa ${numeroMesa}`;

            marcarSeleccion(numeroMesa);
            actualizarBotones();
        }



        // Pinta la mesa como "Con orden" o "Disponible"
        function actualizarEstadoMesa(numeroMesa) {
            const card = document.querySelector(`.mesa-card[data-mesa="${numeroMesa}"]`);
            if (!card) return;

            const tieneOrden = !!mesasConOrden[numeroMesa];

            card.classList.remove("disponible", "con-orden");
            card.classList.add(tieneOrden ? "con-orden" : "disponible");

            card.innerHTML = `
    <div class="label">
      Mesa ${numeroMesa}
      <span class="sub">${tieneOrden ? "Con orden" : "Disponible"}</span>
    </div>
    ${tieneOrden ? `<button class="btn-entregar">Entregar orden</button>` : ``}
  `;

            // Si esta mesa es la actual, mantené borde
            if (String(mesaActual) === String(numeroMesa)) {
                card.classList.add("seleccionada");
            }
        }


        function mostrarProductos(categoria) {
            const menuView = document.getElementById('menu-view');
            const mesasView = document.getElementById('mesas-view');
            const productosGrid = document.getElementById('productos-grid');
            const menuTitle = document.getElementById('menu-title');

            if (!menuView || !mesasView || !productosGrid || !menuTitle) return;

            mesasView.classList.add('hidden');
            menuView.classList.remove('hidden');

            const titulos = {
                cafes: 'Cafés',
                comidas: 'Comidas',
                especialidades: 'Especialidades',
                postres: 'Postres',
                bebidas: 'Bebidas Frías'
            };

            menuTitle.textContent = titulos[categoria] || 'Menú';

            productosGrid.innerHTML = productos[categoria].map(producto => `
        <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200">
            <div class="text-center">
                <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas ${producto.icono} text-white text-xl"></i>
                </div>
                <h3 class="text-brown font-semibold text-lg mb-2">${producto.nombre}</h3>
                <p class="text-mint font-bold text-xl mb-4">$${producto.precio.toLocaleString()}</p>
                <button onclick="agregarProducto('${producto.nombre}', ${producto.precio})" 
                        class="w-full custom-mint text-white py-2 rounded-lg hover-mint-bg transition-all duration-200 flex items-center justify-center">
                    <i class="fas fa-plus mr-2"></i>
                    Agregar
                </button>
            </div>
        </div>
    `).join('');
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
                    <p class="text-mint text-sm">$${item.precio.toLocaleString()} c/u</p>
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
            totalElement.textContent = `$${totalOrden.toLocaleString()}`;
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

                    // Cambiar el estado local
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

            //  Validaciones
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

            // Confirmación (equivalente a confirm + return)
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

            // Construir texto de productos para cocina
            const listaProductos = ordenActual
                .map(item => `${item.nombre} x${item.cantidad}`)
                .join("\n");

            //  Crear estructura de la orden
            const data = {
                mesa: mesaActual,
                items: listaProductos
            };

            try {
                // Loader mientras se envía
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

                // Si el servidor no responde OK HTTP, forzamos error
                if (!respuesta.ok) {
                    throw new Error(`HTTP ${respuesta.status}`);
                }

                const result = await respuesta.json();

                if (result.status === "OK") {

                    //  Marcar la mesa como con orden
                    mesasConOrden[mesaActual] = true;
                    guardarEstadoMesas();

                    // Cerrar loader y mostrar éxito (IMPORTANTE: await)
                    await Swal.fire({
                        position: "center",
                        icon: "success",
                        html: `Orden enviada a cocina para <strong>Mesa ${mesaActual}</strong> ✔`,
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: false
                    });

                    // Reset total de orden
                    ordenActual = [];
                    mesaActual = null;

                    const mesaActualSpan = document.getElementById("mesa-actual");
                    if (mesaActualSpan) mesaActualSpan.textContent = "No seleccionada";

                    actualizarOrden();
                    actualizarBotones();

                    // Redirección al final (para que no se corte el Swal)
                    //window.location.href = "test.php";
                    window.location.href = "index.php";

                } else {
                    // Cerrar loader y mostrar error
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
        // 1) Definición del “plano”: posición y tamaño por mesa (x,y,w,h en % del croquis)
        let planoMesas = []; // se llena con DB
        const zonaActual = "main";

        async function cargarLayout() {
            const res = await fetch(`../controller/mesas_layout_get.php?zona=${encodeURIComponent(zonaActual)}`);
            const json = await res.json();
            if (json.status !== "OK") throw new Error("No layout");
            planoMesas = json.data;
        }


        // 2) Renderiza las mesas dentro del croquis
        async function renderCroquis() {
            const croquis = document.getElementById("croquis");
            if (!croquis) return;

            // carga layout desde DB (una vez)
            if (!planoMesas.length) await cargarLayout();

            croquis.innerHTML = `<div class="absolute left-4 top-4 text-xs text-brown/60">Entrada</div>`;

            planoMesas.forEach(m => {
                const div = document.createElement("div");
                div.className = "mesa mesa-card";
                div.dataset.mesa = String(m.id);

                div.style.left = `${m.x}%`;
                div.style.top = `${m.y}%`;
                div.style.width = `${m.w}%`;
                div.style.height = `${m.h}%`;

                croquis.appendChild(div);
                actualizarEstadoMesa(m.id);
            });

            if (mesaActual) marcarSeleccion(mesaActual);
        }
        let drag = {
            active: false,
            id: null,
            startX: 0,
            startY: 0,
            origX: 0,
            origY: 0
        };

        function percentFromPixel(px, totalPx) {
            return (px / totalPx) * 100;
        }

        function clamp(v, min, max) {
            return Math.max(min, Math.min(max, v));
        }

        async function guardarMesaEnDB(id) {
            const mesa = planoMesas.find(m => String(m.id) === String(id));
            if (!mesa) return;

            const res = await fetch("../controller/save_mesa_layoutController.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    id: mesa.id,
                    x: mesa.x,
                    y: mesa.y,
                    w: mesa.w,
                    h: mesa.h,
                    zona: zonaActual
                })
            });

            const json = await res.json();
            if (json.status !== "OK") throw new Error(json.message || "No guardó");
        }

        // Inicia drag
        function onPointerDownMesa(e, card) {
            // si presiona botón entregar, no drag
            if (e.target.closest(".btn-entregar")) return;

            e.preventDefault();

            const id = card.dataset.mesa;
            const croquis = document.getElementById("croquis");
            const rect = croquis.getBoundingClientRect();

            const mesa = planoMesas.find(m => String(m.id) === String(id));
            if (!mesa) return;

            drag.active = true;
            drag.id = id;
            drag.startX = (e.touches ? e.touches[0].clientX : e.clientX);
            drag.startY = (e.touches ? e.touches[0].clientY : e.clientY);
            drag.origX = mesa.x;
            drag.origY = mesa.y;

            marcarSeleccion(id);
            seleccionarMesa(id);
        }

        // Mueve
        function onPointerMove(e) {
            if (!drag.active) return;

            const croquis = document.getElementById("croquis");
            const rect = croquis.getBoundingClientRect();

            const clientX = (e.touches ? e.touches[0].clientX : e.clientX);
            const clientY = (e.touches ? e.touches[0].clientY : e.clientY);

            const dx = clientX - drag.startX;
            const dy = clientY - drag.startY;

            const dxPct = percentFromPixel(dx, rect.width);
            const dyPct = percentFromPixel(dy, rect.height);

            const mesa = planoMesas.find(m => String(m.id) === String(drag.id));
            if (!mesa) return;

            // límites para que no se salga (considerando w/h)
            const maxX = 100 - mesa.w;
            const maxY = 100 - mesa.h;

            mesa.x = clamp(drag.origX + dxPct, 0, maxX);
            mesa.y = clamp(drag.origY + dyPct, 0, maxY);

            // aplica visual inmediato
            const card = document.querySelector(`.mesa-card[data-mesa="${drag.id}"]`);
            if (card) {
                card.style.left = `${mesa.x}%`;
                card.style.top = `${mesa.y}%`;
            }
        }

        // Suelta + guarda
        async function onPointerUp() {
            if (!drag.active) return;
            const id = drag.id;

            drag.active = false;
            drag.id = null;

            try {
                await guardarMesaEnDB(id);
                // opcional: toast
                // console.log("guardado");
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "No se pudo guardar la posición"
                });
            }
        }




        // 3) Marca visualmente la seleccionada 
        function marcarSeleccion(numeroMesa) {
            document.querySelectorAll('.mesa-card').forEach(card => card.classList.remove('seleccionada'));
            const cardSeleccionada = document.querySelector(`.mesa-card[data-mesa="${numeroMesa}"]`);
            if (cardSeleccionada) cardSeleccionada.classList.add('seleccionada');
        }


        // Cuando todo el DOM esté cargado
        document.addEventListener('DOMContentLoaded', () => {
            // Navbar
            const mesasBtn = document.getElementById('mesas-btn');
            const cafesBtn = document.getElementById('cafes-btn');
            const comidasBtn = document.getElementById('comidas-btn');
            const especialidadesBtn = document.getElementById('especialidades-btn');
            const postresBtn = document.getElementById('postres-btn');
            const bebidasBtn = document.getElementById('bebidas-btn');

            if (mesasBtn) {
                mesasBtn.addEventListener('click', () => {
                    mostrarMesas();
                    actualizarNavbar(mesasBtn);
                });
            }
            if (cafesBtn) {
                cafesBtn.addEventListener('click', () => {
                    mostrarProductos('cafes');
                    actualizarNavbar(cafesBtn);
                });
            }
            if (comidasBtn) {
                comidasBtn.addEventListener('click', () => {
                    mostrarProductos('comidas');
                    actualizarNavbar(comidasBtn);
                });
            }
            if (especialidadesBtn) {
                especialidadesBtn.addEventListener('click', () => {
                    mostrarProductos('especialidades');
                    actualizarNavbar(especialidadesBtn);
                });
            }
            if (postresBtn) {
                postresBtn.addEventListener('click', () => {
                    mostrarProductos('postres');
                    actualizarNavbar(postresBtn);
                });
            }
            if (bebidasBtn) {
                bebidasBtn.addEventListener('click', () => {
                    mostrarProductos('bebidas');
                    actualizarNavbar(bebidasBtn);
                });
            }

            // Render inicial del croquis
            renderCroquis();

            // Click en mesas (delegado)
            document.getElementById("croquis")?.addEventListener("click", (e) => {
                const card = e.target.closest(".mesa-card");
                if (!card) return;
                const mesa = card.getAttribute("data-mesa");
                if (!mesa) return;

                // Si clickeó el botón entregar, NO seleccionar, solo entregar
                if (e.target.closest(".btn-entregar")) {
                    entregarOrden(mesa);
                    return;
                }

                seleccionarMesa(mesa);
            });

            // Botones sidebar
            const eliminarBtn = document.getElementById('eliminar-orden');
            const enviarBtn = document.getElementById('enviar-cocina');
            if (eliminarBtn) eliminarBtn.addEventListener('click', eliminarOrden);
            if (enviarBtn) enviarBtn.addEventListener('click', enviarCocina);

            // Pintar estados guardados
            Object.keys(mesasConOrden).forEach(numMesa => {
                if (mesasConOrden[numMesa]) actualizarEstadoMesa(numMesa);
            });

            actualizarOrden();
            actualizarBotones();
        });

        // Botones del sidebar
        const eliminarBtn = document.getElementById('eliminar-orden');
        const enviarBtn = document.getElementById('enviar-cocina');
        if (eliminarBtn) eliminarBtn.addEventListener('click', eliminarOrden);
        if (enviarBtn) enviarBtn.addEventListener('click', enviarCocina);

        // Aplicar estado de mesas guardado
        Object.keys(mesasConOrden).forEach(numMesa => {
            if (mesasConOrden[numMesa]) {
                actualizarEstadoMesa(numMesa);
            }
        });
        // Delegación — detecta clicks en botones de entregar orden
        document.addEventListener('click', function(e) {

            const boton = e.target.closest('.btn-entregar');
            const card = e.target.closest('.mesa-card');

            // Si no hizo click dentro de una mesa, salir
            if (!boton || !card) return;

            // Obtener número de mesa
            const mesa = card.getAttribute('data-mesa');

            if (!mesa) return;

            // Ejecutar acción
            entregarOrden(mesa);
        });


        //document.getElementById('eliminar-orden').addEventListener('click', eliminarOrden);
        document.getElementById('enviar-cocina').addEventListener('click', enviarCocina);
    </script>


</body>

</html>