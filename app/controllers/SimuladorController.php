<?php
/**
 * Controlador: SimuladorController
 * 
 * Simulador de deudas con estrategias avalancha y bola de nieve.
 */

require_once __DIR__ . '/../models/SimuladorDeuda.php';

class SimuladorController
{
    private $pdo;
    private $model;
    private $idUsuario;
    private $modelConfig;

    public function __construct($pdo, $idUsuario)
{
    $this->pdo = $pdo;
    $this->model = new SimuladorDeuda($pdo);
    require_once __DIR__ . '/../models/Configuracion.php';
    $this->modelConfig = new Configuracion($pdo);
    $this->idUsuario = $idUsuario;
}

    /**
     * Vista principal del simulador
     */
    public function index()
    {
        $deudas = $this->model->obtenerDeudasActivas($this->idUsuario);

        // Calcular totales actuales
        $totalDeuda = 0;
        $totalMinimo = 0;
        $tasaPromedio = 0;
        $pesoTotal = 0;

        foreach ($deudas as $d) {
            $saldo = floatval($d['monto_total']) - floatval($d['monto_pagado']);
            $totalDeuda += $saldo;
            $totalMinimo += floatval($d['pago_minimo']);
            $tasaPromedio += floatval($d['tasa_interes']) * $saldo;
            $pesoTotal += $saldo;
        }

        $tasaPromedio = $pesoTotal > 0 ? $tasaPromedio / $pesoTotal : 0;

        $this->render('index', [
            'deudas' => $deudas,
            'totalDeuda' => $totalDeuda,
            'totalMinimo' => $totalMinimo,
            'tasaPromedio' => $tasaPromedio,
            'tasaUsura' => $this->modelConfig->tasaUsura(),   // ← AGREGAR
            'resultado' => null,
            'pagoExtra' => 0,
            'usuario' => usuarioActual()
        ]);
    }

    /**
     * Ejecuta la simulación
     */
    public function simular()
    {
        $deudas = $this->model->obtenerDeudasActivas($this->idUsuario);

        $pagoExtra = postFloat('pago_extra');
        
        if ($pagoExtra <= 0 || empty($deudas)) {
            redirigir('index.php?error=datos_invalidos');
        }

        $resultado = $this->model->compararEstrategias($deudas, $pagoExtra);

        // Calcular totales actuales (para mostrar en el header)
        $totalDeuda = 0;
        $totalMinimo = 0;
        $tasaPromedio = 0;
        $pesoTotal = 0;

        foreach ($deudas as $d) {
            $saldo = floatval($d['monto_total']) - floatval($d['monto_pagado']);
            $totalDeuda += $saldo;
            $totalMinimo += floatval($d['pago_minimo']);
            $tasaPromedio += floatval($d['tasa_interes']) * $saldo;
            $pesoTotal += $saldo;
        }
        $tasaPromedio = $pesoTotal > 0 ? $tasaPromedio / $pesoTotal : 0;

        $this->render('index', [
            'deudas' => $deudas,
            'totalDeuda' => $totalDeuda,
            'totalMinimo' => $totalMinimo,
            'tasaPromedio' => $tasaPromedio,
            'tasaUsura' => $this->modelConfig->tasaUsura(),   // ← AGREGAR
            'resultado' => $resultado,
            'pagoExtra' => $pagoExtra,
            'usuario' => usuarioActual()
        ]);
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/simulador/{$vista}.php";
    }
}