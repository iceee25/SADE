<?php
session_start();
require_once '../includes/db_connect.php';

$userRole = $_SESSION['user_role'] ?? 'teacher';

// Only technicians can delete schedules
if ($userRole !== 'technician') {
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

// Get the room before deleting
$query = "SELECT room FROM schedules WHERE id = ?";
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

$delete_query = "DELETE FROM schedules WHERE id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("i", $scheduleId);

if ($delete_stmt->execute()) {
    $_SESSION['success'] = 'Schedule deleted successfully!';
} else {
    $_SESSION['error'] = 'Failed to delete schedule: ' . $delete_stmt->error;
}

$delete_stmt->close();
$conn->close();

header('Location: schedule-management.php?room=' . $selectedRoom);
?>
