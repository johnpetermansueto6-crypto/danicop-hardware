<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

// Get filter parameters
$filterDriver = isset($_GET['driver_id']) ? (int)$_GET['driver_id'] : 0;
$filterOrder = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$filterDate = sanitize($_GET['date'] ?? '');

// Build query
$whereConditions = [];
$params = [];
$types = '';

if ($filterDriver) {
    $whereConditions[] = "da.driver_id = ?";
    $params[] = $filterDriver;
    $types .= 'i';
}

if ($filterOrder) {
    $whereConditions[] = "da.order_id = ?";
    $params[] = $filterOrder;
    $types .= 'i';
}

if ($filterDate) {
    $whereConditions[] = "DATE(dh.timestamp) = ?";
    $params[] = $filterDate;
    $types .= 's';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

$query = "
    SELECT 
        dh.*,
        da.order_id,
        o.order_number,
        driver.name as driver_name,
        u.name as updated_by_name,
        dh.action,
        dh.previous_status,
        dh.new_status,
        dh.notes,
        dh.timestamp
    FROM delivery_history dh
    INNER JOIN delivery_assignments da ON dh.assignment_id = da.id
    INNER JOIN orders o ON da.order_id = o.id
    INNER JOIN users driver ON da.driver_id = driver.id
    INNER JOIN users u ON dh.updated_by = u.id
    {$whereClause}
    ORDER BY dh.timestamp DESC
    LIMIT 100
";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $history = $stmt->get_result();
} else {
    $history = $conn->query($query);
}

// Get all drivers for filter (users with role='driver')
$allDrivers = $conn->query("SELECT id, name FROM users WHERE role = 'driver' ORDER BY name ASC");
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Delivery History</h2>
    <a href="#" onclick="loadPage('deliveries'); return false;" class="text-blue-600 hover:underline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Deliveries
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form method="GET" action="content/delivery_history.php" onsubmit="filterHistory(event); return false;" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-semibold mb-1">Filter by Driver</label>
            <select name="driver_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All Drivers</option>
                <?php while ($driver = $allDrivers->fetch_assoc()): ?>
                    <option value="<?= $driver['id'] ?>" <?= $filterDriver == $driver['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($driver['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">Filter by Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <button type="button" onclick="loadPage('delivery_history')" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                <i class="fas fa-redo mr-1"></i> Reset
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left py-3 px-4">Timestamp</th>
                    <th class="text-left py-3 px-4">Order #</th>
                    <th class="text-left py-3 px-4">Driver</th>
                    <th class="text-left py-3 px-4">Action</th>
                    <th class="text-left py-3 px-4">Status Change</th>
                    <th class="text-left py-3 px-4">Updated By</th>
                    <th class="text-left py-3 px-4">Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($history->num_rows > 0): ?>
                    <?php while ($log = $history->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 text-sm">
                                <?= date('M d, Y H:i:s', strtotime($log['timestamp'])) ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold"><?= htmlspecialchars($log['order_number']) ?></span>
                            </td>
                            <td class="py-3 px-4"><?= htmlspecialchars($log['driver_name']) ?></td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                                    <?= htmlspecialchars($log['action']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <?php if ($log['previous_status'] && $log['new_status']): ?>
                                    <div class="text-sm">
                                        <span class="text-gray-500"><?= ucfirst(str_replace('_', ' ', $log['previous_status'])) ?></span>
                                        <i class="fas fa-arrow-right mx-1 text-gray-400"></i>
                                        <span class="font-semibold"><?= ucfirst(str_replace('_', ' ', $log['new_status'])) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-500 text-sm">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-sm"><?= htmlspecialchars($log['updated_by_name']) ?></td>
                            <td class="py-3 px-4 text-sm text-gray-600">
                                <?= htmlspecialchars($log['notes'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-500">No delivery history found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterHistory(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    loadPage('delivery_history', Object.fromEntries(params));
}
</script>

