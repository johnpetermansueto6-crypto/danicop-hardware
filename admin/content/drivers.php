<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

$error = '';
$success = '';

// Handle driver deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if driver has active deliveries
    $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM delivery_assignments WHERE driver_id = ? AND status IN ('assigned', 'picked_up', 'delivering')");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result()->fetch_assoc();
    
    if ($checkResult['count'] > 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete driver with active deliveries'
        ]);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM drivers WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Driver deleted successfully'
        ]);
        exit;
    } else {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete driver'
        ]);
        exit;
    }
}

// Get all drivers with statistics
$drivers = $conn->query("
    SELECT 
        d.*,
        COUNT(DISTINCT CASE WHEN da.status IN ('assigned', 'picked_up', 'delivering') THEN da.id END) as active_deliveries,
        COUNT(DISTINCT CASE WHEN da.status = 'delivered' THEN da.id END) as completed_deliveries,
        MAX(da.delivery_completed_at) as last_delivery
    FROM drivers d
    LEFT JOIN delivery_assignments da ON d.id = da.driver_id
    GROUP BY d.id
    ORDER BY d.name ASC
");
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Driver Management</h2>
    <a href="#" onclick="loadPage('driver_add'); return false;" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i> Add Driver
    </a>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left py-3 px-4">Name</th>
                    <th class="text-left py-3 px-4">Contact</th>
                    <th class="text-left py-3 px-4">Vehicle</th>
                    <th class="text-left py-3 px-4">Status</th>
                    <th class="text-left py-3 px-4">Active</th>
                    <th class="text-left py-3 px-4">Completed</th>
                    <th class="text-left py-3 px-4">Total</th>
                    <th class="text-left py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($drivers->num_rows > 0): ?>
                    <?php while ($driver = $drivers->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($driver['name']) ?></td>
                            <td class="py-3 px-4">
                                <div><?= htmlspecialchars($driver['phone']) ?></div>
                                <?php if ($driver['email']): ?>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($driver['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <div><?= htmlspecialchars($driver['vehicle_type'] ?? 'N/A') ?></div>
                                <?php if ($driver['license_number']): ?>
                                    <div class="text-xs text-gray-500">License: <?= htmlspecialchars($driver['license_number']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded text-xs font-semibold <?php
                                    echo match($driver['status']) {
                                        'available' => 'bg-green-100 text-green-800',
                                        'delivering' => 'bg-yellow-100 text-yellow-800',
                                        'off_duty' => 'bg-gray-100 text-gray-800',
                                        'unavailable' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>">
                                    <?= ucfirst(str_replace('_', ' ', $driver['status'])) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded text-xs <?= $driver['active_deliveries'] > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $driver['active_deliveries'] ?>
                                </span>
                            </td>
                            <td class="py-3 px-4"><?= $driver['completed_deliveries'] ?></td>
                            <td class="py-3 px-4 font-semibold"><?= $driver['total_deliveries'] ?></td>
                            <td class="py-3 px-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewDriverDetails(<?= $driver['id'] ?>)" 
                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button onclick="loadPage('driver_edit', {id: <?= $driver['id'] ?>})" 
                                            class="text-green-600 hover:text-green-800 text-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button onclick="deleteDriver(<?= $driver['id'] ?>, '<?= addslashes(htmlspecialchars($driver['name'])) ?>')" 
                                            class="text-red-600 hover:text-red-800 text-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">No drivers found. Add your first driver!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteDriver(id, driverName) {
    Swal.fire({
        title: 'Are you sure?',
        html: `<p>Do you want to delete driver <strong>"${driverName}"</strong>?</p><p class="text-red-600 text-sm mt-2">This action cannot be undone!</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash mr-2"></i>Yes, delete!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`content/drivers.php?delete=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            loadPage('drivers');
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Failed to delete driver',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }
    });
}

function viewDriverDetails(driverId) {
    loadPage('driver_view', {id: driverId});
}
</script>

