<?php
require_once 'includes/config.php';
require_once 'includes/mailer.php';

// Handle login form submission FIRST (before any output)
$login_error = '';
$login_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $login_error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role, email_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Only require email verification for customers, not for admin/staff
                if ($user['role'] === 'customer' && isset($user['email_verified']) && !$user['email_verified']) {
                    $login_error = 'Please verify your email first using the code we sent to you.';
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Set success flash message
                    $_SESSION['login_success'] = true;
                    $_SESSION['login_message'] = 'Login successful! Redirecting...';
                    
                    // Redirect based on role - ensure proper paths
                    if ($user['role'] === 'superadmin') {
                        // Redirect to admin dashboard
                        header('Location: admin/index.php?page=dashboard&login=success');
                        exit();
                    } elseif ($user['role'] === 'staff') {
                        // Redirect to staff dashboard
                        header('Location: staff/dashboard.php?login=success');
                        exit();
                    } elseif ($user['role'] === 'driver') {
                        // Redirect to driver dashboard
                        header('Location: driver/dashboard.php?login=success');
                        exit();
                    } else {
                        // Customers go to their shop dashboard
                        header('Location: customer/shop.php?login=success');
                        exit();
                    }
                }
            } else {
                $login_error = 'Invalid email or password';
            }
        } else {
            $login_error = 'Invalid email or password';
        }
    }
}

// Check for login success message from redirect
if (isset($_GET['login']) && $_GET['login'] === 'success' && isset($_SESSION['login_success'])) {
    $login_success = true;
    unset($_SESSION['login_success']);
    unset($_SESSION['login_message']);
}

// Get active store locations (after login check to avoid issues)
$locations = $conn->query("SELECT * FROM store_locations WHERE is_active = 1 ORDER BY name ASC");

// Handle register form submission
$register_error = '';
$register_success = '';
$register_showVerificationModal = false;
$registerVerificationEmail = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $register_error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $register_error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $register_error = 'Password must be at least 6 characters';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $register_error = 'Email already registered';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer';
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
            
            if ($stmt->execute()) {
                $userId = $stmt->insert_id;

                // Generate verification code and expiry (1 hour)
                $code = (string)random_int(100000, 999999);
                $expires = date('Y-m-d H:i:s', time() + 3600);

                $u = $conn->prepare("UPDATE users SET verification_code = ?, verification_expires = ?, email_verified = 0 WHERE id = ?");
                $u->bind_param("ssi", $code, $expires, $userId);
                $u->execute();

                // Send verification email (if SMTP is configured)
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
                    // Remember email for verification modal / page
                    $_SESSION['pending_verification_email'] = $email;

                    $register_success = 'Registration successful! We sent a verification code to your email. Please check your inbox and enter the code to verify your account.';
                    $register_showVerificationModal = true;
                    $registerVerificationEmail = $email;
                } else {
                    $register_error = 'Your account was created, but we could not send a verification email. Please contact the site administrator to verify your account or check the email settings.';
                }
            } else {
                $register_error = 'Registration failed. Please try again.';
            }
        }
    }
}

