<?php
session_start();
require_once '../includes/db_connect.php';

$userRole = $_SESSION['user_role'] ?? 'faculty';

// Both technicians and faculty can clear schedules
if ($userRole !== 'technician' && $userRole !== 'faculty') {
    $_SESSION['error'] = 'Unauthorized access.';
    header('Location: schedule-management.php');
    exit();
}

$room = $_GET['room'] ?? '1811';

// Delete all schedules for the specified room
$query = "DELETE FROM schedules WHERE room = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $room);

if ($stmt->execute()) {
    $deletedCount = $stmt->affected_rows;
    $_SESSION['success'] = "Successfully cleared $deletedCount schedule(s) from Lab $room";
} else {
    $_SESSION['error'] = 'Failed to clear schedules. Please try again.';
}

$stmt->close();
$conn->close();

header('Location: schedule-management.php?room=' . urlencode($room));
exit();
?>
