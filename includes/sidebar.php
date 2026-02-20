<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_technician = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'technician';
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
        <a href="../public/dashboard.php" 
           class="menu-item <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-columns"></i>
            Dashboard
        </a>

        <a href="../public/schedule-management.php" 
           class="menu-item <?= ($current_page == 'schedule-management.php') ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list"></i>
            Schedule Management
        </a>

        <a href="../public/registered-face-ids.php" 
           class="menu-item <?= ($current_page == 'registered-face-ids.php') ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            Registered Face IDs
        </a>

        <a href="../public/logs.php" 
           class="menu-item <?= ($current_page == 'logs.php') ? 'active' : '' ?>">
            <i class="far fa-clipboard"></i>
            Logs
        </a>

        <!-- Added participant registration link for technicians only -->
        <?php if ($is_technician): ?>
            <a href="../public/participant-registration.php" 
               class="menu-item <?= ($current_page == 'participant-registration.php') ? 'active' : '' ?>">
                <i class="fas fa-user-plus"></i>
                Participant Registration
            </a>
        <?php endif; ?>

        <a href="../public/technician-panel.php" 
           class="menu-item <?= ($current_page == 'technician-panel.php') ? 'active' : '' ?>">
            <i class="fas fa-keyboard"></i>
            Technician Panel
        </a>
    </div>

    <div class="logout-section">
        <button class="logout-btn" onclick="logout()">
            <i class="fas fa-sign-in-alt"></i>
            Logout
        </button>
    </div>
</div>
