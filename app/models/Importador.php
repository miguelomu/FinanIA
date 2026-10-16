<?php
/**
 * Modelo: Importador
 * 
 * Procesa archivos CSV de extractos bancarios.
 */

class Importador
{
    private $pdo;
    private $idUsuario;

    public function __construct($pdo, $idUsuario)
    {
        $this->pdo = $pdo;
        $this->idUsuario = $idUsuario;
    }

    /**
     * Lee un archivo CSV y devuelve las filas detectadas.
     * 
     * @param string $rutaArchivo Ruta absoluta del archivo
     * @return array ['columnas' => [...], 'filas' => [...], 'delimitador' => ',']
     */
    public function leerCSV($rutaArchivo)
    {
        if (!file_exists($rutaArchivo)) {
            throw new Exception('Archivo no encontrado');
        }

        // Detectar delimitador
        $delimitador = $this->detectarDelimitador($rutaArchivo);

        // Leer archivo
        $handle = fopen($rutaArchivo, 'r');
        if (!$handle) {
            throw new Exception('No se pudo abrir el archivo');
        }

        $filas = [];
        $columnas = [];
        $encabezado = true;
        $linea = 0;

        while (($datos = fgetcsv($handle, 0, $delimitador)) !== false) {
            $linea++;
            if ($linea > 500) break; // Límite de seguridad

            if ($encabezado) {
                $columnas = array_map('trim', $datos);
                $encabezado = false;
            } else {
                if (count($datos) === count($columnas)) {
                    $fila = [];
                    foreach ($columnas as $i => $col) {
                        $fila[$col] = trim($datos[$i] ?? '');
                    }
                    $filas[] = $fila;
                }
            }
        }

        fclose($handle);

        return [
            'columnas' => $columnas,
            'filas' => $filas,
            'delimitador' => $delimitador,
            'total' => count($filas)
        ];
    }

    /**
     * Detecta el delimitador del CSV (coma, punto y coma, tab)
     */
    private function detectarDelimitador($rutaArchivo)
    {
        $handle = fopen($rutaArchivo, 'r');
        $primeraLinea = fgets($handle);
        fclose($handle);

        $comas = substr_count($primeraLinea, ',');
        $puntoComas = substr_count($primeraLinea, ';');
        $tabs = substr_count($primeraLinea, "\t");

        if ($puntoComas > $comas && $puntoComas > $tabs) return ';';
        if ($tabs > $comas && $tabs > $puntoComas) return "\t";
        return ',';
    }

    /**
     * Detecta automáticamente cuáles columnas son fecha, monto y descripción
     * 
     * @param array $columnas
     * @param array $filas
     * @return array ['fecha' => 'nombre_col', 'monto' => 'nombre_col', 'descripcion' => 'nombre_col']
     */
    public function detectarColumnas($columnas, $filas)
    {
        $resultado = ['fecha' => null, 'monto' => null, 'descripcion' => null];

        $patronesFecha = ['fecha', 'date', 'día', 'dia', 'fec'];
        $patronesMonto = ['monto', 'valor', 'amount', 'importe', 'debito', 'débito', 'credito', 'crédito'];
        $patronesDesc = ['descripcion', 'descripción', 'concepto', 'detalle', 'description', 'referencia'];

        foreach ($columnas as $col) {
            $colLower = mb_strtolower($col, 'UTF-8');
            
            if (!$resultado['fecha'] && $this->coincideCon($colLower, $patronesFecha)) {
                $resultado['fecha'] = $col;
            } elseif (!$resultado['monto'] && $this->coincideCon($colLower, $patronesMonto)) {
                $resultado['monto'] = $col;
            } elseif (!$resultado['descripcion'] && $this->coincideCon($colLower, $patronesDesc)) {
                $resultado['descripcion'] = $col;
            }
        }

        // Fallback: si no detecta, probar por contenido de la primera fila
        if (empty($filas)) return $resultado;
        $primeraFila = $filas[0];

        foreach ($columnas as $col) {
            if (!$resultado['fecha'] && $this->pareceFecha($primeraFila[$col] ?? '')) {
                $resultado['fecha'] = $col;
            }
            if (!$resultado['monto'] && $this->pareceMonto($primeraFila[$col] ?? '')) {
                $resultado['monto'] = $col;
            }
        }

        // Descripción: la columna más larga en texto
        if (!$resultado['descripcion']) {
            $mejor = '';
            $mejorLargo = 0;
            foreach ($columnas as $col) {
                if ($col === $resultado['fecha'] || $col === $resultado['monto']) continue;
                $largo = mb_strlen($primeraFila[$col] ?? '');
                if ($largo > $mejorLargo) {
                    $mejorLargo = $largo;
                    $mejor = $col;
                }
            }
            $resultado['descripcion'] = $mejor;
        }

        return $resultado;
    }

    private function coincideCon($texto, $patrones)
    {
        foreach ($patrones as $p) {
            if (mb_strpos($texto, $p) !== false) return true;
        }
        return false;
    }

    private function pareceFecha($valor)
    {
        // Formatos: 15/10/2026, 2026-10-15, 15-10-2026
        return preg_match('/\d{1,4}[\/\-]\d{1,2}[\/\-]\d{1,4}/', $valor);
    }

    private function pareceMonto($valor)
    {
        // Quitar símbolos de moneda y espacios
        $limpio = str_replace(['$', ' ', '.'], '', $valor);
        $limpio = str_replace(',', '.', $limpio);
        return is_numeric($limpio);
    }

