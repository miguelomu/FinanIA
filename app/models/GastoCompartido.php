<?php
/**
 * Modelo: GastoCompartido
 */

class GastoCompartido
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodos($idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT gc.*, c.nombre AS pagador_nombre, c.color AS pagador_color,
                    cu.nombre AS cuenta_nombre
             FROM gasto_compartido gc
             INNER JOIN contacto c ON gc.id_pagador = c.id_contacto
             LEFT JOIN cuenta cu ON gc.id_cuenta = cu.id_cuenta
             WHERE gc.id_usuario = ?
             ORDER BY gc.fecha DESC, gc.id_gasto DESC"
        );
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT gc.*, c.nombre AS pagador_nombre, cu.nombre AS cuenta_nombre
             FROM gasto_compartido gc
             INNER JOIN contacto c ON gc.id_pagador = c.id_contacto
             LEFT JOIN cuenta cu ON gc.id_cuenta = cu.id_cuenta
             WHERE gc.id_gasto = ? AND gc.id_usuario = ?"
        );
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    /**
     * Obtiene los participantes de un gasto
     */
    public function obtenerParticipantes($idGasto)
    {
        $stmt = $this->pdo->prepare(
            "SELECT pg.*, c.nombre AS contacto_nombre, c.color AS contacto_color
             FROM participante_gasto pg
             INNER JOIN contacto c ON pg.id_contacto = c.id_contacto
             WHERE pg.id_gasto = ?
             ORDER BY c.nombre"
        );
        $stmt->execute([$idGasto]);
        return $stmt->fetchAll();
    }

    /**
     * Crea un gasto compartido con sus participantes
     * 
     * @param array $participantes [['id_contacto' => X, 'monto' => Y], ...]
     */
    public function crear($idUsuario, $idCuenta, $concepto, $montoTotal, $fecha, $idPagador, $notas, $participantes)
    {
        $this->pdo->beginTransaction();

        try {
            // Insertar gasto
            $stmt = $this->pdo->prepare(
                "INSERT INTO gasto_compartido 
                 (id_usuario, id_cuenta, concepto, monto_total, fecha, id_pagador, notas) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$idUsuario, $idCuenta, $concepto, $montoTotal, $fecha, $idPagador, $notas]);
            $idGasto = $this->pdo->lastInsertId();

            // Insertar participantes
            $stmt = $this->pdo->prepare(
                "INSERT INTO participante_gasto (id_gasto, id_contacto, monto_corresponde, pagado) 
                 VALUES (?, ?, ?, ?)"
            );
            foreach ($participantes as $p) {
                // El pagador ya pagó su parte (TRUE), los demás deben (FALSE)
                $yaPago = ($p['id_contacto'] == $idPagador) ? 1 : 0;
                $stmt->execute([$idGasto, $p['id_contacto'], $p['monto'], $yaPago]);
            }

            $this->pdo->commit();
            return $idGasto;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function eliminar($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("DELETE FROM gasto_compartido WHERE id_gasto = ? AND id_usuario = ?");
        return $stmt->execute([$id, $idUsuario]);
    }

    /**
     * Marca como pagado el aporte de un participante
     */
    public function marcarPagado($idParticipante, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE participante_gasto pg
             INNER JOIN gasto_compartido gc ON pg.id_gasto = gc.id_gasto
             SET pg.pagado = TRUE, pg.fecha_pago = NOW()
             WHERE pg.id_participante = ? AND gc.id_usuario = ?"
        );
        return $stmt->execute([$idParticipante, $idUsuario]);
    }
}