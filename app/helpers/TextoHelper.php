<?php
/**
 * Helper: Texto
 * 
 * Funciones para manipular y escapar texto.
 */

/**
 * Escapa HTML para prevenir XSS
 * 
 * Uso: <?= e($variable) ?>
 * 
 * @param string $texto
 * @return string
 */
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Capitaliza la primera letra
 * 
 * @param string $texto
 * @return string
 */
function capitalizar($texto)
{
    return ucfirst((string) $texto);
}

/**
 * Trunca un texto a una longitud máxima
 * 
 * @param string $texto
 * @param int $longitud
 * @param string $sufijo
 * @return string
 */
function truncar($texto, $longitud = 50, $sufijo = '...')
{
    $texto = (string) $texto;
    if (mb_strlen($texto) <= $longitud) {
        return $texto;
    }
    return mb_substr($texto, 0, $longitud) . $sufijo;
}

/**
 * Muestra un guion si el valor está vacío
 * 
 * @param mixed $valor
 * @param string $reemplazo
 * @return string
 */
function oGuion($valor, $reemplazo = '—')
{
    return empty($valor) && $valor !== '0' ? $reemplazo : e($valor);
}

/**
 * Convierte un texto a slug (URL-friendly)
 * 
 * @param string $texto
 * @return string
 */
function slug($texto)
{
    $texto = strtolower(trim((string) $texto));
    $texto = preg_replace('/[^a-z0-9-]+/', '-', $texto);
    return trim($texto, '-');
}