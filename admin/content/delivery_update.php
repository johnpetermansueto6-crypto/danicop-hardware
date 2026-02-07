<?php
// Suppress error display for JSON responses but log them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set error handler to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Only output JSON if headers haven't been sent
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'A fatal error occurred: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
            ]);
        }
    }
});

// Try to load config.php
// Use output buffering to catch any die() or echo statements
ob_start();
$configLoaded = false;
try {
    // Set a custom error handler to catch database connection errors
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        // Don't handle errors here, just let them pass through
        return false;
    });
    
    require_once '../../includes/config.php';
    $output = ob_get_clean();
    restore_error_handler();
    
    // Check if config.php output anything (like die() from database connection failure)
    // Trim whitespace and newlines - these are common from closing PHP tags
    $output = trim($output);
    if (!empty($output)) {
        http_response_code(500);
        header('Content-Type: application/json');
        // Clean the output and return as JSON
        $cleanOutput = strip_tags($output);
        $cleanOutput = preg_replace('/\s+/', ' ', $cleanOutput);
        $cleanOutput = trim($cleanOutput);
        // Only report if it's not just whitespace
        if (!empty($cleanOutput)) {
            echo json_encode(['success' => false, 'message' => 'Configuration error: ' . $cleanOutput]);
            exit;
        }
    }
    
    $configLoaded = true;
} catch (Exception $e) {
    ob_end_clean();
    restore_error_handler();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Configuration error: ' . $e->getMessage()]);
    exit;
} catch (Error $e) {
    ob_end_clean();
    restore_error_handler();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}

if (!$configLoaded) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to load configuration']);
    exit;
}

// Try to load mailer.php (optional)
@require_once '../../includes/mailer.php';

// Check if required functions exist
if (!function_exists('isLoggedIn')) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Required function isLoggedIn not found']);
    exit;
}

// Check if database connection exists
if (!isset($conn) || !$conn) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection not available']);
    exit;
}

// Allow both admin/staff and drivers to update delivery status
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

// Handle both JSON (AJAX) and FormData (file upload) requests
$assignmentId = 0;
$newStatus = '';
$updatedBy = $_SESSION['user_id'];
$deliveryProofImage = null;
$deliverySignature = null;
$notes = ''; // Initialize notes variable
$isFileUpload = false; // Initialize file upload flag

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a file upload (FormData) or JSON request
    // Check if files are being uploaded (FormData request)
    $isFileUpload = isset($_FILES['delivery_proof_image']) && 
                    isset($_FILES['delivery_proof_image']['name']) && 
                    !empty($_FILES['delivery_proof_image']['name']);
    
    if ($isFileUpload) {
        // FormData request with file upload
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        $newStatus = sanitize($_POST['status'] ?? '');
        $notes = sanitize($_POST['notes'] ?? ''); // Get notes from POST if provided
        
        // Handle file upload
        if (isset($_FILES['delivery_proof_image']['error']) && $_FILES['delivery_proof_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/delivery_proof/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['delivery_proof_image']['name'], PATHINFO_EXTENSION));
            // Normalize jpeg to jpg
            if ($fileExtension === 'jpeg') {
                $fileExtension = 'jpg';
            }
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($fileExtension, $allowedExtensions)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images are allowed.']);
                exit;
            }
            
            // Validate MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['delivery_proof_image']['tmp_name']);
            finfo_close($finfo);
            
            $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mimeType, $allowedMimeTypes)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only images are allowed.']);
                exit;
            }
            
            // Generate unique filename
            $fileName = 'proof_' . $assignmentId . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['delivery_proof_image']['tmp_name'], $filePath)) {
                $deliveryProofImage = 'uploads/delivery_proof/' . $fileName;
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
                exit;
            }
        }
        
        // Handle signature upload
        if (!empty($_FILES['delivery_signature']['name']) && $_FILES['delivery_signature']['error'] === UPLOAD_ERR_OK) {
            $signatureUploadDir = __DIR__ . '/../../uploads/delivery_signatures/';
            if (!is_dir($signatureUploadDir)) {
                mkdir($signatureUploadDir, 0755, true);
            }
            
            $signatureExtension = 'png'; // Signatures are always PNG from canvas
            $signatureFileName = 'signature_' . $assignmentId . '_' . time() . '.' . $signatureExtension;
            $signatureFilePath = $signatureUploadDir . $signatureFileName;
            
            if (move_uploaded_file($_FILES['delivery_signature']['tmp_name'], $signatureFilePath)) {
                $deliverySignature = 'uploads/delivery_signatures/' . $signatureFileName;
            }
        }
    } else {
        // JSON request (no file upload)
        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
            exit;
        }
        $assignmentId = (int)($input['assignment_id'] ?? 0);
        $newStatus = sanitize($input['status'] ?? '');
        $notes = sanitize($input['notes'] ?? '');
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!$assignmentId || !$newStatus) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Assignment ID and status are required']);
    exit;
}

