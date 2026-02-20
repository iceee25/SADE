<?php
session_start();
require_once '../includes/db_connect.php';

// Fetch recent access logs
$logs_query = "SELECT lab_id, user_name, action, method, timestamp FROM access_logs ORDER BY timestamp DESC LIMIT 10";
$logs_result = $conn->query($logs_query);
$recent_logs = $logs_result->fetch_all(MYSQLI_ASSOC);

// Count active sessions
$active_sessions_query = "SELECT COUNT(*) as count FROM access_logs WHERE DATE(timestamp) = CURDATE() AND action = 'ENTRY'";
$active_sessions_result = $conn->query($active_sessions_query);
$active_sessions = $active_sessions_result->fetch_assoc()['count'];

// Count security alerts
$alerts_query = "SELECT COUNT(*) as count FROM security_alerts WHERE DATE(timestamp) = CURDATE() AND is_acknowledged = 0";
$alerts_result = $conn->query($alerts_query);
$security_alerts = $alerts_result->fetch_assoc()['count'];

// Fetch device statuses — now includes door_locked
$devices_query = "SELECT device_id, lab_id, is_online, door_locked, last_seen FROM devices";
$devices_result = $conn->query($devices_query);
$devices = $devices_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SADE - Alert</title>
  <link rel="icon" type="image/png" href="../assets/images/sade-logo.png">
  <link href="../assets/css/style.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* ── Door Control Modal ───────────────────────────── */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.7);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.active {
      display: flex;
    }
    .modal-box {
      background: #1e293b;
      border: 1px solid #334155;
      border-radius: 12px;
      padding: 28px 32px;
      width: 420px;
      max-width: 95vw;
    }
    .modal-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #e2e8f0;
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .modal-sub {
      font-size: 0.8rem;
      color: #64748b;
      margin-bottom: 20px;
      font-family: monospace;
    }

    /* ── Device Cards ─────────────────────────────────── */
    .device-card {
      background: #0f172a;
      border: 1px solid #1e2d45;
      border-radius: 8px;
      padding: 14px 16px;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .device-card.online  { border-left: 3px solid #10b981; }
    .device-card.offline { border-left: 3px solid #ef4444; }

    .device-info { flex: 1; }
    .device-name {
      font-size: 0.85rem;
      font-weight: 700;
      color: #e2e8f0;
    }
    .device-meta {
      font-size: 0.7rem;
      color: #64748b;
      font-family: monospace;
      margin-top: 3px;
    }
    .device-status {
      font-size: 0.7rem;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 20px;
      white-space: nowrap;
    }
    .device-status.locked   { background: rgba(239,68,68,0.15);  color: #f87171; }
    .device-status.unlocked { background: rgba(16,185,129,0.15); color: #34d399; }
    .device-status.offline  { background: rgba(100,116,139,0.15);color: #94a3b8; }

    /* ── Door Buttons ─────────────────────────────────── */
    .door-btns {
      display: flex;
      gap: 8px;
      margin-top: 8px;
    }
    .btn-unlock {
      flex: 1;
      padding: 9px 0;
      background: rgba(16,185,129,0.15);
      border: 1px solid rgba(16,185,129,0.4);
      color: #34d399;
      border-radius: 6px;
      font-size: 0.78rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-unlock:hover {
      background: rgba(16,185,129,0.3);
    }
    .btn-lock {
      flex: 1;
      padding: 9px 0;
      background: rgba(239,68,68,0.1);
      border: 1px solid rgba(239,68,68,0.3);
      color: #f87171;
      border-radius: 6px;
      font-size: 0.78rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-lock:hover {
      background: rgba(239,68,68,0.25);
    }
    .btn-unlock:disabled,
    .btn-lock:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    /* ── Action feedback ──────────────────────────────── */
    .door-feedback {
      font-family: monospace;
      font-size: 0.75rem;
      min-height: 18px;
      margin-top: 6px;
      text-align: center;
    }
    .door-feedback.ok   { color: #34d399; }
    .door-feedback.err  { color: #f87171; }
    .door-feedback.wait { color: #f59e0b; }

    /* ── Modal footer ─────────────────────────────────── */
    .modal-footer {
      margin-top: 20px;
      display: flex;
      justify-content: flex-end;
    }
    .btn-close-modal {
      background: #334155;
      border: none;
      color: #94a3b8;
      padding: 8px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.8rem;
      transition: background 0.2s;
    }
    .btn-close-modal:hover { background: #475569; color: #e2e8f0; }

    /* ── Live pulse dot ───────────────────────────────── */
    .pulse-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 5px;
    }
    .pulse-dot.online {
      background: #10b981;
      box-shadow: 0 0 0 0 rgba(16,185,129,0.4);
      animation: pulse 1.5s infinite;
    }
    .pulse-dot.offline { background: #ef4444; }

    @keyframes pulse {
      0%   { box-shadow: 0 0 0 0 rgba(16,185,129,0.4); }
      70%  { box-shadow: 0 0 0 6px rgba(16,185,129,0); }
      100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
    }
  </style>
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
        <h1 class="page-title">SADE Alerts | <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h1>
        <div class="user-profile">
          <img src="/placeholder.svg?height=32&width=32&text=User" alt="User Avatar" class="user-avatar">
          <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
          <span>▼</span>
        </div>
      </div>

      <div class="alert-content">

        <!-- Lab Status Indicators -->
        <div class="lab-status">
          <?php foreach ($devices as $device): ?>
          <div class="lab-indicator">
            <div class="lab-dot <?= $device['is_online'] ? 'online' : 'offline' ?>"></div>
            <div class="lab-name">Lab <?= htmlspecialchars($device['lab_id']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Alerts Grid -->
        <div class="alert-grid">

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
                  Lab <?= htmlspecialchars($log['lab_id']) ?>:
                  <?= htmlspecialchars($log['action']) ?>
                  <?php if ($log['user_name']): ?>
                    — <?= htmlspecialchars($log['user_name']) ?>
                  <?php endif; ?>
                  <span style="color:#475569;font-size:0.7em;">[<?= htmlspecialchars($log['method'] ?? '') ?>]</span>
                </div>
                <?php endforeach; ?>
              </div>
              <button class="pause-btn" id="pauseBtn" onclick="toggleLogPause()">Pause/Resume</button>
            </div>

          </div>

          <!-- Right Column - Action Buttons -->
          <div class="action-buttons">

            <!-- Override Door — now opens modal -->
            <button class="action-btn" onclick="openDoorModal()">
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

  <!-- ══════════════════════════════════════════════════
       DOOR CONTROL MODAL
       Opens when admin clicks "Override Door"
  ═══════════════════════════════════════════════════ -->
  <div class="modal-overlay" id="doorModal">
    <div class="modal-box">

      <div class="modal-title">🔐 Door Control Panel</div>
      <div class="modal-sub">Commands are sent to ESP32 within 3 seconds</div>

      <!-- One card per device -->
      <?php foreach ($devices as $device):
        $isOnline  = (bool)$device['is_online'];
        $isLocked  = (bool)$device['door_locked'];
        $deviceId  = htmlspecialchars($device['device_id']);
        $labId     = htmlspecialchars($device['lab_id']);
        $lastSeen  = $device['last_seen']
                     ? date('M d H:i', strtotime($device['last_seen']))
                     : 'Never';
      ?>
      <div class="device-card <?= $isOnline ? 'online' : 'offline' ?>"
           id="card-<?= $deviceId ?>">

        <div class="device-info">
          <div class="device-name">
            <span class="pulse-dot <?= $isOnline ? 'online' : 'offline' ?>"></span>
            Lab <?= $labId ?>
            <span style="font-size:0.7rem;color:#475569;font-weight:400;">
              — <?= $deviceId ?>
            </span>
          </div>
          <div class="device-meta">
            Last seen: <?= $lastSeen ?>
          </div>

          <!-- Unlock / Lock buttons -->
          <?php if ($isOnline): ?>
          <div class="door-btns">
            <button class="btn-unlock"
              onclick="doorAction('<?= $deviceId ?>', 'web_unlock')"
              id="btn-unlock-<?= $deviceId ?>"
              <?= !$isLocked ? 'disabled title="Already unlocked"' : '' ?>>
              🔓 Unlock
            </button>
            <button class="btn-lock"
              onclick="doorAction('<?= $deviceId ?>', 'web_lock')"
              id="btn-lock-<?= $deviceId ?>"
              <?= $isLocked ? 'disabled title="Already locked"' : '' ?>>
              🔒 Lock
            </button>
          </div>
          <?php else: ?>
          <div class="door-btns">
            <button class="btn-unlock" disabled>🔓 Unlock</button>
            <button class="btn-lock"   disabled>🔒 Lock</button>
          </div>
          <?php endif; ?>

          <!-- Feedback message per device -->
          <div class="door-feedback" id="feedback-<?= $deviceId ?>"></div>
        </div>

        <!-- Door status badge -->
        <div>
          <?php if (!$isOnline): ?>
            <span class="device-status offline">OFFLINE</span>
          <?php elseif ($isLocked): ?>
            <span class="device-status locked" id="status-<?= $deviceId ?>">🔒 LOCKED</span>
          <?php else: ?>
            <span class="device-status unlocked" id="status-<?= $deviceId ?>">🔓 UNLOCKED</span>
          <?php endif; ?>
        </div>

      </div>
      <?php endforeach; ?>

      <div class="modal-footer">
        <button class="btn-close-modal" onclick="closeDoorModal()">Close</button>
      </div>

    </div>
  </div>
  <!-- END MODAL -->

  <script src="../assets/js/dashboard.js"></script>
  <script>
  // ── Modal open/close ─────────────────────────────────
  function openDoorModal() {
    document.getElementById('doorModal').classList.add('active');
  }
  function closeDoorModal() {
    document.getElementById('doorModal').classList.remove('active');
  }
  // Close modal if clicking outside
  document.getElementById('doorModal').addEventListener('click', function(e) {
    if (e.target === this) closeDoorModal();
  });

  // ── Send door command to door_control.php ────────────
  function doorAction(deviceId, action) {
    const label      = action === 'web_unlock' ? 'Unlock' : 'Lock';
    const feedbackEl = document.getElementById('feedback-' + deviceId);
    const btnUnlock  = document.getElementById('btn-unlock-' + deviceId);
    const btnLock    = document.getElementById('btn-lock-'   + deviceId);
    const statusEl   = document.getElementById('status-'    + deviceId);

    if (!confirm(label + ' ' + deviceId + '?')) return;

    // Disable both buttons while waiting
    btnUnlock.disabled = true;
    btnLock.disabled   = true;
    feedbackEl.className = 'door-feedback wait';
    feedbackEl.innerText = 'Sending command...';

    fetch('door_control.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: action, device_id: deviceId })
    })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'unlock_queued') {
        feedbackEl.className = 'door-feedback ok';
        feedbackEl.innerText = '✅ Unlock sent! Door opens within 3 seconds.';
        statusEl.className   = 'device-status unlocked';
        statusEl.innerText   = '🔓 UNLOCKED';
        btnLock.disabled     = false; // allow re-lock

        // Refresh log after 4 seconds to show the ESP32 confirmation
        setTimeout(refreshLog, 4000);

      } else if (data.status === 'lock_queued') {
        feedbackEl.className = 'door-feedback ok';
        feedbackEl.innerText = '🔒 Lock command sent!';
        statusEl.className   = 'device-status locked';
        statusEl.innerText   = '🔒 LOCKED';
        btnUnlock.disabled   = false; // allow re-unlock

      } else {
        feedbackEl.className = 'door-feedback err';
        feedbackEl.innerText = '❌ Error: ' + JSON.stringify(data);
        btnUnlock.disabled   = false;
        btnLock.disabled     = false;
      }
    })
    .catch(err => {
      feedbackEl.className = 'door-feedback err';
      feedbackEl.innerText = '❌ Network error. Check XAMPP is running.';
      btnUnlock.disabled   = false;
      btnLock.disabled     = false;
    });
  }

  // ── Auto-refresh access log every 5 seconds ──────────
  let logPaused = false;

  function toggleLogPause() {
    logPaused = !logPaused;
    document.getElementById('pauseBtn').innerText
      = logPaused ? 'Resume' : 'Pause/Resume';
  }

  function refreshLog() {
    if (logPaused) return;
    fetch('door_control.php?refresh_log=1')
      .then(r => r.json())
      .then(data => {
        if (!data.logs) return;
        const el = document.getElementById('logEntries');
        el.innerHTML = data.logs.map(log =>
          `<div class="log-entry">
            <span class="log-time">[${log.time}]</span>
            Lab ${log.lab_id}: ${log.action}
            ${log.user_name ? '— ' + log.user_name : ''}
            <span style="color:#475569;font-size:0.7em;">[${log.method || ''}]</span>
          </div>`
        ).join('');
      })
      .catch(() => {}); // silently fail
  }

  setInterval(refreshLog, 5000);
  </script>
</body>
</html>
<?php $conn->close(); ?>