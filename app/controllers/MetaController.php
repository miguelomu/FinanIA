<?php
/**
 * Controlador: MetaController
 */

require_once __DIR__ . '/../models/Meta.php';
require_once __DIR__ . '/../models/Aporte.php';
require_once __DIR__ . '/../models/Cuenta.php';

class MetaController
{
    private $pdo;
    private $model;
    private $modelAporte;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Meta($pdo);
        $this->modelAporte = new Aporte($pdo);
        $this->idUsuario = $idUsuario;
    }

    // ==========================================
    // METAS
    // ==========================================

    public function index()
    {
        $estado = get('estado', '');
        $metas = $this->model->obtenerTodas($this->idUsuario, $estado);
        $totales = $this->model->calcularTotales($metas);

        $this->render('index', [
            'metas' => $metas,
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
            redirigir('index.php');
        }

        requiereCsrf('index.php?error=csrf');

        $nombre = postStr('nombre');
        $montoObjetivo = postFloat('monto_objetivo');
        $montoInicial = postFloat('monto_actual');
        $fechaInicio = postStr('fecha_inicio');
        $fechaLimite = postStr('fecha_limite');

        if (empty($nombre) || empty($fechaInicio) || empty($fechaLimite)) {
            toastError('Campos vacíos', 'Todos los campos obligatorios deben llenarse');
            redirigir('index.php?accion=crear');
        }

        if ($montoObjetivo <= 0) {
            toastError('Monto inválido', 'El monto objetivo debe ser mayor a cero');
            redirigir('index.php?accion=crear');
        }

        if (strtotime($fechaLimite) <= strtotime($fechaInicio)) {
            toastError('Fechas inválidas', 'La fecha límite debe ser posterior a la de inicio');
            redirigir('index.php?accion=crear');
        }

        $this->model->crear(
            $this->idUsuario, $nombre, $montoObjetivo,
            $montoInicial, $fechaInicio, $fechaLimite
        );

        toastSuccess('Meta creada', 'Tu meta se registró correctamente');
        redirigir('index.php');
    }

    public function editar()
    {
        $id = getInt('id');
        $meta = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$meta) {
            redirigir('index.php');
        }

        $this->render('editar', [
            'meta' => $meta,
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
        $montoObjetivo = postFloat('monto_objetivo');
        $montoInicial = postFloat('monto_actual');
        $fechaInicio = postStr('fecha_inicio');
        $fechaLimite = postStr('fecha_limite');
        $estado = postStr('estado', 'activa');

        if (empty($nombre) || empty($fechaInicio) || empty($fechaLimite) || $montoObjetivo <= 0) {
            toastError('Campos vacíos', 'Todos los campos obligatorios deben llenarse');
            redirigir("index.php?accion=editar&id=$id");
        }

        if (!in_array($estado, ['activa', 'completada', 'cancelada'])) {
            $estado = 'activa';
        }

        $this->model->actualizar(
            $id, $this->idUsuario, $nombre, $montoObjetivo,
            $montoInicial, $fechaInicio, $fechaLimite, $estado
        );

        toastSuccess('Meta actualizada', 'Los cambios se guardaron correctamente');
        redirigir('index.php');
    }

    public function eliminar()
    {
        $id = getInt('id');
        if ($id > 0) {
            $this->model->eliminar($id, $this->idUsuario);
        }

        toastSuccess('Meta eliminada', 'La meta se eliminó correctamente');
        redirigir('index.php');
    }

    // ==========================================
    // APORTES
    // ==========================================

    public function ver()
    {
        $id = getInt('id');
        $meta = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$meta) {
            redirigir('index.php');
        }

        $aportes = $this->modelAporte->obtenerPorMeta($id);
        $progreso = calcularProgreso($meta['monto_actual'], $meta['monto_objetivo']);

        $this->render('ver', [
            'meta' => $meta,
            'aportes' => $aportes,
            'progreso' => $progreso,
            'usuario' => usuarioActual()
        ]);
    }

    public function aportar()
    {
        $id = getInt('id');
        $meta = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$meta) {
            redirigir('index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requiereCsrf("index.php?accion=aportar&id=$id&error=csrf");

            $monto = postFloat('monto');
            $nota = postStr('nota');
            $idCuenta = postInt('id_cuenta');

            if ($monto > 0) {
                $this->modelAporte->crear($id, $this->idUsuario, $idCuenta, $monto, $nota);
                $this->model->recalcular($id);

                toastSuccess('Aporte registrado', 'El aporte se sumó a tu meta');
                redirigir("index.php?accion=ver&id=$id");
            } else {
                toastError('Monto inválido', 'El monto debe ser mayor a cero');
                redirigir("index.php?accion=aportar&id=$id");
            }
        }

        $progreso = calcularProgreso($meta['monto_actual'], $meta['monto_objetivo']);

        $modelCuenta = new Cuenta($this->pdo);
        $cuentas = $modelCuenta->obtenerTodas($this->idUsuario, true);

        $this->render('aportar', [
            'meta' => $meta,
            'cuentas' => $cuentas,
            'progreso' => $progreso,
            'usuario' => usuarioActual()
        ]);
    }

    public function editarAporte()
    {
        $id = getInt('id');
        $aporte = $this->modelAporte->obtenerPorId($id, $this->idUsuario);

        if (!$aporte) {
            redirigir('index.php');
        }

        $modelCuenta = new Cuenta($this->pdo);
        $cuentas = $modelCuenta->obtenerTodas($this->idUsuario, true);

        $this->render('editar_aporte', [
            'aporte' => $aporte,
            'cuentas' => $cuentas,
            'usuario' => usuarioActual()
        ]);
    }

    public function actualizarAporte()
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
            redirigir("index.php?accion=editarAporte&id=$id");
        }

        $aporte = $this->modelAporte->obtenerPorId($id, $this->idUsuario);
        if (!$aporte) {
            redirigir('index.php');
        }

        $idMeta = $aporte['id_meta'];

        $this->modelAporte->actualizar($id, $idCuenta, $monto, $nota);
        $this->model->recalcular($idMeta);

        toastSuccess('Aporte actualizado', 'Los cambios se guardaron correctamente');
        redirigir("index.php?accion=ver&id=$idMeta");
    }

    public function eliminarAporte()
    {
        $id = getInt('id');
        $aporte = $this->modelAporte->obtenerPorId($id, $this->idUsuario);

        if (!$aporte) {
            redirigir('index.php');
        }

        $idMeta = $aporte['id_meta'];

        $this->modelAporte->eliminar($id);
        $this->model->recalcular($idMeta);

        toastSuccess('Aporte eliminado', 'El aporte se eliminó de tu meta');
        redirigir("index.php?accion=ver&id=$idMeta");
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/metas/{$vista}.php";
    }
}