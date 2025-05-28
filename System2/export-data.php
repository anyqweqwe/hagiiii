<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if type parameter is provided
if (!isset($_GET['type']) || empty($_GET['type'])) {
    header("Location: dashboard.php");
    exit;
}

$type = $_GET['type'];
$filename = 'dms_' . $type . '_' . date('Y-m-d_H-i-s') . '.csv';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Initialize data and headers based on export type
if ($type === 'debtors') {
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, "\xEF\xBB\xBF");
    
    // Set column headers for debtors
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Total Debt', 'Status']);
    
    // Get demo debtors data
    $demo_debtors = [
        ['id' => 'DEB001', 'name' => 'John Smith', 'email' => 'john@example.com', 'phone' => '(555) 123-4567', 'total_debt' => 150000.00, 'status' => 'Active'],
        ['id' => 'DEB002', 'name' => 'Maria Garcia', 'email' => 'maria@example.com', 'phone' => '(555) 987-6543', 'total_debt' => 95250.00, 'status' => 'Active'],
        ['id' => 'DEB003', 'name' => 'Robert Johnson', 'email' => 'robert@example.com', 'phone' => '(555) 456-7890', 'total_debt' => 102000.00, 'status' => 'Overdue']
    ];
    
    // Filter out deleted demo debtors
    $filtered_demo_debtors = [];
    if (isset($_SESSION['deleted_demo_debtors'])) {
        foreach ($demo_debtors as $debtor) {
            if (!in_array($debtor['id'], $_SESSION['deleted_demo_debtors'])) {
                $filtered_demo_debtors[] = $debtor;
            }
        }
    } else {
        $filtered_demo_debtors = $demo_debtors;
    }
    
    // Combine with user-created debtors
    $debtors = $filtered_demo_debtors;
    if (isset($_SESSION['debtors']) && is_array($_SESSION['debtors'])) {
        $debtors = array_merge($debtors, $_SESSION['debtors']);
    }
    
    // Apply search and filter if they exist in the query parameters
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_term = $_GET['search'];
        $filtered_debtors = [];
        foreach ($debtors as $debtor) {
            if (stripos($debtor['id'], $search_term) !== false || 
                stripos($debtor['name'], $search_term) !== false || 
                stripos($debtor['email'], $search_term) !== false) {
                $filtered_debtors[] = $debtor;
            }
        }
        $debtors = $filtered_debtors;
    }
    
    if (isset($_GET['status']) && $_GET['status'] !== 'all') {
        $status_filter = $_GET['status'];
        $filtered_debtors = [];
        foreach ($debtors as $debtor) {
            if ($debtor['status'] === $status_filter) {
                $filtered_debtors[] = $debtor;
            }
        }
        $debtors = $filtered_debtors;
    }
    
    // Output debtors data to CSV
    foreach ($debtors as $debtor) {
        fputcsv($output, [
            $debtor['id'],
            $debtor['name'],
            $debtor['email'],
            $debtor['phone'],
            ₱number_format($debtor['total_debt'], 2),
            $debtor['status']
        ]);
    }
    
} elseif ($type === 'payments') {
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, "\xEF\xBB\xBF");
    
    // Set column headers for payments
    fputcsv($output, ['ID', 'Debtor', 'Amount', 'Date', 'Method', 'Status']);
    
    // Get demo payments data
    $demo_payments = [
        ['id' => 'PAY005', 'debtor' => 'Robert Johnson', 'amount' => 1200.00, 'date' => 'Jun 12, 2023', 'method' => 'Bank Transfer', 'status' => 'Pending'],
        ['id' => 'PAY001', 'debtor' => 'John Smith', 'amount' => 500.00, 'date' => 'May 15, 2023', 'method' => 'Bank Transfer', 'status' => 'Completed'],
        ['id' => 'PAY002', 'debtor' => 'Maria Garcia', 'amount' => 750.00, 'date' => 'May 14, 2023', 'method' => 'Cash', 'status' => 'Completed'],
        ['id' => 'PAY003', 'debtor' => 'Robert Johnson', 'amount' => 1200.00, 'date' => 'May 12, 2023', 'method' => 'Credit Card', 'status' => 'Completed'],
        ['id' => 'PAY004', 'debtor' => 'Michael Brown', 'amount' => 300.00, 'date' => 'May 5, 2023', 'method' => 'Check', 'status' => 'Completed']
    ];
    
    // Filter out deleted demo payments
    $filtered_demo_payments = [];
    if (isset($_SESSION['deleted_demo_payments'])) {
        foreach ($demo_payments as $payment) {
            if (!in_array($payment['id'], $_SESSION['deleted_demo_payments'])) {
                $filtered_demo_payments[] = $payment;
            }
        }
    } else {
        $filtered_demo_payments = $demo_payments;
    }
    
    // Combine with user-created payments
    $payments = $filtered_demo_payments;
    if (isset($_SESSION['payments']) && is_array($_SESSION['payments'])) {
        $payments = array_merge($payments, $_SESSION['payments']);
    }
    
    // Apply search and filter if they exist in the query parameters
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_term = $_GET['search'];
        $filtered_payments = [];
        foreach ($payments as $payment) {
            if (stripos($payment['id'], $search_term) !== false || 
                stripos($payment['debtor'], $search_term) !== false) {
                $filtered_payments[] = $payment;
            }
        }
        $payments = $filtered_payments;
    }
    
    if (isset($_GET['status']) && $_GET['status'] !== 'all') {
        $status_filter = $_GET['status'];
        $filtered_payments = [];
        foreach ($payments as $payment) {
            if ($payment['status'] === $status_filter) {
                $filtered_payments[] = $payment;
            }
        }
        $payments = $filtered_payments;
    }
    
    if (isset($_GET['method']) && $_GET['method'] !== 'all') {
        $method_filter = $_GET['method'];
        $filtered_payments = [];
        foreach ($payments as $payment) {
            if ($payment['method'] === $method_filter) {
                $filtered_payments[] = $payment;
            }
        }
        $payments = $filtered_payments;
    }
    
    // Output payments data to CSV
    foreach ($payments as $payment) {
        fputcsv($output, [
            $payment['id'],
            $payment['debtor'],
            ₱number_format($payment['amount'], 2),
            $payment['date'],
            $payment['method'],
            $payment['status']
        ]);
    }
    
} elseif ($type === 'overdue') {
    // Add UTF-8 BOM for Excel compatibility
    fprintf($output, "\xEF\xBB\xBF");
    
    // Set column headers for overdue debts
    fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Total Debt', 'Days Overdue', 'Status']);
    
    // Get overdue debtors from demo data
    $overdueDebtors = [
        ['id' => 'DEB003', 'name' => 'Robert Johnson', 'email' => 'robert@example.com', 'phone' => '(555) 456-7890', 'total_debt' => 102000.00, 'status' => 'Overdue', 'days_overdue' => 45]
    ];
    
    // Add user-created overdue debtors if they exist
    if (isset($_SESSION['debtors']) && is_array($_SESSION['debtors'])) {
        foreach ($_SESSION['debtors'] as $debtor) {
            if (isset($debtor['status']) && $debtor['status'] === 'Overdue') {
                // Calculate days overdue based on due_date if available
                $days_overdue = 0;
                if (isset($debtor['due_date'])) {
                    $due_date = strtotime($debtor['due_date']);
                    $current_date = time();
                    $days_overdue = floor(($current_date - $due_date) / (60 * 60 * 24));
                }
                
                $debtor['days_overdue'] = $days_overdue;
                $overdueDebtors[] = $debtor;
            }
        }
    }
    
    // Output overdue debtors data to CSV
    foreach ($overdueDebtors as $debtor) {
        fputcsv($output, [
            $debtor['id'],
            $debtor['name'],
            $debtor['email'],
            $debtor['phone'],
            ₱number_format($debtor['total_debt'], 2),
            $debtor['days_overdue'],
            $debtor['status']
        ]);
    }
    
} else {
    // Invalid export type, redirect to dashboard
    header("Location: dashboard.php");
    exit;
}

// Close the file pointer
fclose($output);
exit;
?> 