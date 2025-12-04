<?php
$current_user_role = strtolower($_SESSION['user_role'] ?? 'guest');
$current_user_name = $_SESSION['user_name'] ?? 'User';

// Determine what to show in dropdown
$display_role = ucfirst($current_user_role);
?>
<div class="user-dropdown-wrapper">
    <!-- Updated button to show current user instead of opposite role -->
    <button class="user-dropdown-btn" onclick="toggleUserDropdown()">
        <span class="user-role-text"><?= htmlspecialchars($current_user_name) ?> (<?= htmlspecialchars($display_role) ?>)</span>
        <i class="fas fa-chevron-down"></i>
    </button>
    <div id="userDropdownMenu" class="user-dropdown-menu">
        <!-- Show current logged in user info -->
        <div class="dropdown-user-info">
            <strong><?= htmlspecialchars($current_user_name) ?></strong>
            <small><?= htmlspecialchars($display_role) ?></small>
        </div>
        <hr style="margin: 0; border: none; border-top: 1px solid #e5e7eb;">
        
        <!-- Add logout button so users can sign in as a different role -->
        <?php if ($current_user_role === 'technician'): ?>
            <a href="switch-role.php?role=faculty" class="dropdown-item">
                <i class="fas fa-exchange-alt"></i>
                Switch to Faculty
            </a>
        <?php endif; ?>
        
        <a href="logout.php" class="dropdown-item">
            <i class="fas fa-sign-out-alt"></i>
            Technician
        </a>
    </div>
</div>

<style>
    .user-dropdown-wrapper {
        position: relative;
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
    }

    .user-dropdown-btn:hover {
        background: #8b0000;
    }

    .user-role-text {
        font-size: 14px;
        color: white;
    }

    .user-dropdown-menu {
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        min-width: 200px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: none;
        z-index: 1000;
        margin-top: 8px;
    }

    .user-dropdown-menu.active {
        display: block;
    }

    .dropdown-user-info {
        padding: 12px 16px;
        text-align: left;
    }

    .dropdown-user-info strong {
        display: block;
        color: #333;
        font-size: 14px;
    }

    .dropdown-user-info small {
        display: block;
        color: #999;
        font-size: 12px;
        margin-top: 2px;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        color: #333;
        text-decoration: none;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }

    .dropdown-item:last-child {
        border-bottom: none;
    }

    .dropdown-item:hover {
        background: #f9f9f9;
        color: #b30000;
    }

    .dropdown-item i {
        width: 16px;
    }
</style>

<script>
    function toggleUserDropdown() {
        const menu = document.getElementById('userDropdownMenu');
        menu.classList.toggle('active');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const wrapper = document.querySelector('.user-dropdown-wrapper');
        if (!wrapper || !wrapper.contains(e.target)) {
            const menu = document.getElementById('userDropdownMenu');
            if (menu) {
                menu.classList.remove('active');
            }
        }
    });
</script>
