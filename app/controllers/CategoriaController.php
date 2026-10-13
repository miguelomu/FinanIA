<?php
/**
 * Controlador: CategoriaController
 * 
 * Maneja la lógica del módulo de categorías:
 * - Listar
 * - Crear
 * - Editar
 * - Actualizar
 * - Eliminar
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

    /**
     * Acción: Mostrar lista de categorías
     */
    public function index()
    {
        $tipo = $_GET['tipo'] ?? '';
        $categorias = $this->model->obtenerTodas($this->idUsuario, $tipo);

        // Datos para la vista
        $datos = [
            'categorias' => $categorias,
            'tipo' => $tipo,
            'usuario' => usuarioActual()
        ];

        $this->render('index', $datos);
    }

    /**
     * Acción: Mostrar formulario de creación
     */
    public function crear()
    {
        $datos = ['usuario' => usuarioActual()];
        $this->render('crear', $datos);
    }

    /**
     * Acción: Guardar nueva categoría
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $color = $_POST['color'] ?? '#3498db';
        $icono = trim($_POST['icono'] ?? '');

        // Validaciones
        if (empty($nombre) || empty($tipo)) {
            header('Location: index.php?accion=crear&error=campos_vacios');
            exit;
        }

        if (!in_array($tipo, ['ingreso', 'gasto'])) {
            header('Location: index.php?accion=crear&error=campos_vacios');
            exit;
        }

        if ($this->model->existeNombre($this->idUsuario, $nombre)) {
            header('Location: index.php?accion=crear&error=nombre_existe');
            exit;
        }

        $this->model->crear($this->idUsuario, $nombre, $tipo, $color, $icono);

        header('Location: index.php?msg=creado');
        exit;
    }

    /**
     * Acción: Mostrar formulario de edición
     */
    public function editar()
    {
        $id = intval($_GET['id'] ?? 0);
        $categoria = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$categoria) {
            header('Location: index.php');
            exit;
        }

        $datos = [
            'categoria' => $categoria,
            'usuario' => usuarioActual()
        ];

        $this->render('editar', $datos);
    }

    /**
     * Acción: Actualizar categoría
     */
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $color = $_POST['color'] ?? '#3498db';
        $icono = trim($_POST['icono'] ?? '');

        if (empty($nombre) || empty($tipo)) {
            header("Location: index.php?accion=editar&id=$id&error=campos_vacios");
            exit;
        }

        if ($this->model->existeNombre($this->idUsuario, $nombre, $id)) {
            header("Location: index.php?accion=editar&id=$id&error=nombre_existe");
            exit;
        }

        $this->model->actualizar($id, $this->idUsuario, $nombre, $tipo, $color, $icono);

        header('Location: index.php?msg=actualizado');
        exit;
    }

    /**
     * Acción: Eliminar categoría
     */
    public function eliminar()
    {
        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            header('Location: index.php');
            exit;
        }

        // Verificar integridad referencial
        if ($this->model->tieneTransacciones($id)) {
            header('Location: index.php?error=con_transacciones');
            exit;
        }

        if ($this->model->tienePresupuestos($id)) {
            header('Location: index.php?error=con_transacciones');
            exit;
        }

        $this->model->eliminar($id, $this->idUsuario);

        header('Location: index.php?msg=eliminado');
        exit;
    }

    /**
     * Renderiza una vista con datos
     */
    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/categorias/{$vista}.php";
    }
}