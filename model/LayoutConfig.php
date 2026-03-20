<?php
require_once __DIR__ . '/Conexion.php';

class LayoutConfig
{
    private const CLAVE_LAYOUT_PRINCIPAL = 'croquis_principal';

    public static function obtenerCroquisPrincipal(): array
    {
        global $conexion;

        self::asegurarTabla($conexion);

        $stmt = $conexion->prepare('SELECT payload_json FROM layout_configs WHERE layout_key = ? LIMIT 1');
        $key = self::CLAVE_LAYOUT_PRINCIPAL;
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$row || empty($row['payload_json'])) {
            return [];
        }

        $data = json_decode((string)$row['payload_json'], true);
        return is_array($data) ? $data : [];
    }

    public static function guardarCroquisPrincipal(array $items, ?int $usuarioId = null): void
    {
        global $conexion;

        self::asegurarTabla($conexion);

        $payload = json_encode($items, JSON_UNESCAPED_UNICODE);
        $key = self::CLAVE_LAYOUT_PRINCIPAL;
        $stmt = $conexion->prepare(
            'INSERT INTO layout_configs (layout_key, payload_json, actualizado_por) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE payload_json = VALUES(payload_json), actualizado_por = VALUES(actualizado_por), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->bind_param('ssi', $key, $payload, $usuarioId);
        $stmt->execute();
        $stmt->close();
    }

    public static function restablecerCroquisPrincipal(?int $usuarioId = null): void
    {
        self::guardarCroquisPrincipal([], $usuarioId);
    }

    private static function asegurarTabla(mysqli $conexion): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        $conexion->query("CREATE TABLE IF NOT EXISTS layout_configs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            layout_key VARCHAR(100) NOT NULL UNIQUE,
            payload_json LONGTEXT NULL,
            actualizado_por INT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_layout_configs_usuario FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $checked = true;
    }
}
