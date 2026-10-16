<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../app/models/PerfilRiesgo.php';
require_once __DIR__ . '/../app/models/Logro.php';
requiereLogin();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: onboarding.php');
    exit;
}

if (!validarCsrf()) {
    header('Location: onboarding.php?error=csrf');
    exit;
}

$usuario = usuarioActual();
$idUsuario = $usuario['id'];

$pdo->beginTransaction();

try {
    // ============================================
    // 1. DATOS DEL PERFIL PERSONAL
    // ============================================
    $ocupacion = $_POST['ocupacion'] ?? null;
    $tipoIngreso = $_POST['tipo_ingreso'] ?? null;
    $dependientes = intval($_POST['dependientes'] ?? 0);

    // ============================================
    // 2. SITUACIÓN FINANCIERA
    // ============================================
    $tieneBanco = intval($_POST['tiene_banco'] ?? 1);
    $tieneFondo = intval($_POST['tiene_fondo_emergencia'] ?? 0);
    $tieneDeudas = intval($_POST['tiene_deudas'] ?? 0);
    $pagoDeudas = floatval($_POST['pago_mensual_deudas'] ?? 0);

    // ============================================
    // 3. INGRESO MENSUAL
    // ============================================
    $ingresoMensual = floatval($_POST['ingreso_mensual'] ?? 0);

    // ============================================
    // 4. PERFIL DE RIESGO
    // ============================================
    $horizonte = $_POST['horizonte_inversion'] ?? 'corto';
    $reaccion = $_POST['reaccion_caida'] ?? 'vender_todo';
    $objetivo = $_POST['objetivo_dinero'] ?? 'preservar';
    $ahorro = $_POST['porcentaje_ahorro'] ?? 'cero';
    $experiencia = $_POST['experiencia_inversion'] ?? 'ninguna';

    $perfilCalculado = PerfilRiesgo::calcular([
        'horizonte' => $horizonte,
        'reaccion' => $reaccion,
        'objetivo' => $objetivo,
        'ahorro' => $ahorro,
        'experiencia' => $experiencia
    ]);

    // ============================================
    // 5. ACTUALIZAR USUARIO CON TODOS LOS DATOS
    // ============================================
    $stmt = $pdo->prepare(
        "UPDATE usuario SET 
            ocupacion = ?,
            tipo_ingreso = ?,
            dependientes = ?,
            tiene_banco = ?,
            tiene_fondo_emergencia = ?,
            tiene_deudas = ?,
            pago_mensual_deudas = ?,
            ingreso_mensual = ?,
            horizonte_inversion = ?,
            reaccion_caida = ?,
            objetivo_dinero = ?,
            porcentaje_ahorro = ?,
            experiencia_inversion = ?,
            perfil_riesgo = ?,
            onboarding_completado = TRUE
         WHERE id_usuario = ?"
    );
    $stmt->execute([
        $ocupacion, $tipoIngreso, $dependientes,
        $tieneBanco, $tieneFondo, $tieneDeudas, $pagoDeudas,
        $ingresoMensual,
        $horizonte, $reaccion, $objetivo, $ahorro, $experiencia,
        $perfilCalculado['perfil'],
        $idUsuario
    ]);

        // ============================================
        // 6. CREAR CUENTA DE EFECTIVO (siempre)
        // ============================================
        $efectivo = floatval($_POST['efectivo'] ?? 0);

        if ($efectivo > 0) {
            $stmt = $pdo->prepare(
                "INSERT INTO cuenta (id_usuario, nombre, tipo, saldo_inicial, etiqueta, color) 
                VALUES (?, 'Efectivo', 'efectivo', ?, 'Dinero en mano', '#27ae60')"
            );
            $stmt->execute([$idUsuario, $efectivo]);
        }

            // ============================================
            // 7. CREAR CUENTA BANCARIA (si tiene)
            // ============================================
            // Usar la respuesta del paso 2 (tiene_banco)
            if ($tieneBanco === 1) {
                $cuentaNombre = trim($_POST['cuenta_nombre'] ?? 'Mi cuenta');
                $cuentaTipo = $_POST['cuenta_tipo'] ?? 'ahorros';
                $cuentaSaldo = floatval($_POST['cuenta_saldo'] ?? 0);

                if (!empty($cuentaNombre)) {
                    $stmt = $pdo->prepare(
                        "INSERT INTO cuenta (id_usuario, nombre, tipo, saldo_inicial, etiqueta, color) 
                        VALUES (?, ?, ?, ?, 'Cuenta principal', '#3498db')"
                    );
                    $stmt->execute([$idUsuario, $cuentaNombre, $cuentaTipo, $cuentaSaldo]);
                }
            }

    // ============================================
    // 7. CREAR META
    // ============================================
    $metaNombre = trim($_POST['meta_nombre'] ?? 'Fondo de emergencia');
    $metaMonto = floatval($_POST['meta_monto'] ?? 1000000);
    $metaFecha = $_POST['meta_fecha'] ?? date('Y-m-d', strtotime('+6 months'));

    if (!empty($metaNombre) && $metaMonto > 0) {
        $stmt = $pdo->prepare(
            "INSERT INTO meta (id_usuario, nombre, monto_objetivo, monto_inicial, monto_actual, 
                               fecha_inicio, fecha_limite, estado) 
             VALUES (?, ?, ?, 0, 0, ?, ?, 'activa')"
        );
        $stmt->execute([$idUsuario, $metaNombre, $metaMonto, date('Y-m-d'), $metaFecha]);
    }

    // ============================================
    // 8. CREAR CATEGORÍAS
    // ============================================
    $categorias = $_POST['categorias'] ?? [];

    $categoriasMapa = [
        'salario' => ['nombre' => 'Salario', 'tipo' => 'ingreso', 'color' => '#27ae60', 'icono' => '💼'],
        'comida' => ['nombre' => 'Comida', 'tipo' => 'gasto', 'color' => '#e74c3c', 'icono' => '🍔'],
        'transporte' => ['nombre' => 'Transporte', 'tipo' => 'gasto', 'color' => '#f39c12', 'icono' => '🚗'],
        'entretenimiento' => ['nombre' => 'Entretenimiento', 'tipo' => 'gasto', 'color' => '#9b59b6', 'icono' => '🎮'],
        'servicios' => ['nombre' => 'Servicios', 'tipo' => 'gasto', 'color' => '#3498db', 'icono' => '💡'],
        'otros' => ['nombre' => 'Otros', 'tipo' => 'gasto', 'color' => '#7f8c8d', 'icono' => '📦']
    ];

    $stmt = $pdo->prepare(
        "INSERT INTO categoria (id_usuario, nombre, tipo, color, icono) VALUES (?, ?, ?, ?, ?)"
    );

    foreach ($categorias as $key) {
        if (isset($categoriasMapa[$key])) {
            $c = $categoriasMapa[$key];
            $stmt->execute([$idUsuario, $c['nombre'], $c['tipo'], $c['color'], $c['icono']]);
        }
    }

    $pdo->commit();

    // ============================================
    // 9. DESBLOQUEAR LOGROS
    // ============================================
    $modelLogro = new Logro($pdo, $idUsuario);
    $modelLogro->verificarTodos();

    // ============================================
    // 10. GUARDAR PERFIL EN SESIÓN PARA LA VISTA FINAL
    // ============================================
    $_SESSION['perfil_calculado'] = $perfilCalculado;

    // ============================================
    // 11. REDIRIGIR A PANTALLA DE BIENVENIDA
    // ============================================
    header('Location: /FinanIA/modulos/bienvenida/index.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: onboarding.php?error=error_guardado');
    exit;
}