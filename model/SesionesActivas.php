<?php
require_once __DIR__ . "/Usuarios.php";

class SesionesActivas
{
    private static function archivo()
    {
        return __DIR__ . "/../controller/sesiones_activas.json";
    }

    private static function archivoHistorial()
    {
        return __DIR__ . "/../controller/sesiones_historial.json";
    }

    private static function leer()
    {
        $archivo = self::archivo();
        if (!file_exists($archivo)) {
            return [];
        }

        $contenido = json_decode(file_get_contents($archivo), true);
        return is_array($contenido) ? $contenido : [];
    }

    private static function escribir($data)
    {
        file_put_contents(self::archivo(), json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private static function leerHistorial()
    {
        $archivo = self::archivoHistorial();
        if (!file_exists($archivo)) {
            return [];
        }

        $contenido = json_decode(file_get_contents($archivo), true);
        return is_array($contenido) ? $contenido : [];
    }

    private static function escribirHistorial($data)
    {
        file_put_contents(self::archivoHistorial(), json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function registrar($usuario)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $sesiones = self::leer();
        $sessionId = session_id();
        $ahora = time();

        $sesiones = array_values(array_filter($sesiones, function ($s) use ($sessionId) {
            return ($s['session_id'] ?? '') !== $sessionId;
        }));

        $sesiones[] = [
            'session_id' => $sessionId,
            'usuario_id' => (int)($usuario['id'] ?? 0),
            'nombre' => trim((string)($usuario['nombre'] ?? '') . ' ' . (string)($usuario['apellido'] ?? '')),
            'email' => (string)($usuario['email'] ?? ''),
            'rol_id' => (int)($usuario['rol_id'] ?? 0),
            'login_at' => $ahora,
            'ultima_actividad' => $ahora,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'pagina_actual' => $_SERVER['REQUEST_URI'] ?? 'N/A',
        ];

        self::escribir($sesiones);
    }

    public static function tocarSesionActual()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $sessionId = session_id();
        $sesiones = self::leer();
        $actualizado = false;

        foreach ($sesiones as &$sesion) {
            if (($sesion['session_id'] ?? '') === $sessionId) {
                $sesion['ultima_actividad'] = time();
                $sesion['ip'] = $_SERVER['REMOTE_ADDR'] ?? ($sesion['ip'] ?? 'N/A');
                $sesion['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? ($sesion['user_agent'] ?? 'N/A');
                $sesion['pagina_actual'] = $_SERVER['REQUEST_URI'] ?? ($sesion['pagina_actual'] ?? 'N/A');
                $actualizado = true;
                break;
            }
        }
        unset($sesion);

        if ($actualizado) {
            self::escribir($sesiones);
        }
    }

    public static function cerrarSesionActual()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $sessionId = session_id();
        $sesiones = self::leer();
        $historial = self::leerHistorial();
        $ahora = time();

        foreach ($sesiones as $sesion) {
            if (($sesion['session_id'] ?? '') === $sessionId) {
                $historial = array_values(array_filter($historial, function ($h) use ($sessionId) {
                    return ($h['session_id'] ?? '') !== $sessionId;
                }));

                $sesion['logout_at'] = $ahora;
                $historial[] = $sesion;
                break;
            }
        }

        $sesiones = array_values(array_filter($sesiones, function ($s) use ($sessionId) {
            return ($s['session_id'] ?? '') !== $sessionId;
        }));

        self::escribir($sesiones);
        self::escribirHistorial($historial);
    }

    public static function listarActivas($ttlSegundos = 1800)
    {
        $ahora = time();
        $sesiones = self::leer();
        $sesionesActivas = [];

        foreach ($sesiones as $sesion) {
            $ultima = (int)($sesion['ultima_actividad'] ?? 0);
            if ($ultima > 0 && ($ahora - $ultima) <= $ttlSegundos) {
                $sesionesActivas[] = $sesion;
            }
        }

        self::escribir($sesionesActivas);
        return $sesionesActivas;
    }

    public static function nombreRol($rolId)
    {
        $roles = [
            1 => 'Admin',
            2 => 'Mesero',
            3 => 'Cocina',
            4 => 'Barista',
        ];

        return $roles[(int)$rolId] ?? ('Rol ' . (int)$rolId);
    }

    public static function formatearHora($timestamp)
    {
        if (empty($timestamp)) {
            return 'N/A';
        }
        return date('d/m/Y h:i A', (int)$timestamp);
    }

    public static function obtenerEstadoActividad($ultimaActividad)
    {
        $ultimaActividad = (int)$ultimaActividad;
        $diferencia = time() - $ultimaActividad;

        if ($diferencia <= 120) {
            return ['label' => 'Activo ahora', 'class' => 'bg-success-subtle text-success border border-success-subtle'];
        }

        if ($diferencia <= 600) {
            return ['label' => 'Inactivo reciente', 'class' => 'bg-warning-subtle text-warning border border-warning-subtle'];
        }

        return ['label' => 'Inactivo', 'class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle'];
    }

    public static function resumirUserAgent($userAgent)
    {
        $userAgent = (string)$userAgent;
        $ua = strtolower($userAgent);

        $dispositivo = 'Desktop';
        if (strpos($ua, 'mobile') !== false || strpos($ua, 'android') !== false || strpos($ua, 'iphone') !== false) {
            $dispositivo = 'Móvil';
        } elseif (strpos($ua, 'ipad') !== false || strpos($ua, 'tablet') !== false) {
            $dispositivo = 'Tablet';
        }

        $navegador = 'Navegador';
        if (strpos($ua, 'edg') !== false) {
            $navegador = 'Edge';
        } elseif (strpos($ua, 'chrome') !== false && strpos($ua, 'edg') === false) {
            $navegador = 'Chrome';
        } elseif (strpos($ua, 'firefox') !== false) {
            $navegador = 'Firefox';
        } elseif (strpos($ua, 'safari') !== false && strpos($ua, 'chrome') === false) {
            $navegador = 'Safari';
        }

        return $dispositivo . ' / ' . $navegador;
    }

    public static function obtenerUltimoLogout($usuarioId)
    {
        $historial = self::leerHistorial();
        $usuarioId = (int)$usuarioId;
        $ultimo = null;

        foreach ($historial as $sesion) {
            if ((int)($sesion['usuario_id'] ?? 0) === $usuarioId && !empty($sesion['logout_at'])) {
                $logoutAt = (int)$sesion['logout_at'];
                if ($ultimo === null || $logoutAt > $ultimo) {
                    $ultimo = $logoutAt;
                }
            }
        }

        return $ultimo ? self::formatearHora($ultimo) : 'Sin logout registrado';
    }
}
