<?php
require_once '../includes/config.php';
require_once '../includes/mailer.php';

if (!isLoggedIn() || getUserRole() !== 'staff') {
    redirect('../index.php');
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
    redirect('orders.php');
}

// Handle driver assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_driver'])) {
    $driverId = (int)($_POST['driver_id'] ?? 0);
    $notes = sanitize($_POST['driver_notes'] ?? '');
    
    if (!$driverId) {
        $error = 'Please select a driver';
    } else {
        // Check if order already has active assignment
        $checkStmt = $conn->prepare("SELECT id FROM delivery_assignments WHERE order_id = ? AND status NOT IN ('delivered', 'failed')");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $error = 'This order already has an active driver assignment';
        } else {
            // Check if driver exists and is a driver
            $driverStmt = $conn->prepare("SELECT id, name FROM users WHERE id = ? AND role = 'driver'");
            $driverStmt->bind_param("i", $driverId);
            $driverStmt->execute();
            $driver = $driverStmt->get_result()->fetch_assoc();
            
            if (!$driver) {
                $error = 'Invalid driver selected or driver is not active';
            } else {
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Create delivery assignment
                    $assignStmt = $conn->prepare("INSERT INTO delivery_assignments (order_id, driver_id, assigned_by, status, notes) VALUES (?, ?, ?, 'assigned', ?)");
                    $assignedBy = $_SESSION['user_id'];
                    $assignStmt->bind_param("iiis", $id, $driverId, $assignedBy, $notes);
                    $assignStmt->execute();
                    $assignmentId = $conn->insert_id;
                    
                    // Update order status to out_for_delivery
                    $orderStmt = $conn->prepare("UPDATE orders SET status = 'out_for_delivery' WHERE id = ?");
                    $orderStmt->bind_param("i", $id);
                    $orderStmt->execute();
                    
                    // Log in delivery_history
                    $historyStmt = $conn->prepare("INSERT INTO delivery_history (assignment_id, action, new_status, updated_by) VALUES (?, 'assigned', 'assigned', ?)");
                    $historyStmt->bind_param("ii", $assignmentId, $assignedBy);
                    $historyStmt->execute();
                    
                    // Get customer email for notification
                    $customerStmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
                    $customerStmt->bind_param("i", $order['user_id']);
                    $customerStmt->execute();
                    $customer = $customerStmt->get_result()->fetch_assoc();
                    
                    // Get driver email
                    $driverEmailStmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
                    $driverEmailStmt->bind_param("i", $driverId);
                    $driverEmailStmt->execute();
                    $driverEmail = $driverEmailStmt->get_result()->fetch_assoc();
                    
                    // Create notification for customer
                    if ($order['user_id']) {
                        $notifStmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('order_update', ?, ?)");
                        $message = "Your order #{$order['order_number']} has been assigned to a driver and is out for delivery";
                        $notifStmt->bind_param("si", $message, $order['user_id']);
                        $notifStmt->execute();
                    }
                    
                    // Send email to customer - Order is out for delivery
                    if ($customer && !empty($customer['email'])) {
                        $customerEmailSubject = "Your Order #{$order['order_number']} is On the Way!";
                        $customerEmailBody = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <h2 style='color: #16a34a;'>🚚 Your Order is On the Way!</h2>
                                <p>Hello {$customer['name']},</p>
                                <p>Great news! Your order <strong>#{$order['order_number']}</strong> has been assigned to a driver and is now <strong>out for delivery</strong>.</p>
                                
                                <div style='background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                                    <h3 style='margin-top: 0;'>Delivery Information</h3>
                                    <p><strong>Order Number:</strong> #{$order['order_number']}</p>
                                    <p><strong>Delivery Address:</strong> " . htmlspecialchars($order['delivery_address']) . "</p>
                                    <p><strong>Contact Number:</strong> " . htmlspecialchars($order['contact_number']) . "</p>
                                    <p><strong>Total Amount:</strong> ₱" . number_format($order['total_amount'], 2) . "</p>
                                </div>
                                
                                <p>Your driver will be arriving soon. Please make sure someone is available to receive the order.</p>
                                
                                <p>You can track your order status by logging into your account.</p>
                                
                                <p>Thank you for choosing <strong>Danicop Hardware</strong>!</p>
                                
                                <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                                <p style='color: #6b7280; font-size: 12px;'>This is an automated email. Please do not reply.</p>
                            </div>
                        ";
                        send_app_email($customer['email'], $customerEmailSubject, $customerEmailBody);
                    }
                    
                    // Send email to driver - New delivery assignment
                    if ($driverEmail && !empty($driverEmail['email'])) {
                        $driverEmailSubject = "New Delivery Assignment - Order #{$order['order_number']}";
                        $driverEmailBody = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <h2 style='color: #16a34a;'>📦 New Delivery Assignment</h2>
                                <p>Hello {$driver['name']},</p>
                                <p>You have been assigned a new delivery!</p>
                                
                                <div style='background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                                    <h3 style='margin-top: 0;'>Delivery Details</h3>
                                    <p><strong>Order Number:</strong> #{$order['order_number']}</p>
                                    <p><strong>Customer Name:</strong> " . htmlspecialchars($order['customer_name']) . "</p>
                                    <p><strong>Delivery Address:</strong> " . htmlspecialchars($order['delivery_address']) . "</p>
                                    <p><strong>Contact Number:</strong> " . htmlspecialchars($order['contact_number']) . "</p>
                                    <p><strong>Total Amount:</strong> ₱" . number_format($order['total_amount'], 2) . "</p>
                                    " . (!empty($notes) ? "<p><strong>Notes:</strong> " . htmlspecialchars($notes) . "</p>" : "") . "
                                </div>
                                
                                <p>Please log into your driver dashboard to view complete customer details and update the delivery status.</p>
                                
                                <div style='text-align: center; margin: 30px 0;'>
                                    <a href='http://mwa/hardware/driver/assigned_deliveries.php' 
                                       style='background: #16a34a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                                        View Delivery Dashboard
                                    </a>
                                </div>
                                
                                <p>Thank you for your service!</p>
                                
                                <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                                <p style='color: #6b7280; font-size: 12px;'>This is an automated email. Please do not reply.</p>
                            </div>
                        ";
                        send_app_email($driverEmail['email'], $driverEmailSubject, $driverEmailBody);
                    }
                    
                    // Create notification for admin/staff
                    $adminNotifStmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('new_order', ?, NULL)");
                    $adminMessage = "Order #{$order['order_number']} has been assigned to driver: {$driver['name']}";
                    $adminNotifStmt->bind_param("s", $adminMessage);
                    $adminNotifStmt->execute();
                    
                    $conn->commit();
                    $success = "Driver {$driver['name']} assigned successfully. Order is now out for delivery.";
                    $order['status'] = 'out_for_delivery';
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = 'Failed to assign driver: ' . $e->getMessage();
                }
            }
        }
    }
}

