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
        $tasas = $this->obtenerTasas();

        $this->render('index', [
            'tasas' => $tasas,
            'resultado' => null,
            'meta_resultado' => null,
            'usuario' => usuarioActual()
        ]);
    }

    public function simular()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php');
        }

        requiereCsrf('index.php?error=csrf');

        $tasas = $this->obtenerTasas();
        $tipo = postStr('tipo', 'cdt');

        $resultado = null;
        $meta_resultado = null;

        if ($tipo === 'cdt') {
            $monto = postFloat('monto');
            $plazo = postInt('plazo');
            $aplicarGravamen = post('aplicar_gravamen') ? true : false;

            if ($monto > 0 && $plazo > 0) {
                $resultado = $this->model->calcularCDT(
                    $monto, $plazo, $tasas['cdt'], $tasas['retencion_cdt'], $tasas['inflacion'], $aplicarGravamen
                );
                $resultado['nombre'] = 'CDT a ' . $plazo . ' meses';
            }
        } elseif ($tipo === 'fic') {
            $montoInicial = postFloat('monto_inicial');
            $aporteMensual = postFloat('aporte_mensual');
            $plazo = postInt('plazo');

            if ($aporteMensual > 0 && $plazo > 0) {
                $resultado = $this->model->calcularFIC(
                    $montoInicial, $aporteMensual, $plazo, $tasas['fic'], $tasas['inflacion']
                );
                $resultado['nombre'] = 'FIC con aportes mensuales';
            }
        } elseif ($tipo === 'comparador') {
            $monto = postFloat('monto');
            $plazo = postInt('plazo');

            if ($monto > 0 && $plazo > 0) {
                $resultado = [
                    'tipo' => 'comparador',
                    'productos' => $this->model->compararProductos($monto, $plazo, $tasas),
                    'monto' => $monto,
                    'plazo' => $plazo
                ];
            }
        } elseif ($tipo === 'meta') {
            $montoMeta = postFloat('monto_meta');
            $plazo = postInt('plazo');
            $montoInicial = postFloat('monto_inicial');

            if ($montoMeta > 0 && $plazo > 0) {
                $meta_resultado = $this->model->calcularMeta(
                    $montoMeta, $plazo, $tasas['fic'], $montoInicial
                );
            }
        }

        $this->render('index', [
            'tasas' => $tasas,
            'resultado' => $resultado,
            'meta_resultado' => $meta_resultado,
            'tipo_activo' => $tipo,
            'usuario' => usuarioActual()
        ]);
    }

    private function obtenerTasas()
    {
        return [
            'cdt' => $this->modelConfig->obtener('tasa_cdt_promedio', 12),
            'ahorros' => $this->modelConfig->obtener('tasa_ahorros_promedio', 8.5),
            'fic' => $this->modelConfig->obtener('tasa_fic_promedio', 12),
            'inflacion' => $this->modelConfig->obtener('inflacion_anual', 5.2),
            'retencion_cdt' => $this->modelConfig->obtener('retencion_fuente_cdt', 4)
        ];
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/simulador_inversion/{$vista}.php";
    }
}