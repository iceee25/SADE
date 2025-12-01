<?php
header('Content-Type: application/json');

require_once 'esp32-config.php';

$data = json_decode(file_get_contents("php://input"), true);
$device_id = isset($data['device_id']) ? $data['device_id'] : '';

if (empty($device_id)) {
    http_response_code(400);
    die(json_encode(["success" => false, "message" => "Device ID required"]));
}

// Validate device
if (!validateDevice($device_id)) {
    http_response_code(401);
    die(json_encode(["success" => false, "message" => "Device not authorized"]));
}

$conn = getDBConnection();
$device_info = getDeviceInfo($device_id);

// Update device status
$updateQuery = "UPDATE devices SET is_online = 1, last_seen = NOW() WHERE device_id = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("s", $device_id);
$updateStmt->execute();
$updateStmt->close();

// Get device info
$selectQuery = "SELECT * FROM devices WHERE device_id = ?";
$selectStmt = $conn->prepare($selectQuery);
$selectStmt->bind_param("s", $device_id);
$selectStmt->execute();
$result = $selectStmt->get_result();
$device = $result->fetch_assoc();
$selectStmt->close();

http_response_code(200);
echo json_encode([
    "success" => true,
    "device_status" => "online",
    "device_id" => $device_id,
    "device_info" => $device_info,
    "server_time" => date('Y-m-d H:i:s'),
    "door_locked" => $device['door_locked'] ?? 1
]);

$conn->close();
?>
