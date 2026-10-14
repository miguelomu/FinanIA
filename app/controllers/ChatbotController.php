<?php
/**
 * Controlador: ChatbotController
 */

require_once __DIR__ . '/../models/Chatbot.php';

class ChatbotController
{
    private $pdo;
    private $model;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->model = new Chatbot($pdo, $idUsuario);
        $this->idUsuario = $idUsuario;
    }

    public function index()
    {
        $historial = $this->model->obtenerHistorial(50);

        $this->render('index', [
            'historial' => $historial,
            'usuario' => usuarioActual()
        ]);
    }

    /**
     * Procesa un mensaje (llamada AJAX)
     */
    public function enviar()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        if (!validarCsrf()) {
            echo json_encode(['error' => 'Token CSRF inválido']);
            exit;
        }

        $mensaje = trim($_POST['mensaje'] ?? '');

        if (empty($mensaje)) {
            echo json_encode(['error' => 'Mensaje vacío']);
            exit;
        }

        try {
            $resultado = $this->model->procesar($mensaje);
            echo json_encode([
                'success' => true,
                'respuesta' => $resultado['respuesta'],
                'intencion' => $resultado['intencion']
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => 'Error al procesar el mensaje',
                'detalle' => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Limpia el historial
     */
    public function limpiar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirigir('index.php');
        }

        requiereCsrf('index.php?error=csrf');

        $this->model->limpiarHistorial();
        redirigir('index.php?msg=limpiado');
    }

    private function render($vista, $datos = [])
    {
        extract($datos);
        require __DIR__ . "/../views/chatbot/{$vista}.php";
    }
}