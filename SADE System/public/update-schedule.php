<?php
session_start();
require_once '../includes/db_connect.php';

$userRole = $_SESSION['user_role'] ?? 'teacher';

// Both technicians and faculty can update schedules
if ($userRole !== 'technician' && $userRole !== 'faculty') {
    $_SESSION['error'] = 'Unauthorized access.';
    header('Location: schedule-management.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scheduleId = $_POST['scheduleId'] ?? null;
    $courseCode = $_POST['courseCode'] ?? '';
    $courseName = $_POST['courseName'] ?? '';
    $instructor = $_POST['instructor'] ?? '';
    $allowedAbsences = intval($_POST['allowedAbsences'] ?? 0);
    $gracePeriod = intval($_POST['gracePeriod'] ?? 0);
    $dayOfWeek = $_POST['dayOfWeek'] ?? '';
    $room = $_POST['room'] ?? '';
    $startTime = $_POST['startTime'] ?? '';
    $endTime = $_POST['endTime'] ?? '';

    if (!$scheduleId || !$courseCode || !$courseName || !$instructor || $allowedAbsences < 0 || $gracePeriod < 0 || !$dayOfWeek || !$room || !$startTime || !$endTime) {
        $_SESSION['error'] = 'All fields are required.';
        header('Location: edit-schedule.php?id=' . $scheduleId);
        exit();
    }

    $query = "UPDATE schedules SET course_code = ?, course_name = ?, instructor = ?, day = ?, room = ?, start_time = ?, end_time = ?, allowed_absences = ?, grace_period = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssii", $courseCode, $courseName, $instructor, $dayOfWeek, $room, $startTime, $endTime, $allowedAbsences, $gracePeriod, $scheduleId);
    
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
