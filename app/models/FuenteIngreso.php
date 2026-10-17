<?php
/**
 * Modelo: FuenteIngreso
 * 
 * Maneja las fuentes de ingreso (especialmente para ingresos irregulares).
 */

class FuenteIngreso
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerTodas($idUsuario, $soloActivas = false)
    {
        $sql = "SELECT f.*,
                (SELECT COALESCE(AVG(monto),0) FROM transaccion 
                 WHERE id_fuente_ingreso = f.id_fuente AND tipo = 'ingreso') AS promedio_real,
                (SELECT COUNT(*) FROM transaccion 
                 WHERE id_fuente_ingreso = f.id_fuente AND tipo = 'ingreso') AS total_ingresos
                FROM fuente_ingreso f
                WHERE f.id_usuario = ?";
        
        $params = [$idUsuario];

        if ($soloActivas) {
            $sql .= " AND f.activa = TRUE";
        }

        $sql .= " ORDER BY f.activa DESC, f.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM fuente_ingreso WHERE id_fuente = ? AND id_usuario = ?");
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    public function existeNombre($idUsuario, $nombre, $excluirId = null)
    {
        $sql = "SELECT id_fuente FROM fuente_ingreso WHERE id_usuario = ? AND nombre = ?";
        $params = [$idUsuario, $nombre];

        if ($excluirId !== null) {
            $sql .= " AND id_fuente != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    public function crear($idUsuario, $nombre, $tipo, $frecuencia, $montoPromedio, $color)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO fuente_ingreso 
             (id_usuario, nombre, tipo, frecuencia, monto_promedio, color) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$idUsuario, $nombre, $tipo, $frecuencia, $montoPromedio, $color]);
    }

    public function actualizar($id, $idUsuario, $nombre, $tipo, $frecuencia, $montoPromedio, $color, $activa)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE fuente_ingreso 
             SET nombre = ?, tipo = ?, frecuencia = ?, monto_promedio = ?, color = ?, activa = ?
             WHERE id_fuente = ? AND id_usuario = ?"
        );
        return $stmt->execute([
            $nombre, $tipo, $frecuencia, $montoPromedio, $color, $activa,
            $id, $idUsuario
        ]);
    }

    public function eliminar($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM fuente_ingreso WHERE id_fuente = ? AND id_usuario = ?"
        );
        return $stmt->execute([$id, $idUsuario]);
    }

    /**
     * Calcula estadísticas de estabilidad de ingresos
     * 
     * @return array con: promedio, minimo, maximo, desviacion, estabilidad (0-100)
     */
    public function calcularEstabilidad($idUsuario, $meses = 6)
    {
        $fechaDesde = date('Y-m-d', strtotime("-{$meses} months"));

        // Ingresos por mes
        $stmt = $this->pdo->prepare(
            "SELECT 
                DATE_FORMAT(fecha, '%Y-%m') AS mes,
                SUM(monto) AS total
             FROM transaccion 
             WHERE id_usuario = ? AND tipo = 'ingreso' AND fecha >= ?
             GROUP BY mes
             ORDER BY mes ASC"
        );
        $stmt->execute([$idUsuario, $fechaDesde]);
        $ingresosPorMes = $stmt->fetchAll();

        if (empty($ingresosPorMes)) {
            return [
                'promedio' => 0,
                'minimo' => 0,
                'maximo' => 0,
                'desviacion' => 0,
                'estabilidad' => 0,
                'meses_analizados' => 0,
                'datos_mensuales' => []
            ];
        }

        $totales = array_column($ingresosPorMes, 'total');
        $promedio = array_sum($totales) / count($totales);
        $minimo = min($totales);
        $maximo = max($totales);

        // Desviación estándar
        $sumaDiferencias = 0;
        foreach ($totales as $t) {
            $sumaDiferencias += pow($t - $promedio, 2);
        }
        $varianza = $sumaDiferencias / count($totales);
        $desviacion = sqrt($varianza);

        // Estabilidad: 100 si todos los meses iguales, 0 si muy variable
        // Fórmula: 100 - (desviación / promedio * 100), limitado entre 0 y 100
        $estabilidad = $promedio > 0 
            ? max(0, min(100, 100 - ($desviacion / $promedio * 100)))
            : 0;

        return [
            'promedio' => $promedio,
            'minimo' => $minimo,
            'maximo' => $maximo,
            'desviacion' => $desviacion,
            'estabilidad' => $estabilidad,
            'meses_analizados' => count($totales),
            'datos_mensuales' => $ingresosPorMes
        ];
    }
}