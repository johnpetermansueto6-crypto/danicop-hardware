<?php
require_once '../includes/config.php';

$error = '';
$login_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role, email_verified FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Only require email verification for customers, not for admin/staff
                if ($user['role'] === 'customer' && isset($user['email_verified']) && !$user['email_verified']) {
                    $error = 'Please verify your email first using the code we sent to you.';
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Set success flash message
                    $_SESSION['login_success'] = true;
                    $_SESSION['login_message'] = 'Login successful! Redirecting...';
                    
                    // Check for redirect parameter
                    $redirect = $_GET['redirect'] ?? '';
                    
                    // Redirect based on role or redirect parameter
                    if (!empty($redirect) && $redirect === 'checkout') {
                        header('Location: ../customer/checkout.php?login=success');
                        exit();
                    } elseif ($user['role'] === 'superadmin') {
                        // Redirect to admin dashboard with page parameter
                        header('Location: ../admin/index.php?page=dashboard&login=success');
                        exit();
                    } elseif ($user['role'] === 'staff') {
                        // Redirect to staff dashboard
                        header('Location: ../staff/dashboard.php?login=success');
                        exit();
                    } elseif ($user['role'] === 'driver') {
                        // Redirect to driver dashboard
                        header('Location: ../driver/dashboard.php?login=success');
                        exit();
                    } else {
                        // Customers go straight to the customer shop dashboard
                        header('Location: ../customer/shop.php?login=success');
                        exit();
                    }
                }
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

// Check for login success message from redirect
if (isset($_GET['login']) && $_GET['login'] === 'success' && isset($_SESSION['login_success'])) {
    $login_success = true;
    unset($_SESSION['login_success']);
    unset($_SESSION['login_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        .animate-slide-left {
            animation: slideInLeft 0.6s ease-out;
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

        /* Toast Notification Styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInRight 0.4s ease-out, fadeOut 0.3s ease-in 2.7s forwards;
            transform: translateX(0);
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .toast-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .toast-message {
            font-size: 14px;
            opacity: 0.95;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateX(400px);
            }
        }

        .toast-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background 0.2s;
        }

        .toast-close:hover {
            background: rgba(255, 255, 255, 0.3);
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
            <div class="text-center mb-8 animate-slide-left">
                <div class="inline-block p-4 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full mb-4 animate-float">
                    <span class="text-4xl">🔧</span>
                </div>
                <h1 class="text-4xl font-extrabold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                    Danicop Hardware
                </h1>
                <h2 class="text-2xl font-semibold text-gray-700">Welcome Back!</h2>
                <p class="text-gray-500 mt-2">Sign in to your account</p>
            </div>
            
            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 backdrop-blur-sm bg-opacity-95 border border-gray-100">
                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-6 animate-fade-in">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="login.php" class="space-y-6">
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
                            <input type="password" id="password" name="password" required
                                   class="input-focus w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300"
                                   placeholder="Enter your password">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                    </button>
                </form>

                <!-- Google login removed; using email + verification code instead -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-center text-gray-600 mb-4">
                        Don't have an account?
                    </p>
                    <a href="register.php" class="block w-full bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold text-center hover:bg-gray-200 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-user-plus mr-2"></i> Create New Account
                    </a>
                    <a href="../index.php" class="block text-center text-blue-600 hover:text-blue-700 mt-4 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Home
                    </a>
                </div>
            </div>
            
            <!-- Additional Info -->
            <div class="mt-6 text-center text-gray-500 text-sm">
                <p><i class="fas fa-shield-alt mr-2"></i>Secure login with encrypted passwords</p>
            </div>
        </div>
    </div>

    <script>
        // Toast Notification System
        function showToast(type, title, message) {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            const icon = type === 'success' 
                ? '<i class="fas fa-check-circle toast-icon"></i>'
                : '<i class="fas fa-exclamation-circle toast-icon"></i>';
            
            toast.innerHTML = `
                ${icon}
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.animation = 'fadeOut 0.3s ease-in forwards';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 3000);
        }

        // Show login error if exists
        <?php if ($error): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('error', 'Login Failed', '<?= addslashes($error) ?>');
        });
        <?php endif; ?>

        // Show login success if exists
        <?php if ($login_success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showToast('success', 'Login Successful!', 'Welcome back! Redirecting to your dashboard...');
        });
        <?php endif; ?>
    </script>
</body>
</html>
