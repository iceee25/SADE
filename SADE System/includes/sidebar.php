<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_technician = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'technician';
$is_faculty = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'faculty';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>
            <img src="../assets/images/sade-logo.png" alt="Logo" width="250" height="125">
        </h2>
    </div>

    <div class="sidebar-menu">
        <!-- Schedule Management - accessible to both Faculty and Technician -->
        <a href="../public/schedule-management.php" 
           class="menu-item <?= ($current_page == 'schedule-management.php') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i>
            Schedules
        </a>

        <!-- Technician-only menu items -->
        <?php if ($is_technician): ?>
            <!-- Dashboard - Technician only -->
            <a href="../public/dashboard.php" 
               class="menu-item <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                <i class="fas fa-columns"></i>
                Dashboard
            </a>

            <!-- Alerts - Technician only -->
            <a href="../public/alerts.php" 
               class="menu-item <?= ($current_page == 'alerts.php') ? 'active' : '' ?>">
                <i class="fas fa-bell"></i>
                Alerts
            </a>

            <!-- Registered Face IDs - Technician only -->
            <a href="../public/registered-face-ids.php" 
               class="menu-item <?= ($current_page == 'registered-face-ids.php') ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                Registered Face IDs
            </a>

            <!-- Participant Registration - Technician only -->
            <a href="../public/participant-registration.php" 
               class="menu-item <?= ($current_page == 'participant-registration.php') ? 'active' : '' ?>">
                <i class="fas fa-user-plus"></i>
                Participant Registration
            </a>

            <!-- Logs - Technician only -->
            <a href="../public/logs.php" 
               class="menu-item <?= ($current_page == 'logs.php') ? 'active' : '' ?>">
                <i class="far fa-clipboard"></i>
                Logs
            </a>

            <!-- Technician Panel - Technician only -->
            <a href="../public/technician-panel.php" 
               class="menu-item <?= ($current_page == 'technician-panel.php') ? 'active' : '' ?>">
                <i class="fas fa-keyboard"></i>
                Technician Panel
            </a>
        <?php endif; ?>
    </div>

    <div class="logout-section">
        <a href="../public/logout.php" class="logout-btn">
            <i class="fas fa-sign-in-alt"></i>
            Logout
        </a>
    </div>
</div>
