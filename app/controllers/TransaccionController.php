<?php
/**
 * Controlador: TransaccionController
 * 
 * Maneja la lógica del módulo de transacciones.
 */

require_once __DIR__ . '/../models/Transaccion.php';
require_once __DIR__ . '/../models/Categoria.php';

class TransaccionController
{
    private $pdo;
    private $model;
    private $modelCategoria;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Transaccion($pdo);
        $this->modelCategoria = new Categoria($pdo);
        $this->idUsuario = $idUsuario;
    }

    /**
     * Acción: Listar transacciones con filtros
     */
    public function index()
    {
        $tipo = $_GET['tipo'] ?? '';
        $buscar = $_GET['buscar'] ?? '';

        $transacciones = $this->model->obtenerTodas($this->idUsuario, $tipo, $buscar);
        $totales = $this->model->calcularTotales($transacciones);

        $this->render('index', [
            'transacciones' => $transacciones,
            'totales' => $totales,
            'tipo' => $tipo,
            'buscar' => $buscar,
            'usuario' => usuarioActual()
        ]);
    }

    /**
     * Acción: Mostrar formulario de creación
     */
    public function crear()
    {
        // Cargar categorías del usuario
        $categorias = $this->modelCategoria->obtenerTodas($this->idUsuario);

        $this->render('crear', [
            'categorias' => $categorias,
            'usuario' => usuarioActual()
        ]);
    }

    /**
     * Acción: Guardar nueva transacción
     */
    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $tipo = $_POST['tipo'] ?? '';
        $monto = floatval($_POST['monto'] ?? 0);
        $fecha = $_POST['fecha'] ?? '';
        $idCategoria = intval($_POST['id_categoria'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $metodoPago = $_POST['metodo_pago'] ?? 'efectivo';
        $recurrente = isset($_POST['recurrente']) ? 1 : 0;

        // Validaciones
        if (empty($tipo) || empty($fecha) || empty($idCategoria)) {
            header('Location: index.php?accion=crear&error=campos_vacios');
            exit;
        }

        if ($monto <= 0) {
            header('Location: index.php?accion=crear&error=monto_invalido');
            exit;
        }

        if (!in_array($tipo, ['ingreso', 'gasto'])) {
            header('Location: index.php?accion=crear&error=campos_vacios');
            exit;
        }

        if (!$this->model->categoriaEsDelUsuario($idCategoria, $this->idUsuario)) {
            header('Location: index.php?accion=crear&error=campos_vacios');
            exit;
        }

        $this->model->crear(
            $this->idUsuario, $idCategoria, $monto, $tipo,
            $descripcion, $fecha, $metodoPago, $recurrente
        );

        header('Location: index.php?msg=creado');
        exit;
    }

    /**
     * Acción: Mostrar formulario de edición
     */
    public function editar()
    {
        $id = intval($_GET['id'] ?? 0);
        $transaccion = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$transaccion) {
            header('Location: index.php');
            exit;
        }

        $categorias = $this->modelCategoria->obtenerTodas($this->idUsuario);

        $this->render('editar', [
            'transaccion' => $transaccion,
            'categorias' => $categorias,
            'usuario' => usuarioActual()
        ]);
    }

    /**
     * Acción: Actualizar transacción
     */
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        $monto = floatval($_POST['monto'] ?? 0);
        $fecha = $_POST['fecha'] ?? '';
        $idCategoria = intval($_POST['id_categoria'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $metodoPago = $_POST['metodo_pago'] ?? 'efectivo';
        $recurrente = isset($_POST['recurrente']) ? 1 : 0;

        // Validaciones
        if (empty($tipo) || empty($fecha) || empty($idCategoria) || $monto <= 0) {
            header("Location: index.php?accion=editar&id=$id&error=campos_vacios");
            exit;
        }

        if (!$this->model->categoriaEsDelUsuario($idCategoria, $this->idUsuario)) {
            header("Location: index.php?accion=editar&id=$id&error=campos_vacios");
            exit;
        }

        $this->model->actualizar(
            $id, $this->idUsuario, $idCategoria, $monto, $tipo,
            $descripcion, $fecha, $metodoPago, $recurrente
        );

        header('Location: index.php?msg=actualizado');
        exit;
    }

    /**
     * Acción: Eliminar transacción
     */
    public function eliminar()
    {
        $id = intval($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->model->eliminar($id, $this->idUsuario);
        }

        header('Location: index.php?msg=eliminado');
        exit;
    }

    /**
     * Renderiza una vista con datos
     */
    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/transacciones/{$vista}.php";
    }
}