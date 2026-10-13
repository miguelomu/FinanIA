<?php
/**
 * Modelo: Transaccion
 * 
 * Se encarga de todas las operaciones de base de datos
 * relacionadas con las transacciones.
 */

class Transaccion
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las transacciones de un usuario, con filtros opcionales
     */
    public function obtenerTodas($idUsuario, $tipo = '', $buscar = '')
    {
        $sql = "SELECT t.*, c.nombre AS categoria_nombre 
                FROM transaccion t
                INNER JOIN categoria c ON t.id_categoria = c.id_categoria
                WHERE t.id_usuario = ?";
        $params = [$idUsuario];

        if (in_array($tipo, ['ingreso', 'gasto'])) {
            $sql .= " AND t.tipo = ?";
            $params[] = $tipo;
        }

        if (!empty($buscar)) {
            $sql .= " AND (t.descripcion LIKE ? OR c.nombre LIKE ?)";
            $params[] = "%$buscar%";
            $params[] = "%$buscar%";
        }

        $sql .= " ORDER BY t.fecha DESC, t.id_transaccion DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una transacción por ID validando que pertenezca al usuario
     */
    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM transaccion WHERE id_transaccion = ? AND id_usuario = ?"
        );
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    /**
     * Verifica que una categoría pertenezca al usuario
     */
    public function categoriaEsDelUsuario($idCategoria, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_categoria FROM categoria WHERE id_categoria = ? AND id_usuario = ?"
        );
        $stmt->execute([$idCategoria, $idUsuario]);
        return $stmt->fetch() !== false;
    }

    /**
     * Crea una nueva transacción
     */
    public function crear($idUsuario, $idCategoria, $monto, $tipo, $descripcion, $fecha, $metodoPago, $recurrente)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO transaccion 
             (id_usuario, id_categoria, monto, tipo, descripcion, fecha, metodo_pago, recurrente) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $idUsuario, $idCategoria, $monto, $tipo,
            $descripcion, $fecha, $metodoPago, $recurrente
        ]);
    }

    /**
     * Actualiza una transacción
     */
    public function actualizar($id, $idUsuario, $idCategoria, $monto, $tipo, $descripcion, $fecha, $metodoPago, $recurrente)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE transaccion 
             SET tipo = ?, monto = ?, fecha = ?, id_categoria = ?, 
                 descripcion = ?, metodo_pago = ?, recurrente = ?
             WHERE id_transaccion = ? AND id_usuario = ?"
        );
        return $stmt->execute([
            $tipo, $monto, $fecha, $idCategoria,
            $descripcion, $metodoPago, $recurrente,
            $id, $idUsuario
        ]);
    }

    /**
     * Elimina una transacción
     */
    public function eliminar($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM transaccion WHERE id_transaccion = ? AND id_usuario = ?"
        );
        return $stmt->execute([$id, $idUsuario]);
    }

    /**
     * Calcula los totales de un conjunto de transacciones
     * 
     * @param array $transacciones
     * @return array ['ingresos' => float, 'gastos' => float, 'balance' => float]
     */
    public function calcularTotales($transacciones)
    {
        $ingresos = 0;
        $gastos = 0;

        foreach ($transacciones as $t) {
            if ($t['tipo'] === 'ingreso') {
                $ingresos += $t['monto'];
            } else {
                $gastos += $t['monto'];
            }
        }

        return [
            'ingresos' => $ingresos,
            'gastos' => $gastos,
            'balance' => $ingresos - $gastos
        ];
    }
}