// Get all products
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get all categories
$categoriesQuery = "SELECT DISTINCT category FROM products ORDER BY category";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row['category'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danicop Hardware Online - Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'jakarta': ['Plus Jakarta Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <!-- Leaflet (OpenStreetMap) for store location maps -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
    <style>
        [x-cloak] { display: none !important; }
        
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #10b981, #059669);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #059669, #047857);
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Card Hover */
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.25);
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-jakarta" x-data="{ mobileMenuOpen: false, cartOpen: false, loginModalOpen: false, registerModalOpen: false }">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-xl border-b-2 border-emerald-100 shadow-lg shadow-emerald-100/20">
        <div class="container mx-auto px-4 lg:px-8 max-w-7xl">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="index.php" class="flex items-center space-x-3 group">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-green-600 rounded-2xl blur opacity-60 group-hover:opacity-100 transition-all duration-300"></div>
                        <div class="relative bg-white p-2 rounded-2xl shadow-lg">
                            <img src="assets/images/logo.svg" alt="Danicop Hardware Logo" class="w-10 h-10 object-contain">
                        </div>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-extrabold tracking-tight text-gray-900 group-hover:text-emerald-600 transition-colors">Danicop Hardware</span>
                        <span class="text-xs text-emerald-600 font-semibold tracking-wide">Premium Tools & Materials</span>
                    </div>
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="index.php" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-300 relative group">
                        <span>Home</span>
                        <span class="absolute bottom-1 left-4 right-4 h-0.5 bg-emerald-500 rounded-full transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                    </a>
                    <a href="index.php#locations" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-300 relative group">
                        <span>Locations</span>
                        <span class="absolute bottom-1 left-4 right-4 h-0.5 bg-emerald-500 rounded-full transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                    </a>
                    <?php if (isLoggedIn()): ?>
                        <a href="customer/orders.php" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-300 relative group">
                            <span>My Orders</span>
                            <span class="absolute bottom-1 left-4 right-4 h-0.5 bg-emerald-500 rounded-full transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                        </a>
                        <a href="customer/profile.php" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-300 relative group">
                            <span>Profile</span>
                            <span class="absolute bottom-1 left-4 right-4 h-0.5 bg-emerald-500 rounded-full transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                        </a>
                        <?php if (getUserRole() === 'superadmin'): ?>
                            <a href="admin/index.php" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-300 relative group">
                                <span>Admin</span>
                                <span class="absolute bottom-1 left-4 right-4 h-0.5 bg-emerald-500 rounded-full transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                            </a>
                        <?php elseif (getUserRole() === 'staff'): ?>
                        <a href="staff/dashboard.php" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-300 relative group">
                            <span>Staff</span>
                            <span class="absolute bottom-1 left-4 right-4 h-0.5 bg-emerald-500 rounded-full transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                        </a>
                        <?php endif; ?>
                        <a href="auth/logout.php" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:text-red-600 hover:bg-red-50 transition-all duration-300">
                            <i class="fas fa-sign-out-alt mr-1.5"></i>Logout
                        </a>
                    <?php else: ?>
                        <button @click="loginModalOpen = true" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:text-emerald-600 hover:bg-emerald-50 transition-all duration-300">
                            <i class="fas fa-sign-in-alt mr-1.5"></i>Login
                        </button>
                        <button @click="registerModalOpen = true" class="ml-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-bold shadow-lg shadow-emerald-200 hover:shadow-xl hover:shadow-emerald-300 hover:from-emerald-600 hover:to-green-700 transform hover:scale-105 transition-all duration-300">
                            <i class="fas fa-user-plus mr-1.5"></i>Get Started
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-3 rounded-xl hover:bg-emerald-50 transition-colors">
                    <i class="fas fa-bars text-2xl text-gray-700"></i>
                </button>
            </div>
            
            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-cloak 
                 class="lg:hidden pb-6 pt-4 space-y-2 border-t border-emerald-100 mt-2">
                <a href="index.php" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-all duration-300 font-medium">
                    <i class="fas fa-home mr-3 w-5 text-emerald-500"></i> Home
                </a>
                <a href="index.php#locations" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-all duration-300 font-medium">
                    <i class="fas fa-map-marker-alt mr-3 w-5 text-emerald-500"></i> Locations
                </a>
                <?php if (isLoggedIn()): ?>
                    <a href="customer/orders.php" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-all duration-300 font-medium">
                        <i class="fas fa-list-alt mr-3 w-5 text-emerald-500"></i> My Orders
                    </a>
                    <a href="customer/profile.php" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-all duration-300 font-medium">
                        <i class="fas fa-user mr-3 w-5 text-emerald-500"></i> Profile
                    </a>
                    <?php if (getUserRole() === 'superadmin'): ?>
                        <a href="admin/index.php" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-all duration-300 font-medium">
                            <i class="fas fa-tachometer-alt mr-3 w-5 text-emerald-500"></i> Admin Dashboard
                        </a>
                    <?php elseif (getUserRole() === 'staff'): ?>
                    <a href="staff/dashboard.php" class="flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-all duration-300 font-medium">
                        <i class="fas fa-tachometer-alt mr-3 w-5 text-emerald-500"></i> Staff Dashboard
                    </a>
                    <?php endif; ?>
                    <a href="auth/logout.php" class="flex items-center px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-all duration-300 font-medium">
                        <i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout
                    </a>
                <?php else: ?>
                    <button @click="loginModalOpen = true; mobileMenuOpen = false" class="w-full flex items-center px-4 py-3 rounded-xl text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-all duration-300 font-medium">
                        <i class="fas fa-sign-in-alt mr-3 w-5 text-emerald-500"></i> Login
                    </button>
                    <button @click="registerModalOpen = true; mobileMenuOpen = false" class="w-full flex items-center justify-center px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold shadow-lg shadow-emerald-200 hover:shadow-xl transition-all duration-300">
                        <i class="fas fa-user-plus mr-3 w-5"></i> Get Started
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Shopping Cart UI removed: cart is currently disabled on the home page -->

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-green-50 min-h-[90vh] flex items-center">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-emerald-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-green-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-pulse" style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-teal-100 rounded-full mix-blend-multiply filter blur-3xl opacity-40"></div>
        </div>
        
        <!-- Decorative Grid Pattern -->
        <div class="absolute inset-0" style="background-image: radial-gradient(#10b981 1px, transparent 1px); background-size: 40px 40px; opacity: 0.1;"></div>
        
        <div class="container mx-auto px-4 lg:px-8 max-w-7xl relative z-10">
            <div class="py-20 md:py-24 lg:py-28">
                <div class="max-w-5xl mx-auto text-center">
                    <!-- Badge -->
                    <div class="inline-flex items-center px-5 py-2.5 rounded-full bg-white border-2 border-emerald-200 mb-8 shadow-lg">
                        <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full mr-3 animate-pulse"></span>
                        <span class="text-sm font-bold text-emerald-700 tracking-wide">TRUSTED HARDWARE SUPPLIER SINCE 2020</span>
                    </div>
                    
                    <!-- Main Heading -->
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold mb-8 tracking-tight leading-tight">
                        <span class="block text-gray-900">Premium Quality</span>
                        <span class="block mt-2 bg-gradient-to-r from-emerald-600 via-green-500 to-teal-500 bg-clip-text text-transparent">
                            Hardware Solutions
                        </span>
                    </h1>
                    
                    <!-- Subheading -->
                    <p class="text-xl md:text-2xl text-gray-600 mb-12 max-w-3xl mx-auto leading-relaxed font-medium">
                        Your trusted partner for professional tools, construction materials, and hardware essentials. 
                        <span class="text-emerald-600 font-bold">Quality you can build on.</span>
                    </p>
                    
                    <?php if (!isLoggedIn()): ?>
                        <!-- CTA Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-5 mb-16">
                            <button @click="registerModalOpen = true" class="group px-10 py-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-emerald-200 transform hover:scale-105 transition-all duration-300 flex items-center">
                                <span class="text-lg">Get Started Free</span>
                                <i class="fas fa-arrow-right ml-3 group-hover:translate-x-1 transition-transform"></i>
                            </button>
                            <a href="index.php#locations" class="px-10 py-4 bg-white text-gray-700 font-bold rounded-2xl shadow-lg hover:shadow-xl border-2 border-emerald-200 hover:border-emerald-400 transform hover:scale-105 transition-all duration-300 flex items-center group">
                                <i class="fas fa-map-marker-alt mr-3 text-emerald-500 group-hover:scale-110 transition-transform"></i>
                                <span class="text-lg">Find Our Stores</span>
                            </a>
                        </div>
                        
                        <!-- Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-5 max-w-4xl mx-auto">
                            <div class="bg-white rounded-2xl p-6 border-2 border-emerald-100 shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <i class="fas fa-box text-white text-xl"></i>
                                </div>
                                <div class="text-3xl font-extrabold text-gray-800 mb-1">500+</div>
                                <div class="text-sm text-gray-500 font-semibold">Products</div>
                            </div>
                            <div class="bg-white rounded-2xl p-6 border-2 border-emerald-100 shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                                <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <i class="fas fa-headset text-white text-xl"></i>
                                </div>
                                <div class="text-3xl font-extrabold text-gray-800 mb-1">24/7</div>
                                <div class="text-sm text-gray-500 font-semibold">Support</div>
                            </div>
                            <div class="bg-white rounded-2xl p-6 border-2 border-emerald-100 shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                                <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <i class="fas fa-shield-alt text-white text-xl"></i>
                                </div>
                                <div class="text-3xl font-extrabold text-emerald-600 mb-1">100%</div>
                                <div class="text-sm text-gray-500 font-semibold">Guaranteed</div>
                            </div>
                            <div class="bg-white rounded-2xl p-6 border-2 border-emerald-100 shadow-xl hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                    <i class="fas fa-truck text-white text-xl"></i>
                                </div>
                                <div class="text-3xl font-extrabold text-gray-800 mb-1">Fast</div>
                                <div class="text-sm text-gray-500 font-semibold">Delivery</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Welcome Back Section -->
                        <div>
                            <div class="inline-flex items-center px-8 py-4 bg-white rounded-2xl shadow-xl border-2 border-emerald-100 mb-8">
                                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                                    <i class="fas fa-user text-white text-xl"></i>
                                </div>
                                <span class="text-xl font-bold text-gray-800">Welcome back, <span class="text-emerald-600"><?= htmlspecialchars($_SESSION['user_name']) ?></span>!</span>
                            </div>
                            <div>
                                <a href="customer/shop.php" class="inline-flex items-center px-10 py-5 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold text-lg rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-emerald-200 transform hover:scale-105 transition-all duration-300">
                                    <i class="fas fa-shopping-bag mr-4 text-xl"></i>
                                    <span>Start Shopping Now</span>
                                    <i class="fas fa-arrow-right ml-4"></i>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Decorative Bottom Wave -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg class="w-full h-24" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" fill="#10b981" opacity="0.15"></path>
                <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" fill="#10b981" opacity="0.25"></path>
                <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="#10b981" opacity="0.4"></path>
            </svg>
        </div>
    </section>

    <!-- About / Highlights Section -->
    <section class="py-20 relative overflow-hidden bg-gradient-to-b from-green-50 via-white to-emerald-50">
        <!-- Decorative Background -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-100 rounded-full filter blur-3xl opacity-60 -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-green-100 rounded-full filter blur-3xl opacity-50 translate-y-1/3 -translate-x-1/3"></div>
        
        <div class="container mx-auto px-4 lg:px-8 max-w-7xl relative z-10">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <div class="inline-flex items-center px-5 py-2.5 rounded-full bg-white border-2 border-emerald-200 mb-6 shadow-lg">
                    <i class="fas fa-star text-emerald-500 mr-2"></i>
                    <span class="text-sm font-bold text-emerald-700">WHY CHOOSE US</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight text-gray-900 mb-4">
                    Everything You Need for
                    <span class="bg-gradient-to-r from-emerald-600 to-green-500 bg-clip-text text-transparent"> Your Next Project</span>
                </h2>
                <p class="text-gray-600 text-lg md:text-xl leading-relaxed">
                    Danicop Hardware has been serving builders, contractors, and homeowners with quality materials,
                    honest pricing, and reliable service.
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <div class="bg-white rounded-3xl shadow-xl border-2 border-emerald-100 p-8 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-toolbox text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Wide Range of Hardware</h3>
                    <p class="text-gray-600 leading-relaxed">
                        From basic hand tools to construction materials, we carefully select products that stand up to
                        real-world use.
                    </p>
                </div>

                <div class="bg-white rounded-3xl shadow-xl border-2 border-emerald-100 p-8 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-truck text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Fast, Local Delivery</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Place your order online and pick up in-store or have it delivered straight to your site in our
                        service areas.
                    </p>
                </div>

                <div class="bg-white rounded-3xl shadow-xl border-2 border-emerald-100 p-8 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 group">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center mb-6 shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-hard-hat text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Expert Assistance</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Our staff can help you choose the right materials, estimate quantities, and answer questions
                        about your project.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20 relative overflow-hidden bg-gradient-to-br from-white via-emerald-50 to-green-100">
        <!-- Decorative Elements -->
        <div class="absolute inset-0" style="background-image: radial-gradient(#10b981 1px, transparent 1px); background-size: 40px 40px; opacity: 0.08;"></div>
        <div class="absolute top-1/2 left-0 w-72 h-72 bg-emerald-100 rounded-full filter blur-3xl opacity-60 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute top-1/4 right-0 w-64 h-64 bg-green-100 rounded-full filter blur-3xl opacity-50 translate-x-1/2"></div>
        
        <div class="container mx-auto px-4 lg:px-8 max-w-7xl relative z-10">
            <div class="max-w-3xl mx-auto text-center mb-16">
                <div class="inline-flex items-center px-5 py-2.5 rounded-full bg-white border-2 border-emerald-200 mb-6 shadow-lg">
                    <i class="fas fa-cogs text-emerald-500 mr-2"></i>
                    <span class="text-sm font-bold text-emerald-700">HOW IT WORKS</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight text-gray-900 mb-4">
                    Easy Online <span class="bg-gradient-to-r from-emerald-600 to-green-500 bg-clip-text text-transparent">Ordering</span>
                </h2>
                <p class="text-gray-600 text-lg md:text-xl leading-relaxed">
                    Create a free account, browse our catalog, and place orders in just a few simple steps.
                </p>
            </div>

            <div class="max-w-5xl mx-auto relative">
                <!-- Connection Line (Desktop) -->
                <div class="hidden md:block absolute top-10 left-[20%] right-[20%] h-1 bg-gradient-to-r from-emerald-300 via-green-400 to-emerald-300 rounded-full z-0"></div>
                
                <div class="grid gap-10 md:grid-cols-3 relative z-10">
                    <div class="text-center">
                        <div class="relative inline-block mb-6">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white flex items-center justify-center font-extrabold text-2xl shadow-xl mx-auto">
                                1
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-9 h-9 rounded-xl bg-white border-2 border-emerald-200 flex items-center justify-center shadow-lg">
                                <i class="fas fa-user-plus text-emerald-600"></i>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Create Your Account</h3>
                        <p class="text-gray-600 leading-relaxed max-w-xs mx-auto">
                            Register using your email so we can keep your orders and delivery details in one place.
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="relative inline-block mb-6">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center font-extrabold text-2xl shadow-xl mx-auto">
                                2
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-9 h-9 rounded-xl bg-white border-2 border-teal-200 flex items-center justify-center shadow-lg">
                                <i class="fas fa-shopping-cart text-teal-600"></i>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Browse & Add to Cart</h3>
                        <p class="text-gray-600 leading-relaxed max-w-xs mx-auto">
                            Explore our hardware catalog in the shop area, compare options, and add what you need.
                        </p>
                    </div>

                    <div class="text-center">
                        <div class="relative inline-block mb-6">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center font-extrabold text-2xl shadow-xl mx-auto">
                                3
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-9 h-9 rounded-xl bg-white border-2 border-green-200 flex items-center justify-center shadow-lg">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Checkout & Track</h3>
                        <p class="text-gray-600 leading-relaxed max-w-xs mx-auto">
                            Confirm your details, place the order, and track its status from your customer dashboard.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <div x-show="loginModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @click.self="loginModalOpen = false"
         style="display: none;">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-white rounded-3xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto border border-gray-100">
            <div class="p-8">
                <!-- Header -->
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-200">
                            <i class="fas fa-sign-in-alt text-white text-xl"></i>
                        </div>
                        <h2 class="text-3xl font-extrabold text-gray-900">
                            Welcome Back!
                        </h2>
                        <p class="text-gray-500 mt-1 font-medium">Sign in to your account</p>
                    </div>
                    <button @click="loginModalOpen = false" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>
                
                <?php if ($login_error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                            <span class="font-medium"><?= htmlspecialchars($login_error) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="index.php" class="space-y-5">
                    <input type="hidden" name="login_submit" value="1">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">
                            Email Address
                        </label>
                        <div class="relative">
                            <input type="email" name="email" required
                                   class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300 font-medium"
                                   placeholder="your@email.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-emerald-500"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password" required
                                   class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300 font-medium"
                                   placeholder="Enter your password">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-emerald-500"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 to-green-600 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-emerald-200 hover:shadow-xl hover:shadow-emerald-300 transform hover:scale-[1.02] transition-all duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                    </button>
                </form>
                
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-center text-gray-500 mb-4 font-medium">
                        Don't have an account?
                    </p>
                    <button @click="loginModalOpen = false; registerModalOpen = true" class="w-full bg-emerald-50 text-emerald-700 py-3.5 rounded-xl font-bold hover:bg-emerald-100 transition-all duration-300 border border-emerald-200">
                        <i class="fas fa-user-plus mr-2"></i> Create New Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div x-show="registerModalOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @click.self="registerModalOpen = false"
         style="display: none;">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-white rounded-3xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto border border-gray-100">
            <div class="p-8">
                <!-- Header -->
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-200">
                            <i class="fas fa-user-plus text-white text-xl"></i>
                        </div>
                        <h2 class="text-3xl font-extrabold text-gray-900">
                            Create Account
                        </h2>
                        <p class="text-gray-500 mt-1 font-medium">Join us and start shopping!</p>
                    </div>
                    <button @click="registerModalOpen = false" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>
                
                <?php if ($register_error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                            <span class="font-medium"><?= htmlspecialchars($register_error) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($register_success): ?>
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
                            <span class="font-medium"><?= htmlspecialchars($register_success) ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="index.php" class="space-y-4">
                    <input type="hidden" name="register_submit" value="1">
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">
                            Full Name
                        </label>
                        <div class="relative">
                            <input type="text" name="name" required
                                   class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300 font-medium"
                                   placeholder="John Doe"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-emerald-500"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">
                            Email Address
                        </label>
                        <div class="relative">
                            <input type="email" name="email" required
                                   class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300 font-medium"
                                   placeholder="your@email.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-emerald-500"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" name="password" required minlength="6"
                                   class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300 font-medium"
                                   placeholder="Minimum 6 characters">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-emerald-500"></i>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5 font-medium"><i class="fas fa-info-circle mr-1"></i>Minimum 6 characters</p>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-bold mb-2 text-sm">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <input type="password" name="confirm_password" required minlength="6"
                                   class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-300 font-medium"
                                   placeholder="Re-enter password">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-emerald-500"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 to-green-600 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-emerald-200 hover:shadow-xl hover:shadow-emerald-300 transform hover:scale-[1.02] transition-all duration-300 mt-2">
                        <i class="fas fa-user-plus mr-2"></i> Create Account
                    </button>
                </form>

                <!-- Removed Google login; using email verification instead -->

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-center text-gray-500 mb-4 font-medium">
                        Already have an account?
                    </p>
                    <button @click="registerModalOpen = false; loginModalOpen = true" class="w-full bg-emerald-50 text-emerald-700 py-3.5 rounded-xl font-bold hover:bg-emerald-100 transition-all duration-300 border border-emerald-200">
                        <i class="fas fa-sign-in-alt mr-2"></i> Sign In Instead
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Verification Modal for home-page registration -->
    <div id="homeVerificationModal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4 <?php echo $register_showVerificationModal ? '' : 'hidden'; ?>">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 relative border border-gray-100">
            <button type="button"
                    class="absolute top-4 right-4 w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
                    aria-label="Close verification modal"
                    onclick="document.getElementById('homeVerificationModal').classList.add('hidden')">
                <i class="fas fa-times text-gray-500"></i>
            </button>

            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-200">
                    <i class="fas fa-envelope-open text-white text-2xl"></i>
                </div>
                <h2 class="text-2xl font-extrabold text-gray-900 mb-2">
                    Verify Your Email
                </h2>
                <p class="text-gray-500 text-sm font-medium">
                    We sent a 6-digit verification code to
                    <span class="text-emerald-600 font-bold">
                        <?= htmlspecialchars($registerVerificationEmail ?: ($_POST['email'] ?? '')) ?>
                    </span>.
                    Enter it below to activate your account.
                </p>
            </div>

            <!-- Verify code form -->
            <form method="POST" action="auth/verify.php" class="space-y-4">
                <input type="hidden" name="email"
                       value="<?= htmlspecialchars($registerVerificationEmail ?: ($_POST['email'] ?? '')) ?>">

                <div>
                    <label class="block text-gray-700 font-bold mb-2 text-sm">
                        Verification Code
                    </label>
                    <input type="text" name="code" required maxlength="6"
                           class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 tracking-[0.5em] text-center text-2xl font-bold"
                           placeholder="000000">
                </div>

                <button type="submit"
                        class="w-full bg-gradient-to-r from-emerald-500 to-green-600 text-white py-4 rounded-xl font-bold text-lg shadow-lg shadow-emerald-200 hover:shadow-xl hover:shadow-emerald-300 transform hover:scale-[1.02] transition-all duration-300">
                    Verify Email
                </button>
            </form>

            <!-- Resend code form (home modal) -->
            <form method="POST" action="auth/verify.php" class="mt-6 space-y-2 text-center">
                <input type="hidden" name="email"
                       value="<?= htmlspecialchars($registerVerificationEmail ?: ($_POST['email'] ?? '')) ?>">
                <input type="hidden" name="resend" value="1">
                <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-2.5 border-2 border-emerald-200 text-emerald-600 rounded-xl text-sm font-bold hover:bg-emerald-50 transition-colors">
                    <i class="fas fa-redo mr-2"></i> Resend verification code
                </button>
                <p class="text-xs text-gray-400 font-medium">
                    You can request a new code once every 60 seconds.
                </p>
            </form>

            <button type="button"
                    class="w-full mt-4 bg-gray-100 text-gray-600 py-3 rounded-xl font-bold hover:bg-gray-200 transition-all text-sm"
                    onclick="document.getElementById('homeVerificationModal').classList.add('hidden')">
                I will verify later
            </button>
        </div>
    </div>

    <!-- Locations Section -->
    <section id="locations" class="py-20 relative overflow-hidden bg-gradient-to-b from-green-100 via-emerald-50 to-white">
        <!-- Decorative Elements -->
        <div class="absolute top-0 left-0 w-96 h-96 bg-emerald-200 rounded-full filter blur-3xl opacity-40 -translate-x-1/2 -translate-y-1/3"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-green-200 rounded-full filter blur-3xl opacity-30 translate-x-1/3 translate-y-1/3"></div>
        
        <div class="container mx-auto px-4 lg:px-8 max-w-7xl relative z-10">
            <div class="text-center mb-16">
                <div class="inline-flex items-center px-5 py-2.5 rounded-full bg-white border-2 border-emerald-200 mb-6 shadow-lg">
                    <i class="fas fa-map-marker-alt text-emerald-500 mr-2"></i>
                    <span class="text-sm font-bold text-emerald-700">VISIT US</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
                    Our Store <span class="bg-gradient-to-r from-emerald-600 to-green-500 bg-clip-text text-transparent">Locations</span>
                </h2>
                <p class="text-gray-600 text-lg md:text-xl font-medium">Visit us at any of our convenient locations</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                $locations->data_seek(0); // Reset pointer
                $locationIndex = 0;
                while ($location = $locations->fetch_assoc()): 
                    $locationIndex++;
                ?>
                    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border-2 border-emerald-100 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                        <div class="h-56 relative bg-gradient-to-br from-emerald-100 via-green-100 to-teal-100">
                            <?php if ($location['latitude'] && $location['longitude']): ?>
                                <div id="map-<?= $location['id'] ?>" class="w-full h-full"></div>
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full">
                                    <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center shadow-xl">
                                        <i class="fas fa-map-marker-alt text-3xl text-white"></i>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-extrabold text-gray-900 mb-4"><?= htmlspecialchars($location['name']) ?></h3>
                            <div class="space-y-3 text-gray-600">
                                <p class="flex items-start">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-map-marker-alt text-emerald-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium"><?= nl2br(htmlspecialchars($location['address'])) ?></span>
                                </p>
                                <?php if ($location['phone']): ?>
                                    <p class="flex items-center">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center mr-3 flex-shrink-0">
                                            <i class="fas fa-phone text-emerald-600 text-sm"></i>
                                        </div>
                                        <a href="tel:<?= htmlspecialchars($location['phone']) ?>" class="text-sm font-medium hover:text-emerald-600 transition-colors">
                                            <?= htmlspecialchars($location['phone']) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                <?php if ($location['email']): ?>
                                    <p class="flex items-center">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center mr-3 flex-shrink-0">
                                            <i class="fas fa-envelope text-emerald-600 text-sm"></i>
                                        </div>
                                        <a href="mailto:<?= htmlspecialchars($location['email']) ?>" class="text-sm font-medium hover:text-emerald-600 transition-colors">
                                            <?= htmlspecialchars($location['email']) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                <?php if ($location['hours']): ?>
                                    <p class="flex items-start">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center mr-3 flex-shrink-0">
                                            <i class="fas fa-clock text-emerald-600 text-sm"></i>
                                        </div>
                                        <span class="whitespace-pre-line text-sm font-medium"><?= htmlspecialchars($location['hours']) ?></span>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php if ($location['latitude'] && $location['longitude']): ?>
                                <a href="https://www.openstreetmap.org/directions?to=<?= urlencode($location['latitude'] . ',' . $location['longitude']) ?>" 
                                   target="_blank" 
                                   class="mt-5 flex items-center justify-center w-full bg-gradient-to-r from-emerald-500 to-green-600 text-white px-4 py-3.5 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02]">
                                    <i class="fas fa-directions mr-2"></i> Get Directions
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <?php if ($locations->num_rows === 0): ?>
                    <div class="col-span-full text-center py-16">
                        <div class="w-24 h-24 bg-gradient-to-br from-emerald-100 to-green-200 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl">
                            <i class="fas fa-map-marker-alt text-4xl text-emerald-500"></i>
                        </div>
                        <p class="text-gray-500 text-lg font-medium">No store locations available yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Leaflet JS for store location maps -->
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php 
            $locations->data_seek(0);
            while ($location = $locations->fetch_assoc()): 
                if ($location['latitude'] && $location['longitude']):
            ?>
            (function() {
                const mapId = 'map-<?= $location['id'] ?>';
                const el = document.getElementById(mapId);
                if (!el) return;

                const lat = <?= $location['latitude'] ?>;
                const lng = <?= $location['longitude'] ?>;

                const map = L.map(mapId, {
                    zoomControl: true,
                    attributionControl: false
                }).setView([lat, lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                L.marker([lat, lng]).addTo(map).bindPopup('<?= addslashes($location['name']) ?>');
            })();
            <?php 
                endif;
            endwhile; 
            ?>
        });
    </script>

    <!-- Footer -->
    <footer class="relative bg-gradient-to-br from-gray-900 via-emerald-950 to-gray-900 text-gray-100">
        <!-- Decorative Top Wave -->
        <div class="absolute top-0 left-0 right-0 transform -translate-y-1">
            <svg class="w-full h-20" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" fill="rgba(16, 185, 129, 0.1)"></path>
                <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" fill="rgba(16, 185, 129, 0.05)"></path>
            </svg>
        </div>
        
        <div class="container mx-auto px-4 lg:px-8 max-w-7xl relative z-10 pt-24 pb-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <!-- Brand Column -->
                <div class="lg:col-span-1">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="bg-gradient-to-br from-emerald-500 to-green-600 p-3 rounded-2xl shadow-lg shadow-emerald-900/30">
                            <i class="fas fa-tools text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-extrabold text-white">Danicop Hardware</h3>
                            <p class="text-xs text-emerald-400 font-semibold tracking-wide">Premium Tools & Materials</p>
                        </div>
                    </div>
                    <p class="text-gray-400 leading-relaxed mb-6">
                        Your trusted partner for all hardware needs. We deliver quality products with excellent service to builders, contractors, and homeowners.
                    </p>
                    <!-- Social Links -->
                    <div class="flex items-center space-x-3">
                        <a href="#" class="w-11 h-11 bg-gray-800/80 hover:bg-emerald-600 rounded-xl flex items-center justify-center transition-all duration-300 group">
                            <i class="fab fa-facebook-f text-gray-400 group-hover:text-white transition-colors"></i>
                        </a>
                        <a href="#" class="w-11 h-11 bg-gray-800/80 hover:bg-emerald-600 rounded-xl flex items-center justify-center transition-all duration-300 group">
                            <i class="fab fa-twitter text-gray-400 group-hover:text-white transition-colors"></i>
                        </a>
                        <a href="#" class="w-11 h-11 bg-gray-800/80 hover:bg-emerald-600 rounded-xl flex items-center justify-center transition-all duration-300 group">
                            <i class="fab fa-instagram text-gray-400 group-hover:text-white transition-colors"></i>
                        </a>
                        <a href="#" class="w-11 h-11 bg-gray-800/80 hover:bg-emerald-600 rounded-xl flex items-center justify-center transition-all duration-300 group">
                            <i class="fab fa-linkedin-in text-gray-400 group-hover:text-white transition-colors"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-6 flex items-center">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-emerald-400 to-green-600 rounded-full mr-3"></span>
                        Quick Links
                    </h4>
                    <ul class="space-y-3">
                        <li>
                            <a href="index.php" class="text-gray-400 hover:text-emerald-400 transition-colors flex items-center group font-medium">
                                <i class="fas fa-chevron-right text-xs mr-2 text-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                <span>Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="index.php#locations" class="text-gray-400 hover:text-emerald-400 transition-colors flex items-center group font-medium">
                                <i class="fas fa-chevron-right text-xs mr-2 text-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                <span>Store Locations</span>
                            </a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                            <li>
                                <a href="customer/orders.php" class="text-gray-400 hover:text-emerald-400 transition-colors flex items-center group font-medium">
                                    <i class="fas fa-chevron-right text-xs mr-2 text-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                    <span>My Orders</span>
                                </a>
                            </li>
                            <li>
                                <a href="customer/profile.php" class="text-gray-400 hover:text-emerald-400 transition-colors flex items-center group font-medium">
                                    <i class="fas fa-chevron-right text-xs mr-2 text-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                    <span>My Profile</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li>
                                <a href="auth/login.php" class="text-gray-400 hover:text-emerald-400 transition-colors flex items-center group font-medium">
                                    <i class="fas fa-chevron-right text-xs mr-2 text-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                    <span>Login</span>
                                </a>
                            </li>
                            <li>
                                <a href="auth/register.php" class="text-gray-400 hover:text-emerald-400 transition-colors flex items-center group font-medium">
                                    <i class="fas fa-chevron-right text-xs mr-2 text-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                    <span>Register</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-6 flex items-center">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-emerald-400 to-green-600 rounded-full mr-3"></span>
                        Contact Us
                    </h4>
                    <ul class="space-y-4">
                        <?php 
                        $locations->data_seek(0);
                        $firstLocation = $locations->fetch_assoc();
                        if ($firstLocation): 
                            $locations->data_seek(0);
                        ?>
                            <li class="flex items-start">
                                <div class="w-10 h-10 bg-gray-800/80 rounded-xl flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-emerald-400"></i>
                                </div>
                                <div>
                                    <div class="text-white font-semibold mb-1">Address</div>
                                    <div class="text-gray-400 text-sm leading-relaxed"><?= htmlspecialchars($firstLocation['address']) ?></div>
                                </div>
                            </li>
                            <?php if ($firstLocation['phone']): ?>
                                <li class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-800/80 rounded-xl flex items-center justify-center mr-3">
                                        <i class="fas fa-phone text-emerald-400"></i>
                                    </div>
                                    <div>
                                        <div class="text-white font-semibold"><?= htmlspecialchars($firstLocation['phone']) ?></div>
                                    </div>
                                </li>
                            <?php endif; ?>
                            <?php if ($firstLocation['hours']): ?>
                                <li class="flex items-start">
                                    <div class="w-10 h-10 bg-gray-800/80 rounded-xl flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-clock text-emerald-400"></i>
                                    </div>
                                    <div>
                                        <div class="text-white font-semibold mb-1">Business Hours</div>
                                        <div class="text-gray-400 text-sm whitespace-pre-line"><?= htmlspecialchars($firstLocation['hours']) ?></div>
                                    </div>
                                </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li class="flex items-start">
                                <div class="w-10 h-10 bg-gray-800/80 rounded-xl flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-map-marker-alt text-emerald-400"></i>
                                </div>
                                <div>
                                    <div class="text-white font-semibold mb-1">Address</div>
                                    <div class="text-gray-400 text-sm">123 Hardware Street, City, Philippines</div>
                                </div>
                            </li>
                            <li class="flex items-center">
                                <div class="w-10 h-10 bg-gray-800/80 rounded-xl flex items-center justify-center mr-3">
                                    <i class="fas fa-phone text-emerald-400"></i>
                                </div>
                                <div class="text-white font-semibold">(02) 1234-5678</div>
                            </li>
                            <li class="flex items-start">
                                <div class="w-10 h-10 bg-gray-800/80 rounded-xl flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-clock text-emerald-400"></i>
                                </div>
                                <div>
                                    <div class="text-white font-semibold mb-1">Business Hours</div>
                                    <div class="text-gray-400 text-sm">Mon-Sat: 8:00 AM - 6:00 PM</div>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Newsletter/Info -->
                <div>
                    <h4 class="text-lg font-bold text-white mb-6 flex items-center">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-emerald-400 to-green-600 rounded-full mr-3"></span>
                        Stay Updated
                    </h4>
                    <p class="text-gray-400 mb-5 leading-relaxed">
                        Subscribe to our newsletter for the latest products, promotions, and hardware tips.
                    </p>
                    <form class="space-y-3">
                        <input type="email" placeholder="Enter your email" 
                               class="w-full px-4 py-3.5 bg-gray-800/80 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all font-medium">
                        <button type="submit" class="w-full px-4 py-3.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold rounded-xl hover:from-emerald-600 hover:to-green-700 transition-all shadow-lg shadow-emerald-900/30 hover:shadow-xl">
                            <i class="fas fa-paper-plane mr-2"></i>Subscribe
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 pt-8 mt-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-gray-400 text-sm font-medium">
                        <p>&copy; <?= date('Y') ?> <span class="text-white font-bold">Danicop Hardware Online</span>. All rights reserved.</p>
                    </div>
                    <div class="flex items-center space-x-6 text-sm text-gray-400 font-medium">
                        <a href="#" class="hover:text-emerald-400 transition-colors">Privacy Policy</a>
                        <span class="text-gray-700">•</span>
                        <a href="#" class="hover:text-emerald-400 transition-colors">Terms of Service</a>
                        <span class="text-gray-700">•</span>
                        <a href="#" class="hover:text-emerald-400 transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Cart Management
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        function updateCartDisplay() {
            const cartItems = document.getElementById('cart-items');
            const cartCount = document.getElementById('cart-count');
            const cartCountMobile = document.getElementById('cart-count-mobile');
            const cartTotal = document.getElementById('cart-total');
            
            let total = 0;
            let count = 0;
            
            // Calculate totals
            cart.forEach((item) => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                count += item.quantity;
            });
            
            // Update cart items display only if element exists
            if (cartItems) {
                if (cart.length === 0) {
                    cartItems.innerHTML = '<p class="text-gray-500 text-center py-8">Your cart is empty</p>';
                } else {
                    cartItems.innerHTML = cart.map((item, index) => {
                        const itemTotal = item.price * item.quantity;
                        return `
                            <div class="border-b pb-4">
                                <div class="flex justify-between mb-2">
                                    <h4 class="font-semibold">${item.name}</h4>
                                    <button onclick="removeFromCart(${index})" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="updateQuantity(${index}, -1)" class="bg-gray-200 px-2 py-1 rounded">-</button>
                                        <span>${item.quantity}</span>
                                        <button onclick="updateQuantity(${index}, 1)" class="bg-gray-200 px-2 py-1 rounded">+</button>
                                    </div>
                                    <span class="font-semibold">₱${(itemTotal).toFixed(2)}</span>
                                </div>
                                <p class="text-sm text-gray-500">₱${item.price.toFixed(2)} each</p>
                            </div>
                        `;
                    }).join('');
                }
            }
            
            // Update cart count displays only if elements exist
            if (cartCount) {
                cartCount.textContent = count;
                if (count > 0) {
                    cartCount.classList.add('cart-badge');
                    setTimeout(() => cartCount.classList.remove('cart-badge'), 500);
                }
            }
            if (cartCountMobile) {
                cartCountMobile.textContent = count;
            }
            if (cartTotal) {
                cartTotal.textContent = '₱' + total.toFixed(2);
            }
        }

        function addToCart(id, name, price, stock, image) {
            const existingItem = cart.find(item => item.id === id);
            
            if (existingItem) {
                if (existingItem.quantity >= stock) {
                    alert('Cannot add more. Stock limit reached.');
                    return;
                }
                existingItem.quantity++;
            } else {
                cart.push({ id, name, price, stock, quantity: 1, image: image || '' });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
            
            // Show animated notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl z-50 animate-fade-in transform translate-x-0';
            notification.style.animation = 'slideInRight 0.5s ease-out';
            notification.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas fa-check-circle text-2xl animate-pulse"></i>
                    <div>
                        <p class="font-bold">Added to Cart!</p>
                        <p class="text-sm opacity-90">${name}</p>
                    </div>
                </div>
            `;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.animation = 'slideInRight 0.5s ease-out reverse';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
        }

        function updateQuantity(index, change) {
            const item = cart[index];
            const newQuantity = item.quantity + change;
            
            if (newQuantity <= 0) {
                removeFromCart(index);
                return;
            }
            
            if (newQuantity > item.stock) {
                alert('Cannot add more. Stock limit reached.');
                return;
            }
            
            item.quantity = newQuantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartDisplay();
        }

        function checkout() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
           <?php if (!isLoggedIn()): ?>
               // Open login modal
               openLoginModal();
           <?php else: ?>
               // Redirect based on role
               <?php if (getUserRole() === 'superadmin'): ?>
                   window.location.href = 'admin/index.php';
               <?php elseif (getUserRole() === 'staff'): ?>
                   window.location.href = 'staff/dashboard.php';
               <?php else: ?>
                   window.location.href = 'customer/checkout.php';
               <?php endif; ?>
           <?php endif; ?>
       }
        
        // Functions to open modals
        function openLoginModal() {
            const body = document.body;
            if (body && body.__x) {
                body.__x.$data.loginModalOpen = true;
            }
        }
        
        function openRegisterModal() {
            const body = document.body;
            if (body && body.__x) {
                body.__x.$data.registerModalOpen = true;
            }
        }
        
        // Listen for login modal open event
        window.addEventListener('openLoginModal', openLoginModal);

        // Initialize cart display on page load (only if cart elements exist)
        // This prevents errors on pages without cart UI
        if (document.getElementById('cart-items') || document.getElementById('cart-count')) {
            updateCartDisplay();
        }

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
        <?php if ($login_error): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Open login modal if error
            setTimeout(() => {
                const body = document.body;
                if (body && body.__x) {
                    body.__x.$data.loginModalOpen = true;
                }
                // Show error toast
                showToast('error', 'Login Failed', '<?= addslashes($login_error) ?>');
            }, 100);
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

