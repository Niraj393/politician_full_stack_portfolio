<?php
header('Content-Type: application/json');

try {
    require_once '../database/connection.php';
    
    $conn = getPDOConnection();
    
    // Test basic query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM activities");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'total_activities' => $result['count'],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>
