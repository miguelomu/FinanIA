<?php
require_once __DIR__ . '/../../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../../config/database.php';

$usuario = usuarioActual();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = intval($_POST['id'] ?? 0);
$mes = intval($_POST['mes'] ?? 0);
$anio = intval($_POST['anio'] ?? 0);
$limites = $_POST['limites'] ?? [];

// Verificar pertenencia
$stmt = $pdo->prepare("SELECT id_presupuesto FROM presupuesto WHERE id_presupuesto = ? AND id_usuario = ?");
$stmt->execute([$id, $usuario['id']]);
if (!$stmt->fetch()) {
    header('Location: index.php');
    exit;
}

// Verificar que no exista otro presupuesto con ese mes/año
$stmt = $pdo->prepare("SELECT id_presupuesto FROM presupuesto WHERE id_usuario = ? AND mes = ? AND anio = ? AND id_presupuesto != ?");
$stmt->execute([$usuario['id'], $mes, $anio, $id]);
if ($stmt->fetch()) {
    header("Location: editar.php?id=$id&error=ya_existe");
    exit;
}

$montoTotal = 0;
foreach ($limites as $limite) {
    $montoTotal += floatval($limite);
}

$pdo->beginTransaction();

try {
    // Actualizar presupuesto
    $stmt = $pdo->prepare("UPDATE presupuesto SET mes = ?, anio = ?, monto_total = ? WHERE id_presupuesto = ?");
    $stmt->execute([$mes, $anio, $montoTotal, $id]);
    
    // Eliminar detalles anteriores
    $stmt = $pdo->prepare("DELETE FROM detalle_presupuesto WHERE id_presupuesto = ?");
    $stmt->execute([$id]);
    
    // Insertar nuevos detalles
    $stmt = $pdo->prepare("INSERT INTO detalle_presupuesto (id_presupuesto, id_categoria, limite) VALUES (?, ?, ?)");
    foreach ($limites as $id_cat => $limite) {
        $limiteVal = floatval($limite);
        if ($limiteVal >= 0) {
            $check = $pdo->prepare("SELECT id_categoria FROM categoria WHERE id_categoria = ? AND id_usuario = ?");
            $check->execute([$id_cat, $usuario['id']]);
            if ($check->fetch()) {
                $stmt->execute([$id, $id_cat, $limiteVal]);
            }
        }
    }
    
    $pdo->commit();
    header('Location: index.php?msg=actualizado');
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: editar.php?id=$id&error=campos_vacios");
    exit;
}
?>