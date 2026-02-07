<?php
require_once '../includes/config.php';

if (!isLoggedIn() || getUserRole() !== 'driver') {
    redirect('../index.php');
}

$current_user_id = $_SESSION['user_id'];

// Get driver's completed deliveries (delivered status)
$completedStmt = $conn->prepare("
    SELECT 
        da.id as assignment_id,
        da.status as delivery_status,
        da.notes as delivery_notes,
        da.created_at as assigned_at,
        da.delivery_started_at,
        da.delivery_completed_at,
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
    WHERE da.driver_id = ? AND da.status = 'delivered'
    ORDER BY da.delivery_completed_at DESC
");
$completedStmt->bind_param("i", $current_user_id);
$completedStmt->execute();
$completedDeliveries = $completedStmt->get_result();

// Get statistics
$todayStmt = $conn->prepare("
    SELECT COUNT(*) as count
    FROM delivery_assignments
    WHERE driver_id = ? AND status = 'delivered' AND DATE(delivery_completed_at) = CURDATE()
");
$todayStmt->bind_param("i", $current_user_id);
$todayStmt->execute();
$todayResult = $todayStmt->get_result()->fetch_assoc();
$todayCount = $todayResult['count'] ?? 0;

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
    <title>Completed Deliveries - Driver Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="ml-64">
        <!-- Header -->
        <nav class="bg-green-600 text-white shadow-lg">
            <div class="px-6 py-4">
                <h1 class="text-xl font-bold"><i class="fas fa-check-circle mr-2"></i>Completed Deliveries</h1>
                <p class="text-sm text-green-100 mt-1">Items successfully delivered to customers</p>
            </div>
        </nav>

        <div class="p-6">
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Completed</p>
                            <p class="text-3xl font-bold text-green-600"><?= $totalCount ?></p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-full">
                            <i class="fas fa-trophy text-green-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Today Completed</p>
                            <p class="text-3xl font-bold text-blue-600"><?= $todayCount ?></p>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-full">
                            <i class="fas fa-calendar-day text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">This Page</p>
                            <p class="text-3xl font-bold text-purple-600"><?= $completedDeliveries->num_rows ?></p>
                        </div>
                        <div class="bg-purple-100 p-4 rounded-full">
                            <i class="fas fa-list text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Deliveries Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-green-600 text-white px-6 py-4">
                    <h2 class="text-xl font-bold"><i class="fas fa-check-circle mr-2"></i>My Completed Deliveries</h2>
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
                                <th class="text-left py-3 px-4">Completed Date</th>
                                <th class="text-left py-3 px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($completedDeliveries->num_rows > 0): ?>
                                <?php while ($delivery = $completedDeliveries->fetch_assoc()): ?>
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
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-green-600">₱<?= number_format($delivery['total_amount'], 2) ?></div>
                                            <div class="text-xs text-gray-500"><?= ucfirst(str_replace('_', ' ', $delivery['payment_method'])) ?></div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="text-sm font-semibold text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <?= $delivery['delivery_completed_at'] ? date('M d, Y H:i', strtotime($delivery['delivery_completed_at'])) : 'N/A' ?>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Assigned: <?= date('M d, H:i', strtotime($delivery['assigned_at'])) ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <button onclick="viewFullDetails(<?= $delivery['assignment_id'] ?>)" 
                                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                <i class="fas fa-eye mr-1"></i> View Details
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                        <p>No completed deliveries yet</p>
                                        <p class="text-sm mt-2">Completed deliveries will appear here after you mark them as delivered</p>
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
                Swal.fire('Error', 'Failed to load delivery details', 'error');
            });
    }
    </script>
</body>
</html>

