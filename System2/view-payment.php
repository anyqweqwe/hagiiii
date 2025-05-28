<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['payment_error'] = "No payment ID provided for viewing.";
    header("Location: payments.php");
    exit;
}

$payment_id = $_GET['id'];
$payment = null;

// Demo payments data with additional details
$demo_payments = [
    'PAY001' => [
        'id' => 'PAY001', 
        'debtor' => 'John Smith', 
        'amount' => 500.00, 
        'date' => 'May 15, 2023', 
        'method' => 'Bank Transfer', 
        'status' => 'Completed',
        'reference' => 'TRF9876543',
        'notes' => 'Monthly payment received on time',
        'processor' => 'Bank of America'
    ],
    'PAY002' => [
        'id' => 'PAY002', 
        'debtor' => 'Maria Garcia', 
        'amount' => 750.00, 
        'date' => 'May 14, 2023', 
        'method' => 'Cash', 
        'status' => 'Completed',
        'reference' => 'RCPT0057',
        'notes' => 'Paid in person at office',
        'processor' => 'Jane Smith (Staff)'
    ],
    'PAY003' => [
        'id' => 'PAY003', 
        'debtor' => 'Robert Johnson', 
        'amount' => 1200.00, 
        'date' => 'May 12, 2023', 
        'method' => 'Credit Card', 
        'status' => 'Completed',
        'reference' => 'CC98761234',
        'notes' => 'Visa card payment',
        'processor' => 'Stripe'
    ],
    'PAY004' => [
        'id' => 'PAY004', 
        'debtor' => 'Michael Brown', 
        'amount' => 300.00, 
        'date' => 'May 5, 2023', 
        'method' => 'Check', 
        'status' => 'Completed',
        'reference' => 'CHK10043',
        'notes' => 'Check was deposited on May 7',
        'processor' => 'Wells Fargo'
    ],
    'PAY005' => [
        'id' => 'PAY005', 
        'debtor' => 'Robert Johnson', 
        'amount' => 1200.00, 
        'date' => 'Jun 12, 2023', 
        'method' => 'Bank Transfer', 
        'status' => 'Pending',
        'reference' => 'TRF5423789',
        'notes' => 'Awaiting bank clearance',
        'processor' => 'Chase Bank'
    ]
];

// Check if it's a demo payment
if (isset($demo_payments[$payment_id]) && !in_array($payment_id, $_SESSION['deleted_demo_payments'] ?? [])) {
    $payment = $demo_payments[$payment_id];
} else {
    // Look for the payment in the session
    if (isset($_SESSION['payments']) && is_array($_SESSION['payments'])) {
        foreach ($_SESSION['payments'] as $p) {
            if ($p['id'] === $payment_id) {
                $payment = $p;
                break;
            }
        }
    }
}

// If payment not found, show error
if (!$payment) {
    $_SESSION['payment_error'] = "Payment with ID {$payment_id} not found.";
    header("Location: payments.php");
    exit;
}

// Get debtor info if available
$debtor = null;
$debtor_id = null;

// Demo debtors data
$demo_debtors = [
    'DEB001' => [
        'id' => 'DEB001', 
        'name' => 'John Smith', 
        'email' => 'john@example.com',
        'total_debt' => 150000.00
    ],
    'DEB002' => [
        'id' => 'DEB002', 
        'name' => 'Maria Garcia', 
        'email' => 'maria@example.com',
        'total_debt' => 95250.00
    ],
    'DEB003' => [
        'id' => 'DEB003', 
        'name' => 'Robert Johnson', 
        'email' => 'robert@example.com',
        'total_debt' => 102000.00
    ]
];

// Look for matching debtor in demo data
foreach ($demo_debtors as $id => $d) {
    if ($d['name'] === $payment['debtor'] && !in_array($id, $_SESSION['deleted_demo_debtors'] ?? [])) {
        $debtor = $d;
        $debtor_id = $id;
        break;
    }
}

