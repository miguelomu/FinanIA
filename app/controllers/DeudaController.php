<?php
/**
 * Controlador: DeudaController
 * 
 * Maneja la lógica de deudas y pagos.
 */

require_once __DIR__ . '/../models/Deuda.php';
require_once __DIR__ . '/../models/Pago.php';

class DeudaController
{
    private $pdo;
    private $model;
    private $modelPago;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Deuda($pdo);
        $this->modelPago = new Pago($pdo);
        $this->idUsuario = $idUsuario;
    }

    // ==========================================
    // DEUDAS
    // ==========================================

    public function index()
    {
        $estado = $_GET['estado'] ?? '';
        $deudas = $this->model->obtenerTodas($this->idUsuario, $estado);
        $totales = $this->model->calcularTotales($deudas);

        $this->render('index', [
            'deudas' => $deudas,
            'totales' => $totales,
            'estado' => $estado,
            'usuario' => usuarioActual()
        ]);
    }

    public function crear()
    {
        $this->render('crear', ['usuario' => usuarioActual()]);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $acreedor = trim($_POST['acreedor'] ?? '');
        $montoTotal = floatval($_POST['monto_total'] ?? 0);
        $montoPagado = floatval($_POST['monto_pagado'] ?? 0);
        $tasaInteres = floatval($_POST['tasa_interes'] ?? 0);
        $fechaInicio = $_POST['fecha_inicio'] ?? '';
        $fechaVencimiento = $_POST['fecha_vencimiento'] ?? '';
        $pagoMinimo = floatval($_POST['pago_minimo'] ?? 0);

        if (empty($acreedor) || empty($fechaInicio) || empty($fechaVencimiento)) {
            header('Location: index.php?accion=crear&error=campos_vacios');
            exit;
        }

        if ($montoTotal <= 0) {
            header('Location: index.php?accion=crear&error=monto_invalido');
            exit;
        }

        if (strtotime($fechaVencimiento) <= strtotime($fechaInicio)) {
            header('Location: index.php?accion=crear&error=fecha_invalida');
            exit;
        }

        $this->model->crear(
            $this->idUsuario, $acreedor, $montoTotal, $montoPagado,
            $tasaInteres, $fechaInicio, $fechaVencimiento, $pagoMinimo
        );

        header('Location: index.php?msg=creado');
        exit;
    }

    public function editar()
    {
        $id = intval($_GET['id'] ?? 0);
        $deuda = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$deuda) {
            header('Location: index.php');
            exit;
        }

        $this->render('editar', [
            'deuda' => $deuda,
            'usuario' => usuarioActual()
        ]);
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        $acreedor = trim($_POST['acreedor'] ?? '');
        $montoTotal = floatval($_POST['monto_total'] ?? 0);
        $montoPagado = floatval($_POST['monto_pagado'] ?? 0);
        $tasaInteres = floatval($_POST['tasa_interes'] ?? 0);
        $fechaInicio = $_POST['fecha_inicio'] ?? '';
        $fechaVencimiento = $_POST['fecha_vencimiento'] ?? '';
        $pagoMinimo = floatval($_POST['pago_minimo'] ?? 0);
        $estado = $_POST['estado'] ?? 'activa';

        if (empty($acreedor) || empty($fechaInicio) || empty($fechaVencimiento) || $montoTotal <= 0) {
            header("Location: index.php?accion=editar&id=$id&error=campos_vacios");
            exit;
        }

        if (!in_array($estado, ['activa', 'pagada', 'vencida'])) {
            $estado = 'activa';
        }

        $this->model->actualizar(
            $id, $this->idUsuario, $acreedor, $montoTotal, $montoPagado,
            $tasaInteres, $fechaInicio, $fechaVencimiento, $pagoMinimo, $estado
        );

        header('Location: index.php?msg=actualizado');
        exit;
    }

    public function eliminar()
    {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->model->eliminar($id, $this->idUsuario);
        }
        header('Location: index.php?msg=eliminado');
        exit;
    }

    // ==========================================
    // PAGOS
    // ==========================================

    public function ver()
    {
        $id = intval($_GET['id'] ?? 0);
        $deuda = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$deuda) {
            header('Location: index.php');
            exit;
        }

        $pagos = $this->modelPago->obtenerPorDeuda($id);

        $progreso = $deuda['monto_total'] > 0 
            ? min(100, ($deuda['monto_pagado'] / $deuda['monto_total']) * 100) 
            : 0;

        $this->render('ver', [
            'deuda' => $deuda,
            'pagos' => $pagos,
            'progreso' => $progreso,
            'usuario' => usuarioActual()
        ]);
    }

    public function pagar()
    {
        $id = intval($_GET['id'] ?? 0);
        $deuda = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$deuda) {
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $monto = floatval($_POST['monto'] ?? 0);
            $nota = trim($_POST['nota'] ?? '');

            if ($monto > 0) {
                $this->modelPago->crear($id, $this->idUsuario, $monto, $nota);
                $this->model->recalcular($id);

                header("Location: index.php?accion=ver&id=$id&msg=pagado");
                exit;
            }
        }

        $this->render('pagar', [
            'deuda' => $deuda,
            'usuario' => usuarioActual()
        ]);
    }

    public function editarPago()
    {
        $id = intval($_GET['id'] ?? 0);
        $pago = $this->modelPago->obtenerPorId($id, $this->idUsuario);

        if (!$pago) {
            header('Location: index.php');
            exit;
        }

        $this->render('editar_pago', [
            'pago' => $pago,
            'usuario' => usuarioActual()
        ]);
    }

    public function actualizarPago()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        $monto = floatval($_POST['monto'] ?? 0);
        $nota = trim($_POST['nota'] ?? '');

        if ($monto <= 0) {
            header("Location: index.php?accion=editarPago&id=$id&error=monto_invalido");
            exit;
        }

        $pago = $this->modelPago->obtenerPorId($id, $this->idUsuario);
        if (!$pago) {
            header('Location: index.php');
            exit;
        }

        $idDeuda = $pago['id_deuda'];

        $this->modelPago->actualizar($id, $monto, $nota);
        $this->model->recalcular($idDeuda);

        header("Location: index.php?accion=ver&id=$idDeuda&msg=editado");
        exit;
    }

    public function eliminarPago()
    {
        $id = intval($_GET['id'] ?? 0);
        $pago = $this->modelPago->obtenerPorId($id, $this->idUsuario);

        if (!$pago) {
            header('Location: index.php');
            exit;
        }

        $idDeuda = $pago['id_deuda'];

        $this->modelPago->eliminar($id);
        $this->model->recalcular($idDeuda);

        header("Location: index.php?accion=ver&id=$idDeuda&msg=eliminado");
        exit;
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/deudas/{$vista}.php";
    }
}