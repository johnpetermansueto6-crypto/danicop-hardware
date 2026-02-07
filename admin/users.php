<?php
require_once '../includes/config.php';

if (!isLoggedIn() || getUserRole() !== 'superadmin') {
    redirect('../index.php');
}

$error = '';
$success = '';

// Flash success from add-staff page
if (!empty($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = 'User deleted successfully';
        } else {
            $error = 'Failed to delete user';
        }
    } else {
        $error = 'Cannot delete your own account';
    }
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="../index.php" class="text-xl font-bold">🔧 Danicop Hardware</a>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <a href="../auth/logout.php" class="hover:underline">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Manage Staff</h1>
            <a href="user_add.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus"></i> Add Staff
            </a>
        </div>
        
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
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-left py-3 px-4">Name</th>
                            <th class="text-left py-3 px-4">Email</th>
                            <th class="text-left py-3 px-4">Role</th>
                            <th class="text-left py-3 px-4">Created</th>
                            <th class="text-left py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($user['name']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-sm
                                            <?php
                                            switch($user['role']) {
                                                case 'superadmin': echo 'bg-red-100 text-red-800'; break;
                                                case 'staff': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'customer': echo 'bg-gray-100 text-gray-800'; break;
                                            }
                                            ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <td class="py-3 px-4">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="?delete=<?= $user['id'] ?>" 
                                               onclick="return confirm('Are you sure you want to delete this user?')"
                                               class="text-red-600 hover:underline">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">Current User</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

