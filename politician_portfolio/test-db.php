<?php
// Quick test to see what's happening
try {
    $conn = new PDO('mysql:host=localhost;dbname=kp_oli_portfolio', 'root', '');
    $stmt = $conn->query('SELECT COUNT(*) as cnt FROM activities');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'activities_count' => $row['cnt'],
        'message' => 'Database connected successfully'
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Failed to connect to database'
    ]);
}
?>
