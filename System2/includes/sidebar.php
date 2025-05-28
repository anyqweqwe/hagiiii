<?php
// Get current page to highlight active link
$current_page = basename($_SERVER['PHP_SELF']);

// Count unread notifications for badge
$unread_notifications = 0;
if (isset($_SESSION['notifications'])) {
    foreach ($_SESSION['notifications'] as $notification) {
        if (!$notification['read']) {
            $unread_notifications++;
        }
    }
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>DMS</h2>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" <?php echo ($current_page == 'dashboard.php') ? 'class="active"' : ''; ?>>
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="debtors.php" <?php echo ($current_page == 'debtors.php' || $current_page == 'add-debtor.php' || $current_page == 'edit-debtor.php' || $current_page == 'view-debtor.php') ? 'class="active"' : ''; ?>>
            <i class="fas fa-users"></i> Debtors
        </a>
        <a href="payments.php" <?php echo ($current_page == 'payments.php' || $current_page == 'add-payment.php' || $current_page == 'edit-payment.php' || $current_page == 'view-payment.php') ? 'class="active"' : ''; ?>>
            <i class="fas fa-credit-card"></i> Payments
        </a>
        <a href="financial-summary.php" <?php echo ($current_page == 'financial-summary.php') ? 'class="active"' : ''; ?>>
            <i class="fas fa-chart-pie"></i> Financial Summary
        </a>
        <a href="notifications.php" <?php echo ($current_page == 'notifications.php') ? 'class="active"' : ''; ?>>
            <i class="fas fa-bell"></i> Notifications
            <?php if ($unread_notifications > 0): ?>
            <span class="notification-badge"><?php echo $unread_notifications; ?></span>
            <?php endif; ?>
        </a>
        <a href="settings.php" <?php echo ($current_page == 'settings.php') ? 'class="active"' : ''; ?>>
            <i class="fas fa-cog"></i> Settings
        </a>
        <a href="logout.php" class="logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div> 