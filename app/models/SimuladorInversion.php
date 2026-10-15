<?php
/**
 * Modelo: SimuladorInversion
 * 
 * Calcula rendimiento de:
 * - CDT / plazo fijo (inversión única)
 * - FIC con aportes mensuales (anualidad)
 * - Comparación de productos
 * - Calculadora de meta de ahorro
 */

class SimuladorInversion
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // ==========================================
    // 1. CDT / PLAZO FIJO
    // ==========================================

    /**
     * Calcula el rendimiento de una inversión a plazo fijo (CDT)
     * 
     * @param float $montoInvertir
     * @param int $plazoMeses
     * @param float $tasaEA
     * @param float $retencionPorcentaje
     * @param float $inflacionAnual
     * @param bool $aplicarGravamen
     */
    public function calcularCDT($montoInvertir, $plazoMeses, $tasaEA, $retencionPorcentaje = 4, $inflacionAnual = 5.2, $aplicarGravamen = false)
    {
        $tasaMensual = pow(1 + ($tasaEA / 100), 1/12) - 1;
        $valorFinal = $montoInvertir * pow(1 + $tasaMensual, $plazoMeses);
        $rendimientoBruto = $valorFinal - $montoInvertir;

        $retencion = $rendimientoBruto * ($retencionPorcentaje / 100);
        $gravamen = $aplicarGravamen ? ($montoInvertir + $valorFinal) * 0.004 : 0;

        $rendimientoNeto = $rendimientoBruto - $retencion - $gravamen;
        $valorFinalNeto = $montoInvertir + $rendimientoNeto;

        $inflacionAcumulada = pow(1 + ($inflacionAnual / 100), $plazoMeses / 12) - 1;
        $poderAdquisitivo = $valorFinalNeto / (1 + $inflacionAcumulada);
        $rendimientoReal = $poderAdquisitivo - $montoInvertir;

        $tasaNominalEA = (pow($valorFinalNeto / $montoInvertir, 12 / $plazoMeses) - 1) * 100;
        $tasaRealEA = $tasaNominalEA - $inflacionAnual;

        return [
            'tipo' => 'cdt',
            'monto_invertido' => round($montoInvertir, 2),
            'total_aportado' => round($montoInvertir, 2),
            'valor_final_bruto' => round($valorFinal, 2),
            'rendimiento_bruto' => round($rendimientoBruto, 2),
            'retencion_fuente' => round($retencion, 2),
            'gravamen' => round($gravamen, 2),
            'rendimiento_neto' => round($rendimientoNeto, 2),
            'valor_final_neto' => round($valorFinalNeto, 2),
            'inflacion_acumulada' => round($inflacionAcumulada * 100, 2),
            'poder_adquisitivo' => round($poderAdquisitivo, 2),
            'rendimiento_real' => round($rendimientoReal, 2),
            'tasa_efectiva_nominal' => round($tasaNominalEA, 2),
            'tasa_efectiva_real' => round($tasaRealEA, 2)
        ];
    }

    // ==========================================
    // 2. FIC CON APORTES MENSUALES
    // ==========================================

    /**
     * Calcula el rendimiento de un FIC con aportes mensuales
     * 
     * @param float $montoInicial
     * @param float $aporteMensual
     * @param int $plazoMeses
     * @param float $tasaEA
     * @param float $inflacionAnual
     */
    public function calcularFIC($montoInicial, $aporteMensual, $plazoMeses, $tasaEA, $inflacionAnual = 5.2)
    {
        $tasaMensual = pow(1 + ($tasaEA / 100), 1/12) - 1;

        $capital = $montoInicial;
        $totalAportado = $montoInicial;
        $historial = [];

        for ($mes = 1; $mes <= $plazoMeses; $mes++) {
            // Capitalizar el mes actual
            $interesMes = $capital * $tasaMensual;
            $capital += $interesMes;
            
            // Aportar al final del mes
            $capital += $aporteMensual;
            $totalAportado += $aporteMensual;

            $historial[] = [
                'mes' => $mes,
                'capital' => round($capital, 2),
                'total_aportado' => round($totalAportado, 2),
                'rendimiento' => round($capital - $totalAportado, 2)
            ];
        }

        $valorFinal = $capital;
        $rendimientoBruto = $valorFinal - $totalAportado;

        // FIC tiene retención del 4% (los FIC están sujetos a retención)
        $retencion = $rendimientoBruto * 0.04;
        $rendimientoNeto = $rendimientoBruto - $retencion;
        $valorFinalNeto = $totalAportado + $rendimientoNeto;

        $inflacionAcumulada = pow(1 + ($inflacionAnual / 100), $plazoMeses / 12) - 1;
        $poderAdquisitivo = $valorFinalNeto / (1 + $inflacionAcumulada);
        $rendimientoReal = $poderAdquisitivo - $totalAportado;

        $tasaNominalEA = $totalAportado > 0 
            ? (pow($valorFinalNeto / $totalAportado, 12 / $plazoMeses) - 1) * 100 
            : 0;

        return [
            'tipo' => 'fic',
            'monto_inicial' => round($montoInicial, 2),
            'aporte_mensual' => round($aporteMensual, 2),
            'total_aportado' => round($totalAportado, 2),
            'valor_final_bruto' => round($valorFinal, 2),
            'rendimiento_bruto' => round($rendimientoBruto, 2),
            'retencion_fuente' => round($retencion, 2),
            'rendimiento_neto' => round($rendimientoNeto, 2),
            'valor_final_neto' => round($valorFinalNeto, 2),
            'inflacion_acumulada' => round($inflacionAcumulada * 100, 2),
            'poder_adquisitivo' => round($poderAdquisitivo, 2),
            'rendimiento_real' => round($rendimientoReal, 2),
            'tasa_efectiva_nominal' => round($tasaNominalEA, 2),
            'historial' => $historial,
            'plazo_meses' => $plazoMeses
        ];
    }

    // ==========================================
    // 3. COMPARADOR
    // ==========================================

    /**
     * Compara los 3 productos con la misma inversión
     * 
     * @param float $monto Inversión total a comparar
     * @param int $plazoMeses
     * @param array $tasas ['cdt' => X, 'fic' => Y, 'ahorros' => Z, 'inflacion' => W]
     */
    public function compararProductos($monto, $plazoMeses, $tasas)
    {
        $productos = [];

        // 1. CDT (inversión única)
        $productos[] = array_merge(
            $this->calcularCDT($monto, $plazoMeses, $tasas['cdt'], $tasas['retencion_cdt'] ?? 4, $tasas['inflacion']),
            ['nombre' => 'CDT a ' . $plazoMeses . ' meses', 'color' => '#d4af37', 'icono' => '📜']
        );

        // 2. FIC (mismo monto pero repartido en aportes mensuales)
        $aporteMensualFIC = $monto / $plazoMeses;
        $productos[] = array_merge(
            $this->calcularFIC(0, $aporteMensualFIC, $plazoMeses, $tasas['fic'], $tasas['inflacion']),
            ['nombre' => 'FIC con aportes mensuales', 'color' => '#9b59b6', 'icono' => '📈']
        );

        // 3. Cuenta de ahorros
        $productos[] = array_merge(
            $this->calcularCDT($monto, $plazoMeses, $tasas['ahorros'], 0, $tasas['inflacion'], true),
            ['nombre' => 'Cuenta de ahorros', 'color' => '#3498db', 'icono' => '🏦']
        );

        // Ordenar por valor final neto
        usort($productos, fn($a, $b) => $b['valor_final_neto'] <=> $a['valor_final_neto']);

        return $productos;
    }

    // ==========================================
    // 4. CALCULADORA DE META
    // ==========================================

    /**
     * Calcula cuánto hay que aportar mensualmente para alcanzar una meta
     * 
     * @param float $montoMeta Monto total que quiere alcanzar
     * @param int $plazoMeses En cuántos meses
     * @param float $tasaEA Rendimiento esperado
     * @param float $montoInicial Aporte inicial (opcional)
     */
    public function calcularMeta($montoMeta, $plazoMeses, $tasaEA, $montoInicial = 0)
    {
        if ($plazoMeses <= 0) {
            return ['error' => 'El plazo debe ser mayor a 0'];
        }

        $tasaMensual = pow(1 + ($tasaEA / 100), 1/12) - 1;

        // Valor futuro del monto inicial
        $valorInicialFV = $montoInicial * pow(1 + $tasaMensual, $plazoMeses);
        $faltante = $montoMeta - $valorInicialFV;

        if ($faltante <= 0) {
            return [
                'monto_meta' => round($montoMeta, 2),
                'monto_inicial' => round($montoInicial, 2),
                'aporte_mensual' => 0,
                'mensaje' => '¡Con tu monto inicial ya alcanzas la meta sin aportes adicionales!',
                'plazo_meses' => $plazoMeses
            ];
        }

        // Fórmula de anualidad: AporteMensual = Faltante / [((1+i)^n - 1) / i]
        $factor = (pow(1 + $tasaMensual, $plazoMeses) - 1) / $tasaMensual;
        $aporteMensual = $faltante / $factor;

        // Calcular proyección
        $proyeccion = $this->calcularFIC($montoInicial, $aporteMensual, $plazoMeses, $tasaEA);

        return [
            'monto_meta' => round($montoMeta, 2),
            'monto_inicial' => round($montoInicial, 2),
            'aporte_mensual' => round($aporteMensual, 2),
            'total_aportado' => $proyeccion['total_aportado'],
            'valor_final' => $proyeccion['valor_final_neto'],
            'rendimiento_generado' => $proyeccion['rendimiento_neto'],
            'plazo_meses' => $plazoMeses,
            'tasa_usada' => $tasaEA,
            'mensaje' => 'Debes aportar ' . formatoCOP($aporteMensual) . ' mensuales durante ' . $plazoMeses . ' meses.'
        ];
    }
}