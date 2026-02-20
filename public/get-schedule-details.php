<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Schedule ID not provided']);
    exit;
}

$scheduleId = intval($_GET['id']);

$query = "
    SELECT DISTINCT u.id, u.id_number, u.full_name
    FROM users u
    INNER JOIN schedule_enrollments se ON u.id = se.user_id
    WHERE se.schedule_id = ? AND u.user_type = 'student'
    ORDER BY u.full_name ASC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database query failed']);
    exit;
}

$stmt->bind_param("i", $scheduleId);
$stmt->execute();
$result = $stmt->get_result();
$participants = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    'success' => true,
    'participants' => $participants
]);

$conn->close();
?>
