<?php
require_once '../includes/config.php';
require_once '../includes/mailer.php';

$error = '';
$success = '';

// Pull any flash success message from registration redirect
if (!empty($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $code  = sanitize($_POST['code'] ?? '');
    $isResend = isset($_POST['resend']);

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!$isResend && empty($code)) {
        $error = 'Please enter both your email and verification code.';
    } else {
        if ($isResend) {
            // Handle resend verification code with 1-minute cooldown
            $cooldownSeconds = 60;
            if (!isset($_SESSION['verification_resend'])) {
                $_SESSION['verification_resend'] = [];
            }
            $key = strtolower($email);
            $lastSent = $_SESSION['verification_resend'][$key] ?? 0;
            $now = time();

            if ($now - $lastSent < $cooldownSeconds) {
                $remaining = $cooldownSeconds - ($now - $lastSent);
                $error = 'Please wait ' . $remaining . ' seconds before requesting a new code.';
            } else {
                // Find user that is not yet verified
                $stmt = $conn->prepare("SELECT id, name, email_verified FROM users WHERE email = ? LIMIT 1");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();

                if (!$user) {
                    $error = 'No account found for that email.';
                } elseif (!empty($user['email_verified'])) {
                    $success = 'Your email is already verified. You can log in now.';
                } else {
                    // Generate new code and expiry
                    $newCode = (string) random_int(100000, 999999);
                    $expires = date('Y-m-d H:i:s', time() + 3600);

                    $u = $conn->prepare("UPDATE users SET verification_code = ?, verification_expires = ? WHERE id = ?");
                    $u->bind_param("ssi", $newCode, $expires, $user['id']);
                    $u->execute();

                    $subject = 'Your new verification code - Danicop Hardware';
                    $body = '<p>Hi ' . htmlspecialchars($user['name'] ?? '') . ',</p>
                        <p>Here is your new verification code for <strong>Danicop Hardware</strong>:</p>
                        <p style="font-size:24px;font-weight:bold;letter-spacing:4px;">' . $newCode . '</p>
                        <p>This code will expire in 1 hour.</p>
                        <p>Regards,<br>Danicop Hardware</p>';

                    if (send_app_email([$email => ($user['name'] ?? $email)], $subject, $body)) {
                        $_SESSION['verification_resend'][$key] = $now;
                        $_SESSION['pending_verification_email'] = $email;
                        $success = 'We sent a new verification code to your email.';
                        $error = '';
                    } else {
                        $error = 'Failed to resend verification email. Please try again later.';
                    }
                }
            }
        } else {
            // Normal verification flow (check latest stored code for this email)
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ? LIMIT 1");
            $stmt->bind_param("ss", $email, $code);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user) {
                $stmt = $conn->prepare("UPDATE users SET email_verified = 1, verification_code = NULL, verification_expires = NULL WHERE id = ?");
                $stmt->bind_param("i", $user['id']);
                if ($stmt->execute()) {
                    $success = 'Your email has been verified. You can now log in.';
                } else {
                    $error = 'Failed to verify email. Please try again.';
                }
            } else {
                $error = 'Invalid or expired verification code.';
            }
        }
    }
}

// If this is a GET request (or initial load), pre-fill email from session if available
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (empty($_POST['email']) && !empty($_SESSION['pending_verification_email'])) {
        $_POST['email'] = $_SESSION['pending_verification_email'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-2xl p-8">
        <h1 class="text-2xl font-extrabold mb-4 text-center bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
            Verify Your Email
        </h1>
        <p class="text-gray-600 text-sm mb-6 text-center">
            We have sent a 6-digit verification code to your email. Enter it below to activate your account.
        </p>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg mb-4">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="verify.php" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-envelope mr-2 text-blue-600"></i>Email Address
                </label>
                <input type="email" name="email" required
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-key mr-2 text-blue-600"></i>Verification Code
                </label>
                <input type="text" name="code" required maxlength="6"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 tracking-widest text-center"
                       placeholder="123456"
                       value="<?= htmlspecialchars($_POST['code'] ?? '') ?>">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                Verify Email
            </button>
        </form>

        <!-- Resend code form -->
        <form method="POST" action="verify.php" class="mt-4 space-y-2 text-center">
            <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <input type="hidden" name="resend" value="1">
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-blue-500 text-blue-600 rounded-lg text-sm font-semibold hover:bg-blue-50 transition-colors">
                <i class="fas fa-redo mr-2"></i> Resend verification code
            </button>
            <p class="text-xs text-gray-500">
                You can request a new code once every 60 seconds.
            </p>
        </form>

        <div class="mt-6 text-center text-gray-500 text-sm">
            <a href="login.php" class="text-blue-600 hover:text-blue-700 font-semibold">
                <i class="fas fa-arrow-left mr-1"></i> Back to Login
            </a>
        </div>
    </div>

    <?php if ($success && !$error): ?>
    <script>
        // After successful verification, notify the user and send them to the login page.
        setTimeout(function () {
            alert('Your email has been verified. You can now log in.');
            window.location.href = 'login.php';
        }, 300);
    </script>
    <?php endif; ?>
</body>
</html>


