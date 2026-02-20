<?php
/**
 * door_control.php
 * ─────────────────────────────────────────────────────
 * Place at: C:/xampp/htdocs/SADE System/public/door_control.php
 *
 * Handles:
 *   GET  ?device_id=xxx     → ESP32 polls for pending command
 *   GET  ?refresh_log=1     → alerts.php fetches latest log entries
 *   POST action=web_unlock  → admin clicked Unlock on website
 *   POST action=web_lock    → admin clicked Lock on website
 *   POST action=esp32_log   → ESP32 reports what it just did
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once '../includes/db_connect.php';

$method = $_SERVER['REQUEST_METHOD'];

// ────────────────────────────────────────────────────────
// GET — two sub-modes
// ────────────────────────────────────────────────────────
if ($method === 'GET') {

    // ── Log refresh for alerts.php ───────────────────────
    if (isset($_GET['refresh_log'])) {
        $result = $conn->query(
            "SELECT lab_id, user_name, action, method, timestamp
             FROM access_logs
             ORDER BY timestamp DESC
             LIMIT 10"
        );
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = [
                'time'      => date('H:i:s', strtotime($row['timestamp'])),
                'lab_id'    => $row['lab_id'],
                'action'    => $row['action'],
                'method'    => $row['method'] ?? '',
                'user_name' => $row['user_name'] ?? '',
            ];
        }
        echo json_encode(['logs' => $logs]);
        exit;
    }

    // ── ESP32 polling ────────────────────────────────────
    $device_id = $_GET['device_id'] ?? 'SADE_DOOR_1811';

    $stmt = $conn->prepare(
        "SELECT door_locked FROM devices WHERE device_id = ?"
    );
    $stmt->bind_param('s', $device_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Device not found: ' . $device_id]);
        exit;
    }

    $conn->query(
        "UPDATE devices SET is_online=1, last_seen=NOW() WHERE device_id='$device_id'"
    );

    echo json_encode([
        'device_id'   => $device_id,
        'command'     => $row['door_locked'] == 0 ? 'UNLOCK' : 'LOCK',
        'door_locked' => (int)$row['door_locked'],
        'timestamp'   => date('c')
    ]);

// ────────────────────────────────────────────────────────
// POST
// ────────────────────────────────────────────────────────
} elseif ($method === 'POST') {

    $body      = json_decode(file_get_contents('php://input'), true);
    $action    = $body['action']    ?? $_POST['action']    ?? '';
    $device_id = $body['device_id'] ?? $_POST['device_id'] ?? 'SADE_DOOR_1811';
    $lab_id    = substr($device_id, -4);

    if ($action === 'web_unlock') {

        $conn->query("UPDATE devices SET door_locked=0 WHERE device_id='$device_id'");

        $log_id    = 'WEB-' . uniqid();
        $user_id   = $_SESSION['user_id']   ?? 'SYSTEM';
        $user_name = $_SESSION['user_name'] ?? 'Web Admin';

        $stmt = $conn->prepare(
            "INSERT INTO access_logs
             (log_id, device_id, lab_id, user_id, user_name, action, method)
             VALUES (?,?,?,?,?,'DOOR_UNLOCK_QUEUED','WEB')"
        );
        $stmt->bind_param('sssss', $log_id, $device_id, $lab_id, $user_id, $user_name);
        $stmt->execute();

        echo json_encode([
            'status'    => 'unlock_queued',
            'device_id' => $device_id,
            'message'   => 'ESP32 will unlock within 3 seconds'
        ]);

    } elseif ($action === 'web_lock') {

        $conn->query("UPDATE devices SET door_locked=1 WHERE device_id='$device_id'");

        $log_id    = 'WEB-' . uniqid();
        $user_id   = $_SESSION['user_id']   ?? 'SYSTEM';
        $user_name = $_SESSION['user_name'] ?? 'Web Admin';

        $stmt = $conn->prepare(
            "INSERT INTO access_logs
             (log_id, device_id, lab_id, user_id, user_name, action, method)
             VALUES (?,?,?,?,?,'DOOR_LOCK_QUEUED','WEB')"
        );
        $stmt->bind_param('sssss', $log_id, $device_id, $lab_id, $user_id, $user_name);
        $stmt->execute();

        echo json_encode(['status' => 'lock_queued', 'device_id' => $device_id]);

    } elseif ($action === 'esp32_log') {

        $result = $body['result'] ?? 'UNKNOWN';
        $log_id = 'ESP32-' . uniqid();

        $stmt = $conn->prepare(
            "INSERT INTO access_logs
             (log_id, device_id, lab_id, action, method)
             VALUES (?,?,?,?,'ESP32')"
        );
        $stmt->bind_param('ssss', $log_id, $device_id, $lab_id, $result);
        $stmt->execute();

        if ($result === 'DOOR_UNLOCKED') {
            $conn->query("UPDATE devices SET door_locked=1 WHERE device_id='$device_id'");
        }

        echo json_encode(['status' => 'logged', 'log_id' => $log_id]);

    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action: ' . $action]);
    }
}

$conn->close();
?>