<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Initialize session arrays if not already set
if (!isset($_SESSION['debtors'])) {
    $_SESSION['debtors'] = [];
}
if (!isset($_SESSION['payments'])) {
    $_SESSION['payments'] = [];
}
if (!isset($_SESSION['deleted_demo_debtors'])) {
    $_SESSION['deleted_demo_debtors'] = [];
}
if (!isset($_SESSION['deleted_demo_payments'])) {
    $_SESSION['deleted_demo_payments'] = [];
}
if (!isset($_SESSION['notifications'])) {
    $_SESSION['notifications'] = [];
}

// Check for reset message
$show_message = false;
$message = '';
if (isset($_SESSION['reset_message'])) {
    $show_message = true;
    $message = $_SESSION['reset_message'];
    unset($_SESSION['reset_message']);
}

// Demo data
$demo_debtors = [
    ['id' => 'DEB001', 'name' => 'John Smith', 'total_debt' => 150000.00, 'status' => 'Active'],
    ['id' => 'DEB002', 'name' => 'Maria Garcia', 'total_debt' => 95250.00, 'status' => 'Active'],
    ['id' => 'DEB003', 'name' => 'Robert Johnson', 'total_debt' => 102000.00, 'status' => 'Overdue']
];

$demo_payments = [
    ['id' => 'PAY005', 'debtor' => 'Robert Johnson', 'amount' => 1200.00, 'date' => 'Jun 12, 2023', 'method' => 'Bank Transfer', 'status' => 'Pending'],
    ['id' => 'PAY001', 'debtor' => 'John Smith', 'amount' => 500.00, 'date' => 'May 15, 2023', 'method' => 'Bank Transfer', 'status' => 'Completed'],
    ['id' => 'PAY002', 'debtor' => 'Maria Garcia', 'amount' => 750.00, 'date' => 'May 14, 2023', 'method' => 'Cash', 'status' => 'Completed'],
    ['id' => 'PAY003', 'debtor' => 'Robert Johnson', 'amount' => 1200.00, 'date' => 'May 12, 2023', 'method' => 'Credit Card', 'status' => 'Completed'],
    ['id' => 'PAY004', 'debtor' => 'Michael Brown', 'amount' => 300.00, 'date' => 'May 5, 2023', 'method' => 'Check', 'status' => 'Completed']
];

// Filter out deleted demo debtors
$filtered_demo_debtors = [];
foreach ($demo_debtors as $debtor) {
    if (!in_array($debtor['id'], $_SESSION['deleted_demo_debtors'])) {
        $filtered_demo_debtors[] = $debtor;
    }
}

// Filter out deleted demo payments
$filtered_demo_payments = [];
foreach ($demo_payments as $payment) {
    if (!in_array($payment['id'], $_SESSION['deleted_demo_payments'])) {
        $filtered_demo_payments[] = $payment;
    }
}

// Combine demo data with user data
$all_debtors = array_merge($filtered_demo_debtors, $_SESSION['debtors']);
$all_payments = array_merge($filtered_demo_payments, $_SESSION['payments']);

// Calculate statistics
$totalOutstanding = 0; // Initialize to 0 instead of hardcoded amount
foreach ($filtered_demo_debtors as $debtor) {
    $totalOutstanding += $debtor['total_debt'];
}
foreach ($_SESSION['debtors'] as $debtor) {
    $totalOutstanding += $debtor['total_debt'];
}

$paymentsThisMonth = 0.00;
$currentMonth = date('m');
$currentYear = date('Y');

// Count demo payments for this month
foreach ($filtered_demo_payments as $payment) {
    if ($payment['status'] == 'Completed') {
        // Check if payment was made this month
        if (isset($payment['date'])) {
            // Try to extract month from various date formats
            if (strtotime($payment['date'])) {
                $paymentDate = strtotime($payment['date']);
                if (date('m', $paymentDate) == $currentMonth && date('Y', $paymentDate) == $currentYear) {
                    $paymentsThisMonth += $payment['amount'];
                }
            }
        }
    }
}

// Add user payments for this month
foreach ($_SESSION['payments'] as $payment) {
    if ($payment['status'] == 'Completed') {
        // Check if payment was made this month
        if (isset($payment['date'])) {
            // Try to extract month from various date formats
            if (strtotime($payment['date'])) {
                $paymentDate = strtotime($payment['date']);
                if (date('m', $paymentDate) == $currentMonth && date('Y', $paymentDate) == $currentYear) {
                    $paymentsThisMonth += $payment['amount'];
                }
            }
        }
    }
}

