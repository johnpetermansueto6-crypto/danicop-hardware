<?php
require_once '../../includes/config.php';
require_once '../../includes/mailer.php';

if (!isLoggedIn() || getUserRole() !== 'superadmin') {
    die('Unauthorized');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'staff');
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            if (!in_array($role, ['staff', 'superadmin', 'driver'], true)) {
                $role = 'staff';
            }
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, email_verified) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
            
            if ($stmt->execute()) {
                // Send email with credentials if role is driver
                if ($role === 'driver') {
                    $emailSubject = 'Your Driver Account Credentials - Danicop Hardware';
                    $emailBody = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background: #2563eb; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                                .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                                .credentials { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #2563eb; }
                                .credential-item { margin: 10px 0; }
                                .label { font-weight: bold; color: #4b5563; }
                                .value { color: #1f2937; font-size: 16px; }
                                .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
                                .warning { background: #fef3c7; border: 1px solid #fbbf24; padding: 15px; border-radius: 8px; margin: 20px 0; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>🚚 Driver Account Created</h1>
                                </div>
                                <div class='content'>
                                    <p>Hello <strong>{$name}</strong>,</p>
                                    <p>Your driver account has been created successfully. Please use the following credentials to log in:</p>
                                    
                                    <div class='credentials'>
                                        <div class='credential-item'>
                                            <span class='label'>Name:</span>
                                            <div class='value'>{$name}</div>
                                        </div>
                                        <div class='credential-item'>
                                            <span class='label'>Email:</span>
                                            <div class='value'>{$email}</div>
                                        </div>
                                        <div class='credential-item'>
                                            <span class='label'>Password:</span>
                                            <div class='value'>{$password}</div>
                                        </div>
                                    </div>
                                    
                                    <div class='warning'>
                                        <strong>⚠️ Important:</strong> Please keep these credentials secure. You can change your password after logging in.
                                    </div>
                                    
                                    <p><strong>Login URL:</strong> <a href='http://mwa/hardware/auth/login.php'>http://mwa/hardware/auth/login.php</a></p>
                                    
                                    <p>Once you log in, you will be able to view your assigned deliveries and customer information.</p>
                                    
                                    <p>Best regards,<br>Danicop Hardware Team</p>
                                </div>
                                <div class='footer'>
                                    <p>This is an automated email. Please do not reply.</p>
                                </div>
                            </div>
                        </body>
                        </html>
                    ";
                    
                    $emailSent = send_app_email($email, $emailSubject, $emailBody);
                    
                    if ($emailSent) {
                        $success = 'Driver account created successfully. Credentials have been sent to ' . htmlspecialchars($email) . '.';
                    } else {
                        $success = 'Driver account created successfully, but email could not be sent. Please provide credentials manually.';
                    }
                } else {
                    $success = 'Staff member added successfully.';
                }
            } else {
                $error = 'Failed to add staff member';
            }
        }
    }
}
?>
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Add Staff Member</h2>
        <p class="text-sm text-gray-500 mt-1">Create a new staff or super admin account for the system.</p>
    </div>
    <a href="#" onclick="loadPage('users'); return false;" class="text-sm text-blue-600 hover:underline">
        &larr; Back to Staff
    </a>
</div>

<div class="flex justify-center">
    <div class="w-full max-w-xl">
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-start space-x-2">
                <i class="fas fa-exclamation-circle mt-1"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-300 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-start space-x-2">
                <i class="fas fa-check-circle mt-1"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="content/user_add.php"
              onsubmit="handleFormSubmit(event, this, 'user_add'); return false;"
              class="bg-white rounded-2xl shadow-lg p-6 md:p-8 border border-gray-100">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Full Name *</label>
                <input type="text" name="name" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Email *</label>
                <input type="email" name="email" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Password *</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Role *</label>
                <select name="role" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="staff" <?= ($_POST['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
                    <option value="driver" <?= ($_POST['role'] ?? '') === 'driver' ? 'selected' : '' ?>>Driver</option>
                    <option value="superadmin" <?= ($_POST['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Note: Driver accounts will receive login credentials via email</p>
            </div>
            
            <div class="flex gap-4">
                <button type="button"
                        onclick="loadPage('users'); return false;"
                        class="flex-1 bg-gray-100 text-gray-700 py-2.5 rounded-lg hover:bg-gray-200 text-sm font-medium">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 text-sm font-semibold shadow-sm">
                    Add Staff
                </button>
            </div>
        </form>
    </div>
</div>

