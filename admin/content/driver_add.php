<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $status = sanitize($_POST['status'] ?? 'available');
    $vehicle_type = sanitize($_POST['vehicle_type'] ?? '');
    $license_number = sanitize($_POST['license_number'] ?? '');
    
    if (empty($name) || empty($phone)) {
        $error = 'Name and phone are required';
    } else {
        $stmt = $conn->prepare("INSERT INTO drivers (name, phone, email, status, vehicle_type, license_number) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $phone, $email, $status, $vehicle_type, $license_number);
        
        if ($stmt->execute()) {
            $success = 'Driver added successfully';
            echo '<script>setTimeout(() => loadPage("drivers"), 1500);</script>';
        } else {
            $error = 'Failed to add driver';
        }
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Add New Driver</h2>
    <a href="#" onclick="loadPage('drivers'); return false;" class="text-blue-600 hover:underline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Drivers
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

<form method="POST" action="content/driver_add.php" class="bg-white rounded-lg shadow-md p-6 max-w-2xl" onsubmit="handleFormSubmit(event, this, 'drivers'); return false;">
    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Driver Name *</label>
        <input type="text" name="name" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-gray-700 font-bold mb-2">Phone Number *</label>
            <input type="text" name="phone" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
        
        <div>
            <label class="block text-gray-700 font-bold mb-2">Email</label>
            <input type="email" name="email"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-gray-700 font-bold mb-2">Status *</label>
            <select name="status" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="available" <?= ($_POST['status'] ?? 'available') === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="off_duty" <?= ($_POST['status'] ?? '') === 'off_duty' ? 'selected' : '' ?>>Off Duty</option>
                <option value="unavailable" <?= ($_POST['status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
            </select>
        </div>
        
        <div>
            <label class="block text-gray-700 font-bold mb-2">Vehicle Type</label>
            <input type="text" name="vehicle_type" list="vehicleTypes"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                   value="<?= htmlspecialchars($_POST['vehicle_type'] ?? '') ?>">
            <datalist id="vehicleTypes">
                <option value="Motorcycle">
                <option value="Van">
                <option value="Truck">
                <option value="Car">
                <option value="Bicycle">
            </datalist>
        </div>
    </div>
    
    <div class="mb-6">
        <label class="block text-gray-700 font-bold mb-2">License Number</label>
        <input type="text" name="license_number"
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
               value="<?= htmlspecialchars($_POST['license_number'] ?? '') ?>">
    </div>
    
    <div class="flex gap-4">
        <button type="button" onclick="loadPage('drivers')" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
            Cancel
        </button>
        <button type="submit" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
            Add Driver
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
        const contentContainer = document.getElementById('content-container');
        contentContainer.innerHTML = html;
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}
</script>

