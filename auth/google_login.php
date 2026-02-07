<?php
require_once '../includes/config.php';

if (!isGoogleAuthEnabled()) {
    die('Google Login is not configured. Please set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in includes/config.php.');
}

// Optional redirect (e.g. checkout) passed through state
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
$stateData = [
    'redirect' => $redirect,
];
$state = urlencode(base64_encode(json_encode($stateData)));

$params = [
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'email profile',
    'access_type'   => 'online',
    'include_granted_scopes' => 'true',
    'state'         => $state,
    'prompt'        => 'select_account',
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
?>


