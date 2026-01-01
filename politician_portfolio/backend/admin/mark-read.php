<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit();
}

require_once __DIR__ . '/../database/connection.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $conn = getPDOConnection();
        $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
        echo 'success';
    } catch (PDOException $e) {
        http_response_code(500);
        echo 'error';
    }
}
?>