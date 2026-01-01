<?php
header('Content-Type: application/json');
require_once '../database/connection.php';

// Get language parameter
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';

try {
    $conn = getPDOConnection();
    
    if ($lang === 'np') {
        $query = "SELECT id, 
                  COALESCE(content_np, content_en, content) as content,
                  birth_date, education, constituency, political_career,
                  facts
                  FROM about WHERE id = 1";
    } else {
        $query = "SELECT id, 
                  COALESCE(content_en, content) as content,
                  birth_date, education, constituency, political_career,
                  facts
                  FROM about WHERE id = 1";
    }
    
    $stmt = $conn->query($query);
    $about = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($about) {
        echo json_encode($about);
    } else {
        echo json_encode(['error' => 'No about data found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>