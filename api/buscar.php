<?php
/**
 * API: Búsqueda global
 * 
 * Busca en transacciones, metas, deudas, cuentas y categorías.
 * Devuelve JSON con los resultados agrupados.
 */

require_once __DIR__ . '/../includes/auth.php';
requiereLogin();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$usuario = usuarioActual();
$idUsuario = $usuario['id'];

$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    echo json_encode(['success' => true, 'resultados' => []]);
    exit;
}

$busqueda = '%' . $query . '%';
$resultados = [];

try {
    // ============================================
    // 1. TRANSACCIONES
    // ============================================
    $stmt = $pdo->prepare(
        "SELECT t.id_transaccion AS id, t.descripcion AS titulo, 
                t.monto, t.tipo, t.fecha,
                c.nombre AS categoria
         FROM transaccion t
         LEFT JOIN categoria c ON t.id_categoria = c.id_categoria
         WHERE t.id_usuario = ? 
           AND (t.descripcion LIKE ? OR c.nombre LIKE ?)
         ORDER BY t.fecha DESC
         LIMIT 5"
    );
    $stmt->execute([$idUsuario, $busqueda, $busqueda]);
    $transacciones = $stmt->fetchAll();

    if (!empty($transacciones)) {
        foreach ($transacciones as &$t) {
            $t['url'] = '/FinanIA/modulos/transacciones/index.php?accion=editar&id=' . $t['id'];
            $t['tipo_item'] = 'transaccion';
        }
        $resultados[] = [
            'categoria' => '💸 Transacciones',
            'items' => $transacciones
        ];
    }

    // ============================================
    // 2. METAS
    // ============================================
    $stmt = $pdo->prepare(
        "SELECT id_meta AS id, nombre AS titulo, 
                monto_objetivo, monto_actual, fecha_limite, estado
         FROM meta
         WHERE id_usuario = ? AND nombre LIKE ?
         ORDER BY fecha_limite ASC
         LIMIT 5"
    );
    $stmt->execute([$idUsuario, $busqueda]);
    $metas = $stmt->fetchAll();

    if (!empty($metas)) {
        foreach ($metas as &$m) {
            $m['url'] = '/FinanIA/modulos/metas/index.php?accion=ver&id=' . $m['id'];
            $m['progreso'] = $m['monto_objetivo'] > 0 
                ? round(($m['monto_actual'] / $m['monto_objetivo']) * 100) 
                : 0;
            $m['tipo_item'] = 'meta';
        }
        $resultados[] = [
            'categoria' => '🎯 Metas',
            'items' => $metas
        ];
    }

    // ============================================
    // 3. DEUDAS
    // ============================================
    $stmt = $pdo->prepare(
        "SELECT id_deuda AS id, acreedor AS titulo, 
                monto_total, monto_pagado, tasa_interes, estado
         FROM deuda
         WHERE id_usuario = ? AND acreedor LIKE ?
         ORDER BY fecha_vencimiento ASC
         LIMIT 5"
    );
    $stmt->execute([$idUsuario, $busqueda]);
    $deudas = $stmt->fetchAll();

    if (!empty($deudas)) {
        foreach ($deudas as &$d) {
            $d['url'] = '/FinanIA/modulos/deudas/index.php?accion=ver&id=' . $d['id'];
            $d['pendiente'] = $d['monto_total'] - $d['monto_pagado'];
            $d['tipo_item'] = 'deuda';
        }
        $resultados[] = [
            'categoria' => '💳 Deudas',
            'items' => $deudas
        ];
    }

    // ============================================
    // 4. CUENTAS
    // ============================================
    $stmt = $pdo->prepare(
        "SELECT id_cuenta AS id, nombre AS titulo, 
                tipo, saldo_inicial, color,
                (saldo_inicial 
                 + COALESCE((SELECT SUM(CASE WHEN tipo='ingreso' THEN monto ELSE -monto END) 
                             FROM transaccion WHERE id_cuenta = cuenta.id_cuenta), 0)
                 - COALESCE((SELECT SUM(monto) FROM aporte_meta WHERE id_cuenta = cuenta.id_cuenta), 0)
                ) AS saldo_actual
         FROM cuenta
         WHERE id_usuario = ? AND nombre LIKE ?
         LIMIT 5"
    );
    $stmt->execute([$idUsuario, $busqueda]);
    $cuentas = $stmt->fetchAll();

    if (!empty($cuentas)) {
        foreach ($cuentas as &$c) {
            $c['url'] = '/FinanIA/modulos/cuentas/index.php?accion=editar&id=' . $c['id'];
            $c['tipo_item'] = 'cuenta';
        }
        $resultados[] = [
            'categoria' => '🏦 Cuentas',
            'items' => $cuentas
        ];
    }

    // ============================================
    // 5. CATEGORÍAS
    // ============================================
    $stmt = $pdo->prepare(
        "SELECT id_categoria AS id, nombre AS titulo, 
                tipo, color, icono
         FROM categoria
         WHERE id_usuario = ? AND nombre LIKE ?
         LIMIT 5"
    );
    $stmt->execute([$idUsuario, $busqueda]);
    $categorias = $stmt->fetchAll();

    if (!empty($categorias)) {
        foreach ($categorias as &$c) {
            $c['url'] = '/FinanIA/modulos/categorias/index.php?accion=editar&id=' . $c['id'];
            $c['tipo_item'] = 'categoria';
        }
        $resultados[] = [
            'categoria' => '🏷️ Categorías',
            'items' => $categorias
        ];
    }

    echo json_encode([
        'success' => true,
        'query' => $query,
        'resultados' => $resultados,
        'total' => array_sum(array_map(fn($r) => count($r['items']), $resultados))
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}