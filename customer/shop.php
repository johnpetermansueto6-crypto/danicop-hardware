<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Product filters
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

// Categories
$categoriesQuery = "SELECT DISTINCT category FROM products ORDER BY category";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row['category'];
}

$current_page = 'shop';
$page_title = 'Products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
<?php include '../includes/customer_topbar.php'; ?>

<!-- Main Content -->
<div class="container mx-auto px-4 py-6">
        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl shadow-2xl p-8 md:p-12 mb-8 text-white">
            <div class="max-w-4xl">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to Danicop Hardware</h1>
                <p class="text-xl md:text-2xl mb-6 text-green-50">Your Trusted Partner for Quality Hardware & Construction Supplies</p>
                
                <!-- Key Features -->
                <div class="flex flex-wrap gap-6 mt-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <span class="text-lg font-semibold">Quality Products</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <span class="text-lg font-semibold">Expert Service</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <span class="text-lg font-semibold">Fast Delivery</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Why Choose Us Section -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 text-center mb-8">Why Choose Us</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1: Wide Selection -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-tools text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Wide Selection</h3>
                    <p class="text-gray-600 text-center text-sm">Extensive range of hardware products and construction materials</p>
                </div>
                
                <!-- Card 2: Fast Delivery -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-truck text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Fast Delivery</h3>
                    <p class="text-gray-600 text-center text-sm">Quick and reliable delivery service to your doorstep</p>
                </div>
                
                <!-- Card 3: Quality Guaranteed -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-star text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Quality Guaranteed</h3>
                    <p class="text-gray-600 text-center text-sm">Premium quality products from trusted manufacturers</p>
                </div>
                
                <!-- Card 4: Expert Support -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-headset text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Expert Support</h3>
                    <p class="text-gray-600 text-center text-sm">Knowledgeable staff ready to assist with your needs</p>
                </div>
            </div>
        </div>
        
        <!-- Products Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Products</h1>
            <p class="text-gray-600">Browse products and add them to your cart.</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
            <form method="GET" action="shop.php" class="mb-0">
                <div class="flex flex-col md:flex-row gap-4">
                    <!-- Search Bar -->
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Search products..." 
                               class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300">
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="md:w-48 relative">
                        <i class="fas fa-filter absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        <select name="category" class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white transition-all duration-300">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Search Button -->
                    <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-8 py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                    
                    <!-- Clear Button -->
                    <?php if (!empty($search) || !empty($category)): ?>
                        <a href="shop.php" class="bg-gray-500 text-white px-6 py-3 rounded-xl hover:bg-gray-600 flex items-center justify-center font-semibold transform hover:scale-105 transition-all duration-300 shadow-lg">
                            <i class="fas fa-times mr-2"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if ($products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                        <div class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden relative">
                            <?php if ($product['image'] && file_exists('../uploads/' . $product['image'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="h-full w-full object-cover">
                            <?php else: ?>
                                <div class="text-center">
                                    <i class="fas fa-box text-6xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            <div class="absolute top-2 right-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold <?= $product['stock'] < 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?> shadow-md">
                                    <?= $product['stock'] < 10 ? 'Low Stock' : 'In Stock' ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="mb-2">
                                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
                                    <?= htmlspecialchars($product['category']) ?>
                                </span>
                            </div>
                            <h3 class="font-bold text-lg mb-2 text-gray-800 line-clamp-1"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2 min-h-[2.5rem]"><?= htmlspecialchars($product['description']) ?></p>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-3xl font-extrabold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                    ₱<?= number_format($product['price'], 2) ?>
                                </span>
                                <span class="text-sm font-semibold <?= $product['stock'] < 10 ? 'text-red-600' : 'text-green-600' ?>">
                                    <i class="fas fa-cubes mr-1"></i><?= $product['stock'] ?> in stock
                                </span>
                            </div>
                            
                            <!-- Quantity Selector (Shopee-style) -->
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-sort-numeric-up mr-1"></i> Quantity
                                </label>
                                <div class="flex items-center space-x-2">
                                    <button 
                                        type="button"
                                        onclick="updateProductQuantity(<?= $product['id'] ?>, -1, <?= $product['stock'] ?>)"
                                        class="w-10 h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                        id="qty-minus-<?= $product['id'] ?>">
                                        <i class="fas fa-minus text-sm"></i>
                                    </button>
                                    <input 
                                        type="number" 
                                        id="qty-input-<?= $product['id'] ?>"
                                        value="1" 
                                        min="1" 
                                        max="<?= $product['stock'] ?>"
                                        class="w-20 h-10 text-center border-2 border-gray-300 rounded-lg font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="validateProductQuantity(<?= $product['id'] ?>, <?= $product['stock'] ?>)"
                                        oninput="validateProductQuantity(<?= $product['id'] ?>, <?= $product['stock'] ?>)">
                                    <button 
                                        type="button"
                                        onclick="updateProductQuantity(<?= $product['id'] ?>, 1, <?= $product['stock'] ?>)"
                                        class="w-10 h-10 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                        id="qty-plus-<?= $product['id'] ?>">
                                        <i class="fas fa-plus text-sm"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span id="qty-info-<?= $product['id'] ?>">1 item</span>
                                </p>
                            </div>
                            
                            <button 
                                onclick="addToCartWithQuantity(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>', <?= $product['price'] ?>, <?= $product['stock'] ?>, '<?= htmlspecialchars(addslashes($product['image'] ?? '')) ?>')" 
                                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl"
                                <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-cart-plus mr-2"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-16">
                    <div class="inline-block p-6 bg-white rounded-full shadow-lg mb-4">
                        <i class="fas fa-search text-6xl text-gray-400"></i>
                    </div>
                    <p class="text-2xl font-bold text-gray-600 mb-2">No products found</p>
                    <p class="text-gray-500">Try adjusting your search or filter criteria</p>
                </div>
            <?php endif; ?>
        </div>

        <script>
            // Basic cart functions (mirror index.php behavior, using localStorage)
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Quantity management functions
            function updateProductQuantity(productId, change, maxStock) {
                const input = document.getElementById('qty-input-' + productId);
                const minusBtn = document.getElementById('qty-minus-' + productId);
                const plusBtn = document.getElementById('qty-plus-' + productId);
                const info = document.getElementById('qty-info-' + productId);
                
                let currentQty = parseInt(input.value) || 1;
                let newQty = currentQty + change;
                
                // Validate bounds
                if (newQty < 1) newQty = 1;
                if (newQty > maxStock) newQty = maxStock;
                
                input.value = newQty;
                
                // Update button states
                minusBtn.disabled = (newQty <= 1);
                plusBtn.disabled = (newQty >= maxStock);
                
                // Update info text
                info.textContent = newQty + (newQty === 1 ? ' item' : ' items');
            }
            
            function validateProductQuantity(productId, maxStock) {
                const input = document.getElementById('qty-input-' + productId);
                const minusBtn = document.getElementById('qty-minus-' + productId);
                const plusBtn = document.getElementById('qty-plus-' + productId);
                const info = document.getElementById('qty-info-' + productId);
                
                let qty = parseInt(input.value) || 1;
                
                // Validate bounds
                if (qty < 1) qty = 1;
                if (qty > maxStock) qty = maxStock;
                
                input.value = qty;
                
                // Update button states
                minusBtn.disabled = (qty <= 1);
                plusBtn.disabled = (qty >= maxStock);
                
                // Update info text
                info.textContent = qty + (qty === 1 ? ' item' : ' items');
            }
            
            function addToCartWithQuantity(id, name, price, stock, image) {
                const qtyInput = document.getElementById('qty-input-' + id);
                const quantity = parseInt(qtyInput.value) || 1;
                
                if (quantity < 1) {
                    alert('Quantity must be at least 1');
                    return;
                }
                
                if (quantity > stock) {
                    alert('Quantity cannot exceed available stock (' + stock + ')');
                    qtyInput.value = stock;
                    return;
                }
                
                const existingItem = cart.find(item => item.id === id);
                
                if (existingItem) {
                    const newTotalQty = existingItem.quantity + quantity;
                    if (newTotalQty > stock) {
                        alert('Cannot add more. Total quantity would exceed stock limit (' + stock + '). Current in cart: ' + existingItem.quantity);
                        return;
                    }
                    existingItem.quantity = newTotalQty;
                } else {
                    cart.push({ id, name, price, stock, quantity: quantity, image: image || '' });
                }

                localStorage.setItem('cart', JSON.stringify(cart));

                // Update checkout badge in sidebar, if available
                if (typeof updateCheckoutBadge === 'function') {
                    updateCheckoutBadge();
                }

                // Stylish toast notification (matches homepage style)
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl z-50';
                notification.style.animation = 'slideInRight 0.5s ease-out';
                notification.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-check-circle text-2xl animate-pulse"></i>
                        <div>
                            <p class="font-bold">Added to Cart!</p>
                            <p class="text-sm opacity-90">${name} (${quantity} ${quantity === 1 ? 'item' : 'items'})</p>
                        </div>
                    </div>
                `;
                document.body.appendChild(notification);
                setTimeout(() => {
                    notification.style.animation = 'slideInRight 0.5s ease-out reverse';
                    setTimeout(() => notification.remove(), 500);
                }, 2500);
                
                // Reset quantity to 1 after adding
                qtyInput.value = 1;
                validateProductQuantity(id, stock);
            }
            
            // Keep old function for backward compatibility
            function addToCart(id, name, price, stock) {
                addToCartWithQuantity(id, name, price, stock);
            }
        </script>
        </main>
    </div>
</div>
</body>
</html>


