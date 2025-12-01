<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: schedule-management.php');
    exit();
}

$courseCode = trim($_POST['courseCode'] ?? '');
$courseName = trim($_POST['courseName'] ?? '');
$instructor = trim($_POST['instructor'] ?? '');
$dayOfWeek = strtolower(trim($_POST['dayOfWeek'] ?? ''));
$room = trim($_POST['room'] ?? '');
$startTime = trim($_POST['startTime'] ?? '');
$endTime = trim($_POST['endTime'] ?? '');

if (empty($courseCode) || empty($courseName) || empty($instructor) || empty($dayOfWeek) || empty($room) || empty($startTime) || empty($endTime)) {
    $_SESSION['error'] = 'All fields are required';
    header('Location: schedule-management.php?room=' . urlencode($room));
    exit();
}

$scheduleId = 'SCH-' . uniqid() . '-' . time();
$createdBy = $_SESSION['user_id'] ?? 'SYSTEM';

$query = "INSERT INTO schedules (schedule_id, course_code, course_name, instructor, day, room, start_time, end_time, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

if (!$stmt) {
    $_SESSION['error'] = 'Database error: ' . $conn->error;
    header('Location: schedule-management.php?room=' . urlencode($room));
    exit();
}

$stmt->bind_param("sssssssss", $scheduleId, $courseCode, $courseName, $instructor, $dayOfWeek, $room, $startTime, $endTime, $createdBy);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Schedule added successfully!';
} else {
    $_SESSION['error'] = 'Error adding schedule: ' . $stmt->error;
}

$stmt->close();
$conn->close();

header('Location: schedule-management.php?room=' . urlencode($room));
exit();
?>
