<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

// Mark as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $id = (int)$_GET['read'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
}

// Mark all as read
if (isset($_GET['read_all'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    // Reload page
    echo '<script>loadPage("notifications");</script>';
    exit;
}

// Get notifications
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = {$_SESSION['user_id']} ORDER BY created_at DESC");
?>
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Notifications</h2>
    <a href="#" onclick="loadPage('notifications', {read_all: 1}); return false;" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
        Mark All as Read
    </a>
</div>

<div class="space-y-4">
    <?php if ($notifications->num_rows > 0): ?>
        <?php while ($notif = $notifications->fetch_assoc()): ?>
            <div class="bg-white rounded-lg shadow-md p-6 <?= $notif['is_read'] ? 'opacity-75' : 'border-l-4 border-blue-600' ?>">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                <?php
                                switch($notif['type']) {
                                    case 'low_stock': echo 'bg-red-100 text-red-800'; break;
                                    case 'new_order': echo 'bg-green-100 text-green-800'; break;
                                    case 'order_update': echo 'bg-blue-100 text-blue-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?= ucfirst(str_replace('_', ' ', $notif['type'])) ?>
                            </span>
                            <?php if (!$notif['is_read']): ?>
                                <span class="bg-blue-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">!</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-gray-800"><?= htmlspecialchars($notif['message']) ?></p>
                        <p class="text-sm text-gray-500 mt-2">
                            <?= date('M d, Y h:i A', strtotime($notif['created_at'])) ?>
                        </p>
                    </div>
                    <?php if (!$notif['is_read']): ?>
                        <a href="#" onclick="loadPage('notifications', {read: <?= $notif['id'] ?>}); return false;" class="text-blue-600 hover:underline ml-4">
                            Mark as Read
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-bell-slash text-6xl text-gray-400 mb-4"></i>
            <p class="text-xl text-gray-600">No notifications</p>
        </div>
    <?php endif; ?>
</div>