    /**
     * Normaliza un monto del CSV a float
     */
    public function normalizarMonto($valor)
    {
        $valor = str_replace(['$', ' ', 'COP'], '', $valor);
        
        // Formato colombiano: 1.500,50 → 1500.50
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $valor)) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } else {
            $valor = str_replace(',', '', $valor);
        }

        return floatval($valor);
    }

    /**
     * Normaliza una fecha del CSV a formato Y-m-d
     */
    public function normalizarFecha($valor)
    {
        $valor = trim($valor);
        
        // Formato YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $valor)) {
            return substr($valor, 0, 10);
        }

        // Formato DD/MM/YYYY o DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/', $valor, $m)) {
            $dia = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mes = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $anio = strlen($m[3]) === 2 ? '20' . $m[3] : $m[3];
            return "$anio-$mes-$dia";
        }

        return date('Y-m-d');
    }

    /**
     * Sugiere una categoría basándose en la descripción
     * 
     * @return int|null ID de la categoría sugerida
     */
    public function sugerirCategoria($descripcion, $tipo)
    {
        if (empty($descripcion)) return null;

        $descripcion = mb_strtolower($descripcion, 'UTF-8');

        $palabrasClave = [
            'comida' => ['exito', 'éxito', 'carulla', 'olimpica', 'olímpica', 'd1', 'ara', 'jumbo', 'supermercado', 'restaurante', 'rappi', 'domicilio', 'panaderia', 'cafe', 'almuerzo', 'mcdonalds', 'burger', 'pizza', 'kfc'],
            'transporte' => ['uber', 'didi', 'taxi', 'cabify', 'peaje', 'gasolina', 'terpel', 'ecopetrol', 'primax', 'transmilenio', 'metro', 'bus'],
            'entretenimiento' => ['netflix', 'spotify', 'youtube', 'cine', 'cinemark', 'royal films', 'juego', 'steam', 'playstation', 'hbo', 'disney', 'prime'],
            'servicios' => ['claro', 'movistar', 'tigo', 'etb', 'epm', 'codensa', 'enel', 'gas', 'acueducto', 'internet', 'energia', 'agua'],
            'salud' => ['farmacia', 'drogueria', 'cruz verde', 'farmatodo', 'eps', 'medico', 'clinica', 'hospital', 'laboratorio'],
            'educacion' => ['universidad', 'colegio', 'curso', 'udemy', 'coursera', 'platzi', 'libro', 'libreria'],
            'salario' => ['salario', 'sueldo', 'nomina', 'nómina', 'pago empresa'],
            'freelance' => ['freelance', 'honorarios', 'consultoria', 'consultoría']
        ];

        // Buscar categorías del usuario
        $stmt = $this->pdo->prepare("SELECT id_categoria, nombre FROM categoria WHERE id_usuario = ? AND tipo = ?");
        $stmt->execute([$this->idUsuario, $tipo]);
        $categorias = $stmt->fetchAll();

        foreach ($palabrasClave as $nombreCat => $palabras) {
            foreach ($palabras as $palabra) {
                if (mb_strpos($descripcion, $palabra) !== false) {
                    // Buscar una categoría que coincida
                    foreach ($categorias as $cat) {
                        if (mb_stripos($cat['nombre'], $nombreCat) !== false) {
                            return $cat['id_categoria'];
                        }
                    }
                }
            }
        }

        // Si no hay coincidencia, buscar categoría "Otros"
        foreach ($categorias as $cat) {
            if (mb_stripos($cat['nombre'], 'otro') !== false) {
                return $cat['id_categoria'];
            }
        }

        // Última opción: la primera categoría del tipo
        return $categorias[0]['id_categoria'] ?? null;
    }

    /**
     * Importa las transacciones del CSV a la BD
     * 
     * @param array $filas
     * @param array $mapeo ['fecha' => 'col', 'monto' => 'col', 'descripcion' => 'col']
     * @param int $idCuenta
     * @return array ['importadas' => int, 'errores' => array]
     */
    public function importar($filas, $mapeo, $idCuenta)
    {
        $importadas = 0;
        $errores = [];

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO transaccion 
                 (id_usuario, id_categoria, id_cuenta, monto, tipo, descripcion, fecha, metodo_pago, recurrente)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'cuenta', FALSE)"
            );

            foreach ($filas as $i => $fila) {
                $fecha = $this->normalizarFecha($fila[$mapeo['fecha']] ?? '');
                $montoRaw = $this->normalizarMonto($fila[$mapeo['monto']] ?? '0');
                $descripcion = trim($fila[$mapeo['descripcion']] ?? '');

                if ($montoRaw == 0) {
                    $errores[] = "Fila " . ($i + 1) . ": monto inválido";
                    continue;
                }

                $tipo = $montoRaw > 0 ? 'ingreso' : 'gasto';
                $monto = abs($montoRaw);

                $idCategoria = $this->sugerirCategoria($descripcion, $tipo);
                if (!$idCategoria) {
                    $errores[] = "Fila " . ($i + 1) . ": no hay categorías disponibles";
                    continue;
                }

                $stmt->execute([
                    $this->idUsuario,
                    $idCategoria,
                    $idCuenta,
                    $monto,
                    $tipo,
                    mb_substr($descripcion, 0, 255),
                    $fecha
                ]);

                $importadas++;
            }

            $this->pdo->commit();

            return [
                'importadas' => $importadas,
                'errores' => $errores
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}