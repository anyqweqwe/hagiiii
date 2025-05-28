<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if a success message should be displayed
$show_message = false;
$message = '';
$message_type = 'success';

if (isset($_SESSION['payment_added']) && $_SESSION['payment_added']) {
    $show_message = true;
    $message = $_SESSION['payment_message'] ?? 'Payment has been recorded successfully!';
    // Clear the session variables
    $_SESSION['payment_added'] = false;
    $_SESSION['payment_message'] = '';
} elseif (isset($_SESSION['payment_error'])) {
    $show_message = true;
    $message = $_SESSION['payment_error'];
    $message_type = 'danger';
    // Clear the error message
    unset($_SESSION['payment_error']);
}

// Initialize deleted demo payments array if it doesn't exist
if (!isset($_SESSION['deleted_demo_payments'])) {
    $_SESSION['deleted_demo_payments'] = [];
}

// Demo data - same as dashboard
$demo_payments = [
    ['id' => 'PAY005', 'debtor' => 'Robert Johnson', 'amount' => 1200.00, 'date' => 'Jun 12, 2023', 'method' => 'Bank Transfer', 'status' => 'Pending'],
    ['id' => 'PAY001', 'debtor' => 'John Smith', 'amount' => 500.00, 'date' => 'May 15, 2023', 'method' => 'Bank Transfer', 'status' => 'Completed'],
    ['id' => 'PAY002', 'debtor' => 'Maria Garcia', 'amount' => 750.00, 'date' => 'May 14, 2023', 'method' => 'Cash', 'status' => 'Completed'],
    ['id' => 'PAY003', 'debtor' => 'Robert Johnson', 'amount' => 1200.00, 'date' => 'May 12, 2023', 'method' => 'Credit Card', 'status' => 'Completed'],
    ['id' => 'PAY004', 'debtor' => 'Michael Brown', 'amount' => 300.00, 'date' => 'May 5, 2023', 'method' => 'Check', 'status' => 'Completed']
];

// Filter out deleted demo payments
$filtered_demo_payments = [];
foreach ($demo_payments as $payment) {
    if (!in_array($payment['id'], $_SESSION['deleted_demo_payments'])) {
        $filtered_demo_payments[] = $payment;
    }
}

// Combine filtered demo data with user-added payments from session
$payments = $filtered_demo_payments;

// Add user-created payments from session if they exist
if (isset($_SESSION['payments']) && is_array($_SESSION['payments'])) {
    $payments = array_merge($payments, $_SESSION['payments']);
}

// Initialize search and filter variables
$search_term = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$method_filter = isset($_GET['method']) ? $_GET['method'] : 'all';

// Apply search filter if search term provided
if ($search_term !== '') {
    $filtered_payments = [];
    foreach ($payments as $payment) {
        // Search in id, debtor fields
        if (stripos($payment['id'], $search_term) !== false || 
            stripos($payment['debtor'], $search_term) !== false) {
            $filtered_payments[] = $payment;
        }
    }
    $payments = $filtered_payments;
}

// Apply status filter if not set to 'all'
if ($status_filter !== 'all') {
    $filtered_payments = [];
    foreach ($payments as $payment) {
        if ($payment['status'] === $status_filter) {
            $filtered_payments[] = $payment;
        }
    }
    $payments = $filtered_payments;
}

// Apply payment method filter if not set to 'all'
if ($method_filter !== 'all') {
    $filtered_payments = [];
    foreach ($payments as $payment) {
        if ($payment['method'] === $method_filter) {
            $filtered_payments[] = $payment;
        }
    }
    $payments = $filtered_payments;
}

// Get unique payment methods for the filter dropdown
$payment_methods = ['Bank Transfer', 'Credit Card', 'Cash', 'Check'];

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
$page_title = "Payments - DMS";

// Include header
include 'includes/header.php';

$print_mode = isset($_GET['print']) && $_GET['print'] == '1';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <?php if ($print_mode): ?>
    <style>
        @media print {
            body { background: white; }
        }
        .sidebar, .top-bar, .action-buttons, .search-filter-container, .debug-info, .action-links, .menu-toggle {
            display: none !important;
        }
        .dashboard-container, .main-content, .dashboard-content { width: 100% !important; margin: 0 !important; padding: 0 !important; }
    </style>
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 200);
            window.onafterprint = function() { window.close(); };
        };
    </script>
    <?php endif; ?>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top bar -->
        <?php include 'includes/topbar.php'; ?>
        
        <!-- Dashboard content -->
        <div class="dashboard-content">
            <h1>Payments</h1>
            
            <?php if($show_message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="add-payment.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Record New Payment
                </a>
            </div>
            
            <!-- Search and Filter section -->
            <div class="search-filter-container">
                <form method="GET" action="payments.php" class="search-form">
                    <div class="search-input-container">
                        <input type="text" name="search" placeholder="Search by ID or debtor name..." value="<?php echo $search_term; ?>" class="search-input">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="filter-container">
                        <label for="status-filter">Status:</label>
                        <select name="status" id="status-filter" onchange="this.form.submit()">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            <option value="in_dispute" <?php echo $status_filter === 'in_dispute' ? 'selected' : ''; ?>>In Dispute</option>
                            <option value="under_review" <?php echo $status_filter === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                        </select>
                        
                        <label for="method-filter">Method:</label>
                        <select name="method" id="method-filter" onchange="this.form.submit()">
                            <option value="all" <?php echo $method_filter === 'all' ? 'selected' : ''; ?>>All</option>
                            <?php foreach ($payment_methods as $method): ?>
                            <option value="<?php echo $method; ?>" <?php echo $method_filter === $method ? 'selected' : ''; ?>>
                                <?php echo $method; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if($search_term !== '' || $status_filter !== 'all' || $method_filter !== 'all'): ?>
                    <div class="clear-filter">
                        <a href="payments.php">Clear filters</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- For debugging purposes -->
            <div class="debug-info" style="margin-bottom: 10px; font-size: 0.8rem; color: #999;">
                Total payments: <?php echo count($payments); ?> 
                (<?php echo isset($_SESSION['payments']) ? count($_SESSION['payments']) : 0; ?> custom)
                <?php if($search_term !== '' || $status_filter !== 'all' || $method_filter !== 'all'): ?>
                | Filtered results
                <?php endif; ?>
            </div>
            
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
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($payments) > 0): ?>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo $payment['id']; ?></td>
                                <td><?php echo $payment['debtor']; ?></td>
                                <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo $payment['date']; ?></td>
                                <td><?php echo $payment['method']; ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo isset($payment['status']) ? ucfirst(str_replace('_', ' ', $payment['status'])) : 'Unknown'; ?>
                                    </span>
                                </td>
                                <td><?php echo isset($payment['created_at']) ? date('Y-m-d H:i', strtotime($payment['created_at'])) : '-'; ?></td>
                                <td><?php echo isset($payment['updated_at']) ? date('Y-m-d H:i', strtotime($payment['updated_at'])) : '-'; ?></td>
                                <td>
                                    <div class="action-links">
                                        <a href="view-payment.php?id=<?php echo $payment['id']; ?>" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="edit-payment.php?id=<?php echo $payment['id']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="delete-payment.php?id=<?php echo $payment['id']; ?>" 
                                        onclick="return confirm('Are you sure you want to delete this payment?');" 
                                        title="Delete"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-records">No payments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 
</body>
</html> 