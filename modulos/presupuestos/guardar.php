<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$mes = intval($_POST['mes'] ?? 0);
$anio = intval($_POST['anio'] ?? 0);
$limites = $_POST['limites'] ?? [];

if ($mes < 1 || $mes > 12 || $anio < 2000) {
    header('Location: crear.php?error=campos_vacios');
    exit;
}

// Verificar que no exista ya
$stmt = $pdo->prepare("SELECT id_presupuesto FROM presupuesto WHERE id_usuario = ? AND mes = ? AND anio = ?");
$stmt->execute([$usuario['id'], $mes, $anio]);
if ($stmt->fetch()) {
    header('Location: crear.php?error=ya_existe');
    exit;
}

// Calcular monto total = suma de límites
$montoTotal = 0;
foreach ($limites as $id_cat => $limite) {
    $montoTotal += floatval($limite);
}

$pdo->beginTransaction();

try {
    // Insertar presupuesto
    $stmt = $pdo->prepare("INSERT INTO presupuesto (id_usuario, mes, anio, monto_total) VALUES (?, ?, ?, ?)");
    $stmt->execute([$usuario['id'], $mes, $anio, $montoTotal]);
    $idPresupuesto = $pdo->lastInsertId();
    
    // Insertar detalles
    $stmt = $pdo->prepare("INSERT INTO detalle_presupuesto (id_presupuesto, id_categoria, limite) VALUES (?, ?, ?)");
    foreach ($limites as $id_cat => $limite) {
        $limiteVal = floatval($limite);
        if ($limiteVal >= 0) {
            // Verificar que la categoría pertenece al usuario
            $check = $pdo->prepare("SELECT id_categoria FROM categoria WHERE id_categoria = ? AND id_usuario = ?");
            $check->execute([$id_cat, $usuario['id']]);
            if ($check->fetch()) {
                $stmt->execute([$idPresupuesto, $id_cat, $limiteVal]);
            }
        }
    }
    
    $pdo->commit();
    header('Location: index.php?msg=creado');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: crear.php?error=campos_vacios');
    exit;
}
?>