<?php
/**
 * Automated Setup Script
 * This script automatically sets up the database tables and helps configure the API key
 */

require_once '../includes/config.php';

// Check if user is admin
if (!isLoggedIn() || getUserRole() !== 'superadmin') {
    die('Access denied. Only superadmin can access this page.');
}

$messages = [];
$errors = [];
$success = false;

// Handle API key setup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_api_key'])) {
    $api_key = trim($_POST['api_key'] ?? '');
    
    if (empty($api_key)) {
        $errors[] = 'API key cannot be empty';
    } else {
        $config_file = dirname(__DIR__) . '/includes/config.php';
        $config_content = file_get_contents($config_file);
        
        $pattern = "/define\('GOOGLE_MAPS_API_KEY',\s*'[^']*'\);/";
        $replacement = "define('GOOGLE_MAPS_API_KEY', '" . addslashes($api_key) . "');";
        
        if (preg_match($pattern, $config_content)) {
            $config_content = preg_replace($pattern, $replacement, $config_content);
            
            if (file_put_contents($config_file, $config_content)) {
                $messages[] = '✓ API key saved successfully!';
                $success = true;
            } else {
                $errors[] = 'Failed to save API key. Please check file permissions.';
            }
        } else {
            $errors[] = 'Could not find API key definition in config file.';
        }
    }
}

// Handle database setup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_database'])) {
    $setup_queries = [];
    
    // Check if store_locations table exists
    $result = $conn->query("SHOW TABLES LIKE 'store_locations'");
    if ($result->num_rows == 0) {
        $setup_queries[] = "CREATE TABLE IF NOT EXISTS store_locations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            address TEXT NOT NULL,
            latitude DECIMAL(10, 8) DEFAULT NULL,
            longitude DECIMAL(11, 8) DEFAULT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            email VARCHAR(255) DEFAULT NULL,
            hours TEXT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_is_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $setup_queries[] = "INSERT INTO store_locations (name, address, phone, hours, is_active) VALUES
            ('Main Store', '123 Hardware Street, City, Philippines', '(02) 1234-5678', 'Mon-Sat: 8:00 AM - 6:00 PM\nSun: 9:00 AM - 4:00 PM', 1)
            ON DUPLICATE KEY UPDATE name=name";
    }
    
    // Check if delivery coordinates columns exist
    $result = $conn->query("SHOW COLUMNS FROM orders LIKE 'delivery_latitude'");
    if ($result->num_rows == 0) {
        $setup_queries[] = "ALTER TABLE orders 
            ADD COLUMN delivery_latitude DECIMAL(10, 8) DEFAULT NULL AFTER delivery_address,
            ADD COLUMN delivery_longitude DECIMAL(11, 8) DEFAULT NULL AFTER delivery_latitude";
    }
    
    // Execute all setup queries
    foreach ($setup_queries as $query) {
        if (!$conn->query($query)) {
            $errors[] = 'Database error: ' . $conn->error;
            break;
        }
    }
    
    if (empty($errors)) {
        if (!empty($setup_queries)) {
            $messages[] = '✓ Database tables and columns created successfully!';
        } else {
            $messages[] = '✓ Database is already set up correctly.';
        }
        $success = true;
    }
}

// Check current status
$locations_table_exists = false;
$delivery_coords_exist = false;
$api_key_configured = false;

// Check if store_locations table exists
$result = $conn->query("SHOW TABLES LIKE 'store_locations'");
$locations_table_exists = $result->num_rows > 0;

// Check if delivery coordinates columns exist
$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'delivery_latitude'");
$delivery_coords_exist = $result->num_rows > 0;

// Check API key
$current_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
$api_key_configured = $current_key !== 'YOUR_GOOGLE_MAPS_API_KEY_HERE' && !empty($current_key);

