<?php
/**
 * Database Setup Script
 * This script creates the database and imports the schema automatically
 */

// Database Configuration (without database name)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';

echo "Setting up database...\n\n";

// Step 1: Connect to MySQL server (without database)
try {
    $conn = new mysqli($db_host, $db_user, $db_pass);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to MySQL server\n";
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage() . "\n");
}

// Step 2: Create database if it doesn't exist
$db_name = 'hardware_online';
$sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if ($conn->query($sql)) {
    echo "✓ Database '$db_name' created or already exists\n";
} else {
    die("Error creating database: " . $conn->error . "\n");
}

// Step 3: Select the database
$conn->select_db($db_name);
echo "✓ Selected database '$db_name'\n\n";

// Step 4: Read and execute the schema file
$schema_file = __DIR__ . '/../docs/database_schema.sql';

if (!file_exists($schema_file)) {
    die("Error: Schema file not found at: $schema_file\n");
}

echo "Reading schema file...\n";
$schema = file_get_contents($schema_file);

// Remove the CREATE DATABASE and USE statements since we already handled that
$schema = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/i', '', $schema);
$schema = preg_replace('/USE\s+\w+;/i', '', $schema);

// Remove comment lines (lines starting with --)
$lines = explode("\n", $schema);
$cleaned_lines = [];
foreach ($lines as $line) {
    $trimmed = trim($line);
    // Skip comment lines and empty lines
    if (!empty($trimmed) && !preg_match('/^--/', $trimmed)) {
        $cleaned_lines[] = $line;
    }
}
$schema = implode("\n", $cleaned_lines);

echo "Executing schema...\n";

// Use multi_query to execute all statements at once
if ($conn->multi_query($schema)) {
    $query_count = 0;
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
        $query_count++;
    } while ($conn->next_result());
    
    echo "✓ Schema imported successfully!\n";
    echo "  - Executed $query_count queries\n";
} else {
    $error = $conn->error;
    // Some errors are expected (like duplicate key errors)
    if (strpos($error, 'Duplicate') === false && 
        strpos($error, 'already exists') === false) {
        echo "Error executing schema: " . $error . "\n";
    } else {
        echo "✓ Schema imported (some warnings may be expected)\n";
    }
}

// Step 4.5: Add store_locations table if it doesn't exist
echo "\nAdding store_locations table...\n";
$locations_sql_file = __DIR__ . '/../docs/add_locations_table.sql';

if (file_exists($locations_sql_file)) {
    $result = $conn->query("SHOW TABLES LIKE 'store_locations'");
    if ($result->num_rows == 0) {
        $locations_sql = file_get_contents($locations_sql_file);
        // Remove comment lines
        $lines = explode("\n", $locations_sql);
        $cleaned_lines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (!empty($trimmed) && !preg_match('/^--/', $trimmed)) {
                $cleaned_lines[] = $line;
            }
        }
        $locations_sql = implode("\n", $cleaned_lines);
        
        if ($conn->multi_query($locations_sql)) {
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->next_result());
            echo "  ✓ Store locations table created\n";
        } else {
            echo "  ⚠ Warning: Could not create store_locations table: " . $conn->error . "\n";
        }
    } else {
        echo "  ✓ Store locations table already exists\n";
    }
} else {
    echo "  ⚠ Warning: add_locations_table.sql not found\n";
}

// Step 4.6: Add email verification columns if they don't exist
echo "\nAdding email verification columns...\n";
$columns_to_add = [
    'email_verified' => "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER email",
    'verification_code' => "ALTER TABLE users ADD COLUMN verification_code VARCHAR(10) DEFAULT NULL AFTER email_verified",
    'verification_expires' => "ALTER TABLE users ADD COLUMN verification_expires DATETIME DEFAULT NULL AFTER verification_code"
];

foreach ($columns_to_add as $column_name => $sql) {
    $result = $conn->query("SHOW COLUMNS FROM users LIKE '$column_name'");
    if ($result->num_rows == 0) {
        if ($conn->query($sql)) {
            echo "  ✓ Column '$column_name' added\n";
        } else {
            // Ignore errors if column already exists or other expected errors
            if (strpos($conn->error, 'Duplicate column') === false) {
                echo "  ⚠ Warning: Could not add column '$column_name': " . $conn->error . "\n";
            }
        }
    } else {
        echo "  ✓ Column '$column_name' already exists\n";
    }
}

// Step 5: Verify tables were created
echo "\nVerifying tables...\n";
$tables = ['users', 'products', 'orders', 'order_items', 'delivery_logs', 'sales_reports', 'notifications', 'store_locations'];
$existing_tables = [];

$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $existing_tables[] = $row[0];
}

foreach ($tables as $table) {
    if (in_array($table, $existing_tables)) {
        echo "  ✓ Table '$table' exists\n";
    } else {
        echo "  ✗ Table '$table' missing\n";
    }
}

// Step 6: Check for default admin user
echo "\nChecking default admin user...\n";
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@hardware.com'");

if ($result) {
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "  ✓ Default admin user exists\n";
        echo "    Email: admin@hardware.com\n";
        echo "    Password: admin123\n";
        echo "    Note: If password doesn't work, run utils/reset_admin.php\n";
    } else {
        echo "  ⚠ Default admin user not found\n";
        echo "    Run utils/reset_admin.php to create admin user\n";
    }
} else {
    echo "  ⚠ Could not check admin user (table may not exist yet)\n";
}

$conn->close();

echo "\n" . str_repeat("=", 50) . "\n";
echo "Database setup completed!\n";
echo "You can now access the application at: http://localhost/hardware\n";
echo str_repeat("=", 50) . "\n";
?>

