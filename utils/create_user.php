<?php
require_once '../includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'customer');
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
            
            if ($stmt->execute()) {
                $success = "User created successfully! Role: " . ucfirst($role);
                // Clear form
                $_POST = [];
            } else {
                $error = 'Failed to create user: ' . $conn->error;
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
    <title>Create New User - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="../index.php" class="text-xl font-bold">🔧 Danicop Hardware</a>
                <div class="flex items-center space-x-4">
                    <a href="../index.php" class="hover:underline">Home</a>
                    <?php if (isLoggedIn() && isAdmin()): ?>
                        <a href="../admin/dashboard.php" class="hover:underline">Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <h1 class="text-3xl font-bold mb-6">Create New User</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="POST" action="create_user.php">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">
                        Full Name *
                    </label>
                    <input type="text" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="Enter full name">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">
                        Email *
                    </label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="user@example.com">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">
                        Password *
                    </label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Minimum 6 characters">
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">
                        Confirm Password *
                    </label>
                    <input type="password" name="confirm_password" required minlength="6"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Re-enter password">
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-bold mb-2">
                        User Role *
                    </label>
                    <select name="role" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="customer" <?= ($_POST['role'] ?? 'customer') === 'customer' ? 'selected' : '' ?>>Customer</option>
                        <option value="staff" <?= ($_POST['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
                        <option value="superadmin" <?= ($_POST['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <strong>Customer:</strong> Can browse and place orders<br>
                        <strong>Staff:</strong> Can manage orders and products<br>
                        <strong>Super Admin:</strong> Full system access including user management
                    </p>
                </div>
                
                <div class="flex gap-4">
                <a href="../index.php" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 text-center">
                    Cancel
                </a>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 font-semibold">
                        <i class="fas fa-user-plus"></i> Create User
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Quick Links -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-bold text-blue-800 mb-2">Quick Links:</h3>
            <ul class="space-y-1 text-sm text-blue-700">
                <li><a href="login.php" class="hover:underline"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="register.php" class="hover:underline"><i class="fas fa-user-plus"></i> Customer Registration</a></li>
                <?php if (isLoggedIn() && isAdmin()): ?>
                    <li><a href="admin/users.php" class="hover:underline"><i class="fas fa-users"></i> Manage Users (Admin Panel)</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>

