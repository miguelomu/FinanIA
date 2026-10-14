<?php
/**
 * Modelo: Configuracion
 * 
 * Lee y actualiza valores de configuración financiera.
 */

class Configuracion
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene un valor por su clave
     */
    public function obtener($clave, $default = 0)
    {
        $stmt = $this->pdo->prepare("SELECT valor FROM configuracion_financiera WHERE clave = ?");
        $stmt->execute([$clave]);
        $r = $stmt->fetch();
        return $r ? floatval($r['valor']) : $default;
    }

    /**
     * Obtiene todos los valores
     */
    public function obtenerTodos()
    {
        $stmt = $this->pdo->query("SELECT * FROM configuracion_financiera ORDER BY clave");
        return $stmt->fetchAll();
    }

    /**
     * Actualiza un valor
     */
    public function actualizar($clave, $valor)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO configuracion_financiera (clave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)"
        );
        return $stmt->execute([$clave, $valor]);
    }

    /**
     * Obtiene la tasa de usura actual
     */
    public function tasaUsura()
    {
        return $this->obtener('tasa_usura_ea', 28.79);
    }
}