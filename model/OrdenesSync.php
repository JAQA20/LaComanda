<?php
require_once __DIR__ . "/Conexion.php";
require_once __DIR__ . "/../config/text.php";

class OrdenesSync
{
    // Este modelo deja de sincronizar JSON -> MySQL.
    // A partir de aquí la orden se crea directamente en la base de datos
    // usando el schema nuevo: ordenes como cabecera y detalle_orden como
    // fuente real del estado por ítem.

    private static function normalizarTexto($texto)
    {
        return app_normalize_text($texto);
    }

    private static function resolverMesaId(mysqli $conexion, int $mesaNumero): int
    {
        $stmt = $conexion->prepare("SELECT id FROM mesas WHERE numero = ? LIMIT 1");
        $stmt->bind_param("i", $mesaNumero);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row && isset($row['id'])) {
            return (int)$row['id'];
        }

        throw new RuntimeException("La mesa {$mesaNumero} no existe en la base de datos.");
    }

    private static function parsearItems(string $itemsTexto): array
    {
        $itemsTexto = self::normalizarTexto($itemsTexto);
        $lineas = preg_split('/\r\n|\r|\n/', $itemsTexto);
        $salida = [];

        foreach ($lineas as $linea) {
            $linea = trim((string)$linea);
            if ($linea === '' || str_starts_with($linea, '-')) {
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

    private static function claveProducto(string $nombre): string
    {
        $nombre = self::normalizarTexto($nombre);
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

    private static function mapaProductos(mysqli $conexion): array
    {
        $res = $conexion->query("SELECT id, nombre, precio FROM productos WHERE activo = 1");
        $mapa = [];

        while ($row = $res->fetch_assoc()) {
            $nombre = self::normalizarTexto((string)$row['nombre']);
            $key = mb_strtolower($nombre, 'UTF-8');
            $payload = [
                'id' => (int)$row['id'],
                'precio' => (float)$row['precio'],
            ];
            $mapa[$key] = $payload;

            $keyFlexible = self::claveProducto($nombre);
            if ($keyFlexible !== '' && !isset($mapa[$keyFlexible])) {
                $mapa[$keyFlexible] = $payload;
            }
        }

        return $mapa;
    }

    private static function siguienteNumeroOrden(mysqli $conexion): int
    {
        $row = $conexion->query("SELECT COALESCE(MAX(numero_orden), 0) + 1 AS siguiente FROM ordenes")
            ->fetch_assoc();
        return (int)($row['siguiente'] ?? 1);
    }

    // guardarEnBase ahora crea la cabecera y los detalles directamente en MySQL.
    // Devuelve el número de orden generado para que el controlador lo responda al frontend.
    public static function guardarEnBase(array $orden): int
    {
        $conexion = Conexion::conectar();
        $conexion->begin_transaction();

        try {
            $mesaNumero = (int)($orden['mesa'] ?? 0);
            if ($mesaNumero <= 0) {
                throw new RuntimeException('Mesa inválida.');
            }

            $mesaId = self::resolverMesaId($conexion, $mesaNumero);
            $numeroOrden = self::siguienteNumeroOrden($conexion);
            $fechaCreacion = date('Y-m-d H:i:s');
            $usuarioId = isset($orden['usuario_id']) ? (int)$orden['usuario_id'] : null;
            $notas = self::normalizarTexto((string)($orden['notas'] ?? ''));
            $itemsTexto = self::normalizarTexto((string)($orden['items'] ?? ''));
            $items = self::parsearItems($itemsTexto);

            if (count($items) === 0) {
                throw new RuntimeException('La orden no contiene productos válidos.');
            }

            $mapa = self::mapaProductos($conexion);
            $detalles = [];
            $total = 0.0;

            foreach ($items as $item) {
                $key = mb_strtolower($item['nombre'], 'UTF-8');
                if (!isset($mapa[$key])) {
                    $key = self::claveProducto($item['nombre']);
                }

                if (!isset($mapa[$key])) {
                    throw new RuntimeException('Producto no encontrado en catálogo: ' . $item['nombre']);
                }

                $producto = $mapa[$key];
                $cantidad = (int)$item['cantidad'];
                $precio = (float)$producto['precio'];
                $subtotal = $cantidad * $precio;
                $total += $subtotal;

                $detalles[] = [
                    'id_producto' => (int)$producto['id'],
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                ];
            }

            $stmtOrden = $conexion->prepare(
                "INSERT INTO ordenes (numero_orden, mesa_id, id_usuario, notas, total, fecha_creacion)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmtOrden->bind_param(
                "iiisds",
                $numeroOrden,
                $mesaId,
                $usuarioId,
                $notas,
                $total,
                $fechaCreacion
            );
            $stmtOrden->execute();

            $idOrden = (int)$conexion->insert_id;
            if ($idOrden <= 0) {
                throw new RuntimeException('No se pudo crear la cabecera de la orden.');
            }

            $stmtDetalle = $conexion->prepare(
                "INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario, estado_item)
                 VALUES (?, ?, ?, ?, 'pendiente')"
            );

            foreach ($detalles as $detalle) {
                $idProducto = (int)$detalle['id_producto'];
                $cantidad = (int)$detalle['cantidad'];
                $precio = (float)$detalle['precio'];
                $stmtDetalle->bind_param("iiid", $idOrden, $idProducto, $cantidad, $precio);
                $stmtDetalle->execute();
            }

            // Se marca la mesa como ocupada apenas la orden queda creada.
            $stmtMesa = $conexion->prepare("UPDATE mesas SET estado = 'ocupada' WHERE id = ?");
            $stmtMesa->bind_param("i", $mesaId);
            $stmtMesa->execute();

            $conexion->commit();
            return $numeroOrden;
        } catch (Throwable $e) {
            $conexion->rollback();
            throw $e;
        }
    }

    // Consultas auxiliares para cocina. Se agrupan items por orden y se calcula
    // un estado visible para la pantalla de cocina usando solo productos del área cocina.
    public static function obtenerOrdenesCocina(): array
    {
        $conexion = Conexion::conectar();
        $sql = "
            SELECT
                o.id_orden,
                o.numero_orden,
                m.numero AS mesa_numero,
                o.notas,
                o.fecha_entrega,
                d.id_detalle,
                d.cantidad,
                d.estado_item,
                p.nombre AS producto_nombre,
                c.slug AS categoria_slug
            FROM ordenes o
            INNER JOIN mesas m ON m.id = o.mesa_id
            INNER JOIN detalle_orden d ON d.id_orden = o.id_orden
            INNER JOIN productos p ON p.id = d.id_producto
            INNER JOIN categorias c ON c.id = p.categoria_id
            WHERE c.slug NOT IN ('cafes', 'bebidas', 'mesas')
            ORDER BY o.fecha_creacion DESC, d.id_detalle ASC
        ";

        $result = $conexion->query($sql);
        $ordenes = [];

        while ($row = $result->fetch_assoc()) {
            $idOrden = (int)$row['id_orden'];
            if (!isset($ordenes[$idOrden])) {
                $ordenes[$idOrden] = [
                    'id_orden' => $idOrden,
                    'numero' => (int)$row['numero_orden'],
                    'mesa' => (string)$row['mesa_numero'],
                    'notas' => (string)($row['notas'] ?? ''),
                    'hora_entrega' => $row['fecha_entrega'] ? date('H:i', strtotime((string)$row['fecha_entrega'])) : null,
                    'items' => [],
                    'estados' => [],
                ];
            }

            $ordenes[$idOrden]['items'][] = [
                'detalle_id' => (int)$row['id_detalle'],
                'nombre' => (string)$row['producto_nombre'],
                'cantidad' => (int)$row['cantidad'],
                'estado_item' => (string)$row['estado_item'],
            ];
            $ordenes[$idOrden]['estados'][] = (string)$row['estado_item'];
        }

        $salida = [];
        foreach ($ordenes as $orden) {
            $estados = $orden['estados'];
            $estado = 'pendiente';
            if (!empty($estados)) {
                $todosEntregados = count(array_filter($estados, fn($e) => $e === 'entregado')) === count($estados);
                $todosListosOEntregados = count(array_filter($estados, fn($e) => in_array($e, ['listo', 'entregado'], true))) === count($estados);
                $algunoPreparacion = count(array_filter($estados, fn($e) => $e === 'en_preparacion')) > 0;
                if ($todosEntregados) {
                    $estado = 'entregada';
                } elseif ($todosListosOEntregados) {
                    $estado = 'lista';
                } elseif ($algunoPreparacion) {
                    $estado = 'en_preparacion';
                }
            }

            $itemsTexto = array_map(
                fn($item) => $item['nombre'] . ' x' . $item['cantidad'],
                $orden['items']
            );

            $salida[] = [
                'id_orden' => $orden['id_orden'],
                'numero' => $orden['numero'],
                'mesa' => $orden['mesa'],
                'notas' => $orden['notas'],
                'hora_entrega' => $orden['hora_entrega'],
                'estado' => $estado,
                'items' => implode("\n", $itemsTexto),
                'items_detalle' => $orden['items'],
            ];
        }

        usort($salida, fn($a, $b) => $b['numero'] <=> $a['numero']);
        return $salida;
    }

    // Cocina ahora sigue el mismo flujo de barista:
    // pendiente -> en_preparacion -> lista -> entregada por mesero.
    public static function marcarOrdenCocinaEnPreparacion(int $numeroOrden): void
    {
        $conexion = Conexion::conectar();
        $conexion->begin_transaction();

        try {
            $fechaAhora = date('Y-m-d H:i:s');
            $stmt = $conexion->prepare(
                "UPDATE detalle_orden d
                 INNER JOIN ordenes o ON o.id_orden = d.id_orden
                 INNER JOIN productos p ON p.id = d.id_producto
                 INNER JOIN categorias c ON c.id = p.categoria_id
                 SET d.estado_item = 'en_preparacion',
                     d.fecha_inicio_preparacion = COALESCE(d.fecha_inicio_preparacion, ?)
                 WHERE o.numero_orden = ?
                   AND c.slug NOT IN ('cafes', 'bebidas', 'mesas')
                   AND d.estado_item = 'pendiente'"
            );
            $stmt->bind_param("si", $fechaAhora, $numeroOrden);
            $stmt->execute();
            $conexion->commit();
        } catch (Throwable $e) {
            $conexion->rollback();
            throw $e;
        }
    }

    public static function marcarOrdenCocinaLista(int $numeroOrden): void
    {
        $conexion = Conexion::conectar();
        $conexion->begin_transaction();

        try {
            $fechaAhora = date('Y-m-d H:i:s');
            $stmt = $conexion->prepare(
                "UPDATE detalle_orden d
                 INNER JOIN ordenes o ON o.id_orden = d.id_orden
                 INNER JOIN productos p ON p.id = d.id_producto
                 INNER JOIN categorias c ON c.id = p.categoria_id
                 SET d.estado_item = 'listo',
                     d.fecha_lista = ?,
                     d.fecha_inicio_preparacion = COALESCE(d.fecha_inicio_preparacion, ?)
                 WHERE o.numero_orden = ?
                   AND c.slug NOT IN ('cafes', 'bebidas', 'mesas')
                   AND d.estado_item IN ('pendiente', 'en_preparacion')"
            );
            $stmt->bind_param("ssi", $fechaAhora, $fechaAhora, $numeroOrden);
            $stmt->execute();

            $stmtCab = $conexion->prepare("UPDATE ordenes SET fecha_lista = ? WHERE numero_orden = ?");
            $stmtCab->bind_param("si", $fechaAhora, $numeroOrden);
            $stmtCab->execute();

            $conexion->commit();
        } catch (Throwable $e) {
            $conexion->rollback();
            throw $e;
        }
    }

    // La entrega sigue siendo a nivel de orden completa. Esto nos permite mantener
    // el flujo actual mientras luego migramos barista y la entrega final completa.
    public static function marcarOrdenEntregada(int $numeroOrden): void
    {
        $conexion = Conexion::conectar();
        $conexion->begin_transaction();

        try {
            $fechaAhora = date('Y-m-d H:i:s');
            $stmtOrdenId = $conexion->prepare("SELECT id_orden, mesa_id FROM ordenes WHERE numero_orden = ? LIMIT 1");
            $stmtOrdenId->bind_param("i", $numeroOrden);
            $stmtOrdenId->execute();
            $row = $stmtOrdenId->get_result()->fetch_assoc();

            if (!$row) {
                throw new RuntimeException('Orden no encontrada.');
            }

            $idOrden = (int)$row['id_orden'];
            $mesaId = (int)$row['mesa_id'];

            $stmtDetalles = $conexion->prepare(
                "UPDATE detalle_orden
                 SET estado_item = 'entregado',
                     fecha_entrega = ?,
                     fecha_lista = COALESCE(fecha_lista, ?),
                     fecha_inicio_preparacion = COALESCE(fecha_inicio_preparacion, ?)
                 WHERE id_orden = ?"
            );
            $stmtDetalles->bind_param("sssi", $fechaAhora, $fechaAhora, $fechaAhora, $idOrden);
            $stmtDetalles->execute();

            $stmtOrden = $conexion->prepare("UPDATE ordenes SET fecha_entrega = ?, fecha_lista = COALESCE(fecha_lista, ?) WHERE id_orden = ?");
            $stmtOrden->bind_param("ssi", $fechaAhora, $fechaAhora, $idOrden);
            $stmtOrden->execute();

            $stmtMesa = $conexion->prepare("UPDATE mesas SET estado = 'disponible' WHERE id = ?");
            $stmtMesa->bind_param("i", $mesaId);
            $stmtMesa->execute();

            $conexion->commit();
        } catch (Throwable $e) {
            $conexion->rollback();
            throw $e;
        }
    }

    // Devuelve el estado visible de las mesas para el mesero actual.
    // El criterio sigue siendo similar al flujo viejo:
    // - pendiente: existe una orden del usuario con items no listos
    // - lista: todos los items de esa orden ya están listos
    public static function obtenerEstadoMesasPorUsuario(?int $usuarioId): array
    {
        if (!$usuarioId) {
            return [];
        }

        $conexion = Conexion::conectar();
        $sql = "
            SELECT
                o.id_orden,
                o.numero_orden,
                m.numero AS mesa_numero,
                c.slug AS categoria_slug,
                d.estado_item
            FROM ordenes o
            INNER JOIN mesas m ON m.id = o.mesa_id
            INNER JOIN detalle_orden d ON d.id_orden = o.id_orden
            INNER JOIN productos p ON p.id = d.id_producto
            INNER JOIN categorias c ON c.id = p.categoria_id
            WHERE o.id_usuario = ?
              AND o.fecha_entrega IS NULL
            ORDER BY o.fecha_creacion DESC, d.id_detalle ASC
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();

        $ordenes = [];
        while ($row = $result->fetch_assoc()) {
            $idOrden = (int)$row['id_orden'];
            if (!isset($ordenes[$idOrden])) {
                $ordenes[$idOrden] = [
                    'mesa' => (string)$row['mesa_numero'],
                    'barista' => [],
                    'cocina' => [],
                ];
            }

            $area = in_array((string)$row['categoria_slug'], ['cafes', 'bebidas'], true) ? 'barista' : 'cocina';
            $ordenes[$idOrden][$area][] = (string)$row['estado_item'];
        }

        $estadoPorMesa = [];
        foreach ($ordenes as $orden) {
            $mesa = $orden['mesa'];
            $estadoBarista = self::resolverEstadoSuborden($orden['barista']);
            $estadoCocina = self::resolverEstadoSuborden($orden['cocina']);
            $general = self::resolverEstadoGeneralMesa($estadoCocina, $estadoBarista);

            if ($general === 'entregada') {
                continue;
            }

            $estadoPorMesa[$mesa] = [
                'general' => $general,
                'cocina' => $estadoCocina,
                'barista' => $estadoBarista,
                // Compatibilidad mínima con el flujo visual previo del mesero.
                'estado' => in_array($general, ['lista', 'parcial_lista'], true) ? 'lista' : 'pendiente',
            ];
        }

        return $estadoPorMesa;
    }

    // Entrega por sub-orden: cocina o barista. La orden general queda entregada
    // solo cuando ambas áreas ya fueron entregadas.
    public static function entregarOrdenPorMesaUsuario(int $mesaNumero, ?int $usuarioId, ?string $area = null): void
    {
        if ($mesaNumero <= 0) {
            throw new RuntimeException('Mesa inválida.');
        }
        if (!$usuarioId) {
            throw new RuntimeException('Usuario inválido.');
        }

        $area = trim((string)($area ?? ''));
        if (!in_array($area, ['cocina', 'barista'], true)) {
            throw new RuntimeException('Área de entrega inválida.');
        }

        $conexion = Conexion::conectar();
        $conexion->begin_transaction();

        try {
            $sql = "
                SELECT o.id_orden, o.numero_orden, o.mesa_id
                FROM ordenes o
                INNER JOIN mesas m ON m.id = o.mesa_id
                WHERE m.numero = ?
                  AND o.id_usuario = ?
                  AND o.fecha_entrega IS NULL
                ORDER BY o.fecha_creacion DESC
                LIMIT 1
            ";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ii", $mesaNumero, $usuarioId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            if (!$row) {
                throw new RuntimeException('No se encontró una orden pendiente para esa mesa.');
            }

            $idOrden = (int)$row['id_orden'];
            $mesaId = (int)$row['mesa_id'];
            $fechaAhora = date('Y-m-d H:i:s');
            $filtroArea = $area === 'barista'
                ? "c.slug IN ('cafes', 'bebidas')"
                : "c.slug NOT IN ('cafes', 'bebidas', 'mesas')";

            $sqlUpdate = "
                UPDATE detalle_orden d
                INNER JOIN productos p ON p.id = d.id_producto
                INNER JOIN categorias c ON c.id = p.categoria_id
                SET d.estado_item = 'entregado',
                    d.fecha_entrega = ?,
                    d.fecha_lista = COALESCE(d.fecha_lista, ?),
                    d.fecha_inicio_preparacion = COALESCE(d.fecha_inicio_preparacion, ?)
                WHERE d.id_orden = ?
                  AND {$filtroArea}
                  AND d.estado_item = 'listo'
            ";

            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->bind_param("sssi", $fechaAhora, $fechaAhora, $fechaAhora, $idOrden);
            $stmtUpdate->execute();

            $stmtEstados = $conexion->prepare("SELECT estado_item FROM detalle_orden WHERE id_orden = ?");
            $stmtEstados->bind_param("i", $idOrden);
            $stmtEstados->execute();
            $resultEstados = $stmtEstados->get_result();
            $estados = [];
            while ($estadoRow = $resultEstados->fetch_assoc()) {
                $estados[] = (string)$estadoRow['estado_item'];
            }

            $todosEntregados = !empty($estados) && count(array_filter($estados, fn($e) => $e === 'entregado')) === count($estados);
            if ($todosEntregados) {
                $stmtOrden = $conexion->prepare("UPDATE ordenes SET fecha_entrega = ?, fecha_lista = COALESCE(fecha_lista, ?) WHERE id_orden = ?");
                $stmtOrden->bind_param("ssi", $fechaAhora, $fechaAhora, $idOrden);
                $stmtOrden->execute();

                $stmtMesa = $conexion->prepare("UPDATE mesas SET estado = 'disponible' WHERE id = ?");
                $stmtMesa->bind_param("i", $mesaId);
                $stmtMesa->execute();
            }

            $conexion->commit();
        } catch (Throwable $e) {
            $conexion->rollback();
            throw $e;
        }
    }

    // Estado visible por área para manejar sub-órdenes de cocina y barista.
    private static function resolverEstadoSuborden(array $estados): ?string
    {
        if (empty($estados)) {
            return null;
        }

        $total = count($estados);
        $entregados = count(array_filter($estados, fn($e) => $e === 'entregado'));
        $listosOEntregados = count(array_filter($estados, fn($e) => in_array($e, ['listo', 'entregado'], true)));
        $preparacion = count(array_filter($estados, fn($e) => $e === 'en_preparacion'));

        if ($entregados === $total) {
            return 'entregada';
        }
        if ($listosOEntregados === $total) {
            return 'lista';
        }
        if ($preparacion > 0) {
            return 'en_preparacion';
        }
        return 'pendiente';
    }

    private static function resolverEstadoGeneralMesa(?string $estadoCocina, ?string $estadoBarista): string
    {
        $subestados = array_values(array_filter([$estadoCocina, $estadoBarista], fn($e) => $e !== null));
        if (empty($subestados)) {
            return 'libre';
        }

        $todosEntregados = count(array_filter($subestados, fn($e) => $e === 'entregada')) === count($subestados);
        $todosListosOEntregados = count(array_filter($subestados, fn($e) => in_array($e, ['lista', 'entregada'], true))) === count($subestados);
        $algunaLista = count(array_filter($subestados, fn($e) => $e === 'lista')) > 0;

        if ($todosEntregados) {
            return 'entregada';
        }
        if ($todosListosOEntregados) {
            return 'lista';
        }
        if ($algunaLista) {
            return 'parcial_lista';
        }
        return 'pendiente';
    }

}
