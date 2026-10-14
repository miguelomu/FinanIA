<?php
/**
 * Controlador: ConfiguracionController
 */

require_once __DIR__ . '/../models/Configuracion.php';

class ConfiguracionController
{
    private $pdo;
    private $model;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Configuracion($pdo);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $config = $this->model->obtenerTodos();

        $this->render('index', [
            'config' => $config,
            'usuario' => usuarioActual()
        ]);
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php');
        }

        requiereCsrf('index.php?error=csrf');

        $tasaUsura = postFloat('tasa_usura_ea');
        $inflacion = postFloat('inflacion_anual');
        $tasaInteres = postFloat('tasa_interes_bancario_promedio');

        if ($tasaUsura > 0) $this->model->actualizar('tasa_usura_ea', $tasaUsura);
        if ($inflacion > 0) $this->model->actualizar('inflacion_anual', $inflacion);
        if ($tasaInteres > 0) $this->model->actualizar('tasa_interes_bancario_promedio', $tasaInteres);

        redirigir('index.php?msg=actualizado');
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/configuracion/{$vista}.php";
    }
}