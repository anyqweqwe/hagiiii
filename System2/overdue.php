<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Demo data - filtered from debtors
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

// Count unread notifications
$unread_notifications = 0;
if (isset($_SESSION['notifications'])) {
    foreach ($_SESSION['notifications'] as $notification) {
        if (!$notification['read']) {
            $unread_notifications++;
        }
    }
}

$print_mode = isset($_GET['print']) && $_GET['print'] == '1';

// Calculate late fee for each overdue debtor
$late_fee_settings = isset($_SESSION['settings']['fees']['late_fee']) ? $_SESSION['settings']['fees']['late_fee'] : [
    'enabled' => true,
    'amount' => 25.00,
    'type' => 'fixed',
    'grace_period' => 3
];
foreach ($overdueDebtors as &$debtor) {
    $debtor['late_fee'] = 0.00;
    if ($late_fee_settings['enabled'] && isset($debtor['days_overdue']) && $debtor['days_overdue'] > $late_fee_settings['grace_period']) {
        if ($late_fee_settings['type'] === 'fixed') {
            $debtor['late_fee'] = $late_fee_settings['amount'];
        } elseif ($late_fee_settings['type'] === 'percentage') {
            $debtor['late_fee'] = $debtor['total_debt'] * ($late_fee_settings['amount'] / 100);
        }
    }
}
unset($debtor);
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Debts - DMS</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Apply theme before page loads to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="js/scripts.js"></script>
    <script src="js/animations.js"></script>
    <script>
        // Set current date for print report
        window.onbeforeprint = function() {
            const today = new Date();
            const dateStr = today.toLocaleDateString() + ' ' + today.toLocaleTimeString();
            document.querySelector('.dashboard-content').setAttribute('data-print-date', dateStr);
        };
    </script>
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
                <a href="payments.php">
                    <i class="fas fa-credit-card"></i> Payments
                </a>
                <a href="financial-summary.php">
                    <i class="fas fa-chart-pie"></i> Financial Summary
                </a>
                <a href="notifications.php">
                    <i class="fas fa-bell"></i> Notifications
                    <?php if ($unread_notifications > 0): ?>
                    <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                    <?php endif; ?>
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i> Settings
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
                <div class="user-avatar">
                    <a href="notifications.php" class="top-notification-icon">
                        <i class="fas fa-bell"></i>
                        <?php if ($unread_notifications > 0): ?>
                        <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="avatar">AD</div>
                </div>
            </div>
            
            <!-- Dashboard content -->
            <div class="dashboard-content">
                <h1>Overdue Debts</h1>
                
                <div class="action-buttons">
                    <button class="btn-primary">
                        <i class="fas fa-envelope"></i> Send Reminders
                    </button>
                    <button onclick="window.print();" class="btn-secondary print-button">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <a href="export-data.php?type=overdue" class="btn-secondary">
                        <i class="fas fa-file-export"></i> Export to CSV
                    </a>
                </div>
                
                <!-- Overdue debtors table -->
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Total Debt</th>
                                <th>Days Overdue</th>
                                <th>Late Fee</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($overdueDebtors) > 0): ?>
                                <?php foreach ($overdueDebtors as $debtor): ?>
                                <tr>
                                    <td><?php echo $debtor['id']; ?></td>
                                    <td><?php echo $debtor['name']; ?></td>
                                    <td><?php echo $debtor['email']; ?></td>
                                    <td><?php echo $debtor['phone']; ?></td>
                                    <td>₱<?php echo number_format($debtor['total_debt'], 2); ?></td>
                                    <td><?php echo $debtor['days_overdue']; ?></td>
                                    <td>₱<?php echo number_format($debtor['late_fee'], 2); ?></td>
                                    <td>
                                        <span class="status <?php echo strtolower($debtor['status']); ?>">
                                            <?php echo $debtor['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-links">
                                            <a href="view-debtor.php?id=<?php echo $debtor['id']; ?>" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="#" title="Send Reminder"><i class="fas fa-envelope"></i></a>
                                            <a href="add-payment.php?debtor_id=<?php echo $debtor['id']; ?>" title="Record Payment"><i class="fas fa-dollar-sign"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-records">No overdue debts found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 