<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$error = '';
$success = '';

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($name) || empty($email)) {
        $error = 'Please fill in all fields';
    } else {
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already taken by another user';
        } else {
            // Mark profile as completed when user saves their basic info
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, profile_completed = 1 WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $user['name'] = $name;
                $user['email'] = $email;
                $user['profile_completed'] = 1;
                $_SESSION['profile_completed'] = 1;
                $success = 'Profile updated successfully';
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all password fields';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = 'Password changed successfully';
            } else {
                $error = 'Failed to change password';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

// Get statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orderCount = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE user_id = ? AND status != 'cancelled'");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$totalSpent = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ? AND status = 'pending'");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pendingOrders = $stmt->get_result()->fetch_assoc()['total'];

$current_page = 'profile';
$page_title = 'My Profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
<?php include '../includes/customer_topbar.php'; ?>

<!-- Main Content -->
<div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">My Profile</h1>
            <p class="text-gray-600 mb-2">Manage your account settings and view your statistics</p>
            <?php if (($user['auth_provider'] ?? '') === 'google' && empty($user['profile_completed'])): ?>
                <div class="mt-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 px-4 py-3 rounded-lg max-w-2xl">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle mr-2 mt-1"></i>
                        <p class="text-sm">
                            Before you can proceed to checkout, please review and save your profile details below to complete your account.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-6 animate-fade-in">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg mb-6 animate-fade-in">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Account Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl shadow-xl p-6 card-hover animate-fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm mb-1">Total Orders</p>
                        <p class="text-4xl font-bold"><?= $orderCount ?></p>
                    </div>
                    <i class="fas fa-shopping-bag text-5xl opacity-30"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-2xl shadow-xl p-6 card-hover animate-fade-in" style="animation-delay: 0.1s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm mb-1">Total Spent</p>
                        <p class="text-3xl font-bold">₱<?= number_format($totalSpent, 2) ?></p>
                    </div>
                    <i class="fas fa-money-bill-wave text-5xl opacity-30"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white rounded-2xl shadow-xl p-6 card-hover animate-fade-in" style="animation-delay: 0.2s;">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-100 text-sm mb-1">Pending Orders</p>
                        <p class="text-4xl font-bold"><?= $pendingOrders ?></p>
                    </div>
                    <i class="fas fa-clock text-5xl opacity-30"></i>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Profile Information -->
            <div class="bg-white rounded-2xl shadow-xl p-6 card-hover animate-fade-in">
                <div class="flex items-center mb-6">
                    <div class="p-3 bg-blue-100 rounded-full mr-4">
                        <i class="fas fa-user text-blue-600 text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Profile Information</h2>
                </div>
                <form method="POST" action="profile.php" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Full Name
                        </label>
                        <input type="text" name="name" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                               value="<?= htmlspecialchars($user['name']) ?>">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-envelope mr-2 text-blue-600"></i>Email Address
                        </label>
                        <input type="email" name="email" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                               value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Role</label>
                            <input type="text" disabled
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600"
                                   value="<?= ucfirst($user['role']) ?>">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Member Since</label>
                            <input type="text" disabled
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl bg-gray-50 text-gray-600"
                                   value="<?= date('M Y', strtotime($user['created_at'])) ?>">
                        </div>
                    </div>
                    
                    <button type="submit" name="update_profile" 
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                        <i class="fas fa-save mr-2"></i> Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="bg-white rounded-2xl shadow-xl p-6 card-hover animate-fade-in" style="animation-delay: 0.2s;">
                <div class="flex items-center mb-6">
                    <div class="p-3 bg-green-100 rounded-full mr-4">
                        <i class="fas fa-lock text-green-600 text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Change Password</h2>
                </div>
                <form method="POST" action="profile.php" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-key mr-2 text-green-600"></i>Current Password
                        </label>
                        <input type="password" name="current_password" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-lock mr-2 text-green-600"></i>New Password
                        </label>
                        <input type="password" name="new_password" required minlength="6"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300">
                        <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i>Minimum 6 characters</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-lock mr-2 text-green-600"></i>Confirm New Password
                        </label>
                        <input type="password" name="confirm_password" required minlength="6"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300">
                    </div>
                    
                    <button type="submit" name="change_password" 
                            class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                        <i class="fas fa-key mr-2"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
</div>
</body>
</html>
