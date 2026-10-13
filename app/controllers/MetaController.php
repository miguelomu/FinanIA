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
        $id = intval($_GET['id'] ?? 0);
        $meta = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$meta) {
            header('Location: index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $monto = floatval($_POST['monto'] ?? 0);
            $nota = trim($_POST['nota'] ?? '');

            if ($monto > 0) {
                $this->modelAporte->crear($id, $this->idUsuario, $monto, $nota);
                $this->model->recalcular($id);

                header("Location: index.php?accion=ver&id=$id&msg=aportado");
                exit;
            }
        }

        // 👇 CALCULAR EL PROGRESO ANTES DE RENDERIZAR
        $progreso = $meta['monto_objetivo'] > 0 
            ? min(100, ($meta['monto_actual'] / $meta['monto_objetivo']) * 100) 
            : 0;

        $this->render('aportar', [
            'meta' => $meta,
            'progreso' => $progreso,       // 👈 AGREGAR ESTA LÍNEA
            'usuario' => usuarioActual()
        ]);
    }

    public function editarAporte()
    {
        $id = intval($_GET['id'] ?? 0);
        $aporte = $this->modelAporte->obtenerPorId($id, $this->idUsuario);

        if (!$aporte) {
            header('Location: index.php');
            exit;
        }

        $this->render('editar_aporte', [
            'aporte' => $aporte,
            'usuario' => usuarioActual()
        ]);
    }

    public function actualizarAporte()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        $monto = floatval($_POST['monto'] ?? 0);
        $nota = trim($_POST['nota'] ?? '');

        if ($monto <= 0) {
            header("Location: index.php?accion=editarAporte&id=$id&error=monto_invalido");
            exit;
        }

        $aporte = $this->modelAporte->obtenerPorId($id, $this->idUsuario);
        if (!$aporte) {
            header('Location: index.php');
            exit;
        }

        $idMeta = $aporte['id_meta'];

        $this->modelAporte->actualizar($id, $monto, $nota);
        $this->model->recalcular($idMeta);

        header("Location: index.php?accion=ver&id=$idMeta&msg=editado");
        exit;
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