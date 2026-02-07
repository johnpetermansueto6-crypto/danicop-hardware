<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Get unread notifications count
$result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE is_read = 0 AND user_id = {$_SESSION['user_id']}");
$unread_notifications = $result->fetch_assoc()['total'];

// Check setup status
$locations_table_exists = false;
$delivery_coords_exist = false;
$api_key_configured = defined('GOOGLE_MAPS_API_KEY') && GOOGLE_MAPS_API_KEY !== 'YOUR_GOOGLE_MAPS_API_KEY_HERE' && !empty(GOOGLE_MAPS_API_KEY);

$result = $conn->query("SHOW TABLES LIKE 'store_locations'");
$locations_table_exists = $result->num_rows > 0;

$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'delivery_latitude'");
$delivery_coords_exist = $result->num_rows > 0;

$needs_setup = !$locations_table_exists || !$delivery_coords_exist || !$api_key_configured;

$current_page = $_GET['page'] ?? 'dashboard';
$page_title = ucfirst(str_replace('_', ' ', $current_page));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Leaflet (OpenStreetMap) for admin maps like Store Locations -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false, currentPage: '<?= $current_page ?>', loading: true, pageTitle: '<?= $page_title ?>' }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-emerald-800 to-green-900 text-white flex-shrink-0 hidden md:block shadow-xl">
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="p-6 border-b border-emerald-700/50">
                    <a href="../index.php" class="text-xl font-bold flex items-center space-x-2">
                        <img src="../assets/images/logo.svg" alt="Logo" class="w-10 h-10 object-contain bg-white rounded-lg p-1">
                        <span>Danicop Hardware</span>
                    </a>
                    <p class="text-emerald-200 text-sm mt-1">Admin Panel</p>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <a @click.prevent="loadPage('dashboard')" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors" :class="currentPage === 'dashboard' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    <a @click.prevent="loadPage('products')" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors" :class="currentPage === 'products' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-box w-5"></i>
                        <span>Products</span>
                    </a>
                    <a @click.prevent="loadPage('orders')" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors" :class="currentPage === 'orders' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-shopping-cart w-5"></i>
                        <span>Orders</span>
                    </a>
                    <a @click.prevent="loadPage('deliveries')" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors" :class="currentPage === 'deliveries' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-truck w-5"></i>
                        <span>Deliveries</span>
                    </a>
                    <a @click.prevent="loadPage('reports')" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors" :class="currentPage === 'reports' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span>Reports</span>
                    </a>
                    <?php if (getUserRole() === 'superadmin'): ?>
                    <a @click.prevent="loadPage('users')" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors" :class="currentPage === 'users' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-users w-5"></i>
                        <span>Manage Staff</span>
                    </a>
                    <a @click.prevent="loadPage('locations')" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors" :class="currentPage === 'locations' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-map-marker-alt w-5"></i>
                        <span>Locations</span>
                    </a>
                    <?php endif; ?>
                    <a @click.prevent="loadPage('notifications')" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors relative" :class="currentPage === 'notifications' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
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
                <div class="p-4 border-t border-emerald-700/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-emerald-600 rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                            <p class="text-xs text-emerald-200 truncate"><?= $_SESSION['role'] === 'superadmin' ? 'Admin' : ucfirst($_SESSION['role']) ?></p>
                        </div>
                    </div>
                    <a href="../index.php" class="block w-full text-center px-4 py-2 bg-emerald-600 rounded-lg hover:bg-emerald-500 transition-colors mb-2 shadow-lg">
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
        <aside x-show="sidebarOpen" x-cloak x-transition class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-emerald-800 to-green-900 text-white z-50 md:hidden shadow-xl">
            <div class="h-full flex flex-col">
                <div class="p-6 border-b border-emerald-700/50 flex items-center justify-between">
                    <a href="../index.php" class="text-xl font-bold flex items-center space-x-2">
                        <img src="../assets/images/logo.svg" alt="Logo" class="w-10 h-10 object-contain bg-white rounded-lg p-1">
                        <span>Danicop Hardware</span>
                    </a>
                    <button @click="sidebarOpen = false" class="text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                    <a @click.prevent="loadPage('dashboard'); sidebarOpen = false" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg" :class="currentPage === 'dashboard' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-tachometer-alt w-5"></i>
                        <span>Dashboard</span>
                    </a>
                    <a @click.prevent="loadPage('products'); sidebarOpen = false" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg" :class="currentPage === 'products' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-box w-5"></i>
                        <span>Products</span>
                    </a>
                    <a @click.prevent="loadPage('orders'); sidebarOpen = false" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg" :class="currentPage === 'orders' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-shopping-cart w-5"></i>
                        <span>Orders</span>
                    </a>
                    <a @click.prevent="loadPage('deliveries'); sidebarOpen = false" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg" :class="currentPage === 'deliveries' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-truck w-5"></i>
                        <span>Deliveries</span>
                    </a>
                    <a @click.prevent="loadPage('reports'); sidebarOpen = false" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg" :class="currentPage === 'reports' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-chart-bar w-5"></i>
                        <span>Reports</span>
                    </a>
                    <?php if (getUserRole() === 'superadmin'): ?>
                    <a @click.prevent="loadPage('users'); sidebarOpen = false" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg" :class="currentPage === 'users' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-users w-5"></i>
                        <span>Manage Staff</span>
                    </a>
                    <a @click.prevent="loadPage('locations'); sidebarOpen = false" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg" :class="currentPage === 'locations' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-map-marker-alt w-5"></i>
                        <span>Locations</span>
                    </a>
                    <?php endif; ?>
                    <a @click.prevent="loadPage('notifications'); sidebarOpen = false" href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg relative" :class="currentPage === 'notifications' ? 'bg-emerald-600 shadow-lg' : 'hover:bg-emerald-700/50'">
                        <i class="fas fa-bell w-5"></i>
                        <span>Notifications</span>
                        <?php if ($unread_notifications > 0): ?>
                            <span class="absolute right-4 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                <?= $unread_notifications ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </nav>
                <div class="p-4 border-t border-emerald-700/50">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-emerald-600 rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                            <p class="text-xs text-emerald-200"><?= $_SESSION['role'] === 'superadmin' ? 'Admin' : ucfirst($_SESSION['role']) ?></p>
                        </div>
                    </div>
                    <a href="../index.php" class="block w-full text-center px-4 py-2 bg-emerald-600 rounded-lg hover:bg-emerald-500 mb-2 shadow-lg">
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
                    <h1 class="text-2xl font-bold text-gray-800" x-text="pageTitle || 'Admin Panel'"></h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600 hidden sm:block">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6" id="main-content">
                <div x-show="loading" class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-4xl text-emerald-600 mb-4"></i>
                        <p class="text-gray-600">Loading...</p>
                    </div>
                </div>
                <div x-show="!loading" x-cloak id="content-container">
                    <!-- Content will be loaded here via AJAX -->
                </div>
            </main>
        </div>
    </div>

    <script>
        const pageTitles = {
            'dashboard': 'Dashboard',
            'products': 'Manage Products',
            'orders': 'Manage Orders',
            'reports': 'Sales Reports',
            'users': 'Manage Staff',
            'locations': 'Store Locations',
            'notifications': 'Notifications',
            'order_details': 'Order Details',
            'product_add': 'Add Product',
            'product_edit': 'Edit Product',
            'user_add': 'Add Staff',
            'deliveries': 'Delivery Management',
            'drivers': 'Driver Management',
            'driver_add': 'Add Driver',
            'driver_edit': 'Edit Driver',
            'driver_view': 'Driver Details',
            'delivery_assign': 'Assign Driver',
            'delivery_history': 'Delivery History'
        };

        function loadPage(page, params = {}) {
            const app = Alpine.$data(document.body);
            app.loading = true;
            app.currentPage = page;
            app.pageTitle = pageTitles[page] || 'Admin Panel';
            
            // Build URL with params
            let url = `content/${page}.php`;
            const queryParams = new URLSearchParams(params);
            if (queryParams.toString()) {
                url += '?' + queryParams.toString();
            }
            
            // Load content via AJAX
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to load page');
                    }
                    return response.text();
                })
                .then(html => {
                    const contentContainer = document.getElementById('content-container');
                    contentContainer.innerHTML = html;
                    app.loading = false;

                    // Execute any scripts in the loaded content
                    const scripts = contentContainer.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        if (oldScript.src) {
                            newScript.src = oldScript.src;
                        } else {
                            newScript.textContent = oldScript.textContent;
                        }
                        document.body.appendChild(newScript);
                        oldScript.remove();
                    });

                    // If we just loaded the Locations page and the form is visible (edit mode),
                    // initialize the map for any existing coordinates.
                    if (page === 'locations') {
                        setTimeout(function () {
                            const formWrapper = document.getElementById('locationForm');
                            if (formWrapper && formWrapper.style.display !== 'none') {
                                initLocationMap();
                            }
                        }, 100);
                    }
                    
                    // Update URL without reload
                    const urlParams = new URLSearchParams({page: page, ...params});
                    window.history.pushState({page: page, params: params}, '', `?${urlParams.toString()}`);
                })
                .catch(error => {
                    console.error('Error loading page:', error);
                    app.loading = false;
                    const contentContainer = document.getElementById('content-container');
                    contentContainer.innerHTML = 
                        '<div class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded">Error loading page. Please try again.</div>';
                });
        }

        // Handle browser back/forward
        window.addEventListener('popstate', function(event) {
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page') || 'dashboard';
            const params = {};
            urlParams.forEach((value, key) => {
                if (key !== 'page') params[key] = value;
            });
            loadPage(page, params);
        });

        // Load initial page
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page') || 'dashboard';
            const params = {};
            urlParams.forEach((value, key) => {
                if (key !== 'page') params[key] = value;
            });
            loadPage(page, params);
        });

        // Make loadPage globally available
        window.loadPage = loadPage;

        // Product management functions (available globally)
        window.editProduct = function(id) {
            loadPage('product_edit', {id: id});
        };

        window.deleteProduct = function(id, productName) {
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 is not loaded');
                if (confirm(`Do you want to delete "${productName}"?`)) {
                    loadPage('products', {delete: id});
                }
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                html: `<p>Do you want to delete <strong>"${productName}"</strong>?</p><p class="text-red-600 text-sm mt-2">This action cannot be undone!</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-trash mr-2"></i>Yes, delete it!',
                cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancel',
                reverseButtons: true,
                buttonsStyling: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Delete via AJAX
                    fetch(`content/products.php?delete=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#059669',
                                    confirmButtonText: 'OK',
                                    timer: 2000,
                                    timerProgressBar: true
                                }).then(() => {
                                    // Reload the products page
                                    loadPage('products');
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message || 'Failed to delete product',
                                    icon: 'error',
                                    confirmButtonColor: '#dc2626',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while deleting the product',
                                icon: 'error',
                                confirmButtonColor: '#dc2626',
                                confirmButtonText: 'OK'
                            });
                        });
                }
            });
        };

        // =========================
        // Locations (Store Map) JS
        // =========================
        let adminLocationMapInstance;
        let adminLocationMarkerInstance;

        function initLocationMap() {
            const mapElement = document.getElementById('map');
            if (!mapElement || typeof L === 'undefined') return;

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            if (!latInput || !lngInput) return;

            const currentLat = parseFloat(latInput.value);
            const currentLng = parseFloat(lngInput.value);

            const defaultLat = !isNaN(currentLat) ? currentLat : 14.5995;
            const defaultLng = !isNaN(currentLng) ? currentLng : 120.9842;

            // Recreate map if it already exists (e.g., when reopening the form)
            if (adminLocationMapInstance) {
                adminLocationMapInstance.remove();
                adminLocationMapInstance = null;
                adminLocationMarkerInstance = null;
            }

            adminLocationMapInstance = L.map('map').setView([defaultLat, defaultLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(adminLocationMapInstance);

            function setMarker(lat, lng) {
                if (adminLocationMarkerInstance) {
                    adminLocationMarkerInstance.setLatLng([lat, lng]);
                } else {
                    adminLocationMarkerInstance = L.marker([lat, lng], { draggable: true }).addTo(adminLocationMapInstance);

                    adminLocationMarkerInstance.on('dragend', function (e) {
                        const pos = e.target.getLatLng();
                        latInput.value = pos.lat.toFixed(8);
                        lngInput.value = pos.lng.toFixed(8);
                    });
                }
            }

            if (!isNaN(currentLat) && !isNaN(currentLng)) {
                setMarker(currentLat, currentLng);
            }

            adminLocationMapInstance.on('click', function (e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;
                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
                setMarker(lat, lng);
            });
        }

        function showAddForm() {
            const formWrapper = document.getElementById('locationForm');
            if (!formWrapper) return;

            formWrapper.style.display = 'block';
            formWrapper.scrollIntoView({ behavior: 'smooth' });

            const form = formWrapper.querySelector('form');
            if (form) {
                form.reset();
                const idInput = form.querySelector('input[name="id"]');
                if (idInput) {
                    idInput.value = '0';
                }
            }

            setTimeout(initLocationMap, 100);
        }

        function hideForm() {
            const formWrapper = document.getElementById('locationForm');
            if (formWrapper) {
                formWrapper.style.display = 'none';
            }
            loadPage('locations');
        }

        function handleFormSubmit(event, form, redirectPage) {
            event.preventDefault();
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(html => {
                    const contentContainer = document.getElementById('content-container');
                    contentContainer.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        }

        // Expose helpers globally for inline onclick handlers
        window.showAddForm = showAddForm;
        window.hideForm = hideForm;
        window.handleFormSubmit = handleFormSubmit;
    </script>
</body>
</html>

