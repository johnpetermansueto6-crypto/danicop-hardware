<?php
require_once '../includes/config.php';

if (!isLoggedIn() || getUserRole() !== 'driver') {
    redirect('../index.php');
}

$current_user_id = $_SESSION['user_id'];

// Get driver's active deliveries
$activeStmt = $conn->prepare("
    SELECT 
        da.id as assignment_id,
        da.status as delivery_status,
        da.notes as delivery_notes,
        da.created_at as assigned_at,
        da.delivery_started_at,
        o.id as order_id,
        o.order_number,
        o.delivery_method,
        o.delivery_address,
        o.delivery_latitude,
        o.delivery_longitude,
        o.contact_number,
        o.total_amount,
        o.payment_method,
        o.created_at as order_date,
        u.id as customer_id,
        u.name as customer_name,
        u.phone as customer_phone,
        u.email as customer_email,
        u.address as customer_address,
        u.city as customer_city,
        u.province as customer_province,
        u.zipcode as customer_zipcode
    FROM delivery_assignments da
    INNER JOIN orders o ON da.order_id = o.id
    INNER JOIN users u ON o.user_id = u.id
    WHERE da.driver_id = ? AND da.status IN ('assigned', 'picked_up', 'delivering')
    ORDER BY da.created_at DESC
");
$activeStmt->bind_param("i", $current_user_id);
$activeStmt->execute();
$activeDeliveries = $activeStmt->get_result();

// Get driver's completed deliveries (today)
$todayStmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM delivery_assignments
    WHERE driver_id = ? AND status = 'delivered' AND DATE(delivery_completed_at) = CURDATE()
");
$todayStmt->bind_param("i", $current_user_id);
$todayStmt->execute();
$todayResult = $todayStmt->get_result()->fetch_assoc();
$todayCount = $todayResult['count'] ?? 0;

// Get total completed deliveries
$totalStmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM delivery_assignments
    WHERE driver_id = ? AND status = 'delivered'
");
$totalStmt->bind_param("i", $current_user_id);
$totalStmt->execute();
$totalResult = $totalStmt->get_result()->fetch_assoc();
$totalCount = $totalResult['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Ensure SweetAlert modal is clickable on mobile */
        .swal2-popup-mobile {
            pointer-events: auto !important;
            z-index: 10000 !important;
        }
        .swal2-container-mobile {
            pointer-events: auto !important;
            z-index: 9999 !important;
        }
        .swal2-popup-mobile select,
        .swal2-popup-mobile input,
        .swal2-popup-mobile textarea,
        .swal2-popup-mobile button {
            pointer-events: auto !important;
            -webkit-tap-highlight-color: rgba(0,0,0,0.1);
        }
        /* Ensure buttons are tappable on mobile */
        .swal2-confirm,
        .swal2-cancel {
            min-height: 44px !important;
            min-width: 44px !important;
            touch-action: manipulation !important;
        }
        /* Ensure action buttons are clickable */
        button[onclick*="updateStatus"],
        button[onclick*="cancelDelivery"],
        button[onclick*="viewFullDetails"] {
            pointer-events: auto !important;
            cursor: pointer !important;
            z-index: 10 !important;
            position: relative !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="ml-64">
        <!-- Header -->
        <nav class="bg-green-600 text-white shadow-lg">
            <div class="px-6 py-4">
                <h1 class="text-xl font-bold"><i class="fas fa-chart-line mr-2"></i>Dashboard Overview</h1>
            </div>
        </nav>

        <div class="p-6">
        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Active Deliveries</p>
                        <p class="text-3xl font-bold text-blue-600"><?= $activeDeliveries->num_rows ?></p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-truck text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Today Completed</p>
                        <p class="text-3xl font-bold text-green-600"><?= $todayCount ?></p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Completed</p>
                        <p class="text-3xl font-bold text-purple-600"><?= $totalCount ?></p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full">
                        <i class="fas fa-trophy text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <a href="assigned_deliveries.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-clipboard-list text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Assigned Deliveries</h3>
                        <p class="text-gray-500 text-sm">View your active delivery assignments</p>
                    </div>
                </div>
            </a>
            <a href="completed_deliveries.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center space-x-4">
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Completed Deliveries</h3>
                        <p class="text-gray-500 text-sm">View your delivery history</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Active Deliveries Preview -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-green-600 text-white px-6 py-4 flex items-center justify-between">
                <h2 class="text-xl font-bold"><i class="fas fa-list mr-2"></i>Recent Assigned Deliveries</h2>
                <a href="assigned_deliveries.php" class="text-sm hover:underline">View All →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-left py-3 px-4">Order #</th>
                            <th class="text-left py-3 px-4">Customer Details</th>
                            <th class="text-left py-3 px-4">Contact Info</th>
                            <th class="text-left py-3 px-4">Delivery Address</th>
                            <th class="text-left py-3 px-4">Order Info</th>
                            <th class="text-left py-3 px-4">Status</th>
                            <th class="text-left py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($activeDeliveries->num_rows > 0): ?>
                            <?php while ($delivery = $activeDeliveries->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <div class="font-semibold"><?= htmlspecialchars($delivery['order_number']) ?></div>
                                        <div class="text-xs text-gray-500"><?= date('M d, Y', strtotime($delivery['order_date'])) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-gray-900"><?= htmlspecialchars($delivery['customer_name']) ?></div>
                                        <div class="text-xs text-gray-600 mt-1">
                                            <i class="fas fa-envelope mr-1"></i><?= htmlspecialchars($delivery['customer_email']) ?>
                                        </div>
                                        <?php if ($delivery['customer_address'] || $delivery['customer_city']): ?>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-home mr-1"></i>
                                                <?php
                                                $addressParts = array_filter([
                                                    $delivery['customer_address'],
                                                    $delivery['customer_city'],
                                                    $delivery['customer_province'],
                                                    $delivery['customer_zipcode']
                                                ]);
                                                echo htmlspecialchars(implode(', ', $addressParts));
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm font-semibold">
                                            <i class="fas fa-phone mr-1 text-green-600"></i>
                                            <?= htmlspecialchars($delivery['contact_number']) ?>
                                        </div>
                                        <?php if ($delivery['customer_phone'] && $delivery['customer_phone'] !== $delivery['contact_number']): ?>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Alt: <?= htmlspecialchars($delivery['customer_phone']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-sm font-semibold"><?= htmlspecialchars($delivery['delivery_address']) ?></div>
                                        <?php if ($delivery['delivery_latitude'] && $delivery['delivery_longitude']): ?>
                                            <a href="https://www.google.com/maps?q=<?= $delivery['delivery_latitude'] ?>,<?= $delivery['delivery_longitude'] ?>" 
                                               target="_blank" 
                                               class="text-blue-600 hover:underline text-xs mt-1 inline-block">
                                                <i class="fas fa-map-marker-alt mr-1"></i> View on Map
                                            </a>
                                        <?php else: ?>
                                            <a href="https://maps.google.com/?q=<?= urlencode($delivery['delivery_address']) ?>" 
                                               target="_blank" 
                                               class="text-blue-600 hover:underline text-xs mt-1 inline-block">
                                                <i class="fas fa-map-marker-alt mr-1"></i> View on Map
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($delivery['delivery_notes']): ?>
                                            <div class="text-xs text-orange-600 mt-2 p-2 bg-orange-50 rounded">
                                                <i class="fas fa-sticky-note mr-1"></i><?= htmlspecialchars($delivery['delivery_notes']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-green-600">₱<?= number_format($delivery['total_amount'], 2) ?></div>
                                        <div class="text-xs text-gray-500"><?= ucfirst(str_replace('_', ' ', $delivery['payment_method'])) ?></div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-calendar mr-1"></i>Assigned: <?= date('M d, H:i', strtotime($delivery['assigned_at'])) ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-xs font-semibold <?php
                                            echo match($delivery['delivery_status']) {
                                                'assigned' => 'bg-yellow-100 text-yellow-800',
                                                'picked_up' => 'bg-blue-100 text-blue-800',
                                                'delivering' => 'bg-purple-100 text-purple-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            <?= ucfirst(str_replace('_', ' ', $delivery['delivery_status'])) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-col gap-2">
                                            <button onclick="viewFullDetails(<?= $delivery['assignment_id'] ?>)" 
                                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                <i class="fas fa-eye mr-1"></i> View All
                                            </button>
                                            <?php $currentStatus = $delivery['delivery_status'] ?? ''; ?>
                                            <button type="button" onclick="updateStatus(<?= $delivery['assignment_id'] ?>, '<?= htmlspecialchars($currentStatus, ENT_QUOTES) ?>')" 
                                                    class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 w-full cursor-pointer"
                                                    style="pointer-events: auto; z-index: 1; position: relative;">
                                                <i class="fas fa-check-circle mr-1"></i> Mark as Delivered
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                    <p>No active deliveries assigned to you</p>
                                    <a href="assigned_deliveries.php" class="text-blue-600 hover:underline mt-2 inline-block">View All Deliveries</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>

    <script>
    function markAsDelivered(assignmentId) {
        // Directly show photo upload for "delivered" status
        Swal.fire({
            title: 'Mark as Delivered',
            html: `
                <div style="text-align: left;">
                    <p style="margin-bottom: 15px; font-weight: bold; color: #16a34a;">📸 Proof of Delivery Required</p>
                    <p style="margin-bottom: 15px;">Please upload a photo showing the customer receiving the items as proof of delivery:</p>
                    <input type="file" id="delivery_proof_image" accept="image/*" capture="environment" style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">
                    <div id="image_preview" style="margin-top: 10px; text-align: center;"></div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Mark as Delivered',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#16a34a',
            didOpen: () => {
                const fileInput = document.getElementById('delivery_proof_image');
                const preview = document.getElementById('image_preview');
                
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 200px; border-radius: 4px; margin-top: 10px; border: 2px solid #16a34a;">`;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.innerHTML = '';
                    }
                });
            },
            preConfirm: () => {
                const fileInput = document.getElementById('delivery_proof_image');
                if (!fileInput.files || !fileInput.files[0]) {
                    Swal.showValidationMessage('Please upload a proof of delivery photo');
                    return false;
                }
                return {
                    file: fileInput.files[0],
                    status: 'delivered'
                };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire({
                    title: 'Uploading...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create FormData for file upload
                const formData = new FormData();
                formData.append('assignment_id', assignmentId);
                formData.append('status', 'delivered');
                formData.append('delivery_proof_image', result.value.file);

                fetch('../admin/content/delivery_update.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Delivery marked as completed with proof photo',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#16a34a'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to update status',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while uploading',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    }

    function viewFullDetails(assignmentId) {
        fetch(`../admin/content/delivery_view_full.php?assignment_id=${assignmentId}`)
            .then(response => response.text())
            .then(html => {
                Swal.fire({
                    title: 'Complete Delivery Information',
                    html: html,
                    width: '800px',
                    showConfirmButton: true,
                    confirmButtonText: 'Close',
                    customClass: {
                        popup: 'text-left'
                    }
                });
            })
            .catch(error => {
                console.error('Error:', error);
                viewDeliveryDetails(assignmentId);
            });
    }
    
    function viewDeliveryDetails(assignmentId) {
        fetch(`../admin/content/delivery_view.php?assignment_id=${assignmentId}`)
            .then(response => response.text())
            .then(html => {
                Swal.fire({
                    title: 'Delivery Details',
                    html: html,
                    width: '700px',
                    showConfirmButton: true,
                    confirmButtonText: 'Close'
                });
            });
    }

    function updateStatus(assignmentId, currentStatus) {
        console.log('updateStatus called with:', assignmentId, currentStatus);
        
        // Directly show photo and signature modal for marking as delivered
        Swal.fire({
            title: 'Mark as Delivered',
            html: `
                <div style="text-align: left; max-width: 100%; pointer-events: auto;">
                    <div style="background: #f0f9ff; border-left: 4px solid #16a34a; padding: 12px; margin-bottom: 15px; border-radius: 4px;">
                        <p style="margin: 0; font-weight: bold; color: #16a34a; font-size: 14px;">📸 Required: Photo & Signature</p>
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #555;">Please capture a photo of the delivered items and get customer signature</p>
                    </div>
                    
                    <label style="display: block; margin-bottom: 8px; font-weight: bold; font-size: 14px; color: #333;">1. Photo of Delivered Items *</label>
                    <input type="file" id="delivery_proof_image" accept="image/*" capture="environment" style="width: 100%; padding: 12px; margin-bottom: 15px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px; touch-action: manipulation; cursor: pointer; pointer-events: auto;">
                    <div id="image_preview" style="margin-top: 10px; text-align: center;"></div>
                    
                    <label style="display: block; margin-bottom: 8px; margin-top: 20px; font-weight: bold; font-size: 14px; color: #333;">2. Customer Signature *</label>
                    <div style="border: 2px solid #ddd; border-radius: 6px; padding: 10px; background: white; margin-bottom: 10px;">
                        <canvas id="signature_canvas" style="border: 1px solid #ccc; border-radius: 4px; cursor: crosshair; touch-action: none; width: 100%; height: 150px; background: white;"></canvas>
                        <div style="margin-top: 8px; display: flex; gap: 8px;">
                            <button type="button" id="clear_signature" style="flex: 1; padding: 8px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                                <i class="fas fa-eraser mr-1"></i> Clear
                            </button>
                        </div>
                    </div>
                    <div id="signature_preview" style="margin-top: 10px; text-align: center;"></div>
                </div>
            `,
            width: window.innerWidth < 768 ? '90%' : '500px',
            showCancelButton: true,
            confirmButtonText: 'Mark as Delivered',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#16a34a',
            allowOutsideClick: true,
            allowEscapeKey: true,
            backdrop: true,
            customClass: {
                popup: 'swal2-popup-mobile',
                container: 'swal2-container-mobile'
            },
            didOpen: () => {
                const fileInput = document.getElementById('delivery_proof_image');
                const preview = document.getElementById('image_preview');
                const canvas = document.getElementById('signature_canvas');
                const clearBtn = document.getElementById('clear_signature');
                const signaturePreview = document.getElementById('signature_preview');
                
                // Initialize signature canvas
                if (canvas) {
                    let isDrawing = false;
                    let lastX = 0;
                    let lastY = 0;
                    const ctx = canvas.getContext('2d');
                    
                    // Set canvas size
                    const rect = canvas.getBoundingClientRect();
                    canvas.width = rect.width;
                    canvas.height = 150;
                    
                    // Set drawing style
                    ctx.strokeStyle = '#000000';
                    ctx.lineWidth = 2;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                    
                    // Drawing functions
                    function startDrawing(e) {
                        isDrawing = true;
                        const rect = canvas.getBoundingClientRect();
                        lastX = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
                        lastY = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
                    }
                    
                    function draw(e) {
                        if (!isDrawing) return;
                        e.preventDefault();
                        const rect = canvas.getBoundingClientRect();
                        const currentX = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
                        const currentY = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
                        
                        ctx.beginPath();
                        ctx.moveTo(lastX, lastY);
                        ctx.lineTo(currentX, currentY);
                        ctx.stroke();
                        
                        lastX = currentX;
                        lastY = currentY;
                        
                        // Show preview
                        if (signaturePreview) {
                            signaturePreview.innerHTML = '<p style="color: #16a34a; font-size: 12px;"><i class="fas fa-check-circle"></i> Signature captured</p>';
                        }
                    }
                    
                    function stopDrawing() {
                        if (isDrawing) {
                            isDrawing = false;
                        }
                    }
                    
                    // Mouse events
                    canvas.addEventListener('mousedown', startDrawing);
                    canvas.addEventListener('mousemove', draw);
                    canvas.addEventListener('mouseup', stopDrawing);
                    canvas.addEventListener('mouseout', stopDrawing);
                    
                    // Touch events for mobile
                    canvas.addEventListener('touchstart', (e) => {
                        e.preventDefault();
                        startDrawing(e);
                    });
                    canvas.addEventListener('touchmove', (e) => {
                        e.preventDefault();
                        draw(e);
                    });
                    canvas.addEventListener('touchend', stopDrawing);
                    canvas.addEventListener('touchcancel', stopDrawing);
                    
                    // Clear signature
                    if (clearBtn) {
                        clearBtn.addEventListener('click', () => {
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            if (signaturePreview) {
                                signaturePreview.innerHTML = '';
                            }
                        });
                    }
                }
                
                // Handle photo preview
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 200px; border-radius: 4px; margin-top: 10px; border: 2px solid #16a34a;">`;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.innerHTML = '';
                    }
                });
            },
                preConfirm: () => {
                    const statusSelect = document.getElementById('delivery_status_select');
                    const fileInput = document.getElementById('delivery_proof_image');
                    const failedReason = document.getElementById('failed_reason');
                    const canvas = document.getElementById('signature_canvas');
                    const selectedStatus = statusSelect.value;
                    const needsProof = selectedStatus === 'delivered' || selectedStatus === 'completed';
                    
                    if (needsProof) {
                        // Validate photo
                        if (!fileInput.files || !fileInput.files[0]) {
                            Swal.showValidationMessage('Please capture a photo of the delivered items');
                            return false;
                        }
                        
                        // Validate signature
                        const ctx = canvas.getContext('2d');
                        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        const hasSignature = imageData.data.some((channel, index) => {
                            return index % 4 !== 3 && channel !== 255; // Check if any pixel is not white
                        });
                        
                        if (!hasSignature) {
                            Swal.showValidationMessage('Please get customer signature');
                            return false;
                        }
                        
                        // Convert signature canvas to blob
                        return new Promise((resolve) => {
                            canvas.toBlob((signatureBlob) => {
                                resolve({
                                    file: fileInput.files[0],
                                    signature: signatureBlob,
                                    status: selectedStatus
                                });
                            }, 'image/png');
                        });
                    } else {
                        return {
                            status: selectedStatus,
                            reason: failedReason ? failedReason.value.trim() : ''
                        };
                    }
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({
                        title: 'Marking as Delivered...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Create FormData for file upload
                    const formData = new FormData();
                    formData.append('assignment_id', assignmentId);
                    formData.append('status', 'delivered');
                    formData.append('delivery_proof_image', result.value.file);
                    
                    // Add signature
                    if (result.value.signature) {
                        formData.append('delivery_signature', result.value.signature, 'signature.png');
                    }

                    fetch('../admin/content/delivery_update.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Delivery marked as delivered successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to mark as delivered',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while updating',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
    }


    </script>
</body>
</html>

