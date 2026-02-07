<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || (!isAdmin() && !isDriver())) {
    die('Unauthorized');
}

$assignmentId = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : 0;

// Get complete delivery information with all customer and order details
$stmt = $conn->prepare("
    SELECT 
        da.*,
        o.id as order_id,
        o.order_number,
        o.delivery_address,
        o.delivery_latitude,
        o.delivery_longitude,
        o.contact_number,
        o.total_amount,
        o.payment_method,
        o.delivery_method,
        o.created_at as order_date,
        u.id as customer_id,
        u.name as customer_name,
        u.phone as customer_phone,
        u.email as customer_email,
        u.address as customer_address,
        u.city as customer_city,
        u.province as customer_province,
        u.zipcode as customer_zipcode,
        driver.name as driver_name,
        driver.email as driver_email,
        driver.phone as driver_phone
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

// Get order items
$itemsStmt = $conn->prepare("
    SELECT 
        oi.*,
        p.name as product_name,
        p.image as product_image
    FROM order_items oi
    INNER JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemsStmt->bind_param("i", $delivery['order_id']);
$itemsStmt->execute();
$items = $itemsStmt->get_result();
?>

<div class="text-left space-y-6">
    <!-- Customer Information -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-bold text-lg text-blue-900 mb-3">
            <i class="fas fa-user mr-2"></i>Customer Information
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-600">Full Name:</label>
                <p class="font-bold text-gray-900"><?= htmlspecialchars($delivery['customer_name']) ?></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Email:</label>
                <p class="text-gray-900"><?= htmlspecialchars($delivery['customer_email']) ?></p>
            </div>
            <?php if ($delivery['customer_address'] || $delivery['customer_city']): ?>
            <div class="col-span-2">
                <label class="text-sm font-semibold text-gray-600">Registered Address:</label>
                <p class="text-gray-900">
                    <?php
                    $addressParts = array_filter([
                        $delivery['customer_address'],
                        $delivery['customer_city'],
                        $delivery['customer_province'],
                        $delivery['customer_zipcode']
                    ]);
                    echo htmlspecialchars(implode(', ', $addressParts));
                    ?>
                </p>
            </div>
            <?php endif; ?>
            <div>
                <label class="text-sm font-semibold text-gray-600">Primary Contact:</label>
                <p class="text-gray-900">
                    <i class="fas fa-phone text-green-600 mr-1"></i>
                    <?= htmlspecialchars($delivery['contact_number']) ?>
                </p>
            </div>
            <?php if ($delivery['customer_phone'] && $delivery['customer_phone'] !== $delivery['contact_number']): ?>
            <div>
                <label class="text-sm font-semibold text-gray-600">Alternate Phone:</label>
                <p class="text-gray-900"><?= htmlspecialchars($delivery['customer_phone']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delivery Information -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h3 class="font-bold text-lg text-green-900 mb-3">
            <i class="fas fa-truck mr-2"></i>Delivery Information
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-600">Order Number:</label>
                <p class="font-bold text-gray-900"><?= htmlspecialchars($delivery['order_number']) ?></p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Order Date:</label>
                <p class="text-gray-900"><?= date('M d, Y h:i A', strtotime($delivery['order_date'])) ?></p>
            </div>
            <div class="col-span-2">
                <label class="text-sm font-semibold text-gray-600">Delivery Address:</label>
                <p class="text-gray-900 font-semibold"><?= htmlspecialchars($delivery['delivery_address']) ?></p>
                <?php if ($delivery['delivery_latitude'] && $delivery['delivery_longitude']): ?>
                    <a href="https://www.google.com/maps?q=<?= $delivery['delivery_latitude'] ?>,<?= $delivery['delivery_longitude'] ?>" 
                       target="_blank" 
                       class="text-blue-600 hover:underline text-sm mt-1 inline-block">
                        <i class="fas fa-map-marker-alt mr-1"></i> Open in Google Maps
                    </a>
                <?php else: ?>
                    <a href="https://maps.google.com/?q=<?= urlencode($delivery['delivery_address']) ?>" 
                       target="_blank" 
                       class="text-blue-600 hover:underline text-sm mt-1 inline-block">
                        <i class="fas fa-map-marker-alt mr-1"></i> Open in Google Maps
                    </a>
                <?php endif; ?>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Payment Method:</label>
                <p class="text-gray-900">
                    <?php 
                    if (!empty($delivery['payment_method'])) {
                        echo ucfirst(str_replace('_', ' ', $delivery['payment_method']));
                    } else {
                        echo '<span class="text-gray-400 italic">Not specified</span>';
                    }
                    ?>
                </p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Total Amount:</label>
                <p class="font-bold text-green-600 text-lg">₱<?= number_format($delivery['total_amount'], 2) ?></p>
            </div>
            <?php if (!empty($delivery['notes'])): ?>
            <div class="col-span-2">
                <label class="text-sm font-semibold text-gray-600">Delivery Notes:</label>
                <p class="text-gray-900 bg-white p-2 rounded border"><?= htmlspecialchars($delivery['notes']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Items -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h3 class="font-bold text-lg text-gray-900 mb-3">
            <i class="fas fa-shopping-cart mr-2"></i>Order Items
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="text-left py-2 px-3">Product</th>
                        <th class="text-center py-2 px-3">Quantity</th>
                        <th class="text-right py-2 px-3">Price</th>
                        <th class="text-right py-2 px-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="py-2 px-3"><?= htmlspecialchars($item['product_name']) ?></td>
                            <td class="py-2 px-3 text-center"><?= $item['quantity'] ?></td>
                            <td class="py-2 px-3 text-right">₱<?= number_format($item['price'], 2) ?></td>
                            <td class="py-2 px-3 text-right font-semibold">₱<?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="font-bold text-lg bg-gray-100">
                        <td colspan="3" class="py-2 px-3 text-right">Total:</td>
                        <td class="py-2 px-3 text-right text-green-600">₱<?= number_format($delivery['total_amount'], 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Assignment Details -->
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
        <h3 class="font-bold text-lg text-purple-900 mb-3">
            <i class="fas fa-info-circle mr-2"></i>Assignment Details
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-600">Current Status:</label>
                <p class="px-2 py-1 rounded text-xs font-semibold inline-block bg-purple-100 text-purple-800">
                    <?= ucfirst(str_replace('_', ' ', $delivery['status'])) ?>
                </p>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-600">Assigned:</label>
                <p class="text-gray-900"><?= date('M d, Y H:i', strtotime($delivery['created_at'])) ?></p>
            </div>
            <?php if ($delivery['delivery_started_at']): ?>
            <div>
                <label class="text-sm font-semibold text-gray-600">Started:</label>
                <p class="text-gray-900"><?= date('M d, Y H:i', strtotime($delivery['delivery_started_at'])) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($delivery['delivery_completed_at']): ?>
            <div>
                <label class="text-sm font-semibold text-gray-600">Completed:</label>
                <p class="text-gray-900"><?= date('M d, Y H:i', strtotime($delivery['delivery_completed_at'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

