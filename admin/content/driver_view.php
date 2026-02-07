<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get driver details
$stmt = $conn->prepare("SELECT * FROM drivers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$driver = $stmt->get_result()->fetch_assoc();

if (!$driver) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Driver not found</div>';
    echo '<a href="#" onclick="loadPage(\'drivers\'); return false;" class="text-blue-600 hover:underline">Back to Drivers</a>';
    exit;
}

// Get driver's active deliveries
$activeStmt = $conn->prepare("
    SELECT 
        da.*,
        o.order_number,
        o.delivery_address,
        o.total_amount,
        u.name as customer_name,
        da.status as delivery_status,
        da.created_at as assigned_at
    FROM delivery_assignments da
    INNER JOIN orders o ON da.order_id = o.id
    INNER JOIN users u ON o.user_id = u.id
    WHERE da.driver_id = ? AND da.status IN ('assigned', 'picked_up', 'delivering')
    ORDER BY da.created_at DESC
");
$activeStmt->bind_param("i", $id);
$activeStmt->execute();
$activeDeliveries = $activeStmt->get_result();

// Get driver's completed deliveries
$completedStmt = $conn->prepare("
    SELECT 
        da.*,
        o.order_number,
        o.delivery_address,
        o.total_amount,
        u.name as customer_name,
        da.delivery_completed_at
    FROM delivery_assignments da
    INNER JOIN orders o ON da.order_id = o.id
    INNER JOIN users u ON o.user_id = u.id
    WHERE da.driver_id = ? AND da.status = 'delivered'
    ORDER BY da.delivery_completed_at DESC
    LIMIT 20
");
$completedStmt->bind_param("i", $id);
$completedStmt->execute();
$completedDeliveries = $completedStmt->get_result();

// Get driver statistics
$statsStmt = $conn->prepare("
    SELECT 
        COUNT(CASE WHEN da.status = 'delivered' THEN 1 END) as total_completed,
        COUNT(CASE WHEN da.status IN ('assigned', 'picked_up', 'delivering') THEN 1 END) as active_count,
        COUNT(CASE WHEN da.status = 'failed' THEN 1 END) as failed_count,
        AVG(CASE WHEN da.status = 'delivered' AND da.delivery_completed_at IS NOT NULL AND da.delivery_started_at IS NOT NULL 
            THEN TIMESTAMPDIFF(MINUTE, da.delivery_started_at, da.delivery_completed_at) END) as avg_delivery_time
    FROM delivery_assignments da
    WHERE da.driver_id = ?
");
$statsStmt->bind_param("i", $id);
$statsStmt->execute();
$driverStats = $statsStmt->get_result()->fetch_assoc();
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Driver Details</h2>
    <div class="flex gap-2">
        <button onclick="loadPage('driver_edit', {id: <?= $id ?>})" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            <i class="fas fa-edit mr-2"></i> Edit
        </button>
        <a href="#" onclick="loadPage('drivers'); return false;" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>
</div>

<!-- Driver Information -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h3 class="text-lg font-bold mb-4"><i class="fas fa-user-tie mr-2"></i>Driver Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-semibold text-gray-600">Name:</label>
            <p class="text-lg font-bold"><?= htmlspecialchars($driver['name']) ?></p>
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-600">Phone:</label>
            <p><?= htmlspecialchars($driver['phone']) ?></p>
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-600">Email:</label>
            <p><?= htmlspecialchars($driver['email'] ?? 'N/A') ?></p>
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-600">Status:</label>
            <span class="px-2 py-1 rounded text-xs font-semibold <?php
                echo match($driver['status']) {
                    'available' => 'bg-green-100 text-green-800',
                    'delivering' => 'bg-yellow-100 text-yellow-800',
                    'off_duty' => 'bg-gray-100 text-gray-800',
                    'unavailable' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
            ?>">
                <?= ucfirst(str_replace('_', ' ', $driver['status'])) ?>
            </span>
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-600">Vehicle Type:</label>
            <p><?= htmlspecialchars($driver['vehicle_type'] ?? 'N/A') ?></p>
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-600">License Number:</label>
            <p><?= htmlspecialchars($driver['license_number'] ?? 'N/A') ?></p>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="text-gray-500 text-sm">Total Deliveries</div>
        <div class="text-2xl font-bold text-blue-600"><?= $driver['total_deliveries'] ?></div>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="text-gray-500 text-sm">Completed</div>
        <div class="text-2xl font-bold text-green-600"><?= $driverStats['total_completed'] ?? 0 ?></div>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="text-gray-500 text-sm">Active Now</div>
        <div class="text-2xl font-bold text-yellow-600"><?= $driverStats['active_count'] ?? 0 ?></div>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="text-gray-500 text-sm">Failed</div>
        <div class="text-2xl font-bold text-red-600"><?= $driverStats['failed_count'] ?? 0 ?></div>
    </div>
</div>

<!-- Active Deliveries -->
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-yellow-600 text-white px-6 py-3">
        <h3 class="text-lg font-bold"><i class="fas fa-truck mr-2"></i>Active Deliveries</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left py-2 px-4">Order #</th>
                    <th class="text-left py-2 px-4">Customer</th>
                    <th class="text-left py-2 px-4">Address</th>
                    <th class="text-left py-2 px-4">Status</th>
                    <th class="text-left py-2 px-4">Assigned</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($activeDeliveries->num_rows > 0): ?>
                    <?php while ($delivery = $activeDeliveries->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 font-semibold"><?= htmlspecialchars($delivery['order_number']) ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($delivery['customer_name']) ?></td>
                            <td class="py-2 px-4 text-sm"><?= htmlspecialchars(substr($delivery['delivery_address'], 0, 50)) ?>...</td>
                            <td class="py-2 px-4">
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <?= ucfirst(str_replace('_', ' ', $delivery['delivery_status'])) ?>
                                </span>
                            </td>
                            <td class="py-2 px-4 text-sm text-gray-500">
                                <?= date('M d, Y H:i', strtotime($delivery['assigned_at'])) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500">No active deliveries</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Completed Deliveries -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-green-600 text-white px-6 py-3">
        <h3 class="text-lg font-bold"><i class="fas fa-check-circle mr-2"></i>Recent Completed Deliveries</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left py-2 px-4">Order #</th>
                    <th class="text-left py-2 px-4">Customer</th>
                    <th class="text-left py-2 px-4">Address</th>
                    <th class="text-left py-2 px-4">Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($completedDeliveries->num_rows > 0): ?>
                    <?php while ($delivery = $completedDeliveries->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4 font-semibold"><?= htmlspecialchars($delivery['order_number']) ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($delivery['customer_name']) ?></td>
                            <td class="py-2 px-4 text-sm"><?= htmlspecialchars(substr($delivery['delivery_address'], 0, 50)) ?>...</td>
                            <td class="py-2 px-4 text-sm text-gray-500">
                                <?= date('M d, Y H:i', strtotime($delivery['delivery_completed_at'])) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-500">No completed deliveries yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

