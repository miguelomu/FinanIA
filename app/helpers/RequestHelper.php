<?php
/**
 * Helper: Request
 * 
 * Funciones para manejar peticiones GET, POST y redirecciones.
 */

/**
 * Obtiene un valor de $_POST de forma segura
 * 
 * @param string $clave
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function post($clave, $default = null)
{
    return $_POST[$clave] ?? $default;
}

/**
 * Obtiene un valor de $_GET de forma segura
 * 
 * @param string $clave
 * @param mixed $default Valor por defecto
 * @return mixed
 */
function get($clave, $default = null)
{
    return $_GET[$clave] ?? $default;
}

/**
 * Obtiene un valor de $_POST como string limpio
 * 
 * @param string $clave
 * @param string $default
 * @return string
 */
function postStr($clave, $default = '')
{
    return trim((string) ($_POST[$clave] ?? $default));
}

/**
 * Obtiene un valor de $_POST como entero
 * 
 * @param string $clave
 * @param int $default
 * @return int
 */
function postInt($clave, $default = 0)
{
    return intval($_POST[$clave] ?? $default);
}

/**
 * Obtiene un valor de $_POST como decimal
 * 
 * @param string $clave
 * @param float $default
 * @return float
 */
function postFloat($clave, $default = 0)
{
    return floatval($_POST[$clave] ?? $default);
}

/**
 * Obtiene un valor de $_GET como entero
 * 
 * @param string $clave
 * @param int $default
 * @return int
 */
function getInt($clave, $default = 0)
{
    return intval($_GET[$clave] ?? $default);
}

/**
 * Verifica si la petición es POST
 * 
 * @return bool
 */
function esPost()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Verifica si la petición es GET
 * 
 * @return bool
 */
function esGet()
{
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Redirige a una URL y detiene la ejecución
 * 
 * @param string $url
 */
function redirigir($url)
{
    header("Location: $url");
    exit;
}

/**
 * Redirige si la petición NO es POST
 * 
 * @param string $urlDestino URL a la que redirigir si no es POST
 */
function requierePost($urlDestino = 'index.php')
{
    if (!esPost()) {
        redirigir($urlDestino);
    }
}