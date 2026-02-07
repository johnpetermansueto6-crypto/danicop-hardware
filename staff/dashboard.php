<?php
require_once '../includes/config.php';

if (!isLoggedIn() || getUserRole() !== 'staff') {
    redirect('../index.php');
}

// Get statistics for staff
$stats = [];

// Pending Orders (incoming orders)
$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $result->fetch_assoc()['total'];

// Confirmed Orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'confirmed'");
$stats['confirmed_orders'] = $result->fetch_assoc()['total'];

// Preparing Orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status IN ('preparing', 'ready_for_pickup', 'out_for_delivery')");
$stats['preparing_orders'] = $result->fetch_assoc()['total'];

// Low Stock Products
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 10");
$stats['low_stock'] = $result->fetch_assoc()['total'];

// Recent Pending Orders
$recentOrders = $conn->query("SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.status = 'pending' ORDER BY o.created_at DESC LIMIT 5");

// Unread Notifications
$result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE is_read = 0 AND user_id = {$_SESSION['user_id']}");
$stats['unread_notifications'] = $result->fetch_assoc()['total'];

// Check for login success message
$login_success = false;
if (isset($_GET['login']) && $_GET['login'] === 'success' && isset($_SESSION['login_success'])) {
    $login_success = true;
    unset($_SESSION['login_success']);
    unset($_SESSION['login_message']);
}

