<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    
    if (empty($name) || empty($category) || $price <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__) . '/../uploads/';
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
                $image = uniqid() . '.' . $fileExt;
                $uploadPath = $uploadDir . $image;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    // Verify file was uploaded successfully
                    if (file_exists($uploadPath)) {
                        // File uploaded successfully
                    } else {
                        $error = 'Image upload failed. Please try again.';
                        $image = '';
                    }
                } else {
                    $error = 'Failed to move uploaded file. Please check uploads directory permissions.';
                    $image = '';
                }
            } else {
                $error = 'Invalid image format. Please upload JPG, PNG, GIF, or WEBP files only.';
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO products (name, category, description, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdis", $name, $category, $description, $price, $stock, $image);
        
        if ($stmt->execute()) {
            $success = 'Product added successfully';
            echo '<script>setTimeout(() => loadPage("products"), 1500);</script>';
        } else {
            $error = 'Failed to add product';
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
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Add New Product</h2>
    <a href="#" onclick="loadPage('products'); return false;" class="text-blue-600 hover:underline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Products
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

<form method="POST" action="content/product_add.php" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6 max-w-2xl" onsubmit="handleFormSubmit(event, this, 'products'); return false;">
    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Product Name *</label>
        <input type="text" name="name" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    </div>
    
    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Category *</label>
        <input type="text" name="category" list="categories" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
               value="<?= htmlspecialchars($_POST['category'] ?? '') ?>">
        <datalist id="categories">
            <?php foreach ($existingCategories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>">
            <?php endforeach; ?>
        </datalist>
    </div>
    
    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Description</label>
        <textarea name="description" rows="4"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-gray-700 font-bold mb-2">Price (₱) *</label>
            <input type="number" name="price" step="0.01" min="0" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                   value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
        </div>
        
        <div>
            <label class="block text-gray-700 font-bold mb-2">Stock *</label>
            <input type="number" name="stock" min="0" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                   value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>">
        </div>
    </div>
    
    <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">Product Image</label>
        <input type="file" name="image" accept="image/*"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        <p class="text-sm text-gray-500 mt-1">Accepted formats: JPG, PNG, GIF</p>
    </div>
    
    <div class="flex gap-4">
        <button type="button" onclick="loadPage('products')" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
            Cancel
        </button>
        <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
            Add Product
        </button>
    </div>
</form>

<script>
function handleFormSubmit(event, form, redirectPage) {
    event.preventDefault();
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        // Replace content with response (which may include success message and redirect script)
        const contentContainer = document.getElementById('main-content').querySelector('[x-show="!loading"]');
        contentContainer.innerHTML = html;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>

