<?php
/**
 * Controlador: TransaccionController
 */

require_once __DIR__ . '/../models/Transaccion.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Cuenta.php';

class TransaccionController
{
    private $pdo;
    private $model;
    private $modelCategoria;
    private $modelCuenta;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Transaccion($pdo);
        $this->modelCategoria = new Categoria($pdo);
        $this->modelCuenta = new Cuenta($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $tipo = get('tipo', '');
        $buscar = get('buscar', '');

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

    public function crear()
    {
        $categorias = $this->modelCategoria->obtenerTodas($this->idUsuario);
        $cuentas = $this->modelCuenta->obtenerTodas($this->idUsuario, true);

        $this->render('crear', [
            'categorias' => $categorias,
            'cuentas' => $cuentas,
            'usuario' => usuarioActual()
        ]);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php');
        }

        requiereCsrf('index.php?accion=crear&error=csrf');

        $tipo = postStr('tipo');
        $monto = postFloat('monto');
        $fecha = postStr('fecha');
        $idCategoria = postInt('id_categoria');
        $idCuenta = postInt('id_cuenta');
        $descripcion = postStr('descripcion');
        $metodoPago = 'cuenta';  
        $recurrente = post('recurrente') ? 1 : 0;

        if (empty($tipo) || empty($fecha) || empty($idCategoria)) {
            toastError('Campos vacíos', 'Debes llenar todos los campos obligatorios');
            redirigir('index.php?accion=crear');
        }

        if ($monto <= 0) {
            redirigir('index.php?accion=crear&error=monto_invalido');
        }

        if (!in_array($tipo, ['ingreso', 'gasto'])) {
            toastError('Campos vacíos', 'Debes llenar todos los campos obligatorios');
            redirigir('index.php?accion=crear');
        }

        if (!$this->model->categoriaEsDelUsuario($idCategoria, $this->idUsuario)) {
            toastError('Campos vacíos', 'Debes llenar todos los campos obligatorios');
            redirigir('index.php?accion=crear');
        }

        if (!$this->model->cuentaEsDelUsuario($idCuenta, $this->idUsuario)) {
            toastError('Campos vacíos', 'Debes llenar todos los campos obligatorios');
            redirigir('index.php?accion=crear');
        }

        $this->model->crear(
            $this->idUsuario, $idCategoria, $idCuenta, $monto, $tipo,
            $descripcion, $fecha, $metodoPago, $recurrente
        );

        toastSuccess('Transacción creada', 'El movimiento se registró correctamente');
        redirigir('index.php');
    }

    public function editar()
    {
        $id = getInt('id');
        $transaccion = $this->model->obtenerPorId($id, $this->idUsuario);

        if (!$transaccion) {
            redirigir('index.php');
        }

        $categorias = $this->modelCategoria->obtenerTodas($this->idUsuario);
        $cuentas = $this->modelCuenta->obtenerTodas($this->idUsuario, true);

        $this->render('editar', [
            'transaccion' => $transaccion,
            'categorias' => $categorias,
            'cuentas' => $cuentas,
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
        $tipo = postStr('tipo');
        $monto = postFloat('monto');
        $fecha = postStr('fecha');
        $idCategoria = postInt('id_categoria');
        $idCuenta = postInt('id_cuenta');
        $descripcion = postStr('descripcion');
        $metodoPago = 'cuenta';
        $recurrente = post('recurrente') ? 1 : 0;

        if (empty($tipo) || empty($fecha) || empty($idCategoria) || $monto <= 0) {
            redirigir("index.php?accion=editar&id=$id&error=campos_vacios");
        }

        if (!$this->model->categoriaEsDelUsuario($idCategoria, $this->idUsuario)) {
            redirigir("index.php?accion=editar&id=$id&error=campos_vacios");
        }

        if (!$this->model->cuentaEsDelUsuario($idCuenta, $this->idUsuario)) {
            redirigir("index.php?accion=editar&id=$id&error=campos_vacios");
        }

        $this->model->actualizar(
            $id, $this->idUsuario, $idCategoria, $idCuenta, $monto, $tipo,
            $descripcion, $fecha, $metodoPago, $recurrente
        );

        toastSuccess('Transacción actualizada', 'Los cambios se guardaron correctamente');
        redirigir('index.php');
    }

    public function eliminar()
    {
        $id = getInt('id');

        if ($id > 0) {
            $this->model->eliminar($id, $this->idUsuario);
        }

        toastSuccess('Transacción eliminada', 'El movimiento se eliminó correctamente');
        redirigir('index.php');
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/transacciones/{$vista}.php";
    }
}