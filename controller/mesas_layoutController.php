 <?php
    header("Content-Type: application/json; charset=utf-8");
    require_once __DIR__ . "/../model/MesaLayout.php";

    $zona = $_GET["zona"] ?? "main";

    try {
        $data = MesaLayoutModel::obtenerPorZona($zona);
        echo json_encode(["status" => "OK", "data" => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "ERROR", "message" => "No se pudo cargar layout"]);
    }
