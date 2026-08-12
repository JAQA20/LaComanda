<?php
ini_set('display_errors', 0);
require_once __DIR__ . "/../../config/env.php";
require_once __DIR__ . "/../../middleware/auth.php";
require_once __DIR__ . "/../../middleware/roles.php";
require_once __DIR__ . "/../../model/Conexion.php";
require_once __DIR__ . "/../../model/Modificadores.php";

verificarRol([1]);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Método no permitido.");
    
    if (!isset($_FILES['menu_pdf']) || $_FILES['menu_pdf']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error al subir el archivo PDF.");
    }

    $apiKey = trim(app_env('GEMINI_API_KEY'));
    if (!$apiKey) {
        throw new Exception("La clave de API de Gemini no está configurada en .env");
    }

    $pdfPath = $_FILES['menu_pdf']['tmp_name'];
    $pdfData = base64_encode(file_get_contents($pdfPath));

    $conexion = Conexion::conectar();
    
    // Extraer categorias existentes
    $catResult = $conexion->query("SELECT nombre FROM categorias ORDER BY orden ASC");
    $categoriasExistentes = [];
    while ($row = $catResult->fetch_assoc()) {
        $categoriasExistentes[] = $row['nombre'];
    }
    
    $promptExtraCategorias = "";
    if (count($categoriasExistentes) > 0) {
        $listaCat = implode(', ', $categoriasExistentes);
        $promptExtraCategorias = "
        IMPORTANTE: Ya existen estas categorías en el sistema: [{$listaCat}].
        Debes clasificar TODOS los productos ÚNICAMENTE en estas categorías existentes. No inventes ni crees categorías nuevas. Si un producto no encaja perfectamente, asígnalo a la categoría más cercana o lógica de esa lista.
        El campo 'nombre' dentro de 'categorias' en tu JSON debe ser EXACTAMENTE uno de los de la lista (respeta mayúsculas).";
    }

    $prompt = "
    Eres un asistente experto para un sistema de punto de venta (POS) de restaurantes.
    Analiza este menú en PDF y extrae los productos, precios y opciones de modificadores (extras, add-ons).
    {$promptExtraCategorias}
    
    IMPORTANTE SOBRE VARIACIONES DE PRODUCTO Y SABORES:
    - Si encuentras el mismo producto en diferentes tamaños, porciones o variaciones (ej. 'Americano Sencillo' y 'Americano Doble', o 'Coca Cola 600ml' y 'Coca Cola 2L'), NO los crees como productos separados. Fusiónalos en UN SOLO producto base (ej. 'Americano' o 'Coca Cola') con el precio de la variante más barata, y asigna un modificador indicando la diferencia de precio.
    - Específicamente para 'Mini Pizza', 'Emparedados' (o Sandwiches) y 'Paninis': extrae un solo producto genérico (ej. 'Mini Pizza') cuyo PRECIO BASE DEBE SER EXÁCTAMENTE IGUAL AL PRECIO DEL SABOR MÁS BARATO. Convierte todos sus sabores en un modificador (ej. Grupo 'Sabores') ajustando el 'precio_extra' de cada sabor respecto al más barato (el sabor más barato debe tener precio_extra: 0).
    
    IMPORTANTE SOBRE MODIFICADORES DUPLICADOS:
    - Si un grupo de modificadores (ej. 'Tipo de Leche') aplica a varias bebidas o categorías, NO LO DUPLIQUES. Créalo UNA SOLA VEZ dentro del arreglo `modificadores_globales` con un `id_temp` único (ej. 'M1').
    - Luego, dentro de las categorías o productos que necesiten ese modificador, simplemente incluye ese 'id_temp' en el arreglo `modificadores_ids`.
    
    Devuelve estrictamente y SOLO un JSON con esta estructura exacta, sin markdown, sin bloques de código:
    {
      \"modificadores_globales\": [
        {
          \"id_temp\": \"M1\",
          \"nombre\": \"Tipo de Leche\",
          \"requerido\": 0,
          \"seleccion_multiple\": 0,
          \"opciones\": [
            { \"nombre\": \"Entera\", \"precio_extra\": 0 },
            { \"nombre\": \"Deslactosada\", \"precio_extra\": 300 }
          ]
        }
      ],
      \"categorias\": [
        {
          \"nombre\": \"Nombre Categoria\",
          \"modificadores_ids\": [\"M1\"],
          \"productos\": [
            { 
              \"nombre\": \"Producto 1\", 
              \"precio\": 1500,
              \"modificadores_ids\": []
            }
          ]
        }
      ]
    }
    
    Instrucciones adicionales:
    - Los precios deben ser números (sin símbolos de moneda).
    - 'requerido' y 'seleccion_multiple' son 1 (verdadero) o 0 (falso).
    - Asegúrate de que el JSON sea perfectamente válido.
    ";

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt],
                    [
                        "inline_data" => [
                            "mime_type" => "application/pdf",
                            "data" => $pdfData
                        ]
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.1,
            "response_mime_type" => "application/json"
        ]
    ];

    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=" . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Error de Gemini API: " . $response);
    }

    $geminiData = json_decode($response, true);
    $responseText = $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    // Limpiar markdown si Gemini lo envía a pesar de las instrucciones
    $responseText = str_replace(['```json', '```'], '', $responseText);
    $menu = json_decode(trim($responseText), true);

    if (!$menu || !isset($menu['categorias'])) {
        throw new Exception("La IA devolvió un formato inválido.");
    }

    $conexion = Conexion::conectar();

    try {
        // 1. Preparar mapa de modificadores globales
        $mapaModificadores = [];
        $productosNuevos = 0;
        $productosActualizados = 0;
        if (isset($menu['modificadores_globales']) && is_array($menu['modificadores_globales'])) {
            foreach ($menu['modificadores_globales'] as $mod) {
                $idTemp = $mod['id_temp'] ?? null;
                if ($idTemp) {
                    $mapaModificadores[$idTemp] = [
                        'nombre' => $mod['nombre'],
                        'requerido' => (int)($mod['requerido'] ?? 0),
                        'seleccion_multiple' => (int)($mod['seleccion_multiple'] ?? 0),
                        'opciones' => [],
                        'categorias' => [],
                        'productos' => []
                    ];
                    if (isset($mod['opciones']) && is_array($mod['opciones'])) {
                        foreach ($mod['opciones'] as $opt) {
                            $mapaModificadores[$idTemp]['opciones'][] = [
                                'nombre' => $opt['nombre'],
                                'precio_adicional' => floatval($opt['precio_extra'] ?? 0)
                            ];
                        }
                    }
                }
            }
        }

        // 2. Procesar Categorias y Productos
        foreach ($menu['categorias'] as $cat) {
            // Insertar o obtener Categoria
            $nombreCat = $cat['nombre'];
            $slug = strtolower(str_replace(' ', '-', $nombreCat));
            
            $stmtCat = $conexion->prepare("INSERT INTO categorias (nombre, slug, icono, orden) VALUES (?, ?, 'fa-utensils', 99) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
            $stmtCat->bind_param("ss", $nombreCat, $slug);
            $stmtCat->execute();
            
            $catIdResult = $conexion->query("SELECT id FROM categorias WHERE nombre = '" . $conexion->real_escape_string($nombreCat) . "' LIMIT 1");
            $catId = $catIdResult->fetch_assoc()['id'];

            // Asignar modificadores a nivel categoría
            if (isset($cat['modificadores_ids']) && is_array($cat['modificadores_ids'])) {
                foreach ($cat['modificadores_ids'] as $idTemp) {
                    if (isset($mapaModificadores[$idTemp])) {
                        $mapaModificadores[$idTemp]['categorias'][] = $catId;
                    }
                }
            }

            // Insertar Productos con verificación Anti-Duplicados
            if (!empty($cat['productos'])) {
                foreach ($cat['productos'] as $prod) {
                    $pNombre = $prod['nombre'];
                    $pPrecio = floatval($prod['precio']);
                    
                    // Verificar si ya existe en esta categoría
                    $stmtCheck = $conexion->prepare("SELECT id FROM productos WHERE categoria_id = ? AND nombre = ?");
                    $stmtCheck->bind_param("is", $catId, $pNombre);
                    $stmtCheck->execute();
                    $resultCheck = $stmtCheck->get_result();
                    
                    if ($resultCheck->num_rows > 0) {
                        // Ya existe, solo actualizar precio (podría ser opcional, pero evitamos insertarlo)
                        $prodId = $resultCheck->fetch_assoc()['id'];
                        $stmtUpdate = $conexion->prepare("UPDATE productos SET precio = ? WHERE id = ?");
                        $stmtUpdate->bind_param("di", $pPrecio, $prodId);
                        $stmtUpdate->execute();
                        $productosActualizados++;
                    } else {
                        // Insertar nuevo producto
                        $stmtProd = $conexion->prepare("INSERT INTO productos (categoria_id, nombre, precio, imagen) VALUES (?, ?, ?, '')");
                        $stmtProd->bind_param("isd", $catId, $pNombre, $pPrecio);
                        $stmtProd->execute();
                        $prodId = $stmtProd->insert_id;
                        $productosNuevos++;
                    }
                    
                    // Asignar modificadores a nivel producto
                    if (isset($prod['modificadores_ids']) && is_array($prod['modificadores_ids'])) {
                        foreach ($prod['modificadores_ids'] as $idTemp) {
                            if (isset($mapaModificadores[$idTemp])) {
                                $mapaModificadores[$idTemp]['productos'][] = $prodId;
                            }
                        }
                    }
                }
            }
        }

        // 3. Crear los Grupos de Modificadores en la Base de Datos
        foreach ($mapaModificadores as $idTemp => $modData) {
            // Solo crear si tiene opciones y fue asignado a al menos 1 categoria o producto
            if (!empty($modData['opciones']) && (!empty($modData['categorias']) || !empty($modData['productos']))) {
                Modificadores::crearGrupo(
                    $modData['nombre'], 
                    $modData['requerido'], 
                    $modData['seleccion_multiple'], 
                    1, 
                    $modData['opciones'], 
                    array_unique($modData['categorias']), 
                    array_unique($modData['productos'])
                );
            }
        }

        if ($productosNuevos === 0 && $productosActualizados > 0) {
            $msg = "Todos los productos del PDF ya existían en el menú. Se actualizaron los precios de $productosActualizados productos.";
        } else {
            $msg = "Se importó el menú con éxito: $productosNuevos productos nuevos agregados y $productosActualizados productos actualizados.";
        }
        
        echo json_encode(["success" => true, "message" => $msg]);
        exit;
        
    } catch (Exception $dbEx) {
        throw $dbEx;
    }

} catch (Exception $e) {
    $errorMsg = $e->getMessage();
    
    // Si el error contiene JSON de la API de Gemini (503, etc)
    if (strpos($errorMsg, '503') !== false || strpos($errorMsg, 'high demand') !== false || strpos($errorMsg, 'UNAVAILABLE') !== false) {
        $friendlyMsg = "Los servidores de Inteligencia Artificial están temporalmente saturados. Por favor, espera unos minutos e inténtalo de nuevo.";
    } elseif (strpos($errorMsg, '{') !== false) {
        $friendlyMsg = "Hubo un error de comunicación con la Inteligencia Artificial. Por favor, inténtalo de nuevo.";
    } else {
        $friendlyMsg = $errorMsg;
    }

    echo json_encode(["success" => false, "message" => $friendlyMsg]);
    exit;
}
