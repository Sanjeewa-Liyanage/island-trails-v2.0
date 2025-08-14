<?php
// Test script for booking update API
header('Content-Type: application/json');
require_once 'src/utils/imports.php';

// Test 1: Create a test user and get token
echo "=== CREATING TEST USER ===\n";

// Create test user data
$testUser = [
    'name' => 'Test Customer',
    'email' => 'test@customer.com',
    'password' => 'test123',
    'role' => 'customer'
];

$userApi = new UserApi();
$signupResult = $userApi->signup($testUser);
echo "Signup result: " . json_encode($signupResult) . "\n\n";

// Login to get token
echo "=== LOGGING IN ===\n";
$loginData = [
    'email' => 'test@customer.com',
    'password' => 'test123'
];
$loginResult = $userApi->login($loginData);
echo "Login result: " . json_encode($loginResult) . "\n\n";

if ($loginResult['status'] === 'success') {
    $token = $loginResult['token'];
    echo "Token obtained: " . substr($token, 0, 50) . "...\n\n";
    
    // Test 2: Create a test booking
    echo "=== CREATING TEST BOOKING ===\n";
    
    // Set the Authorization header for the booking API
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    
    $bookingApi = new BookingApi();
    $bookingData = [
        'package_id' => 1,
        'booking_date' => '2025-12-25 10:00:00'
    ];
    
    $createResult = $bookingApi->create($bookingData);
    echo "Booking create result: " . json_encode($createResult) . "\n\n";
    
    if ($createResult['status'] === 'success') {
        $bookingId = $createResult['data']['id'];
        echo "Created booking ID: " . $bookingId . "\n\n";
        
        // Test 3: Update the booking
        echo "=== UPDATING BOOKING ===\n";
        
        $updateData = [
            'id' => $bookingId,
            'package_id' => 1,
            'booking_date' => '2025-12-30 15:00:00',
            'status' => 'pending'
        ];
        
        $updateResult = $bookingApi->update($updateData);
        echo "Booking update result: " . json_encode($updateResult) . "\n\n";
        
        // Test 4: Read the updated booking
        echo "=== READING UPDATED BOOKING ===\n";
        
        $readData = ['id' => $bookingId];
        $readResult = $bookingApi->read($readData);
        echo "Booking read result: " . json_encode($readResult) . "\n\n";
        
    } else {
        echo "Failed to create test booking\n";
    }
    
} else {
    echo "Failed to login\n";
}

echo "=== TEST COMPLETE ===\n";
?>
