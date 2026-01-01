<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow frontend access if needed

require_once '../database/connection.php'; // Adjust path if needed

try {
    $conn = getPDOConnection(); // Use PDO for consistency
    $stmt = $conn->query("SELECT id, image_url, category, title, description, title_np, description_np FROM gallery_images WHERE is_active = 1 ORDER BY uploaded_at DESC");
    $images = $stmt->fetchAll();

    echo json_encode($images);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load gallery: ' . $e->getMessage()]);
}
?>