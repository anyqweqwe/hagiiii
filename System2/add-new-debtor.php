<?php
session_start();

// Mock login for demo purposes
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Administrator';
    $_SESSION['role'] = 'admin';
}

// Initialize the debtors array if it doesn't exist
if (!isset($_SESSION['debtors'])) {
    $_SESSION['debtors'] = [];
}

// Create a new debtor
$new_debtor_id = 'DEB' . date('YmdHis');
$new_debtor = [
    'id' => $new_debtor_id,
    'name' => 'Alex Williams',
    'email' => 'alex@example.com',
    'phone' => '(555) 333-9876',
    'address' => '456 Pine Street',
    'city' => 'Boston',
    'state' => 'US',
    'total_debt' => 75000.00,
    'debt_reason' => 'Personal Loan',
    'due_date' => date('Y-m-d', strtotime('-10 days')), // Set due date 10 days ago to trigger notification
    'date_added' => date('Y-m-d'),
    'status' => 'Overdue'
];

// Add the debtor to the session
$_SESSION['debtors'][] = $new_debtor;

// Set success message
$_SESSION['debtor_added'] = true;
$_SESSION['debtor_message'] = "Debtor {$new_debtor['name']} has been added successfully!";

// Print confirmation
echo "Added new debtor: {$new_debtor['name']} with ID {$new_debtor['id']}\n";
echo "The debtor has an overdue debt of \${$new_debtor['total_debt']}\n";
echo "This should trigger a notification.\n";
echo "\nYou can now go to http://localhost:8000/dashboard.php to see the changes.\n";

// Don't redirect - just show the confirmation message
?> 