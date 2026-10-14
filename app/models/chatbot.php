<?php
/**
 * Modelo: Chatbot
 * 
 * Analiza los mensajes del usuario, identifica intenciones
 * y genera respuestas basadas en sus datos reales.
 */

class Chatbot
{
    private $pdo;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->idUsuario = $idUsuario;
    }

    /**
     * Procesa un mensaje y devuelve la respuesta
     */
    public function procesar($mensaje)
    {
        $mensaje = trim($mensaje);
        $mensaje_lower = mb_strtolower($mensaje, 'UTF-8');

        // Guardar mensaje del usuario
        $this->guardarMensaje('user', $mensaje);

        // Detectar intención
        $intencion = $this->detectarIntencion($mensaje_lower);

        // Generar respuesta según intención
        $respuesta = $this->generarRespuesta($intencion, $mensaje, $mensaje_lower);

        // Guardar respuesta del bot
        $this->guardarMensaje('bot', $respuesta, $intencion);

        return [
            'respuesta' => $respuesta,
            'intencion' => $intencion
        ];
    }

    /**
     * Detecta la intención del mensaje
     */
    private function detectarIntencion($msg)
    {
        // Patrones de intención (en orden de prioridad)
        $patrones = [
            // Saludos
            'saludo' => ['hola', 'buenos días', 'buenas tardes', 'buenas noches', 'hey', 'qué tal'],

            // Acciones (crear registros)
            'crear_gasto' => ['registra un gasto', 'agrega un gasto', 'gasté', 'pagué', 'añade gasto'],
            'crear_ingreso' => ['registra un ingreso', 'agrega un ingreso', 'recibí', 'me pagaron', 'añade ingreso'],
            'crear_meta' => ['crea una meta', 'nueva meta', 'quiero ahorrar para', 'meta de ahorro'],
            'crear_deuda' => ['crea una deuda', 'nueva deuda', 'debo a', 'tengo una deuda con'],
            'crear_cuenta' => ['crea una cuenta', 'nueva cuenta', 'agregar cuenta'],
            'crear_contacto' => ['crea un contacto', 'nuevo contacto', 'agregar amigo'],

            // Consultas de patrimonio
            'consultar_patrimonio' => ['cuánto tengo', 'patrimonio', 'cuánto dinero tengo', 'mi capital'],
            'consultar_deudas' => ['cuánto debo', 'mis deudas', 'deuda total', 'cuánto debo en total'],
            'consultar_balance' => ['cómo van mis finanzas', 'balance', 'cuánto he gastado', 'resumen del mes'],
            'consultar_ahorro' => ['cuánto he ahorrado', 'mi ahorro', 'ahorros'],
            'consultar_cuentas' => ['mis cuentas', 'cuántas cuentas', 'saldos de cuentas'],
            'consultar_me_deben' => ['cuánto me deben', 'quién me debe', 'deudas a favor'],

            // Consultas de categorías
            'consultar_gasto_categoria' => ['cuánto gasté en', 'gasto en', 'cuánto llevo en'],

            // Predicciones
            'proyeccion_ahorro' => ['podré ahorrar', 'puedo ahorrar', 'alcanzaré a ahorrar', 'cuánto tiempo para ahorrar'],
            'proyeccion_deuda' => ['cuándo termino de pagar', 'cuándo pagaré', 'cuánto tardaré en pagar'],
            'proyeccion_compra' => ['puedo comprar', 'podré comprar', 'me alcanza para'],
            'proyeccion_futura' => ['cuánto tendré en', 'cómo estaré en', 'proyección a'],

            // Recomendaciones
            'recomendacion_inversion' => ['debo invertir', 'puedo invertir', 'dónde invierto', 'me conviene invertir'],
            'recomendacion_prestamo' => ['debo pedir un préstamo', 'me conviene un crédito', 'puedo pedir prestado'],
            'recomendacion_ahorro' => ['cómo ahorro más', 'cómo ahorrar', 'consejos para ahorrar'],
            'recomendacion_deuda' => ['cómo pago mis deudas', 'cómo salgo de deudas', 'estrategia de deuda'],

            // Educación
            'educacion_cdt' => ['qué es un cdt', 'cómo funciona un cdt', 'cdt'],
            'educacion_usura' => ['qué es la tasa de usura', 'tasa de usura'],
            'educacion_fondo' => ['fondo de emergencia', 'qué es un fondo de emergencia'],
            'educacion_interes' => ['interés compuesto', 'qué es el interés'],

            // Ayuda
            'ayuda' => ['qué puedes hacer', 'ayuda', 'cómo funcionas', 'qué haces', 'opciones']
        ];

        foreach ($patrones as $intencion => $keywords) {
            foreach ($keywords as $kw) {
                if (mb_strpos($msg, $kw) !== false) {
                    return $intencion;
                }
            }
        }

        return 'desconocida';
    }

    /**
     * Genera la respuesta según la intención
     */
    private function generarRespuesta($intencion, $mensajeOriginal, $msg)
    {
        switch ($intencion) {
            case 'saludo':
                return $this->respuestaSaludo();

            case 'ayuda':
                return $this->respuestaAyuda();

            case 'consultar_patrimonio':
                return $this->respuestaPatrimonio();

            case 'consultar_deudas':
                return $this->respuestaDeudas();

            case 'consultar_balance':
                return $this->respuestaBalance();

            case 'consultar_ahorro':
                return $this->respuestaAhorro();

            case 'consultar_cuentas':
                return $this->respuestaCuentas();

            case 'consultar_me_deben':
                return $this->respuestaMeDeben();

            case 'consultar_gasto_categoria':
                return $this->respuestaGastoCategoria($msg);

            case 'proyeccion_ahorro':
                return $this->respuestaProyeccionAhorro($msg);

            case 'proyeccion_deuda':
                return $this->respuestaProyeccionDeuda();

            case 'proyeccion_compra':
                return $this->respuestaProyeccionCompra($msg);

            case 'proyeccion_futura':
                return $this->respuestaProyeccionFutura($msg);

            case 'recomendacion_inversion':
                return $this->respuestaRecomendacionInversion();

            case 'recomendacion_prestamo':
                return $this->respuestaRecomendacionPrestamo();

            case 'recomendacion_ahorro':
                return $this->respuestaRecomendacionAhorro();

            case 'recomendacion_deuda':
                return $this->respuestaRecomendacionDeuda();

            case 'educacion_cdt':
                return $this->respuestaEducacionCDT();

            case 'educacion_usura':
                return $this->respuestaEducacionUsura();

            case 'educacion_fondo':
                return $this->respuestaEducacionFondo();

            case 'crear_gasto':
                return $this->respuestaCrearGasto($msg);

            case 'crear_ingreso':
                return $this->respuestaCrearIngreso($msg);

            case 'crear_meta':
                return $this->respuestaCrearMeta($msg);

            case 'crear_deuda':
                return $this->respuestaCrearDeuda($msg);

            default:
                return $this->respuestaDesconocida();
        }
    }

    // ==========================================
    // RESPUESTAS ESPECÍFICAS
    // ==========================================

    private function respuestaSaludo()
    {
        $nombre = $_SESSION['nombre'] ?? 'amigo';
        return "¡Hola $nombre! 👋 Soy FinanIA, tu asistente financiero personal. Puedo ayudarte a:\n\n" .
               "💰 Consultar tu patrimonio, deudas y ahorros\n" .
               "📊 Analizar tus finanzas del mes\n" .
               "🔮 Hacer proyecciones (\"¿podré comprar un carro?\")\n" .
               "💡 Darte recomendaciones de inversión\n" .
               "⚡ Registrar transacciones, metas y deudas\n\n" .
               "¿Qué necesitas saber hoy?";
    }

    private function respuestaAyuda()
    {
        return "📚 **Puedo ayudarte con:**\n\n" .
               "**Consultas:**\n" .
               "• \"¿Cuánto tengo en total?\"\n" .
               "• \"¿Cuánto debo?\"\n" .
               "• \"¿Cómo van mis finanzas este mes?\"\n" .
               "• \"¿Cuánto gasté en comida?\"\n\n" .
               "**Predicciones:**\n" .
               "• \"¿Podré ahorrar 5 millones en 6 meses?\"\n" .
               "• \"¿Cuándo termino de pagar mis deudas?\"\n" .
               "• \"¿Puedo comprar un carro?\"\n\n" .
               "**Recomendaciones:**\n" .
               "• \"¿Debería invertir?\"\n" .
               "• \"¿Me conviene pedir un préstamo?\"\n\n" .
               "**Acciones:**\n" .
               "• \"Registra un gasto de 50.000 en comida\"\n" .
               "• \"Crea una meta para un viaje\"\n\n" .
               "Escribe tu pregunta natural, sin comandos raros. 😉";
    }

    private function respuestaPatrimonio()
    {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(saldo_inicial),0) AS inicial FROM cuenta WHERE id_usuario = ? AND activa = TRUE"
        );
        $stmt->execute([$this->idUsuario]);
        $saldoInicialTotal = $stmt->fetch()['inicial'];

        // Sumar ingresos
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(monto),0) AS t FROM transaccion WHERE id_usuario = ? AND tipo = 'ingreso'"
        );
        $stmt->execute([$this->idUsuario]);
        $ingresos = $stmt->fetch()['t'];

        // Restar gastos
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(monto),0) AS t FROM transaccion WHERE id_usuario = ? AND tipo = 'gasto'"
        );
        $stmt->execute([$this->idUsuario]);
        $gastos = $stmt->fetch()['t'];

        // Restar deudas pendientes
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(monto_total - monto_pagado),0) AS t 
             FROM deuda WHERE id_usuario = ? AND estado != 'pagada'"
        );
        $stmt->execute([$this->idUsuario]);
        $deudas = $stmt->fetch()['t'];

        $activos = $saldoInicialTotal + $ingresos - $gastos;
        $neto = $activos - $deudas;

        $respuesta = "💰 **Tu situación financiera:**\n\n";
        $respuesta .= "Activos: " . formatoCOP($activos) . "\n";
        $respuesta .= "Pasivos (deudas): " . formatoCOP($deudas) . "\n";
        $respuesta .= "**Patrimonio neto: " . formatoCOP($neto) . "**\n\n";

        if ($neto > 0) {
            $respuesta .= "✅ Estás en números positivos. Vas bien.";
        } else {
            $respuesta .= "🚨 Tu patrimonio es negativo. Considera reducir gastos o pagar deudas cuanto antes.";
        }

        return $respuesta;
    }

    private function respuestaDeudas()
    {
        $stmt = $this->pdo->prepare(
            "SELECT acreedor, monto_total, monto_pagado, tasa_interes, fecha_vencimiento
             FROM deuda WHERE id_usuario = ? AND estado != 'pagada'
             ORDER BY tasa_interes DESC"
        );
        $stmt->execute([$this->idUsuario]);
        $deudas = $stmt->fetchAll();

        if (empty($deudas)) {
            return "🎉 ¡No tienes deudas activas! Excelente posición financiera.";
        }

        $total = 0;
        $respuesta = "💳 **Tus deudas activas:**\n\n";
        foreach ($deudas as $d) {
            $saldo = $d['monto_total'] - $d['monto_pagado'];
            $total += $saldo;
            $alerta = $d['tasa_interes'] > 28.79 ? " 🚨 ¡Tasa de usura!" : "";
            $respuesta .= "• **" . $d['acreedor'] . "**: " . formatoCOP($saldo) . 
                          " (" . number_format($d['tasa_interes'], 2) . "% EA)" . $alerta . "\n";
        }
        $respuesta .= "\n**Total: " . formatoCOP($total) . "**\n\n";

        if ($total > 0) {
            $respuesta .= "💡 Pregúntame \"¿debería pedir un préstamo?\" o \"¿cuál es mi mejor estrategia de pago?\"";
        }

        return $respuesta;
    }

    private function respuestaBalance()
    {
        $mes = date('Y-m');
        $stmt = $this->pdo->prepare(
            "SELECT 
                COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END),0) AS ingresos,
                COALESCE(SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END),0) AS gastos
             FROM transaccion 
             WHERE id_usuario = ? AND DATE_FORMAT(fecha, '%Y-%m') = ?"
        );
        $stmt->execute([$this->idUsuario, $mes]);
        $r = $stmt->fetch();

        $balance = $r['ingresos'] - $r['gastos'];
        $tasaAhorro = $r['ingresos'] > 0 ? ($balance / $r['ingresos']) * 100 : 0;

        $respuesta = "📊 **Resumen del mes:**\n\n";
        $respuesta .= "Ingresos: " . formatoCOP($r['ingresos']) . "\n";
        $respuesta .= "Gastos: " . formatoCOP($r['gastos']) . "\n";
        $respuesta .= "**Balance: " . formatoCOP($balance) . "**\n";
        $respuesta .= "Tasa de ahorro: " . number_format($tasaAhorro, 1) . "%\n\n";

        if ($tasaAhorro >= 20) {
            $respuesta .= "🌟 ¡Excelente! Ahorras más del 20% de tus ingresos.";
        } elseif ($tasaAhorro >= 10) {
            $respuesta .= "👍 Vas bien. Podrías intentar llegar al 20%.";
        } elseif ($balance >= 0) {
            $respuesta .= "⚠️ Ahorras menos del 10%. Considera revisar tus gastos variables.";
        } else {
            $respuesta .= "🚨 Estás gastando más de lo que ganas este mes.";
        }

        return $respuesta;
    }

    private function respuestaAhorro()
    {
        $stmt = $this->pdo->prepare(
            "SELECT nombre, monto_objetivo, monto_actual, fecha_limite, estado
             FROM meta WHERE id_usuario = ? AND estado = 'activa'
             ORDER BY fecha_limite ASC"
        );
        $stmt->execute([$this->idUsuario]);
        $metas = $stmt->fetchAll();

        if (empty($metas)) {
            return "🎯 No tienes metas activas. ¿Quieres crear una? Dime \"crea una meta para...\"";
        }

        $totalAhorrado = 0;
        $totalObjetivo = 0;

        $respuesta = "🎯 **Tus metas activas:**\n\n";
        foreach ($metas as $m) {
            $progreso = $m['monto_objetivo'] > 0 
                ? ($m['monto_actual'] / $m['monto_objetivo']) * 100 
                : 0;
            $totalAhorrado += $m['monto_actual'];
            $totalObjetivo += $m['monto_objetivo'];

            $respuesta .= "• **" . $m['nombre'] . "**: " . formatoCOP($m['monto_actual']) . 
                          " de " . formatoCOP($m['monto_objetivo']) . 
                          " (" . number_format($progreso, 1) . "%)\n";
            $respuesta .= "  Vence: " . formatoFecha($m['fecha_limite']) . "\n";
        }

        $respuesta .= "\n**Total ahorrado: " . formatoCOP($totalAhorrado) . "**\n";
        $respuesta .= "**Meta total: " . formatoCOP($totalObjetivo) . "**";

        return $respuesta;
    }

    private function respuestaCuentas()
    {
        $stmt = $this->pdo->prepare(
            "SELECT c.nombre, c.tipo, c.saldo_inicial,
                c.saldo_inicial 
                  + COALESCE((SELECT SUM(CASE WHEN tipo='ingreso' THEN monto ELSE -monto END) 
                              FROM transaccion WHERE id_cuenta = c.id_cuenta), 0)
                  - COALESCE((SELECT SUM(monto) FROM aporte_meta WHERE id_cuenta = c.id_cuenta), 0)
                  - COALESCE((SELECT SUM(monto) FROM pago_deuda WHERE id_cuenta = c.id_cuenta), 0)
                AS saldo_actual
             FROM cuenta c WHERE c.id_usuario = ? AND c.activa = TRUE
             ORDER BY saldo_actual DESC"
        );
        $stmt->execute([$this->idUsuario]);
        $cuentas = $stmt->fetchAll();

        if (empty($cuentas)) {
            return "No tienes cuentas registradas. Pregúntame cómo crear una.";
        }

        $respuesta = "🏦 **Tus cuentas:**\n\n";
        $total = 0;
        foreach ($cuentas as $c) {
            $respuesta .= "• **" . $c['nombre'] . "** (" . $c['tipo'] . "): " . 
                          formatoCOP($c['saldo_actual']) . "\n";
            $total += $c['saldo_actual'];
        }
        $respuesta .= "\n**Total: " . formatoCOP($total) . "**";

        return $respuesta;
    }

    private function respuestaMeDeben()
    {
        $stmt = $this->pdo->prepare(
            "SELECT c.nombre, COALESCE(SUM(pg.monto_corresponde), 0) AS total
             FROM contacto c
             LEFT JOIN participante_gasto pg ON c.id_contacto = pg.id_contacto
             LEFT JOIN gasto_compartido gc ON pg.id_gasto = gc.id_gasto
             WHERE c.id_usuario = ?
               AND gc.id_pagador = c.id_contacto
               AND pg.pagado = FALSE
             GROUP BY c.id_contacto, c.nombre
             HAVING total > 0
             ORDER BY total DESC"
        );
        $stmt->execute([$this->idUsuario]);
        $saldos = $stmt->fetchAll();

        if (empty($saldos)) {
            return "✅ No te deben dinero actualmente en gastos compartidos.";
        }

        $respuesta = "💚 **Te deben:**\n\n";
        $total = 0;
        foreach ($saldos as $s) {
            $respuesta .= "• **" . $s['nombre'] . "**: " . formatoCOP($s['total']) . "\n";
            $total += $s['total'];
        }
        $respuesta .= "\n**Total a favor: " . formatoCOP($total) . "**";

        return $respuesta;
    }

    private function respuestaGastoCategoria($msg)
    {
        // Buscar categoría mencionada
        $stmt = $this->pdo->prepare("SELECT id_categoria, nombre FROM categoria WHERE id_usuario = ? AND tipo = 'gasto'");
        $stmt->execute([$this->idUsuario]);
        $categorias = $stmt->fetchAll();

        $categoriaEncontrada = null;
        foreach ($categorias as $cat) {
            if (mb_strpos($msg, mb_strtolower($cat['nombre'])) !== false) {
                $categoriaEncontrada = $cat;
                break;
            }
        }

        if (!$categoriaEncontrada) {
            return "No identifiqué la categoría. Dime por ejemplo: \"¿cuánto gasté en comida?\"";
        }

        $stmt = $this->pdo->prepare(
            "SELECT 
                COALESCE(SUM(CASE WHEN DATE_FORMAT(fecha, '%Y-%m') = ? THEN monto ELSE 0 END), 0) AS mes_actual,
                COALESCE(SUM(monto), 0) AS total
             FROM transaccion 
             WHERE id_usuario = ? AND id_categoria = ? AND tipo = 'gasto'"
        );
        $stmt->execute([date('Y-m'), $this->idUsuario, $categoriaEncontrada['id_categoria']]);
        $r = $stmt->fetch();

        $respuesta = "💸 **Gastos en " . $categoriaEncontrada['nombre'] . ":**\n\n";
        $respuesta .= "Este mes: " . formatoCOP($r['mes_actual']) . "\n";
        $respuesta .= "Total histórico: " . formatoCOP($r['total']) . "\n";

        return $respuesta;
    }

    private function respuestaProyeccionAhorro($msg)
    {
        // Extraer monto del mensaje
        $montoObjetivo = $this->extraerMonto($msg);
        $meses = $this->extraerMeses($msg);

        if (!$montoObjetivo) {
            return "Dime cuánto quieres ahorrar. Ej: \"¿podré ahorrar 5 millones en 6 meses?\"";
        }

        if (!$meses) $meses = 12;

        // Calcular capacidad de ahorro promedio (últimos 6 meses)
        $stmt = $this->pdo->prepare(
            "SELECT 
                DATE_FORMAT(fecha, '%Y-%m') AS mes,
                SUM(CASE WHEN tipo='ingreso' THEN monto ELSE -monto END) AS balance
             FROM transaccion 
             WHERE id_usuario = ? AND fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY mes
             HAVING balance > 0"
        );
        $stmt->execute([$this->idUsuario]);
        $mesesPositivos = $stmt->fetchAll();

        if (empty($mesesPositivos)) {
            return "No tengo suficientes datos positivos para calcular tu capacidad de ahorro. Registra más transacciones.";
        }

        $ahorroPromedio = array_sum(array_column($mesesPositivos, 'balance')) / count($mesesPositivos);
        $ahorroPosible = $ahorroPromedio * $meses;

        $respuesta = "🔮 **Proyección de ahorro:**\n\n";
        $respuesta .= "Objetivo: " . formatoCOP($montoObjetivo) . " en $meses meses\n";
        $respuesta .= "Tu ahorro mensual promedio: " . formatoCOP($ahorroPromedio) . "\n\n";

        if ($ahorroPosible >= $montoObjetivo) {
            $tiempoNecesario = ceil($montoObjetivo / $ahorroPromedio);
            $respuesta .= "✅ **¡Sí puedes!** Si mantienes tu ritmo actual, alcanzarías la meta en ";
            $respuesta .= "$tiempoNecesario meses (" . round($tiempoNecesario / 12, 1) . " años).\n\n";
            $respuesta .= "💡 Consejo: automatiza el ahorro al inicio de cada mes para no gastarlo.";
        } else {
            $faltante = $montoObjetivo - $ahorroPosible;
            $necesarioMes = $montoObjetivo / $meses;
            $respuesta .= "⚠️ **No alcanzarás con el ritmo actual.**\n\n";
            $respuesta .= "Necesitarías ahorrar " . formatoCOP($necesarioMes) . " al mes ";
            $respuesta .= "($ahorroPromedio - " . formatoCOP($necesarioMes - $ahorroPromedio) . " más que ahora).\n\n";
            $respuesta .= "💡 Considera: reducir gastos variables, aumentar ingresos o extender el plazo.";
        }

        return $respuesta;
    }

    private function respuestaProyeccionDeuda()
    {
        $stmt = $this->pdo->prepare(
            "SELECT SUM(monto_total - monto_pagado) AS saldo, SUM(pago_minimo) AS minimo
             FROM deuda WHERE id_usuario = ? AND estado != 'pagada'"
        );
        $stmt->execute([$this->idUsuario]);
        $r = $stmt->fetch();

        if (!$r['saldo'] || $r['saldo'] <= 0) {
            return "🎉 No tienes deudas pendientes. ¡No hay nada que proyectar!";
        }

        $mesesAlMinimo = ceil($r['saldo'] / $r['minimo']);
        $mesesConExtra = ceil($r['saldo'] / ($r['minimo'] + 200000));

        $respuesta = "💳 **Proyección de pago de deudas:**\n\n";
        $respuesta .= "Deuda total: " . formatoCOP($r['saldo']) . "\n";
        $respuesta .= "Pago mínimo total: " . formatoCOP($r['minimo']) . "/mes\n\n";
        $respuesta .= "📅 **Solo pagando mínimos:** $mesesAlMinimo meses (" . round($mesesAlMinimo / 12, 1) . " años)\n";
        $respuesta .= "📅 **Con $200.000 extra al mes:** $mesesConExtra meses (" . round($mesesConExtra / 12, 1) . " años)\n\n";
        $respuesta .= "💡 Pregúntame por el simulador de deudas para ver cuánto ahorras con la estrategia avalancha o bola de nieve.";

        return $respuesta;
    }

    private function respuestaProyeccionCompra($msg)
    {
        $monto = $this->extraerMonto($msg);
        if (!$monto) {
            return "Dime cuánto cuesta lo que quieres comprar. Ej: \"¿puedo comprar un carro de 50 millones?\"";
        }

        $stmt = $this->pdo->prepare(
            "SELECT 
                COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE -monto END),0) AS balance_historico
             FROM transaccion WHERE id_usuario = ?"
        );
        $stmt->execute([$this->idUsuario]);
        $balance = $stmt->fetch()['balance_historico'];

        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(monto_total - monto_pagado),0) AS deudas
             FROM deuda WHERE id_usuario = ? AND estado != 'pagada'"
        );
        $stmt->execute([$this->idUsuario]);
        $deudas = $stmt->fetch()['deudas'];

        $stmt = $this->pdo->prepare(
            "SELECT 
                AVG(balance_mensual) AS promedio
             FROM (
                SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
                    SUM(CASE WHEN tipo='ingreso' THEN monto ELSE -monto END) AS balance_mensual
                FROM transaccion 
                WHERE id_usuario = ? AND fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY mes
             ) AS sub"
        );
        $stmt->execute([$this->idUsuario]);
        $promedioMensual = $stmt->fetch()['promedio'] ?? 0;

        $patrimonioActual = $balance - $deudas;

        $respuesta = "🛒 **Análisis de compra: " . formatoCOP($monto) . "**\n\n";
        $respuesta .= "Patrimonio actual: " . formatoCOP($patrimonioActual) . "\n";
        $respuesta .= "Ahorro mensual promedio: " . formatoCOP($promedioMensual) . "\n\n";

        if ($patrimonioActual >= $monto && $deudas == 0) {
            $respuesta .= "✅ **Sí puedes comprarlo.** Tienes el dinero disponible y no tienes deudas.\n";
            $respuesta .= "💡 Asegúrate de mantener tu fondo de emergencia intacto.";
        } elseif ($promedioMensual > 0) {
            $mesesNecesarios = ceil(($monto - max(0, $patrimonioActual)) / $promedioMensual);
            $respuesta .= "📅 **Necesitarías ahorrar " . $mesesNecesarios . " meses** más (aprox " . 
                          round($mesesNecesarios / 12, 1) . " años).\n\n";
            
            if ($deudas > 0) {
                $respuesta .= "⚠️ **Pero primero:** tienes " . formatoCOP($deudas) . " en deudas. ";
                $respuesta .= "Considera pagarlas antes de hacer esta compra.";
            }
        } else {
            $respuesta .= "⚠️ No tengo suficientes datos para proyectar. Registra más transacciones.";
        }

        return $respuesta;
    }

    private function respuestaProyeccionFutura($msg)
    {
        $meses = $this->extraerMeses($msg) ?: 12;

        $stmt = $this->pdo->prepare(
            "SELECT 
                AVG(balance_mensual) AS promedio
             FROM (
                SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
                    SUM(CASE WHEN tipo='ingreso' THEN monto ELSE -monto END) AS balance_mensual
                FROM transaccion 
                WHERE id_usuario = ? AND fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY mes
             ) AS sub"
        );
        $stmt->execute([$this->idUsuario]);
        $promedioMensual = $stmt->fetch()['promedio'] ?? 0;

        $proyeccion = $promedioMensual * $meses;

        $respuesta = "🔮 **Proyección a $meses meses:**\n\n";
        if ($promedioMensual > 0) {
            $respuesta .= "Si mantienes tu ritmo actual de ahorro (" . formatoCOP($promedioMensual) . "/mes), ";
            $respuesta .= "en $meses meses tendrías aproximadamente " . formatoCOP($proyeccion) . " más.\n\n";
            $respuesta .= "💡 Estos cálculos asumen que tus ingresos y gastos se mantienen iguales.";
        } else {
            $respuesta .= "No tengo suficientes datos positivos para hacer una proyección confiable.";
        }

        return $respuesta;
    }

    private function respuestaRecomendacionInversion()
    {
        // Verificar jerarquía financiera
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(monto_total - monto_pagado),0) AS deudas
             FROM deuda WHERE id_usuario = ? AND estado != 'pagada'"
        );
        $stmt->execute([$this->idUsuario]);
        $deudas = $stmt->fetch()['deudas'];

        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(monto_actual),0) AS ahorrado
             FROM meta WHERE id_usuario = ? AND estado = 'activa'"
        );
        $stmt->execute([$this->idUsuario]);
        $ahorrado = $stmt->fetch()['ahorrado'];

        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(AVG(gasto_mensual),0) AS promedio FROM (
                SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, SUM(monto) AS gasto_mensual
                FROM transaccion WHERE id_usuario = ? AND tipo='gasto'
                GROUP BY mes
            ) AS sub"
        );
        $stmt->execute([$this->idUsuario]);
        $gastoPromedio = $stmt->fetch()['promedio'];

        $fondoRecomendado = $gastoPromedio * 3;
        $fondoActual = $ahorrado; // Aproximación

        $respuesta = "💡 **Análisis de inversión:**\n\n";

        // Jerarquía de prioridades
        if ($fondoActual < $fondoRecomendado) {
            $faltante = $fondoRecomendado - $fondoActual;
            $respuesta .= "🚨 **NO inviertas todavía.**\n\n";
            $respuesta .= "**Prioridad 1:** completa tu fondo de emergencia.\n";
            $respuesta .= "Recomendado: " . formatoCOP($fondoRecomendado) . " (3 meses de gastos)\n";
            $respuesta .= "Tienes: " . formatoCOP($fondoActual) . "\n";
            $respuesta .= "Faltan: " . formatoCOP($faltante) . "\n\n";
        }

        if ($deudas > 0) {
            $respuesta .= "🚨 **Tienes deudas por " . formatoCOP($deudas) . ".**\n";
            $respuesta .= "Pagar una deuda al 25% EA equivale a invertir con rendimiento del 25%. ";
            $respuesta .= "Ninguna inversión te da eso con bajo riesgo.\n\n";
            $respuesta .= "**Prioridad 2:** paga tus deudas antes de invertir.";
        } elseif ($fondoActual >= $fondoRecomendado) {
            $respuesta .= "✅ **Buen momento para invertir.**\n\n";
            $respuesta .= "Tienes fondo de emergencia completo y sin deudas.\n";
            $respuesta .= "Te recomiendo el **Simulador de Inversiones** para comparar CDT, ahorros y fondos.";
        }

        return $respuesta;
    }

    private function respuestaRecomendacionPrestamo()
    {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(monto_total - monto_pagado),0) AS deudas
             FROM deuda WHERE id_usuario = ? AND estado != 'pagada'"
        );
        $stmt->execute([$this->idUsuario]);
        $deudas = $stmt->fetch()['deudas'];

        $stmt = $this->pdo->prepare(
            "SELECT 
                AVG(balance_mensual) AS promedio
             FROM (
                SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
                    SUM(CASE WHEN tipo='ingreso' THEN monto ELSE -monto END) AS balance_mensual
                FROM transaccion 
                WHERE id_usuario = ? AND fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY mes
             ) AS sub"
        );
        $stmt->execute([$this->idUsuario]);
        $balancePromedio = $stmt->fetch()['promedio'] ?? 0;

        $respuesta = "🏦 **Análisis de préstamo:**\n\n";

        if ($deudas > 0) {
            $respuesta .= "⚠️ **Ya tienes " . formatoCOP($deudas) . " en deudas.**\n\n";
            $respuesta .= "Antes de pedir un nuevo préstamo, considera:\n";
            $respuesta .= "1. ¿Puedes pagar el actual primero?\n";
            $respuesta .= "2. ¿La nueva deuda tiene mejor tasa?\n";
            $respuesta .= "3. ¿Realmente lo necesitas o es un gasto impulsivo?\n\n";
            $respuesta .= "💡 Prioriza pagar antes de endeudarte más.";
        } elseif ($balancePromedio <= 0) {
            $respuesta .= "🚨 **No te recomiendo pedir un préstamo.**\n\n";
            $respuesta .= "Tu balance mensual es negativo o cero. Un nuevo préstamo agravaría la situación.";
        } else {
            $respuesta .= "✅ **Estás en posición de considerar un préstamo.**\n\n";
            $respuesta .= "Balance mensual positivo: " . formatoCOP($balancePromedio) . "\n";
            $respuesta .= "Sin deudas activas.\n\n";
            $respuesta .= "💡 **Recomendaciones:**\n";
            $respuesta .= "• La cuota mensual no debería superar el 30% de tu ingreso\n";
            $respuesta .= "• Compara tasas antes de firmar\n";
            $respuesta .= "• Verifica que la tasa no supere la usura (28.79% EA)";
        }

        return $respuesta;
    }

    private function respuestaRecomendacionAhorro()
    {
        $respuesta = "💡 **Consejos para ahorrar más:**\n\n";
        $respuesta .= "1. **Págate primero:** al recibir tu sueldo, aparta el ahorro antes de gastar.\n";
        $respuesta .= "2. **Regla 50/30/20:** 50% necesidades, 30% gustos, 20% ahorro.\n";
        $respuesta .= "3. **Revisa suscripciones:** Netflix, Spotify, etc. ¿Usas todas?\n";
        $respuesta .= "4. **Gastos hormiga:** el tinto diario de $3.000 son $90.000 al mes.\n";
        $respuesta .= "5. **Automatiza:** programa transferencias automáticas a tus metas.\n\n";
        $respuesta .= "Pregúntame \"¿cómo van mis finanzas este mes?\" para un análisis personalizado.";

        return $respuesta;
    }

    private function respuestaRecomendacionDeuda()
    {
        return "💳 **Estrategias para salir de deudas:**\n\n" .
               "**1. Avalancha** 🏔️\n" .
               "Paga primero la deuda con MAYOR tasa de interés.\n" .
               "✅ Matemáticamente óptimo.\n" .
               "❌ Menos motivador (puede tardar en ver resultados).\n\n" .
               "**2. Bola de nieve** ⛄\n" .
               "Paga primero la deuda más PEQUEÑA.\n" .
               "✅ Rápido logro psicológico.\n" .
               "❌ Pagas más intereses al final.\n\n" .
               "💡 Visita el **Simulador de Deudas** para comparar ambas y ver cuál te conviene.";
    }

    private function respuestaEducacionCDT()
    {
        return "📜 **¿Qué es un CDT?**\n\n" .
               "Un **Certificado de Depósito a Término** es un producto donde prestas tu dinero " .
               "a un banco por un plazo fijo (3, 6, 12 meses) a cambio de una tasa de interés.\n\n" .
               "**Características:**\n" .
               "• Tasas más altas que una cuenta de ahorros\n" .
               "• El dinero queda bloqueado hasta el vencimiento\n" .
               "• Tiene **retención en la fuente** (4% sobre el rendimiento)\n" .
               "• Está respaldado por el seguro de depósitos (hasta $50 millones)\n\n" .
               "💡 Usa el **Simulador de Inversiones** para ver cuánto ganarías con un CDT.";
    }

    private function respuestaEducacionUsura()
    {
        return "🚨 **Tasa de usura**\n\n" .
               "Es el **interés máximo legal** que un banco puede cobrarte en Colombia. " .
               "La fija mensualmente la Superintendencia Financiera.\n\n" .
               "**Actual (última actualización):** 28.79% EA\n\n" .
               "**¿Qué significa?**\n" .
               "• Si una deuda cobra MÁS del límite, es ilegal\n" .
               "• Puedes denunciar ante la Superintendencia Financiera\n" .
               "• No estás obligado a pagar el exceso\n\n" .
               "💡 Tu sistema tiene una **alerta automática** que te avisa si alguna deuda supera este límite.";
    }

    private function respuestaEducacionFondo()
    {
        return "🛡️ **Fondo de emergencia**\n\n" .
               "Es dinero guardado para imprevistos: despidos, emergencias médicas, " .
               "daños de carro, etc. Es la base de una buena salud financiera.\n\n" .
               "**¿Cuánto necesitas?**\n" .
               "• Mínimo: 3 meses de gastos fijos\n" .
               "• Recomendado: 6 meses\n" .
               "• Si eres independiente: 9-12 meses\n\n" .
               "**¿Dónde guardarlo?**\n" .
               "• Cuenta de ahorros de fácil acceso\n" .
               "• CDT a corto plazo (renovable)\n" .
               "• NO en inversiones volátiles\n\n" .
               "💡 El sistema puede calcular cuánto necesitas. Pregúntame \"¿cuánto debería tener de fondo de emergencia?\"";
    }

    private function respuestaCrearGasto($msg)
    {
        $monto = $this->extraerMonto($msg);
        
        if (!$monto) {
            return "Para registrar un gasto, dime el monto. Ej: \"Registra un gasto de 50.000 en comida\"";
        }

        return "📝 **Para registrar este gasto, necesito confirmar:**\n\n" .
               "• Monto: " . formatoCOP($monto) . "\n" .
               "• Categoría: (¿comida, transporte, entretenimiento?)\n" .
               "• Cuenta: (¿de dónde salió el dinero?)\n\n" .
               "💡 Por ahora, ve al módulo **Transacciones → + Nueva Transacción** para registrarlo con todos los datos.\n\n" .
               "(Esta funcionalidad de crear directamente desde el chat viene en la próxima versión)";
    }

    private function respuestaCrearIngreso($msg)
    {
        $monto = $this->extraerMonto($msg);
        
        if (!$monto) {
            return "Para registrar un ingreso, dime el monto. Ej: \"Recibí 3.000.000 de salario\"";
        }

        return "📝 **Para registrar este ingreso, necesito confirmar:**\n\n" .
               "• Monto: " . formatoCOP($monto) . "\n" .
               "• Categoría: (¿salario, freelance?)\n" .
               "• Cuenta: (¿a dónde llegó?)\n\n" .
               "💡 Ve al módulo **Transacciones → + Nueva Transacción** para registrarlo.";
    }

    private function respuestaCrearMeta($msg)
    {
        return "🎯 **Para crear una meta, necesito:**\n\n" .
               "• Nombre de la meta\n" .
               "• Monto objetivo\n" .
               "• Fecha límite\n\n" .
               "💡 Ve al módulo **Metas → + Nueva Meta** para crearla paso a paso.";
    }

    private function respuestaCrearDeuda($msg)
    {
        return "💳 **Para registrar una deuda, necesito:**\n\n" .
               "• Acreedor (Banco XYZ, tarjeta, etc.)\n" .
               "• Monto total\n" .
               "• Tasa de interés\n" .
               "• Fecha de vencimiento\n\n" .
               "💡 Ve al módulo **Deudas → + Nueva Deuda**.";
    }

    private function respuestaDesconocida()
    {
        return "🤔 No entendí bien tu pregunta. Prueba con:\n\n" .
               "• \"¿Cuánto tengo en total?\"\n" .
               "• \"¿Cuánto debo?\"\n" .
               "• \"¿Podré ahorrar 5 millones en 6 meses?\"\n" .
               "• \"¿Debería invertir?\"\n" .
               "• \"¿Qué puedes hacer?\"\n\n" .
               "También puedes escribir **\"ayuda\"** para ver todas las opciones.";
    }

    // ==========================================
    // UTILIDADES
    // ==========================================

    private function extraerMonto($msg)
    {
        // Buscar números en el mensaje
        $msg = str_replace(['$', '.', ','], ['', '', '.'], $msg);
        
        // Buscar patrones como "5 millones", "50000", "50 mil"
        if (preg_match('/(\d+(?:\.\d+)?)\s*(millones?|millón)/i', $msg, $m)) {
            return floatval($m[1]) * 1000000;
        }
        if (preg_match('/(\d+(?:\.\d+)?)\s*(mil)/i', $msg, $m)) {
            return floatval($m[1]) * 1000;
        }
        if (preg_match('/(\d{4,})/', $msg, $m)) {
            return floatval($m[1]);
        }
        return null;
    }

    private function extraerMeses($msg)
    {
        if (preg_match('/(\d+)\s*(meses?|mes)/i', $msg, $m)) {
            return intval($m[1]);
        }
        if (preg_match('/(\d+)\s*(años?|ano)/i', $msg, $m)) {
            return intval($m[1]) * 12;
        }
        return null;
    }

    /**
     * Guarda un mensaje en el historial
     */
    private function guardarMensaje($rol, $mensaje, $intencion = null)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO chat_conversacion (id_usuario, rol, mensaje, intencion) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$this->idUsuario, $rol, $mensaje, $intencion]);
    }

    /**
     * Obtiene el historial de conversación
     */
    public function obtenerHistorial($limite = 50)
    {
        $stmt = $this->pdo->prepare(
            "SELECT rol, mensaje, intencion, fecha 
             FROM chat_conversacion 
             WHERE id_usuario = ? 
             ORDER BY fecha DESC 
             LIMIT ?"
        );
        $stmt->bindValue(1, $this->idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(2, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }

    /**
     * Limpia el historial
     */
    public function limpiarHistorial()
    {
        $stmt = $this->pdo->prepare("DELETE FROM chat_conversacion WHERE id_usuario = ?");
        $stmt->execute([$this->idUsuario]);
    }
}