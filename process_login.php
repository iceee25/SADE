<?php
session_start();
require_once '../includes/db_connect.php';

$pin = $_POST['pin'] ?? '';

if (empty($pin)) {
    header('Location: signin.php?error=PIN is required');
    exit;
}

$stmt = $conn->prepare("SELECT id, user_id, first_name, last_name, user_type FROM users WHERE pin = ? AND user_type = 'TECHNICIAN' AND is_active = 1");
$stmt->bind_param('s', $pin);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_role'] = 'technician';
    $_SESSION['is_logged_in'] = true;
    
    header('Location: schedule-management.php');
    exit;
} else {
    header('Location: signin.php?error=Invalid PIN');
    exit;
}
?>
