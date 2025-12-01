<?php
session_start();

// Validate user is logged in
if (!isset($_SESSION['user_role'])) {
    header('Location: signin.php');
    exit;
}

// Get the target role from query parameter
$targetRole = $_GET['role'] ?? null;

// Only allow switching between technician and faculty
if ($targetRole === 'faculty' || $targetRole === 'technician') {
    $_SESSION['user_role'] = $targetRole;
}

// Redirect back to schedule management
header('Location: schedule-management.php');
exit;
?>