// Count active debtors (filtered demo + custom)
$activeDebtors = count($filtered_demo_debtors) + count($_SESSION['debtors']);

// Count overdue debts
$overdueDebts = 0;
foreach ($filtered_demo_debtors as $debtor) {
    if (isset($debtor['status']) && $debtor['status'] == 'Overdue') {
        $overdueDebts++;
    }
}
foreach ($_SESSION['debtors'] as $debtor) {
    if (isset($debtor['status']) && $debtor['status'] == 'Overdue') {
        $overdueDebts++;
    }
}

// Get the most recent 5 payments
usort($all_payments, function($a, $b) {
    // Try to convert dates for comparison
    $dateA = strtotime($a['date'] ?? '');
    $dateB = strtotime($b['date'] ?? '');
    
    // If dates can't be parsed, use the payment ID as fallback
    if (!$dateA || !$dateB) {
        return strcmp($b['id'] ?? '', $a['id'] ?? '');
    }
    
    return $dateB - $dateA; // Sort in descending order (newest first)
});

// Get the 5 most recent payments
$recentPayments = array_slice($all_payments, 0, 5);

// Count unread notifications
$unread_notifications = 0;
if (isset($_SESSION['notifications'])) {
    foreach ($_SESSION['notifications'] as $notification) {
        if (!$notification['read']) {
            $unread_notifications++;
        }
    }
}

// Set page title
$page_title = "Dashboard - DMS";

// Include header
include 'includes/header.php';
?>
<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>DMS</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="debtors.php">
                <i class="fas fa-users"></i> Debtors
            </a>
            <a href="payments.php">
                <i class="fas fa-credit-card"></i> Payments
            </a>
            <a href="financial-summary.php">
                <i class="fas fa-chart-pie"></i> Financial Summary
            </a>
            <a href="notifications.php">
                <i class="fas fa-bell"></i> Notifications
                <?php if ($unread_notifications > 0): ?>
                <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                <?php endif; ?>
            </a>
            <a href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top bar -->
        <div class="top-bar">
            <div class="menu-toggle">
                <button id="sidebar-toggle" class="toggle-btn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="welcome">
                Welcome back, <?php echo $_SESSION['username']; ?> !
            </div>
            <div class="user-avatar">
                <a href="notifications.php" class="top-notification-icon">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_notifications > 0): ?>
                    <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                    <?php endif; ?>
                </a>
                <div class="avatar">AD</div>
            </div>
        </div>
        
        <!-- Dashboard content -->
        <div class="dashboard-content">
            <h1>Dashboard</h1>
            
            <?php if($show_message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Stats cards -->
            <div class="stats-container">
                <div class="stat-card blue">
                    <div class="stat-info">
                        <h3>Total Outstanding</h3>
                        <h2>₱<?php echo number_format($totalOutstanding, 2); ?></h2>
                        <a href="debtors.php" class="stat-link">View Debts <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-info">
                        <h3>Payments This Month</h3>
                        <h2>₱<?php echo number_format($paymentsThisMonth, 2); ?></h2>
                        <a href="payments.php" class="stat-link">View Payments <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-info">
                        <h3>Active Debtors</h3>
                        <h2><?php echo $activeDebtors; ?></h2>
                        <a href="debtors.php" class="stat-link">View Debtors <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                
                <div class="stat-card teal">
                    <div class="stat-info">
                        <h3>Overdue Debts</h3>
                        <h2><?php echo $overdueDebts; ?></h2>
                        <a href="overdue.php" class="stat-link">View Overdue <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            
            <!-- Recent payments -->
            <div class="recent-payments">
                <h2>Recent Payments</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Debtor</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPayments as $payment): ?>
                            <tr>
                                <td><?php echo $payment['id']; ?></td>
                                <td><?php echo $payment['debtor']; ?></td>
                                <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo $payment['date']; ?></td>
                                <td><?php echo $payment['method']; ?></td>
                                <td>
                                    <span class="status <?php echo strtolower($payment['status']); ?>">
                                        <?php echo $payment['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// Include footer
include 'includes/footer.php';
?> 