<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Get user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();

$current_page = 'orders';
$page_title = 'My Orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        .order-card {
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .order-item {
            opacity: 0;
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .order-item:nth-child(1) { animation-delay: 0.1s; }
        .order-item:nth-child(2) { animation-delay: 0.2s; }
        .order-item:nth-child(3) { animation-delay: 0.3s; }
        .order-item:nth-child(4) { animation-delay: 0.4s; }
        .order-item:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
<?php include '../includes/customer_topbar.php'; ?>

<!-- Main Content -->
<div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">My Orders</h1>
            <p class="text-gray-600">Track and manage all your orders</p>
        </div>
        
        <?php if ($orders->num_rows > 0): ?>
            <div class="space-y-6">
                <?php $index = 0; while ($order = $orders->fetch_assoc()): $index++; ?>
                    <div class="order-card order-item bg-white rounded-2xl shadow-xl border border-gray-100" x-data="{ expanded: false }">
                        <!-- Order Header (Always Visible) -->
                        <div class="p-6 cursor-pointer" @click="expanded = !expanded">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div class="mb-4 md:mb-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <i class="fas fa-receipt text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-bold text-gray-800">Order #<?= htmlspecialchars($order['order_number']) ?></h2>
                                        <p class="text-gray-500 text-sm">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="px-4 py-2 rounded-full text-sm font-bold shadow-md
                                    <?php
                                    switch($order['status']) {
                                        case 'pending': echo 'bg-yellow-100 text-yellow-800 border-2 border-yellow-300'; break;
                                        case 'confirmed': echo 'bg-blue-100 text-blue-800 border-2 border-blue-300'; break;
                                        case 'preparing': echo 'bg-purple-100 text-purple-800 border-2 border-purple-300'; break;
                                        case 'out_for_delivery': echo 'bg-indigo-100 text-indigo-800 border-2 border-indigo-300'; break;
                                        case 'ready_for_pickup': echo 'bg-green-100 text-green-800 border-2 border-green-300'; break;
                                        case 'completed': echo 'bg-green-200 text-green-900 border-2 border-green-400'; break;
                                        case 'cancelled': echo 'bg-red-100 text-red-800 border-2 border-red-300'; break;
                                        default: echo 'bg-gray-100 text-gray-800 border-2 border-gray-300';
                                    }
                                    ?>">
                                    <i class="fas fa-circle text-xs mr-2"></i>
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                            </div>
                            <!-- Expand/Collapse Icon -->
                            <div class="flex items-center justify-center md:justify-end mt-4 md:mt-0">
                                <button class="flex items-center gap-2 text-gray-600 hover:text-green-600 transition-colors">
                                    <span class="text-sm font-medium" x-text="expanded ? 'Hide Details' : 'Show Details'"></span>
                                    <i class="fas transition-transform duration-300" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Order Details (Collapsible) -->
                        <div x-show="expanded" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             x-cloak
                             class="px-6 pb-6 border-t border-gray-200 pt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-gray-600 text-sm mb-1">
                                    <i class="fas fa-credit-card mr-2 text-blue-600"></i>Payment Method
                                </p>
                                <p class="font-semibold text-gray-800">
                                    <?php 
                                    $payment = $order['payment_method'] ?? '';
                                    if (!empty($payment)) {
                                        // Convert payment method codes to friendly names
                                        switch($payment) {
                                            case 'cash_pickup':
                                                echo 'Over the Counter';
                                                break;
                                            case 'cash_delivery':
                                                echo 'Cash on Delivery';
                                                break;
                                            default:
                                                echo ucfirst(str_replace('_', ' ', $payment));
                                        }
                                    } else {
                                        echo '<span class="text-gray-400 italic">Not specified</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-gray-600 text-sm mb-1">
                                    <i class="fas fa-truck mr-2 text-green-600"></i>Delivery Method
                                </p>
                                <p class="font-semibold text-gray-800">
                                    <?php 
                                    $delivery = $order['delivery_method'] ?? '';
                                    if (!empty($delivery)) {
                                        echo ucfirst(str_replace('_', ' ', $delivery));
                                    } else {
                                        echo '<span class="text-gray-400 italic">Not specified</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                            <?php if (!empty($order['delivery_method']) && $order['delivery_method'] === 'delivery' && !empty($order['delivery_address'])): ?>
                                <div class="bg-gray-50 rounded-xl p-4 md:col-span-2">
                                    <p class="text-gray-600 text-sm mb-1">
                                        <i class="fas fa-map-marker-alt mr-2 text-red-600"></i>Delivery Address
                                    </p>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['delivery_address']) ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($order['contact_number'])): ?>
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <p class="text-gray-600 text-sm mb-1">
                                        <i class="fas fa-phone mr-2 text-purple-600"></i>Contact Number
                                    </p>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['contact_number']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Order Items -->
                        <?php
                        $itemStmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                        $itemStmt->bind_param("i", $order['id']);
                        $itemStmt->execute();
                        $items = $itemStmt->get_result();
                        ?>
                        <div class="border-t-2 border-gray-200 pt-6">
                            <h3 class="font-bold text-lg mb-4 flex items-center">
                                <i class="fas fa-shopping-bag mr-2 text-blue-600"></i>Order Items
                            </h3>
                            <div class="space-y-3 mb-4">
                                <?php while ($item = $items->fetch_assoc()): ?>
                                    <div class="flex items-center bg-gray-50 rounded-xl p-4 gap-4">
                                        <!-- Product Image -->
                                        <div class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden bg-white border border-gray-200 shadow-sm">
                                            <?php if (!empty($item['image']) && file_exists('../uploads/' . $item['image'])): ?>
                                                <img src="../uploads/<?= htmlspecialchars($item['image']) ?>" 
                                                     alt="<?= htmlspecialchars(html_entity_decode($item['name'])) ?>" 
                                                     class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                                    <i class="fas fa-box text-2xl text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Product Details -->
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-800 truncate"><?= htmlspecialchars(html_entity_decode($item['name'])) ?></p>
                                            <p class="text-sm text-gray-600">
                                                Quantity: <?= $item['quantity'] ?> × ₱<?= number_format($item['price'], 2) ?>
                                            </p>
                                        </div>
                                        <!-- Subtotal -->
                                        <div class="flex-shrink-0 text-right">
                                            <span class="font-bold text-blue-600 text-lg">₱<?= number_format($item['subtotal'], 2) ?></span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                            <!-- Delivery Fee Section -->
                            <!-- Delivery Fee Section -->
                            <?php 
                            // Calculate delivery fee
                            // 1. Check if delivery_fee column exists and is > 0
                            // 2. Or if total_amount > sum of items (for older orders)
                            $showDeliveryFee = false;
                            $deliveryFeeAmount = 0;
                            $itemsSubtotal = 0;

                            // We need to re-query or store items to calculate subtotal
                            // Since we already iterated, we can't easily re-iterate the result set pointer without reset.
                            // But we can just use the difference if delivery_fee is not set.
                            
                            if (isset($order['delivery_fee']) && $order['delivery_fee'] > 0) {
                                $showDeliveryFee = true;
                                $deliveryFeeAmount = $order['delivery_fee'];
                            } else {
                                // Fallback: Calculate items total
                                // We need to re-fetch items or use a separate query sum
                                $subtotalQuery = $conn->query("SELECT SUM(subtotal) as total FROM order_items WHERE order_id = " . $order['id']);
                                $subtotalData = $subtotalQuery->fetch_assoc();
                                $itemsSubtotal = $subtotalData['total'] ?? 0;
                                
                                $diff = $order['total_amount'] - $itemsSubtotal;
                                // Allow small float difference
                                if ($diff > 0.01) {
                                    $showDeliveryFee = true;
                                    $deliveryFeeAmount = $diff;
                                }
                            }
                            ?>
                            
                            <?php if ($showDeliveryFee): ?>
                                <div class="flex items-center bg-gray-50 rounded-xl p-4 gap-4 mt-2 border-t-2 border-dashed border-gray-200">
                                    <div class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden bg-white border border-gray-200 shadow-sm flex items-center justify-center">
                                        <i class="fas fa-truck text-2xl text-emerald-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-800">Delivery Fee</p>
                                        <p class="text-sm text-gray-600">Shipping & Handling</p>
                                    </div>
                                    <div class="flex-shrink-0 text-right">
                                        <span class="font-bold text-emerald-600 text-lg">₱<?= number_format($deliveryFeeAmount, 2) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            </div>
                            <div class="flex justify-between items-center pt-4 border-t-2 border-gray-200">
                                <span class="text-xl font-bold text-gray-700">Total Amount:</span>
                                <span class="text-3xl font-extrabold bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent">
                                    ₱<?= number_format($order['total_amount'], 2) ?>
                                </span>
                            </div>
                        </div>
                        </div>
                        <!-- End of Collapsible Section -->
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-xl p-16 text-center animate-fade-in">
                <div class="inline-block p-6 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full mb-6">
                    <i class="fas fa-shopping-bag text-6xl text-blue-600"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-800 mb-3">No Orders Yet</h2>
                <p class="text-gray-600 mb-8 text-lg">Start shopping to see your orders here!</p>
                <a href="../index.php" class="inline-block bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-shopping-cart mr-2"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
</div>
</body>
</html>
