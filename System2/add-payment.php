<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$success = false;
$error = '';
$formData = [
    'debtor_id' => '',
    'amount' => '',
    'payment_date' => date('Y-m-d'), // Default to today's date
    'payment_method' => '',
    'reference_number' => '',
    'status' => 'Completed',
    'notes' => ''
];

// Generate a new payment ID based on current timestamp
$new_payment_id = 'PAY' . date('YmdHis');

// Get debtors from session if available, otherwise use demo debtors
if (isset($_SESSION['debtors']) && !empty($_SESSION['debtors'])) {
    $debtors = array_map(function($debtor) {
        return [
            'id' => $debtor['id'] ?? '', 
            'name' => $debtor['name'] ?? '',
            'total_debt' => $debtor['total_debt'] ?? 0
        ];
    }, $_SESSION['debtors']);
} else {
    // Demo data for debtor dropdown
    $debtors = [
        ['id' => 'DEB001', 'name' => 'John Smith', 'total_debt' => 150000.00],
        ['id' => 'DEB002', 'name' => 'Maria Garcia', 'total_debt' => 95250.00],
        ['id' => 'DEB003', 'name' => 'Robert Johnson', 'total_debt' => 102000.00]
    ];
}

// Payment status options
$payment_statuses = ['Pending', 'Completed', 'Failed', 'Refunded'];

// Payment methods with additional data for card payments
$payment_methods = [
    'Bank Transfer' => ['requires_reference' => true],
    'Credit Card' => ['requires_card' => true],
    'Debit Card' => ['requires_card' => true],
    'Cash' => ['requires_reference' => false],
    'Check' => ['requires_reference' => true],
    'PayPal' => ['requires_reference' => true],
    'Gcash' => ['requires_reference' => true],
    'Money Order' => ['requires_reference' => true],
    'Bitcoin' => ['requires_reference' => true],
    'Other' => ['requires_reference' => false]
];

