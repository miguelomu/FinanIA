<?php
/**
 * Controlador: GastoCompartidoController
 */

require_once __DIR__ . '/../models/Contacto.php';
require_once __DIR__ . '/../models/GastoCompartido.php';
require_once __DIR__ . '/../models/Cuenta.php';

class GastoCompartidoController
{
    private $pdo;
    private $model;
    private $modelContacto;
    private $modelCuenta;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new GastoCompartido($pdo);
        $this->modelContacto = new Contacto($pdo);
        $this->modelCuenta = new Cuenta($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $gastos = $this->model->obtenerTodos($this->idUsuario);
        $resumen = $this->modelContacto->resumenSaldos($this->idUsuario);
        $saldos = $this->modelContacto->obtenerSaldos($this->idUsuario);

        $this->render('index', [
            'gastos' => $gastos,
            'resumen' => $resumen,
            'saldos' => $saldos,
            'usuario' => usuarioActual()
        ]);
    }

    public function crear()
    {
        $contactos = $this->modelContacto->obtenerTodos($this->idUsuario, true);
        $cuentas = $this->modelCuenta->obtenerTodas($this->idUsuario, true);

        if (empty($contactos)) {
            redirigir('index.php?accion=contactos&error=sin_contactos');
        }

        $this->render('crear', [
            'contactos' => $contactos,
            'cuentas' => $cuentas,
            'usuario' => usuarioActual()
        ]);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php');
        }

        requiereCsrf('index.php?accion=crear&error=csrf');

        $concepto = postStr('concepto');
        $montoTotal = postFloat('monto_total');
        $fecha = postStr('fecha');
        $idPagador = postInt('id_pagador');
        $idCuenta = postInt('id_cuenta');
        $notas = postStr('notas');
        $participantesIds = $_POST['participantes'] ?? [];

        if (empty($concepto) || $montoTotal <= 0 || $idPagador <= 0 || empty($participantesIds)) {
            redirigir('index.php?accion=crear&error=campos_vacios');
        }

        // Calcular monto por participante (división equitativa)
        $cantidad = count($participantesIds);
        $montoPorPersona = round($montoTotal / $cantidad, 2);

        // Ajustar última persona para cuadrar centavos
        $participantes = [];
        $sumaAcumulada = 0;
        foreach ($participantesIds as $i => $idContacto) {
            if ($i === $cantidad - 1) {
                $monto = $montoTotal - $sumaAcumulada;
            } else {
                $monto = $montoPorPersona;
                $sumaAcumulada += $monto;
            }
            $participantes[] = [
                'id_contacto' => intval($idContacto),
                'monto' => $monto
            ];
        }

        $this->model->crear(
            $this->idUsuario, $idCuenta, $concepto, $montoTotal,
            $fecha, $idPagador, $notas, $participantes
        );

        redirigir('index.php?msg=creado');
    }

    public function ver()
    {
        $id = getInt('id');
        $gasto = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$gasto) {
            redirigir('index.php');
        }

        $participantes = $this->model->obtenerParticipantes($id);

        $this->render('ver', [
            'gasto' => $gasto,
            'participantes' => $participantes,
            'usuario' => usuarioActual()
        ]);
    }

    public function eliminar()
    {
        $id = getInt('id');
        if ($id > 0) {
            $this->model->eliminar($id, $this->idUsuario);
        }
        redirigir('index.php?msg=eliminado');
    }

    public function marcarPagado()
    {
        $id = getInt('id');
        if ($id > 0) {
            $this->model->marcarPagado($id, $this->idUsuario);
        }
        $gastoId = getInt('gasto_id');
        redirigir("index.php?accion=ver&id=$gastoId&msg=pagado");
    }

    // ==========================================
    // CONTACTOS
    // ==========================================

    public function contactos()
    {
        $contactos = $this->modelContacto->obtenerTodos($this->idUsuario);
        $saldos = $this->modelContacto->obtenerSaldos($this->idUsuario);

        $this->render('contactos', [
            'contactos' => $contactos,
            'saldos' => $saldos,
            'usuario' => usuarioActual()
        ]);
    }

    public function crearContacto()
    {
        $this->render('contacto_crear', ['usuario' => usuarioActual()]);
    }

    public function guardarContacto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php?accion=contactos');
        }

        requiereCsrf('index.php?accion=contactos&error=csrf');

        $nombre = postStr('nombre');
        $email = postStr('email');
        $telefono = postStr('telefono');
        $color = postStr('color', '#3498db');

        if (empty($nombre)) {
            redirigir('index.php?accion=crearContacto&error=campos_vacios');
        }

        $this->modelContacto->crear($this->idUsuario, $nombre, $email, $telefono, $color);

        redirigir('index.php?accion=contactos&msg=contacto_creado');
    }

    public function editarContacto()
    {
        $id = getInt('id');
        $contacto = $this->modelContacto->obtenerPorId($id, $this->idUsuario);

        if (!$contacto) {
            redirigir('index.php?accion=contactos');
        }

        $this->render('contacto_editar', [
            'contacto' => $contacto,
            'usuario' => usuarioActual()
        ]);
    }

    public function actualizarContacto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php?accion=contactos');
        }

        requiereCsrf('index.php?accion=contactos&error=csrf');

        $id = postInt('id');
        $nombre = postStr('nombre');
        $email = postStr('email');
        $telefono = postStr('telefono');
        $color = postStr('color', '#3498db');
        $activo = post('activo') ? 1 : 0;

        if (empty($nombre)) {
            redirigir("index.php?accion=editarContacto&id=$id&error=campos_vacios");
        }

        $this->modelContacto->actualizar($id, $this->idUsuario, $nombre, $email, $telefono, $color, $activo);

        redirigir('index.php?accion=contactos&msg=contacto_actualizado');
    }

    public function eliminarContacto()
    {
        $id = getInt('id');
        if ($id > 0) {
            $this->modelContacto->eliminar($id, $this->idUsuario);
        }
        redirigir('index.php?accion=contactos&msg=contacto_eliminado');
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/gastos_compartidos/{$vista}.php";
    }
}