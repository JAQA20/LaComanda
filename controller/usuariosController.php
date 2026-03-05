<?php
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
verificarRol([1]);

require_once __DIR__ . "/../model/Usuarios.php";

header("Content-Type: application/json; charset=utf-8");
echo json_encode(Usuarios::listar(), JSON_UNESCAPED_UNICODE);
exit;
