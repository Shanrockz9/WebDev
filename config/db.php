<?php
// ==========================================================
// Database Configuration (PDO)
// S Parfum Luxury E-Commerce Midterm Project
// ==========================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'sparfum_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function get_db_connection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Graceful error display without exposing sensitive database credentials
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

