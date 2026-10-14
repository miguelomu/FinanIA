<?php
/**
 * Modelo: Transaccion
 */

class Transaccion
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las transacciones con su categoría y cuenta
     */
    public function obtenerTodas($idUsuario, $tipo = '', $buscar = '')
    {
        $sql = "SELECT t.*, c.nombre AS categoria_nombre, cu.nombre AS cuenta_nombre, cu.color AS cuenta_color
                FROM transaccion t
                INNER JOIN categoria c ON t.id_categoria = c.id_categoria
                LEFT JOIN cuenta cu ON t.id_cuenta = cu.id_cuenta
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

    public function obtenerPorId($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM transaccion WHERE id_transaccion = ? AND id_usuario = ?"
        );
        $stmt->execute([$id, $idUsuario]);
        return $stmt->fetch();
    }

    public function categoriaEsDelUsuario($idCategoria, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "SELECT id_categoria FROM categoria WHERE id_categoria = ? AND id_usuario = ?"
        );
        $stmt->execute([$idCategoria, $idUsuario]);
        return $stmt->fetch() !== false;
    }

    /**
     * Verifica que una cuenta pertenezca al usuario
     */
    public function cuentaEsDelUsuario($idCuenta, $idUsuario)
    {
        if (empty($idCuenta)) return true; // Permitir NULL

        $stmt = $this->pdo->prepare(
            "SELECT id_cuenta FROM cuenta WHERE id_cuenta = ? AND id_usuario = ?"
        );
        $stmt->execute([$idCuenta, $idUsuario]);
        return $stmt->fetch() !== false;
    }

    /**
     * Crea una nueva transacción (ahora con id_cuenta)
     */
    public function crear($idUsuario, $idCategoria, $idCuenta, $monto, $tipo, $descripcion, $fecha, $metodoPago, $recurrente)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO transaccion 
             (id_usuario, id_categoria, id_cuenta, monto, tipo, descripcion, fecha, metodo_pago, recurrente) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $idUsuario, $idCategoria, $idCuenta ?: null, $monto, $tipo,
            $descripcion, $fecha, $metodoPago, $recurrente
        ]);
    }

    /**
     * Actualiza una transacción
     */
    public function actualizar($id, $idUsuario, $idCategoria, $idCuenta, $monto, $tipo, $descripcion, $fecha, $metodoPago, $recurrente)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE transaccion 
             SET tipo = ?, monto = ?, fecha = ?, id_categoria = ?, id_cuenta = ?, 
                 descripcion = ?, metodo_pago = ?, recurrente = ?
             WHERE id_transaccion = ? AND id_usuario = ?"
        );
        return $stmt->execute([
            $tipo, $monto, $fecha, $idCategoria, $idCuenta ?: null,
            $descripcion, $metodoPago, $recurrente,
            $id, $idUsuario
        ]);
    }

    public function eliminar($id, $idUsuario)
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM transaccion WHERE id_transaccion = ? AND id_usuario = ?"
        );
        return $stmt->execute([$id, $idUsuario]);
    }

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