<?php
// Count unread notifications for badge in top bar
$unread_top_notifications = 0;
if (isset($_SESSION['notifications'])) {
    foreach ($_SESSION['notifications'] as $notification) {
        if (!$notification['read']) {
            $unread_top_notifications++;
        }
    }
}
?>
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
            <?php if ($unread_top_notifications > 0): ?>
            <span class="notification-badge"><?php echo $unread_top_notifications; ?></span>
            <?php endif; ?>
        </a>
        <div class="avatar">AD</div>
    </div>
</div> 