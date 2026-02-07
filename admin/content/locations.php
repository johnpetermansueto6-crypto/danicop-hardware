<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || getUserRole() !== 'superadmin') {
    die('Unauthorized');
}

$error = '';
$success = '';

// Handle location update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_location'])) {
    $id = (int)$_POST['id'];
    $name = sanitize($_POST['name'] ?? '');
    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? floatval($_POST['longitude']) : null;

    // Auto-generate a simple address based on coordinates so the front-end
    // and footer have something to display, without asking the admin to type it.
    $address = '';
    if ($latitude !== null && $longitude !== null) {
        $address = 'Pinned location (' . number_format($latitude, 6) . ', ' . number_format($longitude, 6) . ')';
    }
    
    if (empty($name)) {
        $error = 'Location name is required';
    } elseif ($latitude === null || $longitude === null) {
        $error = 'Please click on the map to set the location.';
    } else {
        if ($id > 0) {
            // Update existing
            $stmt = $conn->prepare("UPDATE store_locations SET name = ?, address = ?, latitude = ?, longitude = ? WHERE id = ?");
            $stmt->bind_param("ssddi", $name, $address, $latitude, $longitude, $id);
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO store_locations (name, address, latitude, longitude) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssdd", $name, $address, $latitude, $longitude);
        }
        
        if ($stmt->execute()) {
            $success = $id > 0 ? 'Location updated successfully' : 'Location added successfully';
            echo '<script>setTimeout(() => loadPage("locations"), 1500);</script>';
        } else {
            $error = 'Failed to save location';
        }
    }
}

// Handle location deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM store_locations WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = 'Location deleted successfully';
    } else {
        $error = 'Failed to delete location';
    }
}

// Get all locations
$locations = $conn->query("SELECT * FROM store_locations ORDER BY is_active DESC, name ASC");

// Get location to edit
$editLocation = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM store_locations WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editLocation = $stmt->get_result()->fetch_assoc();
}

// Determine if the form should be shown (edit mode or explicit "add" action)
$showForm = $editLocation || (isset($_GET['add']) && $_GET['add'] == '1');
?>
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Store Locations</h2>
    <button onclick="showAddForm(); return false;" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus"></i> Add Location
    </button>
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

<!-- Add/Edit Form -->
<div id="locationForm" class="bg-white rounded-lg shadow-md p-6 mb-6" style="display: <?= $showForm ? 'block' : 'none' ?>;">
    <h3 class="text-xl font-bold mb-4"><?= $editLocation ? 'Edit Location' : 'Add New Location' ?></h3>
    <form method="POST" action="content/locations.php" onsubmit="handleFormSubmit(event, this, 'locations'); return false;">
        <input type="hidden" name="id" value="<?= $editLocation['id'] ?? 0 ?>">

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Location Name *</label>
            <input type="text" name="name" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                   value="<?= htmlspecialchars($editLocation['name'] ?? '') ?>">
        </div>

        <!-- Hidden fields to store coordinates set via the map -->
        <input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($editLocation['latitude'] ?? '') ?>">
        <input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($editLocation['longitude'] ?? '') ?>">
        
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Map (click to set location)</label>
            <div id="map" style="height: 300px; width: 100%; border-radius: 8px; overflow: hidden; border: 2px solid #e5e7eb;"></div>
            <p class="text-xs text-gray-500 mt-1">
                <i class="fas fa-info-circle"></i> Click on the map or drag the marker to choose the store location.
            </p>
        </div>
        
        <div class="flex gap-4">
            <button type="button" onclick="loadPage('locations'); return false;" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
                Cancel
            </button>
            <button type="submit" name="update_location" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                <?= $editLocation ? 'Update' : 'Add' ?> Location
            </button>
        </div>
    </form>
</div>

<!-- Locations List -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left py-3 px-4">Name</th>
                    <th class="text-left py-3 px-4">Address</th>
                    <th class="text-left py-3 px-4">Phone</th>
                    <th class="text-left py-3 px-4">Coordinates</th>
                    <th class="text-left py-3 px-4">Status</th>
                    <th class="text-left py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($locations->num_rows > 0): ?>
                    <?php while ($location = $locations->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($location['name']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($location['address']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($location['phone'] ?? 'N/A') ?></td>
                            <td class="py-3 px-4 text-sm">
                                <?php if ($location['latitude'] && $location['longitude']): ?>
                                    <?= number_format($location['latitude'], 6) ?>, <?= number_format($location['longitude'], 6) ?>
                                <?php else: ?>
                                    <span class="text-gray-400">Not set</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded text-sm <?= $location['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $location['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex space-x-2">
                                    <a href="#" onclick="loadPage('locations', {edit: <?= $location['id'] ?>}); return false;" 
                                       class="text-blue-600 hover:underline">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="#" onclick="if(confirm('Are you sure?')) { loadPage('locations', {delete: <?= $location['id'] ?>}); } return false;" 
                                       class="text-red-600 hover:underline">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-500">No locations found. Add your first location!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