// If not found in demo, look in session data
if (!$debtor && isset($_SESSION['debtors']) && is_array($_SESSION['debtors'])) {
    foreach ($_SESSION['debtors'] as $d) {
        if ($d['name'] === $payment['debtor']) {
            $debtor = $d;
            $debtor_id = $d['id'];
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payment - DMS</title>
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
                <a href="debtors.php">
                    <i class="fas fa-users"></i> Debtors
                </a>
                <a href="payments.php" class="active">
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
                    <a href="payments.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Payments
                    </a>
                </div>
                
                <h1>Payment Details</h1>
                
                <div class="form-container">
                    <div class="stats-container" style="margin-bottom: 30px;">
                        <div class="stat-card blue">
                            <div class="stat-info">
                                <h3>Payment Amount</h3>
                                <h2>₱<?php echo number_format($payment['amount'], 2); ?></h2>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        
                        <div class="stat-card <?php echo strtolower($payment['status']) === 'completed' ? 'green' : 'orange'; ?>">
                            <div class="stat-info">
                                <h3>Status</h3>
                                <h2><?php echo $payment['status']; ?></h2>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-<?php echo strtolower($payment['status']) === 'completed' ? 'check' : 'clock'; ?>"></i>
                            </div>
                        </div>
                    </div>
                    
                    <h3>Basic Information</h3>
                    <div class="view-group">
                        <div class="view-label">Payment ID:</div>
                        <div class="view-value"><?php echo $payment['id']; ?></div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">Debtor:</div>
                        <div class="view-value">
                            <?php echo $payment['debtor']; ?>
                            <?php if ($debtor_id): ?>
                            <a href="view-debtor.php?id=<?php echo $debtor_id; ?>" class="btn-link">
                                <i class="fas fa-external-link-alt"></i> View Debtor
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">Date:</div>
                        <div class="view-value"><?php echo $payment['date']; ?></div>
                    </div>
                    
                    <h3>Payment Details</h3>
                    <div class="view-group">
                        <div class="view-label">Method:</div>
                        <div class="view-value"><?php echo $payment['method']; ?></div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">Reference #:</div>
                        <div class="view-value"><?php echo $payment['reference'] ?? 'N/A'; ?></div>
                    </div>
                    
                    <div class="view-group">
                        <div class="view-label">Processor:</div>
                        <div class="view-value"><?php echo $payment['processor'] ?? 'N/A'; ?></div>
                    </div>
                    
                    <?php if (isset($payment['notes']) && !empty($payment['notes'])): ?>
                    <h3>Notes</h3>
                    <div class="view-group">
                        <div class="view-label">Payment Notes:</div>
                        <div class="view-value"><?php echo $payment['notes']; ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <h3>Action History</h3>
                    <div class="view-group">
                        <div class="view-label">Created At:</div>
                        <div class="view-value"><?php echo isset($payment['created_at']) ? date('Y-m-d H:i', strtotime($payment['created_at'])) : '-'; ?></div>
                    </div>
                    <div class="view-group">
                        <div class="view-label">Last Updated:</div>
                        <div class="view-value"><?php echo isset($payment['updated_at']) ? date('Y-m-d H:i', strtotime($payment['updated_at'])) : '-'; ?></div>
                    </div>
                    <div class="view-group">
                        <div class="view-label">Current Status:</div>
                        <div class="view-value"><span class="badge bg-secondary"><?php echo isset($payment['status']) ? ucfirst(str_replace('_', ' ', $payment['status'])) : 'Unknown'; ?></span></div>
                    </div>
                    
                    <div class="form-buttons">
                        <a href="payments.php" class="btn-secondary">Back</a>
                        <a href="edit-payment.php?id=<?php echo $payment['id']; ?>" class="btn-primary">Edit Payment</a>
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
        .btn-link {
            margin-left: 10px;
            color: #1a73e8;
            font-size: 0.8rem;
        }
    </style>
</body>
</html> 