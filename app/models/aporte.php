<?php
/**
 * Modelo: Aporte
 * 
 * Maneja los aportes individuales a las metas.
 */

class Aporte
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los aportes de una meta
     */
    public function obtenerPorMeta($idMeta)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM aporte_meta WHERE id_meta = ? ORDER BY fecha DESC"
        );
        $stmt->execute([$idMeta]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un aporte por ID validando que pertenezca al usuario
     */
    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, m.nombre AS meta_nombre, m.id_meta 
             FROM aporte_meta a
             INNER JOIN meta m ON a.id_meta = m.id_meta
             WHERE a.id_aporte = ? AND a.id_usuario = ?"
        );
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    /**
     * Crea un nuevo aporte
     */
    public function crear($idMeta, $idUsuario, $monto, $nota)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO aporte_meta (id_meta, id_usuario, monto, nota) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$idMeta, $idUsuario, $monto, $nota]);
    }

    /**
     * Actualiza un aporte
     */
    public function actualizar($id, $monto, $nota)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE aporte_meta SET monto = ?, nota = ? WHERE id_aporte = ?"
        );
        return $stmt->execute([$monto, $nota, $id]);
    }

    /**
     * Elimina un aporte
     */
    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM aporte_meta WHERE id_aporte = ?");
        return $stmt->execute([$id]);
    }
}