<?php
require_once __DIR__ . "/../../config/rutas.php";
?>

<body>
    <aside
        id="sidebar"
        class="w-full xl:w-[360px] 2xl:w-[400px] custom-beige xl:sticky xl:top-24 self-start">
        <div
            class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100
               xl:max-h-[calc(100vh-7rem)] flex flex-col">
            <h2 class="text-brown text-xl font-semibold mb-4 flex items-center shrink-0">
                <i class="fas fa-receipt mr-2"></i>
                Orden actual
            </h2>

            <div class="mb-4 shrink-0">
                <span class="text-brown font-medium">Mesa: </span>
                <span id="mesa-actual" class="text-mint font-semibold">No seleccionada</span>
            </div>

            <div
                id="orden-items"
                class="space-y-3 mb-6 flex-1 overflow-y-auto pr-2 min-h-[180px] xl:min-h-0">
                <div class="text-gray-500 text-center py-8">
                    <i class="fas fa-coffee text-4xl mb-2"></i>
                    <p>Selecciona una mesa y agrega productos</p>
                </div>
            </div>

            <div class="border-t pt-4 shrink-0 bg-white">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-brown font-semibold">Total:</span>
                    <span id="total-orden" class="text-brown font-bold text-lg">₡0</span>
                </div>

                <!-- Campo de Notas -->
                <div class="mb-4">
                    <label for="notas-orden" class="block text-sm font-medium text-brown mb-2">
                        <i class="fas fa-sticky-note mr-1"></i>
                        Notas adicionales (opcional)
                    </label>
                    <textarea
                        id="notas-orden"
                        placeholder="Ej: Sin hielo, sin azúcar, alérgico a..."
                        class="w-full p-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:border-mint focus:ring-1 focus:ring-mint resize-none"
                        rows="3"></textarea>
                </div>

                <div class="space-y-2">
                    <button
                        id="eliminar-orden"
                        class="w-full py-2 text-brown border border-brown rounded-xl hover:bg-brown hover:text-beige transition-all duration-200 disabled:opacity-50"
                        disabled>
                        Eliminar orden
                    </button>

                    <button
                        id="enviar-cocina"
                        class="w-full py-3 custom-mint text-white rounded-xl hover-mint-bg transition-all duration-200 font-medium disabled:opacity-50"
                        disabled>
                        Enviar a cocina
                    </button>
                </div>
            </div>
        </div>
    </aside>
</body>