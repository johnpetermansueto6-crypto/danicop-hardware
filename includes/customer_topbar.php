<?php
// Customer Top Bar Component (Shopee-style)
// Usage: Set $current_page variable before including this file
$current_page = $current_page ?? 'shop';
?>
<!-- Top Navigation Bar (Shopee-style) -->
<nav class="bg-green-600 text-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Left Side: Logo and Products -->
            <div class="flex items-center space-x-6">
                <!-- Logo -->
                <a href="../index.php" class="flex items-center space-x-2 hover:opacity-80 transition-opacity">
                    <div class="bg-white p-2 rounded-lg">
                        <i class="fas fa-tools text-green-600 text-xl"></i>
                    </div>
                    <span class="text-xl font-bold hidden sm:block">Danicop Hardware</span>
                </a>
                
                <!-- Products Link -->
                <a href="shop.php" class="flex items-center space-x-2 px-4 py-2 rounded-lg hover:bg-green-700 transition-colors <?= $current_page === 'shop' ? 'bg-green-700' : '' ?>">
                    <i class="fas fa-store"></i>
                    <span class="hidden md:inline">Products</span>
                </a>
            </div>
            
            <!-- Right Side: My Orders, Checkout, Our Store, Settings -->
            <div class="flex items-center space-x-2 md:space-x-4">
                <!-- Our Store -->
                <a href="our_store.php" class="flex items-center space-x-1 px-3 py-2 rounded-lg hover:bg-green-700 transition-colors <?= $current_page === 'our_store' ? 'bg-green-700' : '' ?>">
                    <i class="fas fa-store"></i>
                    <span class="hidden lg:inline">Our Store</span>
                </a>
                
                <!-- My Orders -->
                <a href="orders.php" class="flex items-center space-x-1 px-3 py-2 rounded-lg hover:bg-green-700 transition-colors <?= $current_page === 'orders' ? 'bg-green-700' : '' ?>">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="hidden md:inline">My Orders</span>
                </a>
                
                <!-- Checkout with Badge -->
                <a href="checkout.php" class="relative flex items-center space-x-1 px-3 py-2 rounded-lg hover:bg-green-700 transition-colors <?= $current_page === 'checkout' ? 'bg-green-700' : '' ?>">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <span class="hidden md:inline">Checkout</span>
                    <span id="checkout-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                </a>
                
                <!-- Settings/Dropdown -->
                <div class="relative" x-data="{ open: false }" x-init="open = false" @click.outside="open = false">
                    <button @click.stop="open = !open" type="button" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-green-700 transition-colors <?= $current_page === 'profile' ? 'bg-green-700' : '' ?>">
                        <i class="fas fa-user-circle text-xl"></i>
                        <span class="hidden md:inline text-sm"><?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?></span>
                        <i class="fas fa-chevron-down text-xs hidden md:inline"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                         x-cloak
                         @click.stop
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                        <a href="profile.php" @click="open = false" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-user w-5"></i>
                            <span>My Profile</span>
                        </a>
                        <?php if (getUserRole() === 'superadmin'): ?>
                        <a href="../admin/dashboard.php" @click="open = false" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-tachometer-alt w-5"></i>
                            <span>Admin Dashboard</span>
                        </a>
                        <?php elseif (getUserRole() === 'staff'): ?>
                        <a href="../staff/dashboard.php" @click="open = false" class="flex items-center space-x-2 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-tachometer-alt w-5"></i>
                            <span>Staff Dashboard</span>
                        </a>
                        <?php endif; ?>
                        <hr class="my-2">
                        <a href="../auth/logout.php" @click="open = false" class="flex items-center space-x-2 px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

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

        const badge = document.getElementById('checkout-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }

    window.updateCheckoutBadge = updateCheckoutBadge;

    // Initial badge update
    try {
        updateCheckoutBadge();
    } catch (e) {
        console.warn('Could not update checkout badge', e);
    }
</script>

