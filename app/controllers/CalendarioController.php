<?php
/**
 * Controlador: CalendarioController
 */

require_once __DIR__ . '/../models/Calendario.php';

class CalendarioController
{
    private $pdo;
    private $model;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Calendario($pdo, $idUsuario);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        // Mes y año actual (o el que viene por GET)
        $mes = getInt('mes', intval(date('n')));
        $anio = getInt('anio', intval(date('Y')));

        // Validar rangos
        if ($mes < 1 || $mes > 12) $mes = intval(date('n'));
        if ($anio < 2020 || $anio > 2100) $anio = intval(date('Y'));

        $eventos = $this->model->obtenerEventosMes($mes, $anio);
        $resumen = $this->model->resumenMes($mes, $anio);

        // Calcular mes anterior y siguiente
        $mesAnterior = $mes - 1;
        $anioAnterior = $anio;
        if ($mesAnterior < 1) {
            $mesAnterior = 12;
            $anioAnterior--;
        }

        $mesSiguiente = $mes + 1;
        $anioSiguiente = $anio;
        if ($mesSiguiente > 12) {
            $mesSiguiente = 1;
            $anioSiguiente++;
        }

        $this->render('index', [
            'mes' => $mes,
            'anio' => $anio,
            'eventos' => $eventos,
            'resumen' => $resumen,
            'mesAnterior' => $mesAnterior,
            'anioAnterior' => $anioAnterior,
            'mesSiguiente' => $mesSiguiente,
            'anioSiguiente' => $anioSiguiente,
            'usuario' => usuarioActual()
        ]);
    }

    /**
     * API: Detalle de un día específico
     */
    public function dia()
    {
        header('Content-Type: application/json');
        
        $fecha = get('fecha', '');
        
        if (empty($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            echo json_encode(['error' => 'Fecha inválida']);
            exit;
        }

        $detalle = $this->model->detalleDia($fecha);
        
        // Obtener recurrentes que aplican a este día
        $recurrentes = $this->model->recurrentesDelDia($fecha);
        
        // Formatear para JSON
        foreach ($detalle['transacciones'] as &$t) {
            $t['monto_formateado'] = formatoCOP($t['monto']);
        }
        foreach ($recurrentes as &$r) {
            $r['monto_formateado'] = formatoCOP($r['monto']);
        }

        echo json_encode([
            'success' => true,
            'fecha' => $fecha,
            'fecha_formateada' => formatoFecha($fecha),
            'detalle' => [
                'transacciones' => $detalle['transacciones'],
                'recurrentes' => $recurrentes,
                'ingresos' => $detalle['ingresos'],
                'gastos' => $detalle['gastos'],
                'balance' => $detalle['balance']
            ],
            'ingresos_formateado' => formatoCOP($detalle['ingresos']),
            'gastos_formateado' => formatoCOP($detalle['gastos']),
            'balance_formateado' => formatoCOP($detalle['balance'])
        ]);
        exit;
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/calendario/{$vista}.php";
    }
}