<?php
/**
 * Helper: CSRF
 * 
 * Genera y valida tokens CSRF para proteger formularios.
 */

/**
 * Obtiene el token CSRF de la sesión actual.
 * Si no existe, lo genera.
 */
function csrfToken()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Genera el input hidden con el token CSRF.
 * Uso en formularios: <?= csrfField() ?>
 */
function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * Valida el token CSRF recibido en el POST.
 * 
 * @param string|null $token Token recibido (por defecto lo toma de $_POST)
 * @return bool
 */
function validarCsrf($token = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }

    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verifica el token y redirige con error si es inválido.
 * 
 * @param string $urlError URL a la que redirigir si falla
 */
function requiereCsrf($urlError = 'index.php?error=csrf')
{
    if (!validarCsrf()) {
        redirigir($urlError);
    }
}