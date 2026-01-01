<?php
// backend/api/donation-submit.php
header('Content-Type: application/json');
require_once '../database/connection.php'; // Adjust path to your DB connection file

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => ''];

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // Get form data
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $donorName = isset($_POST['donor_name']) ? trim($_POST['donor_name']) : null;
    $donorEmail = isset($_POST['donor_email']) ? trim($_POST['donor_email']) : null;
    $donorPhone = isset($_POST['donor_phone']) ? trim($_POST['donor_phone']) : null;

    // Validate amount
    if ($amount < 100) {
        throw new Exception('Minimum donation amount is NPR 100.');
    }

    // Validate and handle file upload
    if (!isset($_FILES['screenshot']) || $_FILES['screenshot']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Screenshot upload failed. Please select a valid image.');
    }

    $file = $_FILES['screenshot'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, or WebP allowed.');
    }

    if ($file['size'] > $maxSize) {
        throw new Exception('File size must be less than 5MB.');
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = '../uploads/screenshots/'; // Relative to this file
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueFilename = 'donation_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
    $filePath = $uploadDir . $uniqueFilename;

    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Failed to save screenshot.');
    }

    // Generate transaction ID
    $transactionId = 'TXN' . time() . rand(1000, 9999);

    // Insert into database
    $stmt = $conn->prepare("
        INSERT INTO donations (transaction_id, donor_name, donor_email, amount, screenshot_path, status, phone, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())
    ");
    $stmt->execute([$transactionId, $donorName, $donorEmail, $amount, $filePath, $donorPhone]);

    $response['success'] = true;
    $response['message'] = 'Donation submitted successfully!';
    $response['transaction_id'] = $transactionId;

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Donation submission error: ' . $e->getMessage());
} finally {
    echo json_encode($response);
}
?>