$current_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

        /* Toast Notification Styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInRight 0.4s ease-out, fadeOut 0.3s ease-in 2.7s forwards;
            transform: translateX(0);
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .toast-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .toast-message {
            font-size: 14px;
            opacity: 0.95;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateX(400px);
            }
        }

        .toast-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background 0.2s;
        }

        .toast-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-green-800 text-white flex-shrink-0 hidden md:block">
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="p-6 border-b border-green-700">
                    <a href="../index.php" class="text-xl font-bold flex items-center space-x-2">
                        <span class="text-2xl">🔧</span>
                        <span>Danicop Hardware</span>
                    </a>
                    <p class="text-green-200 text-sm mt-1">Staff Panel</p>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'dashboard' ? 'bg-green-700' : 'hover:bg-green-700' ?> transition-colors">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="orders.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-shopping-cart w-5"></i>
                        <span>Orders</span>
                    </a>
                    <a href="products.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-warehouse w-5"></i>
                        <span>Update Stock</span>
                    </a>
                    <a href="notifications.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 transition-colors relative">
                        <i class="fas fa-bell w-5"></i>
                        <span>Notifications</span>
                        <?php if ($stats['unread_notifications'] > 0): ?>
                            <span class="absolute right-4 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                <?= $stats['unread_notifications'] ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </nav>
                
                <!-- User Info -->
                <div class="p-4 border-t border-green-700">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-green-700 rounded-full flex items-center justify-center">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                            <p class="text-xs text-green-200 truncate">Staff</p>
                        </div>
                    </div>
                    <a href="../index.php" class="block w-full text-center px-4 py-2 bg-green-700 rounded-lg hover:bg-green-600 transition-colors mb-2">
                        <i class="fas fa-store mr-2"></i> View Store
                    </a>
                    <a href="../auth/logout.php" class="block w-full text-center px-4 py-2 bg-red-600 rounded-lg hover:bg-red-500 transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"></div>

        <!-- Mobile Sidebar -->
        <aside x-show="sidebarOpen" x-cloak x-transition class="fixed inset-y-0 left-0 w-64 bg-green-800 text-white z-50 md:hidden">
            <div class="h-full flex flex-col">
                <div class="p-6 border-b border-green-700 flex items-center justify-between">
                    <a href="../index.php" class="text-xl font-bold flex items-center space-x-2">
                        <span class="text-2xl">🔧</span>
                        <span>Danicop Hardware</span>
                    </a>
                    <button @click="sidebarOpen = false" class="text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-green-700">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="orders.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700">
                        <i class="fas fa-shopping-cart w-5"></i>
                        <span>Orders</span>
                    </a>
                    <a href="products.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700">
                        <i class="fas fa-warehouse w-5"></i>
                        <span>Update Stock</span>
                    </a>
                    <a href="notifications.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-700 relative">
                        <i class="fas fa-bell w-5"></i>
                        <span>Notifications</span>
                        <?php if ($stats['unread_notifications'] > 0): ?>
                            <span class="absolute right-4 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                <?= $stats['unread_notifications'] ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </nav>
                <div class="p-4 border-t border-green-700">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-green-700 rounded-full flex items-center justify-center">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                            <p class="text-xs text-green-200">Staff</p>
                        </div>
                    </div>
                    <a href="../index.php" class="block w-full text-center px-4 py-2 bg-green-700 rounded-lg hover:bg-green-600 mb-2">
                        <i class="fas fa-store mr-2"></i> View Store
                    </a>
                    <a href="../auth/logout.php" class="block w-full text-center px-4 py-2 bg-red-600 rounded-lg hover:bg-red-500">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-4 py-3">
                    <button @click="sidebarOpen = true" class="md:hidden text-gray-600 hover:text-gray-900">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-2xl font-bold text-gray-800">Staff Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600 hidden sm:block">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Incoming Orders</p>
                                <p class="text-3xl font-bold text-yellow-600"><?= $stats['pending_orders'] ?></p>
                            </div>
                            <i class="fas fa-inbox text-4xl text-yellow-200"></i>
                        </div>
                        <a href="orders.php?status=pending" class="block mt-4 text-sm text-yellow-600 hover:underline">
                            View Pending Orders <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Confirmed Orders</p>
                                <p class="text-3xl font-bold text-blue-600"><?= $stats['confirmed_orders'] ?></p>
                            </div>
                            <i class="fas fa-check-circle text-4xl text-blue-200"></i>
                        </div>
                        <a href="orders.php?status=confirmed" class="block mt-4 text-sm text-blue-600 hover:underline">
                            View Confirmed <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Preparing Orders</p>
                                <p class="text-3xl font-bold text-purple-600"><?= $stats['preparing_orders'] ?></p>
                            </div>
                            <i class="fas fa-box text-4xl text-purple-200"></i>
                        </div>
                        <a href="orders.php?status=preparing" class="block mt-4 text-sm text-purple-600 hover:underline">
                            View Preparing <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Low Stock Items</p>
                                <p class="text-3xl font-bold text-red-600"><?= $stats['low_stock'] ?></p>
                            </div>
                            <i class="fas fa-exclamation-triangle text-4xl text-red-200"></i>
                        </div>
                        <a href="products.php" class="block mt-4 text-sm text-red-600 hover:underline">
                            Update Stock <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <a href="orders.php?status=pending" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center">
                            <div class="bg-yellow-100 p-4 rounded-lg mr-4">
                                <i class="fas fa-inbox text-2xl text-yellow-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">View Incoming Orders</h3>
                                <p class="text-gray-600 text-sm">Review and confirm new orders</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="products.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-4 rounded-lg mr-4">
                                <i class="fas fa-warehouse text-2xl text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">Update Stock</h3>
                                <p class="text-gray-600 text-sm">Manage product inventory</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="notifications.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center">
                            <div class="bg-green-100 p-4 rounded-lg mr-4">
                                <i class="fas fa-bell text-2xl text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">Notifications</h3>
                                <p class="text-gray-600 text-sm">View system notifications</p>
                                <?php if ($stats['unread_notifications'] > 0): ?>
                                    <span class="inline-block bg-red-500 text-white text-xs px-2 py-1 rounded-full mt-1">
                                        <?= $stats['unread_notifications'] ?> new
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Recent Pending Orders -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold">Recent Incoming Orders</h2>
                        <a href="orders.php?status=pending" class="text-blue-600 hover:underline">View All</a>
                    </div>
                    
                    <?php if ($recentOrders->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="text-left py-3 px-4">Order #</th>
                                        <th class="text-left py-3 px-4">Customer</th>
                                        <th class="text-left py-3 px-4">Amount</th>
                                        <th class="text-left py-3 px-4">Date</th>
                                        <th class="text-left py-3 px-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                        <tr class="border-b hover:bg-gray-50">
                                            <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($order['order_number']) ?></td>
                                            <td class="py-3 px-4"><?= htmlspecialchars($order['customer_name']) ?></td>
                                            <td class="py-3 px-4">₱<?= number_format($order['total_amount'], 2) ?></td>
                                            <td class="py-3 px-4"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td class="py-3 px-4">
                                                <a href="order_details.php?id=<?= $order['id'] ?>" class="text-blue-600 hover:underline">
                                                    <i class="fas fa-eye mr-1"></i> View & Confirm
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No pending orders at the moment.</p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Toast Notification System
        function showToast(type, title, message) {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            const icon = type === 'success' 
                ? '<i class="fas fa-check-circle toast-icon"></i>'
                : '<i class="fas fa-exclamation-circle toast-icon"></i>';
            
            toast.innerHTML = `
                ${icon}
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.animation = 'fadeOut 0.3s ease-in forwards';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 3000);
        }

        // Show login success if exists
        <?php if ($login_success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('success', 'Login Successful!', 'Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!');
        });
        <?php endif; ?>
    </script>
</body>
</html>
