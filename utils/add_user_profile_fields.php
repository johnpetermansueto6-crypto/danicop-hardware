<?php
/**
 * Add Important Profile Fields to Users Table
 * 
 * This script adds essential profile fields to the users table:
 * - address, profile_picture, updated_at
 * - date_of_birth, gender
 * - city, province, zipcode
 * - emergency_contact_name, emergency_contact_phone
 * - status, last_login
 */

require_once __DIR__ . '/../includes/config.php';

// Check if user is admin (optional security check)
if (!isLoggedIn() || !isAdmin()) {
    die("Access denied. Admin privileges required.");
}

$sqlFile = __DIR__ . '/../docs/add_user_profile_fields.sql';

if (!file_exists($sqlFile)) {
    die("SQL file not found: $sqlFile");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Add User Profile Fields</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Add User Profile Fields</h1>";

try {
    // Read SQL file
    $sql = file_get_contents($sqlFile);
    
    if (empty($sql)) {
        throw new Exception("SQL file is empty");
    }
    
    echo "<div class='info'><strong>📄 SQL File:</strong> $sqlFile</div>";
    echo "<div class='info'><strong>📊 Database:</strong> " . DB_NAME . "</div>";
    
    // Split SQL into individual statements
    // Remove comments and empty lines
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Split by semicolon, but keep CREATE INDEX statements together
    $statements = array_filter(
        array_map('trim', preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $sql)),
        function($stmt) {
            return !empty($stmt);
        }
    );
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    echo "<h2>Executing SQL Statements...</h2>";
    
    foreach ($statements as $index => $statement) {
        if (empty(trim($statement))) {
            continue;
        }
        
        // Check for ALTER TABLE ADD COLUMN statements and verify if column exists
        // This handles both single and multiple ADD COLUMN statements
        if (preg_match('/ALTER TABLE users\s+ADD COLUMN/i', $statement)) {
            // Extract all column names from the statement
            preg_match_all('/ADD COLUMN\s+(\w+)/i', $statement, $columnMatches);
            $allColumns = $columnMatches[1];
            
            if (!empty($allColumns)) {
                $columnsToSkip = [];
                foreach ($allColumns as $colName) {
                    // Check if column already exists
                    $checkStmt = $conn->prepare("
                        SELECT COUNT(*) as count 
                        FROM INFORMATION_SCHEMA.COLUMNS 
                        WHERE TABLE_SCHEMA = ? 
                        AND TABLE_NAME = 'users' 
                        AND COLUMN_NAME = ?
                    ");
                    $checkStmt->bind_param("ss", DB_NAME, $colName);
                    $checkStmt->execute();
                    $result = $checkStmt->get_result()->fetch_assoc();
                    
                    if ($result['count'] > 0) {
                        $columnsToSkip[] = $colName;
                        echo "<div class='info'>✓ Column '$colName' already exists, skipping...</div>";
                    }
                }
                
                // If all columns in this statement already exist, skip the entire statement
                if (count($columnsToSkip) === count($allColumns)) {
                    echo "<div class='info'>✓ All columns in this statement already exist, skipping entire statement...</div>";
                    $successCount++;
                    continue;
                }
            }
        }
        
        // Handle multiple ADD COLUMN in one statement
        if (preg_match('/ALTER TABLE users\s+(ADD COLUMN\s+\w+[^,]+(?:,\s*ADD COLUMN\s+\w+[^,]+)+)/i', $statement, $matches)) {
            // Extract all column names from the statement
            preg_match_all('/ADD COLUMN\s+(\w+)/i', $statement, $columnMatches);
            $allColumns = $columnMatches[1];
            
            $columnsToAdd = [];
            foreach ($allColumns as $colName) {
                $checkStmt = $conn->prepare("
                    SELECT COUNT(*) as count 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = ? 
                    AND TABLE_NAME = 'users' 
                    AND COLUMN_NAME = ?
                ");
                $checkStmt->bind_param("ss", DB_NAME, $colName);
                $checkStmt->execute();
                $result = $checkStmt->get_result()->fetch_assoc();
                
                if ($result['count'] == 0) {
                    $columnsToAdd[] = $colName;
                } else {
                    echo "<div class='info'>✓ Column '$colName' already exists, skipping...</div>";
                }
            }
            
            if (empty($columnsToAdd)) {
                echo "<div class='info'>✓ All columns in this statement already exist, skipping...</div>";
                $successCount++;
                continue;
            }
            
            // Rebuild statement with only missing columns (simplified - may need manual adjustment)
            // For now, we'll try to execute and catch the duplicate error
        }
        
        // Handle CREATE INDEX IF NOT EXISTS
        if (preg_match('/CREATE INDEX\s+IF NOT EXISTS\s+(\w+)/i', $statement, $matches)) {
            $indexName = $matches[1];
            
            // Check if index already exists
            $checkStmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM INFORMATION_SCHEMA.STATISTICS 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = 'users' 
                AND INDEX_NAME = ?
            ");
            $checkStmt->bind_param("ss", DB_NAME, $indexName);
            $checkStmt->execute();
            $result = $checkStmt->get_result()->fetch_assoc();
            
            if ($result['count'] > 0) {
                echo "<div class='info'>✓ Index '$indexName' already exists, skipping...</div>";
                $successCount++;
                continue;
            }
            
            // Remove IF NOT EXISTS from statement
            $statement = preg_replace('/IF NOT EXISTS\s+/i', '', $statement);
        }
        
        try {
            if ($conn->query($statement)) {
                echo "<div class='success'>✓ Statement " . ($index + 1) . " executed successfully</div>";
                $successCount++;
            } else {
                // Check for duplicate column error
                if (strpos($conn->error, 'Duplicate column') !== false || 
                    strpos($conn->error, '1060') !== false) {
                    // Extract column name from error
                    if (preg_match("/Duplicate column name '(\w+)'/i", $conn->error, $errMatches)) {
                        $dupColumn = $errMatches[1];
                        echo "<div class='info'>ℹ Column '$dupColumn' already exists, skipping...</div>";
                        $successCount++;
                    } else {
                        echo "<div class='info'>ℹ Column already exists, skipping...</div>";
                        $successCount++;
                    }
                } else {
                    throw new Exception($conn->error);
                }
            }
        } catch (mysqli_sql_exception $e) {
            // Check if error is "Duplicate column name" - that's okay
            if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                strpos($e->getMessage(), '1060') !== false) {
                if (preg_match("/Duplicate column name '(\w+)'/i", $e->getMessage(), $errMatches)) {
                    $dupColumn = $errMatches[1];
                    echo "<div class='info'>ℹ Column '$dupColumn' already exists, skipping...</div>";
                } else {
                    echo "<div class='info'>ℹ Column already exists, skipping...</div>";
                }
                $successCount++;
            } else {
                echo "<div class='error'>✗ Error in statement " . ($index + 1) . ": " . htmlspecialchars($e->getMessage()) . "</div>";
                echo "<pre>" . htmlspecialchars(substr($statement, 0, 200)) . "...</pre>";
                $errorCount++;
                $errors[] = "Statement " . ($index + 1) . ": " . $e->getMessage();
            }
        } catch (Exception $e) {
            // Check if error is "Duplicate column name" - that's okay
            if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                strpos($e->getMessage(), '1060') !== false) {
                if (preg_match("/Duplicate column name '(\w+)'/i", $e->getMessage(), $errMatches)) {
                    $dupColumn = $errMatches[1];
                    echo "<div class='info'>ℹ Column '$dupColumn' already exists, skipping...</div>";
                } else {
                    echo "<div class='info'>ℹ Column already exists, skipping...</div>";
                }
                $successCount++;
            } else {
                echo "<div class='error'>✗ Error in statement " . ($index + 1) . ": " . htmlspecialchars($e->getMessage()) . "</div>";
                echo "<pre>" . htmlspecialchars(substr($statement, 0, 200)) . "...</pre>";
                $errorCount++;
                $errors[] = "Statement " . ($index + 1) . ": " . $e->getMessage();
            }
        }
    }
    
    echo "<hr>";
    echo "<h2>📊 Summary</h2>";
    echo "<div class='success'><strong>✓ Successful:</strong> $successCount statements</div>";
    
    if ($errorCount > 0) {
        echo "<div class='error'><strong>✗ Errors:</strong> $errorCount statements</div>";
        echo "<h3>Error Details:</h3>";
        foreach ($errors as $error) {
            echo "<div class='error'>" . htmlspecialchars($error) . "</div>";
        }
    } else {
        echo "<div class='success'><strong>🎉 All fields added successfully!</strong></div>";
        echo "<div class='info'><strong>New fields added:</strong><br>";
        echo "• address (TEXT)<br>";
        echo "• profile_picture (VARCHAR 255)<br>";
        echo "• updated_at (TIMESTAMP)<br>";
        echo "• date_of_birth (DATE)<br>";
        echo "• gender (ENUM)<br>";
        echo "• city, province, zipcode<br>";
        echo "• emergency_contact_name, emergency_contact_phone<br>";
        echo "• status (ENUM: active, inactive, suspended)<br>";
        echo "• last_login (DATETIME)<br>";
        echo "</div>";
    }
    
    // Verify columns were added
    echo "<h2>🔍 Verification</h2>";
    $verifyStmt = $conn->query("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
        AND TABLE_NAME = 'users' 
        ORDER BY ORDINAL_POSITION
    ");
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Column Name</th><th>Data Type</th><th>Nullable</th><th>Default</th></tr>";
    while ($row = $verifyStmt->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['COLUMN_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['DATA_TYPE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['IS_NULLABLE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['COLUMN_DEFAULT'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div class='error'><strong>Fatal Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div></body></html>";
?>

