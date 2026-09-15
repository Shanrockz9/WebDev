<?php
// ==========================================================
// S PARFUM - DATABASE CONFIGURATION (config/db.php)
// Purpose: Establishes a secure PDO connection to the MySQL
// database using a reusable singleton pattern.
// ==========================================================

// Database connection credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'sparfum_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * get_db_connection()
 * Returns an active PDO instance. Uses a static variable ($pdo)
 * so only one connection is opened per page load (Singleton Pattern).
 */
function get_db_connection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        // DSN (Data Source Name) specifying driver, host, database name, and charset
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        // PDO security & behavior options:
        // - ERRMODE_EXCEPTION: Throws exceptions if a query has an error
        // - FETCH_ASSOC: Fetches results as associative arrays (e.g., $row['name'])
        // - EMULATE_PREPARES => false: Forces native prepared statements to prevent SQL injection
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Logs technical error message to server log without showing database credentials to users
            error_log("Database connection error: " . $e->getMessage());
            die("
                <div style='font-family: sans-serif; text-align: center; padding: 50px;'>
                    <h2 style='color: #C39468;'>S Parfum</h2>
                    <p style='color: #666;'>We are currently unable to connect to the database. Please ensure your MySQL server is running.</p>
                </div>
            ");
        }
    }

    return $pdo;
}

