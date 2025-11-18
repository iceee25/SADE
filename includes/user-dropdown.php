<?php
$current_user_role = strtolower($_SESSION['user_role'] ?? 'guest');
$display_role = ($current_user_role === 'technician') ? 'Faculty' : 'Technicians';
$link_role = ($current_user_role === 'technician') ? 'faculty' : 'technician';
?>
<div class="user-dropdown-wrapper">
    <button class="user-dropdown-btn" onclick="toggleUserDropdown()">
        <span class="user-role-text"><?= $display_role ?></span>
        <i class="fas fa-chevron-down"></i>
    </button>
    <div id="userDropdownMenu" class="user-dropdown-menu">
        <!-- Only show the opposite role option that redirects to signin -->
        <a href="../public/signin.php" class="dropdown-item">
            <i class="fas fa-exchange-alt"></i>
            <?= $display_role ?>
        </a>
        <a href="../public/logout.php" class="dropdown-item">
            <i class="fas fa-sign-out-alt"></i>
            Logout
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
        min-width: 180px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: none;
        z-index: 1000;
        margin-top: 8px;
    }

    .user-dropdown-menu.active {
        display: block;
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
