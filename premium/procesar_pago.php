<?php
require_once __DIR__ . '/../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validarCsrf()) {
    header('Location: index.php?error=csrf');
    exit;
}

$usuario = usuarioActual();
$metodo = $_POST['metodo_pago'] ?? '';

if (empty($metodo)) {
    header('Location: index.php?error=metodo_invalido');
    exit;
}

// ============================================
// VALIDACIONES SEGÚN MÉTODO DE PAGO
// ============================================

if ($metodo === 'tarjeta_credito' || $metodo === 'tarjeta_debito') {
    // Validar tarjeta
    $numeroTarjeta = str_replace(' ', '', $_POST['numero_tarjeta'] ?? '');
    $nombre = trim($_POST['nombre_tarjeta'] ?? '');
    $mes = intval($_POST['mes_vencimiento'] ?? 0);
    $anio = intval($_POST['anio_vencimiento'] ?? 0);
    $cvv = trim($_POST['cvv'] ?? '');

    if (strlen($numeroTarjeta) !== 16) {
        header('Location: index.php?error=tarjeta_invalida');
        exit;
    }
    if (empty($nombre) || $mes < 1 || $mes > 12 || empty($cvv)) {
        header('Location: index.php?error=tarjeta_invalida');
        exit;
    }

    $detallePago = [
        'metodo' => $metodo,
        'cuotas' => intval($_POST['cuotas'] ?? 1),
        'ultimos4' => substr($numeroTarjeta, -4),
        'fecha' => date('Y-m-d H:i:s')
    ];

} elseif ($metodo === 'nequi' || $metodo === 'daviplata') {
    // Validar billetera digital
    $celular = trim($_POST['celular_billetera'] ?? '');
    $cedula = trim($_POST['cedula_billetera'] ?? '');

    if (strlen($celular) !== 10) {
        header('Location: index.php?error=billetera_invalida');
        exit;
    }
    if (empty($cedula)) {
        header('Location: index.php?error=billetera_invalida');
        exit;
    }

    $detallePago = [
        'metodo' => $metodo,
        'celular' => substr($celular, -4),
        'fecha' => date('Y-m-d H:i:s')
    ];

} elseif ($metodo === 'pse') {
    // Validar PSE
    $banco = trim($_POST['banco_pse'] ?? '');

    if (empty($banco)) {
        header('Location: index.php?error=pse_invalido');
        exit;
    }

    $detallePago = [
        'metodo' => $metodo,
        'banco' => $banco,
        'fecha' => date('Y-m-d H:i:s')
    ];

} else {
    header('Location: index.php?error=metodo_invalido');
    exit;
}

// ============================================
// ACTUALIZAR A PREMIUM
// ============================================

$detalleJson = json_encode($detallePago);

$stmt = $pdo->prepare(
    "UPDATE usuario 
     SET rol = 'premium', 
         premium_desde = NOW(),
         metodo_pago = ?
     WHERE id_usuario = ?"
);
$stmt->execute([$detalleJson, $usuario['id']]);

// Actualizar la sesión
$_SESSION['rol'] = 'premium';

// Desbloquear logro de premium
require_once __DIR__ . '/../app/models/Logro.php';
$modelLogro = new Logro($pdo, $usuario['id']);
if (method_exists($modelLogro, 'desbloquearPremium')) {
    $modelLogro->desbloquearPremium();
}

// Redirigir al dashboard premium
header('Location: /FinanIA/dashboards/premium.php?msg=premium_activado');
exit;
?>