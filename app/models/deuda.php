<?php
/**
 * Modelo: Deuda
 * 
 * Maneja las operaciones de base de datos de las deudas.
 */

class Deuda
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodas($idUsuario, $estado = '')
    {
        $sql = "SELECT * FROM deuda WHERE id_usuario = ?";
        $params = [$idUsuario];

        if (in_array($estado, ['activa', 'pagada', 'vencida'])) {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }

        $sql .= " ORDER BY 
            CASE estado 
                WHEN 'vencida' THEN 1 
                WHEN 'activa' THEN 2 
                WHEN 'pagada' THEN 3 
            END, fecha_vencimiento ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM deuda WHERE id_deuda = ? AND id_usuario = ?");
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    public function crear($idUsuario, $acreedor, $montoTotal, $montoPagado, $tasaInteres, $fechaInicio, $fechaVencimiento, $pagoMinimo)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO deuda 
             (id_usuario, acreedor, monto_total, monto_inicial, monto_pagado, tasa_interes, 
              fecha_inicio, fecha_vencimiento, pago_minimo, estado) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activa')"
        );
        return $stmt->execute([
            $idUsuario, $acreedor, $montoTotal, $montoTotal, $montoPagado,
            $tasaInteres, $fechaInicio, $fechaVencimiento, $pagoMinimo
        ]);
    }

    public function actualizar($id, $idUsuario, $acreedor, $montoTotal, $montoPagado, $tasaInteres, $fechaInicio, $fechaVencimiento, $pagoMinimo, $estado)
    {
        if ($montoPagado >= $montoTotal && $estado === 'activa') {
            $estado = 'pagada';
        }

        $stmt = $this->pdo->prepare(
            "UPDATE deuda 
             SET acreedor = ?, monto_total = ?, monto_inicial = ?, monto_pagado = ?, 
                 tasa_interes = ?, fecha_inicio = ?, fecha_vencimiento = ?, 
                 pago_minimo = ?, estado = ?
             WHERE id_deuda = ? AND id_usuario = ?"
        );
        return $stmt->execute([
            $acreedor, $montoTotal, $montoTotal, $montoPagado,
            $tasaInteres, $fechaInicio, $fechaVencimiento,
            $pagoMinimo, $estado,
            $id, $idUsuario
        ]);
    }

    public function eliminar($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("DELETE FROM deuda WHERE id_deuda = ? AND id_usuario = ?");
        return $stmt->execute([$id, $idUsuario]);
    }

    /**
     * Recalcula monto_pagado y estado sumando todos los pagos
     */
    public function recalcular($id)
    {
        $stmt = $this->pdo->prepare("SELECT monto_total FROM deuda WHERE id_deuda = ?");
        $stmt->execute([$id]);
        $deuda = $stmt->fetch();

        if (!$deuda) return false;

        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(monto),0) AS total FROM pago_deuda WHERE id_deuda = ?");
        $stmt->execute([$id]);
        $totalPagos = $stmt->fetch()['total'];

        $estado = $totalPagos >= $deuda['monto_total'] ? 'pagada' : 'activa';

        $stmt = $this->pdo->prepare("UPDATE deuda SET monto_pagado = ?, estado = ? WHERE id_deuda = ?");
        return $stmt->execute([$totalPagos, $estado, $id]);
    }

    public function calcularTotales($deudas)
    {
        $totalDeuda = 0;
        $totalPagado = 0;

        foreach ($deudas as $d) {
            $totalDeuda += $d['monto_total'];
            $totalPagado += $d['monto_pagado'];
        }

        return [
            'total' => $totalDeuda,
            'pagado' => $totalPagado,
            'pendiente' => $totalDeuda - $totalPagado
        ];
    }
}