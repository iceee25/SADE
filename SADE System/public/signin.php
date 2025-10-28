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
            <form action="process.php" method="POST">
                <label for="pin">PIN</label>
                <input type="password" id="pin" name="pin" placeholder="Enter your PIN" required>
                
                <button type="submit" class="btn-submit">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>
