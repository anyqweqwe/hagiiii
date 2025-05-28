<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Initialize notifications array if it doesn't exist
if (!isset($_SESSION['notifications'])) {
    $_SESSION['notifications'] = [];
}

// Process notification actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Mark notification as read
    if ($action === 'read' && isset($_GET['id'])) {
        $notification_id = $_GET['id'];
        if (isset($_SESSION['notifications'][$notification_id])) {
            $_SESSION['notifications'][$notification_id]['read'] = true;
        }
        
        // Return to previous page if available
        if (isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
    
    // Mark all notifications as read
    if ($action === 'read_all') {
        foreach ($_SESSION['notifications'] as $id => $notification) {
            $_SESSION['notifications'][$id]['read'] = true;
        }
        
        // Return to previous page if available
        if (isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
    
    // Clear all notifications
    if ($action === 'clear_all') {
        $_SESSION['notifications'] = [];
        
        // Return to previous page if available
        if (isset($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
}

// Function to generate notifications for overdue debts
function generate_overdue_notifications() {
    // Get demo debtors data
    $demo_debtors = [
        ['id' => 'DEB001', 'name' => 'John Smith', 'email' => 'john@example.com', 'phone' => '(555) 123-4567', 'total_debt' => 150000.00, 'due_date' => '2023-12-31', 'status' => 'Active'],
        ['id' => 'DEB002', 'name' => 'Maria Garcia', 'email' => 'maria@example.com', 'phone' => '(555) 987-6543', 'total_debt' => 95250.00, 'due_date' => '2023-11-15', 'status' => 'Active'],
        ['id' => 'DEB003', 'name' => 'Robert Johnson', 'email' => 'robert@example.com', 'phone' => '(555) 456-7890', 'total_debt' => 102000.00, 'due_date' => '2023-06-30', 'status' => 'Overdue']
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
    $all_debtors = $filtered_demo_debtors;
    if (isset($_SESSION['debtors']) && is_array($_SESSION['debtors'])) {
        $all_debtors = array_merge($all_debtors, $_SESSION['debtors']);
    }
    
    // Get current date for comparison
    $current_date = date('Y-m-d');
    
    // Check for overdue debts
    foreach ($all_debtors as $debtor) {
        if (isset($debtor['due_date']) && $debtor['due_date'] < $current_date) {
            // Check if we already have a notification for this debtor
            $notification_exists = false;
            foreach ($_SESSION['notifications'] as $notification) {
                if ($notification['debtor_id'] === $debtor['id'] && $notification['type'] === 'overdue') {
                    $notification_exists = true;
                    break;
                }
            }
            
            // If no notification exists, create one
            if (!$notification_exists) {
                $days_overdue = floor((strtotime($current_date) - strtotime($debtor['due_date'])) / (60 * 60 * 24));
                
                $_SESSION['notifications'][] = [
                    'id' => uniqid(),
                    'type' => 'overdue',
                    'message' => $debtor['name'] . "'s debt is overdue by " . $days_overdue . " days.",
                    'debtor_id' => $debtor['id'],
                    'amount' => $debtor['total_debt'],
                    'due_date' => $debtor['due_date'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'read' => false
                ];
            }
        }
    }
}

// Generate notifications if not already done in this session
if (!isset($_SESSION['notifications_generated']) || $_SESSION['notifications_generated'] !== date('Y-m-d')) {
    generate_overdue_notifications();
    $_SESSION['notifications_generated'] = date('Y-m-d');
}

// Count unread notifications
$unread_count = 0;
foreach ($_SESSION['notifications'] as $notification) {
    if (!$notification['read']) {
        $unread_count++;
    }
}

// If this is an AJAX request, return JSON with notifications
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        'notifications' => $_SESSION['notifications'],
        'unread_count' => $unread_count
    ]);
    exit;
}

$page_title = 'Notifications';
include 'includes/header.php';
?>
<div class="dashboard-container">
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top bar -->
        <?php include 'includes/topbar.php'; ?>
        
        <!-- Dashboard content -->
        <div class="dashboard-content">
            <h1>Notifications</h1>
            
            <div class="notifications-header">
                <div class="notification-count">
                    You have <strong><?php echo $unread_count; ?></strong> unread notification<?php echo $unread_count !== 1 ? 's' : ''; ?>
                </div>
                <div class="notification-actions">
                    <?php if (count($_SESSION['notifications']) > 0): ?>
                    <a href="notifications.php?action=read_all" class="btn-text">Mark all as read</a>
                    <a href="notifications.php?action=clear_all" class="btn-text">Clear all</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="notifications-list">
                <?php if (count($_SESSION['notifications']) > 0): ?>
                    <?php foreach ($_SESSION['notifications'] as $id => $notification): ?>
                    <div class="notification-item <?php echo $notification['read'] ? 'read' : 'unread'; ?>">
                        <div class="notification-icon">
                            <?php if ($notification['type'] === 'overdue'): ?>
                            <i class="fas fa-exclamation-circle"></i>
                            <?php else: ?>
                            <i class="fas fa-info-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="notification-content">
                            <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                            <div class="notification-meta">
                                <span class="notification-time"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></span>
                                <?php if (!$notification['read']): ?>
                                <a href="notifications.php?action=read&id=<?php echo $id; ?>" class="mark-read">Mark as read</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="notification-empty">
                        <i class="fas fa-check-circle"></i>
                        <p>You have no notifications</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?> 