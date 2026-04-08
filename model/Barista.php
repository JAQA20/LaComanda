<?php
require_once __DIR__ . "/Conexion.php";

// Este archivo reemplaza el Barista.php legacy que usaba PDO, tablas inexistentes
// y el diseño viejo del proyecto. A partir de ahora este es el módulo oficial de barista.

class Barista
{
    // Reescribí completo este modelo para que ahora sí sea el módulo propio de barista.
    // Ya no usa PDO, ya no usa tablas viejas, ya no asume id_estado global ni items_text.
    // Todo se calcula desde MySQL con detalle_orden y categorías de bebidas.

    private static function conexion(): mysqli
    {
        return Conexion::conectar();
    }

    private static function queryBase(): string
    {
        return "
            SELECT
                o.id_orden,
                o.numero_orden,
                m.numero AS mesa_numero,
                o.notas,
                o.fecha_creacion,
                o.fecha_entrega,
                d.id_detalle,
                d.cantidad,
                d.estado_item,
                d.fecha_inicio_preparacion,
                d.fecha_lista,
                d.fecha_entrega AS detalle_fecha_entrega,
                p.nombre AS producto_nombre,
                c.slug AS categoria_slug
            FROM ordenes o
            INNER JOIN mesas m ON m.id = o.mesa_id
            INNER JOIN detalle_orden d ON d.id_orden = o.id_orden
            INNER JOIN productos p ON p.id = d.id_producto
            INNER JOIN categorias c ON c.id = p.categoria_id
            WHERE c.slug IN ('cafes', 'bebidas')
            ORDER BY o.fecha_creacion DESC, d.id_detalle ASC
        ";
    }

    private static function agruparOrdenes(mysqli_result $result): array
    {
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
                    'hora_lista' => null,
                    'items' => [],
                    'estados' => [],
                ];
            }

            $fechaLista = $row['fecha_lista'] ? date('H:i', strtotime((string)$row['fecha_lista'])) : null;
            if ($fechaLista !== null) {
                $ordenes[$idOrden]['hora_lista'] = $fechaLista;
            }

            $ordenes[$idOrden]['items'][] = [
                'detalle_id' => (int)$row['id_detalle'],
                'nombre' => (string)$row['producto_nombre'],
                'cantidad' => (int)$row['cantidad'],
                'estado_item' => (string)$row['estado_item'],
            ];
            $ordenes[$idOrden]['estados'][] = (string)$row['estado_item'];
        }

        return $ordenes;
    }

    private static function clasificarOrden(array $orden): array
    {
        $estados = $orden['estados'] ?? [];
        $todosEntregados = !empty($estados) && count(array_filter($estados, fn($e) => $e === 'entregado')) === count($estados);
        $todosListosOEntregados = !empty($estados) && count(array_filter($estados, fn($e) => in_array($e, ['listo', 'entregado'], true))) === count($estados);
        $algunoPreparacion = count(array_filter($estados, fn($e) => $e === 'en_preparacion')) > 0;

        if ($todosEntregados) {
            $orden['estado'] = 'entregada';
        } elseif ($todosListosOEntregados) {
            $orden['estado'] = 'lista';
        } else {
            $orden['estado'] = $algunoPreparacion ? 'en_preparacion' : 'pendiente';
        }

        unset($orden['estados']);
        return $orden;
    }

    public static function obtenerPanel(): array
    {
        // Método principal para la pantalla de barista.
        // Devuelve pendientes y listas ya clasificados para que la vista no haga lógica pesada.
        $conexion = self::conexion();
        $result = $conexion->query(self::queryBase());
        $ordenes = self::agruparOrdenes($result);

        $pendientes = [];
        $entregadas = [];

        foreach ($ordenes as $orden) {
            $orden = self::clasificarOrden($orden);
            if (in_array(($orden['estado'] ?? ''), ['pendiente', 'en_preparacion', 'lista'], true)) {
                $pendientes[] = $orden;
            } elseif (($orden['estado'] ?? '') === 'entregada') {
                $entregadas[] = $orden;
            }
        }

        usort($pendientes, fn($a, $b) => $b['numero'] <=> $a['numero']);
        usort($entregadas, fn($a, $b) => $b['numero'] <=> $a['numero']);

        return [
            'pendientes' => $pendientes,
            'listas' => array_slice($entregadas, 0, 20),
        ];
    }

    // Devuelve la misma estructura visual de cocina para reutilizar la pantalla,
    // pero filtrando solo ítems del área barista.
    public static function obtenerOrdenesVista(): array
    {
        $panel = self::obtenerPanel();
        return array_merge($panel['pendientes'], $panel['listas']);
    }

    public static function marcarEnPreparacion(int $numeroOrden): void
    {
        $conexion = self::conexion();
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
                   AND c.slug IN ('cafes', 'bebidas')
                   AND d.estado_item = 'pendiente'"
            );
            $stmt->bind_param('si', $fechaAhora, $numeroOrden);
            $stmt->execute();
            $conexion->commit();
        } catch (Throwable $e) {
            $conexion->rollback();
            throw $e;
        }
    }

    public static function marcarLista(int $numeroOrden): void
    {
        $conexion = self::conexion();
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
                   AND c.slug IN ('cafes', 'bebidas')
                   AND d.estado_item IN ('pendiente', 'en_preparacion')"
            );
            $stmt->bind_param('ssi', $fechaAhora, $fechaAhora, $numeroOrden);
            $stmt->execute();
            $conexion->commit();
        } catch (Throwable $e) {
            $conexion->rollback();
            throw $e;
        }
    }
}
