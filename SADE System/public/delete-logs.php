<?php
session_start();
require_once '../includes/db_connect.php';

// Check if user is authenticated and has admin permissions
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Optional: Check if user has admin role (adjust based on your user roles)
// if ($_SESSION['user_role'] !== 'admin') {
//     die('Unauthorized access');
// }

// Get filter parameters to determine which logs to delete
$filterLab = isset($_GET['lab']) ? $_GET['lab'] : '';
$filterType = isset($_GET['type']) ? $_GET['type'] : '';
$filterDateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filterDateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause based on filters
$whereConditions = array();
if (!empty($filterLab)) {
    $whereConditions[] = "lab_id = '" . $conn->real_escape_string($filterLab) . "'";
}
if (!empty($filterType)) {
    $whereConditions[] = "action = '" . $conn->real_escape_string($filterType) . "'";
}
if (!empty($filterDateFrom)) {
    $whereConditions[] = "DATE(timestamp) >= '" . $conn->real_escape_string($filterDateFrom) . "'";
}
if (!empty($filterDateTo)) {
    $whereConditions[] = "DATE(timestamp) <= '" . $conn->real_escape_string($filterDateTo) . "'";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

if (!empty($whereClause)) {
    // Delete filtered logs
    $delete_query = "DELETE FROM access_logs $whereClause";
    if ($conn->query($delete_query) === TRUE) {
        $deleted_rows = $conn->affected_rows;
        $_SESSION['message'] = "Successfully deleted $deleted_rows log entries.";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Error deleting logs: " . $conn->error;
        $_SESSION['message_type'] = 'error';
    }
} else {
    // If no filters, show warning
    $_SESSION['message'] = "No filters specified. No logs were deleted.";
    $_SESSION['message_type'] = 'warning';
}

$conn->close();
header('Location: logs.php');
exit();
?>
