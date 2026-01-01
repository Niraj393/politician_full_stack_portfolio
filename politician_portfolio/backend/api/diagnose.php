<?php
// Diagnostic script to check database connectivity
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Diagnostics</h1>";

// Check PHP version
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Check if PDO is available
echo "<p><strong>PDO Available:</strong> " . (extension_loaded('pdo') ? 'Yes ✓' : 'No ✗') . "</p>";
echo "<p><strong>PDO MySQL Available:</strong> " . (extension_loaded('pdo_mysql') ? 'Yes ✓' : 'No ✗') . "</p>";

// Try to connect to database
echo "<h2>Connection Test</h2>";

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'kp_oli_portfolio';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    echo "<p style='color: green;'><strong>✓ Database Connected Successfully</strong></p>";
    
    // Check if activities table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'activities'");
    $tableExists = $stmt->rowCount() > 0;
    echo "<p><strong>Activities Table Exists:</strong> " . ($tableExists ? 'Yes ✓' : 'No ✗') . "</p>";
    
    if ($tableExists) {
        // Count activities
        $stmt = $conn->query("SELECT COUNT(*) as count FROM activities");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>Total Activities in Database:</strong> " . $result['count'] . "</p>";
        
        // Show sample activity
        $stmt = $conn->query("SELECT id, title_en, status FROM activities LIMIT 1");
        $sample = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($sample) {
            echo "<p><strong>Sample Activity:</strong> " . $sample['title_en'] . " (ID: " . $sample['id'] . ", Status: " . $sample['status'] . ")</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>✗ Connection Failed</strong></p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Database:</strong> $dbname</p>";
    echo "<p><strong>Host:</strong> $host</p>";
    echo "<p><strong>User:</strong> $username</p>";
}

// Test API endpoint
echo "<h2>API Endpoint Test</h2>";
echo "<p>Testing: <code>/kp-oli-portfolio/backend/api/get-activities.php</code></p>";

$apiUrl = 'http://localhost/kp-oli-portfolio/backend/api/get-activities.php';
$response = @file_get_contents($apiUrl);

if ($response === false) {
    echo "<p style='color: red;'><strong>✗ Failed to reach API</strong></p>";
} else {
    echo "<p style='color: green;'><strong>✓ API Response Received</strong></p>";
    echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ddd; overflow-x: auto;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
}

?>
