<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['payment_error'] = "No payment ID provided for editing.";
    header("Location: payments.php");
    exit;
}

$payment_id = $_GET['id'];
$payment = null;
$is_demo = false;

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
    $is_demo = true;
} else {
    // Look for the payment in the session
    if (isset($_SESSION['payments']) && is_array($_SESSION['payments'])) {
        foreach ($_SESSION['payments'] as $key => $p) {
            if ($p['id'] === $payment_id) {
                $payment = $p;
                $payment_key = $key;
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

// Get all available debtors for selection
$all_debtors = [];

// Demo debtors
$demo_debtors = [
    'DEB001' => 'John Smith',
    'DEB002' => 'Maria Garcia',
    'DEB003' => 'Robert Johnson'
];

// Add demo debtors that haven't been deleted
foreach ($demo_debtors as $id => $name) {
    if (!in_array($id, $_SESSION['deleted_demo_debtors'] ?? [])) {
        $all_debtors[] = $name;
    }
}

// Add custom debtors from session
if (isset($_SESSION['debtors']) && is_array($_SESSION['debtors'])) {
    foreach ($_SESSION['debtors'] as $debtor) {
        $all_debtors[] = $debtor['name'];
    }
}

// Remove duplicates and sort alphabetically
$all_debtors = array_unique($all_debtors);
sort($all_debtors);

$error = '';
$success = false;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic validation
    if (empty($_POST['debtor'])) {
        $error = "Debtor is required";
    } elseif (!is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
        $error = "Valid payment amount is required";
    } elseif (empty($_POST['date'])) {
        $error = "Payment date is required";
    } elseif (empty($_POST['method'])) {
        $error = "Payment method is required";
    } elseif (empty($_POST['status'])) {
        $error = "Payment status is required";
    } else {
        // Format the date for display
        $formatted_date = date('M d, Y', strtotime($_POST['date']));
        
        // Prepare updated payment data
        $updated_payment = [
            'id' => $payment_id,
            'debtor' => $_POST['debtor'],
            'amount' => (float)$_POST['amount'],
            'date' => $formatted_date,
            'method' => $_POST['method'],
            'status' => $_POST['status'],
            'reference' => $_POST['reference'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'processor' => $_POST['processor'] ?? ''
        ];
        
        if ($is_demo) {
            // For demo payments, we store the edited version in a special session array
            if (!isset($_SESSION['edited_demo_payments'])) {
                $_SESSION['edited_demo_payments'] = [];
            }
            $_SESSION['edited_demo_payments'][$payment_id] = $updated_payment;
        } else {
            // For regular payments, update it in the session array
            $_SESSION['payments'][$payment_key] = $updated_payment;
        }
        
        // Set success message
        $success = true;
        $_SESSION['payment_added'] = true;
        $_SESSION['payment_message'] = "Payment {$payment_id} has been updated successfully!";
        header("Location: payments.php");
        exit;
    }
    
    // If there was an error, use the POST data to pre-populate form
    if ($error) {
        $payment = [
            'id' => $payment_id,
            'debtor' => $_POST['debtor'],
            'amount' => $_POST['amount'],
            'date' => $_POST['date'],
            'method' => $_POST['method'],
            'status' => $_POST['status'],
            'reference' => $_POST['reference'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'processor' => $_POST['processor'] ?? ''
        ];
    }
}

// Payment methods
$payment_methods = [
    'Cash',
    'Check',
    'Bank Transfer',
    'Credit Card',
    'Debit Card',
    'PayPal',
    'Money Order',
    'Bitcoin',
    'Other'
];

// Payment statuses
$payment_statuses = [
    'Pending',
    'Completed',
    'Failed',
    'Refunded'
];

// Convert date format for the input field if it's in a different format
$input_date = isset($payment['date']) ? $payment['date'] : '';
if ($input_date) {
    // Try to convert to Y-m-d format for the date input field
    $date_obj = DateTime::createFromFormat('M d, Y', $input_date);
    if ($date_obj) {
        $input_date = $date_obj->format('Y-m-d');
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Payment - DMS</title>
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
                
                <h1>Edit Payment</h1>
                
                <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="form-container">
                    <form method="post" action="">
                        <h3>Payment Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="payment_id">Payment ID</label>
                                <input type="text" id="payment_id" value="<?php echo $payment_id; ?>" readonly disabled>
                            </div>
                            
                            <div class="form-group">
                                <label for="debtor">Debtor <span class="required">*</span></label>
                                <select id="debtor" name="debtor" required>
                                    <option value="">-- Select Debtor --</option>
                                    <?php foreach ($all_debtors as $debtor): ?>
                                    <option value="<?php echo htmlspecialchars($debtor); ?>" <?php if($payment['debtor'] === $debtor) echo 'selected'; ?>><?php echo htmlspecialchars($debtor); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="amount">Amount (₱) <span class="required">*</span></label>
                                <input type="number" id="amount" name="amount" value="<?php echo htmlspecialchars($payment['amount']); ?>" min="0.01" step="0.01" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="date">Payment Date <span class="required">*</span></label>
                                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($input_date); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="method">Payment Method <span class="required">*</span></label>
                                <select id="method" name="method" required>
                                    <option value="">-- Select Method --</option>
                                    <?php foreach ($payment_methods as $method): ?>
                                    <option value="<?php echo $method; ?>" <?php if($payment['method'] === $method) echo 'selected'; ?>><?php echo $method; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status <span class="required">*</span></label>
                                <select id="status" name="status" required>
                                    <option value="">-- Select Status --</option>
                                    <?php foreach ($payment_statuses as $status): ?>
                                    <option value="<?php echo $status; ?>" <?php if($payment['status'] === $status) echo 'selected'; ?>><?php echo $status; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <h3>Additional Details</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="reference">Reference Number</label>
                                <input type="text" id="reference" name="reference" value="<?php echo htmlspecialchars($payment['reference'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="processor">Payment Processor</label>
                                <input type="text" id="processor" name="processor" value="<?php echo htmlspecialchars($payment['processor'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($payment['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-buttons">
                            <a href="payments.php" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">Update Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 