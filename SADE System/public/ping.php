<?php
/**
 * ping.php — ESP32 connection test endpoint
 * Place at: /sade_system/public/ping.php
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/db_connect.php';
$db_ok = ($conn && !$conn->connect_error);

echo json_encode([
    'status'  => 'ok',
    'message' => 'SADE server reachable',
    'db'      => $db_ok ? 'connected' : 'error',
    'time'    => date('Y-m-d H:i:s'),
]);
?>