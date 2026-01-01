<?php
// backend/api/get-activities.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../database/connection.php';

$response = [
    'success' => false,
    'message' => '',
    'activities' => [],
    'stats' => [
        'total' => 0,
        'upcoming' => 0,
        'ongoing' => 0,
        'completed' => 0,
        'cancelled' => 0,
        'this_month' => 0
    ]
];

try {
    $conn = getPDOConnection();
    
    // Get current date
    $currentDate = date('Y-m-d');
    $currentMonthStart = date('Y-m-01');
    $currentMonthEnd = date('Y-m-t');
    
    // Get filter parameters
    $category = isset($_GET['category']) ? $_GET['category'] : 'all';
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
    
    // Build WHERE clause
    $whereClauses = [];
    $params = [];
    
    if ($category !== 'all') {
        $whereClauses[] = "category = :category";
        $params[':category'] = $category;
    }
    
    if ($status !== 'all') {
        $whereClauses[] = "status = :status";
        $params[':status'] = $status;
    }
    
    $whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
    
    // Get activities
    $query = "
        SELECT 
            id,
            title_en,
            title_np,
            description_en,
            description_np,
            category,
            DATE_FORMAT(activity_date, '%Y-%m-%d') as date,
            DATE_FORMAT(activity_date, '%M %d, %Y') as formatted_date,
            TIME_FORMAT(start_time, '%h:%i %p') as start_time,
            TIME_FORMAT(end_time, '%h:%i %p') as end_time,
            location,
            location_map,
            priority,
            status,
            IFNULL(image_url, 'https://via.placeholder.com/800x400?text=No+Image') as image_url,
            organizer,
            attendee_count,
            created_at
        FROM activities 
        $whereSQL 
        ORDER BY 
            CASE status 
                WHEN 'ongoing' THEN 1
                WHEN 'upcoming' THEN 2
                WHEN 'completed' THEN 3
                WHEN 'cancelled' THEN 4
            END,
            priority DESC,
            activity_date ASC,
            start_time ASC
    ";
    
    if ($limit > 0) {
        $query .= " LIMIT :limit";
        $params[':limit'] = $limit;
    }
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        if ($key === ':limit') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->execute();
    $activitiesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $activities = [];
    foreach ($activitiesData as $row) {
        // Determine status color and icon
        $statusConfig = [
            'upcoming' => ['color' => '#3b82f6', 'icon' => 'fa-calendar-alt', 'label_en' => 'Upcoming', 'label_np' => 'आगामी'],
            'ongoing' => ['color' => '#10b981', 'icon' => 'fa-spinner', 'label_en' => 'Ongoing', 'label_np' => 'चलिरहेको'],
            'completed' => ['color' => '#6b7280', 'icon' => 'fa-check-circle', 'label_en' => 'Completed', 'label_np' => 'सम्पन्न'],
            'cancelled' => ['color' => '#ef4444', 'icon' => 'fa-times-circle', 'label_en' => 'Cancelled', 'label_np' => 'रद्द']
        ];
        
        // Get category icon
        $categoryIcons = [
            'public_event' => 'fa-users',
            'meeting' => 'fa-handshake',
            'conference' => 'fa-comments',
            'inauguration' => 'fa-ribbon',
            'health_camp' => 'fa-heartbeat',
            'party_meeting' => 'fa-landmark',
            'development' => 'fa-seedling',
            'media' => 'fa-tv',
            'general' => 'fa-calendar'
        ];
        
        // Calculate if event is today
        $isToday = ($row['date'] == $currentDate);
        $isUpcoming = ($row['date'] > $currentDate && $row['status'] == 'upcoming');
        $isPast = ($row['date'] < $currentDate);
        
        $activities[] = [
            'id' => $row['id'],
            'title_en' => $row['title_en'],
            'title_np' => $row['title_np'],
            'description_en' => $row['description_en'],
            'description_np' => $row['description_np'],
            'category' => $row['category'],
            'category_icon' => $categoryIcons[$row['category']] ?? 'fa-calendar',
            'date' => $row['date'],
            'formatted_date' => $row['formatted_date'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'location' => $row['location'],
            'location_map' => $row['location_map'],
            'priority' => $row['priority'],
            'status' => $row['status'],
            'status_color' => $statusConfig[$row['status']]['color'],
            'status_icon' => $statusConfig[$row['status']]['icon'],
            'status_label_en' => $statusConfig[$row['status']]['label_en'],
            'status_label_np' => $statusConfig[$row['status']]['label_np'],
            'image_url' => $row['image_url'],
            'organizer' => $row['organizer'],
            'attendee_count' => $row['attendee_count'],
            'is_today' => $isToday,
            'is_upcoming' => $isUpcoming,
            'is_past' => $isPast,
            'created_at' => $row['created_at']
        ];
    }
    
    // Get statistics
    $statsQuery = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'upcoming' THEN 1 ELSE 0 END) as upcoming,
            SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as ongoing,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN activity_date BETWEEN :monthStart AND :monthEnd THEN 1 ELSE 0 END) as this_month
        FROM activities
    ";
    
    $stmt = $conn->prepare($statsQuery);
    $stmt->bindValue(':monthStart', $currentMonthStart);
    $stmt->bindValue(':monthEnd', $currentMonthEnd);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['activities'] = $activities;
    $response['stats'] = $stats;
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Activities API Error: ' . $e->getMessage());
}

echo json_encode($response);
?>