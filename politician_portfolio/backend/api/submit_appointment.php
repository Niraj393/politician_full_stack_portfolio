<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin if needed
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../database/connection.php'; // Adjust path if needed

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $conn = getPDOConnection();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }
    
    // Validate required fields
    $required_fields = ['full_name', 'mobile_number', 'purpose'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Validate mobile number (Nepali format)
    if (!preg_match('/^[9][6-8][0-9]{7}$/', $input['mobile_number'])) {
        throw new Exception('Invalid mobile number format');
    }
    
    // Validate date if provided
    if (!empty($input['preferred_date'])) {
        $date = DateTime::createFromFormat('Y-m-d', $input['preferred_date']);
        if (!$date || $date->format('Y-m-d') !== $input['preferred_date']) {
            throw new Exception('Invalid date format');
        }
        if ($date < new DateTime()) {
            throw new Exception('Preferred date cannot be in the past');
        }
    }
    
    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO appointments (full_name, mobile_number, address, purpose, preferred_date, message, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([
        trim($input['full_name']),
        trim($input['mobile_number']),
        trim($input['address'] ?? ''),
        $input['purpose'],
        $input['preferred_date'] ?: null,
        trim($input['message'] ?? '')
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Appointment submitted successfully',
        'appointment_id' => $conn->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log('Appointment submission error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>