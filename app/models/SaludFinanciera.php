<?php
/**
 * Modelo: SaludFinanciera
 * 
 * Calcula un puntaje de salud financiera (0-100) basado en:
 * - Fondo de emergencia (25 puntos)
 * - Nivel de deuda (25 puntos)
 * - Tasa de ahorro (20 puntos)
 * - Diversificación de ingresos (15 puntos)
 * - Estabilidad de ingresos (15 puntos)
 */

class SaludFinanciera
{
    private $pdo;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->idUsuario = $idUsuario;
    }

    /**
     * Calcula el puntaje completo
     */
    public function calcular()
    {
        $fondo = $this->evaluarFondoEmergencia();
        $deuda = $this->evaluarNivelDeuda();
        $ahorro = $this->evaluarTasaAhorro();
        $diversificacion = $this->evaluarDiversificacion();
        $estabilidad = $this->evaluarEstabilidad();

        $total = $fondo['puntos'] + $deuda['puntos'] + $ahorro['puntos'] 
               + $diversificacion['puntos'] + $estabilidad['puntos'];

        return [
            'total' => round($total),
            'categoria' => $this->categorizar($total),
            'componentes' => [
                'fondo_emergencia' => $fondo,
                'nivel_deuda' => $deuda,
                'tasa_ahorro' => $ahorro,
                'diversificacion' => $diversificacion,
                'estabilidad' => $estabilidad
            ]
        ];
    }

    /**
     * Fondo de emergencia (25 puntos)
     * Ideal: 6+ meses de gastos
     */
    private function evaluarFondoEmergencia()
    {
        // Gastos fijos mensuales promedio (últimos 3 meses)
        $stmt = $this->pdo->prepare(
            "SELECT AVG(gasto_mensual) AS promedio FROM (
                SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, SUM(monto) AS gasto_mensual
                FROM transaccion 
                WHERE id_usuario = ? AND tipo='gasto' 
                  AND fecha >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                GROUP BY mes
            ) AS sub"
        );
        $stmt->execute([$this->idUsuario]);
        $gastoMensual = $stmt->fetch()['promedio'] ?? 0;

        // Saldo actual en cuentas
        $stmt = $this->pdo->prepare(
            "SELECT 
                COALESCE(SUM(saldo_inicial),0) 
                + COALESCE((SELECT SUM(CASE WHEN tipo='ingreso' THEN monto ELSE -monto END) 
                            FROM transaccion WHERE id_usuario = ?),0)
                - COALESCE((SELECT SUM(monto) FROM aporte_meta WHERE id_usuario = ?),0)
                - COALESCE((SELECT SUM(monto) FROM pago_deuda WHERE id_usuario = ?),0)
                AS saldo_actual
             FROM cuenta WHERE id_usuario = ? AND activa = TRUE"
        );
        $stmt->execute([$this->idUsuario, $this->idUsuario, $this->idUsuario, $this->idUsuario]);
        $saldoActual = $stmt->fetch()['saldo_actual'] ?? 0;

        if ($gastoMensual <= 0) {
            return [
                'puntos' => 0,
                'valor' => 0,
                'descripcion' => 'Sin datos suficientes',
                'recomendacion' => 'Registra gastos para calcular tu fondo de emergencia',
                'estado' => 'neutral'
            ];
        }

        $mesesCubiertos = $saldoActual / $gastoMensual;
        
        // Puntaje: 0 a 25 puntos
        // 0 meses = 0 puntos
        // 3 meses = 15 puntos (mínimo aceptable)
        // 6 meses = 25 puntos (ideal)
        if ($mesesCubiertos >= 6) {
            $puntos = 25;
        } elseif ($mesesCubiertos >= 3) {
            $puntos = 15 + (($mesesCubiertos - 3) / 3) * 10;
        } else {
            $puntos = ($mesesCubiertos / 3) * 15;
        }

        $puntos = max(0, min(25, $puntos));

        if ($mesesCubiertos >= 6) {
            $estado = 'excelente';
            $recomendacion = '¡Excelente! Tienes un fondo de emergencia sólido.';
        } elseif ($mesesCubiertos >= 3) {
            $estado = 'bueno';
            $recomendacion = 'Bien, pero podrías reforzar hasta 6 meses para mayor seguridad.';
        } else {
            $estado = 'alerta';
            $recomendacion = 'Prioridad: completa tu fondo de emergencia antes de invertir.';
        }

        return [
            'puntos' => round($puntos, 1),
            'valor' => round($mesesCubiertos, 1),
            'descripcion' => number_format($mesesCubiertos, 1) . ' meses cubiertos',
            'recomendacion' => $recomendacion,
            'estado' => $estado
        ];
    }

    /**
     * Nivel de deuda (25 puntos)
     * Ideal: menos del 20% de ingresos en cuotas
     */
    private function evaluarNivelDeuda()
    {
        // Ingreso mensual promedio
        $stmt = $this->pdo->prepare(
            "SELECT AVG(ingreso_mensual) AS promedio FROM (
                SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, SUM(monto) AS ingreso_mensual
                FROM transaccion 
                WHERE id_usuario = ? AND tipo='ingreso'
                  AND fecha >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                GROUP BY mes
            ) AS sub"
        );
        $stmt->execute([$this->idUsuario]);
        $ingresoMensual = $stmt->fetch()['promedio'] ?? 0;

        // Total de deudas pendientes
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(monto_total - monto_pagado),0) AS total,
                    COALESCE(SUM(pago_minimo),0) AS cuotas
             FROM deuda WHERE id_usuario = ? AND estado != 'pagada'"
        );
        $stmt->execute([$this->idUsuario]);
        $r = $stmt->fetch();
        $deudaTotal = $r['total'] ?? 0;
        $cuotas = $r['cuotas'] ?? 0;

        if ($deudaTotal <= 0) {
            return [
                'puntos' => 25,
                'valor' => 0,
                'descripcion' => 'Sin deudas',
                'recomendacion' => '¡Perfecto! No tienes deudas.',
                'estado' => 'excelente'
            ];
        }

        // Ratio cuota/ingreso
        $ratio = $ingresoMensual > 0 ? ($cuotas / $ingresoMensual) * 100 : 100;

        // Puntaje
        if ($ratio <= 10) {
            $puntos = 25;
            $estado = 'excelente';
            $recomendacion = 'Tu nivel de deuda es saludable.';
        } elseif ($ratio <= 20) {
            $puntos = 18;
            $estado = 'bueno';
            $recomendacion = 'Deuda manejable. Procura no aumentar.';
        } elseif ($ratio <= 35) {
            $puntos = 10;
            $estado = 'alerta';
            $recomendacion = 'Tu nivel de deuda está en zona de riesgo. Prioriza pagarlas.';
        } else {
            $puntos = 3;
            $estado = 'critico';
            $recomendacion = '¡Alerta! Tus deudas superan el 35% de tus ingresos.';
        }

        return [
            'puntos' => $puntos,
            'valor' => round($ratio, 1),
            'descripcion' => number_format($ratio, 1) . '% de ingresos en cuotas',
            'recomendacion' => $recomendacion,
            'estado' => $estado
        ];
    }

    /**
     * Tasa de ahorro (20 puntos)
     * Ideal: 20%+ de ingresos
     */
    private function evaluarTasaAhorro()
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                COALESCE(AVG(ingreso),0) AS ingreso,
                COALESCE(AVG(gasto),0) AS gasto
             FROM (
                SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
                    SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END) AS ingreso,
                    SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END) AS gasto
                FROM transaccion 
                WHERE id_usuario = ? AND fecha >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                GROUP BY mes
             ) AS sub"
        );
        $stmt->execute([$this->idUsuario]);
        $r = $stmt->fetch();
        $ingresoProm = $r['ingreso'] ?? 0;
        $gastoProm = $r['gasto'] ?? 0;

        if ($ingresoProm <= 0) {
            return [
                'puntos' => 0,
                'valor' => 0,
                'descripcion' => 'Sin datos suficientes',
                'recomendacion' => 'Registra tus ingresos para calcular tu tasa de ahorro.',
                'estado' => 'neutral'
            ];
        }

        $ahorro = $ingresoProm - $gastoProm;
        $tasa = ($ahorro / $ingresoProm) * 100;

        if ($tasa >= 20) {
            $puntos = 20;
            $estado = 'excelente';
        } elseif ($tasa >= 15) {
            $puntos = 15;
            $estado = 'bueno';
        } elseif ($tasa >= 10) {
            $puntos = 10;
            $estado = 'regular';
        } elseif ($tasa >= 0) {
            $puntos = 5;
            $estado = 'alerta';
        } else {
            $puntos = 0;
            $estado = 'critico';
        }

        if ($tasa >= 20) {
            $recomendacion = '¡Excelente tasa de ahorro!';
        } elseif ($tasa >= 10) {
            $recomendacion = 'Buena tasa. Intenta llegar al 20% para máximo puntaje.';
        } elseif ($tasa >= 0) {
            $recomendacion = 'Ahorras poco. Revisa tus gastos variables.';
        } else {
            $recomendacion = 'Estás gastando más de lo que ganas. ¡Urgente!';
        }

        return [
            'puntos' => $puntos,
            'valor' => round($tasa, 1),
            'descripcion' => number_format($tasa, 1) . '% de tus ingresos',
            'recomendacion' => $recomendacion,
            'estado' => $estado
        ];
    }

    /**
     * Diversificación de ingresos (15 puntos)
     * Ideal: 3+ fuentes de ingreso
     */
    private function evaluarDiversificacion()
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(DISTINCT id_categoria) AS fuentes
             FROM transaccion 
             WHERE id_usuario = ? AND tipo='ingreso' 
               AND fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)"
        );
        $stmt->execute([$this->idUsuario]);
        $fuentes = $stmt->fetch()['fuentes'] ?? 0;

        if ($fuentes >= 3) {
            $puntos = 15;
            $estado = 'excelente';
            $recomendacion = 'Excelente diversificación de ingresos.';
        } elseif ($fuentes === 2) {
            $puntos = 10;
            $estado = 'bueno';
            $recomendacion = 'Buena, pero considera agregar más fuentes.';
        } elseif ($fuentes === 1) {
            $puntos = 5;
            $estado = 'alerta';
            $recomendacion = 'Dependes de una sola fuente. Es riesgoso.';
        } else {
            $puntos = 0;
            $estado = 'neutral';
            $recomendacion = 'Sin ingresos registrados en los últimos 6 meses.';
        }

        return [
            'puntos' => $puntos,
            'valor' => $fuentes,
            'descripcion' => $fuentes . ' fuente(s) de ingreso',
            'recomendacion' => $recomendacion,
            'estado' => $estado
        ];
    }

    /**
     * Estabilidad de ingresos (15 puntos)
     * Ideal: ingresos regulares
     */
    private function evaluarEstabilidad()
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                AVG(monto) AS promedio,
                STDDEV(monto) AS desviacion
             FROM (
                SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, SUM(monto) AS monto
                FROM transaccion 
                WHERE id_usuario = ? AND tipo='ingreso'
                  AND fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY mes
             ) AS sub"
        );
        $stmt->execute([$this->idUsuario]);
        $r = $stmt->fetch();
        $promedio = $r['promedio'] ?? 0;
        $desviacion = $r['desviacion'] ?? 0;

        if ($promedio <= 0) {
            return [
                'puntos' => 0,
                'valor' => 0,
                'descripcion' => 'Sin datos suficientes',
                'recomendacion' => 'Registra más ingresos para evaluar.',
                'estado' => 'neutral'
            ];
        }

        $estabilidad = max(0, 100 - ($desviacion / $promedio * 100));

        if ($estabilidad >= 80) {
            $puntos = 15;
            $estado = 'excelente';
            $recomendacion = 'Ingresos muy estables.';
        } elseif ($estabilidad >= 60) {
            $puntos = 11;
            $estado = 'bueno';
            $recomendacion = 'Ingresos relativamente estables.';
        } elseif ($estabilidad >= 40) {
            $puntos = 7;
            $estado = 'regular';
            $recomendacion = 'Ingresos variables. Considera diversificar.';
        } else {
            $puntos = 3;
            $estado = 'alerta';
            $recomendacion = 'Ingresos muy irregulares. Prioriza fondo de emergencia.';
        }

        return [
            'puntos' => $puntos,
            'valor' => round($estabilidad, 1),
            'descripcion' => number_format($estabilidad, 0) . '% de estabilidad',
            'recomendacion' => $recomendacion,
            'estado' => $estado
        ];
    }

    /**
     * Categoriza el puntaje total
     */
    private function categorizar($total)
    {
        if ($total >= 85) return ['nombre' => 'Excelente', 'color' => '#27ae60', 'emoji' => '🌟'];
        if ($total >= 70) return ['nombre' => 'Bueno', 'color' => '#2ecc71', 'emoji' => '✅'];
        if ($total >= 50) return ['nombre' => 'Regular', 'color' => '#f39c12', 'emoji' => '⚠️'];
        if ($total >= 30) return ['nombre' => 'Deficiente', 'color' => '#e67e22', 'emoji' => '🔶'];
        return ['nombre' => 'Crítico', 'color' => '#e74c3c', 'emoji' => '🚨'];
    }
}