<?php
session_start();

// Get the target role from query parameter
$targetRole = $_GET['role'] ?? null;

// If switching to faculty, allow direct switch
if ($targetRole === 'faculty') {
    $_SESSION['user_role'] = 'faculty';
    $_SESSION['user_name'] = 'Faculty User';
    // Clear technician-specific session data
    unset($_SESSION['user_id']);
    unset($_SESSION['is_logged_in']);
    header('Location: schedule-management.php');
    exit;
}

// If switching to technician, redirect to signin page for authentication
if ($targetRole === 'technician') {
    // Clear current session except preserve the intent
    session_unset();
    header('Location: signin.php');
    exit;
}

// Default: redirect to schedule management
header('Location: schedule-management.php');
exit;
?>
