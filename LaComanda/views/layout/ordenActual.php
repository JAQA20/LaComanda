<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <script src="https://cdn.tailwindcss.com"></script>
    <script> window.FontAwesomeConfig = { autoReplaceSvg: 'nest'};</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">
    <title>Document</title>
</head>
<body>
        <aside id="sidebar" class="w-1/4 custom-beige p-6 border-r border-gray-200 shadow-sm">
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h2 class="text-brown text-xl font-semibold mb-4 flex items-center">
                <i class="fas fa-receipt mr-2"></i>
                Orden actual
            </h2>
            
            <div class="mb-4">
                <span class="text-brown font-medium">Mesa: </span>
                <span id="mesa-actual" class="text-mint font-semibold">No seleccionada</span>
            </div>
            
            <div id="orden-items" class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                <div class="text-gray-500 text-center py-8">
                    <i class="fas fa-coffee text-4xl mb-2"></i>
                    <p>Selecciona una mesa y agrega productos</p>
                </div>
            </div>
            
            <div class="border-t pt-4">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-brown font-semibold">Total:</span>
                    <span id="total-orden" class="text-brown font-bold text-lg">$0.00</span>
                </div>
                
                <div class="space-y-2">
                    <button id="eliminar-orden" class="w-full py-2 text-brown border border-brown rounded-lg hover:bg-brown hover:text-beige transition-all duration-200 disabled:opacity-50" disabled>
                        Eliminar orden
                    </button>
                    <button id="enviar-cocina" class="w-full py-3 custom-mint text-white rounded-lg hover-mint-bg transition-all duration-200 font-medium disabled:opacity-50" disabled>
                        Enviar a cocina
                    </button>
                </div>
            </div>
        </div>
    </aside>
</body>
</html>