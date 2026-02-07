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
$assignmentId = (int)($input['assignment_id'] ?? 0);
$newDriverId = (int)($input['driver_id'] ?? 0);
$updatedBy = $_SESSION['user_id'];

if (!$assignmentId || !$newDriverId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Assignment ID and Driver ID are required']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get current assignment
    $assignStmt = $conn->prepare("
        SELECT da.*, o.id as order_id, o.order_number, driver.id as old_driver_id
        FROM delivery_assignments da
        INNER JOIN orders o ON da.order_id = o.id
        INNER JOIN users driver ON da.driver_id = driver.id
        WHERE da.id = ?
    ");
    $assignStmt->bind_param("i", $assignmentId);
    $assignStmt->execute();
    $assignment = $assignStmt->get_result()->fetch_assoc();
    
    if (!$assignment) {
        throw new Exception('Assignment not found');
    }
    
    $oldDriverId = $assignment['old_driver_id'];
    
    // Check if new driver exists (user with role='driver')
    $driverStmt = $conn->prepare("SELECT id, role FROM users WHERE id = ? AND role = 'driver'");
    $driverStmt->bind_param("i", $newDriverId);
    $driverStmt->execute();
    $newDriver = $driverStmt->get_result()->fetch_assoc();
    
    if (!$newDriver) {
        throw new Exception('New driver not found or invalid role');
    }
    
    // Update assignment
    $updateStmt = $conn->prepare("UPDATE delivery_assignments SET driver_id = ?, updated_at = NOW() WHERE id = ?");
    $updateStmt->bind_param("ii", $newDriverId, $assignmentId);
    $updateStmt->execute();
    
    // Note: Driver status is managed through delivery_assignments, not user table
    
    // Log reassignment
    $historyStmt = $conn->prepare("INSERT INTO delivery_history (assignment_id, action, notes, updated_by) VALUES (?, 'reassigned', ?, ?)");
    $notes = "Reassigned from driver ID {$oldDriverId} to driver ID {$newDriverId}";
    $historyStmt->bind_param("isi", $assignmentId, $notes, $updatedBy);
    $historyStmt->execute();
    
    // Create notification
    $notificationMsg = "Order {$assignment['order_number']} has been reassigned to a different driver";
    $notifStmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('order_update', ?, NULL)");
    $notifStmt->bind_param("s", $notificationMsg);
    $notifStmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Driver reassigned successfully'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

