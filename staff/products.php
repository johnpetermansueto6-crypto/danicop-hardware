<?php
require_once '../includes/config.php';

if (!isLoggedIn() || getUserRole() !== 'staff') {
    redirect('../index.php');
}

$error = '';
$success = '';

// Unread notifications for sidebar badge
$unread_notifications = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE is_read = 0 AND user_id = {$_SESSION['user_id']}");
if ($result) {
    $row = $result->fetch_assoc();
    $unread_notifications = $row['total'] ?? 0;
}

// Handle stock update only (staff cannot delete products)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id = (int)$_POST['product_id'];
    $stock = (int)$_POST['stock'];
    
    if ($stock < 0) {
        $error = 'Stock cannot be negative';
    } else {
        $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->bind_param("ii", $stock, $id);
        
        if ($stmt->execute()) {
            $success = 'Stock updated successfully';
            
            // Create low stock notification if stock is low
            if ($stock < 10) {
                $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $product = $result->fetch_assoc();
                
                // Notify all admins
                $admins = $conn->query("SELECT id FROM users WHERE role IN ('superadmin', 'staff')");
                while ($admin = $admins->fetch_assoc()) {
                    $stmt = $conn->prepare("INSERT INTO notifications (type, message, user_id) VALUES ('low_stock', ?, ?)");
                    $message = "Low stock alert: {$product['name']} has only {$stock} units remaining";
                    $stmt->bind_param("si", $message, $admin['id']);
                    $stmt->execute();
                }
            }
        } else {
            $error = 'Failed to update stock';
        }
    }
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY stock ASC, created_at DESC");

$current_page = 'products';
$page_title = 'Update Stock';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Stock - Staff - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
<?php include '../includes/staff_sidebar.php'; ?>

    <div class="space-y-6">
        <div class="flex justify-between items-center mb-2">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Update Stock</h1>
                <p class="text-gray-600 mt-1">Manage product inventory levels</p>
            </div>
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
                            <th class="text-left py-3 px-4">Image</th>
                            <th class="text-left py-3 px-4">Name</th>
                            <th class="text-left py-3 px-4">Category</th>
                            <th class="text-left py-3 px-4">Price</th>
                            <th class="text-left py-3 px-4">Current Stock</th>
                            <th class="text-left py-3 px-4">Update Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4">
                                        <?php if ($product['image'] && file_exists(dirname(__DIR__) . '/uploads/' . $product['image'])): ?>
                                            <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                                 class="w-16 h-16 object-cover rounded">
                                        <?php else: ?>
                                            <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                                <i class="fas fa-box text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($product['name']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($product['category']) ?></td>
                                    <td class="py-3 px-4">₱<?= number_format($product['price'], 2) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded text-sm font-semibold <?= $product['stock'] < 10 ? 'bg-red-100 text-red-800' : ($product['stock'] < 20 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') ?>">
                                            <?= $product['stock'] ?> units
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <form method="POST" action="products.php" class="flex items-center space-x-2">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <input type="number" name="stock" value="<?= $product['stock'] ?>" min="0" required
                                                   class="w-24 px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                            <button type="submit" name="update_stock" 
                                                    class="bg-green-600 text-white px-4 py-1 rounded-lg hover:bg-green-700">
                                                <i class="fas fa-save"></i> Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-500">No products found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Note:</strong> Staff can only update stock levels. To add, edit, or delete products, contact an administrator.
            </p>
        </div>
    </div>

    </main>
    </div>
    </div>
</body>
</html>

