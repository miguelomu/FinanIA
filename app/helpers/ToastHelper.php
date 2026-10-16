<?php
/**
 * Helper: Toast
 * 
 * Sistema para disparar notificaciones toast desde el servidor.
 * 
 * Uso:
 *   toastSuccess('Transacción creada', 'La transacción se guardó correctamente');
 *   redirigir('index.php');
 * 
 * O directo:
 *   flashToast('success', 'Éxito', 'Mensaje');
 *   redirigir('index.php');
 */

/**
 * Guarda un toast en la sesión para mostrarlo en la próxima página
 */
function flashToast($tipo, $titulo, $mensaje = '')
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['toasts'])) {
        $_SESSION['toasts'] = [];
    }
    
    $_SESSION['toasts'][] = [
        'tipo' => $tipo,
        'titulo' => $titulo,
        'mensaje' => $mensaje
    ];
}

/**
 * Atajos por tipo
 */
function toastSuccess($titulo, $mensaje = '')
{
    flashToast('success', $titulo, $mensaje);
}

function toastError($titulo, $mensaje = '')
{
    flashToast('error', $titulo, $mensaje);
}

function toastWarning($titulo, $mensaje = '')
{
    flashToast('warning', $titulo, $mensaje);
}

function toastInfo($titulo, $mensaje = '')
{
    flashToast('info', $titulo, $mensaje);
}

/**
 * Renderiza los toasts pendientes y los limpia de la sesión.
 * Se llama desde el footer.php
 */
function renderToasts()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['toasts'])) {
        return '';
    }
    
    $toasts = $_SESSION['toasts'];
    unset($_SESSION['toasts']);
    
    $js = '<script>';
    $js .= 'document.addEventListener("DOMContentLoaded", function() {';
    foreach ($toasts as $t) {
        $tipo = addslashes($t['tipo']);
        $titulo = addslashes($t['titulo']);
        $mensaje = addslashes($t['mensaje']);
        $js .= "toast('$tipo', '$titulo', '$mensaje');";
    }
    $js .= '});';
    $js .= '</script>';
    
    return $js;
}