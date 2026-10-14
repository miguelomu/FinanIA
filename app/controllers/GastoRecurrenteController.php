<?php
/**
 * Controlador: GastoRecurrenteController
 */

require_once __DIR__ . '/../models/GastoRecurrente.php';

class GastoRecurrenteController
{
    private $pdo;
    private $model;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new GastoRecurrente($pdo, $idUsuario);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $gastos = $this->model->obtenerTodos();
        $impacto = $this->model->calcularImpacto();
        $suscripciones = $this->model->detectarSuscripciones();

        $this->render('index', [
            'gastos' => $gastos,
            'impacto' => $impacto,
            'suscripciones' => $suscripciones,
            'usuario' => usuarioActual()
        ]);
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/recurrentes/{$vista}.php";
    }
}