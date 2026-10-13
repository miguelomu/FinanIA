<?php
/**
 * Controlador: ReporteController
 */

require_once __DIR__ . '/../models/Reporte.php';

class ReporteController
{
    private $pdo;
    private $model;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Reporte($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        // Filtro de fechas (por defecto: últimos 6 meses)
        $fechaDesde = $_GET['desde'] ?? date('Y-m-d', strtotime('-6 months'));
        $fechaHasta = $_GET['hasta'] ?? date('Y-m-d');

        $resumen = $this->model->resumenGeneral($this->idUsuario, $fechaDesde, $fechaHasta);
        $mensual = $this->model->mensual($this->idUsuario, $fechaDesde, $fechaHasta);
        $gastosPorCategoria = $this->model->gastosPorCategoria($this->idUsuario, $fechaDesde, $fechaHasta);
        $ingresosPorCategoria = $this->model->ingresosPorCategoria($this->idUsuario, $fechaDesde, $fechaHasta);
        $topGastos = $this->model->topGastos($this->idUsuario, $fechaDesde, $fechaHasta);

        $this->render('index', [
            'resumen' => $resumen,
            'mensual' => $mensual,
            'gastosPorCategoria' => $gastosPorCategoria,
            'ingresosPorCategoria' => $ingresosPorCategoria,
            'topGastos' => $topGastos,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'usuario' => usuarioActual()
        ]);
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/reportes/{$vista}.php";
    }
}