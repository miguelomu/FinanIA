<?php
/**
 * Controlador: ImportarController
 */

require_once __DIR__ . '/../models/Importador.php';
require_once __DIR__ . '/../models/Cuenta.php';

class ImportarController
{
    private $pdo;
    private $model;
    private $modelCuenta;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Importador($pdo, $idUsuario);
        $this->modelCuenta = new Cuenta($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $cuentas = $this->modelCuenta->obtenerTodas($this->idUsuario, true);

        $this->render('index', [
            'cuentas' => $cuentas,
            'preview' => null,
            'usuario' => usuarioActual()
        ]);
    }

    /**
     * API: Previsualiza el archivo CSV subido
     */
    public function preview()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        if (!validarCsrf()) {
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }

        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => 'No se recibió el archivo']);
            exit;
        }

        $archivo = $_FILES['archivo'];

        // Validar extensión
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            echo json_encode(['error' => 'Solo se permiten archivos CSV']);
            exit;
        }

        // Validar tamaño (max 5 MB)
        if ($archivo['size'] > 5 * 1024 * 1024) {
            echo json_encode(['error' => 'El archivo es muy grande (máximo 5 MB)']);
            exit;
        }

        try {
            $csv = $this->model->leerCSV($archivo['tmp_name']);
            $mapeo = $this->model->detectarColumnas($csv['columnas'], $csv['filas']);

            // Limitar preview a 20 filas
            $preview = array_slice($csv['filas'], 0, 20);

            // Guardar el archivo temporalmente
            $nombreTemporal = 'import_' . $this->idUsuario . '_' . time() . '.csv';
            $rutaTemporal = __DIR__ . '/../../assets/uploads/' . $nombreTemporal;
            move_uploaded_file($archivo['tmp_name'], $rutaTemporal);

            echo json_encode([
                'success' => true,
                'columnas' => $csv['columnas'],
                'mapeo' => $mapeo,
                'preview' => $preview,
                'total' => $csv['total'],
                'archivo_temporal' => $nombreTemporal
            ]);

        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Importa el archivo CSV previamente analizado
     */
    public function importar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        if (!validarCsrf()) {
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }

        $nombreArchivo = $_POST['archivo_temporal'] ?? '';
        $idCuenta = intval($_POST['id_cuenta'] ?? 0);
        $mapeo = $_POST['mapeo'] ?? [];

        if (empty($nombreArchivo) || $idCuenta <= 0 || empty($mapeo)) {
            echo json_encode(['error' => 'Datos incompletos']);
            exit;
        }

        // Validar que la cuenta pertenece al usuario
        $cuenta = $this->modelCuenta->obtenerPorId($idCuenta, $this->idUsuario);
        if (!$cuenta) {
            echo json_encode(['error' => 'Cuenta no válida']);
            exit;
        }

        // Ruta del archivo temporal
        $rutaArchivo = __DIR__ . '/../../assets/uploads/' . basename($nombreArchivo);

        if (!file_exists($rutaArchivo)) {
            echo json_encode(['error' => 'Archivo no encontrado o expirado']);
            exit;
        }

        try {
            $csv = $this->model->leerCSV($rutaArchivo);
            $resultado = $this->model->importar($csv['filas'], $mapeo, $idCuenta);

            // Eliminar archivo temporal
            @unlink($rutaArchivo);

            echo json_encode([
                'success' => true,
                'importadas' => $resultado['importadas'],
                'errores' => $resultado['errores']
            ]);

        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/importar/{$vista}.php";
    }
}