// Strictly validate payment method
$allowed_payment_methods = array_keys($payment_methods);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture all form data
    $formData = [
        'debtor_id' => trim($_POST['debtor_id'] ?? ''),
        'amount' => floatval($_POST['amount'] ?? 0),
        'payment_date' => trim($_POST['payment_date'] ?? ''),
        'payment_method' => trim($_POST['payment_method'] ?? ''),
        'reference_number' => trim($_POST['reference_number'] ?? ''),
        'card_number' => trim($_POST['card_number'] ?? ''),
        'card_expiry' => trim($_POST['card_expiry'] ?? ''),
        'card_cvv' => trim($_POST['card_cvv'] ?? ''),
        'status' => trim($_POST['status'] ?? 'Completed'),
        'notes' => trim($_POST['notes'] ?? '')
    ];
    
    // Enhanced validation
    if (empty($formData['debtor_id'])) {
        $error = "Debtor is required";
    } elseif (!is_numeric($formData['amount']) || $formData['amount'] <= 0) {
        $error = "Valid payment amount is required";
    } elseif (empty($formData['payment_date'])) {
        $error = "Payment date is required";
    } elseif (empty($formData['payment_method']) || !in_array($formData['payment_method'], $allowed_payment_methods, true)) {
        $error = "Valid payment method is required";
    } elseif (
        in_array($formData['payment_method'], ['Credit Card', 'Debit Card']) && 
        (empty($formData['card_number']) || empty($formData['card_expiry']) || empty($formData['card_cvv']))
    ) {
        $error = "Card details are required for card payments";
    } elseif (isset($payment_methods[$formData['payment_method']]) && 
              $payment_methods[$formData['payment_method']]['requires_reference'] && 
              empty($formData['reference_number'])) {
        $error = "Reference number is required for {$formData['payment_method']}";
    } else {
        // Compute total payments for this debtor
        $total_paid = 0;
        if (isset($_SESSION['payments']) && is_array($_SESSION['payments'])) {
            foreach ($_SESSION['payments'] as $existing_payment) {
                if ($existing_payment['debtor_id'] === $formData['debtor_id']) {
                    $total_paid += (float)($existing_payment['amount'] ?? 0);
                }
            }
        }
        // Find the debtor's original debt
        $debtor_total_debt = 0;
        foreach ($debtors as $debtor) {
            if ($debtor['id'] === $formData['debtor_id']) {
                $debtor_total_debt = (float)($debtor['total_debt'] ?? 0);
                break;
            }
        }
        // Compute remaining debt
        $remaining_debt = $debtor_total_debt - $total_paid;
        // Prevent overpayment
        if ($formData['amount'] > $remaining_debt) {
            $error = "Payment amount exceeds the remaining debt (₱" . number_format($remaining_debt, 2) . "). Please enter a valid amount.";
        } else {
            // Check for duplicate payment (same debtor, reference number, and amount in session payments)
            $is_duplicate = false;
            if (isset($_SESSION['payments']) && is_array($_SESSION['payments'])) {
                foreach ($_SESSION['payments'] as $existing_payment) {
                    if (
                        $existing_payment['debtor_id'] === $formData['debtor_id'] &&
                        $existing_payment['reference_number'] === $formData['reference_number'] &&
                        $existing_payment['amount'] == $formData['amount']
                    ) {
                        $is_duplicate = true;
                        break;
                    }
                }
            }
            if ($is_duplicate) {
                $error = "Duplicate payment detected: this reference number and amount have already been recorded for this debtor.";
            } else {
                // Find the debtor name and update their debt
                $debtor_name = '';
                $debtor_total_debt = 0;
                $debtor_index = -1;
                
                foreach ($debtors as $index => $debtor) {
                    if ($debtor['id'] === $formData['debtor_id']) {
                        $debtor_name = $debtor['name'];
                        $debtor_total_debt = $debtor['total_debt'];
                        $debtor_index = $index;
                        break;
                    }
                }
                
                // In a real application, save to database and update debtor's balance
                // For demo, just show success message and redirect
                
                // Store payment in session for demo - this would normally go to a database
                $_SESSION['payments'][] = [
                    'id' => $new_payment_id,
                    'debtor_id' => $formData['debtor_id'],
                    'debtor' => $debtor_name,
                    'amount' => (float)$formData['amount'],
                    'date' => $formData['payment_date'],
                    'method' => $formData['payment_method'],
                    'reference_number' => $formData['reference_number'],
                    'status' => $formData['status'],
                    'notes' => $formData['notes'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Update debtor's debt if this is a session tracked debtor
                if (isset($_SESSION['debtors']) && $debtor_index >= 0) {
                    // Always reduce the debt by the payment amount
                    $_SESSION['debtors'][$debtor_index]['total_debt'] = max(0, $debtor_total_debt - (float)$formData['amount']);
                }
                
                $success = true;
                $_SESSION['payment_added'] = true;
                $_SESSION['payment_message'] = "Payment of ₱" . number_format($formData['amount'], 2) . " from $debtor_name has been recorded successfully!";
                header("Location: payments.php");
                exit;
            }
        }
    }

    // In the PHP form processing, add a fallback for Gcash reference number
    if (isset($payment_methods[$formData['payment_method']]) &&
        $formData['payment_method'] === 'Gcash' &&
        empty($formData['reference_number'])
    ) {
        $formData['reference_number'] = 'GCASH' . date('Ymd') . rand(100000, 999999);
    }
}

$page_title = 'Add Payment';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>DMS</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">
                <i class="fas fa-home"></i> <span>Dashboard</span>
            </a>
            <a href="debtors.php">
                <i class="fas fa-users"></i> <span>Debtors</span>
            </a>
            <a href="payments.php" class="active">
                <i class="fas fa-credit-card"></i> <span>Payments</span>
            </a>
            <a href="financial-summary.php">
                <i class="fas fa-chart-pie"></i> <span>Financial Summary</span>
            </a>
            <a href="logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
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
                <div class="avatar">AD</div>
            </div>
        </div>
        
        <!-- Dashboard content -->
        <div class="dashboard-content">
            <h1>Record New Payment</h1>
            
            <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
                    <button type="button" class="theme-toggle" onclick="window.ThemeManager.toggleTheme()" title="Toggle dark mode">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
                <form method="post" action="">
                    <h3>Payment Information</h3>
                    <div class="form-group">
                        <label for="debtor_id">Select Debtor <span class="required">*</span></label>
                        <select id="debtor_id" name="debtor_id" required>
                            <option value="">-- Select Debtor --</option>
                            <?php foreach ($debtors as $debtor): ?>
                            <option value="<?php echo $debtor['id']; ?>" <?php if($formData['debtor_id'] === $debtor['id']) echo 'selected'; ?>>
                                <?php echo $debtor['name']; ?> - ₱<?php echo number_format($debtor['total_debt'], 2); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="amount">Payment Amount (₱) <span class="required">*</span></label>
                            <input type="number" id="amount" name="amount" value="<?php echo htmlspecialchars($formData['amount']); ?>" min="0.01" step="0.01" required placeholder="e.g., 1000.00">
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_date">Payment Date <span class="required">*</span></label>
                            <input type="date" id="payment_date" name="payment_date" value="<?php echo htmlspecialchars($formData['payment_date']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method <span class="required">*</span></label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">-- Select Method --</option>
                            <?php foreach (array_keys($payment_methods) as $method): ?>
                            <option value="<?php echo $method; ?>" <?php if($formData['payment_method'] === $method) echo 'selected'; ?>><?php echo $method; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" id="reference_number_group">
                        <label for="reference_number">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number" value="<?php echo htmlspecialchars($formData['reference_number'] ?? ''); ?>" <?php if (!empty($formData['payment_method']) && $formData['payment_method'] === 'Gcash') echo 'required'; ?> />
                    </div>
                    
                    <div id="card_details_group">
                        <h3>Card Details</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="card_number">Card Number <span class="required">*</span></label>
                                <input type="text" id="card_number" name="card_number" placeholder="•••• •••• •••• ••••">
                            </div>
                            
                            <div class="form-group">
                                <label for="card_expiry">Expiry Date <span class="required">*</span></label>
                                <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY">
                            </div>
                            
                            <div class="form-group">
                                <label for="card_cvv">CVV <span class="required">*</span></label>
                                <input type="text" id="card_cvv" name="card_cvv" placeholder="123">
                            </div>
                        </div>
                    </div>
                    
                    <h3>Additional Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Payment Status</label>
                            <select id="status" name="status">
                                <?php foreach ($payment_statuses as $status): ?>
                                <option value="<?php echo $status; ?>" <?php if($formData['status'] === $status) echo 'selected'; ?>><?php echo $status; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($formData['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-buttons">
                        <a href="payments.php" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-generate Gcash reference number
function generateGcashReference() {
    const now = new Date();
    const pad = n => n.toString().padStart(2, '0');
    const dateStr = `${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}`;
    const rand = Math.floor(100000 + Math.random() * 900000);
    return `GCASH${dateStr}${rand}`;
}
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.getElementById('payment_method');
    const referenceInput = document.getElementById('reference_number');
    if (paymentMethod && referenceInput) {
        paymentMethod.addEventListener('change', function() {
            if (this.value === 'Gcash') {
                referenceInput.value = generateGcashReference();
                referenceInput.required = true;
            } else {
                referenceInput.required = false;
            }
        });
        // If Gcash is already selected on load
        if (paymentMethod.value === 'Gcash' && !referenceInput.value) {
            referenceInput.value = generateGcashReference();
            referenceInput.required = true;
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?> 