<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin'])) {
    $pin = $_POST['pin'];
    $selected_role = $_POST['role'] ?? 'technician'; // get the role from role selection page
    
    $stmt = $conn->prepare("SELECT id, first_name, last_name, user_type FROM users WHERE pin = ? AND is_active = 1");
    $stmt->bind_param("s", $pin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = strtolower($selected_role);
        
        header('Location: ../public/schedule-management.php');
        exit();
    } else {
        header('Location: signin.php?error=Invalid PIN&role=' . urlencode($selected_role));
        exit();
    }
    
    $stmt->close();
} else {
    header('Location: signin.php');
    exit();
}

$conn->close();
?>
