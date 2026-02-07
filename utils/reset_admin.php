<?php
require_once '../includes/config.php';

// This script resets the admin password to 'admin123'
// DELETE THIS FILE AFTER USE FOR SECURITY

$email = 'admin@hardware.com';
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Check if admin exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing admin
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);
    
    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✅ Admin password reset successfully!</h2>";
        echo "<p><strong>Email:</strong> admin@hardware.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p style='color: red;'><strong>⚠️ IMPORTANT: Delete this file (reset_admin.php) for security!</strong></p>";
    } else {
        echo "<h2 style='color: red;'>❌ Error updating password</h2>";
        echo "<p>" . $conn->error . "</p>";
    }
} else {
    // Create admin if doesn't exist
    $name = 'Super Admin';
    $role = 'superadmin';
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✅ Admin account created successfully!</h2>";
        echo "<p><strong>Email:</strong> admin@hardware.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p style='color: red;'><strong>⚠️ IMPORTANT: Delete this file (reset_admin.php) for security!</strong></p>";
    } else {
        echo "<h2 style='color: red;'>❌ Error creating admin</h2>";
        echo "<p>" . $conn->error . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='../auth/login.php'>Go to Login Page</a></p>";
?>

