<?php
require_once __DIR__ . "/Conexion.php";

class Usuarios
{
    public static function listar()
    {
        $conexion = Conexion::conectar();

        $sql = "
            SELECT u.id, u.nombre, u.apellido, u.email, u.rol_id, u.can_view_logs,
                   r.nombre AS rol_nombre
            FROM usuarios u
            LEFT JOIN roles r ON r.id = u.rol_id
            WHERE u.activo = 1
            ORDER BY u.id DESC
        ";

        $result = $conexion->query($sql);
        if (!$result) throw new Exception("Error al listar usuarios: " . $conexion->error);

        $usuarios = [];
        while ($row = $result->fetch_assoc()) $usuarios[] = $row;
        return $usuarios;
    }

    public static function listarRoles()
    {
        $conexion = Conexion::conectar();
        $sql = "SELECT id, nombre FROM roles ORDER BY id ASC";
        $result = $conexion->query($sql);
        if (!$result) throw new Exception("Error al listar roles: " . $conexion->error);

        $roles = [];
        while ($row = $result->fetch_assoc()) $roles[] = $row;
        return $roles;
    }

    public static function emailExiste($email)
    {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        if (!$stmt) throw new Exception("Error prepare emailExiste: " . $conexion->error);

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->num_rows > 0;
    }

    public static function crear($nombre, $apellido, $email, $passwordPlano, $rol_id, $can_view_logs = 0)
    {
        $conexion = Conexion::conectar();

        // Validaciones básicas
        $nombre = trim($nombre);
        $apellido = trim($apellido);
        $email = trim($email);
        $rol_id = (int)$rol_id;
        $can_view_logs = (int)$can_view_logs;

        if ($nombre === "" || $apellido === "" || $email === "" || $passwordPlano === "" || $rol_id <= 0) {
            throw new Exception("Debe completar todos los campos.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El email no es válido.");
        }

        if (self::emailExiste($email)) {
            throw new Exception("El email ya está registrado.");
        }

        // Hash
        $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);

        $stmt = $conexion->prepare("
            INSERT INTO usuarios (nombre, apellido, email, password, rol_id, can_view_logs)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) throw new Exception("Error prepare crear: " . $conexion->error);

        $stmt->bind_param("ssssii", $nombre, $apellido, $email, $hash, $rol_id, $can_view_logs);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo crear el usuario: " . $stmt->error);
        }

        
        require_once __DIR__ . "/Logs.php";
        $currentUserId = $_SESSION['usuario_id'] ?? null;
        $newId = $stmt->insert_id;
        Logs::addLog("Usuario ID $newId ($email) creado exitosamente.", 'success', $currentUserId);

        return $newId;
    }

    //Editar usuario
    public static function obtenerPorId($id)
    {
        $conexion = Conexion::conectar();
        $id = (int)$id;

        $stmt = $conexion->prepare("
        SELECT id, nombre, apellido, email, rol_id, can_view_logs
        FROM usuarios
        WHERE id = ?
        LIMIT 1
    ");
        if (!$stmt) throw new Exception("Error prepare obtenerPorId: " . $conexion->error);

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $res = $stmt->get_result();
        return $res->fetch_assoc() ?: null;
    }

    public static function actualizar($id, $nombre, $apellido, $email, $rol_id, $passwordPlano = null, $can_view_logs = 0)
    {
        $conexion = Conexion::conectar();

        $id = (int)$id;
        $rol_id = (int)$rol_id;
        $can_view_logs = (int)$can_view_logs;

        $nombre = trim($nombre);
        $apellido = trim($apellido);
        $email = trim($email);

        if ($id <= 0) throw new Exception("ID inválido.");
        if ($nombre === "" || $apellido === "" || $email === "" || $rol_id <= 0) {
            throw new Exception("Debe completar todos los campos obligatorios.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El email no es válido.");
        }

        // Email único (excepto el mismo usuario)
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1");
        if (!$stmt) throw new Exception("Error prepare email único: " . $conexion->error);
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            throw new Exception("El email ya está registrado por otro usuario.");
        }

        // Si viene password, se actualiza con hash; si no, se deja igual
        if ($passwordPlano !== null && trim($passwordPlano) !== "") {
            if (strlen($passwordPlano) < 6) throw new Exception("La contraseña debe tener al menos 6 caracteres.");
            $hash = password_hash($passwordPlano, PASSWORD_DEFAULT);

            $stmt2 = $conexion->prepare("
            UPDATE usuarios
            SET nombre = ?, apellido = ?, email = ?, rol_id = ?, password = ?, can_view_logs = ?
            WHERE id = ?
            LIMIT 1
        ");
            if (!$stmt2) throw new Exception("Error prepare update con password: " . $conexion->error);
            $stmt2->bind_param("sssisii", $nombre, $apellido, $email, $rol_id, $hash, $can_view_logs, $id);

            if (!$stmt2->execute()) {
                throw new Exception("No se pudo actualizar el usuario: " . $stmt2->error);
            }
            
            require_once __DIR__ . "/Logs.php";
            $currentUserId = $_SESSION['usuario_id'] ?? null;
            Logs::addLog("Usuario ID $id actualizado exitosamente (con contraseña).", 'success', $currentUserId);
            
            return true;
        }

        $stmt3 = $conexion->prepare("
        UPDATE usuarios
        SET nombre = ?, apellido = ?, email = ?, rol_id = ?, can_view_logs = ?
        WHERE id = ?
        LIMIT 1
    ");
        if (!$stmt3) throw new Exception("Error prepare update: " . $conexion->error);
        $stmt3->bind_param("sssiii", $nombre, $apellido, $email, $rol_id, $can_view_logs, $id);

        if (!$stmt3->execute()) {
            throw new Exception("No se pudo actualizar el usuario: " . $stmt3->error);
        }
        require_once __DIR__ . "/Logs.php";
        $currentUserId = $_SESSION['usuario_id'] ?? null;
        Logs::addLog("Usuario ID $id actualizado exitosamente.", 'success', $currentUserId);

        return true;
    }

    // Eliminar usuario
    public static function eliminar($id)
    {
        $conexion = Conexion::conectar();
        $id = (int)$id;

        if ($id <= 0) {
            throw new Exception("ID inválido.");
        }

        $stmt = $conexion->prepare("UPDATE usuarios SET activo = 0 WHERE id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Error prepare delete: " . $conexion->error);
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            require_once __DIR__ . "/Logs.php";
            $currentUserId = $_SESSION['usuario_id'] ?? null;
            Logs::addLog("Error al desactivar (eliminar) usuario ID $id: " . $stmt->error, 'error', $currentUserId);
            throw new Exception("No se pudo eliminar el usuario: " . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("No se encontró el usuario para eliminar o ya estaba eliminado.");
        }

        require_once __DIR__ . "/Logs.php";
        $currentUserId = $_SESSION['usuario_id'] ?? null;
        Logs::addLog("Usuario ID $id desactivado exitosamente (Soft delete).", 'success', $currentUserId);

        return true;
    }
}
