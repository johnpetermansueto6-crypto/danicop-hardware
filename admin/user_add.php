<?php
require_once '../includes/config.php';

if (!isLoggedIn() || getUserRole() !== 'superadmin') {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'staff');
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
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

            // Limit role to allowed values for safety
            if (!in_array($role, ['staff', 'superadmin'], true)) {
                $role = 'staff';
            }
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
            
            if ($stmt->execute()) {
                // Use flash message + redirect back to Manage Staff
                $_SESSION['flash_success'] = 'Staff member added successfully.';
                redirect('users.php');
            } else {
                $error = 'Failed to add staff member';
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
    <title>Add Staff - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="../index.php" class="text-xl font-bold">🔧 Danicop Hardware</a>
                <div class="flex items-center space-x-4">
                    <a href="users.php" class="hover:underline">Back to Staff</a>
                    <a href="../auth/logout.php" class="hover:underline">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <h1 class="text-3xl font-bold mb-6">Add Staff Member</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="user_add.php" class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Full Name *</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Email *</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Password *</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Role *</label>
                <select name="role" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="staff" <?= ($_POST['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="superadmin" <?= ($_POST['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
            </div>
            
            <div class="flex gap-4">
                <a href="users.php" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 text-center">
                    Cancel
                </a>
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                    Add Staff
                </button>
            </div>
        </form>
    </div>
</body>
</html>

