<?php
require_once '../config/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

if (!isAdminLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $conn = getDBConnection();
    
    // Upload image
    $upload_dir = '../../uploads/gallery/';
    $result = uploadImage($_FILES['image'], $upload_dir);
    
    if ($result['success']) {
        $image_path = 'uploads/gallery/' . $result['filename'];
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $caption_en = mysqli_real_escape_string($conn, $_POST['caption_en'] ?? '');
        $caption_np = mysqli_real_escape_string($conn, $_POST['caption_np'] ?? '');
        
        // Insert into database
        $sql = "INSERT INTO gallery (image_path, category, caption_en, caption_np) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssss', $image_path, $category, $caption_en, $caption_np);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            unlink($upload_dir . $result['filename']); // Delete uploaded file if DB insert fails
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
    
    closeDBConnection($conn);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>