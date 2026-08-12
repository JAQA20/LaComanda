<?php
require_once __DIR__ . "/../../config/env.php";
require_once __DIR__ . "/../../model/Conexion.php";

header('Content-Type: application/json; charset=utf-8');

try {
    $conexion = Conexion::conectar();

    // Obtener productos sin imagen
    $sql = "SELECT id, nombre FROM productos WHERE imagen IS NULL OR imagen = ''";
    $result = $conexion->query($sql);

    $productosSinImagen = [];
    while ($row = $result->fetch_assoc()) {
        $productosSinImagen[$row['id']] = $row['nombre'];
    }

    if (empty($productosSinImagen)) {
        echo json_encode(['success' => true, 'count' => 0, 'message' => 'Todos los productos ya tienen imagen.']);
        exit;
    }

    // Preparar el prompt para Gemini
    $prompt = "Eres un asistente de restaurantes. Tu tarea es generar un 'prompt' en inglés descriptivo y fotorealista para cada platillo, ideal para un generador de imágenes por IA. Por ejemplo: si recibes 'Hamburguesa Clásica', devuelve 'A delicious classic cheeseburger with lettuce and tomato, professional food photography, restaurant setting'. Si recibes 'Americano', devuelve 'A hot cup of americano coffee on a wooden table, professional food photography'.\n\n";
    $prompt .= "Retorna ÚNICAMENTE un objeto JSON válido donde la llave sea el ID numérico proporcionado y el valor sea el prompt descriptivo. Sin formato markdown ni texto adicional.\n\n";
    $prompt .= "Platillos a traducir:\n" . json_encode($productosSinImagen, JSON_UNESCAPED_UNICODE);

    // Llamar a la API de Gemini (usaremos gemini-3.5-flash)
    $geminiApiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . app_env('GEMINI_API_KEY');

    $requestBody = json_encode([
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.4,
            "responseMimeType" => "application/json"
        ]
    ]);

    $ch = curl_init($geminiApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Error en la API de Gemini: " . $response);
    }

    $geminiData = json_decode($response, true);
    $geminiResponseText = $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? '';

    // Limpiar respuesta (por si acaso viene con markdown)
    $geminiResponseText = str_replace(['```json', '```'], '', trim($geminiResponseText));
    $keywordsMap = json_decode($geminiResponseText, true);

    if (!$keywordsMap || !is_array($keywordsMap)) {
        throw new Exception("La respuesta de Gemini no fue un JSON válido: " . $geminiResponseText);
    }

    // Actualizar la base de datos
    $updateCount = 0;
    $stmt = $conexion->prepare("UPDATE productos SET imagen = ? WHERE id = ?");

    foreach ($keywordsMap as $id => $imagePrompt) {
        // Usamos Pollinations AI para generar una imagen fotorrealista exacta en lugar de fotos de stock al azar
        $seed = rand(1, 100000);
        // Limpiar el prompt de caracteres extraños y armar URL
        $cleanPrompt = preg_replace('/[^a-zA-Z0-9\s,\-]/', '', $imagePrompt);
        $imageUrl = "https://image.pollinations.ai/prompt/" . urlencode($cleanPrompt) . "?width=600&height=400&nologo=true&seed=" . $seed;
        
        $idInt = (int)$id;
        $stmt->bind_param("si", $imageUrl, $idInt);
        
        if ($stmt->execute()) {
            $updateCount++;
        }
    }

    echo json_encode([
        'success' => true,
        'count' => $updateCount
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
