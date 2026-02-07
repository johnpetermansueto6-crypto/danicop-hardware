<?php
require_once '../../includes/config.php';
require_once '../../includes/mailer.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">Order not found</div>';
    echo '<a href="#" onclick="loadPage(\'orders\'); return false;" class="text-blue-600 hover:underline">Back to Orders</a>';
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = sanitize($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        $success = 'Order status updated successfully';
        $order['status'] = $status;
        
        // Create notification for customer
        if ($order['user_id']) {
            $stmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('order_update', ?, ?)");
            $message = "Your order #{$order['order_number']} status has been updated to: " . ucfirst(str_replace('_', ' ', $status));
            $stmt->bind_param("si", $message, $order['user_id']);
            $stmt->execute();
        }

        // Email customer about status change
        if (!empty($order['customer_email'])) {
            $subject = "Your Order #{$order['order_number']} Status Updated";
            $prettyStatus = ucfirst(str_replace('_', ' ', $status));
            $body = "<p>Hi " . htmlspecialchars($order['customer_name'] ?? 'Customer') . ",</p>
                <p>Your order <strong>#{$order['order_number']}</strong> status has been updated to <strong>{$prettyStatus}</strong>.</p>
                <p><strong>Total Amount:</strong> ₱" . number_format($order['total_amount'], 2) . "</p>
                <p>You can log in to your account to see full details.</p>
                <p>Thank you for ordering from Danicop Hardware.</p>";
            send_app_email($order['customer_email'], $subject, $body);
        }
    } else {
        $error = 'Failed to update order status';
    }
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result();
?>
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Order Details</h2>
    <a href="#" onclick="loadPage('orders'); return false;" class="text-blue-600 hover:underline">
        <i class="fas fa-arrow-left mr-2"></i> Back to Orders
    </a>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Order Information -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Order Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Order Number</p>
                    <p class="font-semibold"><?= htmlspecialchars($order['order_number']) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Order Date</p>
                    <p class="font-semibold"><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Customer</p>
                    <p class="font-semibold"><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></p>
                </div>
                <?php if ($order['customer_email']): ?>
                <div>
                    <p class="text-gray-600">Email</p>
                    <p class="font-semibold"><?= htmlspecialchars($order['customer_email']) ?></p>
                </div>
                <?php endif; ?>
                <div>
                    <p class="text-gray-600">Contact Number</p>
                    <p class="font-semibold"><?= htmlspecialchars($order['contact_number'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Payment Method</p>
                    <p class="font-semibold"><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Delivery Method</p>
                    <p class="font-semibold"><?= ucfirst($order['delivery_method']) ?></p>
                </div>
                <?php if ($order['delivery_method'] === 'delivery' && $order['delivery_address']): ?>
                <div class="md:col-span-2">
                    <p class="text-gray-600">Delivery Address</p>
                    <p class="font-semibold"><?= htmlspecialchars($order['delivery_address']) ?></p>
                    <?php if ($order['delivery_address']): ?>
                        <a href="https://maps.google.com/?q=<?= urlencode($order['delivery_address']) ?>" 
                           target="_blank" 
                           class="text-blue-600 hover:underline mt-2 inline-block">
                            <i class="fas fa-map-marker-alt"></i> View on Map
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Order Items</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-left py-2 px-4">Product</th>
                            <th class="text-left py-2 px-4">Quantity</th>
                            <th class="text-left py-2 px-4">Price</th>
                            <th class="text-left py-2 px-4">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="py-2 px-4"><?= htmlspecialchars($item['product_name']) ?></td>
                                <td class="py-2 px-4"><?= $item['quantity'] ?></td>
                                <td class="py-2 px-4">₱<?= number_format($item['price'], 2) ?></td>
                                <td class="py-2 px-4">₱<?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-bold text-lg">
                            <td colspan="3" class="py-2 px-4 text-right">Total:</td>
                            <td class="py-2 px-4">₱<?= number_format($order['total_amount'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Status Update -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
            <h2 class="text-xl font-bold mb-4">Update Status</h2>
            <form method="POST" action="order_details.php?id=<?= $id ?>" onsubmit="handleOrderStatusUpdate(event, this); return false;">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Current Status</label>
                    <p class="px-3 py-2 bg-gray-100 rounded-lg font-semibold
                        <?php
                        switch($order['status']) {
                            case 'pending': echo 'text-yellow-800'; break;
                            case 'confirmed': echo 'text-blue-800'; break;
                            case 'preparing': echo 'text-purple-800'; break;
                            case 'out_for_delivery': echo 'text-indigo-800'; break;
                            case 'ready_for_pickup': echo 'text-green-800'; break;
                            case 'completed': echo 'text-green-900'; break;
                            case 'cancelled': echo 'text-red-800'; break;
                        }
                        ?>">
                        <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                    </p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Change Status To</label>
                    <select name="status" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="preparing" <?= $order['status'] === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                        <option value="out_for_delivery" <?= $order['status'] === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                        <option value="ready_for_pickup" <?= $order['status'] === 'ready_for_pickup' ? 'selected' : '' ?>>Ready for Pickup</option>
                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <button type="submit" name="update_status" 
                        class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                    Update Status
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function handleOrderStatusUpdate(event, form) {
    event.preventDefault();
    const formData = new FormData(form);
    const orderId = <?= $id ?>;
    
    fetch(`content/order_details.php?id=${orderId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        const contentContainer = document.getElementById('content-container');
        contentContainer.innerHTML = html;
        
        // Re-execute any scripts in the loaded content
        const scripts = contentContainer.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            if (oldScript.src) {
                newScript.src = oldScript.src;
            } else {
                newScript.textContent = oldScript.textContent;
            }
            document.body.appendChild(newScript);
            oldScript.remove();
        });
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the order status. Please try again.');
    });
}
</script>

