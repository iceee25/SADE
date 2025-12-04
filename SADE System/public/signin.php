<?php
session_start();

// This allows users to sign in as a different role after logging out
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADE</title>
    <link rel="icon" type="image/png" href="../assets/images/sade-logo.png">
    <link href="../assets/css/main-style.css" rel="stylesheet">
    <link href="../assets/css/signin.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo-container">
                <img src="../assets/images/sade-logo.png" alt="SADE Logo" class="logo">
            </div>
            
            <!-- Single PIN login form only -->
            <form action="process_login.php" method="POST">
                <input type="hidden" name="role" value="technician">
                <label for="pin">PIN</label>
                <input type="password" id="pin" name="pin" placeholder="Enter your PIN" required>
                <button type="submit" class="btn-submit">Sign In</button>
            </form>

            <?php if (isset($_GET['error'])): ?>
                <p class="error-message"><?= htmlspecialchars($_GET['error']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
