<?php
/**
 * Controlador: FuenteIngresoController
 */

require_once __DIR__ . '/../models/FuenteIngreso.php';

class FuenteIngresoController
{
    private $pdo;
    private $model;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new FuenteIngreso($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $fuentes = $this->model->obtenerTodas($this->idUsuario);
        $estabilidad = $this->model->calcularEstabilidad($this->idUsuario);

        $this->render('index', [
            'fuentes' => $fuentes,
            'estabilidad' => $estabilidad,
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
        $tipo = postStr('tipo', 'variable');
        $frecuencia = postStr('frecuencia', 'irregular');
        $montoPromedio = postFloat('monto_promedio');
        $color = postStr('color', '#27ae60');

        if (empty($nombre)) {
            redirigir('index.php?accion=crear&error=campos_vacios');
        }

        if ($this->model->existeNombre($this->idUsuario, $nombre)) {
            redirigir('index.php?accion=crear&error=nombre_existe');
        }

        $this->model->crear($this->idUsuario, $nombre, $tipo, $frecuencia, $montoPromedio, $color);

        toastSuccess('Título', 'Mensaje');
        redirigir('index.php');
    }

    public function editar()
    {
        $id = getInt('id');
        $fuente = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$fuente) {
            redirigir('index.php');
        }

        $this->render('editar', [
            'fuente' => $fuente,
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
        $tipo = postStr('tipo', 'variable');
        $frecuencia = postStr('frecuencia', 'irregular');
        $montoPromedio = postFloat('monto_promedio');
        $color = postStr('color', '#27ae60');
        $activa = post('activa') ? 1 : 0;

        if (empty($nombre)) {
            redirigir("index.php?accion=editar&id=$id&error=campos_vacios");
        }

        if ($this->model->existeNombre($this->idUsuario, $nombre, $id)) {
            redirigir("index.php?accion=editar&id=$id&error=nombre_existe");
        }

        $this->model->actualizar($id, $this->idUsuario, $nombre, $tipo, $frecuencia, $montoPromedio, $color, $activa);

        redirigir('index.php?msg=actualizado');
    }

    public function eliminar()
    {
        $id = getInt('id');
        if ($id > 0) {
            $this->model->eliminar($id, $this->idUsuario);
        }
        redirigir('index.php?msg=eliminado');
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/fuentes_ingreso/{$vista}.php";
    }
}