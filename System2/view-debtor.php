<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['debtor_error'] = "No debtor ID provided for viewing.";
    header("Location: debtors.php");
    exit;
}

$debtor_id = $_GET['id'];
$debtor = null;

// Demo debtors data
$demo_debtors = [
    'DEB001' => [
        'id' => 'DEB001', 
        'name' => 'John Smith', 
        'email' => 'john@example.com', 
        'phone' => '(555) 123-4567', 
        'address' => '123 Main St', 
        'city' => 'New York', 
        'state' => 'US', 
        'total_debt' => 150000.00, 
        'debt_reason' => 'Mortgage',
        'due_date' => '2023-12-31',
        'date_added' => '2023-01-15',
        'status' => 'Active'
    ],
    'DEB002' => [
        'id' => 'DEB002', 
        'name' => 'Maria Garcia', 
        'email' => 'maria@example.com', 
        'phone' => '(555) 987-6543', 
        'address' => '456 Park Ave', 
        'city' => 'Los Angeles', 
        'state' => 'US', 
        'total_debt' => 95250.00, 
        'debt_reason' => 'Business Loan',
        'due_date' => '2023-11-15',
        'date_added' => '2023-02-10',
        'status' => 'Active'
    ],
    'DEB003' => [
        'id' => 'DEB003', 
        'name' => 'Robert Johnson', 
        'email' => 'robert@example.com', 
        'phone' => '(555) 456-7890', 
        'address' => '789 Broadway', 
        'city' => 'Chicago', 
        'state' => 'US', 
        'total_debt' => 102000.00, 
        'debt_reason' => 'Auto Loan',
        'due_date' => '2023-06-30',
        'date_added' => '2023-01-05',
        'status' => 'Overdue'
    ]
];

// Check if it's a demo debtor
if (isset($demo_debtors[$debtor_id]) && !in_array($debtor_id, $_SESSION['deleted_demo_debtors'] ?? [])) {
    $debtor = $demo_debtors[$debtor_id];
} else {
    // Look for the debtor in the session
    if (isset($_SESSION['debtors']) && is_array($_SESSION['debtors'])) {
        foreach ($_SESSION['debtors'] as $d) {
            if ($d['id'] === $debtor_id) {
                $debtor = $d;
                break;
            }
        }
    }
}

// If debtor not found, show error
if (!$debtor) {
    $_SESSION['debtor_error'] = "Debtor with ID {$debtor_id} not found.";
    header("Location: debtors.php");
    exit;
}

// Get related payments for this debtor
$related_payments = [];
// Demo payments
$demo_payments = [
    ['id' => 'PAY005', 'debtor' => 'Robert Johnson', 'amount' => 1200.00, 'date' => 'Jun 12, 2023', 'method' => 'Bank Transfer', 'status' => 'Pending'],
    ['id' => 'PAY001', 'debtor' => 'John Smith', 'amount' => 500.00, 'date' => 'May 15, 2023', 'method' => 'Bank Transfer', 'status' => 'Completed'],
    ['id' => 'PAY002', 'debtor' => 'Maria Garcia', 'amount' => 750.00, 'date' => 'May 14, 2023', 'method' => 'Cash', 'status' => 'Completed'],
    ['id' => 'PAY003', 'debtor' => 'Robert Johnson', 'amount' => 1200.00, 'date' => 'May 12, 2023', 'method' => 'Credit Card', 'status' => 'Completed'],
    ['id' => 'PAY004', 'debtor' => 'Michael Brown', 'amount' => 300.00, 'date' => 'May 5, 2023', 'method' => 'Check', 'status' => 'Completed']
];

// Filter demo payments for this debtor
foreach ($demo_payments as $payment) {
    if ($payment['debtor'] === $debtor['name'] && !in_array($payment['id'], $_SESSION['deleted_demo_payments'] ?? [])) {
        $related_payments[] = $payment;
    }
}

// Check session payments
if (isset($_SESSION['payments']) && is_array($_SESSION['payments'])) {
    foreach ($_SESSION['payments'] as $payment) {
        if ($payment['debtor'] === $debtor['name']) {
            $related_payments[] = $payment;
        }
    }
}

