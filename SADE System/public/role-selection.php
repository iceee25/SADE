<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SADE | Select Your Role</title>
    <link href="../assets/css/main-style.css" rel="stylesheet">
    <style>
        .role-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .role-box {
            background: white;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
        }
        .logo-container {
            margin-bottom: 30px;
        }
        .logo-container img {
            max-width: 200px;
            height: auto;
        }
        .role-title {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        .role-subtitle {
            color: #666;
            margin-bottom: 40px;
            font-size: 14px;
        }
        .role-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .role-btn {
            background: #c00;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .role-btn:hover {
            background: #a00;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .role-btn.faculty {
            background: #0066cc;
        }
        .role-btn.faculty:hover {
            background: #0052a3;
        }
    </style>
</head>
<body>
    <div class="role-container">
        <div class="role-box">
            <div class="logo-container">
                <img src="../assets/images/sade-logo.png" alt="SADE Logo">
            </div>
            <h2 class="role-title">Select Your Role</h2>
            <p class="role-subtitle">Choose how you want to access the system</p>
            <div class="role-buttons">
                <a href="signin.php?role=technician" class="role-btn">Technician Access</a>
                <a href="signin.php?role=faculty" class="role-btn faculty">Faculty/Teacher Access</a>
            </div>
        </div>
    </div>
</body>
</html>
