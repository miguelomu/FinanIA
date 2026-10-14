<?php
/**
 * Modelo: Cuenta
 * 
 * Maneja las operaciones de base de datos de las cuentas.
 */

class Cuenta
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las cuentas de un usuario con su saldo actual
     */
    public function obtenerTodas($idUsuario, $soloActivas = false)
    {
        $sql = "SELECT c.*,
            c.saldo_inicial 
              + COALESCE((SELECT SUM(CASE WHEN tipo='ingreso' THEN monto ELSE -monto END) 
                          FROM transaccion WHERE id_cuenta = c.id_cuenta), 0)
              - COALESCE((SELECT SUM(monto) FROM aporte_meta WHERE id_cuenta = c.id_cuenta), 0)
              - COALESCE((SELECT SUM(monto) FROM pago_deuda WHERE id_cuenta = c.id_cuenta), 0)
            AS saldo_actual
            FROM cuenta c
            WHERE c.id_usuario = ?";
        
        $params = [$idUsuario];

        if ($soloActivas) {
            $sql .= " AND c.activa = TRUE";
        }

        $sql .= " ORDER BY c.activa DESC, c.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una cuenta por ID validando que pertenezca al usuario
     */
    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM cuenta WHERE id_cuenta = ? AND id_usuario = ?");
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    /**
     * Verifica si ya existe una cuenta con ese nombre
     */
    public function existeNombre($idUsuario, $nombre, $excluirId = null)
    {
        $sql = "SELECT id_cuenta FROM cuenta WHERE id_usuario = ? AND nombre = ?";
        $params = [$idUsuario, $nombre];

        if ($excluirId !== null) {
            $sql .= " AND id_cuenta != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Crea una cuenta nueva
     */
    public function crear($idUsuario, $nombre, $tipo, $saldoInicial, $etiqueta, $color)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO cuenta (id_usuario, nombre, tipo, saldo_inicial, etiqueta, color) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$idUsuario, $nombre, $tipo, $saldoInicial, $etiqueta, $color]);
    }

    /**
     * Actualiza una cuenta
     */
    public function actualizar($id, $idUsuario, $nombre, $tipo, $saldoInicial, $etiqueta, $color, $activa)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE cuenta 
             SET nombre = ?, tipo = ?, saldo_inicial = ?, etiqueta = ?, color = ?, activa = ?
             WHERE id_cuenta = ? AND id_usuario = ?"
        );
        return $stmt->execute([
            $nombre, $tipo, $saldoInicial, $etiqueta, $color, $activa,
            $id, $idUsuario
        ]);
    }

    /**
     * Elimina una cuenta (solo si no tiene transacciones, aportes ni pagos)
     */
    public function eliminar($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("DELETE FROM cuenta WHERE id_cuenta = ? AND id_usuario = ?");
        return $stmt->execute([$id, $idUsuario]);
    }

    /**
     * Verifica si la cuenta tiene movimientos asociados
     */
    public function tieneMovimientos($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                (SELECT COUNT(*) FROM transaccion WHERE id_cuenta = ?) +
                (SELECT COUNT(*) FROM aporte_meta WHERE id_cuenta = ?) +
                (SELECT COUNT(*) FROM pago_deuda WHERE id_cuenta = ?) 
             AS total"
        );
        $stmt->execute([$id, $id, $id]);
        return $stmt->fetch()['total'] > 0;
    }

    /**
     * Calcula el patrimonio del usuario (activos, pasivos, neto)
     */
    public function calcularPatrimonio($idUsuario)
    {
        // Total en cuentas (activos)
        $cuentas = $this->obtenerTodas($idUsuario, true);
        $totalActivos = 0;
        foreach ($cuentas as $c) {
            $totalActivos += $c['saldo_actual'];
        }

        // Total de deudas pendientes (pasivos)
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(monto_total - monto_pagado), 0) AS total 
             FROM deuda WHERE id_usuario = ? AND estado != 'pagada'"
        );
        $stmt->execute([$idUsuario]);
        $totalPasivos = $stmt->fetch()['total'];

        // Patrimonio neto
        $patrimonioNeto = $totalActivos - $totalPasivos;

        return [
            'activos' => $totalActivos,
            'pasivos' => $totalPasivos,
            'neto' => $patrimonioNeto,
            'cuentas' => $cuentas
        ];
    }
}