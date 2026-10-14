<?php
/**
 * Modelo: Contacto
 */

class Contacto
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodos($idUsuario, $soloActivos = false)
    {
        $sql = "SELECT * FROM contacto WHERE id_usuario = ?";
        $params = [$idUsuario];

        if ($soloActivos) {
            $sql .= " AND activo = TRUE";
        }

        $sql .= " ORDER BY activo DESC, nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM contacto WHERE id_contacto = ? AND id_usuario = ?");
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    public function crear($idUsuario, $nombre, $email, $telefono, $color)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO contacto (id_usuario, nombre, email, telefono, color) 
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$idUsuario, $nombre, $email, $telefono, $color]);
    }

    public function actualizar($id, $idUsuario, $nombre, $email, $telefono, $color, $activo)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE contacto 
             SET nombre = ?, email = ?, telefono = ?, color = ?, activo = ?
             WHERE id_contacto = ? AND id_usuario = ?"
        );
        return $stmt->execute([$nombre, $email, $telefono, $color, $activo, $id, $idUsuario]);
    }

    public function eliminar($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("DELETE FROM contacto WHERE id_contacto = ? AND id_usuario = ?");
        return $stmt->execute([$id, $idUsuario]);
    }

    /**
     * Obtiene los saldos pendientes con cada contacto.
     * 
     * Saldo positivo = el contacto me debe
     * Saldo negativo = yo le debo al contacto
     */
    public function obtenerSaldos($idUsuario)
    {
        $contactos = $this->obtenerTodos($idUsuario, true);
        $saldos = [];

        foreach ($contactos as $c) {
            // Lo que el contacto me debe (gastos donde YO pagué y él participó sin pagar)
            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(SUM(pg.monto_corresponde), 0) AS total
                 FROM participante_gasto pg
                 INNER JOIN gasto_compartido gc ON pg.id_gasto = gc.id_gasto
                 WHERE gc.id_usuario = ?
                   AND gc.id_pagador = ?
                   AND pg.id_contacto = ?
                   AND pg.pagado = FALSE"
            );
            
            // Buscar mi contacto personal (yo como pagador en gastos de otros)
            // En esta versión simplificada, asumimos que "yo" no tengo un id_contacto
            // Los gastos donde otro pagó y yo participé se manejan de otra forma
            
            $stmt->execute([$idUsuario, $c['id_contacto'], $c['id_contacto']]);
            $meDebe = $stmt->fetch()['total'];

            // Lo que yo le debo (gastos donde ÉL pagó y yo participé)
            // Como yo no soy un contacto en la BD, este cálculo es limitado en esta versión
            $yoLeDebo = 0;

            $saldos[] = [
                'contacto' => $c,
                'me_debe' => $meDebe,
                'yo_le_debo' => $yoLeDebo,
                'neto' => $meDebe - $yoLeDebo
            ];
        }

        return $saldos;
    }

    /**
     * Resumen global de gastos compartidos
     */
    public function resumenSaldos($idUsuario)
    {
        $saldos = $this->obtenerSaldos($idUsuario);
        
        $totalMeDeben = 0;
        $totalYoDebo = 0;

        foreach ($saldos as $s) {
            if ($s['neto'] > 0) $totalMeDeben += $s['neto'];
            if ($s['neto'] < 0) $totalYoDebo += abs($s['neto']);
        }

        return [
            'me_deben' => $totalMeDeben,
            'yo_debo' => $totalYoDebo,
            'neto' => $totalMeDeben - $totalYoDebo
        ];
    }
}