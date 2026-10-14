<?php
require_once __DIR__ . '/../models/Logro.php';

class LogroController
{
    private $pdo;
    private $model;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Logro($pdo, $idUsuario);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        // Verificar logros nuevos antes de mostrar
        $nuevos = $this->model->verificarTodos();
        $resumen = $this->model->resumen();

        $this->render('index', [
            'resumen' => $resumen,
            'nuevos' => $nuevos,
            'usuario' => usuarioActual()
        ]);
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/logros/{$vista}.php";
    }
}