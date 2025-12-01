<?php
// ESP32 Configuration Settings
// Store all ESP32 related constants and database connections

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sade_system');

// Device Configuration
define('ALLOWED_DEVICES', json_encode([
    'SADE_BARCODE_SCANNER_01' => [
        'lab_id' => '1811',
        'room' => 'Lab 1811',
        'device_type' => 'BARCODE_SCANNER'
    ],
    'SADE_KEYPAD_01' => [
        'lab_id' => '1811',
        'room' => 'Lab 1811',
        'device_type' => 'KEYPAD'
    ]
]));

// Function to get database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
    }
    
    return $conn;
}

// Function to validate device
function validateDevice($device_id) {
    $allowed = json_decode(ALLOWED_DEVICES, true);
    return isset($allowed[$device_id]);
}

// Function to get device info
function getDeviceInfo($device_id) {
    $allowed = json_decode(ALLOWED_DEVICES, true);
    return isset($allowed[$device_id]) ? $allowed[$device_id] : null;
}

// Function to log to system
function logSystemAction($conn, $action, $device_id, $description) {
    $log_id = "SYS_LOG_" . $device_id . "_" . time();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $query = "INSERT INTO system_logs (log_id, event_type, message, user_name, ip_address, timestamp) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $log_id, $action, $description, $device_id, $ip_address);
    return $stmt->execute();
}
?>
