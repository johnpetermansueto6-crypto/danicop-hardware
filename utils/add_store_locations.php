<?php
/**
 * Add Store Locations Table
 * This script creates the store_locations table if it doesn't exist
 */

require_once __DIR__ . '/../includes/config.php';

echo "Adding store_locations table...\n\n";

// Check if table already exists
$result = $conn->query("SHOW TABLES LIKE 'store_locations'");
if ($result->num_rows > 0) {
    echo "✓ Table 'store_locations' already exists\n";
    exit(0);
}

// Read and execute the SQL file
$sql_file = __DIR__ . '/../docs/add_locations_table.sql';

if (!file_exists($sql_file)) {
    die("Error: SQL file not found at: $sql_file\n");
}

$sql = file_get_contents($sql_file);

// Execute the SQL
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    
    echo "✓ Table 'store_locations' created successfully!\n";
    echo "✓ Default store location inserted\n";
} else {
    die("Error creating table: " . $conn->error . "\n");
}

// Verify the table was created
$result = $conn->query("SHOW TABLES LIKE 'store_locations'");
if ($result->num_rows > 0) {
    echo "\n✓ Verification: Table exists\n";
    
    // Check for default location
    $result = $conn->query("SELECT COUNT(*) as count FROM store_locations");
    $row = $result->fetch_assoc();
    echo "✓ Store locations in database: " . $row['count'] . "\n";
} else {
    echo "\n✗ Error: Table was not created\n";
}

echo "\nDone!\n";
?>

