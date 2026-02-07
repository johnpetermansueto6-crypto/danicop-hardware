<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="w-64 bg-green-700 text-white min-h-screen fixed left-0 top-0 overflow-y-auto">
    <div class="p-4">
        <!-- Logo/Header -->
        <div class="flex items-center space-x-3 mb-8 pt-4">
            <img src="../assets/images/logo.svg" alt="Logo" class="w-10 h-10 object-contain bg-white rounded-lg p-1">
            <div>
                <h2 class="text-lg font-bold">Driver Portal</h2>
                <p class="text-xs text-green-200"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="space-y-2">
            <a href="dashboard.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors <?= $current_page === 'dashboard.php' ? 'bg-green-800 text-white' : 'text-green-100 hover:bg-green-600' ?>">
                <i class="fas fa-chart-line w-5"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="assigned_deliveries.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors <?= $current_page === 'assigned_deliveries.php' ? 'bg-green-800 text-white' : 'text-green-100 hover:bg-green-600' ?>">
                <i class="fas fa-clipboard-list w-5"></i>
                <span>Assigned Deliveries</span>
            </a>
            
            <a href="completed_deliveries.php" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors <?= $current_page === 'completed_deliveries.php' ? 'bg-green-800 text-white' : 'text-green-100 hover:bg-green-600' ?>">
                <i class="fas fa-check-circle w-5"></i>
                <span>Completed Deliveries</span>
            </a>
        </nav>
    </div>
    
    <!-- Logout -->
    <div class="absolute bottom-0 w-full p-4 border-t border-green-600">
        <a href="../auth/logout.php" 
           class="flex items-center space-x-3 px-4 py-3 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
            <i class="fas fa-sign-out-alt w-5"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

