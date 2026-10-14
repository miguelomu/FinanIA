<?php
/**
 * Controlador: SimuladorInversionController
 */

require_once __DIR__ . '/../models/SimuladorInversion.php';
require_once __DIR__ . '/../models/Configuracion.php';

class SimuladorInversionController
{
    private $pdo;
    private $model;
    private $modelConfig;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new SimuladorInversion($pdo);
        $this->modelConfig = new Configuracion($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $tasas = [
            'cdt' => $this->modelConfig->obtener('tasa_cdt_promedio', 12),
            'ahorros' => $this->modelConfig->obtener('tasa_ahorros_promedio', 8.5),
            'fondo' => $this->modelConfig->obtener('tasa_fondo_promedio', 14),
            'inflacion' => $this->modelConfig->obtener('inflacion_anual', 5.2),
            'retencion_cdt' => $this->modelConfig->obtener('retencion_fuente_cdt', 4)
        ];

        $this->render('index', [
            'tasas' => $tasas,
            'resultados' => null,
            'recomendacion' => null,
            'monto' => 0,
            'plazo' => 0,
            'usuario' => usuarioActual()
        ]);
    }

    public function simular()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php');
        }

        requiereCsrf('index.php?error=csrf');

        $monto = postFloat('monto');
        $plazo = postInt('plazo');

        if ($monto <= 0 || $plazo <= 0) {
            redirigir('index.php?error=campos_vacios');
        }

        $tasas = [
            'cdt' => $this->modelConfig->obtener('tasa_cdt_promedio', 12),
            'ahorros' => $this->modelConfig->obtener('tasa_ahorros_promedio', 8.5),
            'fondo' => $this->modelConfig->obtener('tasa_fondo_promedio', 14),
            'inflacion' => $this->modelConfig->obtener('inflacion_anual', 5.2),
            'retencion_cdt' => $this->modelConfig->obtener('retencion_fuente_cdt', 4)
        ];

        $alternativas = [
            [
                'nombre' => 'Cuenta de ahorros',
                'tasa' => $tasas['ahorros'],
                'retencion' => 0,
                'aplica_gravamen' => true,
                'color' => '#3498db'
            ],
            [
                'nombre' => 'CDT a ' . $plazo . ' meses',
                'tasa' => $tasas['cdt'],
                'retencion' => $tasas['retencion_cdt'],
                'aplica_gravamen' => false,
                'color' => '#d4af37'
            ],
            [
                'nombre' => 'Fondo de inversión',
                'tasa' => $tasas['fondo'],
                'retencion' => 0,
                'aplica_gravamen' => false,
                'color' => '#9b59b6'
            ]
        ];

        $resultados = $this->model->comparar($monto, $plazo, $tasas['inflacion'], $alternativas);
        $recomendacion = $this->model->recomendar($resultados, $tasas['inflacion']);

        $this->render('index', [
            'tasas' => $tasas,
            'resultados' => $resultados,
            'recomendacion' => $recomendacion,
            'monto' => $monto,
            'plazo' => $plazo,
            'usuario' => usuarioActual()
        ]);
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/simulador_inversion/{$vista}.php";
    }
}