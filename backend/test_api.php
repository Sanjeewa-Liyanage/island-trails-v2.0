<?php
// Simple API test endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once 'src/utils/imports.php';

try {
    // Get the posted data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Test the booking update
    $bookingApi = new BookingApi();
    
    // Test update
    $updateData = [
        'id' => $input['id'] ?? 1,
        'package_id' => $input['package_id'] ?? 1,
        'booking_date' => $input['booking_date'] ?? '2025-12-25 10:00:00',
        'status' => $input['status'] ?? 'pending'
    ];
    
    $result = $bookingApi->update($updateData);
    
    echo json_encode([
        'test' => 'booking_update',
        'input' => $updateData,
        'result' => $result,
        'headers' => function_exists('getallheaders') ? getallheaders() : 'getallheaders not available'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
