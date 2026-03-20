<?php
require_once __DIR__ . "/Conexion.php";
require_once __DIR__ . "/../config/text.php";

class OrdenesSync
{
    private static $schemaReady = false;

    private static function normalizarTexto($texto)
    {
        return app_normalize_text($texto);
    }

    private static function columnaExiste($conexion, $tabla, $columna)
    {
        $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->bind_param("ss", $tabla, $columna);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return ((int)($row['total'] ?? 0)) > 0;
    }

    private static function indiceExiste($conexion, $tabla, $indice)
    {
        $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
        $stmt->bind_param("ss", $tabla, $indice);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return ((int)($row['total'] ?? 0)) > 0;
    }

    private static function asegurarSchema($conexion)
    {
        if (self::$schemaReady) {
            return;
        }

        if (!self::columnaExiste($conexion, 'ordenes', 'numero_json')) {
            $conexion->query("ALTER TABLE ordenes ADD COLUMN numero_json INT NULL");
        }
        if (!self::indiceExiste($conexion, 'ordenes', 'uniq_numero_json')) {
            $conexion->query("ALTER TABLE ordenes ADD UNIQUE KEY uniq_numero_json (numero_json)");
        }
        if (!self::columnaExiste($conexion, 'ordenes', 'notas')) {
            $conexion->query("ALTER TABLE ordenes ADD COLUMN notas TEXT NULL");
        }
        if (!self::columnaExiste($conexion, 'ordenes', 'items_text')) {
            $conexion->query("ALTER TABLE ordenes ADD COLUMN items_text TEXT NULL");
        }
        if (!self::columnaExiste($conexion, 'ordenes', 'timestamp_unix')) {
            $conexion->query("ALTER TABLE ordenes ADD COLUMN timestamp_unix BIGINT NULL");
        }

        self::$schemaReady = true;
    }

    private static function resolverMesaId($conexion, $mesaNumero)
    {
        $stmt = $conexion->prepare("SELECT id FROM mesas WHERE numero = ? LIMIT 1");
        $stmt->bind_param("i", $mesaNumero);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row && isset($row['id'])) {
            return (int)$row['id'];
        }

