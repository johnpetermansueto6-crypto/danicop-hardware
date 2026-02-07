<?php
/**
 * Fix Delivery Assignments Foreign Key Constraint
 * 
 * This script fixes the foreign key constraint in delivery_assignments table
 * that incorrectly references the old 'drivers' table instead of 'users' table
 */

require_once __DIR__ . '/../includes/config.php';

// Check if user is admin (optional security check)
if (!isLoggedIn() || !isAdmin()) {
    die("Access denied. Admin privileges required.");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Delivery Assignments Foreign Key</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Fix Delivery Assignments Foreign Key</h1>";

try {
    echo "<div class='info'><strong>📊 Database:</strong> " . DB_NAME . "</div>";
    
    // Step 1: Check current foreign key constraints
    echo "<h2>Step 1: Checking Current Foreign Key Constraints</h2>";
    $checkStmt = $conn->query("
        SELECT 
            CONSTRAINT_NAME,
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '" . DB_NAME . "'
            AND TABLE_NAME = 'delivery_assignments'
            AND COLUMN_NAME = 'driver_id'
    ");
    
    $constraints = [];
    $oldConstraintName = null;
    
    if ($checkStmt && $checkStmt->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Constraint Name</th><th>Column</th><th>References Table</th><th>References Column</th></tr>";
        while ($row = $checkStmt->fetch_assoc()) {
            $constraints[] = $row;
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['CONSTRAINT_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['COLUMN_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['REFERENCED_TABLE_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['REFERENCED_COLUMN_NAME']) . "</td>";
            echo "</tr>";
            
            // Check if it references the wrong table
            if ($row['REFERENCED_TABLE_NAME'] === 'drivers') {
                $oldConstraintName = $row['CONSTRAINT_NAME'];
                echo "<div class='error'>⚠ Found incorrect foreign key: <strong>{$oldConstraintName}</strong> references 'drivers' table!</div>";
            }
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>No foreign key constraints found for driver_id column.</div>";
    }
    
    // Step 2: Drop old constraint if it exists
    if ($oldConstraintName) {
        echo "<h2>Step 2: Dropping Old Foreign Key Constraint</h2>";
        try {
            $dropSql = "ALTER TABLE delivery_assignments DROP FOREIGN KEY `{$oldConstraintName}`";
            if ($conn->query($dropSql)) {
                echo "<div class='success'>✓ Successfully dropped foreign key constraint: <strong>{$oldConstraintName}</strong></div>";
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            // Check if error is "Unknown key" - that's okay, constraint might already be dropped
            if (strpos($e->getMessage(), 'Unknown key') !== false || 
                strpos($conn->error, 'Unknown key') !== false) {
                echo "<div class='info'>ℹ Constraint already removed or doesn't exist.</div>";
            } else {
                echo "<div class='error'>✗ Error dropping constraint: " . htmlspecialchars($conn->error ?: $e->getMessage()) . "</div>";
            }
        }
    } else {
        echo "<h2>Step 2: No Incorrect Constraint Found</h2>";
        echo "<div class='info'>No foreign key constraint referencing 'drivers' table was found. Checking if correct constraint exists...</div>";
    }
    
    // Step 3: Check if correct constraint already exists
    echo "<h2>Step 3: Checking for Correct Foreign Key Constraint</h2>";
    $checkCorrectStmt = $conn->query("
        SELECT 
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '" . DB_NAME . "'
            AND TABLE_NAME = 'delivery_assignments'
            AND COLUMN_NAME = 'driver_id'
            AND REFERENCED_TABLE_NAME = 'users'
    ");
    
    $correctConstraintExists = $checkCorrectStmt && $checkCorrectStmt->num_rows > 0;
    
    if ($correctConstraintExists) {
        echo "<div class='success'>✓ Correct foreign key constraint already exists (references 'users' table)</div>";
    } else {
        echo "<div class='info'>Correct constraint not found. Adding new foreign key constraint...</div>";
        
        // Step 4: Add correct foreign key constraint
        echo "<h2>Step 4: Adding Correct Foreign Key Constraint</h2>";
        try {
            $addSql = "ALTER TABLE delivery_assignments 
                       ADD CONSTRAINT delivery_assignments_driver_id_fk 
                       FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE RESTRICT";
            
            if ($conn->query($addSql)) {
                echo "<div class='success'>✓ Successfully added foreign key constraint: <strong>delivery_assignments_driver_id_fk</strong></div>";
                echo "<div class='success'>✓ Constraint now correctly references <strong>users.id</strong> instead of drivers.id</div>";
            } else {
                // Check if error is "Duplicate key" - constraint might already exist with different name
                if (strpos($conn->error, 'Duplicate key') !== false || 
                    strpos($conn->error, 'already exists') !== false) {
                    echo "<div class='info'>ℹ Constraint already exists (possibly with different name).</div>";
                } else {
                    throw new Exception($conn->error);
                }
            }
        } catch (Exception $e) {
            echo "<div class='error'>✗ Error adding constraint: " . htmlspecialchars($conn->error ?: $e->getMessage()) . "</div>";
        }
    }
    
    // Step 5: Verify final state
    echo "<h2>Step 5: Final Verification</h2>";
    $finalCheckStmt = $conn->query("
        SELECT 
            CONSTRAINT_NAME,
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '" . DB_NAME . "'
            AND TABLE_NAME = 'delivery_assignments'
            AND COLUMN_NAME = 'driver_id'
    ");
    
    if ($finalCheckStmt && $finalCheckStmt->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Constraint Name</th><th>Column</th><th>References Table</th><th>References Column</th><th>Status</th></tr>";
        $allCorrect = true;
        while ($row = $finalCheckStmt->fetch_assoc()) {
            $isCorrect = $row['REFERENCED_TABLE_NAME'] === 'users';
            if (!$isCorrect) $allCorrect = false;
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['CONSTRAINT_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['COLUMN_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['REFERENCED_TABLE_NAME']) . "</td>";
            echo "<td>" . htmlspecialchars($row['REFERENCED_COLUMN_NAME']) . "</td>";
            echo "<td>" . ($isCorrect ? "<span style='color: green;'>✓ Correct</span>" : "<span style='color: red;'>✗ Incorrect</span>") . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if ($allCorrect) {
            echo "<div class='success'><strong>🎉 All foreign key constraints are now correct!</strong></div>";
            echo "<div class='info'>The delivery_assignments.driver_id now correctly references users.id</div>";
        } else {
            echo "<div class='error'>⚠ Some constraints still reference incorrect tables. Please review the table above.</div>";
        }
    } else {
        echo "<div class='warning'>No foreign key constraints found. This might indicate an issue.</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'><strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div></body></html>";
?>

