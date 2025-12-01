<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sade_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["authenticated" => false, "message" => "Database connection failed"]));
}

// Get JSON data from ESP32
$data = json_decode(file_get_contents("php://input"), true);

$pin = isset($data['pin']) ? $data['pin'] : '';
$device_id = isset($data['device_id']) ? $data['device_id'] : '';
$lab_id = isset($data['lab_id']) ? $data['lab_id'] : '';

if (empty($pin)) {
    http_response_code(400);
    die(json_encode(["authenticated" => false, "message" => "Invalid PIN"]));
}

$query = "SELECT id, user_id, first_name, last_name, access_level FROM users WHERE pin = ? AND user_type = 'TECHNICIAN' AND is_active = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $pin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $technician = $result->fetch_assoc();
    $tech_id = $technician['user_id'];
    $tech_name = $technician['first_name'] . " " . $technician['last_name'];
    $access_level = $technician['access_level'];
    
    $log_id = "TECH_LOG_" . $device_id . "_" . time();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $insertQuery = "INSERT INTO access_logs (log_id, device_id, lab_id, user_id, user_name, action, method, timestamp) 
                    VALUES (?, ?, ?, ?, ?, 'TECHNICIAN_AUTH', 'KEYPAD', NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("sssis", $log_id, $device_id, $lab_id, $tech_id, $tech_name);
    
    $activityInsert = "INSERT INTO activity_logs (activity_type, description, ip_address) 
                       VALUES ('TECHNICIAN_LOGIN', ?, ?)";
    $activityStmt = $conn->prepare($activityInsert);
    $description = "Technician " . $tech_name . " authenticated at " . $device_id;
    $activityStmt->bind_param("ss", $description, $ip_address);
    
    if ($insertStmt->execute() && $activityStmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "authenticated" => true,
            "message" => "Technician authenticated: " . $tech_name,
            "technician_id" => $tech_id,
            "technician_name" => $tech_name,
            "access_level" => $access_level
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["authenticated" => false, "message" => "Failed to log authentication"]);
    }
    $insertStmt->close();
    $activityStmt->close();
} else {
    $log_id = "TECH_LOG_" . $device_id . "_FAILED_" . time();
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $insertQuery = "INSERT INTO access_logs (log_id, device_id, lab_id, action, method, timestamp) 
                    VALUES (?, ?, ?, 'TECHNICIAN_DENIED', 'KEYPAD', NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("sss", $log_id, $device_id, $lab_id);
    $insertStmt->execute();
    
    $activityInsert = "INSERT INTO activity_logs (activity_type, description, ip_address) 
                       VALUES ('TECHNICIAN_AUTH_FAILED', ?, ?)";
    $activityStmt = $conn->prepare($activityInsert);
    $description = "Failed technician authentication attempt at " . $device_id;
    $activityStmt->bind_param("ss", $description, $ip_address);
    $activityStmt->execute();
    
    $insertStmt->close();
    $activityStmt->close();
    
    http_response_code(200);
    echo json_encode(["authenticated" => false, "message" => "Invalid technician PIN"]);
}

$stmt->close();
$conn->close();
?>
