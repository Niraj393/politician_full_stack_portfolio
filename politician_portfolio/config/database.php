<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kp_oli_portfolio');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Get database connection
 * @return mysqli
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if (!$conn) {
            die("Database connection failed: " . mysqli_connect_error());
        }
        
        mysqli_set_charset($conn, "utf8mb4");
    }
    
    return $conn;
}

/**
 * Close database connection
 * @param mysqli $conn
 */
function closeDBConnection($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}

/**
 * Execute query and return result
 * @param string $sql
 * @return mysqli_result|bool
 */
function executeQuery($sql) {
    $conn = getDBConnection();
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        error_log("Query failed: " . mysqli_error($conn) . " | SQL: " . $sql);
        return false;
    }
    
    return $result;
}

/**
 * Fetch single row
 * @param string $sql
 * @return array|null
 */
function fetchOne($sql) {
    $result = executeQuery($sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Fetch all rows
 * @param string $sql
 * @return array
 */
function fetchAll($sql) {
    $result = executeQuery($sql);
    $rows = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    
    return $rows;
}

// Optional: PDO connection if you prefer PDO
function getPDOConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
?>