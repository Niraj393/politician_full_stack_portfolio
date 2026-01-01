<?php
// backend/api/get-videos.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../database/connection.php';

try {
    $stmt = $conn->query("SELECT * FROM videos ORDER BY uploaded_at DESC");
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Normalize thumbnail paths so frontend can directly use them
    foreach ($videos as &$v) {
        if (!empty($v['thumbnail_url'])) {
            $thumb = $v['thumbnail_url'];
            // If already a URL or absolute path, leave it
            if (strpos($thumb, 'http://') === 0 || strpos($thumb, 'https://') === 0 || strpos($thumb, '/') === 0) {
                $v['thumbnail_url'] = $thumb;
            } else {
                // Thumbnails are stored under backend/uploads/videos/ by admin area
                $v['thumbnail_url'] = 'backend/uploads/videos/' . ltrim($thumb, '/');
            }
        }

        // Ensure category is always present (frontend can rely on this)
        if (empty($v['category'])) {
            $v['category'] = 'speeches';
        }
    }

    echo json_encode([
        'success' => true,
        'videos' => $videos
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch videos'
    ]);
}