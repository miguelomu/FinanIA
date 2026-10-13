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
        // Solo categorías de gasto
        $categorias = $this->modelCategoria->obtenerTodas($this->idUsuario, 'gasto');

        $this->render('crear', [
            'categorias' => $categorias,
            'usuario' => usuarioActual()
        ]);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        requiereCsrf('index.php?error=csrf');

        $mes = intval($_POST['mes'] ?? 0);
        $anio = intval($_POST['anio'] ?? 0);
        $limites = $_POST['limites'] ?? [];

        if ($mes < 1 || $mes > 12 || $anio < 2000) {
            header('Location: index.php?accion=crear&error=campos_vacios');
            exit;
        }

        if ($this->model->existe($this->idUsuario, $mes, $anio)) {
            header('Location: index.php?accion=crear&error=ya_existe');
            exit;
        }

        // Filtrar solo categorías del usuario
        $limitesValidos = $this->filtrarLimites($limites);

        $this->model->crear($this->idUsuario, $mes, $anio, $limitesValidos);

        header('Location: index.php?msg=creado');
        exit;
    }

    public function editar()
    {
        $id = intval($_GET['id'] ?? 0);
        $presupuesto = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$presupuesto) {
            header('Location: index.php');
            exit;
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
            header('Location: index.php');
            exit;
        }

        requiereCsrf('index.php?error=csrf');

        $id = intval($_POST['id'] ?? 0);
        $mes = intval($_POST['mes'] ?? 0);
        $anio = intval($_POST['anio'] ?? 0);
        $limites = $_POST['limites'] ?? [];

        if ($this->model->existe($this->idUsuario, $mes, $anio, $id)) {
            header("Location: index.php?accion=editar&id=$id&error=ya_existe");
            exit;
        }

        $limitesValidos = $this->filtrarLimites($limites);

        $this->model->actualizar($id, $mes, $anio, $limitesValidos);

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

    public function ver()
    {
        $id = intval($_GET['id'] ?? 0);
        $presupuesto = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$presupuesto) {
            header('Location: index.php');
            exit;
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

    /**
     * Filtra los límites para quedarse solo con categorías del usuario
     */
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