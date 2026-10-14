<?php
/**
 * Modelo: SimuladorDeuda
 * 
 * Simula el pago de deudas usando dos estrategias:
 * - Avalancha: paga primero la de mayor tasa de interés
 * - Bola de nieve: paga primero la de menor saldo
 */

class SimuladorDeuda
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene las deudas activas del usuario
     */
    public function obtenerDeudasActivas($idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_deuda, acreedor, monto_total, monto_pagado, tasa_interes, pago_minimo
             FROM deuda 
             WHERE id_usuario = ? 
               AND estado != 'pagada' 
               AND (monto_total - monto_pagado) > 0
             ORDER BY tasa_interes DESC"
        );
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    /**
     * Simula el pago de deudas con una estrategia dada.
     * 
     * @param array $deudas Lista de deudas con: acreedor, saldo, tasa, pago_minimo
     * @param float $pagoExtra Monto extra mensual para abonar
     * @param string $estrategia 'avalancha' o 'bolanieve'
     * @param int $maxMeses Límite de meses a simular (seguridad)
     * @return array
     */
    public function simular($deudas, $pagoExtra, $estrategia = 'avalancha', $maxMeses = 600)
    {
        // Preparar deudas con saldos
        $deudasSim = [];
        foreach ($deudas as $d) {
            $saldo = floatval($d['monto_total']) - floatval($d['monto_pagado']);
            if ($saldo <= 0) continue;
            
            $deudasSim[] = [
                'acreedor' => $d['acreedor'],
                'saldo_inicial' => $saldo,
                'saldo' => $saldo,
                'tasa_mensual' => floatval($d['tasa_interes']) / 100 / 12,
                'pago_minimo' => floatval($d['pago_minimo'])
            ];
        }

        if (empty($deudasSim)) {
            return [
                'meses' => 0,
                'total_pagado' => 0,
                'total_intereses' => 0,
                'deudas_pagadas' => [],
                'historial' => []
            ];
        }

        // Ordenar según estrategia
        $this->ordenarDeudas($deudasSim, $estrategia);

        $mes = 0;
        $totalPagado = 0;
        $totalIntereses = 0;
        $historial = [];
        $deudasPagadas = [];

        while ($mes < $maxMeses) {
            $mes++;
            $saldoPendienteTotal = 0;
            
            // Calcular saldo pendiente total
            foreach ($deudasSim as $d) {
                if ($d['saldo'] > 0) {
                    $saldoPendienteTotal += $d['saldo'];
                }
            }

            if ($saldoPendienteTotal <= 0) break;

            // 1. Aplicar interés del mes
            foreach ($deudasSim as &$d) {
                if ($d['saldo'] > 0) {
                    $interes = $d['saldo'] * $d['tasa_mensual'];
                    $d['saldo'] += $interes;
                    $totalIntereses += $interes;
                }
            }
            unset($d);

            // 2. Pagar mínimos
            $restanteExtra = $pagoExtra;
            foreach ($deudasSim as &$d) {
                if ($d['saldo'] <= 0) continue;

                $pagoMinimo = min($d['pago_minimo'], $d['saldo']);
                $d['saldo'] -= $pagoMinimo;
                $totalPagado += $pagoMinimo;
            }
            unset($d);

            // 3. Aplicar pago extra a la primera deuda con saldo pendiente
            foreach ($deudasSim as &$d) {
                if ($d['saldo'] <= 0 || $restanteExtra <= 0) continue;

                $pago = min($restanteExtra, $d['saldo']);
                $d['saldo'] -= $pago;
                $totalPagado += $pago;
                $restanteExtra -= $pago;
            }
            unset($d);

            // 4. Registrar deudas que se completaron este mes
            foreach ($deudasSim as $d) {
                if ($d['saldo'] <= 0 && !in_array($d['acreedor'], $deudasPagadas)) {
                    $deudasPagadas[] = $d['acreedor'];
                }
            }

            $historial[] = [
                'mes' => $mes,
                'saldo_total' => $saldoPendienteTotal
            ];
        }

        return [
            'meses' => $mes,
            'total_pagado' => round($totalPagado, 2),
            'total_intereses' => round($totalIntereses, 2),
            'deudas_pagadas' => $deudasPagadas,
            'historial' => $historial
        ];
    }

    /**
     * Ordena las deudas según la estrategia
     */
    private function ordenarDeudas(&$deudas, $estrategia)
    {
        if ($estrategia === 'avalancha') {
            // Mayor tasa de interés primero
            usort($deudas, function($a, $b) {
                return $b['tasa_mensual'] <=> $a['tasa_mensual'];
            });
        } else {
            // Menor saldo primero (bola de nieve)
            usort($deudas, function($a, $b) {
                return $a['saldo'] <=> $b['saldo'];
            });
        }
    }

    /**
     * Compara ambas estrategias y devuelve la recomendación
     */
    public function compararEstrategias($deudas, $pagoExtra)
    {
        $avalancha = $this->simular($deudas, $pagoExtra, 'avalancha');
        $bolanieve = $this->simular($deudas, $pagoExtra, 'bolanieve');

        $ahorroIntereses = $bolanieve['total_intereses'] - $avalancha['total_intereses'];
        $diferenciaMeses = $bolanieve['meses'] - $avalancha['meses'];

        // Decidir recomendación
        $recomendacion = 'avalancha';
        $motivo = '';

        if ($ahorroIntereses > 0 && $diferenciaMeses <= 2) {
            // Avalancha ahorra dinero y no tarda mucho más
            $recomendacion = 'avalancha';
            $motivo = 'Ahorras ' . formatoCOP($ahorroIntereses) . ' en intereses con la misma duración aproximada.';
        } elseif ($diferenciaMeses >= 3 && $ahorroIntereses < 100000) {
            // Bola de nieve es más motivador si la diferencia de tiempo es grande
            $recomendacion = 'bolanieve';
            $motivo = 'Terminas ' . $diferenciaMeses . ' meses antes y la diferencia en intereses es baja.';
        } elseif ($ahorroIntereses > 0) {
            $recomendacion = 'avalancha';
            $motivo = 'Es la opción matemáticamente óptima: ahorras ' . formatoCOP($ahorroIntereses) . ' en intereses.';
        } else {
            $recomendacion = 'bolanieve';
            $motivo = 'Ambas son similares. La bola de nieve es más motivadora psicológicamente.';
        }

        return [
            'avalancha' => $avalancha,
            'bolanieve' => $bolanieve,
            'ahorro_intereses' => $ahorroIntereses,
            'diferencia_meses' => $diferenciaMeses,
            'recomendacion' => $recomendacion,
            'motivo' => $motivo
        ];
    }
}