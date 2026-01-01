<?php
// database/connection.php
// Database connection manager

if (!function_exists('getPDOConnection')) {
    $GLOBALS['_pdo_conn'] = null;
    
    function getPDOConnection() {
        // Check if we already have a connection
        if (isset($GLOBALS['_pdo_conn']) && $GLOBALS['_pdo_conn'] instanceof PDO) {
            return $GLOBALS['_pdo_conn'];
        }
        
        $host = 'localhost';
        $username = 'root';
        $password = '';
        $dbname = 'kp_oli_portfolio';
        
        try {
            $dsn = "mysql:host=" . $host . ";dbname=" . $dbname . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $GLOBALS['_pdo_conn'] = new PDO($dsn, $username, $password, $options);
            return $GLOBALS['_pdo_conn'];
            
        } catch (PDOException $e) {
            error_log("PDO Connection Failed: " . $e->getMessage() . " | DSN: mysql:host=" . $host . ";dbname=" . $dbname);
            throw $e;
        }
    }
}

// Initialize connection globally for backward compatibility
try {
    $conn = getPDOConnection();
} catch (Exception $e) {
    // Connection will be attempted when needed
    $conn = null;
}

?>