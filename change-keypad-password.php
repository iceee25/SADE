<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sade_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// Get JSON data from ESP32
$data = json_decode(file_get_contents("php://input"), true);

$new_pin = isset($data['new_pin']) ? trim($data['new_pin']) : '';
$device_id = isset($data['device_id']) ? $data['device_id'] : '';
$lab_id = isset($data['lab_id']) ? $data['lab_id'] : '';
$technician_id = isset($data['technician_id']) ? $data['technician_id'] : '';

if (empty($new_pin) || strlen($new_pin) < 4 || strlen($new_pin) > 8) {
    http_response_code(400);
    die(json_encode(["success" => false, "message" => "PIN must be 4-8 digits", "pin_valid" => false]));
}

if (!ctype_digit($new_pin)) {
    http_response_code(400);
    die(json_encode(["success" => false, "message" => "PIN must contain only digits", "pin_valid" => false]));
}

$updateQuery = "UPDATE users SET pin = ? WHERE user_type = 'TECHNICIAN' AND is_active = 1";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("s", $new_pin);

if ($updateStmt->execute()) {
    $affectedRows = $updateStmt->affected_rows;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $log_id = "PIN_CHANGE_" . $device_id . "_" . time();
    
    $logQuery = "INSERT INTO access_logs (log_id, device_id, lab_id, user_id, action, method, timestamp) 
                 VALUES (?, ?, ?, ?, 'PIN_CHANGED', 'KEYPAD', NOW())";
    $logStmt = $conn->prepare($logQuery);
    $logStmt->bind_param("ssss", $log_id, $device_id, $lab_id, $technician_id);
    $logStmt->execute();
    
    $activityInsert = "INSERT INTO activity_logs (activity_type, description, ip_address) 
                       VALUES ('TECHNICIAN_PIN_CHANGE', ?, ?)";
    $activityStmt = $conn->prepare($activityInsert);
    $description = "Keypad PIN changed for lab " . $lab_id . " by device " . $device_id . " - " . $affectedRows . " users updated";
    $activityStmt->bind_param("ss", $description, $ip_address);
    $activityStmt->execute();
    $logStmt->close();
    $activityStmt->close();
    
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Keypad PIN updated successfully",
        "updated_count" => $affectedRows,
        "new_pin" => $new_pin,
        "timestamp" => date('Y-m-d H:i:s')
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to update PIN: " . $updateStmt->error]);
}

$updateStmt->close();
$conn->close();
?>
