<?php
/**
 * Modelo: GastoRecurrente
 * 
 * Agrupa los gastos marcados como recurrentes.
 */

class GastoRecurrente
{
    private $pdo;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->idUsuario = $idUsuario;
    }

    /**
     * Obtiene todos los gastos recurrentes agrupados por categoría
     */
    public function obtenerTodos()
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                t.id_transaccion,
                t.descripcion,
                t.monto,
                t.fecha,
                t.tipo,
                c.nombre AS categoria_nombre,
                c.color AS categoria_color,
                cu.nombre AS cuenta_nombre
             FROM transaccion t
             LEFT JOIN categoria c ON t.id_categoria = c.id_categoria
             LEFT JOIN cuenta cu ON t.id_cuenta = cu.id_cuenta
             WHERE t.id_usuario = ? AND t.recurrente = TRUE
             ORDER BY t.tipo DESC, c.nombre ASC, t.monto DESC"
        );
        $stmt->execute([$this->idUsuario]);
        return $stmt->fetchAll();
    }

    /**
     * Calcula el impacto mensual y anual
     */
    public function calcularImpacto()
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                COALESCE(SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END),0) AS gastos,
                COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END),0) AS ingresos,
                COUNT(*) AS total
             FROM transaccion 
             WHERE id_usuario = ? AND recurrente = TRUE"
        );
        $stmt->execute([$this->idUsuario]);
        $r = $stmt->fetch();

        return [
            'gastos_mensual' => $r['gastos'],
            'gastos_anual' => $r['gastos'] * 12,
            'ingresos_mensual' => $r['ingresos'],
            'ingresos_anual' => $r['ingresos'] * 12,
            'total' => $r['total'],
            'balance_mensual' => $r['ingresos'] - $r['gastos']
        ];
    }

    /**
     * Detecta posibles suscripciones (gastos pequeños repetidos)
     */
    public function detectarSuscripciones()
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                t.descripcion,
                COUNT(*) AS veces,
                AVG(t.monto) AS monto_promedio,
                SUM(t.monto) AS monto_total,
                MIN(t.fecha) AS primera_vez,
                MAX(t.fecha) AS ultima_vez,
                c.nombre AS categoria
             FROM transaccion t
             LEFT JOIN categoria c ON t.id_categoria = c.id_categoria
             WHERE t.id_usuario = ? 
               AND t.tipo = 'gasto'
               AND t.monto BETWEEN 5000 AND 100000
               AND t.fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY t.descripcion, c.nombre
             HAVING veces >= 3
             ORDER BY monto_total DESC
             LIMIT 15"
        );
        $stmt->execute([$this->idUsuario]);
        return $stmt->fetchAll();
    }
}