        return $mesaNumero > 0 ? $mesaNumero : 1;
    }

    private static function parsearItems($itemsTexto)
    {
        $itemsTexto = self::normalizarTexto($itemsTexto);
        $lineas = preg_split('/\r\n|\r|\n/', $itemsTexto);
        $salida = [];

        foreach ($lineas as $linea) {
            $linea = trim((string)$linea);
            if ($linea === '') {
                continue;
            }

            $nombre = $linea;
            $cantidad = 1;

            if (preg_match('/^(.*?)\s*x\s*(\d+)$/u', $linea, $m)) {
                $nombre = trim($m[1]);
                $cantidad = max(1, (int)$m[2]);
            }

            $salida[] = [
                'nombre' => self::normalizarTexto($nombre),
                'cantidad' => $cantidad,
            ];
        }

        return $salida;
    }

    private static function claveProducto($nombre)
    {
        $nombre = self::normalizarTexto((string)$nombre);
        $nombre = mb_strtolower($nombre, 'UTF-8');

        if (function_exists('iconv')) {
            $sinAcentos = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre);
            if (is_string($sinAcentos) && $sinAcentos !== '') {
                $nombre = $sinAcentos;
            }
        }

        $nombre = preg_replace('/[^a-z0-9]+/i', ' ', $nombre);
        return trim((string)$nombre);
    }

    private static function mapaProductos($conexion)
    {
        $res = $conexion->query("SELECT id, nombre, precio FROM productos");
        $mapa = [];

        while ($row = $res->fetch_assoc()) {
            $nombre = self::normalizarTexto((string)$row['nombre']);
            $key = mb_strtolower($nombre, 'UTF-8');
            $mapa[$key] = [
                'id' => (int)$row['id'],
                'precio' => (float)$row['precio'],
            ];

            $keyFlexible = self::claveProducto($nombre);
            if ($keyFlexible !== '' && !isset($mapa[$keyFlexible])) {
                $mapa[$keyFlexible] = [
                    'id' => (int)$row['id'],
                    'precio' => (float)$row['precio'],
                ];
            }
        }

        return $mapa;
    }

    private static function fechaEntregaDesdeOrden($orden)
    {
        $hora = trim((string)($orden['hora_entrega'] ?? ''));
        if ($hora === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/', $hora) === 1) {
            return $hora;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $hora) === 1) {
            $baseTs = isset($orden['timestamp_entrega']) ? (int)$orden['timestamp_entrega'] : (int)($orden['timestamp'] ?? time());
            return date('Y-m-d', $baseTs) . ' ' . $hora . ':00';
        }

        return null;
    }

    public static function guardarEnBase($orden)
    {
        $conexion = Conexion::conectar();
        self::asegurarSchema($conexion);

        $numero = (int)($orden['numero'] ?? 0);
        if ($numero <= 0) {
            return;
        }

        $mesaNumero = (int)($orden['mesa'] ?? 0);
        $mesaId = self::resolverMesaId($conexion, $mesaNumero);

        $estadoTxt = strtolower(trim((string)($orden['estado'] ?? 'pendiente')));
        $idEstado = $estadoTxt === 'entregada' ? 2 : 1;

        $timestampUnix = (int)($orden['timestamp'] ?? time());
        $fechaOrden = date('Y-m-d H:i:s', $timestampUnix);
        $horaEntrega = self::fechaEntregaDesdeOrden($orden);

        $notas = app_normalize_text((string)($orden['notas'] ?? ''));
        $itemsTexto = app_normalize_text((string)($orden['items'] ?? ''));
        $items = self::parsearItems($itemsTexto);

        $mapa = self::mapaProductos($conexion);
        $total = 0.0;
        $detalles = [];

        foreach ($items as $item) {
            $key = mb_strtolower($item['nombre'], 'UTF-8');
            if (!isset($mapa[$key])) {
                $key = self::claveProducto($item['nombre']);
            }

            if (!isset($mapa[$key])) {
                continue;
            }

            $producto = $mapa[$key];
            $precio = (float)$producto['precio'];
            $cantidad = (int)$item['cantidad'];

            $total += $precio * $cantidad;
            $detalles[] = [
                'id_producto' => (int)$producto['id'],
                'cantidad' => $cantidad,
                'precio' => $precio,
            ];
        }

        $sql = "
            INSERT INTO ordenes
                (numero_json, mesa_id, id_estado, total, id_usuario, timestamp, hora_entrega, notas, items_text, timestamp_unix)
            VALUES
                (?, ?, ?, ?, NULL, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                mesa_id = VALUES(mesa_id),
                id_estado = VALUES(id_estado),
                total = VALUES(total),
                timestamp = VALUES(timestamp),
                hora_entrega = VALUES(hora_entrega),
                notas = VALUES(notas),
                items_text = VALUES(items_text),
                timestamp_unix = VALUES(timestamp_unix)
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "iiidssssi",
            $numero,
            $mesaId,
            $idEstado,
            $total,
            $fechaOrden,
            $horaEntrega,
            $notas,
            $itemsTexto,
            $timestampUnix
        );
        $stmt->execute();

        $stmtId = $conexion->prepare("SELECT id_orden FROM ordenes WHERE numero_json = ? LIMIT 1");
        $stmtId->bind_param("i", $numero);
        $stmtId->execute();
        $row = $stmtId->get_result()->fetch_assoc();
        $idOrden = (int)($row['id_orden'] ?? 0);

        if ($idOrden <= 0) {
            return;
        }

        $stmtDel = $conexion->prepare("DELETE FROM detalle_orden WHERE id_orden = ?");
        $stmtDel->bind_param("i", $idOrden);
        $stmtDel->execute();

        if (count($detalles) === 0) {
            return;
        }

        $stmtDet = $conexion->prepare("INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        foreach ($detalles as $d) {
            $idProducto = (int)$d['id_producto'];
            $cantidad = (int)$d['cantidad'];
            $precio = (float)$d['precio'];
            $stmtDet->bind_param("iiid", $idOrden, $idProducto, $cantidad, $precio);
            $stmtDet->execute();
        }
    }

    public static function marcarEntregadaPorMesa($mesaNumero, $timestampEntrega = null)
    {
        $conexion = Conexion::conectar();
        self::asegurarSchema($conexion);

        $mesaId = self::resolverMesaId($conexion, (int)$mesaNumero);
        $fechaEntrega = date('Y-m-d H:i:s', $timestampEntrega ? (int)$timestampEntrega : time());

        $sql = "
            UPDATE ordenes
            SET id_estado = 2, hora_entrega = ?
            WHERE mesa_id = ? AND id_estado = 1
            ORDER BY id_orden DESC
            LIMIT 1
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $fechaEntrega, $mesaId);
        $stmt->execute();
    }

    public static function marcarEntregadaPorNumero($numeroJson, $timestampEntrega = null)
    {
        $conexion = Conexion::conectar();
        self::asegurarSchema($conexion);

        $fechaEntrega = date('Y-m-d H:i:s', $timestampEntrega ? (int)$timestampEntrega : time());
        $numero = (int)$numeroJson;

        $stmt = $conexion->prepare("UPDATE ordenes SET id_estado = 2, hora_entrega = ? WHERE numero_json = ? LIMIT 1");
        $stmt->bind_param("si", $fechaEntrega, $numero);
        $stmt->execute();
    }
}
