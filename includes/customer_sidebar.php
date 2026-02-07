<?php
// Customer Sidebar Component
// Usage: Set $current_page variable before including this file
$current_page = $current_page ?? 'profile';
?>
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-indigo-800 text-white flex-shrink-0 hidden md:block">
        <div class="h-full flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-indigo-700">
                <a href="../index.php" class="text-xl font-bold flex items-center space-x-2">
                    <span class="text-2xl">🔧</span>
                    <span>Danicop Hardware</span>
                </a>
                <p class="text-indigo-200 text-sm mt-1">My Account</p>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="profile.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'profile' ? 'bg-indigo-700' : 'hover:bg-indigo-700' ?> transition-colors">
                    <i class="fas fa-user w-5"></i>
                    <span>My Profile</span>
                </a>
                <a href="orders.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'orders' ? 'bg-indigo-700' : 'hover:bg-indigo-700' ?> transition-colors">
                    <i class="fas fa-shopping-bag w-5"></i>
                    <span>My Orders</span>
                </a>
                <a href="checkout.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'checkout' ? 'bg-indigo-700' : 'hover:bg-indigo-700' ?> transition-colors">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span>Checkout</span>
                    <span id="checkout-count-desktop" class="ml-2 text-xs px-2 py-0.5 rounded-full bg-red-500 text-white hidden">0</span>
                </a>
                <?php if (getUserRole() === 'superadmin'): ?>
                <a href="../admin/dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Admin Dashboard</span>
                </a>
                <?php elseif (getUserRole() === 'staff'): ?>
                <a href="../staff/dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Staff Dashboard</span>
                </a>
                <?php endif; ?>
            </nav>
            
            <!-- User Info -->
            <div class="p-4 border-t border-indigo-700">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-indigo-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                        <p class="text-xs text-indigo-200 truncate"><?= ucfirst($_SESSION['role']) ?></p>
                    </div>
                </div>
                <a href="shop.php" class="block w-full text-center px-4 py-2 bg-indigo-700 rounded-lg hover:bg-indigo-600 transition-colors mb-2">
                    <i class="fas fa-store mr-2"></i> Continue Shopping
                </a>
                <a href="../auth/logout.php" class="block w-full text-center px-4 py-2 bg-red-600 rounded-lg hover:bg-red-500 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </aside>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"></div>

    <!-- Mobile Sidebar -->
    <aside x-show="sidebarOpen" x-cloak x-transition class="fixed inset-y-0 left-0 w-64 bg-indigo-800 text-white z-50 md:hidden">
        <div class="h-full flex flex-col">
            <div class="p-6 border-b border-indigo-700 flex items-center justify-between">
                <a href="../index.php" class="text-xl font-bold flex items-center space-x-2">
                    <span class="text-2xl">🔧</span>
                    <span>Danicop Hardware</span>
                </a>
                <button @click="sidebarOpen = false" class="text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="profile.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'profile' ? 'bg-indigo-700' : 'hover:bg-indigo-700' ?>">
                    <i class="fas fa-user w-5"></i>
                    <span>My Profile</span>
                </a>
                <a href="orders.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'orders' ? 'bg-indigo-700' : 'hover:bg-indigo-700' ?>">
                    <i class="fas fa-shopping-bag w-5"></i>
                    <span>My Orders</span>
                </a>
                <a href="checkout.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'checkout' ? 'bg-indigo-700' : 'hover:bg-indigo-700' ?>">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span>Checkout</span>
                    <span id="checkout-count-mobile" class="ml-2 text-xs px-2 py-0.5 rounded-full bg-red-500 text-white hidden">0</span>
                </a>
                <?php if (getUserRole() === 'superadmin'): ?>
                <a href="../admin/dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Admin Dashboard</span>
                </a>
                <?php elseif (getUserRole() === 'staff'): ?>
                <a href="../staff/dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Staff Dashboard</span>
                </a>
                <?php endif; ?>
            </nav>
            <div class="p-4 border-t border-indigo-700">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-indigo-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                        <p class="text-xs text-indigo-200"><?= ucfirst($_SESSION['role']) ?></p>
                    </div>
                </div>
                <a href="shop.php" class="block w-full text-center px-4 py-2 bg-indigo-700 rounded-lg hover:bg-indigo-600 mb-2">
                    <i class="fas fa-store mr-2"></i> Continue Shopping
                </a>
                <a href="../auth/logout.php" class="block w-full text-center px-4 py-2 bg-red-600 rounded-lg hover:bg-red-500">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="flex items-center justify-between px-4 py-3">
                <button @click="sidebarOpen = true" class="md:hidden text-gray-600 hover:text-gray-900">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h1 class="text-2xl font-bold text-gray-800"><?= $page_title ?? 'My Account' ?></h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600 hidden sm:block"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 overflow-y-auto p-6">
        
        <script>
            function updateCheckoutBadge() {
                let cart = [];
                try {
                    cart = JSON.parse(localStorage.getItem('cart')) || [];
                } catch (e) {
                    cart = [];
                }

                let count = 0;
                cart.forEach(item => {
                    const qty = typeof item.quantity === 'number' ? item.quantity : 1;
                    count += qty;
                });

                const desktop = document.getElementById('checkout-count-desktop');
                const mobile = document.getElementById('checkout-count-mobile');
                [desktop, mobile].forEach(el => {
                    if (!el) return;
                    if (count > 0) {
                        el.textContent = count;
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });
            }

            window.updateCheckoutBadge = updateCheckoutBadge;

            // Initial badge update when the layout loads
            try {
                updateCheckoutBadge();
            } catch (e) {
                console.warn('Could not update checkout badge', e);
            }
        </script>
