<?php
/**
 * Create Super Admin User
 * 
 * This script allows you to create a new superadmin user.
 * DELETE THIS FILE AFTER USE FOR SECURITY!
 */

require_once __DIR__ . '/../includes/config.php';

// Only allow access if not in production or add IP restriction
// For security, you may want to restrict this to localhost only

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing user to superadmin
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, password = ?, role = 'superadmin', email_verified = 1 WHERE email = ?");
            $stmt->bind_param("sss", $name, $hashed_password, $email);
            
            if ($stmt->execute()) {
                $message = "✅ User updated to superadmin successfully!";
            } else {
                $error = "Error updating user: " . $conn->error;
            }
        } else {
            // Create new superadmin
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'superadmin';
            $email_verified = 1; // Superadmins don't need email verification
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, email_verified) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $name, $email, $hashed_password, $role, $email_verified);
            
            if ($stmt->execute()) {
                $message = "✅ Superadmin created successfully!";
            } else {
                $error = "Error creating superadmin: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Super Admin - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8">
        <div class="text-center mb-6">
            <div class="inline-block p-4 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full mb-4">
                <i class="fas fa-user-shield text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Create Super Admin</h1>
            <p class="text-gray-600">Add a new superadmin user to the system</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?= htmlspecialchars($message) ?></span>
                </div>
                <?php if (strpos($message, 'successfully') !== false): ?>
                    <div class="mt-4 p-4 bg-white rounded border border-green-200">
                        <p class="font-semibold mb-2">Login Credentials:</p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
                        <p><strong>Password:</strong> <?= htmlspecialchars($password) ?></p>
                        <p class="text-sm text-red-600 mt-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Important:</strong> Save these credentials and delete this file for security!
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="create_superadmin.php" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-user mr-2 text-blue-600"></i>Full Name
                </label>
                <input type="text" name="name" required
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Super Admin"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-envelope mr-2 text-blue-600"></i>Email Address
                </label>
                <input type="email" name="email" required
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="admin@hardware.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-lock mr-2 text-blue-600"></i>Password
                </label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Minimum 6 characters">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-lock mr-2 text-blue-600"></i>Confirm Password
                </label>
                <input type="password" name="confirm_password" required minlength="6"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Re-enter password">
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                <i class="fas fa-user-plus mr-2"></i> Create Super Admin
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 px-4 py-3 rounded-lg">
                <p class="text-sm">
                    <i class="fas fa-shield-alt mr-2"></i>
                    <strong>Security Notice:</strong> Delete this file after creating the superadmin account.
                </p>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="../index.php" class="text-blue-600 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-2"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>

