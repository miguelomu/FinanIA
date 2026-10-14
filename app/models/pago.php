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

    /**
     * Obtiene todos los pagos de una deuda, con info de la cuenta
     */
    public function obtenerPorDeuda($idDeuda)
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, c.nombre AS cuenta_nombre, c.color AS cuenta_color
             FROM pago_deuda p
             LEFT JOIN cuenta c ON p.id_cuenta = c.id_cuenta
             WHERE p.id_deuda = ? 
             ORDER BY p.fecha DESC"
        );
        $stmt->execute([$idDeuda]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un pago por ID validando que pertenezca al usuario
     */
    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, d.acreedor AS deuda_acreedor, d.id_deuda,
                    c.nombre AS cuenta_nombre
             FROM pago_deuda p
             INNER JOIN deuda d ON p.id_deuda = d.id_deuda
             LEFT JOIN cuenta c ON p.id_cuenta = c.id_cuenta
             WHERE p.id_pago = ? AND p.id_usuario = ?"
        );
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    /**
     * Crea un nuevo pago (con id_cuenta)
     */
    public function crear($idDeuda, $idUsuario, $idCuenta, $monto, $nota)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO pago_deuda (id_deuda, id_usuario, id_cuenta, monto, nota) 
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$idDeuda, $idUsuario, $idCuenta ?: null, $monto, $nota]);
    }

    /**
     * Actualiza un pago
     */
    public function actualizar($id, $idCuenta, $monto, $nota)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE pago_deuda SET id_cuenta = ?, monto = ?, nota = ? WHERE id_pago = ?"
        );
        return $stmt->execute([$idCuenta ?: null, $monto, $nota, $id]);
    }

    /**
     * Elimina un pago
     */
    public function eliminar($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM pago_deuda WHERE id_pago = ?");
        return $stmt->execute([$id]);
    }
}