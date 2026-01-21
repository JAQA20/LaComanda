<?php
class User
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.password, u.rol_id, r.nombre AS rol_nombre
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                WHERE u.email = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        if (!$result || $result->num_rows === 0) return null;

        return $result->fetch_assoc();
    }
}
