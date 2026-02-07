<?php
require_once '../includes/config.php';
require_once '../includes/mailer.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$error = '';
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $success = 'Order status updated successfully';
        
        // Create notification for customer
        $stmt = $conn->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        
        if ($order && $order['user_id']) {
            $stmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('order_update', ?, ?)");
            $message = "Your order #{$order['order_number']} status has been updated to: " . ucfirst(str_replace('_', ' ', $status));
            $stmt->bind_param("si", $message, $order['user_id']);
            $stmt->execute();
        }

        // Email customer about status change
        if ($order && !empty($order['customer_email'])) {
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

// Filter orders
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$query = "SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1";
if (!empty($status_filter)) {
    $query .= " AND o.status = '$status_filter'";
}
$query .= " ORDER BY o.created_at DESC";
$orders = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="../index.php" class="text-xl font-bold">🔧 Danicop Hardware</a>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <a href="../auth/logout.php" class="hover:underline">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Manage Orders</h1>
            <div>
                <form method="GET" class="inline-block">
                    <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Orders</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="preparing" <?= $status_filter === 'preparing' ? 'selected' : '' ?>>Preparing</option>
                        <option value="out_for_delivery" <?= $status_filter === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                        <option value="ready_for_pickup" <?= $status_filter === 'ready_for_pickup' ? 'selected' : '' ?>>Ready for Pickup</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </form>
            </div>
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
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-left py-3 px-4">Order #</th>
                            <th class="text-left py-3 px-4">Customer</th>
                            <th class="text-left py-3 px-4">Amount</th>
                            <th class="text-left py-3 px-4">Payment</th>
                            <th class="text-left py-3 px-4">Delivery</th>
                            <th class="text-left py-3 px-4">Status</th>
                            <th class="text-left py-3 px-4">Date</th>
                            <th class="text-left py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></td>
                                    <td class="py-3 px-4">₱<?= number_format($order['total_amount'], 2) ?></td>
                                    <td class="py-3 px-4"><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></td>
                                    <td class="py-3 px-4"><?= ucfirst($order['delivery_method']) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-sm
                                            <?php
                                            switch($order['status']) {
                                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'confirmed': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'preparing': echo 'bg-purple-100 text-purple-800'; break;
                                                case 'out_for_delivery': echo 'bg-indigo-100 text-indigo-800'; break;
                                                case 'ready_for_pickup': echo 'bg-green-100 text-green-800'; break;
                                                case 'completed': echo 'bg-green-200 text-green-900'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                    <td class="py-3 px-4">
                                        <div class="flex space-x-2">
                                            <a href="order_details.php?id=<?= $order['id'] ?>" 
                                               class="text-blue-600 hover:underline">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="py-8 text-center text-gray-500">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

