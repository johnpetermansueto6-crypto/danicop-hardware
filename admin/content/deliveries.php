<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

// Get pending deliveries (assigned but not delivered)
$pendingDeliveries = $conn->query("
    SELECT 
        da.id as assignment_id,
        da.status as delivery_status,
        da.created_at as assigned_at,
        da.updated_at as last_update,
        o.id as order_id,
        o.order_number,
        o.delivery_method,
        o.delivery_address,
        o.contact_number,
        o.total_amount,
        u.name as customer_name,
        driver.name as driver_name,
        driver.id as driver_id,
        driver.phone as driver_phone,
        driver.email as driver_email
    FROM delivery_assignments da
    INNER JOIN orders o ON da.order_id = o.id
    INNER JOIN users u ON o.user_id = u.id
    INNER JOIN users driver ON da.driver_id = driver.id
    WHERE da.status IN ('assigned', 'picked_up', 'delivering')
    ORDER BY da.created_at DESC
");

// Get orders needing assignment (confirmed or preparing, not yet assigned)
$ordersNeedingAssignment = $conn->query("
    SELECT 
        o.id,
        o.order_number,
        o.delivery_method,
        o.delivery_address,
        o.contact_number,
        o.total_amount,
        o.status as order_status,
        o.created_at,
        u.name as customer_name,
        u.phone as customer_phone
    FROM orders o
    INNER JOIN users u ON o.user_id = u.id
    LEFT JOIN delivery_assignments da ON o.id = da.order_id AND da.status NOT IN ('delivered', 'failed')
    WHERE o.status IN ('confirmed', 'preparing')
    AND o.delivery_method = 'delivery'
    AND da.id IS NULL
    ORDER BY o.created_at ASC
");

// Get available drivers (users with role='driver')
$availableDrivers = $conn->query("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.phone,
        COUNT(DISTINCT da.id) as active_deliveries
    FROM users u
    LEFT JOIN delivery_assignments da ON u.id = da.driver_id AND da.status IN ('assigned', 'picked_up', 'delivering')
    WHERE u.role = 'driver'
    GROUP BY u.id
    HAVING active_deliveries = 0 OR active_deliveries < 3
    ORDER BY u.name ASC
");

// Get recent delivery logs
$recentLogs = $conn->query("
    SELECT 
        dh.*,
        da.order_id,
        o.order_number,
        driver.name as driver_name,
        updater.name as updated_by_name,
        dh.action,
        dh.previous_status,
        dh.new_status,
        dh.timestamp
    FROM delivery_history dh
    INNER JOIN delivery_assignments da ON dh.assignment_id = da.id
    INNER JOIN orders o ON da.order_id = o.id
    INNER JOIN users driver ON da.driver_id = driver.id
    INNER JOIN users updater ON dh.updated_by = updater.id
    ORDER BY dh.timestamp DESC
    LIMIT 20
");

// Statistics
$statsQuery = $conn->query("
    SELECT 
        COUNT(CASE WHEN da.status IN ('assigned', 'picked_up', 'delivering') THEN 1 END) as active_deliveries,
        COUNT(CASE WHEN da.status = 'delivered' AND DATE(da.delivery_completed_at) = CURDATE() THEN 1 END) as today_delivered,
        (SELECT COUNT(*) FROM users WHERE role = 'driver') as available_drivers,
        COUNT(CASE WHEN o.status IN ('confirmed', 'preparing') AND o.delivery_method = 'delivery' AND da.id IS NULL THEN 1 END) as pending_assignments
    FROM orders o
    LEFT JOIN delivery_assignments da ON o.id = da.order_id
    WHERE o.delivery_method = 'delivery'
");
$stats = $statsQuery->fetch_assoc();
?>

<div class="flex justify-between items-center mb-6">
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fas fa-truck text-white text-xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Delivery Management</h2>
    </div>
    <div class="flex gap-3">
        <a href="#" onclick="loadPage('users'); return false;" class="bg-gradient-to-r from-emerald-600 to-green-600 text-white px-5 py-2.5 rounded-xl hover:from-emerald-700 hover:to-green-700 shadow-lg hover:shadow-xl transition-all flex items-center">
            <i class="fas fa-users mr-2"></i> Manage Drivers
        </a>
        <a href="#" onclick="loadPage('delivery_history'); return false;" class="bg-gradient-to-r from-gray-600 to-gray-700 text-white px-5 py-2.5 rounded-xl hover:from-gray-700 hover:to-gray-800 shadow-lg hover:shadow-xl transition-all flex items-center">
            <i class="fas fa-history mr-2"></i> Delivery History
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
    <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-emerald-500 hover:shadow-xl transition-all">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Active Deliveries</p>
                <p class="text-3xl font-bold text-emerald-600"><?= $stats['active_deliveries'] ?? 0 ?></p>
            </div>
            <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-truck text-emerald-600 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-green-500 hover:shadow-xl transition-all">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Today Delivered</p>
                <p class="text-3xl font-bold text-green-600"><?= $stats['today_delivered'] ?? 0 ?></p>
            </div>
            <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-teal-500 hover:shadow-xl transition-all">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Available Drivers</p>
                <p class="text-3xl font-bold text-teal-600"><?= $stats['available_drivers'] ?? 0 ?></p>
            </div>
            <div class="w-14 h-14 bg-teal-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-check text-teal-600 text-2xl"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-amber-500 hover:shadow-xl transition-all">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Pending Assignment</p>
                <p class="text-3xl font-bold text-amber-600"><?= $stats['pending_assignments'] ?? 0 ?></p>
            </div>
            <div class="w-14 h-14 bg-amber-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-clock text-amber-600 text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Pending Deliveries -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <div class="bg-gradient-to-r from-emerald-600 to-green-600 text-white px-6 py-4">
            <h3 class="text-lg font-bold flex items-center">
                <i class="fas fa-truck mr-3"></i>Pending Deliveries
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Order #</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Customer</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Driver</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pendingDeliveries->num_rows > 0): ?>
                        <?php while ($delivery = $pendingDeliveries->fetch_assoc()): ?>
                            <tr class="border-b border-gray-100 hover:bg-emerald-50/50 transition-colors">
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-gray-800"><?= htmlspecialchars($delivery['order_number']) ?></span>
                                </td>
                                <td class="py-3 px-4 text-gray-600"><?= htmlspecialchars($delivery['customer_name']) ?></td>
                                <td class="py-3 px-4">
                                    <div>
                                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($delivery['driver_name']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($delivery['driver_phone'] ?? 'N/A') ?></div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php
                                        echo match($delivery['delivery_status']) {
                                            'assigned' => 'bg-yellow-100 text-yellow-800',
                                            'picked_up' => 'bg-emerald-100 text-emerald-800',
                                            'delivering' => 'bg-teal-100 text-teal-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    ?>">
                                        <?= ucfirst(str_replace('_', ' ', $delivery['delivery_status'])) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex gap-2">
                                        <button onclick="viewDelivery(<?= $delivery['assignment_id'] ?>)" 
                                                class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200 flex items-center justify-center transition-colors">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="updateDeliveryStatus(<?= $delivery['assignment_id'] ?>, '<?= $delivery['delivery_status'] ?>')" 
                                                class="w-8 h-8 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 flex items-center justify-center transition-colors">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="reassignDelivery(<?= $delivery['assignment_id'] ?>, <?= $delivery['order_id'] ?>, <?= $delivery['driver_id'] ?>)" 
                                                class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 hover:bg-amber-200 flex items-center justify-center transition-colors">
                                            <i class="fas fa-user-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-10 text-center text-gray-500">
                                <i class="fas fa-truck text-4xl text-gray-300 mb-3"></i>
                                <p>No pending deliveries</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Orders Needing Assignment -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white px-6 py-4">
            <h3 class="text-lg font-bold flex items-center">
                <i class="fas fa-clock mr-3"></i>Orders Needing Assignment
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Order #</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Customer</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Address</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ordersNeedingAssignment->num_rows > 0): ?>
                        <?php while ($order = $ordersNeedingAssignment->fetch_assoc()): ?>
                            <tr class="border-b border-gray-100 hover:bg-amber-50/50 transition-colors">
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-gray-800"><?= htmlspecialchars($order['order_number']) ?></span>
                                    <div class="text-xs text-emerald-600 font-semibold">₱<?= number_format($order['total_amount'], 2) ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-gray-800"><?= htmlspecialchars($order['customer_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-sm text-gray-600"><?= htmlspecialchars(substr($order['delivery_address'], 0, 50)) ?>...</div>
                                </td>
                                <td class="py-3 px-4">
                                    <button onclick="assignDriver(<?= $order['id'] ?>, '<?= addslashes($order['order_number']) ?>')" 
                                            class="bg-gradient-to-r from-emerald-500 to-green-500 text-white px-4 py-2 rounded-lg text-sm hover:from-emerald-600 hover:to-green-600 shadow hover:shadow-lg transition-all">
                                        <i class="fas fa-user-plus mr-1"></i> Assign
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="py-10 text-center text-gray-500">
                                <i class="fas fa-check-circle text-4xl text-gray-300 mb-3"></i>
                                <p>No orders need assignment</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Available Drivers -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden mt-6 border border-gray-100">
    <div class="bg-gradient-to-r from-teal-600 to-emerald-600 text-white px-6 py-4">
        <h3 class="text-lg font-bold flex items-center">
            <i class="fas fa-users mr-3"></i>Available Drivers
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Name</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Contact</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Active Deliveries</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($availableDrivers->num_rows > 0): ?>
                    <?php while ($driver = $availableDrivers->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100 hover:bg-teal-50/50 transition-colors">
                            <td class="py-3 px-4 font-semibold text-gray-800"><?= htmlspecialchars($driver['name']) ?></td>
                            <td class="py-3 px-4">
                                <div class="text-gray-800"><?= htmlspecialchars($driver['phone'] ?? $driver['email']) ?></div>
                                <div class="text-xs text-gray-500"><?= htmlspecialchars($driver['email']) ?></div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $driver['active_deliveries'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
                                    <?= $driver['active_deliveries'] ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <a href="#" onclick="loadPage('users'); return false;" 
                                   class="text-emerald-600 hover:text-emerald-700 text-sm font-semibold flex items-center gap-1 w-fit">
                                    <i class="fas fa-users"></i> Manage
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="py-10 text-center text-gray-500">
                            <i class="fas fa-user-plus text-4xl text-gray-300 mb-3"></i>
                            <p>No available drivers. Add drivers via Manage Staff.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Delivery Logs -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden mt-6 border border-gray-100">
    <div class="bg-gradient-to-r from-gray-700 to-gray-800 text-white px-6 py-4">
        <h3 class="text-lg font-bold flex items-center">
            <i class="fas fa-history mr-3"></i>Recent Delivery Logs
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Order #</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Driver</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Action</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Status Change</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Updated By</th>
                    <th class="text-left py-3 px-4 text-sm font-semibold text-gray-600">Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recentLogs->num_rows > 0): ?>
                    <?php while ($log = $recentLogs->fetch_assoc()): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-semibold text-gray-800"><?= htmlspecialchars($log['order_number']) ?></span>
                            </td>
                            <td class="py-3 px-4 text-gray-600"><?= htmlspecialchars($log['driver_name']) ?></td>
                            <td class="py-3 px-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <?php if ($log['previous_status'] && $log['new_status']): ?>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="text-gray-500"><?= ucfirst($log['previous_status']) ?></span>
                                        <i class="fas fa-arrow-right text-emerald-400"></i>
                                        <span class="font-semibold text-emerald-600"><?= ucfirst($log['new_status']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600"><?= htmlspecialchars($log['updated_by_name']) ?></td>
                            <td class="py-3 px-4 text-xs text-gray-500">
                                <?= date('M d, Y H:i', strtotime($log['timestamp'])) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-10 text-center text-gray-500">
                            <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-3"></i>
                            <p>No delivery logs yet</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function assignDriver(orderId, orderNumber) {
    loadPage('delivery_assign', {order_id: orderId});
}

function viewDelivery(assignmentId) {
    Swal.fire({
        title: 'Delivery Details',
        html: '<div class="text-left">Loading...</div>',
        showConfirmButton: true,
        confirmButtonText: 'Close',
        width: '600px',
        didOpen: () => {
            fetch(`content/delivery_view.php?assignment_id=${assignmentId}`)
                .then(response => response.text())
                .then(html => {
                    Swal.getHtmlContainer().innerHTML = html;
                });
        }
    });
}

function updateDeliveryStatus(assignmentId, currentStatus) {
    const statusOptions = {
        'assigned': ['picked_up', 'delivering', 'failed'],
        'picked_up': ['delivering', 'failed'],
        'delivering': ['delivered', 'failed']
    };

    const nextStatuses = statusOptions[currentStatus] || [];
    
    if (nextStatuses.length === 0) {
        Swal.fire('Info', 'This delivery cannot be updated further.', 'info');
        return;
    }

    Swal.fire({
        title: 'Update Delivery Status',
        input: 'select',
        inputOptions: Object.fromEntries(nextStatuses.map(s => [s, s.replace('_', ' ').toUpperCase()])),
        inputPlaceholder: 'Select new status',
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to select a status!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            updateStatus(assignmentId, result.value);
        }
    });
}

function updateStatus(assignmentId, newStatus) {
    Swal.fire({
        title: 'Updating...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('content/delivery_update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            assignment_id: assignmentId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                loadPage('deliveries');
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message || 'Failed to update status',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'An error occurred',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}

function reassignDelivery(assignmentId, orderId, currentDriverId) {
    Swal.fire({
        title: 'Reassign Driver',
        html: '<div class="text-left">Loading drivers...</div>',
        showCancelButton: true,
        confirmButtonText: 'Reassign',
        cancelButtonText: 'Cancel',
        width: '500px',
        didOpen: () => {
            fetch('content/delivery_get_drivers.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.drivers) {
                        if (data.drivers.length === 0) {
                            Swal.getHtmlContainer().innerHTML = `
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-yellow-800 text-sm">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        No drivers available. Please add drivers via Admin Panel → Manage Staff.
                                    </p>
                                </div>
                            `;
                            // Disable confirm button
                            Swal.getConfirmButton().style.display = 'none';
                        } else {
                            // Show all drivers, but mark the current one
                            const driverOptions = data.drivers
                                .map(d => {
                                    const isCurrent = parseInt(d.id) === parseInt(currentDriverId);
                                    const marker = isCurrent ? ' (Current Driver)' : '';
                                    return `<option value="${d.id}" ${isCurrent ? 'selected' : ''}>${d.name} - ${d.phone || d.email || 'N/A'}${marker}</option>`;
                                })
                                .join('');
                            
                            Swal.getHtmlContainer().innerHTML = `
                                <label class="block text-sm font-semibold mb-2">Select Driver:</label>
                                <select id="newDriverId" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                                    ${driverOptions}
                                </select>
                                <p class="text-xs text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    You can reassign to the same driver or select a different one.
                                </p>
                            `;
                        }
                    } else {
                        Swal.getHtmlContainer().innerHTML = `
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <p class="text-red-600 text-sm">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    ${data.message || 'Failed to load drivers'}
                                </p>
                            </div>
                        `;
                        Swal.getConfirmButton().style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error loading drivers:', error);
                    Swal.getHtmlContainer().innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-red-600 text-sm">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Error loading drivers. Please try again.
                            </p>
                        </div>
                    `;
                    Swal.getConfirmButton().style.display = 'none';
                });
        },
        preConfirm: () => {
            const selectElement = document.getElementById('newDriverId');
            if (!selectElement) {
                Swal.showValidationMessage('Driver selection not available');
                return false;
            }
            const driverId = selectElement.value;
            if (!driverId) {
                Swal.showValidationMessage('Please select a driver');
                return false;
            }
            return driverId;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            reassignDriver(assignmentId, result.value);
        }
    });
}

function reassignDriver(assignmentId, newDriverId) {
    Swal.fire({
        title: 'Reassigning...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('content/delivery_reassign.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            assignment_id: assignmentId,
            driver_id: newDriverId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                loadPage('deliveries');
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message || 'Failed to reassign driver',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
}

function viewDriverDetails(driverId) {
    loadPage('user_edit', {id: driverId});
}
</script>

