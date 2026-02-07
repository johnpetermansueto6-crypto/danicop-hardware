<?php
/**
 * Google Maps API Key Setup Helper
 * This script helps you configure your Google Maps API key
 */

require_once '../includes/config.php';

// Check if user is admin
if (!isLoggedIn() || getUserRole() !== 'superadmin') {
    die('Access denied. Only superadmin can access this page.');
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_api_key'])) {
    $api_key = trim($_POST['api_key'] ?? '');
    
    if (empty($api_key)) {
        $error = 'API key cannot be empty';
    } else {
        // Read config file
        $config_file = dirname(__DIR__) . '/includes/config.php';
        $config_content = file_get_contents($config_file);
        
        // Replace the API key
        $pattern = "/define\('GOOGLE_MAPS_API_KEY',\s*'[^']*'\);/";
        $replacement = "define('GOOGLE_MAPS_API_KEY', '" . addslashes($api_key) . "');";
        
        if (preg_match($pattern, $config_content)) {
            $config_content = preg_replace($pattern, $replacement, $config_content);
            
            if (file_put_contents($config_file, $config_content)) {
                $message = 'API key saved successfully! The page will reload in 3 seconds.';
                echo "<script>setTimeout(() => window.location.reload(), 3000);</script>";
            } else {
                $error = 'Failed to save API key. Please check file permissions.';
            }
        } else {
            $error = 'Could not find API key definition in config file.';
        }
    }
}

// Get current API key (masked)
$current_key = defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
$is_configured = $current_key !== 'YOUR_GOOGLE_MAPS_API_KEY_HERE' && !empty($current_key);
$masked_key = $is_configured ? substr($current_key, 0, 10) . '...' . substr($current_key, -4) : 'Not configured';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Maps API Setup - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="flex items-center mb-6">
                <i class="fas fa-map-marked-alt text-4xl text-blue-600 mr-4"></i>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Google Maps API Setup</h1>
                    <p class="text-gray-600">Configure your Google Maps API key to enable map features</p>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Current Status -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-blue-900 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>Current Status
                </h2>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-800 font-semibold">
                            API Key: <span class="font-mono"><?= htmlspecialchars($masked_key) ?></span>
                        </p>
                        <p class="text-sm text-blue-600 mt-1">
                            <?php if ($is_configured): ?>
                                <i class="fas fa-check-circle text-green-600"></i> API key is configured
                            <?php else: ?>
                                <i class="fas fa-times-circle text-red-600"></i> API key needs to be configured
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Setup Form -->
            <form method="POST" action="setup_maps_api.php" class="space-y-6">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">
                        <i class="fas fa-key mr-2"></i>Google Maps API Key
                    </label>
                    <input type="text" 
                           name="api_key" 
                           value="<?= htmlspecialchars($current_key) ?>"
                           placeholder="AIzaSy..."
                           required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono">
                    <p class="text-sm text-gray-600 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Enter your Google Maps API key. Get one from 
                        <a href="https://console.cloud.google.com/google/maps-apis" target="_blank" class="text-blue-600 hover:underline">
                            Google Cloud Console
                        </a>
                    </p>
                </div>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <h3 class="font-bold text-yellow-900 mb-2">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Required APIs
                    </h3>
                    <p class="text-sm text-yellow-800 mb-2">Make sure to enable these APIs in Google Cloud Console:</p>
                    <ul class="list-disc list-inside text-sm text-yellow-800 space-y-1">
                        <li><strong>Maps JavaScript API</strong> - For displaying maps</li>
                        <li><strong>Geocoding API</strong> - For converting addresses to coordinates</li>
                        <li><strong>Places API</strong> - For address autocomplete</li>
                    </ul>
                </div>
                
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-bold text-gray-900 mb-2">
                        <i class="fas fa-question-circle mr-2"></i>How to Get Your API Key
                    </h3>
                    <ol class="list-decimal list-inside text-sm text-gray-700 space-y-2">
                        <li>Go to <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a></li>
                        <li>Create a new project or select an existing one</li>
                        <li>Enable the required APIs (listed above)</li>
                        <li>Go to "Credentials" → "Create Credentials" → "API Key"</li>
                        <li>Copy your API key and paste it in the field above</li>
                        <li>(Optional) Restrict the API key to your domain for security</li>
                    </ol>
                </div>
                
                <div class="flex gap-4">
                    <a href="../admin/index.php" class="flex-1 bg-gray-500 text-white py-3 rounded-lg hover:bg-gray-600 text-center font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Admin
                    </a>
                    <button type="submit" name="save_api_key" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-semibold">
                        <i class="fas fa-save mr-2"></i> Save API Key
                    </button>
                </div>
            </form>
            
            <!-- Test Section -->
            <?php if ($is_configured): ?>
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-xl font-bold mb-4">Test Your API Key</h3>
                    <p class="text-gray-600 mb-4">Click the button below to test if your API key is working:</p>
                    <a href="../admin/index.php?page=locations" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-semibold">
                        <i class="fas fa-map-marker-alt mr-2"></i> Test in Locations Page
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

