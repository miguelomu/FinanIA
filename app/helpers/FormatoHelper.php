<?php
/**
 * Helper: Formato
 * 
 * Funciones para formatear números, monedas y fechas.
 */

/**
 * Formatea un número como moneda colombiana (COP)
 * 
 * @param float $valor
 * @param bool $conSimbolo Si es true, incluye el símbolo $
 * @return string
 */
function formatoCOP($valor, $conSimbolo = true)
{
    $formateado = number_format(floatval($valor), 2, ',', '.');
    return $conSimbolo ? '$' . $formateado : $formateado;
}

/**
 * Formatea una fecha en formato dd/mm/YYYY
 * 
 * @param string $fecha Fecha en formato MySQL (Y-m-d)
 * @return string
 */
function formatoFecha($fecha)
{
    if (empty($fecha)) return '—';
    return date('d/m/Y', strtotime($fecha));
}

/**
 * Formatea una fecha con hora (dd/mm/YYYY HH:MM)
 * 
 * @param string $fecha
 * @return string
 */
function formatoFechaHora($fecha)
{
    if (empty($fecha)) return '—';
    return date('d/m/Y H:i', strtotime($fecha));
}

/**
 * Formatea un porcentaje con 1 decimal
 * 
 * @param float $valor
 * @return string
 */
function formatoPorcentaje($valor)
{
    return number_format(floatval($valor), 1) . '%';
}

/**
 * Devuelve el nombre del mes en español
 * 
 * @param int $numeroMes 1-12
 * @return string
 */
function nombreMes($numeroMes)
{
    $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    return $meses[$numeroMes] ?? '';
}

/**
 * Calcula el porcentaje de progreso de un valor sobre un total
 * 
 * @param float $actual
 * @param float $total
 * @param float $maximo Tope máximo (por defecto 100)
 * @return float
 */
function calcularProgreso($actual, $total, $maximo = 100)
{
    if ($total <= 0) return 0;
    return min($maximo, ($actual / $total) * 100);
}

/**
 * Devuelve la clase CSS de la barra de progreso según el valor
 * 
 * @param float $progreso
 * @param bool $invertido Si es true, más progreso = peor (gastos)
 * @return string
 */
function claseBarraProgreso($progreso, $invertido = false)
{
    if ($invertido) {
        if ($progreso >= 100) return 'danger';
        if ($progreso >= 80) return 'warning';
        return 'success';
    }

    if ($progreso >= 100) return 'success';
    if ($progreso >= 50) return '';
    return 'warning';
}

/**
 * Calcula la diferencia en días desde hoy hasta una fecha
 * 
 * @param string $fecha
 * @return int Días (positivo si es futuro, negativo si ya pasó)
 */
function diasHasta($fecha)
{
    if (empty($fecha)) return 0;
    return (int) floor((strtotime($fecha) - time()) / 86400);
}