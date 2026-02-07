<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
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
?>
<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-emerald-500 hover:shadow-xl transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Total Orders</p>
                <p class="text-3xl font-bold text-emerald-600"><?= $stats['total_orders'] ?></p>
            </div>
            <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center">
                <i class="fas fa-shopping-cart text-2xl text-emerald-500"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Total Sales</p>
                <p class="text-3xl font-bold text-green-600">₱<?= number_format($stats['total_sales'], 2) ?></p>
            </div>
            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-2xl text-green-500"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500 hover:shadow-xl transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Pending Orders</p>
                <p class="text-3xl font-bold text-yellow-600"><?= $stats['pending_orders'] ?></p>
            </div>
            <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-2xl text-yellow-500"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500 hover:shadow-xl transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm">Low Stock Items</p>
                <p class="text-3xl font-bold text-red-600"><?= $stats['low_stock'] ?></p>
            </div>
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-2xl text-red-500"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <a href="#" onclick="loadPage('products'); return false;" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-gray-100 group">
        <div class="w-16 h-16 bg-emerald-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-emerald-200 transition-colors">
            <i class="fas fa-box text-3xl text-emerald-600"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800">Manage Products</h3>
        <p class="text-gray-600">Add, edit, or delete products</p>
    </a>
    
    <a href="#" onclick="loadPage('orders'); return false;" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-gray-100 group">
        <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-green-200 transition-colors">
            <i class="fas fa-list-alt text-3xl text-green-600"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800">Manage Orders</h3>
        <p class="text-gray-600">View and update order status</p>
    </a>
    
    <a href="#" onclick="loadPage('reports'); return false;" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-gray-100 group">
        <div class="w-16 h-16 bg-teal-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-teal-200 transition-colors">
            <i class="fas fa-chart-bar text-3xl text-teal-600"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800">Sales Reports</h3>
        <p class="text-gray-600">View sales analytics</p>
    </a>
    
    <?php if (getUserRole() === 'superadmin'): ?>
    <a href="#" onclick="loadPage('users'); return false;" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-gray-100 group">
        <div class="w-16 h-16 bg-emerald-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-emerald-200 transition-colors">
            <i class="fas fa-users text-3xl text-emerald-600"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800">Manage Staff</h3>
        <p class="text-gray-600">Add or remove staff accounts</p>
    </a>
    <?php endif; ?>
    
    <a href="#" onclick="loadPage('notifications'); return false;" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all hover:-translate-y-1 border border-gray-100 group relative">
        <div class="w-16 h-16 bg-yellow-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-yellow-200 transition-colors">
            <i class="fas fa-bell text-3xl text-yellow-600"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800">Notifications</h3>
        <p class="text-gray-600">View system notifications</p>
    </a>
</div>

<!-- Recent Orders -->
<div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
            <i class="fas fa-receipt text-emerald-600"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Recent Orders</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600">Order #</th>
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600">Customer</th>
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600">Amount</th>
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600">Status</th>
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600">Date</th>
                    <th class="text-left py-3 px-2 text-sm font-semibold text-gray-600">Action</th>
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
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-2 font-medium text-gray-800"><?= htmlspecialchars($order['order_number']) ?></td>
                            <td class="py-3 px-2 text-gray-600"><?= htmlspecialchars($user['name'] ?? 'Guest') ?></td>
                            <td class="py-3 px-2 font-semibold text-emerald-600">₱<?= number_format($order['total_amount'], 2) ?></td>
                            <td class="py-3 px-2">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    <?php
                                    switch($order['status']) {
                                        case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'confirmed': echo 'bg-emerald-100 text-emerald-800'; break;
                                        case 'completed': echo 'bg-green-100 text-green-800'; break;
                                        case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-800';
                                    }
                                    ?>">
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                            </td>
                            <td class="py-3 px-2 text-gray-600"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                            <td class="py-3 px-2">
                                <a href="#" onclick="loadPage('order_details', {id: <?= $order['id'] ?>}); return false;" class="text-emerald-600 hover:text-emerald-700 font-semibold hover:underline">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                            <p>No orders yet</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

