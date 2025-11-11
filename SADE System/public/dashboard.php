<?php
session_start();
require_once '../includes/db_connect.php';

// Fetch recent access logs
$logs_query = "SELECT lab_id, user_name, action, method, timestamp FROM access_logs ORDER BY timestamp DESC LIMIT 10";
$logs_result = $conn->query($logs_query);
$recent_logs = $logs_result->fetch_all(MYSQLI_ASSOC);

// Count active sessions (entries without exits on current day)
$active_sessions_query = "SELECT COUNT(*) as count FROM access_logs WHERE DATE(timestamp) = CURDATE() AND action = 'ENTRY'";
$active_sessions_result = $conn->query($active_sessions_query);
$active_sessions = $active_sessions_result->fetch_assoc()['count'];

// Count security alerts
$alerts_query = "SELECT COUNT(*) as count FROM security_alerts WHERE DATE(timestamp) = CURDATE() AND is_acknowledged = 0";
$alerts_result = $conn->query($alerts_query);
$security_alerts = $alerts_result->fetch_assoc()['count'];

// Fetch device statuses
$devices_query = "SELECT device_id, lab_id, is_online, door_locked FROM devices";
$devices_result = $conn->query($devices_query);
$devices = $devices_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SADE - Dashboard</title>
  <link href="../assets/css/style.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
  <div class="main-container">
    
    <!-- Sidebar -->
    <aside class="sidebar">
      <?php include '../includes/sidebar.php'; ?>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <div class="header">
        <h1 class="page-title">SADE Dashboard | <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h1>
        <div class="user-profile">
          <img src="/placeholder.svg?height=32&width=32&text=User" alt="User Avatar" class="user-avatar">
          <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
          <span>▼</span>
        </div>
      </div>

      <div class="dashboard-content">
        <!-- Lab Status Indicators -->
        <div class="lab-status">
          <?php foreach ($devices as $device): ?>
          <div class="lab-indicator">
            <div class="lab-dot <?= $device['is_online'] ? 'online' : 'offline' ?>"></div>
            <div class="lab-name">Lab <?= htmlspecialchars($device['lab_id']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
          <!-- Left Column -->
          <div class="left-column">
            <!-- Today's Summary -->
            <div class="summary-card">
              <div class="summary-header">
                <div class="summary-highlight"></div>
                <div class="summary-title">Today's Summary</div>
              </div>
              
              <div class="summary-item">
                <div class="summary-icon"></div>
                <span>Active Sessions: <span id="activeSessions"><?= $active_sessions ?></span></span>
              </div>
              
              <div class="summary-item">
                <div class="summary-icon"></div>
                <span>Security Alerts: <span id="securityAlerts"><?= $security_alerts ?></span> <span class="alert-badge">NEW</span></span>
              </div>
            </div>

            <!-- Real-time Access Log -->
            <div class="access-log">
              <div class="log-title">Real-time Access Log</div>
              
              <div id="logEntries">
                <?php foreach ($recent_logs as $log): ?>
                <div class="log-entry">
                  <span class="log-time">[<?= date('H:i:s', strtotime($log['timestamp'])) ?>]</span> 
                  Lab <?= htmlspecialchars($log['lab_id']) ?>: <?= htmlspecialchars($log['action']) ?>
                </div>
                <?php endforeach; ?>
              </div>
              
              <button class="pause-btn" id="pauseBtn" onclick="toggleLogPause()">Pause/Resume</button>
            </div>
          </div>

          <!-- Right Column - Action Buttons -->
          <div class="action-buttons">
            <button class="action-btn" onclick="overrideDoor()">
              🔓<br>Override<br>Door
            </button>
            
            <button class="action-btn" onclick="viewAlerts()">
              🔔<br>View Alerts
              <div class="notification-badge" id="alertBadge"><?= $security_alerts ?></div>
            </button>
            
            <button class="action-btn" onclick="generateReport()">
              📊<br>Generate<br>Report
            </button>
            
            <button class="action-btn" onclick="window.location.href='#'">
              💚<br>System<br>Health
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/js/dashboard.js"></script>
</body>
</html>
<?php $conn->close(); ?>
