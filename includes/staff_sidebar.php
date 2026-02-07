<?php
// Staff Sidebar Component
// Usage: Set $current_page variable before including this file
$current_page = $current_page ?? 'dashboard';
$unread_notifications = $unread_notifications ?? 0;
?>
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-green-800 text-white flex-shrink-0 hidden md:block">
        <div class="h-full flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-green-700">
                <a href="../index.php" class="text-xl font-bold flex items-center space-x-2">
                    <img src="../assets/images/logo.svg" alt="Logo" class="w-10 h-10 object-contain bg-white rounded-lg p-1">
                    <span>Danicop Hardware</span>
                </a>
                <p class="text-green-200 text-sm mt-1">Staff Panel</p>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'dashboard' ? 'bg-green-700' : 'hover:bg-green-700' ?> transition-colors">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="orders.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'orders' ? 'bg-green-700' : 'hover:bg-green-700' ?> transition-colors">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span>Orders</span>
                </a>
                <a href="products.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'products' ? 'bg-green-700' : 'hover:bg-green-700' ?> transition-colors">
                    <i class="fas fa-warehouse w-5"></i>
                    <span>Update Stock</span>
                </a>
                <a href="notifications.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'notifications' ? 'bg-green-700' : 'hover:bg-green-700' ?> transition-colors relative">
                    <i class="fas fa-bell w-5"></i>
                    <span>Notifications</span>
                    <?php if ($unread_notifications > 0): ?>
                        <span class="absolute right-4 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?= $unread_notifications ?>
                        </span>
                    <?php endif; ?>
                </a>
            </nav>
            
            <!-- User Info -->
            <div class="p-4 border-t border-green-700">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-green-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                        <p class="text-xs text-green-200 truncate">Staff</p>
                    </div>
                </div>
                <a href="../index.php" class="block w-full text-center px-4 py-2 bg-green-700 rounded-lg hover:bg-green-600 transition-colors mb-2">
                    <i class="fas fa-store mr-2"></i> View Store
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
    <aside x-show="sidebarOpen" x-cloak x-transition class="fixed inset-y-0 left-0 w-64 bg-green-800 text-white z-50 md:hidden">
        <div class="h-full flex flex-col">
            <div class="p-6 border-b border-green-700 flex items-center justify-between">
                <a href="../index.php" class="text-xl font-bold flex items-center space-x-2">
                    <img src="../assets/images/logo.svg" alt="Logo" class="w-10 h-10 object-contain bg-white rounded-lg p-1">
                    <span>Danicop Hardware</span>
                </a>
                <button @click="sidebarOpen = false" class="text-white">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'dashboard' ? 'bg-green-700' : 'hover:bg-green-700' ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="orders.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'orders' ? 'bg-green-700' : 'hover:bg-green-700' ?>">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span>Orders</span>
                </a>
                <a href="products.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'products' ? 'bg-green-700' : 'hover:bg-green-700' ?>">
                    <i class="fas fa-warehouse w-5"></i>
                    <span>Update Stock</span>
                </a>
                <a href="notifications.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg <?= $current_page === 'notifications' ? 'bg-green-700' : 'hover:bg-green-700' ?> relative">
                    <i class="fas fa-bell w-5"></i>
                    <span>Notifications</span>
                    <?php if ($unread_notifications > 0): ?>
                        <span class="absolute right-4 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?= $unread_notifications ?>
                        </span>
                    <?php endif; ?>
                </a>
            </nav>
            <div class="p-4 border-t border-green-700">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-green-700 rounded-full flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                        <p class="text-xs text-green-200">Staff</p>
                    </div>
                </div>
                <a href="../index.php" class="block w-full text-center px-4 py-2 bg-green-700 rounded-lg hover:bg-green-600 mb-2">
                    <i class="fas fa-store mr-2"></i> View Store
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
                <h1 class="text-2xl font-bold text-gray-800"><?= $page_title ?? 'Staff Panel' ?></h1>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600 hidden sm:block">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="flex-1 overflow-y-auto p-6">

