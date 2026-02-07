<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

$assignmentId = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : 0;

$stmt = $conn->prepare("
    SELECT 
        da.*,
        o.id as order_id,
        o.order_number,
        o.delivery_address,
        o.contact_number,
        o.total_amount,
        o.payment_method,
        o.delivery_method,
        u.name as customer_name,
        u.phone as customer_phone,
        driver.name as driver_name,
        driver.phone as driver_phone,
        driver.email as driver_email
    FROM delivery_assignments da
    INNER JOIN orders o ON da.order_id = o.id
    INNER JOIN users u ON o.user_id = u.id
    INNER JOIN users driver ON da.driver_id = driver.id
    WHERE da.id = ?
");
$stmt->bind_param("i", $assignmentId);
$stmt->execute();
$delivery = $stmt->get_result()->fetch_assoc();

if (!$delivery) {
    echo '<p class="text-red-600">Delivery not found</p>';
    exit;
}
?>

<div class="text-left space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-semibold text-gray-600">Order Number:</label>
            <p class="font-bold"><?= htmlspecialchars($delivery['order_number']) ?></p>
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-600">Status:</label>
            <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                <?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?>
            </span>
        </div>
    </div>
    
    <div>
        <label class="text-sm font-semibold text-gray-600">Customer:</label>
        <p><?= htmlspecialchars($delivery['customer_name']) ?></p>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($delivery['customer_phone']) ?></p>
    </div>
    
    <div>
        <label class="text-sm font-semibold text-gray-600">Delivery Address:</label>
        <p><?= htmlspecialchars($delivery['delivery_address']) ?></p>
    </div>
    
    <div>
        <label class="text-sm font-semibold text-gray-600">Assigned Driver:</label>
        <p><?= htmlspecialchars($delivery['driver_name']) ?></p>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($delivery['driver_phone'] ?? $delivery['driver_email'] ?? 'N/A') ?></p>
    </div>
    
    <?php if ($delivery['notes']): ?>
    <div>
        <label class="text-sm font-semibold text-gray-600">Notes:</label>
        <p class="text-sm"><?= htmlspecialchars($delivery['notes']) ?></p>
    </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-2 gap-4 pt-4 border-t">
        <div>
            <label class="text-sm font-semibold text-gray-600">Assigned:</label>
            <p class="text-sm"><?= date('M d, Y H:i', strtotime($delivery['created_at'])) ?></p>
        </div>
        <?php if ($delivery['delivery_completed_at']): ?>
        <div>
            <label class="text-sm font-semibold text-gray-600">Completed:</label>
            <p class="text-sm"><?= date('M d, Y H:i', strtotime($delivery['delivery_completed_at'])) ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

