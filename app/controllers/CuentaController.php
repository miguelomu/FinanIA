<?php
/**
 * Controlador: CuentaController
 */

require_once __DIR__ . '/../models/Cuenta.php';

class CuentaController
{
    private $pdo;
    private $model;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Cuenta($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $cuentas = $this->model->obtenerTodas($this->idUsuario);
        $patrimonio = $this->model->calcularPatrimonio($this->idUsuario);

        $this->render('index', [
            'cuentas' => $cuentas,
            'patrimonio' => $patrimonio,
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

        requiereCsrf('index.php?accion=crear&error=csrf');

        $nombre = postStr('nombre');
        $tipo = postStr('tipo');
        $saldoInicial = postFloat('saldo_inicial');
        $etiqueta = postStr('etiqueta');
        $color = postStr('color', '#3498db');

        if (empty($nombre) || empty($tipo)) {
            redirigir('index.php?accion=crear&error=campos_vacios');
        }

        if ($this->model->existeNombre($this->idUsuario, $nombre)) {
            redirigir('index.php?accion=crear&error=nombre_existe');
        }

        $this->model->crear($this->idUsuario, $nombre, $tipo, $saldoInicial, $etiqueta, $color);

        redirigir('index.php?msg=creado');
    }

    public function editar()
    {
        $id = getInt('id');
        $cuenta = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$cuenta) {
            redirigir('index.php');
        }

        $this->render('editar', [
            'cuenta' => $cuenta,
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
        $nombre = postStr('nombre');
        $tipo = postStr('tipo');
        $saldoInicial = postFloat('saldo_inicial');
        $etiqueta = postStr('etiqueta');
        $color = postStr('color', '#3498db');
        $activa = post('activa') ? 1 : 0;

        if (empty($nombre) || empty($tipo)) {
            redirigir("index.php?accion=editar&id=$id&error=campos_vacios");
        }

        if ($this->model->existeNombre($this->idUsuario, $nombre, $id)) {
            redirigir("index.php?accion=editar&id=$id&error=nombre_existe");
        }

        $this->model->actualizar($id, $this->idUsuario, $nombre, $tipo, $saldoInicial, $etiqueta, $color, $activa);

        redirigir('index.php?msg=actualizado');
    }

    public function eliminar()
    {
        $id = getInt('id');

        if ($id > 0) {
            if ($this->model->tieneMovimientos($id)) {
                redirigir('index.php?error=con_movimientos');
            }
            $this->model->eliminar($id, $this->idUsuario);
        }

        redirigir('index.php?msg=eliminado');
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/cuentas/{$vista}.php";
    }
}