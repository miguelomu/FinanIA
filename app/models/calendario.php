<?php
/**
 * Modelo: Calendario
 * 
 * Obtiene los eventos para el calendario:
 * - Transacciones (con colores)
 * - Vencimientos de deudas
 * - Fechas límite de metas
 */

class Calendario
{
    private $pdo;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->idUsuario = $idUsuario;
    }

    /**
     * Obtiene todos los eventos de un mes específico
     * 
     * @param int $mes
     * @param int $anio
     * @return array ['YYYY-MM-DD' => ['transacciones' => [...], 'deudas' => [...], 'metas' => [...], 'totales' => [...]]]
     */
    public function obtenerEventosMes($mes, $anio)
    {
        $eventos = [];

        // ============================================
        // 1. TRANSACCIONES
        // ============================================
        $stmt = $this->pdo->prepare(
            "SELECT t.id_transaccion AS id, t.descripcion, t.monto, t.tipo, t.fecha,
                    c.nombre AS categoria, c.color AS categoria_color
             FROM transaccion t
             LEFT JOIN categoria c ON t.id_categoria = c.id_categoria
             WHERE t.id_usuario = ? 
               AND MONTH(t.fecha) = ? 
               AND YEAR(t.fecha) = ?
             ORDER BY t.fecha ASC, t.monto DESC"
        );
        $stmt->execute([$this->idUsuario, $mes, $anio]);
        $transacciones = $stmt->fetchAll();

        foreach ($transacciones as $t) {
            $fecha = $t['fecha'];
            if (!isset($eventos[$fecha])) {
                $eventos[$fecha] = [
                    'transacciones' => [],
                    'deudas' => [],
                    'metas' => [],
                    'totales' => ['ingresos' => 0, 'gastos' => 0]
                ];
            }
            $eventos[$fecha]['transacciones'][] = $t;
            
            if ($t['tipo'] === 'ingreso') {
                $eventos[$fecha]['totales']['ingresos'] += $t['monto'];
            } else {
                $eventos[$fecha]['totales']['gastos'] += $t['monto'];
            }
        }

        // ============================================
        // 2. DEUDAS (vencimientos)
        // ============================================
        $stmt = $this->pdo->prepare(
            "SELECT id_deuda AS id, acreedor AS descripcion, 
                    (monto_total - monto_pagado) AS monto, 
                    fecha_vencimiento AS fecha, estado
             FROM deuda
             WHERE id_usuario = ? 
               AND MONTH(fecha_vencimiento) = ? 
               AND YEAR(fecha_vencimiento) = ?
               AND estado != 'pagada'"
        );
        $stmt->execute([$this->idUsuario, $mes, $anio]);
        $deudas = $stmt->fetchAll();

        foreach ($deudas as $d) {
            $fecha = $d['fecha'];
            if (!isset($eventos[$fecha])) {
                $eventos[$fecha] = [
                    'transacciones' => [],
                    'deudas' => [],
                    'metas' => [],
                    'totales' => ['ingresos' => 0, 'gastos' => 0]
                ];
            }
            $eventos[$fecha]['deudas'][] = $d;
        }

        // ============================================
        // 3. METAS (fechas límite)
        // ============================================
        $stmt = $this->pdo->prepare(
            "SELECT id_meta AS id, nombre AS descripcion, 
                    monto_objetivo, monto_actual, 
                    fecha_limite AS fecha, estado
             FROM meta
             WHERE id_usuario = ? 
               AND MONTH(fecha_limite) = ? 
               AND YEAR(fecha_limite) = ?
               AND estado = 'activa'"
        );
        $stmt->execute([$this->idUsuario, $mes, $anio]);
        $metas = $stmt->fetchAll();

        foreach ($metas as $m) {
            $fecha = $m['fecha'];
            if (!isset($eventos[$fecha])) {
                $eventos[$fecha] = [
                    'transacciones' => [],
                    'deudas' => [],
                    'metas' => [],
                    'totales' => ['ingresos' => 0, 'gastos' => 0]
                ];
            }
            $eventos[$fecha]['metas'][] = $m;
        }

        return $eventos;
    }

    /**
     * Obtiene resumen de un mes
     */
    public function resumenMes($mes, $anio)
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END),0) AS ingresos,
                COALESCE(SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END),0) AS gastos,
                COUNT(*) AS total
             FROM transaccion
             WHERE id_usuario = ? AND MONTH(fecha) = ? AND YEAR(fecha) = ?"
        );
        $stmt->execute([$this->idUsuario, $mes, $anio]);
        $r = $stmt->fetch();
        $r['balance'] = $r['ingresos'] - $r['gastos'];
        return $r;
    }

    /**
     * Obtiene los eventos de un día específico con detalles
     */
    public function detalleDia($fecha)
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.*, c.nombre AS categoria_nombre, c.color AS categoria_color,
                    cu.nombre AS cuenta_nombre
             FROM transaccion t
             LEFT JOIN categoria c ON t.id_categoria = c.id_categoria
             LEFT JOIN cuenta cu ON t.id_cuenta = cu.id_cuenta
             WHERE t.id_usuario = ? AND t.fecha = ?
             ORDER BY t.monto DESC"
        );
        $stmt->execute([$this->idUsuario, $fecha]);
        $transacciones = $stmt->fetchAll();

        // Totales del día
        $ingresos = 0;
        $gastos = 0;
        foreach ($transacciones as $t) {
            if ($t['tipo'] === 'ingreso') $ingresos += $t['monto'];
            else $gastos += $t['monto'];
        }

        return [
            'transacciones' => $transacciones,
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'balance' => $ingresos - $gastos
        ];
    }
}