$validStatuses = ['assigned', 'picked_up', 'delivering', 'delivered', 'failed'];
if (!in_array($newStatus, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get current assignment with customer details
    $assignStmt = $conn->prepare("
        SELECT 
            da.*, 
            o.id as order_id, 
            o.order_number, 
            o.user_id as customer_id,
            o.total_amount,
            o.delivery_address,
            o.contact_number,
            driver.id as driver_id, 
            driver.name as driver_name
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
    
    // Check authorization: Drivers can only update their own deliveries
    $userRole = getUserRole();
    if ($userRole === 'driver' && $assignment['driver_id'] != $updatedBy) {
        throw new Exception('You can only update your own deliveries');
    }
    
    // If status is "delivered", require proof image (only if it's a file upload request)
    if ($newStatus === 'delivered' && $isFileUpload && !$deliveryProofImage) {
        throw new Exception('Proof of delivery photo is required when marking as delivered');
    }
    
    $previousStatus = $assignment['status'];
    
    // Update assignment status
    $updateFields = "status = ?";
    $updateParams = [$newStatus];
    $updateTypes = "s";
    
    if ($newStatus === 'delivered') {
        $updateFields .= ", delivery_completed_at = NOW()";
        
        // If delivery proof image is provided, add it to the update
        if ($deliveryProofImage) {
            $updateFields .= ", delivery_proof_image = ?";
            $updateParams[] = $deliveryProofImage;
            $updateTypes .= "s";
            
            // Delete old proof image if it exists
            if (!empty($assignment['delivery_proof_image'])) {
                $oldImagePath = __DIR__ . '/../../' . $assignment['delivery_proof_image'];
                if (file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }
        }
        
        // If delivery signature is provided, check if column exists first
        if ($deliverySignature) {
            // Simple check: try to describe the table and see if column exists
            try {
                $columnCheck = $conn->query("SHOW COLUMNS FROM delivery_assignments WHERE Field = 'delivery_signature'");
                if ($columnCheck && $columnCheck->num_rows > 0) {
                    // Column exists, add it to update
                    $updateFields .= ", delivery_signature = ?";
                    $updateParams[] = $deliverySignature;
                    $updateTypes .= "s";
                }
            } catch (Exception $e) {
                // If check fails, assume column doesn't exist
                // Signature file is still saved in uploads directory
            }
        }
    } elseif ($newStatus === 'delivering' && $previousStatus === 'assigned') {
        $updateFields .= ", delivery_started_at = NOW()";
    } elseif ($newStatus === 'failed') {
        // Store failure reason in notes field
        if (!empty($notes)) {
            $updateFields .= ", notes = ?";
            $updateParams[] = $notes;
            $updateTypes .= "s";
        }
    }
    
    // Prepare update statement
    $updateQuery = "UPDATE delivery_assignments SET {$updateFields}, updated_at = NOW() WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    
    if (!$updateStmt) {
        throw new Exception('Failed to prepare update statement: ' . $conn->error);
    }
    
    $updateParams[] = $assignmentId;
    $updateTypes .= "i";
    
    if (!$updateStmt->bind_param($updateTypes, ...$updateParams)) {
        throw new Exception('Failed to bind parameters: ' . $updateStmt->error);
    }
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to execute update: ' . $updateStmt->error);
    }
    
    // Note: Driver status is managed through delivery_assignments, not user table
    // No need to update driver status here
    
    // Update order status
    if ($newStatus === 'delivered') {
        $orderStmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        if (!$orderStmt) {
            throw new Exception('Failed to prepare order update: ' . $conn->error);
        }
        $orderStmt->bind_param("i", $assignment['order_id']);
        if (!$orderStmt->execute()) {
            throw new Exception('Failed to update order status: ' . $orderStmt->error);
        }
    } elseif ($newStatus === 'failed') {
        // Optionally set order back to preparing or keep as out_for_delivery
        // $orderStmt = $conn->prepare("UPDATE orders SET status = 'preparing' WHERE id = ?");
        // $orderStmt->bind_param("i", $assignment['order_id']);
        // $orderStmt->execute();
    }
    
    // Log in delivery_history
    // Query has 4 placeholders: assignment_id, previous_status, new_status, updated_by
    // Type string should match: i (assignment_id), s (previous_status), s (new_status), i (updated_by)
    $historyStmt = $conn->prepare("INSERT INTO delivery_history (assignment_id, action, previous_status, new_status, updated_by) VALUES (?, 'status_update', ?, ?, ?)");
    if (!$historyStmt) {
        error_log("Failed to prepare history statement: " . $conn->error);
        // Don't throw - history logging is not critical
    } else {
        $historyStmt->bind_param("issi", $assignmentId, $previousStatus, $newStatus, $updatedBy);
        if (!$historyStmt->execute()) {
            error_log("Failed to execute history statement: " . $historyStmt->error);
            // Don't throw - history logging is not critical
        }
    }
    
    // If delivered, send notifications and emails
    if ($newStatus === 'delivered') {
        // Get customer information
        $customerStmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
        $customerStmt->bind_param("i", $assignment['customer_id']);
        $customerStmt->execute();
        $customer = $customerStmt->get_result()->fetch_assoc();
        
        // Create notification for customer
        if ($assignment['customer_id']) {
            $customerNotifStmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('order_update', ?, ?)");
            $customerMessage = "Your order #{$assignment['order_number']} has been delivered successfully!";
            $customerNotifStmt->bind_param("si", $customerMessage, $assignment['customer_id']);
            $customerNotifStmt->execute();
        }
        
        // Send email to customer - Order delivered
        if ($customer && !empty($customer['email'])) {
            $customerEmailSubject = "Your Order #{$assignment['order_number']} Has Been Delivered!";
            $customerEmailBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #16a34a;'>✅ Your Order Has Been Delivered!</h2>
                    <p>Hello {$customer['name']},</p>
                    <p>Great news! Your order <strong>#{$assignment['order_number']}</strong> has been successfully delivered to you.</p>
                    
                    <div style='background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='margin-top: 0;'>Order Details</h3>
                        <p><strong>Order Number:</strong> #{$assignment['order_number']}</p>
                        <p><strong>Delivery Address:</strong> " . htmlspecialchars($assignment['delivery_address']) . "</p>
                        <p><strong>Total Amount:</strong> ₱" . number_format($assignment['total_amount'], 2) . "</p>
                        <p><strong>Delivered By:</strong> {$assignment['driver_name']}</p>
                    </div>
                    
                    <p>Thank you for choosing <strong>Danicop Hardware</strong>! We hope you're satisfied with your purchase.</p>
                    
                    <p>If you have any questions or concerns, please don't hesitate to contact us.</p>
                    
                    <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                    <p style='color: #6b7280; font-size: 12px;'>This is an automated email. Please do not reply.</p>
                </div>
            ";
            if (function_exists('send_app_email')) {
                send_app_email($customer['email'], $customerEmailSubject, $customerEmailBody);
            }
        }
        
        // Create notification for all staff/admin
        $staffNotifStmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('order_update', ?, NULL)");
        $staffMessage = "Order #{$assignment['order_number']} has been delivered by driver {$assignment['driver_name']}";
        $staffNotifStmt->bind_param("s", $staffMessage);
        $staffNotifStmt->execute();
        
        // Send email to all staff/admin
        $staffStmt = $conn->query("SELECT id, name, email FROM users WHERE role IN ('superadmin', 'staff') AND status = 'active'");
        $staffEmails = [];
        while ($staff = $staffStmt->fetch_assoc()) {
            if (!empty($staff['email'])) {
                $staffEmails[$staff['email']] = $staff['name'];
            }
        }
        
        if (!empty($staffEmails)) {
            $staffEmailSubject = "Order #{$assignment['order_number']} Delivered Successfully";
            $staffEmailBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #16a34a;'>✅ Order Delivered</h2>
                    <p>Dear Staff/Admin,</p>
                    <p>Order <strong>#{$assignment['order_number']}</strong> has been successfully delivered.</p>
                    
                    <div style='background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h3 style='margin-top: 0;'>Delivery Details</h3>
                        <p><strong>Order Number:</strong> #{$assignment['order_number']}</p>
                        <p><strong>Customer:</strong> " . htmlspecialchars($customer['name'] ?? 'N/A') . "</p>
                        <p><strong>Delivery Address:</strong> " . htmlspecialchars($assignment['delivery_address']) . "</p>
                        <p><strong>Total Amount:</strong> ₱" . number_format($assignment['total_amount'], 2) . "</p>
                        <p><strong>Driver:</strong> {$assignment['driver_name']}</p>
                        " . ($deliveryProofImage ? "<p><strong>Proof of Delivery:</strong> Photo uploaded</p>" : "") . "
                    </div>
                    
                    <p>You can view the complete delivery details in the admin panel.</p>
                    
                    <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                    <p style='color: #6b7280; font-size: 12px;'>This is an automated email. Please do not reply.</p>
                </div>
            ";
            if (function_exists('send_app_email')) {
                send_app_email($staffEmails, $staffEmailSubject, $staffEmailBody);
            }
        }
    } else {
        // Create notification for other status updates
        $notificationMsg = "Delivery status updated for order {$assignment['order_number']}: " . ucfirst(str_replace('_', ' ', $newStatus));
        $notifStmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('order_update', ?, NULL)");
        $notifStmt->bind_param("s", $notificationMsg);
        $notifStmt->execute();
    }
    
    // Commit transaction
    if (!$conn->commit()) {
        error_log("Commit failed: " . $conn->error);
        throw new Exception('Failed to commit transaction: ' . $conn->error);
    }
    
    error_log("Transaction committed successfully for assignment ID: " . $assignmentId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Delivery status updated successfully',
        'assignment_id' => $assignmentId,
        'new_status' => $newStatus
    ]);
    
} catch (mysqli_sql_exception $e) {
    if (isset($conn) && method_exists($conn, 'in_transaction') && $conn->in_transaction) {
        $conn->rollback();
    } elseif (isset($conn)) {
        @$conn->rollback();
    }
    http_response_code(500);
    // Log the full error for debugging
    $errorMsg = "Delivery update SQL error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine();
    error_log($errorMsg);
    // Return more detailed error in development (you can remove this in production)
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
    exit;
} catch (Exception $e) {
    if (isset($conn) && method_exists($conn, 'in_transaction') && $conn->in_transaction) {
        $conn->rollback();
    } elseif (isset($conn)) {
        @$conn->rollback();
    }
    http_response_code(500);
    $errorMsg = "Delivery update error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine();
    error_log($errorMsg);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
    exit;
} catch (Error $e) {
    // Catch fatal errors (PHP 7+)
    if (isset($conn) && method_exists($conn, 'in_transaction') && $conn->in_transaction) {
        @$conn->rollback();
    } elseif (isset($conn)) {
        @$conn->rollback();
    }
    http_response_code(500);
    $errorMsg = "Delivery update fatal error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine();
    error_log($errorMsg);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'type' => get_class($e)
        ]
    ]);
    exit;
}

