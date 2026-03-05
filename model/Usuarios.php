<?php
require_once __DIR__ . "/Conexion.php";

class Usuarios
{
    public static function listar()
    {
        $conexion = Conexion::conectar();

        $sql = "
            SELECT u.id, u.nombre, u.apellido, u.email, u.rol_id,
                   r.nombre AS rol_nombre
            FROM usuarios u
            LEFT JOIN roles r ON r.id = u.rol_id
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

    public static function crear($nombre, $apellido, $email, $passwordPlano, $rol_id)
    {
        $conexion = Conexion::conectar();

        // Validaciones básicas
        $nombre = trim($nombre);
        $apellido = trim($apellido);
        $email = trim($email);
        $rol_id = (int)$rol_id;

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
            INSERT INTO usuarios (nombre, apellido, email, password, rol_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        if (!$stmt) throw new Exception("Error prepare crear: " . $conexion->error);

        $stmt->bind_param("ssssi", $nombre, $apellido, $email, $hash, $rol_id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo crear el usuario: " . $stmt->error);
        }

        return $stmt->insert_id;
    }

    //Editar usuario
    public static function obtenerPorId($id)
    {
        $conexion = Conexion::conectar();
        $id = (int)$id;

        $stmt = $conexion->prepare("
        SELECT id, nombre, apellido, email, rol_id
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

    public static function actualizar($id, $nombre, $apellido, $email, $rol_id, $passwordPlano = null)
    {
        $conexion = Conexion::conectar();

        $id = (int)$id;
        $rol_id = (int)$rol_id;

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
            SET nombre = ?, apellido = ?, email = ?, rol_id = ?, password = ?
            WHERE id = ?
            LIMIT 1
        ");
            if (!$stmt2) throw new Exception("Error prepare update con password: " . $conexion->error);
            $stmt2->bind_param("sssisi", $nombre, $apellido, $email, $rol_id, $hash, $id);

            if (!$stmt2->execute()) {
                throw new Exception("No se pudo actualizar el usuario: " . $stmt2->error);
            }
            return true;
        }

        $stmt3 = $conexion->prepare("
        UPDATE usuarios
        SET nombre = ?, apellido = ?, email = ?, rol_id = ?
        WHERE id = ?
        LIMIT 1
    ");
        if (!$stmt3) throw new Exception("Error prepare update: " . $conexion->error);
        $stmt3->bind_param("sssii", $nombre, $apellido, $email, $rol_id, $id);

        if (!$stmt3->execute()) {
            throw new Exception("No se pudo actualizar el usuario: " . $stmt3->error);
        }
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

        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Error prepare delete: " . $conexion->error);
        }

        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("No se pudo eliminar el usuario: " . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("No se encontró el usuario para eliminar.");
        }

        return true;
    }
}
