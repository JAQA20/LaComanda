<?php
require_once __DIR__ . "/Conexion.php";

class Logs
{
    public static function addLog($message, $type = 'error', $user_id = null)
    {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("INSERT INTO system_logs (error_message, type, user_id) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssi", $message, $type, $user_id);
                $stmt->execute();
            }
        } catch (Throwable $e) {
            // Silencioso para no romper la app si falla el logger
        }
    }

    public static function getLogs()
    {
        $conexion = Conexion::conectar();
        $query = "SELECT l.*, u.nombre, u.apellido 
                  FROM system_logs l 
                  LEFT JOIN usuarios u ON l.user_id = u.id 
                  ORDER BY l.created_at DESC 
                  LIMIT 100";
        $logs = [];
        try {
            $result = $conexion->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $logs[] = $row;
                }
            }
        } catch (Throwable $e) {
            // Si la tabla no existe, devolveremos un log temporal de aviso
            $logs[] = [
                'id' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 'error',
                'user_id' => null,
                'nombre' => 'Sistema',
                'apellido' => '',
                'error_message' => 'Advertencia: Ocurrió un error al cargar los logs. ' . $e->getMessage()
            ];
        }
        return $logs;
    }
}
