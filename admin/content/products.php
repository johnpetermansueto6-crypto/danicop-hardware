<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

$error = '';
$success = '';

// Handle product deletion via AJAX
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get product name for response
    $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $productName = $product['name'] ?? 'Product';
    
    // Delete the product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Product '{$productName}' deleted successfully"
        ]);
        exit;
    } else {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete product'
        ]);
        exit;
    }
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Products</h2>
    <a href="#" onclick="loadPage('product_add'); return false;" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus"></i> Add Product
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
                    <th class="text-left py-3 px-4">Image</th>
                    <th class="text-left py-3 px-4">Name</th>
                    <th class="text-left py-3 px-4">Category</th>
                    <th class="text-left py-3 px-4">Price</th>
                    <th class="text-left py-3 px-4">Stock</th>
                    <th class="text-left py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products->num_rows > 0): ?>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <?php 
                                $imagePath = '';
                                if ($product['image']) {
                                    // Check file exists on server
                                    $fullPath = __DIR__ . '/../../uploads/' . $product['image'];
                                    
                                    if (file_exists($fullPath)) {
                                        // Path relative to admin/index.php (where content is loaded)
                                        $imagePath = '../uploads/' . htmlspecialchars($product['image']);
                                    }
                                }
                                ?>
                                <?php if ($imagePath): ?>
                                    <img src="<?= $imagePath ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="w-16 h-16 object-cover rounded border border-gray-200"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-16 h-16 bg-gray-200 rounded flex items-center justify-center\'><i class=\'fas fa-box text-gray-400\'></i></div>';">
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
                                <span class="px-2 py-1 rounded text-sm <?= $product['stock'] < 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                    <?= $product['stock'] ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex space-x-2">
                                    <button onclick="window.editProduct(<?= $product['id'] ?>)" 
                                       class="text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 cursor-pointer">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button onclick="window.deleteProduct(<?= $product['id'] ?>, '<?= addslashes(htmlspecialchars($product['name'])) ?>')" 
                                       class="text-red-600 hover:text-red-800 hover:underline flex items-center gap-1 cursor-pointer">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
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

