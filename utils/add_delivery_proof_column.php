<?php
/**
 * Add Delivery Proof Image Column
 * 
 * This script adds the delivery_proof_image column to delivery_assignments table
 * and creates the uploads/delivery_proof directory
 */

require_once __DIR__ . '/../includes/config.php';

// Check if user is admin (optional security check)
if (!isLoggedIn() || !isAdmin()) {
    die("Access denied. Admin privileges required.");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Add Delivery Proof Image Column</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Add Delivery Proof Image Column</h1>";

try {
    echo "<div class='info'><strong>📊 Database:</strong> " . DB_NAME . "</div>";
    
    // Step 1: Check if column already exists
    echo "<h2>Step 1: Checking if Column Exists</h2>";
    $checkStmt = $conn->query("
        SELECT COUNT(*) as count 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
        AND TABLE_NAME = 'delivery_assignments' 
        AND COLUMN_NAME = 'delivery_proof_image'
    ");
    $result = $checkStmt->fetch_assoc();
    
    if ($result['count'] > 0) {
        echo "<div class='info'>✓ Column 'delivery_proof_image' already exists</div>";
    } else {
        echo "<div class='info'>Column does not exist. Adding it now...</div>";
        
        // Step 2: Add the column
        echo "<h2>Step 2: Adding Column</h2>";
        $addColumnSql = "ALTER TABLE delivery_assignments 
                        ADD COLUMN delivery_proof_image VARCHAR(255) DEFAULT NULL 
                        COMMENT 'Path to proof of delivery photo' 
                        AFTER delivery_completed_at";
        
        if ($conn->query($addColumnSql)) {
            echo "<div class='success'>✓ Successfully added 'delivery_proof_image' column</div>";
        } else {
            throw new Exception($conn->error);
        }
    }
    
    // Step 3: Create upload directory
    echo "<h2>Step 3: Creating Upload Directory</h2>";
    $uploadDir = __DIR__ . '/../uploads/delivery_proof/';
    
    if (!is_dir($uploadDir)) {
        if (mkdir($uploadDir, 0755, true)) {
            echo "<div class='success'>✓ Created directory: uploads/delivery_proof/</div>";
        } else {
            echo "<div class='error'>✗ Failed to create directory: uploads/delivery_proof/</div>";
        }
    } else {
        echo "<div class='info'>✓ Directory already exists: uploads/delivery_proof/</div>";
    }
    
    // Step 4: Verify
    echo "<h2>Step 4: Verification</h2>";
    $verifyStmt = $conn->query("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
        AND TABLE_NAME = 'delivery_assignments' 
        AND COLUMN_NAME = 'delivery_proof_image'
    ");
    
    if ($verifyStmt && $verifyStmt->num_rows > 0) {
        $col = $verifyStmt->fetch_assoc();
        echo "<div class='success'><strong>✅ Column Details:</strong></div>";
        echo "<div class='info'>";
        echo "Column Name: " . htmlspecialchars($col['COLUMN_NAME']) . "<br>";
        echo "Data Type: " . htmlspecialchars($col['DATA_TYPE']) . "<br>";
        echo "Nullable: " . htmlspecialchars($col['IS_NULLABLE']) . "<br>";
        echo "Default: " . htmlspecialchars($col['COLUMN_DEFAULT'] ?? 'NULL') . "<br>";
        echo "</div>";
    }
    
    echo "<div class='success'><strong>🎉 Setup Complete!</strong></div>";
    echo "<div class='info'>Drivers can now upload proof of delivery photos when marking deliveries as 'Delivered'.</div>";
    
} catch (Exception $e) {
    echo "<div class='error'><strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div></body></html>";
?>

