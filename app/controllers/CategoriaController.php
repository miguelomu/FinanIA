<?php
/**
 * Controlador: CategoriaController
 */

require_once __DIR__ . '/../models/Categoria.php';

class CategoriaController
{
    private $pdo;
    private $model;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Categoria($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $tipo = get('tipo', '');
        $categorias = $this->model->obtenerTodas($this->idUsuario, $tipo);

        $this->render('index', [
            'categorias' => $categorias,
            'tipo' => $tipo,
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
        $tipo = postStr('tipo');
        $color = postStr('color', '#3498db');
        $icono = postStr('icono');

        if (empty($nombre) || empty($tipo)) {
            toastError('Campos vacíos', 'El nombre y el tipo son obligatorios');
            redirigir('index.php?accion=crear');
        }

        if (!in_array($tipo, ['ingreso', 'gasto'])) {
            toastError('Tipo inválido', 'Debes seleccionar ingreso o gasto');
            redirigir('index.php?accion=crear');
        }

        if ($this->model->existeNombre($this->idUsuario, $nombre)) {
            toastError('Nombre duplicado', 'Ya tienes una categoría con ese nombre');
            redirigir('index.php?accion=crear');
        }

        $this->model->crear($this->idUsuario, $nombre, $tipo, $color, $icono);

        toastSuccess('Categoría creada', 'La categoría se registró correctamente');
        redirigir('index.php');
    }

    public function editar()
    {
        $id = getInt('id');
        $categoria = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$categoria) {
            redirigir('index.php');
        }

        $this->render('editar', [
            'categoria' => $categoria,
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
        $color = postStr('color', '#3498db');
        $icono = postStr('icono');

        if (empty($nombre) || empty($tipo)) {
            toastError('Campos vacíos', 'El nombre y el tipo son obligatorios');
            redirigir("index.php?accion=editar&id=$id");
        }

        if (!in_array($tipo, ['ingreso', 'gasto'])) {
            toastError('Tipo inválido', 'Debes seleccionar ingreso o gasto');
            redirigir("index.php?accion=editar&id=$id");
        }

        if ($this->model->existeNombre($this->idUsuario, $nombre, $id)) {
            toastError('Nombre duplicado', 'Ya tienes otra categoría con ese nombre');
            redirigir("index.php?accion=editar&id=$id");
        }

        $this->model->actualizar($id, $this->idUsuario, $nombre, $tipo, $color, $icono);

        toastSuccess('Categoría actualizada', 'Los cambios se guardaron correctamente');
        redirigir('index.php');
    }

    public function eliminar()
    {
        $id = getInt('id');

        if ($id > 0) {
            if ($this->model->tieneTransacciones($id)) {
                toastWarning('No se puede eliminar', 'Esta categoría tiene transacciones asociadas');
                redirigir('index.php');
            }

            if ($this->model->tienePresupuestos($id)) {
                toastWarning('No se puede eliminar', 'Esta categoría tiene presupuestos asociados');
                redirigir('index.php');
            }

            $this->model->eliminar($id, $this->idUsuario);
        }

        toastSuccess('Categoría eliminada', 'La categoría se eliminó correctamente');
        redirigir('index.php');
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/categorias/{$vista}.php";
    }
}