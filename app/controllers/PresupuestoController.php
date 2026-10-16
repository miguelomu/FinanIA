<?php
/**
 * Controlador: PresupuestoController
 */

require_once __DIR__ . '/../models/Presupuesto.php';
require_once __DIR__ . '/../models/Categoria.php';

class PresupuestoController
{
    private $pdo;
    private $model;
    private $modelCategoria;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Presupuesto($pdo);
        $this->modelCategoria = new Categoria($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $presupuestos = $this->model->obtenerTodos($this->idUsuario);

        $this->render('index', [
            'presupuestos' => $presupuestos,
            'usuario' => usuarioActual()
        ]);
    }

    public function crear()
    {
        $categorias = $this->modelCategoria->obtenerTodas($this->idUsuario, 'gasto');

        $this->render('crear', [
            'categorias' => $categorias,
            'usuario' => usuarioActual()
        ]);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php');
        }

        requiereCsrf('index.php?error=csrf');

        $mes = postInt('mes');
        $anio = postInt('anio');
        $limites = $_POST['limites'] ?? [];

        if ($mes < 1 || $mes > 12 || $anio < 2000) {
            toastError('Datos inválidos', 'Debes seleccionar un mes y año válidos');
            redirigir('index.php?accion=crear');
        }

        if ($this->model->existe($this->idUsuario, $mes, $anio)) {
            toastWarning('Presupuesto duplicado', 'Ya tienes un presupuesto para ese mes');
            redirigir('index.php?accion=crear');
        }

        $limitesValidos = $this->filtrarLimites($limites);

        $this->model->crear($this->idUsuario, $mes, $anio, $limitesValidos);

        toastSuccess('Presupuesto creado', 'Tu presupuesto mensual está listo');
        redirigir('index.php');
    }

    public function editar()
    {
        $id = getInt('id');
        $presupuesto = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$presupuesto) {
            redirigir('index.php');
        }

        $categorias = $this->modelCategoria->obtenerTodas($this->idUsuario, 'gasto');
        $limitesActuales = $this->model->obtenerLimitesActuales($id);

        $this->render('editar', [
            'presupuesto' => $presupuesto,
            'categorias' => $categorias,
            'limitesActuales' => $limitesActuales,
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
        $mes = postInt('mes');
        $anio = postInt('anio');
        $limites = $_POST['limites'] ?? [];

        if ($this->model->existe($this->idUsuario, $mes, $anio, $id)) {
            toastWarning('Presupuesto duplicado', 'Ya tienes un presupuesto para ese mes');
            redirigir("index.php?accion=editar&id=$id");
        }

        $limitesValidos = $this->filtrarLimites($limites);

        $this->model->actualizar($id, $mes, $anio, $limitesValidos);

        toastSuccess('Presupuesto actualizado', 'Los cambios se guardaron correctamente');
        redirigir('index.php');
    }

    public function eliminar()
    {
        $id = getInt('id');
        if ($id > 0) {
            $this->model->eliminar($id, $this->idUsuario);
        }

        toastSuccess('Presupuesto eliminado', 'El presupuesto se eliminó correctamente');
        redirigir('index.php');
    }

    public function ver()
    {
        $id = getInt('id');
        $presupuesto = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$presupuesto) {
            redirigir('index.php');
        }

        $detalles = $this->model->obtenerDetalleConGasto(
            $id,
            $this->idUsuario,
            $presupuesto['mes'],
            $presupuesto['anio']
        );

        $totales = $this->model->calcularTotales($detalles);

        $this->render('ver', [
            'presupuesto' => $presupuesto,
            'detalles' => $detalles,
            'totales' => $totales,
            'usuario' => usuarioActual()
        ]);
    }

    private function filtrarLimites($limites)
    {
        $categorias = $this->modelCategoria->obtenerTodas($this->idUsuario, 'gasto');
        $idsValidos = array_column($categorias, 'id_categoria');

        $resultado = [];
        foreach ($limites as $idCat => $limite) {
            if (in_array($idCat, $idsValidos)) {
                $resultado[$idCat] = floatval($limite);
            }
        }
        return $resultado;
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/presupuestos/{$vista}.php";
    }
}