<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Schedule ID not provided']);
    exit;
}

$scheduleId = $_GET['id'];
$query = "SELECT id, course_code, course_name, instructor, day, room, start_time, end_time FROM schedules WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $scheduleId);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();
$stmt->close();

if ($schedule) {
    echo json_encode(['success' => true, 'schedule' => $schedule]);
} else {
    echo json_encode(['success' => false, 'error' => 'Schedule not found']);
}

$conn->close();
?>
