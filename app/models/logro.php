<?php
/**
 * Modelo: Logro
 * 
 * Detecta y desbloquea logros automáticamente.
 */

class Logro
{
    private $pdo;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->idUsuario = $idUsuario;
    }

    /**
     * Verifica y desbloquea todos los logros posibles
     * 
     * @return array Logros nuevos desbloqueados
     */
    public function verificarTodos()
    {
        $nuevos = [];

        // Primera cuenta
        if ($this->contar('cuenta') > 0) {
            $this->desbloquear('primera_cuenta', $nuevos);
        }

        // Primera transacción
        if ($this->contar('transaccion') > 0) {
            $this->desbloquear('primera_transaccion', $nuevos);
        }

        // Primera meta
        if ($this->contar('meta') > 0) {
            $this->desbloquear('primera_meta', $nuevos);
        }

        // Primera deuda
        if ($this->contar('deuda') > 0) {
            $this->desbloquear('primera_deuda', $nuevos);
        }

        // 10 transacciones
        if ($this->contar('transaccion') >= 10) {
            $this->desbloquear('10_transacciones', $nuevos);
        }

        // 50 transacciones
        if ($this->contar('transaccion') >= 50) {
            $this->desbloquear('50_transacciones', $nuevos);
        }

        // Racha de 7 días
        if ($this->racha() >= 7) {
            $this->desbloquear('racha_7', $nuevos);
        }

        // Racha de 30 días
        if ($this->racha() >= 30) {
            $this->desbloquear('racha_30', $nuevos);
        }

        // Meta cumplida
        if ($this->metasCumplidas() >= 1) {
            $this->desbloquear('meta_cumplida', $nuevos);
        }

        // Deuda pagada
        if ($this->deudasPagadas() >= 1) {
            $this->desbloquear('deuda_pagada', $nuevos);
        }

        // Salud financiera
        require_once __DIR__ . '/SaludFinanciera.php';
        $modelSalud = new SaludFinanciera($this->pdo, $this->idUsuario);
        $salud = $modelSalud->calcular();

        if ($salud['total'] >= 70) {
            $this->desbloquear('salud_70', $nuevos);
        }
        if ($salud['total'] >= 90) {
            $this->desbloquear('salud_90', $nuevos);
        }

        return $nuevos;
    }

    /**
     * Desbloquea un logro si no lo tiene ya
     */
    private function desbloquear($clave, &$nuevos = null)
    {
        if ($nuevos === null) $nuevos = [];
        // Verificar que ya no lo tenga
        $stmt = $this->pdo->prepare(
            "SELECT ul.id_usuario_logro 
             FROM usuario_logro ul
             INNER JOIN logro l ON ul.id_logro = l.id_logro
             WHERE ul.id_usuario = ? AND l.clave = ?"
        );
        $stmt->execute([$this->idUsuario, $clave]);
        if ($stmt->fetch()) return;

        // Obtener id del logro
        $stmt = $this->pdo->prepare("SELECT id_logro FROM logro WHERE clave = ?");
        $stmt->execute([$clave]);
        $logro = $stmt->fetch();
        if (!$logro) return;

        // Insertar
        $stmt = $this->pdo->prepare(
            "INSERT INTO usuario_logro (id_usuario, id_logro) VALUES (?, ?)"
        );
        $stmt->execute([$this->idUsuario, $logro['id_logro']]);

        $nuevos[] = $clave;
    }

    /**
     * Obtiene todos los logros del usuario (desbloqueados y bloqueados)
     */
    public function obtenerTodos()
    {
        $stmt = $this->pdo->prepare(
            "SELECT l.*, 
                CASE WHEN ul.id_usuario_logro IS NOT NULL THEN 1 ELSE 0 END AS desbloqueado,
                ul.fecha_desbloqueo
             FROM logro l
             LEFT JOIN usuario_logro ul ON ul.id_logro = l.id_logro AND ul.id_usuario = ?
             ORDER BY desbloqueado DESC, l.categoria ASC, l.puntos ASC"
        );
        $stmt->execute([$this->idUsuario]);
        return $stmt->fetchAll();
    }

    /**
     * Resumen de logros para mostrar en el dashboard
     */
    public function resumen()
    {
        $todos = $this->obtenerTodos();
        $desbloqueados = array_filter($todos, fn($l) => $l['desbloqueado']);
        $totalPuntos = array_sum(array_column($desbloqueados, 'puntos'));

        return [
            'total' => count($todos),
            'desbloqueados' => count($desbloqueados),
            'puntos' => $totalPuntos,
            'logros' => $todos,
            'recientes' => array_slice(array_values($desbloqueados), 0, 3)
        ];
    }

    /**
    * Desbloquea el logro de premium
    */
    public function desbloquearPremium()
    {
        $nuevos = [];
        $this->desbloquear('usuario_premium', $nuevos);
    }

    // ==========================================
    // UTILIDADES
    // ==========================================

    private function contar($tabla)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM $tabla WHERE id_usuario = ?");
        $stmt->execute([$this->idUsuario]);
        return $stmt->fetch()['total'];
    }

    private function metasCumplidas()
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total FROM meta WHERE id_usuario = ? AND estado = 'completada'"
        );
        $stmt->execute([$this->idUsuario]);
        return $stmt->fetch()['total'];
    }

    private function deudasPagadas()
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS total FROM deuda WHERE id_usuario = ? AND estado = 'pagada'"
        );
        $stmt->execute([$this->idUsuario]);
        return $stmt->fetch()['total'];
    }

    /**
     * Calcula la racha de días consecutivos registrando transacciones
     */
    private function racha()
    {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT DATE(fecha) AS dia 
             FROM transaccion 
             WHERE id_usuario = ? 
             ORDER BY dia DESC"
        );
        $stmt->execute([$this->idUsuario]);
        $dias = $stmt->fetchAll();

        if (empty($dias)) return 0;

        $racha = 1;
        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);
        
        $ultimoDia = new DateTime($dias[0]['dia']);
        $ultimoDia->setTime(0, 0, 0);

        // La racha solo cuenta si el último registro fue hoy o ayer
        $diff = $hoy->diff($ultimoDia)->days;
        if ($diff > 1) return 0;

        for ($i = 1; $i < count($dias); $i++) {
            $actual = new DateTime($dias[$i - 1]['dia']);
            $anterior = new DateTime($dias[$i]['dia']);
            $actual->setTime(0, 0, 0);
            $anterior->setTime(0, 0, 0);

            $diferencia = $actual->diff($anterior)->days;

            if ($diferencia === 1) {
                $racha++;
            } else {
                break;
            }
        }

        return $racha;
    }
}