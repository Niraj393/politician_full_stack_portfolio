<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../database/connection.php';

try {
    $conn = getPDOConnection();
    $stmt = $conn->query("SELECT id, year, title_en, title_np, content_en, content_np FROM timeline_entries ORDER BY year DESC");
    $entries = $stmt->fetchAll();

    echo json_encode($entries);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load timeline: ' . $e->getMessage()]);
}
?>