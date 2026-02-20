<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: schedule-management.php');
    exit();
}

$userRole = $_SESSION['user_role'] ?? 'guest';
if ($userRole !== 'technician' && $userRole !== 'faculty') {
    $_SESSION['error'] = 'Unauthorized access.';
    header('Location: schedule-management.php');
    exit();
}

$courseCode = trim($_POST['courseCode'] ?? '');
$courseName = trim($_POST['courseName'] ?? '');
$instructor = trim($_POST['instructor'] ?? '');
$allowedAbsences = intval($_POST['allowedAbsences'] ?? 0);
$gracePeriod = intval($_POST['gracePeriod'] ?? 0);
$daysOfWeek = $_POST['dayOfWeek'] ?? [];
$rooms = $_POST['room'] ?? [];
$startTimes = $_POST['startTime'] ?? [];
$endTimes = $_POST['endTime'] ?? [];

if (empty($courseCode) || empty($courseName) || empty($instructor) || $allowedAbsences < 0 || $gracePeriod < 0 || empty($daysOfWeek) || empty($rooms) || empty($startTimes) || empty($endTimes)) {
    $_SESSION['error'] = 'All fields are required';
    header('Location: schedule-management.php');
    exit();
}

// Ensure arrays are the same length
$slotCount = count($daysOfWeek);
if (count($rooms) !== $slotCount || count($startTimes) !== $slotCount || count($endTimes) !== $slotCount) {
    $_SESSION['error'] = 'Invalid schedule data';
    header('Location: schedule-management.php');
    exit();
}

$createdBy = $_SESSION['user_id'] ?? 'SYSTEM';
$errors = [];
$successCount = 0;
$redirectRoom = $rooms[0] ?? '1811';

// Loop through each schedule slot
for ($i = 0; $i < $slotCount; $i++) {
    $dayOfWeek = strtolower(trim($daysOfWeek[$i]));
    $room = trim($rooms[$i]);
    $startTime = trim($startTimes[$i]);
    $endTime = trim($endTimes[$i]);

    // Check for schedule conflicts
    $conflictQuery = "SELECT id, course_code, start_time, end_time FROM schedules 
                      WHERE day = ? AND room = ? 
                      AND ((start_time < ? AND end_time > ?) 
                           OR (start_time < ? AND end_time > ?)
                           OR (start_time >= ? AND end_time <= ?))
                      LIMIT 1";
    $conflictStmt = $conn->prepare($conflictQuery);
    $conflictStmt->bind_param("ssssssss", $dayOfWeek, $room, $endTime, $startTime, $endTime, $endTime, $startTime, $endTime);
    $conflictStmt->execute();
    $conflictResult = $conflictStmt->get_result();

    if ($conflictResult->num_rows > 0) {
        $errors[] = "Conflict on " . ucfirst($dayOfWeek) . " in Room $room at $startTime-$endTime";
        $conflictStmt->close();
        continue;
    }
    $conflictStmt->close();

    // Insert the schedule
    $scheduleId = 'SCH-' . uniqid() . '-' . time();
    $query = "INSERT INTO schedules (schedule_id, course_code, course_name, instructor, day, room, start_time, end_time, allowed_absences, grace_period, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        $errors[] = "Database error for " . ucfirst($dayOfWeek) . " in Room $room";
        continue;
    }

    $stmt->bind_param("ssssssssiis", $scheduleId, $courseCode, $courseName, $instructor, $dayOfWeek, $room, $startTime, $endTime, $allowedAbsences, $gracePeriod, $createdBy);

    if ($stmt->execute()) {
        $successCount++;
    } else {
        $errors[] = "Failed to add schedule for " . ucfirst($dayOfWeek) . " in Room $room";
    }
    $stmt->close();
}

// Return JSON response for AJAX
header('Content-Type: application/json');

if ($successCount > 0 && empty($errors)) {
    $_SESSION['success'] = "$successCount schedule(s) added successfully!";
    echo json_encode([
        'success' => true,
        'message' => "$successCount schedule(s) added successfully!",
        'room' => $redirectRoom
    ]);
} elseif ($successCount > 0 && !empty($errors)) {
    $_SESSION['warning'] = "$successCount schedule(s) added. Some conflicts: " . implode(', ', $errors);
    echo json_encode([
        'success' => true,
        'message' => "$successCount schedule(s) added. Some conflicts: " . implode(', ', $errors),
        'room' => $redirectRoom
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => implode('. ', $errors)
    ]);
}
$conn->close();
exit();
?>
