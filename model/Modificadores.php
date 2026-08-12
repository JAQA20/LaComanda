<?php
require_once __DIR__ . "/Conexion.php";

class Modificadores
{
    // Obtener todos los grupos con sus opciones y asignaciones (Para el Panel Admin)
    public static function listarGrupos()
    {
        $conexion = Conexion::conectar();
        
        $sql = "SELECT id, nombre, requerido, seleccion_multiple, activo FROM grupos_opciones ORDER BY id DESC";
        $result = $conexion->query($sql);
        
        if (!$result) throw new Exception("Error al listar grupos: " . $conexion->error);
        
        $grupos = [];
        while ($row = $result->fetch_assoc()) {
            // Cargar opciones
            $row['opciones'] = self::obtenerOpcionesPorGrupo($row['id'], $conexion);
            // Cargar categorias asignadas
            $row['categorias'] = self::obtenerCategoriasPorGrupo($row['id'], $conexion);
            // Cargar productos asignados
            $row['productos'] = self::obtenerProductosPorGrupo($row['id'], $conexion);
            
            $grupos[] = $row;
        }
        return $grupos;
    }

    private static function obtenerOpcionesPorGrupo($grupo_id, $conexion)
    {
        $stmt = $conexion->prepare("SELECT id, nombre, precio_adicional FROM opciones WHERE grupo_id = ? AND activo = 1 ORDER BY id ASC");
        $stmt->bind_param("i", $grupo_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $opciones = [];
        while ($row = $res->fetch_assoc()) {
            $opciones[] = $row;
        }
        return $opciones;
    }

    private static function obtenerCategoriasPorGrupo($grupo_id, $conexion)
    {
        $stmt = $conexion->prepare("SELECT categoria_id FROM categoria_grupos WHERE grupo_id = ?");
        $stmt->bind_param("i", $grupo_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $categorias = [];
        while ($row = $res->fetch_assoc()) {
            $categorias[] = $row['categoria_id'];
        }
        return $categorias;
    }

    private static function obtenerProductosPorGrupo($grupo_id, $conexion)
    {
        $stmt = $conexion->prepare("SELECT producto_id FROM producto_grupos WHERE grupo_id = ?");
        $stmt->bind_param("i", $grupo_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $productos = [];
        while ($row = $res->fetch_assoc()) {
            $productos[] = $row['producto_id'];
        }
        return $productos;
    }

    public static function crearGrupo($nombre, $requerido, $seleccion_multiple, $activo, $opciones, $categorias, $productos)
    {
        $conexion = Conexion::conectar();
        $conexion->begin_transaction();

        try {
            // Verificar si el grupo ya existe (Anti-Duplicados)
            $stmtCheck = $conexion->prepare("SELECT id FROM grupos_opciones WHERE nombre = ?");
            $stmtCheck->bind_param("s", $nombre);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();

            if ($resCheck->num_rows > 0) {
                $grupo_id = $resCheck->fetch_assoc()['id'];
                
                // Actualizar info básica
                $stmtUpdate = $conexion->prepare("UPDATE grupos_opciones SET requerido=?, seleccion_multiple=?, activo=? WHERE id=?");
                $stmtUpdate->bind_param("iiii", $requerido, $seleccion_multiple, $activo, $grupo_id);
                $stmtUpdate->execute();

                // Limpiar relaciones anteriores
                $conexion->query("DELETE FROM opciones WHERE grupo_id = $grupo_id");
                $conexion->query("DELETE FROM categoria_grupos WHERE grupo_id = $grupo_id");
                $conexion->query("DELETE FROM producto_grupos WHERE grupo_id = $grupo_id");
            } else {
                $stmt = $conexion->prepare("INSERT INTO grupos_opciones (nombre, requerido, seleccion_multiple, activo) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siii", $nombre, $requerido, $seleccion_multiple, $activo);
                $stmt->execute();
                $grupo_id = $stmt->insert_id;
            }

            // Insertar Opciones
            if (is_array($opciones)) {
                $stmtOpt = $conexion->prepare("INSERT INTO opciones (grupo_id, nombre, precio_adicional) VALUES (?, ?, ?)");
                foreach ($opciones as $opt) {
                    $precio = floatval($opt['precio_adicional'] ?? 0);
                    $stmtOpt->bind_param("isd", $grupo_id, $opt['nombre'], $precio);
                    $stmtOpt->execute();
                }
            }

            // Asignar Categorias
            if (is_array($categorias)) {
                $stmtCat = $conexion->prepare("INSERT INTO categoria_grupos (categoria_id, grupo_id) VALUES (?, ?)");
                foreach ($categorias as $cat_id) {
                    $stmtCat->bind_param("ii", $cat_id, $grupo_id);
                    $stmtCat->execute();
                }
            }

            // Asignar Productos
            if (is_array($productos)) {
                $stmtProd = $conexion->prepare("INSERT INTO producto_grupos (producto_id, grupo_id) VALUES (?, ?)");
                foreach ($productos as $prod_id) {
                    $stmtProd->bind_param("ii", $prod_id, $grupo_id);
                    $stmtProd->execute();
                }
            }

            $conexion->commit();
            return $grupo_id;
        } catch (Exception $e) {
            $conexion->rollback();
            throw $e;
        }
    }

    public static function eliminarGrupo($grupo_id)
    {
        $conexion = Conexion::conectar();
        $stmt = $conexion->prepare("DELETE FROM grupos_opciones WHERE id = ?");
        $stmt->bind_param("i", $grupo_id);
        return $stmt->execute();
    }

    public static function editarGrupo($grupo_id, $nombre, $requerido, $seleccion_multiple, $activo, $opciones, $categorias, $productos)
    {
        $conexion = Conexion::conectar();
        $conexion->begin_transaction();

        try {
            // Actualizar datos básicos
            $stmt = $conexion->prepare("UPDATE grupos_opciones SET nombre = ?, requerido = ?, seleccion_multiple = ?, activo = ? WHERE id = ?");
            $stmt->bind_param("siiii", $nombre, $requerido, $seleccion_multiple, $activo, $grupo_id);
            $stmt->execute();

            // Eliminar opciones anteriores e insertar nuevas
            $stmtDelOpt = $conexion->prepare("DELETE FROM opciones WHERE grupo_id = ?");
            $stmtDelOpt->bind_param("i", $grupo_id);
            $stmtDelOpt->execute();

            if (is_array($opciones)) {
                $stmtOpt = $conexion->prepare("INSERT INTO opciones (grupo_id, nombre, precio_adicional) VALUES (?, ?, ?)");
                foreach ($opciones as $opt) {
                    $precio = floatval($opt['precio_adicional'] ?? 0);
                    $stmtOpt->bind_param("isd", $grupo_id, $opt['nombre'], $precio);
                    $stmtOpt->execute();
                }
            }

            // Eliminar y reasignar categorías
            $stmtDelCat = $conexion->prepare("DELETE FROM categoria_grupos WHERE grupo_id = ?");
            $stmtDelCat->bind_param("i", $grupo_id);
            $stmtDelCat->execute();

            if (is_array($categorias)) {
                $stmtCat = $conexion->prepare("INSERT INTO categoria_grupos (categoria_id, grupo_id) VALUES (?, ?)");
                foreach ($categorias as $cat_id) {
                    $stmtCat->bind_param("ii", $cat_id, $grupo_id);
                    $stmtCat->execute();
                }
            }

            // Eliminar y reasignar productos
            $stmtDelProd = $conexion->prepare("DELETE FROM producto_grupos WHERE grupo_id = ?");
            $stmtDelProd->bind_param("i", $grupo_id);
            $stmtDelProd->execute();

            if (is_array($productos)) {
                $stmtProd = $conexion->prepare("INSERT INTO producto_grupos (producto_id, grupo_id) VALUES (?, ?)");
                foreach ($productos as $prod_id) {
                    $stmtProd->bind_param("ii", $prod_id, $grupo_id);
                    $stmtProd->execute();
                }
            }

            $conexion->commit();
            return true;
        } catch (Exception $e) {
            $conexion->rollback();
            throw $e;
        }
    }
}
