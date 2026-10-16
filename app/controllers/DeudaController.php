<?php
/**
 * Controlador: DeudaController
 */

require_once __DIR__ . '/../models/Deuda.php';
require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Cuenta.php';
require_once __DIR__ . '/../models/Configuracion.php';

class DeudaController
{
    private $pdo;
    private $model;
    private $modelPago;
    private $modelCuenta;
    private $modelConfig;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Deuda($pdo);
        $this->modelPago = new Pago($pdo);
        $this->modelCuenta = new Cuenta($pdo);
        $this->modelConfig = new Configuracion($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $estado = get('estado', '');
        $deudas = $this->model->obtenerTodas($this->idUsuario, $estado);
        $totales = $this->model->calcularTotales($deudas);
        $tasaUsura = $this->modelConfig->tasaUsura();

        $this->render('index', [
            'deudas' => $deudas,
            'totales' => $totales,
            'estado' => $estado,
            'tasaUsura' => $tasaUsura,
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
            redirigir('index.php');
        }

        requiereCsrf('index.php?error=csrf');

        $acreedor = postStr('acreedor');
        $montoTotal = postFloat('monto_total');
        $montoPagado = postFloat('monto_pagado');
        $tasaInteres = postFloat('tasa_interes');
        $fechaInicio = postStr('fecha_inicio');
        $fechaVencimiento = postStr('fecha_vencimiento');
        $pagoMinimo = postFloat('pago_minimo');

        if (empty($acreedor) || empty($fechaInicio) || empty($fechaVencimiento)) {
            toastError('Campos vacíos', 'Todos los campos obligatorios deben llenarse');
            redirigir('index.php?accion=crear');
        }

        if ($montoTotal <= 0) {
            toastError('Monto inválido', 'El monto total debe ser mayor a cero');
            redirigir('index.php?accion=crear');
        }

        if (strtotime($fechaVencimiento) <= strtotime($fechaInicio)) {
            toastError('Fechas inválidas', 'La fecha de vencimiento debe ser posterior a la de inicio');
            redirigir('index.php?accion=crear');
        }

        $this->model->crear(
            $this->idUsuario, $acreedor, $montoTotal, $montoPagado,
            $tasaInteres, $fechaInicio, $fechaVencimiento, $pagoMinimo
        );

        toastSuccess('Deuda registrada', 'Tu deuda se registró correctamente');
        redirigir('index.php');
    }

    public function editar()
    {
        $id = getInt('id');
        $deuda = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$deuda) {
            redirigir('index.php');
        }

        $this->render('editar', [
            'deuda' => $deuda,
            'usuario' => usuarioActual()
        ]);
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php');
        }

        requiereCsrf('index.php?error=csrf');

        $id = postInt('id');
        $acreedor = postStr('acreedor');
        $montoTotal = postFloat('monto_total');
        $montoPagado = postFloat('monto_pagado');
        $tasaInteres = postFloat('tasa_interes');
        $fechaInicio = postStr('fecha_inicio');
        $fechaVencimiento = postStr('fecha_vencimiento');
        $pagoMinimo = postFloat('pago_minimo');
        $estado = postStr('estado', 'activa');

        if (empty($acreedor) || empty($fechaInicio) || empty($fechaVencimiento) || $montoTotal <= 0) {
            toastError('Campos vacíos', 'Todos los campos obligatorios deben llenarse');
            redirigir("index.php?accion=editar&id=$id");
        }

        if (!in_array($estado, ['activa', 'pagada', 'vencida'])) {
            $estado = 'activa';
        }

        $this->model->actualizar(
            $id, $this->idUsuario, $acreedor, $montoTotal, $montoPagado,
            $tasaInteres, $fechaInicio, $fechaVencimiento, $pagoMinimo, $estado
        );

        toastSuccess('Deuda actualizada', 'Los cambios se guardaron correctamente');
        redirigir('index.php');
    }

    public function eliminar()
    {
        $id = getInt('id');
        if ($id > 0) {
            $this->model->eliminar($id, $this->idUsuario);
        }

        toastSuccess('Deuda eliminada', 'La deuda se eliminó correctamente');
        redirigir('index.php');
    }

    // ==========================================
    // PAGOS
    // ==========================================

    public function ver()
    {
        $id = getInt('id');
        $deuda = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$deuda) {
            redirigir('index.php');
        }

        $pagos = $this->modelPago->obtenerPorDeuda($id);
        $progreso = calcularProgreso($deuda['monto_pagado'], $deuda['monto_total']);

        $this->render('ver', [
            'deuda' => $deuda,
            'pagos' => $pagos,
            'progreso' => $progreso,
            'usuario' => usuarioActual()
        ]);
    }

    public function pagar()
    {
        $id = getInt('id');
        $deuda = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$deuda) {
            redirigir('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requiereCsrf("index.php?accion=pagar&id=$id&error=csrf");

            $monto = postFloat('monto');
            $nota = postStr('nota');
            $idCuenta = postInt('id_cuenta');

            if ($monto > 0) {
                $this->modelPago->crear($id, $this->idUsuario, $idCuenta, $monto, $nota);
                $this->model->recalcular($id);

                toastSuccess('Pago registrado', 'El pago se sumó a tu deuda');
                redirigir("index.php?accion=ver&id=$id");
            } else {
                toastError('Monto inválido', 'El monto debe ser mayor a cero');
                redirigir("index.php?accion=pagar&id=$id");
            }
        }

        $cuentas = $this->modelCuenta->obtenerTodas($this->idUsuario, true);

        $this->render('pagar', [
            'deuda' => $deuda,
            'cuentas' => $cuentas,
            'usuario' => usuarioActual()
        ]);
    }

    public function editarPago()
    {
        $id = getInt('id');
        $pago = $this->modelPago->obtenerPorId($id, $this->idUsuario);

        if (!$pago) {
            redirigir('index.php');
        }

        $cuentas = $this->modelCuenta->obtenerTodas($this->idUsuario, true);

        $this->render('editar_pago', [
            'pago' => $pago,
            'cuentas' => $cuentas,
            'usuario' => usuarioActual()
        ]);
    }

    public function actualizarPago()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php');
        }

        requiereCsrf('index.php?error=csrf');

        $id = postInt('id');
        $monto = postFloat('monto');
        $nota = postStr('nota');
        $idCuenta = postInt('id_cuenta');

        if ($monto <= 0) {
            toastError('Monto inválido', 'El monto debe ser mayor a cero');
            redirigir("index.php?accion=editarPago&id=$id");
        }

        $pago = $this->modelPago->obtenerPorId($id, $this->idUsuario);
        if (!$pago) {
            redirigir('index.php');
        }

        $idDeuda = $pago['id_deuda'];

        $this->modelPago->actualizar($id, $idCuenta, $monto, $nota);
        $this->model->recalcular($idDeuda);

        toastSuccess('Pago actualizado', 'Los cambios se guardaron correctamente');
        redirigir("index.php?accion=ver&id=$idDeuda");
    }

    public function eliminarPago()
    {
        $id = getInt('id');
        $pago = $this->modelPago->obtenerPorId($id, $this->idUsuario);

        if (!$pago) {
            redirigir('index.php');
        }

        $idDeuda = $pago['id_deuda'];

        $this->modelPago->eliminar($id);
        $this->model->recalcular($idDeuda);

        toastSuccess('Pago eliminado', 'El pago se eliminó de tu deuda');
        redirigir("index.php?accion=ver&id=$idDeuda");
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/deudas/{$vista}.php";
    }
}