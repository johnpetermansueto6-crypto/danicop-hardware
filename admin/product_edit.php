<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Get product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    redirect('products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    
    if (empty($name) || empty($category) || $price <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        $image = $product['image'];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__) . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Get file extension and normalize it
            $fileName = $_FILES['image']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Normalize jpeg to jpg for consistency
            if ($fileExt === 'jpeg') {
                $fileExt = 'jpg';
            }
            
            $allowedExts = ['jpg', 'png', 'gif', 'webp'];
            
            // Also check MIME type for additional security
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);
            
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            
            if (in_array($fileExt, $allowedExts) && in_array($mimeType, $allowedMimes)) {
                // Delete old image
                if ($image && file_exists($uploadDir . $image)) {
                    unlink($uploadDir . $image);
                }
                
                $image = uniqid() . '.' . $fileExt;
                $uploadPath = $uploadDir . $image;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    // Verify file was uploaded successfully
                    if (!file_exists($uploadPath)) {
                        $error = 'Image upload failed. Please try again.';
                        $image = $product['image']; // Keep old image
                    }
                } else {
                    $error = 'Failed to move uploaded file. Please check uploads directory permissions.';
                    $image = $product['image']; // Keep old image
                }
            } else {
                $error = 'Invalid image format. Please upload JPG, PNG, GIF, or WEBP files only.';
                $image = $product['image']; // Keep old image
            }
        }
        
        $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sssdisi", $name, $category, $description, $price, $stock, $image, $id);
        
        if ($stmt->execute()) {
            $success = 'Product updated successfully';
            header("refresh:2;url=products.php");
        } else {
            $error = 'Failed to update product';
        }
    }
}

// Get existing categories
$categoriesResult = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
$existingCategories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $existingCategories[] = $row['category'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Danicop Hardware</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="../index.php" class="text-xl font-bold">🔧 Danicop Hardware</a>
                <div class="flex items-center space-x-4">
                    <a href="products.php" class="hover:underline">Back to Products</a>
                    <a href="../auth/logout.php" class="hover:underline">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <h1 class="text-3xl font-bold mb-6">Edit Product</h1>
        
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
        
        <form method="POST" action="product_edit.php?id=<?= $id ?>" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Product Name *</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       value="<?= htmlspecialchars($product['name']) ?>">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Category *</label>
                <input type="text" name="category" list="categories" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       value="<?= htmlspecialchars($product['category']) ?>">
                <datalist id="categories">
                    <?php foreach ($existingCategories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Description</label>
                <textarea name="description" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Price (₱) *</label>
                    <input type="number" name="price" step="0.01" min="0" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           value="<?= htmlspecialchars($product['price']) ?>">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Stock *</label>
                    <input type="number" name="stock" min="0" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           value="<?= htmlspecialchars($product['stock']) ?>">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Product Image</label>
                <?php if ($product['image'] && file_exists('../uploads/' . $product['image'])): ?>
                    <div class="mb-2">
                        <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" 
                             alt="Current image" 
                             class="w-32 h-32 object-cover rounded">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <p class="text-sm text-gray-500 mt-1">Leave empty to keep current image</p>
            </div>
            
            <div class="flex gap-4">
                <a href="products.php" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 text-center">
                    Cancel
                </a>
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</body>
</html>

