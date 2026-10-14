<?php
/**
 * Modelo: Aporte
 */

class Aporte
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerPorMeta($idMeta)
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, c.nombre AS cuenta_nombre, c.color AS cuenta_color
             FROM aporte_meta a
             LEFT JOIN cuenta c ON a.id_cuenta = c.id_cuenta
             WHERE a.id_meta = ? 
             ORDER BY a.fecha DESC"
        );
        $stmt->execute([$idMeta]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, m.nombre AS meta_nombre, m.id_meta,
                    c.nombre AS cuenta_nombre
             FROM aporte_meta a
             INNER JOIN meta m ON a.id_meta = m.id_meta
             LEFT JOIN cuenta c ON a.id_cuenta = c.id_cuenta
             WHERE a.id_aporte = ? AND a.id_usuario = ?"
        );
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    public function crear($idMeta, $idUsuario, $idCuenta, $monto, $nota)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO aporte_meta (id_meta, id_usuario, id_cuenta, monto, nota) 
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$idMeta, $idUsuario, $idCuenta ?: null, $monto, $nota]);
    }

    public function actualizar($id, $idCuenta, $monto, $nota)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE aporte_meta SET id_cuenta = ?, monto = ?, nota = ? WHERE id_aporte = ?"
        );
        return $stmt->execute([$idCuenta ?: null, $monto, $nota, $id]);
    }

    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM aporte_meta WHERE id_aporte = ?");
        return $stmt->execute([$id]);
    }
}