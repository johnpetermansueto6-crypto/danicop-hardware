<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = (int)($input['order_id'] ?? 0);
$driverId = (int)($input['driver_id'] ?? 0);
$notes = sanitize($input['notes'] ?? '');
$assignedBy = $_SESSION['user_id'];

if (!$orderId || !$driverId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Order ID and Driver ID are required']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if order exists and is assignable
    $orderStmt = $conn->prepare("SELECT id, order_number, status, delivery_method FROM orders WHERE id = ?");
    $orderStmt->bind_param("i", $orderId);
    $orderStmt->execute();
    $order = $orderStmt->get_result()->fetch_assoc();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    if ($order['delivery_method'] !== 'delivery') {
        throw new Exception('This order is not a delivery order');
    }
    
    if ($order['status'] !== 'confirmed' && $order['status'] !== 'preparing') {
        throw new Exception('Order is not in a state that allows assignment. Current status: ' . $order['status']);
    }
    
    // Check if driver exists (user with role='driver')
    $driverStmt = $conn->prepare("SELECT id, role FROM users WHERE id = ? AND role = 'driver'");
    $driverStmt->bind_param("i", $driverId);
    $driverStmt->execute();
    $driver = $driverStmt->get_result()->fetch_assoc();
    
    if (!$driver) {
        throw new Exception('Driver not found or invalid role');
    }
    
    // Check if order already has active assignment
    $checkStmt = $conn->prepare("SELECT id FROM delivery_assignments WHERE order_id = ? AND status NOT IN ('delivered', 'failed')");
    $checkStmt->bind_param("i", $orderId);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        throw new Exception('Order already has an active assignment');
    }
    
    // Create assignment
    $assignStmt = $conn->prepare("INSERT INTO delivery_assignments (order_id, driver_id, assigned_by, status, notes) VALUES (?, ?, ?, 'assigned', ?)");
    $assignStmt->bind_param("iiis", $orderId, $driverId, $assignedBy, $notes);
    
    if (!$assignStmt->execute()) {
        throw new Exception('Failed to create assignment: ' . $conn->error);
    }
    
    $assignmentId = $conn->insert_id;
    
    if (!$assignmentId) {
        throw new Exception('Failed to get assignment ID');
    }
    
    // Note: Driver status is managed through delivery_assignments, not user table
    
    // Update order status to out_for_delivery
    $updateOrderStmt = $conn->prepare("UPDATE orders SET status = 'out_for_delivery' WHERE id = ?");
    $updateOrderStmt->bind_param("i", $orderId);
    if (!$updateOrderStmt->execute()) {
        throw new Exception('Failed to update order status: ' . $conn->error);
    }
    
    // Log assignment in delivery_history
    $historyStmt = $conn->prepare("INSERT INTO delivery_history (assignment_id, action, new_status, updated_by) VALUES (?, 'assigned', 'assigned', ?)");
    $historyStmt->bind_param("ii", $assignmentId, $assignedBy);
    if (!$historyStmt->execute()) {
        throw new Exception('Failed to log assignment history');
    }
    
    // Get customer user_id for notification
    $customerStmt = $conn->prepare("SELECT user_id FROM orders WHERE id = ?");
    $customerStmt->bind_param("i", $orderId);
    $customerStmt->execute();
    $customerResult = $customerStmt->get_result()->fetch_assoc();
    $customerUserId = $customerResult['user_id'] ?? null;
    
    // Create notification for customer
    if ($customerUserId) {
        $notificationMsg = "Order {$order['order_number']} has been assigned to a driver and is out for delivery";
        $notifStmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('order_update', ?, ?)");
        $notifStmt->bind_param("si", $notificationMsg, $customerUserId);
        $notifStmt->execute();
    }
    
    // Create notification for driver
    $driverNotificationMsg = "You have been assigned to deliver Order {$order['order_number']}";
    $driverNotifStmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('delivery_assigned', ?, ?)");
    $driverNotifStmt->bind_param("si", $driverNotificationMsg, $driverId);
    $driverNotifStmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Driver assigned successfully',
        'assignment_id' => $assignmentId
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

