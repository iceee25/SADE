<?php
/**
 * Archive Log Helper
 * Functions to log archive/delete operations across the system
 */

function logArchiveAction($conn, $dataType, $dataId, $dataDetails, $userId = null, $userName = null, $labId = null) {
    /**
     * Logs an archive action to access_logs table
     * 
     * @param mysqli $conn Database connection
     * @param string $dataType Type of data archived (e.g., 'SCHEDULE', 'STUDENT', 'FACULTY')
     * @param mixed $dataId ID of the archived item
     * @param string $dataDetails Details about what was archived (e.g., course name)
     * @param string|null $userId ID of user performing the action
     * @param string|null $userName Name of user performing the action
     * @param string|null $labId Lab ID if applicable
     * @return bool Success status
     */
    
    try {
        $logId = 'ARCHIVE-' . uniqid() . '-' . time();
        $action = 'ARCHIVED_' . $dataType;
        $method = 'ARCHIVE';
        $timestamp = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("
            INSERT INTO access_logs 
            (log_id, device_id, lab_id, user_id, user_name, action, method, timestamp)
            VALUES (?, 'SYSTEM', ?, ?, ?, ?, ?, ?)
        ");
        
        $deviceId = 'ARCHIVE_SYSTEM';
        
        $stmt->bind_param(
            "sssssss",
            $logId,
            $labId,
            $userId,
            $userName,
            $action,
            $method,
            $timestamp
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        // Also log to activity_logs for additional tracking
        if ($dataType === 'SCHEDULE' && $userId !== null) {
            $activityStmt = $conn->prepare("
                INSERT INTO activity_logs 
                (faculty_id, activity_type, description, ip_address)
                VALUES (?, ?, ?, ?)
            ");
            
            $activityType = 'DATA_ARCHIVED';
            $description = "Archived $dataType: $dataDetails";
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            
            $activityStmt->bind_param(
                "ssss",
                $userId,
                $activityType,
                $description,
                $ipAddress
            );
            
            $activityStmt->execute();
            $activityStmt->close();
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Archive logging error: " . $e->getMessage());
        return false;
    }
}

function logSystemAction($conn, $actionType, $description, $userId = null, $userName = null) {
    /**
     * Generic system action logger
     */
    try {
        $logId = 'SYS-' . uniqid() . '-' . time();
        $timestamp = date('Y-m-d H:i:s');
        
        $stmt = $conn->prepare("
            INSERT INTO system_logs 
            (log_id, event_type, message, user_id, user_name, ip_address, timestamp)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $stmt->bind_param(
            "sssssss",
            $logId,
            $actionType,
            $description,
            $userId,
            $userName,
            $ipAddress,
            $timestamp
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("System logging error: " . $e->getMessage());
        return false;
    }
}
?>
