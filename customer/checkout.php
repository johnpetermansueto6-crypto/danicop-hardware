<?php
require_once '../includes/config.php';
require_once '../includes/mailer.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php?redirect=checkout');
}

/**
 * Calculate distance between two coordinates using Haversine formula
 * @param float $lat1 Latitude of first point
 * @param float $lon1 Longitude of first point
 * @param float $lat2 Latitude of second point
 * @param float $lon2 Longitude of second point
 * @return float Distance in kilometers
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth's radius in kilometers
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;
    
    return $distance;
}

// Load current user to enforce profile completion for Google accounts
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();
$requiresProfileCompletion = false;
if ($currentUser && isset($currentUser['auth_provider']) && $currentUser['auth_provider'] === 'google') {
    $requiresProfileCompletion = isset($currentUser['profile_completed']) && !$currentUser['profile_completed'];
}

// Cart will be loaded from localStorage via JavaScript
$cart = [];

$error = '';
$success = '';
$order_success_number = '';

// Handle order success from redirect
if (isset($_GET['order_success']) && isset($_SESSION['order_success'])) {
    $order_success_number = $_SESSION['order_success'];
    unset($_SESSION['order_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if ($requiresProfileCompletion) {
        $error = 'Please complete your account details in your profile before proceeding to checkout.';
    } else {
        $delivery_method = sanitize($_POST['delivery_method'] ?? 'pickup');
        $payment_method = sanitize($_POST['payment_method'] ?? 'cash_pickup');
        $delivery_address = sanitize($_POST['delivery_address'] ?? '');
        $delivery_latitude = !empty($_POST['delivery_latitude']) ? floatval($_POST['delivery_latitude']) : null;
        $delivery_longitude = !empty($_POST['delivery_longitude']) ? floatval($_POST['delivery_longitude']) : null;
        $contact_number = sanitize($_POST['contact_number'] ?? '');
        
        // Get cart from POST data
        $cart = json_decode($_POST['cart'] ?? '[]', true);
        
        if (empty($cart)) {
            $error = 'Your cart is empty';
        } elseif (empty($contact_number)) {
            $error = 'Contact number is required';
        } elseif ($delivery_method === 'delivery' && empty($delivery_address)) {
            $error = 'Delivery address is required';
        } elseif ($delivery_method === 'delivery' && (empty($delivery_latitude) || empty($delivery_longitude))) {
            $error = 'Please set your delivery location on the map or use "Use My Current Location" button';
        } else {
            // Calculate total
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            
            // Calculate delivery fee if delivery method
            $delivery_fee = 0;
            if ($delivery_method === 'delivery' && $delivery_latitude && $delivery_longitude) {
                // Store location: DANICOP HARDWARE & CONSTRUCTION SUPPLY
                $storeLat = 9.797538;
                $storeLng = 123.792802;
                
                // Calculate distance using Haversine formula
                $distance = calculateDistance($storeLat, $storeLng, $delivery_latitude, $delivery_longitude);
                
                // Fee per km (you can adjust this)
                $feePerKm = 10; // PHP 10 per km
                // Only charge if distance is 1km or more (if less than 1km, no fee)
                if ($distance >= 1.0) {
                    $delivery_fee = $distance * $feePerKm; // Charge exactly per km
                } else {
                    $delivery_fee = 0; // No fee if less than 1km
                }
                
                $total += $delivery_fee;
            }
            
            // Generate order number
            $order_number = generateOrderNumber();
            
            // Create order - check if delivery_fee column exists
            $checkColumn = $conn->query("SHOW COLUMNS FROM orders LIKE 'delivery_fee'");
            if ($checkColumn && $checkColumn->num_rows > 0) {
                // Column exists, include it in insert
                $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, payment_method, delivery_method, delivery_address, delivery_latitude, delivery_longitude, contact_number, status, delivery_fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
                $stmt->bind_param("isdsssddsd", $_SESSION['user_id'], $order_number, $total, $payment_method, $delivery_method, $delivery_address, $delivery_latitude, $delivery_longitude, $contact_number, $delivery_fee);
            } else {
                // Column doesn't exist, insert without it
                $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, payment_method, delivery_method, delivery_address, delivery_latitude, delivery_longitude, contact_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param("isdsssdds", $_SESSION['user_id'], $order_number, $total, $payment_method, $delivery_method, $delivery_address, $delivery_latitude, $delivery_longitude, $contact_number);
            }
            
            if ($stmt->execute()) {
                $order_id = $conn->insert_id;
                
                // Add order items and update stock
                foreach ($cart as $item) {
                    $subtotal = $item['price'] * $item['quantity'];
                    
                    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("iiidd", $order_id, $item['id'], $item['quantity'], $item['price'], $subtotal);
                    $stmt->execute();
                    
                    // Update product stock
                    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $stmt->bind_param("ii", $item['quantity'], $item['id']);
                    $stmt->execute();
                    
                    // Check for low stock and create notification
                    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
                    $stmt->bind_param("i", $item['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();
                    
                    if ($product['stock'] < 10) {
                        $stmt = $conn->prepare("SELECT id FROM users WHERE role IN ('superadmin', 'staff')");
                        $stmt->execute();
                        $admins = $stmt->get_result();
                        
                        while ($admin = $admins->fetch_assoc()) {
                            $stmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('low_stock', ?, ?)");
                            $message = "Low stock alert: Product ID {$item['id']} has {$product['stock']} items remaining";
                            $stmt->bind_param("si", $message, $admin['id']);
                            $stmt->execute();
                        }

                        // Email low-stock alert to admins/staff
                        try {
                            $stmt = $conn->prepare("SELECT name, email FROM users WHERE role IN ('superadmin', 'staff') AND email IS NOT NULL");
                            $stmt->execute();
                            $adminResult = $stmt->get_result();
                            $recipients = [];
                            while ($row = $adminResult->fetch_assoc()) {
                                $recipients[$row['email']] = $row['name'];
                            }
                            if (!empty($recipients)) {
                                $subject = "Low Stock Alert - Product ID {$item['id']}";
                                $body = "<p>Dear Admin/Staff,</p>
                                    <p>The following product is low on stock:</p>
                                    <ul>
                                        <li><strong>Product ID:</strong> {$item['id']}</li>
                                        <li><strong>Quantity Remaining:</strong> {$product['stock']}</li>
                                    </ul>
                                    <p>Please review inventory and restock if necessary.</p>
                                    <p>Regards,<br>Danicop Hardware System</p>";
                                send_app_email($recipients, $subject, $body);
                            }
                        } catch (Exception $e) {
                            // Fail silently but log if needed
                            error_log('Low stock mail error: ' . $e->getMessage());
                        }
                    }
                }
                
                // Create notification for new order
                $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE role IN ('superadmin', 'staff')");
                $stmt->execute();
                $admins = $stmt->get_result();
                
                $emailRecipients = [];
                while ($admin = $admins->fetch_assoc()) {
                    $stmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('new_order', ?, ?)");
                    $message = "New order #{$order_number} - Total: ₱" . number_format($total, 2);
                    $stmt->bind_param("si", $message, $admin['id']);
                    $stmt->execute();

                    if (!empty($admin['email'])) {
                        $emailRecipients[$admin['email']] = $admin['name'];
                    }
                }

                // Send email about new order to admins/staff
                if (!empty($emailRecipients)) {
                    $itemsForEmail = [];
                    foreach ($cart as $item) {
                        $itemsForEmail[] = [
                            'name' => $item['name'],
                            'quantity' => $item['quantity'],
                            'price' => $item['price'],
                        ];
                    }
                    $itemsTable = render_order_items_html($itemsForEmail);
                    $subject = "New Order #{$order_number} - ₱" . number_format($total, 2);
                    $body = "<p>Dear Admin/Staff,</p>
                        <p>A new order has been placed.</p>
                        <p><strong>Order Number:</strong> {$order_number}<br>
                           <strong>Total Amount:</strong> ₱" . number_format($total, 2) . "<br>
                           <strong>Delivery Method:</strong> " . htmlspecialchars($delivery_method) . "<br>
                           <strong>Payment Method:</strong> " . htmlspecialchars($payment_method) . "</p>
                        <p><strong>Items:</strong></p>
                        {$itemsTable}
                        <p>You can review this order in the admin panel.</p>
                        <p>Regards,<br>Danicop Hardware System</p>";
                    send_app_email($emailRecipients, $subject, $body);
                }
                
                // Clear cart and redirect with success parameter
                $_SESSION['order_success'] = $order_number;
                header('Location: checkout.php?order_success=1');
                exit;
            } else {
                $error = 'Failed to place order. Please try again.';
            }
        }
    }
}

// Calculate totals (will be calculated by JavaScript)
$subtotal = 0;

// Layout helpers for customer sidebar
$current_page = 'checkout';
$page_title = 'Checkout';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <!-- Leaflet (OpenStreetMap) -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
    <!-- Leaflet Routing Machine -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
<?php include '../includes/customer_topbar.php'; ?>

<!-- Main Content -->
<div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Checkout</h1>
            <p class="text-gray-600">Review your cart, choose delivery, and confirm payment.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
                <p>Redirecting to orders page...</p>
            </div>
        <?php else: ?>
        
        <?php if ($requiresProfileCompletion): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 px-4 py-4 rounded-lg mb-6 max-w-2xl">
                <div class="flex items-start">
                    <i class="fas fa-info-circle mr-3 mt-1"></i>
                    <div>
                        <h2 class="font-bold mb-1">Complete your profile to continue</h2>
                        <p class="text-sm mb-3">
                            You signed in with your Google account. Before you can place an order, please review and save your account details in your profile.
                        </p>
                        <a href="profile.php?complete_profile=1" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-semibold">
                            <i class="fas fa-user-cog mr-2"></i> Go to My Profile
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 <?= $requiresProfileCompletion ? 'opacity-60 pointer-events-none select-none' : '' ?>">
            <!-- Order Summary -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold">Order Summary</h2>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" id="select-all-items" checked 
                                   class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                   onchange="toggleSelectAll(this.checked)">
                            <span class="text-sm font-medium text-gray-700">Select All</span>
                        </label>
                    </div>
                    <div id="order-summary" class="space-y-4">
                        <p class="text-gray-500 text-center py-8">Loading cart...</p>
                    </div>
                    <div class="mt-4 pt-4 border-t">
                        <div class="flex justify-between text-xl font-bold">
                            <span>Total:</span>
                            <span id="order-total">₱0.00</span>
                        </div>
                    </div>
                    
                    <!-- Checkout Now Button -->
                    <button type="button" onclick="openCheckoutModal()" class="w-full mt-6 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 font-semibold text-lg shadow-lg">
                        <i class="fas fa-shopping-cart mr-2"></i> Checkout Now
                    </button>
                </div>

                <!-- Checkout Form (Hidden, will be shown in modal) -->
                <form method="POST" action="checkout.php" class="hidden" id="checkout-form" onsubmit="return validateDeliveryLocation(event)">
                    <input type="hidden" name="cart" id="cart-data" value='[]'>
                    
                    <h2 class="text-xl font-bold mb-4">Delivery & Payment Information</h2>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Delivery Method</label>
                        <select name="delivery_method" id="delivery_method" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                onchange="toggleDeliveryAddress()">
                            <option value="pickup">Pickup at Store</option>
                            <option value="delivery">Home Delivery</option>
                        </select>
                    </div>
                    
                    <div id="delivery_address_field" class="mb-4 hidden">
                        <label class="block text-gray-700 font-bold mb-2">Delivery Address</label>
                        <div class="mb-3">
                            <div class="flex gap-2 mb-2">
                            <textarea name="delivery_address" id="delivery_address" rows="3"
                                          class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="Enter your complete delivery address or click on the map to set location"></textarea>
                                <button type="button" id="use_current_location" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 font-semibold whitespace-nowrap flex items-center gap-2"
                                        onclick="useCurrentLocation()">
                                    <i class="fas fa-location-arrow"></i>
                                    <span class="hidden sm:inline">Use My Current Location</span>
                                    <span class="sm:hidden">Current Location</span>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle"></i> Type an address, click on the map, or use your current location to set your delivery location
                            </p>
                        </div>
                        <div class="mb-3">
                            <div id="delivery_map" style="height: 400px; width: 100%; border-radius: 8px; overflow: hidden; border: 2px solid #e5e7eb;"></div>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-map-marker-alt"></i> Click on the map or use "Use My Current Location" to set your exact delivery location
                            </p>
                            <div id="delivery_info" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded hidden">
                                <p class="text-sm font-semibold text-blue-900">
                                    <i class="fas fa-route mr-1"></i> Distance: <span id="delivery_distance">0</span> km
                                </p>
                                <p class="text-sm font-semibold text-green-600">
                                    <i class="fas fa-peso-sign mr-1"></i> Delivery Fee: ₱<span id="delivery_fee">0.00</span>
                            </p>
                        </div>
                        </div>
                        <input type="hidden" name="delivery_latitude" id="delivery_latitude" value="" required>
                        <input type="hidden" name="delivery_longitude" id="delivery_longitude" value="" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Contact Number</label>
                        <input type="tel" name="contact_number" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="09XX XXX XXXX">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Payment Method</label>
                        <select name="payment_method" id="payment_method" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                onchange="updatePaymentMethod()">
                            <option value="cash_pickup">Over the Counter</option>
                            <option value="cash_delivery">Cash on Delivery</option>
                        </select>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-blue-800" id="payment_info">
                            <i class="fas fa-info-circle"></i> You will pay with cash when you pick up your order at the store (Over the Counter).
                        </p>
                    </div>
                    
                    <div class="flex gap-4">
                <a href="../index.php" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 text-center">
                    Cancel
                </a>
                        <button type="submit" name="place_order" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                            Place Order
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Store Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-bold mb-4">Store Information</h2>
                        <?php
                    // Get main store location (DANICOP HARDWARE) - try to find by coordinates or name
                    $mainStoreLat = 9.797538;
                    $mainStoreLng = 123.792802;
                    $storeLocation = $conn->query("SELECT * FROM store_locations WHERE ((ABS(latitude - $mainStoreLat) < 0.0001 AND ABS(longitude - $mainStoreLng) < 0.0001) OR name LIKE '%DANICOP%' OR name LIKE '%Main%') AND is_active = 1 ORDER BY name ASC LIMIT 1")->fetch_assoc();
                    
                    // If not found, use default values
                    if (!$storeLocation) {
                        $storeLocation = [
                            'name' => 'DANICOP HARDWARE & CONSTRUCTION SUPPLY',
                            'address' => 'Loon, Bohol, Philippines',
                            'phone' => '(038) 501-1234',
                            'hours' => "Mon-Sat: 8:00 AM - 6:00 PM\nSun: 9:00 AM - 4:00 PM",
                            'latitude' => $mainStoreLat,
                            'longitude' => $mainStoreLng
                        ];
                    }
                    
                    // Ensure coordinates are set
                    if (empty($storeLocation['latitude']) || empty($storeLocation['longitude'])) {
                        $storeLocation['latitude'] = $mainStoreLat;
                        $storeLocation['longitude'] = $mainStoreLng;
                    }
                    ?>
                    <div class="space-y-4 text-gray-700">
                        <div>
                            <p class="mb-3">
                                <strong class="text-lg text-gray-900"><?= htmlspecialchars($storeLocation['name'] ?? 'DANICOP HARDWARE & CONSTRUCTION SUPPLY') ?></strong>
                            </p>
                        </div>
                        <div>
                            <p class="flex items-start mb-2">
                                <i class="fas fa-map-marker-alt text-blue-600 mt-1 mr-2"></i>
                                <span><strong>Address:</strong><br>
                                <?= nl2br(htmlspecialchars($storeLocation['address'])) ?></span>
                            </p>
                        </div>
                        <?php if (!empty($storeLocation['phone'])): ?>
                        <div>
                            <p class="flex items-start mb-2">
                                <i class="fas fa-phone text-blue-600 mt-1 mr-2"></i>
                                <span><strong>Phone:</strong><br>
                                <?= htmlspecialchars($storeLocation['phone']) ?></span>
                            </p>
                        </div>
                            <?php endif; ?>
                        <?php if (!empty($storeLocation['hours'])): ?>
                        <div>
                            <p class="flex items-start mb-2">
                                <i class="fas fa-clock text-blue-600 mt-1 mr-2"></i>
                                <span><strong>Hours:</strong><br>
                                <?= nl2br(htmlspecialchars($storeLocation['hours'])) ?></span>
                            </p>
                        </div>
                            <?php endif; ?>
                        
                        <!-- Store Map -->
                        <div class="mt-4">
                            <div id="store_map" style="height: 250px; width: 100%; border-radius: 8px; overflow: hidden; border: 2px solid #e5e7eb;"></div>
                            <p class="text-xs text-gray-500 mt-2 text-center">
                                <i class="fas fa-map-marker-alt"></i> Our store location
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Checkout Modal -->
        <div id="checkoutModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeCheckoutModal()">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
                <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
                    <h2 class="text-2xl font-bold text-gray-800">Delivery & Payment Information</h2>
                    <button onclick="closeCheckoutModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <!-- Order Summary in Modal -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Selected Items</h3>
                        <div id="order-summary-modal" class="space-y-3 max-h-64 overflow-y-auto mb-4">
                            <p class="text-gray-500 text-center py-4">Loading cart...</p>
                        </div>
                        <div class="pt-3 border-t border-gray-300">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span id="order-total-modal">₱0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="checkout.php" id="checkout-form-modal" onsubmit="event.preventDefault(); submitCheckoutForm(); return false;">
                        <input type="hidden" name="cart" id="cart-data-modal" value='[]'>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">Delivery Method</label>
                            <select name="delivery_method" id="delivery_method_modal" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                    onchange="toggleDeliveryAddressModal()">
                                <option value="pickup">Pickup at Store</option>
                                <option value="delivery">Home Delivery</option>
                            </select>
                        </div>
                        
                        <div id="delivery_address_field_modal" class="mb-6 hidden">
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-lg p-4 mb-4">
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-green-600 text-xl mt-1 mr-3"></i>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-800 mb-1">Delivery Address</h3>
                                        <p class="text-sm text-gray-600">Set your delivery location to calculate delivery fee</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 font-semibold mb-2">
                                    <i class="fas fa-home mr-2 text-green-600"></i>Address
                                </label>
                                <div class="flex flex-col sm:flex-row gap-3 mb-3">
                                    <textarea name="delivery_address" id="delivery_address_modal" rows="3"
                                              class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all resize-none shadow-sm"
                                              placeholder="Enter your complete delivery address or click on the map to set location"></textarea>
                                    <button type="button" id="use_current_location_modal" 
                                            class="px-5 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl hover:from-green-700 hover:to-emerald-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 font-semibold whitespace-nowrap flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition-all transform hover:scale-105"
                                            onclick="useCurrentLocationModal()">
                                        <i class="fas fa-location-arrow text-lg"></i>
                                        <span class="hidden sm:inline">Use My Current Location</span>
                                        <span class="sm:hidden">Current Location</span>
                                    </button>
                                </div>
                                <div class="flex items-start gap-2 text-xs text-gray-500 bg-blue-50 rounded-lg p-2">
                                    <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                                    <span>Type an address, click on the map, or use your current location to set your delivery location</span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 font-semibold mb-2">
                                    <i class="fas fa-map mr-2 text-green-600"></i>Select Location on Map
                                </label>
                                <div class="relative rounded-xl overflow-hidden shadow-lg border-2 border-gray-200">
                                    <div id="delivery_map_modal" style="height: 400px; width: 100%;"></div>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-500 mt-2 bg-gray-50 rounded-lg p-2">
                                    <i class="fas fa-hand-pointer text-gray-400"></i>
                                    <span>Click on the map or use "Use My Current Location" to set your exact delivery location</span>
                                </div>
                            </div>
                            
                            <div id="delivery_info_modal" class="hidden bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-4 mb-4 shadow-sm">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-route text-green-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-600">Distance</p>
                                            <p class="text-lg font-bold text-green-700"><span id="delivery_distance_modal">0</span> km</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-peso-sign text-emerald-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-600">Delivery Fee</p>
                                            <p class="text-lg font-bold text-emerald-700">₱<span id="delivery_fee_modal">0.00</span>
                                                <span id="free_delivery_note" class="text-xs text-green-600 ml-1 hidden">(Free)</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="delivery_latitude" id="delivery_latitude_modal" value="" required>
                            <input type="hidden" name="delivery_longitude" id="delivery_longitude_modal" value="" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-gray-700 font-bold mb-2">Contact Number</label>
                            <input type="tel" name="contact_number" id="contact_number_modal" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                   placeholder="09XX XXX XXXX">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-bold mb-2">Payment Method</label>
                            <select name="payment_method" id="payment_method_modal" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                                    onchange="updatePaymentMethodModal()">
                                <option value="cash_pickup">Over the Counter</option>
                                <option value="cash_delivery">Cash on Delivery</option>
                            </select>
                        </div>
                        
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <p class="text-sm text-green-800" id="payment_info_modal">
                                <i class="fas fa-info-circle"></i> You will pay with cash when you pick up your order at the store (Over the Counter).
                            </p>
                        </div>
                        
                        <!-- Order Total Summary -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-6 mb-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fas fa-receipt text-green-600"></i>
                                Order Summary
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700 font-medium">Subtotal:</span>
                                    <span class="text-gray-800 font-semibold" id="subtotal_modal">₱0.00</span>
                                </div>
                                <div class="flex justify-between items-center" id="delivery_fee_row_modal" style="display: none;">
                                    <span class="text-gray-700 font-medium">Delivery Fee:</span>
                                    <span class="text-gray-800 font-semibold">₱<span id="delivery_fee_total_modal">0.00</span></span>
                                </div>
                                <div class="border-t-2 border-gray-300 pt-3 mt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xl font-bold text-gray-900">Total:</span>
                                        <span class="text-2xl font-bold text-green-600" id="grand_total_modal">₱0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-4">
                            <button type="button" onclick="closeCheckoutModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="button" onclick="submitCheckoutForm()" class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">
                                <i class="fas fa-check mr-2"></i> Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    <!-- Leaflet JS (OpenStreetMap) -->
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
    <!-- Leaflet Routing Machine -->
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let deliveryMap;
        let deliveryMarker;
        let storeMarker;
        let routingControl;
        
        // Modal map variables
        let deliveryMapModal;
        let deliveryMarkerModal;
        let storeMarkerModal;
        let routingControlModal;
        
        // Store location: DANICOP HARDWARE & CONSTRUCTION SUPPLY
        const STORE_LAT = 9.797538;
        const STORE_LNG = 123.792802;
        const FEE_PER_KM = 10; // PHP 10 per km

        function initDeliveryMap() {
            const mapDiv = document.getElementById('delivery_map');
            if (!mapDiv) return;

            const latInput = document.getElementById('delivery_latitude');
            const lngInput = document.getElementById('delivery_longitude');
            const addressInput = document.getElementById('delivery_address');

            const existingLat = parseFloat(latInput.value);
            const existingLng = parseFloat(lngInput.value);

            // Default to store location
            const defaultLat = !isNaN(existingLat) ? existingLat : STORE_LAT;
            const defaultLng = !isNaN(existingLng) ? existingLng : STORE_LNG;

            // If map already exists, just invalidate size and return
            if (deliveryMap) {
                deliveryMap.invalidateSize();
                return;
            }

            deliveryMap = L.map('delivery_map').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(deliveryMap);

            // Add store marker
            storeMarker = L.marker([STORE_LAT, STORE_LNG], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34]
                })
            }).addTo(deliveryMap);
            storeMarker.bindPopup('<b>DANICOP HARDWARE & CONSTRUCTION SUPPLY</b><br>Store Location').openPopup();

            function setMarker(lat, lng) {
                if (deliveryMarker) {
                    deliveryMarker.setLatLng([lat, lng]);
                } else {
                    deliveryMarker = L.marker([lat, lng], { draggable: true }).addTo(deliveryMap);

                    deliveryMarker.on('dragend', function (e) {
                        const pos = e.target.getLatLng();
                        latInput.value = pos.lat.toFixed(8);
                        lngInput.value = pos.lng.toFixed(8);
                        reverseGeocode(pos.lat, pos.lng);
                        calculateRouteAndFee(pos.lat, pos.lng);
                    });
                }
                
                // Calculate route and fee when marker is set
                calculateRouteAndFee(lat, lng);
            }

            // If we already have coordinates (e.g. after validation error), show marker
            if (!isNaN(existingLat) && !isNaN(existingLng)) {
                setMarker(existingLat, existingLng);
            }

            // Click on map to set location
            deliveryMap.on('click', function (e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
                setMarker(lat, lng);
                reverseGeocode(lat, lng);
            });
        }
        
        function calculateRouteAndFee(customerLat, customerLng) {
            if (!deliveryMap) return;
            
            // Remove existing route if any
            if (routingControl) {
                deliveryMap.removeControl(routingControl);
            }
            
            // Create route from store to customer
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(STORE_LAT, STORE_LNG),
                    L.latLng(customerLat, customerLng)
                ],
                routeWhileDragging: false,
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1'
                }),
                createMarker: function() { return null; } // Don't create default markers
            }).addTo(deliveryMap);
            
            // Calculate distance and fee
            routingControl.on('routesfound', function(e) {
                const routes = e.routes;
                if (routes && routes.length > 0) {
                    const distance = routes[0].summary.totalDistance / 1000; // Convert to km
                    const distanceKm = distance.toFixed(2);
                    // Only charge if distance is 1km or more (if less than 1km, no fee)
                    let fee = 0;
                    let showFreeNote = false;
                    if (distance >= 1.0) {
                        fee = distance * FEE_PER_KM; // Charge exactly per km
                    } else {
                        fee = 0; // No fee if less than 1km
                        showFreeNote = true;
                    }
                    
                    // Update UI (for both regular and modal)
                    const distanceEl = document.getElementById('delivery_distance') || document.getElementById('delivery_distance_modal');
                    const feeEl = document.getElementById('delivery_fee') || document.getElementById('delivery_fee_modal');
                    const infoEl = document.getElementById('delivery_info') || document.getElementById('delivery_info_modal');
                    const freeNoteEl = document.getElementById('free_delivery_note');
                    
                    if (distanceEl) distanceEl.textContent = distanceKm;
                    if (feeEl) feeEl.textContent = fee.toFixed(2);
                    if (infoEl) infoEl.classList.remove('hidden');
                    if (freeNoteEl) {
                        if (showFreeNote) {
                            freeNoteEl.classList.remove('hidden');
                        } else {
                            freeNoteEl.classList.add('hidden');
                        }
                    }
                    
                    // Update cart total
                    updateCartTotal(fee);
                }
            });
        }
        
        function updateCartTotal(deliveryFee) {
            // Update the order total display with delivery fee
            const cart = JSON.parse(document.getElementById('cart-data').value || '[]');
            let subtotal = 0;
            cart.forEach(item => {
                subtotal += item.price * item.quantity;
            });
            const total = subtotal + deliveryFee;
            document.getElementById('order-total').textContent = '₱' + total.toFixed(2);
        }

        function useCurrentLocation() {
            const useLocationBtn = document.getElementById('use_current_location');
            const latInput = document.getElementById('delivery_latitude');
            const lngInput = document.getElementById('delivery_longitude');
            const addressInput = document.getElementById('delivery_address');
            
            // Check if geolocation is supported
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser. Please use the map to set your location.');
                return;
            }
            
            // Disable button and show loading state
            const originalHTML = useLocationBtn.innerHTML;
            useLocationBtn.disabled = true;
            useLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span class="hidden sm:inline">Getting location...</span><span class="sm:hidden">Loading...</span>';
            useLocationBtn.classList.add('opacity-50', 'cursor-not-allowed');
            
            // Get current position
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    // Update hidden inputs
                    latInput.value = lat.toFixed(8);
                    lngInput.value = lng.toFixed(8);
                    
                    // Initialize map if not already initialized
                    if (!deliveryMap) {
                        initDeliveryMap();
                        // Wait a bit for map to initialize
                        setTimeout(() => {
                            updateMapWithLocation(lat, lng);
                        }, 500);
                    } else {
                        updateMapWithLocation(lat, lng);
                    }
                    
                    // Reverse geocode to get address
                    reverseGeocodeWithDetails(lat, lng);
                    
                    // Restore button
                    useLocationBtn.disabled = false;
                    useLocationBtn.innerHTML = originalHTML;
                    useLocationBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                },
                function(error) {
                    // Handle errors
                    let errorMessage = 'Unable to retrieve your location. ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Please allow location access in your browser settings.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Location information is unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Location request timed out.';
                            break;
                        default:
                            errorMessage += 'An unknown error occurred.';
                            break;
                    }
                    alert(errorMessage);
                    
                    // Restore button
                    useLocationBtn.disabled = false;
                    useLocationBtn.innerHTML = originalHTML;
                    useLocationBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }
        
        function updateMapWithLocation(lat, lng) {
            if (!deliveryMap) return;
            
            // Set map view to show both store and customer location
            const bounds = L.latLngBounds(
                [STORE_LAT, STORE_LNG],
                [lat, lng]
            );
            deliveryMap.fitBounds(bounds, { padding: [50, 50] });
            
            // Set marker
            const latInput = document.getElementById('delivery_latitude');
            const lngInput = document.getElementById('delivery_longitude');
            
            if (deliveryMarker) {
                deliveryMarker.setLatLng([lat, lng]);
            } else {
                deliveryMarker = L.marker([lat, lng], { draggable: true }).addTo(deliveryMap);
                
                deliveryMarker.on('dragend', function (e) {
                    const pos = e.target.getLatLng();
                    latInput.value = pos.lat.toFixed(8);
                    lngInput.value = pos.lng.toFixed(8);
                    reverseGeocode(pos.lat, pos.lng);
                    calculateRouteAndFee(pos.lat, pos.lng);
                });
            }
            
            // Calculate route and fee
            calculateRouteAndFee(lat, lng);
            }

            function reverseGeocode(lat, lng) {
            reverseGeocodeWithDetails(lat, lng);
        }
        
        // Modal-specific functions
        function initDeliveryMapModal() {
            const mapDiv = document.getElementById('delivery_map_modal');
            if (!mapDiv) return;

            const latInput = document.getElementById('delivery_latitude_modal');
            const lngInput = document.getElementById('delivery_longitude_modal');
            const addressInput = document.getElementById('delivery_address_modal');

            const existingLat = parseFloat(latInput.value);
            const existingLng = parseFloat(lngInput.value);

            const defaultLat = !isNaN(existingLat) ? existingLat : STORE_LAT;
            const defaultLng = !isNaN(existingLng) ? existingLng : STORE_LNG;

            if (deliveryMapModal) {
                deliveryMapModal.invalidateSize();
                return;
            }

            deliveryMapModal = L.map('delivery_map_modal').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(deliveryMapModal);

            // Add store marker
            storeMarkerModal = L.marker([STORE_LAT, STORE_LNG], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34]
                })
            }).addTo(deliveryMapModal);
            storeMarkerModal.bindPopup('<b>DANICOP HARDWARE & CONSTRUCTION SUPPLY</b><br>Store Location').openPopup();

            function setMarker(lat, lng) {
                if (deliveryMarkerModal) {
                    deliveryMarkerModal.setLatLng([lat, lng]);
                } else {
                    deliveryMarkerModal = L.marker([lat, lng], { draggable: true }).addTo(deliveryMapModal);

                    deliveryMarkerModal.on('dragend', function (e) {
                        const pos = e.target.getLatLng();
                        latInput.value = pos.lat.toFixed(8);
                        lngInput.value = pos.lng.toFixed(8);
                        reverseGeocodeWithDetailsModal(pos.lat, pos.lng);
                        calculateRouteAndFeeModal(pos.lat, pos.lng);
                    });
                }
                
                calculateRouteAndFeeModal(lat, lng);
            }

            if (!isNaN(existingLat) && !isNaN(existingLng)) {
                setMarker(existingLat, existingLng);
            }

            deliveryMapModal.on('click', function (e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
                setMarker(lat, lng);
                reverseGeocodeWithDetailsModal(lat, lng);
            });
        }
        
        function calculateRouteAndFeeModal(customerLat, customerLng) {
            if (!deliveryMapModal) return;
            
            if (routingControlModal) {
                deliveryMapModal.removeControl(routingControlModal);
            }
            
            routingControlModal = L.Routing.control({
                waypoints: [
                    L.latLng(STORE_LAT, STORE_LNG),
                    L.latLng(customerLat, customerLng)
                ],
                routeWhileDragging: false,
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1'
                }),
                createMarker: function() { return null; }
            }).addTo(deliveryMapModal);
            
            routingControlModal.on('routesfound', function(e) {
                const routes = e.routes;
                if (routes && routes.length > 0) {
                    const distance = routes[0].summary.totalDistance / 1000;
                    const distanceKm = distance.toFixed(2);
                    let fee = 0;
                    let showFreeNote = false;
                    if (distance >= 1.0) {
                        fee = distance * FEE_PER_KM;
                    } else {
                        fee = 0;
                        showFreeNote = true;
                    }
                    
                    document.getElementById('delivery_distance_modal').textContent = distanceKm;
                    document.getElementById('delivery_fee_modal').textContent = fee.toFixed(2);
                    document.getElementById('delivery_info_modal').classList.remove('hidden');
                    const freeNoteEl = document.getElementById('free_delivery_note');
                    if (freeNoteEl) {
                        if (showFreeNote) {
                            freeNoteEl.classList.remove('hidden');
                        } else {
                            freeNoteEl.classList.add('hidden');
                        }
                    }
                    
                    // Update grand total
                    updateModalTotal(fee);
                }
            });
        }
        
        function useCurrentLocationModal() {
            const useLocationBtn = document.getElementById('use_current_location_modal');
            const latInput = document.getElementById('delivery_latitude_modal');
            const lngInput = document.getElementById('delivery_longitude_modal');
            const addressInput = document.getElementById('delivery_address_modal');
            
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser. Please use the map to set your location.');
                return;
            }
            
            const originalHTML = useLocationBtn.innerHTML;
            useLocationBtn.disabled = true;
            useLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span class="hidden sm:inline">Getting location...</span><span class="sm:hidden">Loading...</span>';
            useLocationBtn.classList.add('opacity-50', 'cursor-not-allowed');
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    latInput.value = lat.toFixed(8);
                    lngInput.value = lng.toFixed(8);
                    
                    if (!deliveryMapModal) {
                        initDeliveryMapModal();
                        setTimeout(() => {
                            updateMapWithLocationModal(lat, lng);
                        }, 500);
                    } else {
                        updateMapWithLocationModal(lat, lng);
                    }
                    
                    reverseGeocodeWithDetailsModal(lat, lng);
                    
                    useLocationBtn.disabled = false;
                    useLocationBtn.innerHTML = originalHTML;
                    useLocationBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                },
                function(error) {
                    let errorMessage = 'Unable to retrieve your location. ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Please allow location access in your browser settings.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Location information is unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Location request timed out.';
                            break;
                        default:
                            errorMessage += 'An unknown error occurred.';
                            break;
                    }
                    alert(errorMessage);
                    
                    useLocationBtn.disabled = false;
                    useLocationBtn.innerHTML = originalHTML;
                    useLocationBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }
        
        function updateMapWithLocationModal(lat, lng) {
            if (!deliveryMapModal) return;
            
            const bounds = L.latLngBounds(
                [STORE_LAT, STORE_LNG],
                [lat, lng]
            );
            deliveryMapModal.fitBounds(bounds, { padding: [50, 50] });
            
            const latInput = document.getElementById('delivery_latitude_modal');
            const lngInput = document.getElementById('delivery_longitude_modal');
            
            if (deliveryMarkerModal) {
                deliveryMarkerModal.setLatLng([lat, lng]);
            } else {
                deliveryMarkerModal = L.marker([lat, lng], { draggable: true }).addTo(deliveryMapModal);
                
                deliveryMarkerModal.on('dragend', function (e) {
                    const pos = e.target.getLatLng();
                    latInput.value = pos.lat.toFixed(8);
                    lngInput.value = pos.lng.toFixed(8);
                    reverseGeocodeWithDetailsModal(pos.lat, pos.lng);
                    calculateRouteAndFeeModal(pos.lat, pos.lng);
                });
            }
            
            calculateRouteAndFeeModal(lat, lng);
        }
        
        function reverseGeocodeWithDetailsModal(lat, lng) {
            const addressInput = document.getElementById('delivery_address_modal');
            if (!addressInput) return;
            
            addressInput.value = 'Getting address...';
            addressInput.disabled = true;

            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1&zoom=18`, {
                headers: {
                    'User-Agent': 'DanicopHardware/1.0'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.address) {
                        const addr = data.address;
                        let fullAddress = [];
                        
                        if (addr.house_number) {
                            fullAddress.push(addr.house_number);
                        }
                        if (addr.road || addr.street || addr.pedestrian) {
                            fullAddress.push(addr.road || addr.street || addr.pedestrian);
                        }
                        if (addr.neighbourhood || addr.suburb) {
                            fullAddress.push(addr.neighbourhood || addr.suburb);
                        }
                        if (addr.village || addr.town || addr.city || addr.municipality) {
                            fullAddress.push(addr.village || addr.town || addr.city || addr.municipality);
                        }
                        if (addr.state || addr.region || addr.province) {
                            fullAddress.push(addr.state || addr.region || addr.province);
                        }
                        if (addr.postcode) {
                            fullAddress.push(addr.postcode);
                        }
                        if (addr.country) {
                            fullAddress.push(addr.country);
                        }
                        
                        if (fullAddress.length > 0) {
                            addressInput.value = fullAddress.join(', ');
                        } else if (data.display_name) {
                            addressInput.value = data.display_name;
                        } else {
                            addressInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)} (Please enter your complete address manually)`;
                        }
                    } else if (data && data.display_name) {
                        addressInput.value = data.display_name;
                    } else {
                        addressInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)} (Please enter your complete address manually)`;
                    }
                    addressInput.disabled = false;
                })
                .catch(error => {
                    console.warn('Reverse geocoding failed:', error);
                    addressInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)} (Please enter your complete address manually)`;
                    addressInput.disabled = false;
                });
        }
        
        function reverseGeocodeWithDetails(lat, lng) {
            const addressInput = document.getElementById('delivery_address');
            if (!addressInput) return;
            
            // Show loading state
            addressInput.value = 'Getting address...';
            addressInput.disabled = true;

            // Nominatim requires a User-Agent header
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&addressdetails=1&zoom=18`, {
                headers: {
                    'User-Agent': 'DanicopHardware/1.0'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.address) {
                        // Build complete address from address components
                        const addr = data.address;
                        let fullAddress = [];
                        
                        // Build address in order: house number, road, village/town/city, state, postcode, country
                        if (addr.house_number) {
                            fullAddress.push(addr.house_number);
                        }
                        if (addr.road || addr.street || addr.pedestrian) {
                            fullAddress.push(addr.road || addr.street || addr.pedestrian);
                        }
                        if (addr.neighbourhood || addr.suburb) {
                            fullAddress.push(addr.neighbourhood || addr.suburb);
                        }
                        if (addr.village || addr.town || addr.city || addr.municipality) {
                            fullAddress.push(addr.village || addr.town || addr.city || addr.municipality);
                        }
                        if (addr.state || addr.region || addr.province) {
                            fullAddress.push(addr.state || addr.region || addr.province);
                        }
                        if (addr.postcode) {
                            fullAddress.push(addr.postcode);
                        }
                        if (addr.country) {
                            fullAddress.push(addr.country);
                        }
                        
                        // If we have a complete address, use it; otherwise fall back to display_name
                        if (fullAddress.length > 0) {
                            addressInput.value = fullAddress.join(', ');
                        } else if (data.display_name) {
                            // Use display_name as fallback
                            addressInput.value = data.display_name;
                        } else {
                            // Last resort: use coordinates with a message
                            addressInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)} (Please enter your complete address manually)`;
                        }
                    } else if (data && data.display_name) {
                        // Fallback to display_name if address components not available
                        addressInput.value = data.display_name;
                    } else {
                        // If no address data, show coordinates with instruction
                        addressInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)} (Please enter your complete address manually)`;
                    }
                    addressInput.disabled = false;
                })
                .catch(error => {
                    console.warn('Reverse geocoding failed:', error);
                    // On error, show coordinates with instruction to enter address manually
                    addressInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)} (Please enter your complete address manually)`;
                    addressInput.disabled = false;
                });
        }
        
        // Modal functions
        function openCheckoutModal() {
            // Check if any items are selected
            const selectedItems = cart.filter(item => item.selected !== false);
            if (selectedItems.length === 0) {
                alert('Please select at least one item to checkout.');
                return;
            }
            
            document.getElementById('checkoutModal').classList.remove('hidden');
            document.getElementById('checkoutModal').classList.add('flex');
            // Copy selected cart data to modal form
            updateCartData(); // Ensure cart-data is updated with selected items
            // Use the already filtered selectedItems
            const cartData = JSON.stringify(selectedItems);
            document.getElementById('cart-data').value = cartData;
            document.getElementById('cart-data-modal').value = cartData;
            // Load order summary in modal
            loadCartSummaryModal();
            // Update modal total (will be updated when delivery fee is calculated)
            setTimeout(() => {
                updateModalTotal(0);
            }, 100);
            // Initialize map in modal if delivery is selected
            setTimeout(() => {
                if (document.getElementById('delivery_method_modal').value === 'delivery') {
                    if (!deliveryMapModal) {
                        initDeliveryMapModal();
                    } else {
                        deliveryMapModal.invalidateSize();
                    }
                }
            }, 300);
        }
        
        function loadCartSummaryModal() {
            // Get only selected items from cart-data (which should already be filtered)
            const selectedCart = JSON.parse(document.getElementById('cart-data').value || '[]');
            const summaryDiv = document.getElementById('order-summary-modal');
            const totalSpan = document.getElementById('order-total-modal');
            
            if (!summaryDiv || !totalSpan) return;
            
            if (selectedCart.length === 0) {
                summaryDiv.innerHTML = '<p class="text-gray-500 text-center py-4">No items selected. Please select items from the Order Summary.</p>';
                totalSpan.textContent = '₱0.00';
                updateModalTotal(0);
                return;
            }
            
            let total = 0;
            summaryDiv.innerHTML = selectedCart.map(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                const imagePath = item.image ? `../uploads/${item.image}` : '../uploads/default-product.png';
                return `
                    <div class="flex items-center gap-4 border-b pb-4">
                        <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            <img src="${imagePath}" alt="${item.name}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.src='../uploads/default-product.png'; this.onerror=null;">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-800 truncate">${item.name}</h3>
                            <p class="text-sm text-gray-600">Quantity: ${item.quantity} × ₱${item.price.toFixed(2)}</p>
                        </div>
                        <span class="font-bold text-gray-800">₱${itemTotal.toFixed(2)}</span>
                    </div>
                `;
            }).join('');
            
            totalSpan.textContent = '₱' + total.toFixed(2);
            
            // Update modal total section
            updateModalTotal(0);
        }
        
        function updateModalTotal(deliveryFee) {
            // Use cart-data-modal which contains only selected items
            const cartDataInput = document.getElementById('cart-data-modal');
            if (!cartDataInput) return;
            
            const selectedCart = JSON.parse(cartDataInput.value || '[]');
            let subtotal = 0;
            selectedCart.forEach(item => {
                subtotal += item.price * item.quantity;
            });
            
            // Get delivery method
            const deliveryMethod = document.getElementById('delivery_method_modal')?.value || 'pickup';
            
            // Update subtotal
            const subtotalEl = document.getElementById('subtotal_modal');
            if (subtotalEl) {
                subtotalEl.textContent = '₱' + subtotal.toFixed(2);
            }
            
            // Update delivery fee row visibility
            const deliveryFeeRow = document.getElementById('delivery_fee_row_modal');
            
            if (deliveryMethod === 'delivery' && deliveryFee > 0) {
                if (deliveryFeeRow) {
                    deliveryFeeRow.style.display = 'flex';
                }
                const deliveryFeeTotalEl = document.getElementById('delivery_fee_total_modal');
                if (deliveryFeeTotalEl) {
                    deliveryFeeTotalEl.textContent = deliveryFee.toFixed(2);
                }
            } else {
                // Hide delivery fee for pickup (Over the Counter)
                if (deliveryFeeRow) {
                    deliveryFeeRow.style.display = 'none';
                }
                deliveryFee = 0;
            }
            
            // Calculate and update grand total
            // For pickup (Over the Counter), grand total = subtotal only
            // For delivery, grand total = subtotal + delivery fee
            const grandTotal = subtotal + deliveryFee;
            const grandTotalEl = document.getElementById('grand_total_modal');
            if (grandTotalEl) {
                grandTotalEl.textContent = '₱' + grandTotal.toFixed(2);
            }
        }
        
        function closeCheckoutModal() {
            document.getElementById('checkoutModal').classList.add('hidden');
            document.getElementById('checkoutModal').classList.remove('flex');
        }
        
        function toggleDeliveryAddressModal() {
            const method = document.getElementById('delivery_method_modal').value;
            const addressField = document.getElementById('delivery_address_field_modal');
            if (method === 'delivery') {
                addressField.classList.remove('hidden');
                const textarea = addressField.querySelector('textarea');
                if (textarea) textarea.required = true;
                // Initialize map if not already initialized
                setTimeout(() => {
                    if (!deliveryMapModal) {
                        initDeliveryMapModal();
                    } else {
                        // Invalidate size to ensure map displays correctly
                        deliveryMapModal.invalidateSize();
                    }
                }, 300);
            } else {
                addressField.classList.add('hidden');
                const textarea = addressField.querySelector('textarea');
                if (textarea) textarea.required = false;
                // Reset delivery fee when switching to pickup
                updateModalTotal(0);
            }
            updatePaymentMethodModal();
        }
        
        function updatePaymentMethodModal() {
            const deliveryMethod = document.getElementById('delivery_method_modal').value;
            const paymentMethod = document.getElementById('payment_method_modal').value;
            const paymentInfo = document.getElementById('payment_info_modal');
            
            if (deliveryMethod === 'pickup') {
                if (paymentMethod === 'cash_pickup') {
                    paymentInfo.innerHTML = '<i class="fas fa-info-circle"></i> You will pay with cash when you pick up your order at the store (Over the Counter).';
                }
            } else {
                if (paymentMethod === 'cash_delivery') {
                    paymentInfo.innerHTML = '<i class="fas fa-info-circle"></i> You will pay with cash when your order is delivered.';
                }
            }
        }
        
        function validateDeliveryLocation(event) {
            const deliveryMethod = document.getElementById('delivery_method_modal')?.value || document.getElementById('delivery_method').value;
            const latInput = document.getElementById('delivery_latitude_modal') || document.getElementById('delivery_latitude');
            const lngInput = document.getElementById('delivery_longitude_modal') || document.getElementById('delivery_longitude');
            
            if (deliveryMethod === 'delivery') {
                if (!latInput || !latInput.value || !lngInput || !lngInput.value) {
                    event.preventDefault();
                    alert('Please set your delivery location on the map or click "Use My Current Location" button before placing your order.');
                    return false;
                }
            }
            return true;
        }
        
        // Load cart from localStorage or sessionStorage (if redirected from login)
        let cart = JSON.parse(localStorage.getItem('cart')) || JSON.parse(sessionStorage.getItem('cart')) || [];
        // Clear sessionStorage if it exists
        if (sessionStorage.getItem('cart')) {
            sessionStorage.removeItem('cart');
            localStorage.setItem('cart', JSON.stringify(cart));
        }
        
        // Ensure all items have selected property (default to true if not set)
        cart.forEach(item => {
            if (item.selected === undefined) {
                item.selected = true;
            }
        });
        localStorage.setItem('cart', JSON.stringify(cart));
        
        function loadCartSummary() {
            const summaryDiv = document.getElementById('order-summary');
            const totalSpan = document.getElementById('order-total');
            const cartDataInput = document.getElementById('cart-data');
            
            if (cart.length === 0) {
                summaryDiv.innerHTML = '<p class="text-gray-500 text-center py-8">Your cart is empty. <a href="../index.php" class="text-blue-600 hover:underline">Go shopping</a></p>';
                totalSpan.textContent = '₱0.00';
                cartDataInput.value = '[]';
                document.getElementById('checkout-form').style.display = 'none';
                return;
            }
            
            let total = 0;
            summaryDiv.innerHTML = cart.map((item, index) => {
                const itemTotal = item.price * item.quantity;
                const imagePath = item.image ? `../uploads/${item.image}` : '../uploads/default-product.png';
                const isChecked = item.selected !== false; // Default to true (checked)
                // Only add to total if item is selected
                if (isChecked) {
                    total += itemTotal;
                }
                return `
                    <div class="flex items-center gap-4 border-b pb-4">
                        <input type="checkbox" 
                               class="item-checkbox w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 flex-shrink-0" 
                               data-index="${index}"
                               ${isChecked ? 'checked' : ''}
                               onchange="updateCartSelection(${index}, this.checked)">
                        <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            <img src="${imagePath}" alt="${item.name}" 
                                 class="w-full h-full object-cover"
                                 onerror="this.src='../uploads/default-product.png'; this.onerror=null;">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-800 truncate">${item.name}</h3>
                            <p class="text-sm text-gray-600 mb-2">₱${item.price.toFixed(2)} each</p>
                            <div class="flex items-center space-x-2">
                                <label class="text-xs text-gray-600">Quantity:</label>
                                <button type="button" 
                                        onclick="updateCheckoutQuantity(${index}, -1)"
                                        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                        ${item.quantity <= 1 ? 'disabled' : ''}>
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <input type="number" 
                                       id="qty-checkout-${index}"
                                       value="${item.quantity}" 
                                       min="1" 
                                       max="${item.stock}"
                                       class="w-16 h-8 text-center border-2 border-gray-300 rounded font-semibold focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                                       onchange="updateCheckoutQuantityInput(${index}, this.value)"
                                       oninput="updateCheckoutQuantityInput(${index}, this.value)">
                                <button type="button" 
                                        onclick="updateCheckoutQuantity(${index}, 1)"
                                        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                        ${item.quantity >= item.stock ? 'disabled' : ''}>
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                                <span class="text-xs text-gray-500 ml-2">(Stock: ${item.stock})</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-gray-800 block">₱${itemTotal.toFixed(2)}</span>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Update total display with calculated total (only selected items)
            if (totalSpan) {
                totalSpan.textContent = '₱' + total.toFixed(2);
            }
            
            updateCartData();
            updateSelectAllCheckbox();
        }
        
        function updateCartSelection(index, isSelected) {
            if (cart[index]) {
                cart[index].selected = isSelected;
                localStorage.setItem('cart', JSON.stringify(cart));
                updateTotal();
                updateCartData();
                updateSelectAllCheckbox();
            }
        }
        
        function toggleSelectAll(selectAll) {
            cart.forEach((item, index) => {
                item.selected = selectAll;
                const checkbox = document.querySelector(`.item-checkbox[data-index="${index}"]`);
                if (checkbox) {
                    checkbox.checked = selectAll;
                }
            });
            localStorage.setItem('cart', JSON.stringify(cart));
            updateTotal();
            updateCartData();
        }
        
        function updateCheckoutQuantity(index, change) {
            if (!cart[index]) return;
            
            const newQuantity = cart[index].quantity + change;
            
            if (newQuantity < 1) {
                return;
            }
            
            if (newQuantity > cart[index].stock) {
                alert('Quantity cannot exceed available stock (' + cart[index].stock + ')');
                return;
            }
            
            cart[index].quantity = newQuantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Update the input field
            const qtyInput = document.getElementById('qty-checkout-' + index);
            if (qtyInput) {
                qtyInput.value = newQuantity;
            }
            
            // Update buttons disabled state
            const minusBtn = qtyInput?.parentElement?.querySelector('button[onclick*="updateCheckoutQuantity(' + index + ', -1)"]');
            const plusBtn = qtyInput?.parentElement?.querySelector('button[onclick*="updateCheckoutQuantity(' + index + ', 1)"]');
            if (minusBtn) minusBtn.disabled = (newQuantity <= 1);
            if (plusBtn) plusBtn.disabled = (newQuantity >= cart[index].stock);
            
            // Reload summary to update totals
            loadCartSummary();
        }
        
        function updateCheckoutQuantityInput(index, value) {
            if (!cart[index]) return;
            
            const newQuantity = parseInt(value) || 1;
            
            if (newQuantity < 1) {
                document.getElementById('qty-checkout-' + index).value = 1;
                cart[index].quantity = 1;
            } else if (newQuantity > cart[index].stock) {
                alert('Quantity cannot exceed available stock (' + cart[index].stock + ')');
                document.getElementById('qty-checkout-' + index).value = cart[index].stock;
                cart[index].quantity = cart[index].stock;
            } else {
                cart[index].quantity = newQuantity;
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Update buttons disabled state
            const qtyInput = document.getElementById('qty-checkout-' + index);
            const minusBtn = qtyInput?.parentElement?.querySelector('button[onclick*="updateCheckoutQuantity(' + index + ', -1)"]');
            const plusBtn = qtyInput?.parentElement?.querySelector('button[onclick*="updateCheckoutQuantity(' + index + ', 1)"]');
            if (minusBtn) minusBtn.disabled = (cart[index].quantity <= 1);
            if (plusBtn) plusBtn.disabled = (cart[index].quantity >= cart[index].stock);
            
            // Reload summary to update totals
            loadCartSummary();
        }
        
        function updateSelectAllCheckbox() {
            const selectAllCheckbox = document.getElementById('select-all-items');
            if (!selectAllCheckbox) return;
            
            const allSelected = cart.length > 0 && cart.every(item => item.selected !== false);
            selectAllCheckbox.checked = allSelected;
        }
        
        function updateTotal() {
            const totalSpan = document.getElementById('order-total');
            if (!totalSpan) return;
            
            let total = 0;
            cart.forEach(item => {
                if (item.selected !== false) {
                    total += item.price * item.quantity;
                }
            });
            totalSpan.textContent = '₱' + total.toFixed(2);
        }
        
        function updateCartData() {
            const cartDataInput = document.getElementById('cart-data');
            if (!cartDataInput) return;
            
            // Only include selected items
            const selectedItems = cart.filter(item => item.selected !== false);
            cartDataInput.value = JSON.stringify(selectedItems);
        }
        
        // Store map variables
        let storeMap;
        let storeMapMarker;
        
        // Initialize store map
        function initStoreMap() {
            const mapDiv = document.getElementById('store_map');
            if (!mapDiv) return;
            
            const storeLat = <?= $storeLocation['latitude'] ?>;
            const storeLng = <?= $storeLocation['longitude'] ?>;
            
            if (storeMap) {
                storeMap.invalidateSize();
                return;
            }
            
            storeMap = L.map('store_map').setView([storeLat, storeLng], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(storeMap);
            
            // Add store marker
            storeMapMarker = L.marker([storeLat, storeLng], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34]
                })
            }).addTo(storeMap);
            
            storeMapMarker.bindPopup('<b><?= addslashes($storeLocation['name'] ?? 'DANICOP HARDWARE & CONSTRUCTION SUPPLY') ?></b><br><?= addslashes($storeLocation['address'] ?? 'Loon, Bohol, Philippines') ?>').openPopup();
        }
        
        // Load cart on page load
        loadCartSummary();
        
        // Initialize store map when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initStoreMap();
            
            // Check for order success
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('order_success') === '1') {
                // Get order number from PHP
                const orderNumber = '<?= htmlspecialchars($order_success_number) ?>';
                
                if (orderNumber) {
                    // Clear cart from localStorage
                    localStorage.removeItem('cart');
                    
                    // Wait for SweetAlert2 to load, then show success popup
                    function showSuccessPopup() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Order Placed Successfully!',
                                html: '<p class="text-lg mb-2">Your order has been placed successfully!</p><p class="text-gray-600">Order Number: <strong>' + orderNumber + '</strong></p>',
                                confirmButtonText: 'Go to My Orders',
                                confirmButtonColor: '#16a34a',
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then((result) => {
                                window.location.href = 'orders.php';
                            });
                            
                            // Clean URL
                            window.history.replaceState({}, document.title, window.location.pathname);
                        } else {
                            // Retry after a short delay if Swal is not yet loaded
                            setTimeout(showSuccessPopup, 100);
                        }
                    }
                    
                    showSuccessPopup();
                }
            }
        });
        
        function toggleDeliveryAddress() {
            const method = document.getElementById('delivery_method').value;
            const addressField = document.getElementById('delivery_address_field');
            if (method === 'delivery') {
                addressField.classList.remove('hidden');
                addressField.querySelector('textarea').required = true;
                // Initialize OpenStreetMap when delivery is selected
                setTimeout(initDeliveryMap, 100);
            } else {
                addressField.classList.add('hidden');
                addressField.querySelector('textarea').required = false;
            }
            updatePaymentMethod();
        }
        
        function updatePaymentMethod() {
            const deliveryMethod = document.getElementById('delivery_method').value;
            const paymentMethod = document.getElementById('payment_method').value;
            const paymentInfo = document.getElementById('payment_info');
            
            if (deliveryMethod === 'pickup') {
                if (paymentMethod === 'cash_pickup') {
                    paymentInfo.innerHTML = '<i class="fas fa-info-circle"></i> You will pay with cash when you pick up your order at the store (Over the Counter).';
                }
            } else {
                if (paymentMethod === 'cash_delivery') {
                    paymentInfo.innerHTML = '<i class="fas fa-info-circle"></i> You will pay with cash when your order is delivered.';
                }
            }
        }
        
        function submitCheckoutForm() {
            // Ensure cart data is updated with only selected items
            updateCartData();
            const selectedCartData = document.getElementById('cart-data').value;
            
            // Validate that we have selected items
            const selectedItems = JSON.parse(selectedCartData || '[]');
            if (selectedItems.length === 0) {
                alert('Please select at least one item to checkout.');
                return;
            }
            
            // Copy values from modal form to hidden form
            const deliveryMethod = document.getElementById('delivery_method_modal').value;
            const deliveryAddress = document.getElementById('delivery_address_modal').value;
            const deliveryLat = document.getElementById('delivery_latitude_modal').value;
            const deliveryLng = document.getElementById('delivery_longitude_modal').value;
            const contactNumber = document.getElementById('contact_number_modal').value;
            const paymentMethod = document.getElementById('payment_method_modal').value;
            
            // Use the selected items cart data
            const cartData = selectedCartData;
            
            // Validate
            if (deliveryMethod === 'delivery' && (!deliveryLat || !deliveryLng)) {
                alert('Please set your delivery location on the map or click "Use My Current Location" button before placing your order.');
                return;
            }
            
            // Create a temporary form to submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'checkout.php';
            
            // Add all fields
            const fields = {
                'cart': cartData,
                'delivery_method': deliveryMethod,
                'delivery_address': deliveryAddress,
                'delivery_latitude': deliveryLat,
                'delivery_longitude': deliveryLng,
                'contact_number': contactNumber,
                'payment_method': paymentMethod,
                'place_order': '1'
            };
            
            for (const [name, value] of Object.entries(fields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</div>
</body>
</html>

