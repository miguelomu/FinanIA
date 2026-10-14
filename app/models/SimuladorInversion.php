<?php
/**
 * Modelo: SimuladorInversion
 * 
 * Calcula el rendimiento de diferentes alternativas de inversión:
 * - Cuenta de ahorros
 * - CDT (con retención en la fuente)
 * - Fondo de inversión
 * 
 * Descuenta inflación para mostrar rendimiento real.
 */

class SimuladorInversion
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Calcula el rendimiento de una inversión
     */
    public function calcularRendimiento($montoInvertir, $plazoMeses, $tasaEA, $retencionPorcentaje = 0, $inflacionAnual = 5, $aplicarGravamen = false)
    {
        // Convertir tasa EA a tasa mensual equivalente
        $tasaMensual = pow(1 + ($tasaEA / 100), 1/12) - 1;

        // Calcular valor final
        $valorFinal = $montoInvertir * pow(1 + $tasaMensual, $plazoMeses);
        $rendimientoBruto = $valorFinal - $montoInvertir;

        // Aplicar retención en la fuente (solo sobre el rendimiento)
        $retencion = $rendimientoBruto * ($retencionPorcentaje / 100);
        $rendimientoNeto = $rendimientoBruto - $retencion;

        // Aplicar gravamen 4x1000 si aplica (sobre movimientos)
        $gravamen = 0;
        if ($aplicarGravamen) {
            $gravamen = ($montoInvertir + $valorFinal) * 0.004;
        }

        $rendimientoFinal = $rendimientoNeto - $gravamen;
        $valorFinalNeto = $montoInvertir + $rendimientoFinal;

        // Calcular rendimiento real (descontando inflación)
        $inflacionAcumulada = pow(1 + ($inflacionAnual / 100), $plazoMeses / 12) - 1;
        $poderAdquisitivoFuturo = $valorFinalNeto / (1 + $inflacionAcumulada);
        $rendimientoReal = $poderAdquisitivoFuturo - $montoInvertir;

        // Tasa efectiva real
        $tasaRealEA = (pow($valorFinalNeto / $montoInvertir, 12 / $plazoMeses) - 1) * 100;
        $tasaRealNeta = $tasaRealEA - $inflacionAnual;

        return [
            'monto_invertido' => round($montoInvertir, 2),
            'valor_final_bruto' => round($valorFinal, 2),
            'rendimiento_bruto' => round($rendimientoBruto, 2),
            'retencion_fuente' => round($retencion, 2),
            'gravamen' => round($gravamen, 2),
            'rendimiento_neto' => round($rendimientoFinal, 2),
            'valor_final_neto' => round($valorFinalNeto, 2),
            'inflacion_acumulada' => round($inflacionAcumulada * 100, 2),
            'poder_adquisitivo' => round($poderAdquisitivoFuturo, 2),
            'rendimiento_real' => round($rendimientoReal, 2),
            'tasa_efectiva_nominal' => round($tasaRealEA, 2),
            'tasa_efectiva_real' => round($tasaRealNeta, 2)
        ];
    }

    /**
     * Compara múltiples alternativas de inversión
     */
    public function comparar($montoInvertir, $plazoMeses, $inflacion, $alternativas)
    {
        $resultados = [];

        foreach ($alternativas as $alt) {
            $resultados[] = array_merge(
                [
                    'nombre' => $alt['nombre'],
                    'tasa_nominal' => $alt['tasa'],
                    'color' => $alt['color'] ?? '#3498db'
                ],
                $this->calcularRendimiento(
                    $montoInvertir,
                    $plazoMeses,
                    $alt['tasa'],
                    $alt['retencion'] ?? 0,
                    $inflacion,
                    $alt['aplica_gravamen'] ?? false
                )
            );
        }

        // Ordenar por valor final neto (mayor primero)
        usort($resultados, fn($a, $b) => $b['valor_final_neto'] <=> $a['valor_final_neto']);

        return $resultados;
    }

    /**
     * Genera la recomendación
     */
    public function recomendar($resultados, $inflacion)
    {
        if (empty($resultados)) {
            return ['mejor' => null, 'motivo' => 'No hay alternativas para comparar.'];
        }

        $mejor = $resultados[0];
        $motivos = [];

        if (count($resultados) > 1) {
            $segunda = $resultados[1];
            $diferencia = $mejor['valor_final_neto'] - $segunda['valor_final_neto'];
            
            if ($diferencia > 0) {
                $motivos[] = "Te genera " . formatoCOP($diferencia) . " más que la segunda mejor opción (" . $segunda['nombre'] . ").";
            }
        }

        if ($mejor['rendimiento_real'] < 0) {
            $motivos[] = "⚠️ Aunque el valor final es mayor, el rendimiento real es NEGATIVO. Estás perdiendo poder adquisitivo por la inflación.";
        } elseif ($mejor['rendimiento_real'] < $mejor['rendimiento_neto'] * 0.5) {
            $motivos[] = "⚠️ La inflación se come más de la mitad de tu rendimiento. Considera plazos más cortos o alternativas con mejor tasa.";
        } else {
            $motivos[] = "El rendimiento real es positivo: tu dinero crece por encima de la inflación.";
        }

        $motivos[] = "Tasa nominal: " . number_format($mejor['tasa_efectiva_nominal'], 2) . "% EA · Tasa real: " . number_format($mejor['tasa_efectiva_real'], 2) . "% EA";

        return [
            'mejor' => $mejor,
            'motivo' => implode(' ', $motivos)
        ];
    }
}