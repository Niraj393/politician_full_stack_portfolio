<?php
// backend/api/get-achievements.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../database/connection.php';

try {
    $stmt = $conn->query("SELECT * FROM achievements ORDER BY display_order ASC, year DESC");
    $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'achievements' => $achievements
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch achievements'
    ]);
}