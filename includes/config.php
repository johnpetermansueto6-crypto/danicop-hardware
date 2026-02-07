<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'danicop');
define('DB_PASS', 'danicop');
define('DB_NAME', 'danicop');

// Google Maps API Key
// Get your API key from: https://console.cloud.google.com/google/maps-apis
// Make sure to enable: Maps JavaScript API, Geocoding API, Places API
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');

// Google OAuth (for "Continue with Google")
// 1. Create credentials at https://console.cloud.google.com/apis/credentials
// 2. Set an OAuth 2.0 Client ID for a Web application
// 3. Add the authorized redirect URI (example for localhost below)
// 4. Put your Client ID and Client Secret here
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
// Default redirect for local dev; adjust if your base URL changes
define('GOOGLE_REDIRECT_URI', 'http://mwa/hardware/auth/google_callback.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Helper Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

function isAdmin() {
    return in_array(getUserRole(), ['superadmin', 'staff']);
}

function isDriver() {
    return getUserRole() === 'driver';
}

function isGoogleAuthEnabled() {
    return defined('GOOGLE_CLIENT_ID') 
        && GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID_HERE' 
        && !empty(GOOGLE_CLIENT_ID)
        && defined('GOOGLE_CLIENT_SECRET')
        && GOOGLE_CLIENT_SECRET !== 'YOUR_GOOGLE_CLIENT_SECRET_HERE'
        && !empty(GOOGLE_CLIENT_SECRET);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}
