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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">


</head>

<body class="custom-beige min-h-screen">

    <!-- Navbar -->
    <?php
    include './layout/navbar.php';
    ?>

    <!-- Main Content -->
    <div class="flex pt-16 min-h-screen">

        <!-- Sidebar - Orden Actual -->

        <!-- Content Area -->
        <main id="content-area" class="flex-1 p-6">

            <!-- Mesas View -->
            <div id="" class="block">
                <h1 class="text-brown text-3xl font-bold mb-8">Admin</h1>
                <div class="grid grid-cols-4 gap-6">
                    <div class=" bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-user text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Usuarios</h3>
                        </div>
                    </div>
                    <div class="mesa-card bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-mint transition-all duration-200 cursor-pointer" data-mesa="1">
                        <div class="text-center">
                            <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-chart-simple text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-lg">Reportes</h3>
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
            if (mesaActualSpan) {
                mesaActualSpan.textContent = `Mesa ${numeroMesa}`;
            }

            document.querySelectorAll('.mesa-card').forEach(card => {
                card.classList.remove('border-mint');
            });

            const cardSeleccionada = document.querySelector(`[data-mesa="${numeroMesa}"]`);
            if (cardSeleccionada) {
                cardSeleccionada.classList.add('border-mint');
            }

            actualizarBotones();
        }

        // Pinta la mesa como "Con orden" o "Disponible"
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
                alert('Selecciona una mesa primero');
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

        function entregarOrden(numeroMesa) {
            if (!confirm(`¿Marcar la Mesa ${numeroMesa} como entregada?`)) return;

            // Cambiar estado a disponible
            mesasConOrden[numeroMesa] = false;

            // Guardar cambio en localStorage
            guardarEstadoMesas();

            // Actualizar visualmente la mesa
            actualizarEstadoMesa(numeroMesa);

            alert(`La Mesa ${numeroMesa} ahora está disponible`);
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

            if (confirm('¿Estás seguro de eliminar toda la orden?')) {
                ordenActual = [];
                actualizarOrden();
                actualizarBotones();
            }
        }

        function enviarCocina() {
            if (!mesaActual) {
                alert('Selecciona una mesa primero');
                return;
            }

            if (confirm(`¿Enviar orden de Mesa ${mesaActual} a cocina?`)) {

                // Marcar la mesa como con orden y guardar
                mesasConOrden[mesaActual] = true;
                guardarEstadoMesas();

                alert('Orden enviada a cocina exitosamente');

                // Limpiar estado de la orden actual
                ordenActual = [];
                mesaActual = null;
                const mesaActualSpan = document.getElementById('mesa-actual');
                if (mesaActualSpan) {
                    mesaActualSpan.textContent = 'No seleccionada';
                }
                actualizarOrden();
                actualizarBotones();

                // Volver a index (como tú necesitas)
                window.location.href = "index.php";
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

            // Click en mesas (cada tarjeta)
            document.querySelectorAll('.mesa-card').forEach(card => {
                card.addEventListener('click', () => {
                    const mesa = card.getAttribute('data-mesa');
                    if (!mesa) return;
                    seleccionarMesa(mesa);
                });
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

        });

        //document.getElementById('eliminar-orden').addEventListener('click', eliminarOrden);
        //document.getElementById('enviar-cocina').addEventListener('click', enviarCocina);
    </script>


</body>

</html>