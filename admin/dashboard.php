<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Get statistics
$stats = [];

// Total Orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['total'];

// Total Sales
$result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
$stats['total_sales'] = $result->fetch_assoc()['total'] ?? 0;

// Pending Orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $result->fetch_assoc()['total'];

// Low Stock Products
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 10");
$stats['low_stock'] = $result->fetch_assoc()['total'];

// Recent Orders
$recentOrders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");

// Unread Notifications
$result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE is_read = 0 AND user_id = {$_SESSION['user_id']}");
$stats['unread_notifications'] = $result->fetch_assoc()['total'];

$current_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-800 text-white flex-shrink-0 hidden md:block">
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="p-6 border-b border-blue-700">
                    <a href="../index.php" class="text-xl font-bold flex items-center space-x-2">
                        <span class="text-2xl">🔧</span>
                        <span>Danicop Hardware</span>
                    </a>
                    <p class="text-blue-200 text-sm mt-1">Admin Panel</p>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'dashboard' ? 'bg-blue-700' : 'hover:bg-blue-700' ?> transition-colors">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="products.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-box w-5"></i>
                        <span>Products</span>
                    </a>
                    <a href="orders.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-shopping-cart w-5"></i>
                        <span>Orders</span>
                    </a>
                    <a href="reports.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span>Reports</span>
                    </a>
                    <?php if (getUserRole() === 'superadmin'): ?>
                    <a href="users.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-users w-5"></i>
                        <span>Manage Staff</span>
                    </a>
                    <?php endif; ?>
                    <a href="notifications.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors relative">
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
                <div class="p-4 border-t border-blue-700">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-blue-700 rounded-full flex items-center justify-center">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                            <p class="text-xs text-blue-200 truncate"><?= ucfirst($_SESSION['role']) ?></p>
                        </div>
                    </div>
                    <a href="../index.php" class="block w-full text-center px-4 py-2 bg-blue-700 rounded-lg hover:bg-blue-600 transition-colors mb-2">
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
        <aside x-show="sidebarOpen" x-cloak x-transition class="fixed inset-y-0 left-0 w-64 bg-blue-800 text-white z-50 md:hidden">
            <div class="h-full flex flex-col">
                <div class="p-6 border-b border-blue-700 flex items-center justify-between">
                    <a href="../index.php" class="text-xl font-bold flex items-center space-x-2">
                        <span class="text-2xl">🔧</span>
                        <span>Danicop Hardware</span>
                    </a>
                    <button @click="sidebarOpen = false" class="text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-blue-700">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="products.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-box w-5"></i>
                        <span>Products</span>
                    </a>
                    <a href="orders.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-shopping-cart w-5"></i>
                        <span>Orders</span>
                    </a>
                    <a href="reports.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span>Reports</span>
                    </a>
                    <?php if (getUserRole() === 'superadmin'): ?>
                    <a href="users.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-users w-5"></i>
                        <span>Manage Staff</span>
                    </a>
                    <?php endif; ?>
                    <a href="notifications.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-700 relative">
                        <i class="fas fa-bell w-5"></i>
                        <span>Notifications</span>
                        <?php if ($stats['unread_notifications'] > 0): ?>
                            <span class="absolute right-4 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                <?= $stats['unread_notifications'] ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </nav>
                <div class="p-4 border-t border-blue-700">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-blue-700 rounded-full flex items-center justify-center">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                            <p class="text-xs text-blue-200"><?= ucfirst($_SESSION['role']) ?></p>
                        </div>
                    </div>
                    <a href="../index.php" class="block w-full text-center px-4 py-2 bg-blue-700 rounded-lg hover:bg-blue-600 mb-2">
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
                    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600 hidden sm:block">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Orders</p>
                                <p class="text-3xl font-bold text-blue-600"><?= $stats['total_orders'] ?></p>
                            </div>
                            <i class="fas fa-shopping-cart text-4xl text-blue-200"></i>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Sales</p>
                                <p class="text-3xl font-bold text-green-600">₱<?= number_format($stats['total_sales'], 2) ?></p>
                            </div>
                            <i class="fas fa-money-bill-wave text-4xl text-green-200"></i>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Pending Orders</p>
                                <p class="text-3xl font-bold text-yellow-600"><?= $stats['pending_orders'] ?></p>
                            </div>
                            <i class="fas fa-clock text-4xl text-yellow-200"></i>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Low Stock Items</p>
                                <p class="text-3xl font-bold text-red-600"><?= $stats['low_stock'] ?></p>
                            </div>
                            <i class="fas fa-exclamation-triangle text-4xl text-red-200"></i>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <a href="products.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <i class="fas fa-box text-4xl text-blue-600 mb-3"></i>
                        <h3 class="text-xl font-bold">Manage Products</h3>
                        <p class="text-gray-600">Add, edit, or delete products</p>
                    </a>
                    
                    <a href="orders.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <i class="fas fa-list-alt text-4xl text-green-600 mb-3"></i>
                        <h3 class="text-xl font-bold">Manage Orders</h3>
                        <p class="text-gray-600">View and update order status</p>
                    </a>
                    
                    <a href="reports.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <i class="fas fa-chart-bar text-4xl text-purple-600 mb-3"></i>
                        <h3 class="text-xl font-bold">Sales Reports</h3>
                        <p class="text-gray-600">View sales analytics</p>
                    </a>
                    
                    <?php if (getUserRole() === 'superadmin'): ?>
                    <a href="users.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <i class="fas fa-users text-4xl text-indigo-600 mb-3"></i>
                        <h3 class="text-xl font-bold">Manage Staff</h3>
                        <p class="text-gray-600">Add or remove staff accounts</p>
                    </a>
                    <?php endif; ?>
                    
                    <a href="notifications.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow relative">
                        <?php if ($stats['unread_notifications'] > 0): ?>
                            <span class="absolute top-4 right-4 bg-red-500 text-white text-xs rounded-full h-6 w-6 flex items-center justify-center">
                                <?= $stats['unread_notifications'] ?>
                            </span>
                        <?php endif; ?>
                        <i class="fas fa-bell text-4xl text-yellow-600 mb-3"></i>
                        <h3 class="text-xl font-bold">Notifications</h3>
                        <p class="text-gray-600">View system notifications</p>
                    </a>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-4">Recent Orders</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2">Order #</th>
                                    <th class="text-left py-2">Customer</th>
                                    <th class="text-left py-2">Amount</th>
                                    <th class="text-left py-2">Status</th>
                                    <th class="text-left py-2">Date</th>
                                    <th class="text-left py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentOrders->num_rows > 0): ?>
                                    <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                        <?php
                                        $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                                        $stmt->bind_param("i", $order['user_id']);
                                        $stmt->execute();
                                        $user = $stmt->get_result()->fetch_assoc();
                                        ?>
                                        <tr class="border-b">
                                            <td class="py-2"><?= htmlspecialchars($order['order_number']) ?></td>
                                            <td class="py-2"><?= htmlspecialchars($user['name'] ?? 'Guest') ?></td>
                                            <td class="py-2">₱<?= number_format($order['total_amount'], 2) ?></td>
                                            <td class="py-2">
                                                <span class="px-2 py-1 rounded text-sm
                                                    <?php
                                                    switch($order['status']) {
                                                        case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                        case 'confirmed': echo 'bg-blue-100 text-blue-800'; break;
                                                        case 'completed': echo 'bg-green-100 text-green-800'; break;
                                                        case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                        default: echo 'bg-gray-100 text-gray-800';
                                                    }
                                                    ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                                </span>
                                            </td>
                                            <td class="py-2"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td class="py-2">
                                                <a href="order_details.php?id=<?= $order['id'] ?>" class="text-blue-600 hover:underline">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-gray-500">No orders yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
