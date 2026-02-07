<?php
require_once '../includes/config.php';

// Only allow logged-in staff to access dashboard
if (!isLoggedIn() || getUserRole() !== 'staff') {
    redirect('../auth/login.php');
}

// Preserve login success parameter if present
$redirect_url = 'dashboard.php';
if (isset($_GET['login']) && $_GET['login'] === 'success') {
    $redirect_url .= '?login=success';
}

// Simple redirect to main staff dashboard
redirect($redirect_url);


