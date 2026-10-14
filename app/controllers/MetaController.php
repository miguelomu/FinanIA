<?php
/**
 * Controlador: MetaController
 * 
 * Maneja la lógica de metas y aportes.
 */

require_once __DIR__ . '/../models/Meta.php';
require_once __DIR__ . '/../models/Aporte.php';

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
        $estado = $_GET['estado'] ?? '';
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
            header('Location: index.php');
            exit;
        }

        requiereCsrf('index.php?error=csrf');

        $nombre = trim($_POST['nombre'] ?? '');
        $montoObjetivo = floatval($_POST['monto_objetivo'] ?? 0);
        $montoInicial = floatval($_POST['monto_actual'] ?? 0);
        $fechaInicio = $_POST['fecha_inicio'] ?? '';
        $fechaLimite = $_POST['fecha_limite'] ?? '';

        if (empty($nombre) || empty($fechaInicio) || empty($fechaLimite)) {
            header('Location: index.php?accion=crear&error=campos_vacios');
            exit;
        }

        if ($montoObjetivo <= 0) {
            header('Location: index.php?accion=crear&error=monto_invalido');
            exit;
        }

        if (strtotime($fechaLimite) <= strtotime($fechaInicio)) {
            header('Location: index.php?accion=crear&error=fecha_invalida');
            exit;
        }

        $this->model->crear(
            $this->idUsuario, $nombre, $montoObjetivo,
            $montoInicial, $fechaInicio, $fechaLimite
        );

        header('Location: index.php?msg=creado');
        exit;
    }

    public function editar()
    {
        $id = intval($_GET['id'] ?? 0);
        $meta = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$meta) {
            header('Location: index.php');
            exit;
        }

        $this->render('editar', [
            'meta' => $meta,
            'usuario' => usuarioActual()
        ]);
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        requiereCsrf('index.php?error=csrf');

        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $montoObjetivo = floatval($_POST['monto_objetivo'] ?? 0);
        $montoInicial = floatval($_POST['monto_actual'] ?? 0);
        $fechaInicio = $_POST['fecha_inicio'] ?? '';
        $fechaLimite = $_POST['fecha_limite'] ?? '';
        $estado = $_POST['estado'] ?? 'activa';

        if (empty($nombre) || empty($fechaInicio) || empty($fechaLimite) || $montoObjetivo <= 0) {
            header("Location: index.php?accion=editar&id=$id&error=campos_vacios");
            exit;
        }

        if (!in_array($estado, ['activa', 'completada', 'cancelada'])) {
            $estado = 'activa';
        }

        $this->model->actualizar(
            $id, $this->idUsuario, $nombre, $montoObjetivo,
            $montoInicial, $fechaInicio, $fechaLimite, $estado
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
    // APORTES
    // ==========================================

    public function ver()
    {
        $id = intval($_GET['id'] ?? 0);
        $meta = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$meta) {
            header('Location: index.php');
            exit;
        }

        $aportes = $this->modelAporte->obtenerPorMeta($id);

        $progreso = $meta['monto_objetivo'] > 0 
            ? min(100, ($meta['monto_actual'] / $meta['monto_objetivo']) * 100) 
            : 0;

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

                redirigir("index.php?accion=ver&id=$id&msg=aportado");
            }
        }

        $progreso = calcularProgreso($meta['monto_actual'], $meta['monto_objetivo']);

        // Cargar cuentas disponibles
        require_once __DIR__ . '/../models/Cuenta.php';
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

        // Cargar cuentas disponibles
        require_once __DIR__ . '/../models/Cuenta.php';
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
            redirigir("index.php?accion=editarAporte&id=$id&error=monto_invalido");
        }

        $aporte = $this->modelAporte->obtenerPorId($id, $this->idUsuario);
        if (!$aporte) {
            redirigir('index.php');
        }

        $idMeta = $aporte['id_meta'];

        $this->modelAporte->actualizar($id, $idCuenta, $monto, $nota);
        $this->model->recalcular($idMeta);

        redirigir("index.php?accion=ver&id=$idMeta&msg=editado");
    }

    public function eliminarAporte()
    {
        $id = intval($_GET['id'] ?? 0);

        $aporte = $this->modelAporte->obtenerPorId($id, $this->idUsuario);
        if (!$aporte) {
            header('Location: index.php');
            exit;
        }

        $idMeta = $aporte['id_meta'];

        $this->modelAporte->eliminar($id);
        $this->model->recalcular($idMeta);

        header("Location: index.php?accion=ver&id=$idMeta&msg=eliminado");
        exit;
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/metas/{$vista}.php";
    }
}