$all_setup = $locations_table_exists && $delivery_coords_exist && $api_key_configured;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automated Setup - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="flex items-center mb-6">
                <i class="fas fa-magic text-4xl text-blue-600 mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Automated Setup</h1>
                    <p class="text-gray-600">Automatically configure your system</p>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <?php foreach ($errors as $error): ?>
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($messages)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <?php foreach ($messages as $msg): ?>
                        <div class="flex items-center mb-2">
                            <span><?= htmlspecialchars($msg) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($all_setup): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-6 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-bold text-lg mb-1">All Set! ✓</h3>
                            <p>Your system is fully configured and ready to use.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Status Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-<?= $locations_table_exists ? 'green' : 'yellow' ?>-50 border-2 border-<?= $locations_table_exists ? 'green' : 'yellow' ?>-300 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-gray-800">Store Locations Table</h3>
                        <?php if ($locations_table_exists): ?>
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle text-yellow-600 text-xl"></i>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-gray-600">
                        <?= $locations_table_exists ? 'Table exists' : 'Needs setup' ?>
                    </p>
                </div>
                
                <div class="bg-<?= $delivery_coords_exist ? 'green' : 'yellow' ?>-50 border-2 border-<?= $delivery_coords_exist ? 'green' : 'yellow' ?>-300 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-gray-800">Delivery Coordinates</h3>
                        <?php if ($delivery_coords_exist): ?>
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle text-yellow-600 text-xl"></i>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-gray-600">
                        <?= $delivery_coords_exist ? 'Columns exist' : 'Needs setup' ?>
                    </p>
                </div>
                
                <div class="bg-<?= $api_key_configured ? 'green' : 'yellow' ?>-50 border-2 border-<?= $api_key_configured ? 'green' : 'yellow' ?>-300 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-gray-800">Google Maps API</h3>
                        <?php if ($api_key_configured): ?>
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle text-yellow-600 text-xl"></i>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-gray-600">
                        <?= $api_key_configured ? 'Configured' : 'Needs setup' ?>
                    </p>
                </div>
            </div>
            
            <!-- Database Setup -->
            <?php if (!$locations_table_exists || !$delivery_coords_exist): ?>
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-blue-900 mb-4">
                        <i class="fas fa-database mr-2"></i>Database Setup
                    </h2>
                    <p class="text-blue-800 mb-4">
                        The following database tables/columns need to be created:
                    </p>
                    <ul class="list-disc list-inside text-blue-800 mb-4 space-y-2">
                        <?php if (!$locations_table_exists): ?>
                            <li><strong>store_locations</strong> table - For managing store locations</li>
                        <?php endif; ?>
                        <?php if (!$delivery_coords_exist): ?>
                            <li><strong>delivery_latitude</strong> and <strong>delivery_longitude</strong> columns in orders table</li>
                        <?php endif; ?>
                    </ul>
                    <form method="POST" action="auto_setup.php">
                        <button type="submit" name="setup_database" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 font-semibold">
                            <i class="fas fa-magic mr-2"></i> Setup Database Automatically
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        <span class="text-green-800 font-semibold">Database is properly configured</span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- API Key Setup -->
            <?php if (!$api_key_configured): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-bold text-yellow-900 mb-4">
                        <i class="fas fa-map-marked-alt mr-2"></i>Google Maps API Key
                    </h2>
                    <p class="text-yellow-800 mb-4">
                        To enable map features, you need to configure your Google Maps API key.
                    </p>
                    
                    <form method="POST" action="auto_setup.php" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">
                                <i class="fas fa-key mr-2"></i>Google Maps API Key
                            </label>
                            <input type="text" 
                                   name="api_key" 
                                   placeholder="AIzaSy..."
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 font-mono">
                            <p class="text-sm text-gray-600 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Get your API key from 
                                <a href="https://console.cloud.google.com/google/maps-apis" target="_blank" class="text-blue-600 hover:underline">
                                    Google Cloud Console
                                </a>
                            </p>
                        </div>
                        
                        <div class="bg-white border border-yellow-200 rounded-lg p-4">
                            <h3 class="font-bold text-yellow-900 mb-2">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Required APIs to Enable:
                            </h3>
                            <ul class="list-disc list-inside text-sm text-yellow-800 space-y-1">
                                <li><strong>Maps JavaScript API</strong></li>
                                <li><strong>Geocoding API</strong></li>
                                <li><strong>Places API</strong></li>
                            </ul>
                        </div>
                        
                        <button type="submit" name="save_api_key" class="bg-yellow-600 text-white px-6 py-3 rounded-lg hover:bg-yellow-700 font-semibold">
                            <i class="fas fa-save mr-2"></i> Save API Key
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <span class="text-green-800 font-semibold">Google Maps API key is configured</span>
                        </div>
                        <span class="text-sm text-green-700 font-mono"><?= substr($current_key, 0, 10) ?>...<?= substr($current_key, -4) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Quick Actions -->
            <div class="flex gap-4 mt-8 pt-8 border-t border-gray-200">
                <a href="../admin/index.php" class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 text-center font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Admin
                </a>
                <?php if ($all_setup): ?>
                    <a href="../admin/index.php?page=locations" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 text-center font-semibold">
                        <i class="fas fa-map-marker-alt mr-2"></i> Go to Locations
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