// Sort payments by date
usort($related_payments, function($a, $b) {
    $dateA = strtotime($a['date'] ?? '');
    $dateB = strtotime($b['date'] ?? '');
    
    if (!$dateA || !$dateB) {
        return strcmp($b['id'] ?? '', $a['id'] ?? '');
    }
    
    return $dateB - $dateA; // newest first
});
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Debtor - DMS</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/themes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Apply theme before page loads to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="js/scripts.js"></script>
    <script src="js/animations.js"></script>
    <script src="js/theme.js"></script>
    <style>
    [data-theme="dark"] .dashboard-content,
    [data-theme="dark"] .form-container {
        background-color: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }
    [data-theme="dark"] .form-container h3 {
        color: var(--text-secondary) !important;
    }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>DMS</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="debtors.php" class="active">
                    <i class="fas fa-users"></i> Debtors
                </a>
                <a href="payments.php">
                    <i class="fas fa-credit-card"></i> Payments
                </a>
                <a href="financial-summary.php">
                    <i class="fas fa-chart-pie"></i> Financial Summary
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
                <button type="button" class="theme-toggle" onclick="window.ThemeManager.toggleTheme()" title="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="user-avatar">
                    <div class="avatar">AD</div>
                </div>
            </div>
            
            <!-- Dashboard content -->
            <div class="dashboard-content">
                <div class="action-buttons" style="margin-bottom: 20px;">
                    <a href="debtors.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Debtors
                    </a>
                </div>
                
                <h1>Debtor Details</h1>
                
                <div class="form-container">
                    <div class="stats-container" style="margin-bottom: 30px;">
                        <div class="stat-card blue">
                            <div class="stat-info">
                                <h3>Total Debt</h3>
                                <h2>₱<?php echo number_format($debtor['total_debt'], 2); ?></h2>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        
                        <div class="stat-card <?php echo strtolower($debtor['status']) === 'active' ? 'green' : 'orange'; ?>">
                            <div class="stat-info">
                                <h3>Status</h3>
                                <h2><?php echo $debtor['status']; ?></h2>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-<?php echo strtolower($debtor['status']) === 'active' ? 'check' : 'exclamation-circle'; ?>"></i>
                            </div>
                        </div>
                    </div>
                    
                    <h3>Personal Information</h3>
                    <div class="view-group">
                        <div class="view-label">Name:</div>
                        <div class="view-value"><?php echo $debtor['name']; ?></div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">Email:</div>
                        <div class="view-value"><?php echo $debtor['email']; ?></div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">Phone:</div>
                        <div class="view-value"><?php echo $debtor['phone']; ?></div>
                    </div>
                    
                    <h3>Address Information</h3>
                    <div class="view-group">
                        <div class="view-label">Address:</div>
                        <div class="view-value"><?php echo $debtor['address'] ?? 'N/A'; ?></div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">City:</div>
                        <div class="view-value"><?php echo $debtor['city']; ?></div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">Country/State:</div>
                        <div class="view-value"><?php echo $debtor['state']; ?></div>
                    </div>
                    
                    <h3>Debt Information</h3>
                    <div class="view-group">
                        <div class="view-label">Amount:</div>
                        <div class="view-value">₱<?php echo number_format($debtor['total_debt'], 2); ?></div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">Reason:</div>
                        <div class="view-value"><?php echo $debtor['debt_reason'] ?? 'N/A'; ?></div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">Due Date:</div>
                        <div class="view-value"><?php echo $debtor['due_date'] ?? 'N/A'; ?></div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">Added On:</div>
                        <div class="view-value"><?php echo $debtor['date_added'] ?? 'N/A'; ?></div>
                    </div>
                    
                    <h3>Action History</h3>
                    <div class="view-group">
                        <div class="view-label">Created At:</div>
                        <div class="view-value"><?php echo isset($debtor['created_at']) ? date('Y-m-d H:i', strtotime($debtor['created_at'])) : (isset($debtor['date_added']) ? date('Y-m-d', strtotime($debtor['date_added'])) : '-'); ?></div>
                    </div>
                    <div class="view-group">
                        <div class="view-label">Last Updated:</div>
                        <div class="view-value"><?php echo isset($debtor['updated_at']) ? date('Y-m-d H:i', strtotime($debtor['updated_at'])) : '-'; ?></div>
                    </div>
                    <div class="view-group">
                        <div class="view-label">Current Status:</div>
                        <div class="view-value"><span class="badge bg-secondary"><?php echo isset($debtor['status']) ? ucfirst(str_replace('_', ' ', $debtor['status'])) : 'Unknown'; ?></span></div>
                    </div>
                    
                    <h3>Related Payments</h3>
                    <?php if (count($related_payments) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($related_payments as $payment): ?>
                                <tr>
                                    <td><?php echo $payment['id']; ?></td>
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
                    <?php else: ?>
                    <p>No payments found for this debtor.</p>
                    <?php endif; ?>
                    
                    <div class="form-buttons">
                        <a href="debtors.php" class="btn-secondary">Back</a>
                        <a href="edit-debtor.php?id=<?php echo $debtor['id']; ?>" class="btn-primary">Edit Debtor</a>
                        <a href="add-payment.php?debtor=<?php echo urlencode($debtor['name']); ?>" class="btn-primary">Add Payment</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .view-group {
            display: flex;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        .view-label {
            width: 180px;
            font-weight: 500;
            color: #555;
        }
        .view-value {
            flex: 1;
        }
    </style>
</body>
</html> 