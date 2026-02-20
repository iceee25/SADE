<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Schedule ID not provided']);
    exit;
}

$scheduleId = $_GET['id'];

// For now, fetch all participants since there's no direct relationship
// In the future, you might want to create a schedule_participants junction table
$query = "SELECT id, id_number, full_name, email, status FROM participants ORDER BY full_name ASC";
$result = $conn->query($query);

$participants = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $participants[] = $row;
    }
}

echo json_encode(['success' => true, 'participants' => $participants]);

$conn->close();
?>


