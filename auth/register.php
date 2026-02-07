<?php
require_once '../includes/config.php';
require_once '../includes/mailer.php';

$error = '';
$success = '';
$showVerificationModal = false;
$verificationEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer';
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
            
            if ($stmt->execute()) {
                $userId = $stmt->insert_id;

                // Generate verification code and expiry (1 hour)
                $code = (string) random_int(100000, 999999);
                $expires = date('Y-m-d H:i:s', time() + 3600);

                $u = $conn->prepare("UPDATE users SET verification_code = ?, verification_expires = ?, email_verified = 0 WHERE id = ?");
                $u->bind_param("ssi", $code, $expires, $userId);
                $u->execute();

                // Send verification email
                $subject = 'Verify your Danicop Hardware account';
                $body = '<p>Hi ' . htmlspecialchars($name) . ',</p>
                    <p>Thank you for registering at <strong>Danicop Hardware</strong>.</p>
                    <p>Your verification code is:</p>
                    <p style="font-size:24px;font-weight:bold;letter-spacing:4px;">' . $code . '</p>
                    <p>Enter this code on the verification page to activate your account:</p>
                    <p><a href="' . htmlspecialchars('http://localhost/hardware/auth/verify.php') . '">Verify your email</a></p>
                    <p>This code will expire in 1 hour.</p>
                    <p>Regards,<br>Danicop Hardware</p>';
                $sent = send_app_email([$email => $name], $subject, $body);

                if ($sent) {
                    // Store email so we can pre-fill it on the verification screen / modal
                    $_SESSION['pending_verification_email'] = $email;
                    $_SESSION['flash_success'] = 'Registration successful! We sent a verification code to your email. Please verify your account before logging in.';

                    // Show in-page verification modal instead of redirecting
                    $success = 'Registration successful! We sent a verification code to your email. Please check your inbox and enter the code below to verify your account.';
                    $showVerificationModal = true;
                    $verificationEmail = $email;
                } else {
                    // Registration succeeded but email failed – let the user know clearly.
                    $error = 'Your account was created, but we could not send a verification email. Please contact the site administrator to verify your account or check the email settings.';
                }
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        .animate-slide-right {
            animation: slideInRight 0.6s ease-out;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .btn-primary {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .input-focus:focus {
            transform: scale(1.02);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
    <!-- Background Animation -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-400 bg-opacity-20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-400 bg-opacity-20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1.5s;"></div>
    </div>
    
    <div class="min-h-screen flex items-center justify-center px-4 py-12 relative z-10">
        <div class="max-w-md w-full animate-fade-in">
            <!-- Logo Section -->
            <div class="text-center mb-8 animate-slide-right">
                <div class="inline-block p-4 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full mb-4 animate-float">
                    <span class="text-4xl">🔧</span>
                </div>
                <h1 class="text-4xl font-extrabold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                    Danicop Hardware
                </h1>
                <h2 class="text-2xl font-semibold text-gray-700">Create Account</h2>
                <p class="text-gray-500 mt-2">Join us and start shopping!</p>
            </div>
            
            <!-- Register Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 backdrop-blur-sm bg-opacity-95 border border-gray-100">
                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-6 animate-fade-in">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg mb-6 animate-fade-in">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span><?= htmlspecialchars($success) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="register.php" class="space-y-5">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2" for="name">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Full Name
                        </label>
                        <div class="relative">
                            <input type="text" id="name" name="name" required
                                   class="input-focus w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                   placeholder="John Doe"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-bold mb-2" for="email">
                            <i class="fas fa-envelope mr-2 text-blue-600"></i>Email Address
                        </label>
                        <div class="relative">
                            <input type="email" id="email" name="email" required
                                   class="input-focus w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                   placeholder="your@email.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-bold mb-2" for="password">
                            <i class="fas fa-lock mr-2 text-blue-600"></i>Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required minlength="6"
                                   class="input-focus w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                   placeholder="Minimum 6 characters">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-1"><i class="fas fa-info-circle mr-1"></i>Minimum 6 characters</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-bold mb-2" for="confirm_password">
                            <i class="fas fa-lock mr-2 text-blue-600"></i>Confirm Password
                        </label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                                   class="input-focus w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                   placeholder="Re-enter password">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                        <i class="fas fa-user-plus mr-2"></i> Create Account
                    </button>
                </form>

                <!-- Google login removed; using email + verification code instead -->

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-center text-gray-600 mb-4">
                        Already have an account?
                    </p>
                    <a href="login.php" class="block w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold text-center hover:bg-gray-200 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-sign-in-alt mr-2"></i> Sign In Instead
                    </a>
                    <a href="../index.php" class="block text-center text-blue-600 hover:text-blue-700 mt-4 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Home
                    </a>
                </div>
            </div>
            
            <!-- Additional Info -->
            <div class="mt-6 text-center text-gray-500 text-sm">
                <p><i class="fas fa-shield-alt mr-2"></i>Your data is secure and encrypted</p>
            </div>
        </div>
    </div>

    <!-- Email Verification Modal -->
    <div id="verificationModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 px-4 <?php echo $showVerificationModal ? '' : 'hidden'; ?>">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative animate-fade-in">
            <button type="button"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
                    aria-label="Close verification modal"
                    data-close-modal>
                <i class="fas fa-times text-lg"></i>
            </button>

            <div class="text-center mb-4">
                <h2 class="text-2xl font-extrabold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-1">
                    Verify Your Email
                </h2>
                <p class="text-gray-600 text-sm">
                    We sent a 6-digit verification code to
                    <span class="font-semibold">
                        <?= htmlspecialchars($verificationEmail ?: ($_POST['email'] ?? '')) ?>
                    </span>.
                    Enter it below to activate your account.
                </p>
            </div>

            <!-- Verify code form -->
            <form method="POST" action="verify.php" class="space-y-4">
                <input type="hidden" name="email"
                       value="<?= htmlspecialchars($verificationEmail ?: ($_POST['email'] ?? '')) ?>">

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-key mr-2 text-blue-600"></i>Verification Code
                    </label>
                    <input type="text" name="code" required maxlength="6"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 tracking-widest text-center"
                           placeholder="123456">
                </div>

                <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    Verify Email
                </button>
            </form>

            <!-- Resend code form (modal) -->
            <form method="POST" action="verify.php" class="mt-4 space-y-2 text-center">
                <input type="hidden" name="email"
                       value="<?= htmlspecialchars($verificationEmail ?: ($_POST['email'] ?? '')) ?>">
                <input type="hidden" name="resend" value="1">
                <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2 border border-blue-500 text-blue-600 rounded-lg text-sm font-semibold hover:bg-blue-50 transition-colors">
                    <i class="fas fa-redo mr-2"></i> Resend verification code
                </button>
                <p class="text-xs text-gray-500">
                    You can request a new code once every 60 seconds.
                </p>
            </form>

            <button type="button"
                    class="w-full mt-4 bg-gray-100 text-gray-700 py-2.5 rounded-xl font-semibold hover:bg-gray-200 transition-all text-sm"
                    data-close-modal>
                I will verify later
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('verificationModal');
            const closeButtons = document.querySelectorAll('[data-close-modal]');

            closeButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (modal) {
                        modal.classList.add('hidden');
                    }
                });
            });
        });
    </script>
</body>
</html>
