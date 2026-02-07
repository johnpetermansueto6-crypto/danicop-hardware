<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $drivers = $conn->query("
        SELECT 
            u.id,
            u.name,
            u.email,
            u.phone,
            COUNT(DISTINCT CASE WHEN da.status IN ('assigned', 'picked_up', 'delivering') THEN da.id END) as active_deliveries
        FROM users u
        LEFT JOIN delivery_assignments da ON u.id = da.driver_id
        WHERE u.role = 'driver'
        GROUP BY u.id
        ORDER BY u.name ASC
    ");
    
    if (!$drivers) {
        throw new Exception('Database query failed: ' . $conn->error);
    }
    
    $driverList = [];
    while ($driver = $drivers->fetch_assoc()) {
        $driverList[] = [
            'id' => (int)$driver['id'],
            'name' => $driver['name'] ?? '',
            'phone' => $driver['phone'] ?? $driver['email'] ?? 'N/A',
            'email' => $driver['email'] ?? '',
            'vehicle_type' => 'N/A',
            'active_deliveries' => (int)($driver['active_deliveries'] ?? 0)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'drivers' => $driverList
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error loading drivers: ' . $e->getMessage()
    ]);
}

