<?php
session_start();
require_once '../includes/db_connect.php';
require_once 'archive-log-helper.php';

$userRole = $_SESSION['user_role'] ?? 'teacher';

// Both technicians and faculty can delete schedules
if ($userRole !== 'technician' && $userRole !== 'faculty') {
    $_SESSION['error'] = 'Unauthorized access.';
    header('Location: schedule-management.php');
    exit();
}

$scheduleId = $_GET['id'] ?? null;

if (!$scheduleId) {
    $_SESSION['error'] = 'Schedule ID not provided.';
    header('Location: schedule-management.php');
    exit();
}

// Get the schedule details before deleting
$query = "SELECT room, course_code, course_name, instructor, day, start_time, end_time FROM schedules WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $scheduleId);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();
$stmt->close();

if (!$schedule) {
    $_SESSION['error'] = 'Schedule not found.';
    header('Location: schedule-management.php');
    exit();
}

$selectedRoom = $schedule['room'];

$userId = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_name'] ?? 'SYSTEM';

// Log the archive action
$scheduleDetails = $schedule['course_code'] . ' - ' . $schedule['course_name'] . ' (' . $schedule['instructor'] . ')';
logArchiveAction($conn, 'SCHEDULE', $scheduleId, $scheduleDetails, $userId, $userName, $selectedRoom);

// Delete the schedule
$delete_query = "DELETE FROM schedules WHERE id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("i", $scheduleId);

if ($delete_stmt->execute()) {
    $_SESSION['success'] = 'Schedule archived successfully and logged!';
} else {
    $_SESSION['error'] = 'Failed to archive schedule: ' . $delete_stmt->error;
}

$delete_stmt->close();
$conn->close();

header('Location: schedule-management.php?room=' . $selectedRoom);
?>
