<?php
$current_user_role = strtolower($_SESSION['user_role'] ?? 'guest');
$current_user_name = $_SESSION['user_name'] ?? 'User';

// Determine what to show in dropdown
$display_role = ucfirst($current_user_role);
?>
<div class="user-dropdown-wrapper">
    <!-- Show role switch button based on current role -->
    <?php if ($current_user_role === 'technician'): ?>
        <a href="switch-role.php?role=faculty" class="user-dropdown-btn">
            <i class="fas fa-exchange-alt"></i>
            <span class="user-role-text">Switch to Faculty</span>
        </a>
    <?php elseif ($current_user_role === 'faculty'): ?>
        <a href="switch-role.php?role=technician" class="user-dropdown-btn">
            <i class="fas fa-user-shield"></i>
            <span class="user-role-text">Switch to Technician</span>
        </a>
    <?php endif; ?>
</div>

<style>
    .user-dropdown-wrapper {
        display: inline-block;
    }

    .user-dropdown-btn {
        background: #b30000;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s;
        color: white;
        font-size: 14px;
        text-decoration: none;
    }

    .user-dropdown-btn:hover {
        background: #8b0000;
    }

    .user-role-text {
        font-size: 14px;
        color: white;
    }
</style>
