<?php
/**
 * Modelo: Categoria
 * 
 * Se encarga de todas las operaciones de base de datos
 * relacionadas con las categorías.
 */

class Categoria
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las categorías de un usuario
     * 
     * @param int $idUsuario
     * @param string|null $tipo  'ingreso', 'gasto' o null para todas
     * @return array
     */
    public function obtenerTodas($idUsuario, $tipo = null)
    {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM transaccion WHERE id_categoria = c.id_categoria) AS total_transacciones
                FROM categoria c
                WHERE c.id_usuario = ?";
        $params = [$idUsuario];

        if (in_array($tipo, ['ingreso', 'gasto'])) {
            $sql .= " AND c.tipo = ?";
            $params[] = $tipo;
        }

        $sql .= " ORDER BY c.tipo, c.nombre";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una categoría por ID (validando que pertenezca al usuario)
     */
    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categoria WHERE id_categoria = ? AND id_usuario = ?");
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    /**
     * Verifica si ya existe una categoría con ese nombre
     * 
     * @param int $idUsuario
     * @param string $nombre
     * @param int|null $excluirId  ID a excluir (para edición)
     */
    public function existeNombre($idUsuario, $nombre, $excluirId = null)
    {
        $sql = "SELECT id_categoria FROM categoria WHERE id_usuario = ? AND nombre = ?";
        $params = [$idUsuario, $nombre];

        if ($excluirId !== null) {
            $sql .= " AND id_categoria != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Crea una nueva categoría
     */
    public function crear($idUsuario, $nombre, $tipo, $color, $icono)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO categoria (id_usuario, nombre, tipo, color, icono) 
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$idUsuario, $nombre, $tipo, $color, $icono]);
    }

    /**
     * Actualiza una categoría
     */
    public function actualizar($id, $idUsuario, $nombre, $tipo, $color, $icono)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE categoria 
             SET nombre = ?, tipo = ?, color = ?, icono = ? 
             WHERE id_categoria = ? AND id_usuario = ?"
        );
        return $stmt->execute([$nombre, $tipo, $color, $icono, $id, $idUsuario]);
    }

    /**
     * Elimina una categoría (solo si no tiene transacciones ni presupuestos)
     */
    public function eliminar($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("DELETE FROM categoria WHERE id_categoria = ? AND id_usuario = ?");
        return $stmt->execute([$id, $idUsuario]);
    }

    /**
     * Verifica si tiene transacciones asociadas
     */
    public function tieneTransacciones($id)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM transaccion WHERE id_categoria = ?");
        $stmt->execute([$id]);
        return $stmt->fetch()['total'] > 0;
    }

    /**
     * Verifica si tiene presupuestos asociados
     */
    public function tienePresupuestos($id)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM detalle_presupuesto WHERE id_categoria = ?");
        $stmt->execute([$id]);
        return $stmt->fetch()['total'] > 0;
    }
}