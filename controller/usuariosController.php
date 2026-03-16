<?php
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../model/Usuarios.php";

verificarRol([1]);

header("Content-Type: application/json; charset=utf-8");
echo json_encode(Usuarios::listar(), JSON_UNESCAPED_UNICODE);
exit;
