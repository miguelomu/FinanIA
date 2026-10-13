<?php
/**
 * Plantilla de configuración de base de datos
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo y renómbralo como "database.php"
 * 2. Ajusta los valores según tu entorno local
 * 3. NO subas el archivo "database.php" real al repositorio
 */

// Configuración del servidor de base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');        // XAMPP por defecto: 3306. Otros: 3307, 3308, 82, etc.
define('DB_USER', 'root');
define('DB_PASS', '');            // XAMPP por defecto: vacío
define('DB_NAME', 'gestor_financiero');

// Conexión PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>