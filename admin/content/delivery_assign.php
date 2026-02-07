<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$error = '';
$success = '';

// Get order details
$stmt = $conn->prepare("
    SELECT 
        o.*,
        u.name as customer_name,
        u.phone as customer_phone,
        u.email as customer_email
    FROM orders o
    INNER JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Order not found</div>';
    echo '<a href="#" onclick="loadPage(\'deliveries\'); return false;" class="text-blue-600 hover:underline">Back to Deliveries</a>';
    exit;
}

// Check if already assigned
$checkStmt = $conn->prepare("SELECT * FROM delivery_assignments WHERE order_id = ? AND status NOT IN ('delivered', 'failed')");
$checkStmt->bind_param("i", $orderId);
$checkStmt->execute();
$existingAssignment = $checkStmt->get_result()->fetch_assoc();

if ($existingAssignment) {
    echo '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">This order is already assigned to a driver.</div>';
    echo '<a href="#" onclick="loadPage(\'deliveries\'); return false;" class="text-blue-600 hover:underline">Back to Deliveries</a>';
    exit;
}

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
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Assign Driver to Order</h2>
    <a href="#" onclick="loadPage('deliveries'); return false;" class="text-blue-600 hover:underline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Deliveries
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Order Information -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-bold mb-4"><i class="fas fa-shopping-cart mr-2"></i>Order Information</h3>
        <div class="space-y-3">
            <div>
                <label class="text-sm font-semibold text-gray-600">Order Number:</label>
                <p class="text-lg font-bold"><?= htmlspecialchars($order['order_number']) ?></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Customer:</label>
                <p><?= htmlspecialchars($order['customer_name']) ?></p>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($order['customer_phone']) ?></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Total Amount:</label>
                <p class="text-lg font-bold text-green-600">₱<?= number_format($order['total_amount'], 2) ?></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Delivery Address:</label>
                <p class="text-sm"><?= htmlspecialchars($order['delivery_address']) ?></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Contact Number:</label>
                <p><?= htmlspecialchars($order['contact_number']) ?></p>
            </div>
        </div>
    </div>

    <!-- Driver Assignment Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-bold mb-4"><i class="fas fa-user-tie mr-2"></i>Select Driver</h3>
        
        <?php if ($availableDrivers->num_rows > 0): ?>
            <form id="assignForm" onsubmit="assignDriver(event); return false;">
                <input type="hidden" name="order_id" value="<?= $orderId ?>">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Available Drivers *</label>
                    <select name="driver_id" id="driverSelect" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Select Driver --</option>
                        <?php while ($driver = $availableDrivers->fetch_assoc()): ?>
                            <option value="<?= $driver['id'] ?>">
                                <?= htmlspecialchars($driver['name']) ?> - 
                                <?= htmlspecialchars($driver['phone'] ?? $driver['email']) ?>
                                <?php if ($driver['active_deliveries'] > 0): ?>
                                    (<?= $driver['active_deliveries'] ?> active)
                                <?php endif; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Add any special instructions for the driver..."></textarea>
                </div>
                
                <div class="flex gap-4">
                    <button type="button" onclick="loadPage('deliveries')" 
                            class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-check mr-2"></i> Assign Driver
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p class="font-semibold">No available drivers</p>
                <p class="text-sm mt-1">All drivers are currently busy or unavailable.</p>
                <a href="#" onclick="loadPage('user_add'); return false;" class="text-blue-600 hover:underline text-sm mt-2 inline-block">
                    Add a new driver
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function assignDriver(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    // Get form values
    const orderId = formData.get('order_id');
    const driverId = formData.get('driver_id');
    const notes = formData.get('notes') || '';
    
    // Validate
    if (!driverId) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a driver',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    const data = {
        order_id: parseInt(orderId),
        driver_id: parseInt(driverId),
        notes: notes.trim()
    };
    
    Swal.fire({
        title: 'Assigning Driver...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('content/delivery_assign_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            Swal.fire({
                title: 'Success!',
                text: result.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                loadPage('deliveries');
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: result.message || 'Failed to assign driver',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'An error occurred while assigning the driver. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
}
</script>

