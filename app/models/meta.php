<?php
/**
 * Modelo: Meta
 * 
 * Maneja las operaciones de base de datos de las metas de ahorro.
 */

class Meta
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las metas de un usuario, con filtro opcional por estado
     */
    public function obtenerTodas($idUsuario, $estado = '')
    {
        $sql = "SELECT * FROM meta WHERE id_usuario = ?";
        $params = [$idUsuario];

        if (in_array($estado, ['activa', 'completada', 'cancelada'])) {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }

        $sql .= " ORDER BY 
            CASE estado 
                WHEN 'activa' THEN 1 
                WHEN 'completada' THEN 2 
                ELSE 3 
            END, fecha_limite ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una meta por ID validando que pertenezca al usuario
     */
    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM meta WHERE id_meta = ? AND id_usuario = ?");
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    /**
     * Crea una nueva meta
     * 
     * El monto_actual se inicializa con el monto_inicial
     */
    public function crear($idUsuario, $nombre, $montoObjetivo, $montoInicial, $fechaInicio, $fechaLimite)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO meta 
             (id_usuario, nombre, monto_objetivo, monto_inicial, monto_actual, fecha_inicio, fecha_limite, estado) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 'activa')"
        );
        return $stmt->execute([
            $idUsuario, $nombre, $montoObjetivo, $montoInicial, $montoInicial,
            $fechaInicio, $fechaLimite
        ]);
    }

    /**
     * Actualiza una meta
     * 
     * Recalcula el monto_actual como: monto_inicial + SUM(aportes)
     */
    public function actualizar($id, $idUsuario, $nombre, $montoObjetivo, $montoInicial, $fechaInicio, $fechaLimite, $estado)
    {
        // Calcular total de aportes existentes
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(monto),0) AS total FROM aporte_meta WHERE id_meta = ?");
        $stmt->execute([$id]);
        $totalAportes = $stmt->fetch()['total'];

        $montoActual = $montoInicial + $totalAportes;

        if ($montoActual >= $montoObjetivo && $estado === 'activa') {
            $estado = 'completada';
        }

        $stmt = $this->pdo->prepare(
            "UPDATE meta 
             SET nombre = ?, monto_objetivo = ?, monto_inicial = ?, monto_actual = ?, 
                 fecha_inicio = ?, fecha_limite = ?, estado = ?
             WHERE id_meta = ? AND id_usuario = ?"
        );
        return $stmt->execute([
            $nombre, $montoObjetivo, $montoInicial, $montoActual,
            $fechaInicio, $fechaLimite, $estado,
            $id, $idUsuario
        ]);
    }

    /**
     * Elimina una meta
     */
    public function eliminar($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare("DELETE FROM meta WHERE id_meta = ? AND id_usuario = ?");
        return $stmt->execute([$id, $idUsuario]);
    }

    /**
     * Recalcula el monto_actual y el estado de una meta
     * 
     * Se llama después de crear, editar o eliminar un aporte.
     */
    public function recalcular($id)
    {
        // Obtener meta actual
        $stmt = $this->pdo->prepare("SELECT monto_inicial, monto_objetivo FROM meta WHERE id_meta = ?");
        $stmt->execute([$id]);
        $meta = $stmt->fetch();

        if (!$meta) return false;

        // Sumar aportes
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(monto),0) AS total FROM aporte_meta WHERE id_meta = ?");
        $stmt->execute([$id]);
        $totalAportes = $stmt->fetch()['total'];

        $montoActual = $meta['monto_inicial'] + $totalAportes;
        $estado = $montoActual >= $meta['monto_objetivo'] ? 'completada' : 'activa';

        $stmt = $this->pdo->prepare("UPDATE meta SET monto_actual = ?, estado = ? WHERE id_meta = ?");
        return $stmt->execute([$montoActual, $estado, $id]);
    }

    /**
     * Calcula totales para el resumen
     */
    public function calcularTotales($metas)
    {
        $totalObjetivo = 0;
        $totalAhorrado = 0;

        foreach ($metas as $m) {
            $totalObjetivo += $m['monto_objetivo'];
            $totalAhorrado += $m['monto_actual'];
        }

        return [
            'objetivo' => $totalObjetivo,
            'ahorrado' => $totalAhorrado,
            'falta' => $totalObjetivo - $totalAhorrado,
            'progreso' => $totalObjetivo > 0 ? ($totalAhorrado / $totalObjetivo) * 100 : 0
        ];
    }
}