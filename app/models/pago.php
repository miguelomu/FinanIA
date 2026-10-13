<?php
/**
 * Modelo: Pago
 * 
 * Maneja los pagos individuales de las deudas.
 */

class Pago
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerPorDeuda($idDeuda)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM pago_deuda WHERE id_deuda = ? ORDER BY fecha DESC"
        );
        $stmt->execute([$idDeuda]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, d.acreedor AS deuda_acreedor, d.id_deuda 
             FROM pago_deuda p
             INNER JOIN deuda d ON p.id_deuda = d.id_deuda
             WHERE p.id_pago = ? AND p.id_usuario = ?"
        );
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    public function crear($idDeuda, $idUsuario, $monto, $nota)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO pago_deuda (id_deuda, id_usuario, monto, nota) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$idDeuda, $idUsuario, $monto, $nota]);
    }

    public function actualizar($id, $monto, $nota)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE pago_deuda SET monto = ?, nota = ? WHERE id_pago = ?"
        );
        return $stmt->execute([$monto, $nota, $id]);
    }

    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM pago_deuda WHERE id_pago = ?");
        return $stmt->execute([$id]);
    }
}