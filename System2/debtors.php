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

if (isset($_SESSION['debtor_added']) && $_SESSION['debtor_added']) {
    $show_message = true;
    $message = $_SESSION['debtor_message'] ?? 'Debtor has been added successfully!';
    // Clear the session variables
    $_SESSION['debtor_added'] = false;
    $_SESSION['debtor_message'] = '';
} elseif (isset($_SESSION['debtor_error'])) {
    $show_message = true;
    $message = $_SESSION['debtor_error'];
    $message_type = 'danger';
    // Clear the error message
    unset($_SESSION['debtor_error']);
}

// Initialize deleted demo debtors array if it doesn't exist
if (!isset($_SESSION['deleted_demo_debtors'])) {
    $_SESSION['deleted_demo_debtors'] = [];
}

// Demo data
$demo_debtors = [
    ['id' => 'DEB001', 'name' => 'John Smith', 'email' => 'john@example.com', 'phone' => '(555) 123-4567', 'total_debt' => 150000.00, 'status' => 'Active'],
    ['id' => 'DEB002', 'name' => 'Maria Garcia', 'email' => 'maria@example.com', 'phone' => '(555) 987-6543', 'total_debt' => 95250.00, 'status' => 'Active'],
    ['id' => 'DEB003', 'name' => 'Robert Johnson', 'email' => 'robert@example.com', 'phone' => '(555) 456-7890', 'total_debt' => 102000.00, 'status' => 'Overdue']
];

// Filter out deleted demo debtors
$filtered_demo_debtors = [];
foreach ($demo_debtors as $debtor) {
    if (!in_array($debtor['id'], $_SESSION['deleted_demo_debtors'])) {
        $filtered_demo_debtors[] = $debtor;
    }
}

// Combine filtered demo data with user-added debtors from session
$debtors = $filtered_demo_debtors;

// Add user-created debtors from session if they exist
if (isset($_SESSION['debtors']) && is_array($_SESSION['debtors'])) {
    $debtors = array_merge($debtors, $_SESSION['debtors']);
}

// Initialize search and filter variables
$search_term = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Apply search filter if search term provided
if ($search_term !== '') {
    $filtered_debtors = [];
    foreach ($debtors as $debtor) {
        // Search in id, name and email fields
        if (stripos($debtor['id'], $search_term) !== false || 
            stripos($debtor['name'], $search_term) !== false || 
            stripos($debtor['email'], $search_term) !== false) {
            $filtered_debtors[] = $debtor;
        }
    }
    $debtors = $filtered_debtors;
}

// Apply status filter if not set to 'all'
if ($status_filter !== 'all') {
    $filtered_debtors = [];
    foreach ($debtors as $debtor) {
        if ($debtor['status'] === $status_filter) {
            $filtered_debtors[] = $debtor;
        }
    }
    $debtors = $filtered_debtors;
}

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
$page_title = "Debtors - DMS";

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
            <h1>Debtors</h1>
            
            <?php if($show_message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="add-debtor.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Debtor
                </a>
            </div>
            
            <!-- Search and Filter section -->
            <div class="search-filter-container">
                <form method="GET" action="debtors.php" class="search-form">
                    <div class="search-input-container">
                        <input type="text" name="search" placeholder="Search by ID, name or email..." value="<?php echo $search_term; ?>" class="search-input">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="filter-container">
                        <label for="status-filter">Status:</label>
                        <select name="status" id="status-filter" onchange="this.form.submit()">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                            <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="overdue" <?php echo $status_filter === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                            <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="in_dispute" <?php echo $status_filter === 'in_dispute' ? 'selected' : ''; ?>>In Dispute</option>
                            <option value="under_review" <?php echo $status_filter === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                        </select>
                    </div>
                    <?php if($search_term !== '' || $status_filter !== 'all'): ?>
                    <div class="clear-filter">
                        <a href="debtors.php">Clear filters</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- For debugging purposes -->
            <div class="debug-info" style="margin-bottom: 10px; font-size: 0.8rem; color: #999;">
                Total debtors: <?php echo count($debtors); ?> 
                (<?php echo isset($_SESSION['debtors']) ? count($_SESSION['debtors']) : 0; ?> custom)
                <?php if($search_term !== '' || $status_filter !== 'all'): ?>
                | Filtered results
                <?php endif; ?>
            </div>
            
            <!-- Debtors table -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Total Debt</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($debtors) > 0): ?>
                            <?php foreach ($debtors as $debtor): ?>
                            <tr>
                                <td><?php echo $debtor['id'] ?? ''; ?></td>
                                <td><?php echo $debtor['name'] ?? ''; ?></td>
                                <td><?php echo $debtor['email'] ?? ''; ?></td>
                                <td><?php echo $debtor['phone'] ?? ''; ?></td>
                                <td>₱<?php echo number_format($debtor['total_debt'], 2); ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo isset($debtor['status']) ? ucfirst(str_replace('_', ' ', $debtor['status'])) : 'Unknown'; ?>
                                    </span>
                                </td>
                                <td><?php echo isset($debtor['created_at']) ? date('Y-m-d H:i', strtotime($debtor['created_at'])) : (isset($debtor['date_added']) ? date('Y-m-d', strtotime($debtor['date_added'])) : '-'); ?></td>
                                <td><?php echo isset($debtor['updated_at']) ? date('Y-m-d H:i', strtotime($debtor['updated_at'])) : '-'; ?></td>
                                <td>
                                    <div class="action-links">
                                        <a href="view-debtor.php?id=<?php echo $debtor['id'] ?? ''; ?>" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="edit-debtor.php?id=<?php echo $debtor['id'] ?? ''; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                        <?php if (!empty($debtor['id'])): ?>
                                        <a href="delete-debtor.php?id=<?php echo $debtor['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this debtor?');" 
                                           title="Delete"><i class="fas fa-trash-alt"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-records">No debtors found</td>
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