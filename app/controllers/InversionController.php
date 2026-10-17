<?php
require_once __DIR__ . '/../models/Inversion.php';
require_once __DIR__ . '/../models/PerfilRiesgo.php';

class InversionController
{
    private $pdo;
    private $model;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Inversion($pdo, $idUsuario);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $perfil = PerfilRiesgo::obtenerPerfil($this->pdo, $this->idUsuario) ?: 'moderado';
        $presupuesto = getFloat('presupuesto', 0);
        
        // Si no hay presupuesto, usamos uno por defecto para los cálculos (ej. 1.000.000)
        $montoCalculo = $presupuesto > 0 ? $presupuesto : 1000000;

        // Obtener TODAS las opciones intangibles (la primera es la recomendada)
        $opcionesIntangibles = $this->model->obtenerTodasLasIntangibles($perfil, $montoCalculo);
        
        // Obtener productos tangibles (con filtro de presupuesto)
        $productosTangibles = $this->model->obtenerProductosTangibles($presupuesto);

        $this->render('index', [
            'perfil' => $perfil,
            'opcionesIntangibles' => $opcionesIntangibles,
            'productosTangibles' => $productosTangibles,
            'presupuesto' => $presupuesto,
            'usuario' => usuarioActual()
        ]);
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/inversion/{$vista}.php";
    }

        /**
     * API: Obtiene productos tangibles filtrados por presupuesto (AJAX)
     */
    public function productosTangiblesAPI()
    {
        header('Content-Type: application/json');
        
        $presupuesto = getFloat('presupuesto', 0);
        $productos = $this->model->obtenerProductosTangibles($presupuesto);
        
        echo json_encode([
            'success' => true,
            'productos' => $productos
        ]);
        exit;
    }
}
?>