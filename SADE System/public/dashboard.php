<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SADE - Registered Face IDs</title>
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
                <h1 class="page-title">SADE Dashboard | Technician</h1>
                <div class="user-profile">
                    <img src="/placeholder.svg?height=32&width=32&text=Tech" alt="Tech. Victorio" class="user-avatar">
                    <span class="user-name">Tech. Victorio</span>
                    <span>▼</span>
                </div>
            </div>

            <div class="dashboard-content">
                <!-- Lab Status Indicators -->
                <div class="lab-status">
                    <div class="lab-indicator">
                        <div class="lab-dot green"></div>
                        <div class="lab-name">Lab 1811</div>
                    </div>
                    <div class="lab-indicator">
                        <div class="lab-dot red"></div>
                        <div class="lab-name">Lab 1815</div>
                    </div>
                    <div class="lab-indicator">
                        <div class="lab-dot orange"></div>
                        <div class="lab-name">Lab 1812</div>
                    </div>
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
                                <span>Active Sessions: <span id="activeSessions">3</span></span>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon"></div>
                                <span>Security Alerts: <span id="securityAlerts">1</span> <span class="alert-badge">NEW</span></span>
                            </div>
                            
                            <div style="margin-top: 15px; color: #666; font-size: 14px;">
                                <div style="margin-bottom: 5px;">Lab 1811: <span id="lab1811Status">Active</span></div>
                                <div style="margin-bottom: 5px;">Lab 1812: <span id="lab1812Status">Maintenance</span></div>
                            </div>
                        </div>

                        <!-- Real-time Access Log -->
                        <div class="access-log">
                            <div class="log-title">Real-time Access Log</div>
                            
                            <div id="logEntries">
                                <div class="log-entry">
                                    <span class="log-time">[14:30:05]</span> Lab1811: Student 2020123456 entered
                                </div>
                                
                                <div class="log-entry">
                                    <span class="log-time">[14:32:17]</span> Lab1812: Prof. Cruz authenticated
                                </div>
                                
                                <div class="log-alert">[14:33:22] ALERT: Door tamper detected - Lab1811</div>
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
                            <div class="notification-badge" id="alertBadge">3</div>
                        </button>
                        
                        <button class="action-btn" onclick="generateReport()">
                            📊<br>Generate<br>Report
                        </button>
                        
                        <button class="action-btn" onclick="window.location.href='system-health.php'">
                            💚<br>System<br>Health
                        </button>
                    </div>
                </div>
            </div>

            <!-- Critical Alert Banner -->
            <div class="critical-alert" id="criticalAlert">
                <div class="alert-text">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><strong>CRITICAL ALERT:</strong> Lab 1811 door forced at 14:33</span>
                </div>
                <button class="acknowledge-btn" onclick="acknowledgeAlert()">ACKNOWLEDGE</button>
            </div>
        </div>
    </div>
    <script src="../assets/js/dashboard.js">
    </script>
</body>
</html>
