<?php
/**
 * Add Email Verification Columns
 * This script adds email verification fields to the users table
 */

require_once __DIR__ . '/../includes/config.php';

echo "Adding email verification columns...\n\n";

// Check if columns already exist
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'email_verified'");
if ($result->num_rows > 0) {
    echo "✓ Column 'email_verified' already exists\n";
} else {
    $sql = "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER email";
    if ($conn->query($sql)) {
        echo "✓ Column 'email_verified' added successfully\n";
    } else {
        echo "✗ Error adding email_verified: " . $conn->error . "\n";
    }
}

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'verification_code'");
if ($result->num_rows > 0) {
    echo "✓ Column 'verification_code' already exists\n";
} else {
    $sql = "ALTER TABLE users ADD COLUMN verification_code VARCHAR(10) DEFAULT NULL AFTER email_verified";
    if ($conn->query($sql)) {
        echo "✓ Column 'verification_code' added successfully\n";
    } else {
        echo "✗ Error adding verification_code: " . $conn->error . "\n";
    }
}

$result = $conn->query("SHOW COLUMNS FROM users LIKE 'verification_expires'");
if ($result->num_rows > 0) {
    echo "✓ Column 'verification_expires' already exists\n";
} else {
    $sql = "ALTER TABLE users ADD COLUMN verification_expires DATETIME DEFAULT NULL AFTER verification_code";
    if ($conn->query($sql)) {
        echo "✓ Column 'verification_expires' added successfully\n";
    } else {
        echo "✗ Error adding verification_expires: " . $conn->error . "\n";
    }
}

// Verify all columns exist
echo "\nVerifying columns...\n";
$columns = ['email_verified', 'verification_code', 'verification_expires'];
$result = $conn->query("SHOW COLUMNS FROM users");
$existing_columns = [];
while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

foreach ($columns as $column) {
    if (in_array($column, $existing_columns)) {
        echo "  ✓ Column '$column' exists\n";
    } else {
        echo "  ✗ Column '$column' missing\n";
    }
}

echo "\nDone!\n";
?>

