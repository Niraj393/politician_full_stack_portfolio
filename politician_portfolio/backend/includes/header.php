<?php
session_start();

// Simple authentication check
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
    header('Location: login.php');
    exit();
}

// Simple database connection for dashboard
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'kp_oli_portfolio';

$conn = mysqli_connect($host, $user, $pass, $dbname);

// Get counts for dashboard
$counts = [
    'messages' => 0,
    'gallery' => 0,
    'activities' => 0,
    'unread' => 0
    
];

if ($conn) {
    // Get message count
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $counts['messages'] = $row['count'] ?? 0;
    }
    
    // Get gallery count
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM gallery");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $counts['gallery'] = $row['count'] ?? 0;
    }
    
    // Get unread count
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $counts['unread'] = $row['count'] ?? 0;
    }
    
    mysqli_close($conn);
}
?>