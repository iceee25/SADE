<?php
// Make session expire when browser closes
ini_set('session.cookie_lifetime', 0);

session_start();

require_once '../includes/db_connect.php';

$pin = $_POST['pin'] ?? '';

if (empty($pin)) {
    header('Location: signin.php?error=PIN is required');
    exit;
}

// Get all active technicians from database
$stmt = $conn->prepare("SELECT id, user_id, first_name, last_name, user_type, pin FROM users WHERE user_type = 'TECHNICIAN' AND is_active = 1");
$stmt->execute();
$result = $stmt->get_result();

$authenticated = false;
$user = null;

// Check each technician's PIN using password_verify
while ($row = $result->fetch_assoc()) {
    // Verify the entered PIN against the hashed PIN in database
    if (password_verify($pin, $row['pin'])) {
        $authenticated = true;
        $user = $row;
        break;
    }
}

$stmt->close();

if ($authenticated && $user) {
    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_role'] = 'technician';
    $_SESSION['is_logged_in'] = true;
    $_SESSION['initialized'] = true;
    
    header('Location: schedule-management.php');
    exit;
} else {
    header('Location: signin.php?error=Invalid PIN');
    exit;
}
?>
