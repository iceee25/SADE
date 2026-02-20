<?php
require_once '../includes/db_connect.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>PIN Encryption Migration</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #c00; }
        .success { color: green; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        .warning { color: orange; padding: 10px; background: #fff3cd; border-radius: 4px; margin: 10px 0; font-weight: bold; }
        .original-pin { background: #ffffcc; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h2>PIN Encryption Migration</h2>";
echo "<p>Starting migration to encrypt existing PINs in the database...</p>";
echo "<p><strong>Please save these original PINs before they are encrypted:</strong></p>";

// Get all users with PINs
$query = "SELECT id, user_id, first_name, last_name, pin FROM users WHERE user_type = 'TECHNICIAN'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $updated = 0;
    $skipped = 0;
    
    while ($user = $result->fetch_assoc()) {
        // Check if PIN is already hashed (bcrypt hashes are 60 characters long and start with $2y$)
        if (strlen($user['pin']) === 60 && substr($user['pin'], 0, 4) === '$2y$') {
            echo "<div class='info'>- PIN already encrypted for: {$user['first_name']} {$user['last_name']} (User ID: {$user['user_id']})</div>";
            $skipped++;
        } else {
            // PIN is not hashed, so hash it using bcrypt
            $originalPin = $user['pin'];
            $hashedPin = password_hash($originalPin, PASSWORD_DEFAULT);
            
            $updateStmt = $conn->prepare("UPDATE users SET pin = ? WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPin, $user['id']);
            
            if ($updateStmt->execute()) {
                echo "<div class='success'>✓ Encrypted PIN for: <strong>{$user['first_name']} {$user['last_name']}</strong> (User ID: {$user['user_id']})<br>Original PIN: <span class='original-pin'>{$originalPin}</span> → Now Encrypted</div>";
                $updated++;
            } else {
                echo "<div class='error'>✗ Failed to encrypt PIN for: {$user['first_name']} {$user['last_name']} (User ID: {$user['user_id']})</div>";
            }
            
            $updateStmt->close();
        }
    }
    
    echo "<h3>Migration Complete!</h3>";
    echo "<div class='success'>Total PINs encrypted: $updated</div>";
    echo "<div class='info'>Total PINs already encrypted: $skipped</div>";
    echo "<div class='warning'>⚠️ IMPORTANT: Please delete this file (encrypt-pins-migration.php) immediately after verifying the migration was successful!</div>";
    echo "<p><a href='technician-panel.php' style='color: #c00; text-decoration: none; font-weight: bold;'>← Go to Technician Panel</a></p>";
} else {
    echo "<div class='info'>No technician users found in database.</div>";
}

echo "</div></body></html>";

$conn->close();
?>