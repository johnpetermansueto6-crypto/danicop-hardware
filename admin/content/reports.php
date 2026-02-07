<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    die('Unauthorized');
}

// Get date range
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-d');

// Sales Summary
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_sales,
    AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? AND status != 'cancelled'");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();

// Best Selling Products
$bestSellers = $conn->query("SELECT 
    p.name,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.subtotal) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN '$start_date' AND '$end_date' AND o.status != 'cancelled'
    GROUP BY p.id, p.name
    ORDER BY total_quantity DESC
    LIMIT 10");

// Orders by Status
$statusStats = $conn->query("SELECT 
    status,
    COUNT(*) as count,
    SUM(total_amount) as total
    FROM orders
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    GROUP BY status");

// Daily Sales
$dailySales = $conn->query("SELECT 
    DATE(created_at) as date,
    COUNT(*) as orders,
    SUM(total_amount) as sales
    FROM orders
    WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date' AND status != 'cancelled'
    GROUP BY DATE(created_at)
    ORDER BY date ASC");
?>
<!-- Date Filter -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form onsubmit="event.preventDefault(); loadPage('reports', {start_date: document.getElementById('start_date').value, end_date: document.getElementById('end_date').value});" class="flex flex-col md:flex-row gap-4">
        <div>
            <label class="block text-gray-700 font-bold mb-2">Start Date</label>
            <input type="date" id="start_date" value="<?= $start_date ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-gray-700 font-bold mb-2">End Date</label>
            <input type="date" id="end_date" value="<?= $end_date ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm">Total Orders</p>
        <p class="text-3xl font-bold text-blue-600"><?= $summary['total_orders'] ?? 0 ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm">Total Sales</p>
        <p class="text-3xl font-bold text-green-600">₱<?= number_format($summary['total_sales'] ?? 0, 2) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-md p-6">
        <p class="text-gray-600 text-sm">Average Order Value</p>
        <p class="text-3xl font-bold text-purple-600">₱<?= number_format($summary['avg_order_value'] ?? 0, 2) ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Orders by Status -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Orders by Status</h2>
        <div class="space-y-2">
            <?php 
            $statusStats->data_seek(0);
            while ($stat = $statusStats->fetch_assoc()): ?>
                <div class="flex justify-between items-center">
                    <span><?= ucfirst(str_replace('_', ' ', $stat['status'])) ?></span>
                    <div class="flex items-center space-x-4">
                        <span class="font-semibold"><?= $stat['count'] ?> orders</span>
                        <span class="text-gray-600">₱<?= number_format($stat['total'] ?? 0, 2) ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Best Selling Products -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Best Selling Products</h2>
        <div class="space-y-2">
            <?php if ($bestSellers->num_rows > 0): ?>
                <?php 
                $bestSellers->data_seek(0);
                while ($product = $bestSellers->fetch_assoc()): ?>
                    <div class="flex justify-between items-center border-b pb-2">
                        <div>
                            <p class="font-semibold"><?= htmlspecialchars($product['name']) ?></p>
                            <p class="text-sm text-gray-600"><?= $product['total_quantity'] ?> sold</p>
                        </div>
                        <span class="font-bold">₱<?= number_format($product['total_revenue'], 2) ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500">No sales data available</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Daily Sales Chart -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold mb-4">Daily Sales</h2>
    <canvas id="dailySalesChart" height="100"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dailySalesData = {
        labels: [<?php 
            $dailySales->data_seek(0);
            $labels = [];
            $sales = [];
            while ($row = $dailySales->fetch_assoc()) {
                $labels[] = "'" . date('M d', strtotime($row['date'])) . "'";
                $sales[] = $row['sales'];
            }
            echo implode(',', $labels);
        ?>],
        datasets: [{
            label: 'Sales (₱)',
            data: [<?= implode(',', $sales ?? []) ?>],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.1
        }]
    };
    
    // Destroy existing chart if it exists
    if (window.dailySalesChart) {
        window.dailySalesChart.destroy();
    }
    
    window.dailySalesChart = new Chart(document.getElementById('dailySalesChart'), {
        type: 'line',
        data: dailySalesData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

