<?php
session_start();
require_once '../includes/db_connect.php';

$userRole = $_SESSION['user_role'] ?? 'teacher';

// Only technicians can update schedules
if ($userRole !== 'technician') {
    $_SESSION['error'] = 'Unauthorized access.';
    header('Location: schedule-management.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduleId = $_POST['scheduleId'] ?? null;
    $courseCode = $_POST['courseCode'] ?? '';
    $courseName = $_POST['courseName'] ?? '';
    $instructor = $_POST['instructor'] ?? '';
    $dayOfWeek = $_POST['dayOfWeek'] ?? '';
    $room = $_POST['room'] ?? '';
    $startTime = $_POST['startTime'] ?? '';
    $endTime = $_POST['endTime'] ?? '';

    if (!$scheduleId || !$courseCode || !$courseName || !$instructor || !$dayOfWeek || !$room || !$startTime || !$endTime) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: edit-schedule.php?id=' . $scheduleId);
        exit();
    }

    $query = "UPDATE schedules SET course_code = ?, course_name = ?, instructor = ?, day = ?, room = ?, start_time = ?, end_time = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssi", $courseCode, $courseName, $instructor, $dayOfWeek, $room, $startTime, $endTime, $scheduleId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Schedule updated successfully!';
        $selectedRoom = $room;
        header('Location: schedule-management.php?room=' . $selectedRoom);
    } else {
        $_SESSION['error'] = 'Failed to update schedule: ' . $stmt->error;
        header('Location: edit-schedule.php?id=' . $scheduleId);
    }
    
    $stmt->close();
    $conn->close();
} else {
    header('Location: schedule-management.php');
}
?>
