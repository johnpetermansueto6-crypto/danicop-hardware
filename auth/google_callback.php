<?php
require_once '../includes/config.php';

if (!isGoogleAuthEnabled()) {
    die('Google Login is not configured.');
}

// Read state (used for redirect info)
$stateRaw = $_GET['state'] ?? '';
$state = [];
if ($stateRaw) {
    $decoded = json_decode(base64_decode($stateRaw), true);
    if (is_array($decoded)) {
        $state = $decoded;
    }
}
$redirectAfter = $state['redirect'] ?? '';

if (!isset($_GET['code'])) {
    // User denied or error
    redirect('login.php');
}

$code = $_GET['code'];

// Exchange code for access token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$postData = [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? null;

if (!$accessToken) {
    // Failed to get token
    redirect('login.php');
}

// Get user info from Google
$userinfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . urlencode($accessToken);
$userInfoResponse = file_get_contents($userinfoUrl);
$googleUser = json_decode($userInfoResponse, true);

if (!is_array($googleUser) || empty($googleUser['id']) || empty($googleUser['email'])) {
    redirect('login.php');
}

$googleId = $googleUser['id'];
$email = $googleUser['email'];
$name = $googleUser['name'] ?? $email;

// Find or create local user
// 1) Try by google_id
$stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ? LIMIT 1");
$stmt->bind_param("s", $googleId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // 2) Try by email (existing account to link)
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Link existing account with Google
        $stmt = $conn->prepare("UPDATE users SET google_id = ?, auth_provider = 'google' WHERE id = ?");
        $stmt->bind_param("si", $googleId, $user['id']);
        $stmt->execute();
        $user['google_id'] = $googleId;
        $user['auth_provider'] = 'google';
    } else {
        // 3) Create new customer account
        $randomPassword = bin2hex(random_bytes(8));
        $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);
        $role = 'customer';
        $profileCompleted = 0;

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, google_id, auth_provider, profile_completed) VALUES (?, ?, ?, ?, ?, 'google', ?)");
        $stmt->bind_param("sssssi", $name, $email, $hashedPassword, $role, $googleId, $profileCompleted);
        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            redirect('login.php');
        }
    }
}

if (!$user) {
    redirect('login.php');
}

// Ensure default values for new columns
if (!isset($user['auth_provider'])) {
    $user['auth_provider'] = 'local';
}
if (!isset($user['profile_completed'])) {
    $user['profile_completed'] = 0;
}

// Log the user in
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['auth_provider'] = $user['auth_provider'];
$_SESSION['profile_completed'] = (int)$user['profile_completed'];

// Decide where to send the user next
$needsProfile = ($user['auth_provider'] === 'google' && !$user['profile_completed']);

if ($needsProfile) {
    // Force user to complete profile before normal flow
    redirect('../customer/profile.php?complete_profile=1');
}

if ($redirectAfter === 'checkout') {
    redirect('../customer/checkout.php');
}

// Default: go to homepage
redirect('../index.php');
?>


