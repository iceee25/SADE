<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADE | Secure Access Door Entry</title>
    <link href="../assets/css/main-style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo-container">
                <img src="../assets/images/sade-logo.png" alt="SADE Logo" class="logo">
            </div>
            <!-- Store selected role in hidden field to pass to process_login.php -->
            <form action="process_login.php" method="POST">
                <input type="hidden" name="role" value="<?= htmlspecialchars($_GET['role'] ?? 'technician') ?>">
                <label for="pin">PIN</label>
                <input type="password" id="pin" name="pin" placeholder="Enter your PIN" required>
                
                <button type="submit" class="btn-submit">Sign In</button>
            </form>
            <?php if (isset($_GET['error'])): ?>
                <p class="error-message"><?= htmlspecialchars($_GET['error']) ?></p>
            <?php endif; ?>
            <!-- Add back link to role selection -->
            <p style="margin-top: 20px; text-align: center;">
                <a href="role-selection.php" style="color: #0066cc; text-decoration: none;">Change Role</a>
            </p>
        </div>
    </div>
</body>
</html>
