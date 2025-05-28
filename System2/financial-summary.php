<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Demo data
$monthlyCollections = [
    'Jan' => 15000,
    'Feb' => 22500,
    'Mar' => 18750,
    'Apr' => 25000,
    'May' => 31200,
    'Jun' => 0
];

$debtorDistribution = [
    'John Smith' => 150000.00,
    'Maria Garcia' => 95250.00,
    'Robert Johnson' => 102000.00
];

$paymentMethods = [
    'Bank Transfer' => 60,
    'Credit Card' => 25,
    'Cash' => 10,
    'Check' => 5
];

// Count unread notifications
$unread_notifications = 0;
if (isset($_SESSION['notifications'])) {
    foreach ($_SESSION['notifications'] as $notification) {
        if (!$notification['read']) {
            $unread_notifications++;
        }
    }
}

// Set page title and custom scripts for chart.js
$page_title = "Financial Summary - DMS";
$extra_scripts = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

// Include header
include 'includes/header.php';
?>
<div class="dashboard-container">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top bar -->
        <?php include 'includes/topbar.php'; ?>
        
        <!-- Dashboard content -->
        <div class="dashboard-content">
            <h1>Financial Summary</h1>
            
            <div class="charts-container">
                <div class="chart-card">
                    <h3>Monthly Collections</h3>
                    <div class="chart-wrapper">
                        <canvas id="monthlyCollectionsChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <h3>Debt Distribution by Debtor</h3>
                    <div class="chart-wrapper">
                        <canvas id="debtDistributionChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-card">
                    <h3>Payment Methods</h3>
                    <div class="chart-wrapper">
                        <canvas id="paymentMethodsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Add chart.js code for the footer
$before_body_close = "
<script>
    // Monthly Collections Chart
    const monthlyCollectionsChart = new Chart(
        document.getElementById('monthlyCollectionsChart'),
        {
            type: 'bar',
            data: {
                labels: " . json_encode(array_keys($monthlyCollections)) . ",
                datasets: [{
                    label: 'Collections (₱)',
                    data: " . json_encode(array_values($monthlyCollections)) . ",
                    backgroundColor: '#1a73e8',
                    borderColor: '#1558b9',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        }
    );
    
    // Debt Distribution Chart
    const debtDistributionChart = new Chart(
        document.getElementById('debtDistributionChart'),
        {
            type: 'pie',
            data: {
                labels: " . json_encode(array_keys($debtorDistribution)) . ",
                datasets: [{
                    data: " . json_encode(array_values($debtorDistribution)) . ",
                    backgroundColor: ['#1a73e8', '#34c759', '#ff9500']
                }]
            },
            options: {
                responsive: true
            }
        }
    );
    
    // Payment Methods Chart
    const paymentMethodsChart = new Chart(
        document.getElementById('paymentMethodsChart'),
        {
            type: 'doughnut',
            data: {
                labels: " . json_encode(array_keys($paymentMethods)) . ",
                datasets: [{
                    data: " . json_encode(array_values($paymentMethods)) . ",
                    backgroundColor: ['#1a73e8', '#34c759', '#ff9500', '#00c7be']
                }]
            },
            options: {
                responsive: true
            }
        }
    );
</script>
";

// Include footer
include 'includes/footer.php';
?> 