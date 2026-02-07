<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Get all active store locations
$locations = $conn->query("SELECT * FROM store_locations WHERE is_active = 1 ORDER BY name ASC");

$current_page = 'our_store';
$page_title = 'Our Store';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Store - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        [x-cloak] { display: none !important; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        .store-card {
            transition: all 0.3s ease;
        }
        .store-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
<?php include '../includes/customer_topbar.php'; ?>

<!-- Main Content -->
<div class="container mx-auto px-4 py-6">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl shadow-xl p-8 md:p-12 mb-8 text-white animate-fade-in">
        <div class="max-w-3xl">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to Danicop Hardware</h1>
            <p class="text-xl md:text-2xl mb-6 opacity-90">Your Trusted Partner for Quality Hardware & Construction Supplies</p>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-check-circle text-2xl"></i>
                    <span>Quality Products</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-check-circle text-2xl"></i>
                    <span>Expert Service</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-check-circle text-2xl"></i>
                    <span>Fast Delivery</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Features -->
    <div class="mb-12 animate-fade-in">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Why Choose Us</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-lg p-6 text-center store-card">
                <div class="feature-icon bg-green-100 text-green-600 rounded-full mx-auto mb-4">
                    <i class="fas fa-tools text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Wide Selection</h3>
                <p class="text-gray-600">Extensive range of hardware products and construction materials</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 text-center store-card">
                <div class="feature-icon bg-blue-100 text-blue-600 rounded-full mx-auto mb-4">
                    <i class="fas fa-shipping-fast text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Fast Delivery</h3>
                <p class="text-gray-600">Quick and reliable delivery service to your doorstep</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 text-center store-card">
                <div class="feature-icon bg-yellow-100 text-yellow-600 rounded-full mx-auto mb-4">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Quality Guaranteed</h3>
                <p class="text-gray-600">Premium quality products from trusted manufacturers</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 text-center store-card">
                <div class="feature-icon bg-purple-100 text-purple-600 rounded-full mx-auto mb-4">
                    <i class="fas fa-headset text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Expert Support</h3>
                <p class="text-gray-600">Knowledgeable staff ready to assist with your needs</p>
            </div>
        </div>
    </div>

    <!-- Store Locations -->
    <div class="mb-8 animate-fade-in">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Our Store Locations</h2>
        
        <?php if ($locations->num_rows > 0): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <?php 
                $locations->data_seek(0);
                $locationIndex = 0;
                while ($location = $locations->fetch_assoc()): 
                    $locationIndex++;
                ?>
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 store-card" style="animation-delay: <?= $locationIndex * 0.1 ?>s;">
                        <!-- Store Image/Map Section -->
                        <div class="h-64 bg-gradient-to-br from-green-100 to-emerald-100 relative">
                            <?php if ($location['latitude'] && $location['longitude']): ?>
                                <div id="map-<?= $location['id'] ?>" class="w-full h-full"></div>
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full">
                                    <div class="text-center">
                                        <i class="fas fa-store text-6xl text-green-400 mb-4"></i>
                                        <p class="text-green-600 font-semibold"><?= htmlspecialchars($location['name']) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Store Details -->
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($location['name']) ?></h3>
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                                    <i class="fas fa-check-circle mr-1"></i> Open
                                </span>
                            </div>
                            
                            <div class="space-y-4 mb-6">
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-map-marker-alt text-green-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 mb-1">Address</p>
                                        <p class="text-gray-600"><?= nl2br(htmlspecialchars($location['address'])) ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($location['phone']): ?>
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-phone text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 mb-1">Phone</p>
                                        <a href="tel:<?= htmlspecialchars($location['phone']) ?>" class="text-green-600 hover:text-green-700 font-semibold">
                                            <?= htmlspecialchars($location['phone']) ?>
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($location['email']): ?>
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-envelope text-purple-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 mb-1">Email</p>
                                        <a href="mailto:<?= htmlspecialchars($location['email']) ?>" class="text-green-600 hover:text-green-700">
                                            <?= htmlspecialchars($location['email']) ?>
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($location['hours']): ?>
                                <div class="flex items-start">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                        <i class="fas fa-clock text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700 mb-1">Operating Hours</p>
                                        <p class="text-gray-600 whitespace-pre-line"><?= htmlspecialchars($location['hours']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-3">
                                <?php if ($location['latitude'] && $location['longitude']): ?>
                                <a href="https://www.openstreetmap.org/directions?to=<?= urlencode($location['latitude'] . ',' . $location['longitude']) ?>" 
                                   target="_blank" 
                                   class="flex-1 bg-green-600 text-white px-4 py-3 rounded-lg hover:bg-green-700 transition-colors text-center font-semibold">
                                    <i class="fas fa-directions mr-2"></i> Get Directions
                                </a>
                                <?php endif; ?>
                                <a href="shop.php" 
                                   class="flex-1 bg-gray-100 text-gray-700 px-4 py-3 rounded-lg hover:bg-gray-200 transition-colors text-center font-semibold">
                                    <i class="fas fa-shopping-cart mr-2"></i> Shop Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12 bg-white rounded-2xl shadow-lg">
                <i class="fas fa-store text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No store locations available yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Additional Information Section -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 animate-fade-in">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">More About Us</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-green-600 mr-2"></i>
                    Our Mission
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    At Danicop Hardware, we are committed to providing high-quality hardware and construction supplies 
                    to builders, contractors, and homeowners. We strive to offer excellent customer service, competitive 
                    prices, and reliable delivery to ensure your projects succeed.
                </p>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-handshake text-green-600 mr-2"></i>
                    Our Promise
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    We guarantee quality products, timely delivery, and exceptional customer support. Your satisfaction 
                    is our priority, and we're here to help you find exactly what you need for your construction and 
                    hardware projects.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS for maps -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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

            // Custom green marker
            const greenIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34]
            });

            L.marker([lat, lng], { icon: greenIcon })
                .addTo(map)
                .bindPopup('<b><?= addslashes($location['name']) ?></b><br><?= addslashes($location['address']) ?>');
        })();
        <?php 
            endif;
        endwhile; 
        ?>
    });
</script>
</body>
</html>

