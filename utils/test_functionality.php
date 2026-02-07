<?php
/**
 * Functionality Test Page
 * This page helps verify all user roles and features are working
 */
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Functionality Test - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">System Functionality Test</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Database Connection -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Database Connection</h2>
                <?php
                try {
                    $test = $conn->query("SELECT 1");
                    echo '<p class="text-green-600"><i class="fas fa-check-circle"></i> Connected</p>';
                } catch (Exception $e) {
                    echo '<p class="text-red-600"><i class="fas fa-times-circle"></i> Error: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>
            
            <!-- Tables Check -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Database Tables</h2>
                <?php
                $tables = ['users', 'products', 'orders', 'order_items', 'notifications'];
                $allExist = true;
                foreach ($tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result->num_rows === 0) {
                        $allExist = false;
                        echo '<p class="text-red-600"><i class="fas fa-times-circle"></i> Missing: ' . $table . '</p>';
                    }
                }
                if ($allExist) {
                    echo '<p class="text-green-600"><i class="fas fa-check-circle"></i> All tables exist</p>';
                }
                ?>
            </div>
            
            <!-- User Count -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Users</h2>
                <?php
                $result = $conn->query("SELECT COUNT(*) as total, role FROM users GROUP BY role");
                while ($row = $result->fetch_assoc()) {
                    echo '<p>' . ucfirst($row['role']) . ': ' . $row['total'] . '</p>';
                }
                ?>
            </div>
            
            <!-- Product Count -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Products</h2>
                <?php
                $result = $conn->query("SELECT COUNT(*) as total FROM products");
                $total = $result->fetch_assoc()['total'];
                echo '<p>Total Products: ' . $total . '</p>';
                
                $result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 10");
                $lowStock = $result->fetch_assoc()['total'];
                echo '<p class="text-red-600">Low Stock: ' . $lowStock . '</p>';
                ?>
            </div>
            
            <!-- Orders Count -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Orders</h2>
                <?php
                $result = $conn->query("SELECT COUNT(*) as total FROM orders");
                $total = $result->fetch_assoc()['total'];
                echo '<p>Total Orders: ' . $total . '</p>';
                
                $result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
                $pending = $result->fetch_assoc()['total'];
                echo '<p>Pending: ' . $pending . '</p>';
                ?>
            </div>
            
            <!-- Session Status -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Session Status</h2>
                <?php if (isLoggedIn()): ?>
                    <p class="text-green-600"><i class="fas fa-check-circle"></i> Logged In</p>
                    <p>User: <?= htmlspecialchars($_SESSION['user_name'] ?? 'N/A') ?></p>
                    <p>Role: <?= htmlspecialchars(getUserRole()) ?></p>
                <?php else: ?>
                    <p class="text-gray-600"><i class="fas fa-user"></i> Not Logged In</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Feature Checklist -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-4">Feature Checklist</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="font-bold mb-2">Customer Features</h3>
                    <ul class="space-y-1">
                        <li><i class="fas fa-check text-green-600"></i> Browse products (no login required)</li>
                        <li><i class="fas fa-check text-green-600"></i> Search & filter products</li>
                        <li><i class="fas fa-check text-green-600"></i> Add to cart</li>
                        <li><i class="fas fa-check text-green-600"></i> Register account</li>
                        <li><i class="fas fa-check text-green-600"></i> Login</li>
                        <li><i class="fas fa-check text-green-600"></i> Place orders</li>
                        <li><i class="fas fa-check text-green-600"></i> View order history</li>
                        <li><i class="fas fa-check text-green-600"></i> Update profile</li>
                        <li><i class="fas fa-check text-green-600"></i> Change password</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold mb-2">Admin/Staff Features</h3>
                    <ul class="space-y-1">
                        <li><i class="fas fa-check text-green-600"></i> Admin dashboard</li>
                        <li><i class="fas fa-check text-green-600"></i> Manage products</li>
                        <li><i class="fas fa-check text-green-600"></i> Manage orders</li>
                        <li><i class="fas fa-check text-green-600"></i> Update order status</li>
                        <li><i class="fas fa-check text-green-600"></i> View sales reports</li>
                        <li><i class="fas fa-check text-green-600"></i> View notifications</li>
                        <li><i class="fas fa-check text-green-600"></i> Low stock alerts</li>
                        <li><i class="fas fa-check text-green-600"></i> Manage staff (Super Admin only)</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-bold text-blue-800 mb-4">Quick Test Links:</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="index.php" class="bg-white px-4 py-2 rounded hover:bg-blue-100 text-center">Home</a>
                <a href="login.php" class="bg-white px-4 py-2 rounded hover:bg-blue-100 text-center">Login</a>
                <a href="register.php" class="bg-white px-4 py-2 rounded hover:bg-blue-100 text-center">Register</a>
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php" class="bg-white px-4 py-2 rounded hover:bg-blue-100 text-center">Profile</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/dashboard.php" class="bg-white px-4 py-2 rounded hover:bg-blue-100 text-center">Dashboard</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

