<?php
/**
 * Modelo: Presupuesto
 * 
 * Maneja las operaciones de base de datos de los presupuestos.
 */

class Presupuesto
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todos los presupuestos de un usuario con conteo de categorías
     */
    public function obtenerTodos($idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, 
             (SELECT COUNT(*) FROM detalle_presupuesto WHERE id_presupuesto = p.id_presupuesto) AS total_categorias
             FROM presupuesto p
             WHERE p.id_usuario = ?
             ORDER BY p.anio DESC, p.mes DESC"
        );
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un presupuesto por ID
     */
    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM presupuesto WHERE id_presupuesto = ? AND id_usuario = ?"
        );
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    /**
     * Verifica si ya existe un presupuesto para ese mes/año
     */
    public function existe($idUsuario, $mes, $anio, $excluirId = null)
    {
        $sql = "SELECT id_presupuesto FROM presupuesto WHERE id_usuario = ? AND mes = ? AND anio = ?";
        $params = [$idUsuario, $mes, $anio];

        if ($excluirId !== null) {
            $sql .= " AND id_presupuesto != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Crea un presupuesto con sus detalles
     * 
     * @param array $limites ['id_categoria' => limite, ...]
     */
    public function crear($idUsuario, $mes, $anio, $limites)
    {
        // Calcular total
        $montoTotal = 0;
        foreach ($limites as $limite) {
            $montoTotal += floatval($limite);
        }

        $this->pdo->beginTransaction();

        try {
            // Insertar presupuesto
            $stmt = $this->pdo->prepare(
                "INSERT INTO presupuesto (id_usuario, mes, anio, monto_total) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$idUsuario, $mes, $anio, $montoTotal]);
            $idPresupuesto = $this->pdo->lastInsertId();

            // Insertar detalles
            $stmt = $this->pdo->prepare(
                "INSERT INTO detalle_presupuesto (id_presupuesto, id_categoria, limite) VALUES (?, ?, ?)"
            );

            foreach ($limites as $idCategoria => $limite) {
                $limiteVal = floatval($limite);
                if ($limiteVal >= 0) {
                    $stmt->execute([$idPresupuesto, $idCategoria, $limiteVal]);
                }
            }

            $this->pdo->commit();
            return $idPresupuesto;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un presupuesto y sus detalles
     */
    public function actualizar($id, $mes, $anio, $limites)
    {
        $montoTotal = 0;
        foreach ($limites as $limite) {
            $montoTotal += floatval($limite);
        }

        $this->pdo->beginTransaction();

        try {
            // Actualizar presupuesto
            $stmt = $this->pdo->prepare(
                "UPDATE presupuesto SET mes = ?, anio = ?, monto_total = ? WHERE id_presupuesto = ?"
            );
            $stmt->execute([$mes, $anio, $montoTotal, $id]);

            // Eliminar detalles anteriores
            $stmt = $this->pdo->prepare("DELETE FROM detalle_presupuesto WHERE id_presupuesto = ?");
            $stmt->execute([$id]);

            // Insertar nuevos detalles
            $stmt = $this->pdo->prepare(
                "INSERT INTO detalle_presupuesto (id_presupuesto, id_categoria, limite) VALUES (?, ?, ?)"
            );

            foreach ($limites as $idCategoria => $limite) {
                $limiteVal = floatval($limite);
                if ($limiteVal >= 0) {
                    $stmt->execute([$id, $idCategoria, $limiteVal]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un presupuesto
     */
    public function eliminar($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM presupuesto WHERE id_presupuesto = ? AND id_usuario = ?"
        );
        return $stmt->execute([$id, $idUsuario]);
    }

    /**
     * Obtiene el detalle de un presupuesto con gasto real por categoría
     */
    public function obtenerDetalleConGasto($idPresupuesto, $idUsuario, $mes, $anio)
    {
        $stmt = $this->pdo->prepare(
            "SELECT dp.*, c.nombre AS categoria_nombre,
                COALESCE((
                    SELECT SUM(monto) FROM transaccion t
                    WHERE t.id_usuario = ?
                      AND t.id_categoria = dp.id_categoria
                      AND t.tipo = 'gasto'
                      AND MONTH(t.fecha) = ?
                      AND YEAR(t.fecha) = ?
                ), 0) AS gastado
             FROM detalle_presupuesto dp
             INNER JOIN categoria c ON dp.id_categoria = c.id_categoria
             WHERE dp.id_presupuesto = ?
             ORDER BY c.nombre"
        );
        $stmt->execute([$idUsuario, $mes, $anio, $idPresupuesto]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los límites actuales de un presupuesto (para editar)
     * 
     * @return array ['id_categoria' => limite]
     */
    public function obtenerLimitesActuales($idPresupuesto)
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_categoria, limite FROM detalle_presupuesto WHERE id_presupuesto = ?"
        );
        $stmt->execute([$idPresupuesto]);

        $limites = [];
        while ($row = $stmt->fetch()) {
            $limites[$row['id_categoria']] = $row['limite'];
        }
        return $limites;
    }

    /**
     * Calcula totales del presupuesto
     */
    public function calcularTotales($detalles)
    {
        $presupuestado = 0;
        $gastado = 0;

        foreach ($detalles as $d) {
            $presupuestado += $d['limite'];
            $gastado += $d['gastado'];
        }

        return [
            'presupuestado' => $presupuestado,
            'gastado' => $gastado,
            'disponible' => $presupuestado - $gastado,
            'progreso' => $presupuestado > 0 ? ($gastado / $presupuestado) * 100 : 0
        ];
    }
}