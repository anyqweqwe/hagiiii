<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['debtor_error'] = "No debtor ID provided for deletion.";
    header("Location: debtors.php");
    exit;
}

$debtor_id = $_GET['id'];
$debtor_name = ""; // Will store the name of the deleted debtor

// Initialize deleted demo debtors array if it doesn't exist
if (!isset($_SESSION['deleted_demo_debtors'])) {
    $_SESSION['deleted_demo_debtors'] = [];
}

// Check if this is a demo debtor (IDs: DEB001, DEB002, DEB003)
$is_demo_debtor = in_array($debtor_id, ['DEB001', 'DEB002', 'DEB003']);

// Handle demo debtor deletion
if ($is_demo_debtor) {
    // Store the debtor ID in session to mark it as deleted
    $_SESSION['deleted_demo_debtors'][] = $debtor_id;
    
    // Get the name of the demo debtor
    $demo_debtors = [
        'DEB001' => 'John Smith',
        'DEB002' => 'Maria Garcia',
        'DEB003' => 'Robert Johnson'
    ];
    
    $debtor_name = $demo_debtors[$debtor_id] ?? 'Demo Debtor';
    $found = true;
} else {
    // Find and remove the custom debtor from session
    $found = false;
    if (isset($_SESSION['debtors']) && is_array($_SESSION['debtors'])) {
        foreach ($_SESSION['debtors'] as $key => $debtor) {
            if ($debtor['id'] === $debtor_id) {
                $debtor_name = $debtor['name'];
                unset($_SESSION['debtors'][$key]);
                $found = true;
                break;
            }
        }
        
        // Re-index the array
        if ($found) {
            $_SESSION['debtors'] = array_values($_SESSION['debtors']);
        }
    }
}

// Set success or error message
if ($found) {
    $_SESSION['debtor_added'] = true;
    $_SESSION['debtor_message'] = "Debtor {$debtor_name} has been deleted successfully!";
} else {
    $_SESSION['debtor_error'] = "Debtor with ID {$debtor_id} not found.";
}

// Redirect back to debtors page
header("Location: debtors.php");
exit;
?> 