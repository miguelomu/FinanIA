<?php
/**
 * Modelo: Reporte
 * 
 * Genera reportes basados en los datos existentes.
 * Solo lectura, no modifica datos.
 */

class Reporte
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Resumen general en un rango de fechas
     */
    public function resumenGeneral($idUsuario, $fechaDesde, $fechaHasta)
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END),0) AS total_ingresos,
                COALESCE(SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END),0) AS total_gastos,
                COUNT(*) AS total_transacciones
             FROM transaccion 
             WHERE id_usuario = ? AND fecha BETWEEN ? AND ?"
        );
        $stmt->execute([$idUsuario, $fechaDesde, $fechaHasta]);
        $r = $stmt->fetch();

        $r['balance'] = $r['total_ingresos'] - $r['total_gastos'];
        $r['tasa_ahorro'] = $r['total_ingresos'] > 0 
            ? ($r['balance'] / $r['total_ingresos']) * 100 
            : 0;

        return $r;
    }

    /**
     * Ingresos y gastos por mes
     */
    public function mensual($idUsuario, $fechaDesde, $fechaHasta)
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                DATE_FORMAT(fecha, '%Y-%m') AS periodo,
                DATE_FORMAT(fecha, '%b %Y') AS mes_nombre,
                COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END),0) AS ingresos,
                COALESCE(SUM(CASE WHEN tipo='gasto' THEN monto ELSE 0 END),0) AS gastos
             FROM transaccion 
             WHERE id_usuario = ? AND fecha BETWEEN ? AND ?
             GROUP BY periodo, mes_nombre
             ORDER BY periodo ASC"
        );
        $stmt->execute([$idUsuario, $fechaDesde, $fechaHasta]);
        return $stmt->fetchAll();
    }

    /**
     * Gastos agrupados por categoría
     */
    public function gastosPorCategoria($idUsuario, $fechaDesde, $fechaHasta)
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                c.nombre AS categoria,
                c.color,
                COALESCE(SUM(t.monto),0) AS total
             FROM categoria c
             INNER JOIN transaccion t ON c.id_categoria = t.id_categoria
             WHERE t.id_usuario = ? 
               AND t.tipo = 'gasto'
               AND t.fecha BETWEEN ? AND ?
             GROUP BY c.id_categoria, c.nombre, c.color
             HAVING total > 0
             ORDER BY total DESC"
        );
        $stmt->execute([$idUsuario, $fechaDesde, $fechaHasta]);
        return $stmt->fetchAll();
    }

    /**
     * Ingresos agrupados por categoría
     */
    public function ingresosPorCategoria($idUsuario, $fechaDesde, $fechaHasta)
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                c.nombre AS categoria,
                c.color,
                COALESCE(SUM(t.monto),0) AS total
             FROM categoria c
             INNER JOIN transaccion t ON c.id_categoria = t.id_categoria
             WHERE t.id_usuario = ? 
               AND t.tipo = 'ingreso'
               AND t.fecha BETWEEN ? AND ?
             GROUP BY c.id_categoria, c.nombre, c.color
             HAVING total > 0
             ORDER BY total DESC"
        );
        $stmt->execute([$idUsuario, $fechaDesde, $fechaHasta]);
        return $stmt->fetchAll();
    }

    /**
     * Top N de gastos más altos
     */
    public function topGastos($idUsuario, $fechaDesde, $fechaHasta, $limite = 5)
    {
        $stmt = $this->pdo->prepare(
            "SELECT 
                t.monto, t.descripcion, t.fecha, c.nombre AS categoria
             FROM transaccion t
             INNER JOIN categoria c ON t.id_categoria = c.id_categoria
             WHERE t.id_usuario = ? AND t.tipo = 'gasto' AND t.fecha BETWEEN ? AND ?
             ORDER BY t.monto DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(2, $fechaDesde);
        $stmt->bindValue(3, $fechaHasta);
        $stmt->bindValue(4, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}