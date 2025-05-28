<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['payment_error'] = "No payment ID provided for deletion.";
    header("Location: payments.php");
    exit;
}

$payment_id = $_GET['id'];
$payment_details = ""; // Will store payment details for confirmation message

// Initialize deleted demo payments array if it doesn't exist
if (!isset($_SESSION['deleted_demo_payments'])) {
    $_SESSION['deleted_demo_payments'] = [];
}

// Check if this is a demo payment
$demo_payment_ids = ['PAY001', 'PAY002', 'PAY003', 'PAY004', 'PAY005'];
$is_demo_payment = in_array($payment_id, $demo_payment_ids);

// Handle demo payment deletion
if ($is_demo_payment) {
    // Store the payment ID in session to mark it as deleted
    $_SESSION['deleted_demo_payments'][] = $payment_id;
    
    // Get the details of the demo payment
    $demo_payments = [
        'PAY005' => 'Payment of $1,200.00 from Robert Johnson',
        'PAY001' => 'Payment of $500.00 from John Smith',
        'PAY002' => 'Payment of $750.00 from Maria Garcia',
        'PAY003' => 'Payment of $1,200.00 from Robert Johnson',
        'PAY004' => 'Payment of $300.00 from Michael Brown'
    ];
    
    $payment_details = $demo_payments[$payment_id] ?? 'Demo Payment';
    $found = true;
} else {
    // Find and remove the custom payment from session
    $found = false;
    if (isset($_SESSION['payments']) && is_array($_SESSION['payments'])) {
        foreach ($_SESSION['payments'] as $key => $payment) {
            if ($payment['id'] === $payment_id) {
                $payment_details = "Payment of $" . number_format($payment['amount'], 2) . " from " . $payment['debtor'];
                unset($_SESSION['payments'][$key]);
                $found = true;
                break;
            }
        }
        
        // Re-index the array
        if ($found) {
            $_SESSION['payments'] = array_values($_SESSION['payments']);
        }
    }
}

// Set success or error message
if ($found) {
    $_SESSION['payment_added'] = true;
    $_SESSION['payment_message'] = "{$payment_details} has been deleted successfully!";
} else {
    $_SESSION['payment_error'] = "Payment with ID {$payment_id} not found.";
}

// Redirect back to payments page
header("Location: payments.php");
exit;
?> 