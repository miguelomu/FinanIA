<?php
class Inversion
{
    private $pdo;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->idUsuario = $idUsuario;
    }

    /**
     * Obtiene los productos tangibles con proyección exacta según presupuesto.
     */
    public function obtenerProductosTangibles($presupuesto = 0)
    {
        $sql = "SELECT * FROM producto_inversion WHERE activo = TRUE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $productos = $stmt->fetchAll();

        $resultado = [];

        foreach ($productos as $p) {
            $inversionMinima = $p['costo_unitario'] * $p['cantidad_minima'];
            
            // Si hay presupuesto y no alcanza para el mínimo, saltar
            if ($presupuesto > 0 && $presupuesto < $inversionMinima) {
                continue;
            }

            // Calcular cuántas unidades puede comprar con el presupuesto
            if ($presupuesto > 0) {
                // Máximo de unidades que puede comprar (respetando el mínimo)
                $unidadesPosibles = floor($presupuesto / $p['costo_unitario']);
                
                // Si no alcanza ni para el mínimo, no lo mostramos
                if ($unidadesPosibles < $p['cantidad_minima']) {
                    continue;
                }
                
                // Ajustar al mínimo si es necesario
                if ($unidadesPosibles < $p['cantidad_minima']) {
                    $unidadesPosibles = $p['cantidad_minima'];
                }
            } else {
                // Sin presupuesto, mostrar el lote mínimo
                $unidadesPosibles = $p['cantidad_minima'];
            }

            $inversionTotal = $unidadesPosibles * $p['costo_unitario'];
            $ingresoTotal = $unidadesPosibles * $p['precio_venta_sugerido'];
            $gananciaTotal = $ingresoTotal - $inversionTotal;
            
            $roi = $inversionTotal > 0 ? ($gananciaTotal / $inversionTotal) * 100 : 0;
            $margen = $ingresoTotal > 0 ? ($gananciaTotal / $ingresoTotal) * 100 : 0;

            $resultado[] = [
                'id' => $p['id_producto'],
                'nombre' => $p['nombre'],
                'categoria' => $p['categoria'],
                'proveedor' => $p['proveedor_sugerido'],
                'donde' => $p['donde_comprar'],
                'costo_unitario' => $p['costo_unitario'],
                'precio_venta' => $p['precio_venta_sugerido'],
                'cantidad_minima' => $p['cantidad_minima'],
                'unidades_compradas' => $unidadesPosibles,
                'inversion_total' => $inversionTotal,
                'ingreso_total' => $ingresoTotal,
                'ganancia_total' => $gananciaTotal,
                'roi' => round($roi, 1),
                'margen' => round($margen, 1),
                'notas' => $p['notas']
            ];
        }

        // Ordenar por ROI descendente
        usort($resultado, fn($a, $b) => $b['roi'] <=> $a['roi']);

        return $resultado;
    }

    /**
     * Obtiene TODAS las opciones de inversión intangible con sus detalles.
     * La primera opción del array será la recomendada para el perfil.
     */
    public function obtenerTodasLasIntangibles($perfil, $monto)
    {
        // Tasas de referencia (basadas en investigación de mercado Colombia 2026)
        // CDT a 12 meses: hasta 13.51% EA [citation:2][citation:15]
        $tasaCDT = 13.51; 
        $inflacion = 6.24; // Inflación anual [citation:8]

        // Calcular rendimiento real del CDT
        // Fórmula: (1 + tasa_nominal) / (1 + inflacion) - 1
        $tasaRealCDT = ((1 + ($tasaCDT / 100)) / (1 + ($inflacion / 100)) - 1) * 100;
        $rendimientoNetoCDT = $monto * ($tasaCDT / 100);
        $rendimientoRealCDT = $monto * ($tasaRealCDT / 100);

        $opciones = [
            'conservador' => [
                'id' => 'cdt',
                'titulo' => 'CDT (Certificado de Depósito a Término)',
                'descripcion' => 'La opción más segura. Congelas tu dinero por un plazo fijo y recibes una tasa fija. Protegido por Fogafín.',
                'riesgo' => 'Bajo',
                'liquidez' => 'Baja (hasta el vencimiento)',
                'donde' => 'Bancos (Banco Pichincha, Davibank) o plataformas digitales (Trii, MejorCDT).',
                'proyeccion' => [
                    'inversion' => $monto,
                    'tasa_nominal' => $tasaCDT,
                    'rendimiento_bruto' => $rendimientoNetoCDT,
                    'inflacion' => $inflacion,
                    'rendimiento_real' => $rendimientoRealCDT,
                    'nota' => 'Con una tasa del ' . number_format($tasaCDT, 2) . '% EA, tu ganancia real (descontando inflación) sería de aproximadamente ' . formatoCOP($rendimientoRealCDT) . '.'
                ]
            ],
            'cauteloso' => [
                'id' => 'fic',
                'titulo' => 'FIC (Fondo de Inversión Colectiva)',
                'descripcion' => 'Canastas diversificadas gestionadas por expertos. Riesgo moderado, buscan mejor rendimiento que un CDT.',
                'riesgo' => 'Medio-Bajo',
                'liquidez' => 'Media (puedes retirar con penalidad)',
                'donde' => 'Fiduciarias (Bancolombia, Davivienda) o plataformas como Trii.',
                'proyeccion' => [
                    'inversion' => $monto,
                    'nota' => 'El rendimiento depende del fondo específico. Un FIC de renta fija podría rentar entre 9% y 12% EA, pero no está garantizado.'
                ]
            ],
            'moderado' => [
                'id' => 'etf',
                'titulo' => 'ETFs (Fondos Cotizados en Bolsa)',
                'descripcion' => 'Compra una canasta de acciones (ej. S&P 500) como si fuera una sola acción. Diversificación instantánea y bajo costo.',
                'riesgo' => 'Medio',
                'liquidez' => 'Alta (se venden en la bolsa)',
                'donde' => 'Comisionistas de bolsa o apps como Trii (desde $30.000 COP). ETFs como IGV, XLP o SLV [citation:10].',
                'proyeccion' => [
                    'inversion' => $monto,
                    'nota' => 'Los ETFs de mercado amplio han rentado históricamente 7-10% anual. Con $' . number_format($monto, 0, ',', '.') . ' podrías empezar con un portafolio diversificado.'
                ]
            ],
            'valiente' => [
                'id' => 'acciones',
                'titulo' => 'Acciones Individuales (Bolsa de Valores de Colombia)',
                'descripcion' => 'Compra una parte de empresas específicas (Ecopetrol, Grupo Argos, Bancolombia). Mayor volatilidad y riesgo.',
                'riesgo' => 'Alto',
                'liquidez' => 'Alta',
                'donde' => 'Comisionistas de bolsa (Acciones y Valores, Casa de Bolsa).',
                'proyeccion' => [
                    'inversion' => $monto,
                    'nota' => 'La bolsa colombiana proyecta un retorno del 15-20% para 2026 sumando dividendos, pero con alta volatilidad [citation:5].'
                ]
            ],
            'arriesgado' => [
                'id' => 'cripto',
                'titulo' => 'Criptomonedas o Derivados',
                'descripcion' => 'Alta volatilidad. Solo para dinero que no necesites a corto plazo y con alta tolerancia al riesgo.',
                'riesgo' => 'Muy Alto',
                'liquidez' => 'Alta',
                'donde' => 'Exchanges regulados (Binance, Bitso).',
                'proyeccion' => [
                    'inversion' => $monto,
                    'nota' => 'No hay proyección de rentabilidad confiable. Puedes ganar o perder una gran parte de tu inversión en poco tiempo.'
                ]
            ]
        ];

        // Reordenar el array para que la opción recomendada (según el perfil) sea la primera
        $opcionRecomendada = $opciones[$perfil] ?? $opciones['moderado'];
        $otrasOpciones = $opciones;
        unset($otrasOpciones[$perfil]);

        return array_merge([$opcionRecomendada], $otrasOpciones);
    }
}
?>