<?php
header('Content-Type: application/json');

// Database connection
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

$barcode = isset($data['barcode']) ? trim($data['barcode']) : '';
$device_id = isset($data['device_id']) ? $data['device_id'] : '';
$lab_id = isset($data['lab_id']) ? $data['lab_id'] : '';
$action = isset($data['action']) ? $data['action'] : 'SCAN';

if (empty($barcode)) {
    http_response_code(400);
    die(json_encode(["success" => false, "message" => "Invalid barcode"]));
}

// Check if student/participant exists
$query = "SELECT id, id_number, full_name FROM participants WHERE id_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    $user_name = $user['full_name'];
    
    $log_id = "LOG_" . $device_id . "_" . time();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $insertQuery = "INSERT INTO access_logs (log_id, device_id, lab_id, user_id, user_name, action, method, timestamp) 
                    VALUES (?, ?, ?, ?, ?, 'ENTRY', 'BARCODE', NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("sssis", $log_id, $device_id, $lab_id, $user_id, $user_name);
    
    // Also log to activity_logs
    $activityInsert = "INSERT INTO activity_logs (activity_type, description, ip_address) 
                       VALUES ('BARCODE_SCAN', ?, ?)";
    $activityStmt = $conn->prepare($activityInsert);
    $description = "Student " . $user_name . " (" . $barcode . ") scanned at " . $device_id;
    $activityStmt->bind_param("ss", $description, $ip_address);
    
    if ($insertStmt->execute() && $activityStmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Access granted for " . $user_name,
            "user_id" => $user_id,
            "user_name" => $user_name,
            "access_granted" => true
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to log access"]);
    }
    $insertStmt->close();
    $activityStmt->close();
} else {
    $log_id = "LOG_" . $device_id . "_DENIED_" . time();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $insertQuery = "INSERT INTO access_logs (log_id, device_id, lab_id, action, method, timestamp) 
                    VALUES (?, ?, ?, 'DENIED', 'BARCODE', NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("sss", $log_id, $device_id, $lab_id);
    $insertStmt->execute();
    
    $activityInsert = "INSERT INTO activity_logs (activity_type, description, ip_address) 
                       VALUES ('BARCODE_DENIED', ?, ?)";
    $activityStmt = $conn->prepare($activityInsert);
    $description = "Unauthorized barcode scan: " . $barcode . " at " . $device_id;
    $activityStmt->bind_param("ss", $description, $ip_address);
    $activityStmt->execute();
    
    $insertStmt->close();
    $activityStmt->close();
    
    http_response_code(200);
    echo json_encode(["success" => false, "message" => "Student not found or not enrolled", "access_granted" => false]);
}

$stmt->close();
$conn->close();
?>
