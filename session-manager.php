<?php
/**
 * Session Manager - Centralized role and user session handling
 * Ensures user_role always reflects the actual user_type from database
 */

session_start();

// Database connection (reuse existing config)
require_once 'config.php';

/**
 * Initialize or refresh user session with correct role from database
 * @param string $user_id - The user ID to load
 * @return bool - Success/failure
 */
function initializeUserSession($user_id) {
    global $conn;
    
    try {
        // Fetch actual user_type from database
        $query = "SELECT id, user_id, user_type, first_name, last_name, email FROM users WHERE user_id = ? AND is_active = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $user = $result->fetch_assoc();
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = strtoupper($user['user_type']); // TECHNICIAN, FACULTY, STUDENT
        $_SESSION['user_role'] = strtolower($user['user_type']); // technician, faculty, student
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_db_id'] = $user['id'];
        $_SESSION['session_start_time'] = time();
        
        return true;
    } catch (Exception $e) {
        error_log("Session initialization error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get current user's actual role from database
 * @return string|null - User role (technician, faculty, student) or null
 */
function getCurrentUserRole() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    global $conn;
    
    try {
        $query = "SELECT user_type FROM users WHERE user_id = ? AND is_active = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            return strtolower($user['user_type']);
        }
        return null;
    } catch (Exception $e) {
        error_log("Get user role error: " . $e->getMessage());
        return null;
    }
}

/**
 * Validate current session and update role if changed
 * Call this at the beginning of protected pages
 */
function validateAndRefreshSession() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Get fresh role from database
    $currentRole = getCurrentUserRole();
    
    // If role changed in database, update session immediately
    if ($currentRole && $currentRole !== strtolower($_SESSION['user_role'] ?? '')) {
        $_SESSION['user_role'] = $currentRole;
        $_SESSION['user_type'] = strtoupper($currentRole);
    }
    
    return $currentRole !== null;
}

/**
 * Destroy user session cleanly
 */
function destroyUserSession() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    session_destroy();
}

?>