// Handle status update - Staff can confirm and update order status
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
    } else {
        $error = 'Failed to update order status';
    }
}

// Check if order already has driver assignment (for delivery orders)
$existingAssignment = null;
$availableDrivers = null;

// Check if order already has driver assignment (for all orders)
$existingAssignment = null;
$availableDrivers = null;

if (true) { // Enable for all orders
    // Check if order already has active driver assignment
    $checkStmt = $conn->prepare("
        SELECT 
            da.*,
            u.name as driver_name,
            u.email as driver_email
        FROM delivery_assignments da
        INNER JOIN users u ON da.driver_id = u.id
        WHERE da.order_id = ? AND da.status NOT IN ('delivered', 'failed')
        ORDER BY da.created_at DESC
        LIMIT 1
    ");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $existingAssignment = $checkStmt->get_result()->fetch_assoc();
    
    // Get available drivers (users with role='driver')
    // Only fetch if no assignment exists yet
    // Show all drivers, ordered by active deliveries (fewer first)
    if (!$existingAssignment) {
        $availableDrivers = $conn->query("
            SELECT 
                u.id,
                u.name,
                u.email,
                u.phone,
                COUNT(DISTINCT CASE WHEN da.status IN ('assigned', 'picked_up', 'delivering') THEN da.id END) as active_deliveries
            FROM users u
            LEFT JOIN delivery_assignments da ON u.id = da.driver_id
            WHERE u.role = 'driver'
            GROUP BY u.id
            ORDER BY active_deliveries ASC, u.name ASC
        ");
        
        // Check for query errors
        if (!$availableDrivers) {
            error_log("Driver query error: " . $conn->error);
            $availableDrivers = null;
        }
    }
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result();

// Unread notifications for sidebar badge
$unread_notifications = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE is_read = 0 AND user_id = {$_SESSION['user_id']}");
if ($result) {
    $row = $result->fetch_assoc();
    $unread_notifications = $row['total'] ?? 0;
}

$current_page = 'orders';
$page_title = 'Order Details';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Staff - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
<?php include '../includes/staff_sidebar.php'; ?>

    <div class="space-y-6">
        <h1 class="text-2xl font-bold mb-2 text-gray-800">Order Details</h1>
        
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
                            <a href="https://maps.google.com/?q=<?= urlencode($order['delivery_address']) ?>" 
                               target="_blank" 
                               class="text-blue-600 hover:underline mt-2 inline-block">
                                <i class="fas fa-map-marker-alt"></i> View on Map
                            </a>
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
            
            <!-- Status Update - Staff Actions -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <!-- Driver Assignment Section (For all orders) -->
                    <?php if (true): ?>
                        <div>
                            <h3 class="text-xl font-bold mb-4">
                                <i class="fas fa-truck mr-2"></i>Assign Driver
                            </h3>
                            
                            <?php if ($existingAssignment): ?>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                    <p class="text-sm font-semibold text-blue-800 mb-2">
                                        <i class="fas fa-info-circle mr-1"></i>Driver Already Assigned
                                    </p>
                                    <p class="text-sm text-blue-700">
                                        <strong>Driver:</strong> <?= htmlspecialchars($existingAssignment['driver_name']) ?>
                                    </p>
                                    <p class="text-sm text-blue-700">
                                        <strong>Status:</strong> 
                                        <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                                            <?= ucfirst(str_replace('_', ' ', $existingAssignment['status'])) ?>
                                        </span>
                                    </p>
                                    <?php if ($existingAssignment['notes']): ?>
                                        <p class="text-sm text-blue-700 mt-2">
                                            <strong>Notes:</strong> <?= htmlspecialchars($existingAssignment['notes']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="text-xs text-blue-600 mt-2">
                                        Assigned: <?= date('M d, Y H:i', strtotime($existingAssignment['created_at'])) ?>
                                    </p>
                                </div>
                            <?php else: ?>
                                <?php if ($availableDrivers && $availableDrivers->num_rows > 0): ?>
                                    <form method="POST" action="order_details.php?id=<?= $id ?>" id="assignDriverForm">
                                        <div class="mb-4">
                                            <label class="block text-gray-700 font-semibold mb-2">Select Driver *</label>
                                            <select name="driver_id" required
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                                <option value="">-- Select Driver --</option>
                                                <?php 
                                                // Reset pointer to beginning
                                                $availableDrivers->data_seek(0);
                                                while ($driver = $availableDrivers->fetch_assoc()): 
                                                    $activeCount = (int)($driver['active_deliveries'] ?? 0);
                                                    $statusText = $activeCount === 0 ? 'Available' : ($activeCount . ' active delivery' . ($activeCount > 1 ? 'ies' : ''));
                                                ?>
                                                    <option value="<?= $driver['id'] ?>">
                                                        <?= htmlspecialchars($driver['name']) ?> 
                                                        (<?= htmlspecialchars($driver['phone'] ?? $driver['email']) ?>)
                                                        - <?= $statusText ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="block text-gray-700 font-semibold mb-2">Notes (Optional)</label>
                                            <textarea name="driver_notes" rows="3"
                                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                                      placeholder="Add any special instructions for the driver..."></textarea>
                                        </div>
                                        
                                        <button type="submit" name="assign_driver" 
                                                class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">
                                            <i class="fas fa-user-tie mr-2"></i> Assign Driver
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <p class="text-sm text-yellow-800 font-semibold mb-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            No Drivers Available
                                        </p>
                                        <p class="text-xs text-yellow-700 mb-3">
                                            There are no drivers in the system. Please contact an administrator to add drivers.
                                        </p>
                                        <?php
                                        // Check if there are any users with driver role at all
                                        $driverCheck = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'driver'");
                                        if ($driverCheck) {
                                            $driverCount = $driverCheck->fetch_assoc()['count'];
                                            if ($driverCount == 0) {
                                                echo '<p class="text-xs text-yellow-600">No drivers found in the system.</p>';
                                            } else {
                                                echo '<p class="text-xs text-yellow-600">All drivers are currently busy with deliveries.</p>';
                                            }
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    </main>
    </div>
    </div>
</body>
</html>

