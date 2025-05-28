<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    // In a real application, we would check if email exists and send a reset link
    // For demo purposes, we'll just show a success message
    $message = "If an account exists with email $email, a password reset link has been sent.";
}

// Set page title
$page_title = "Reset Password - DMS";

// Include header
include 'includes/header.php';
?>
<div class="login-container">
    <div class="login-image">
        <!-- Credit card image background -->
    </div>
    <div class="login-form">
        <div class="login-header">
            <h1>DMS</h1>
            <p>Debt Management System</p>
        </div>
        
        <h2>Reset Your Password</h2>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit" class="btn-login">Send Reset Link</button>
            
            <div class="forgot-password">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        </form>
    </div>
</div>
<?php
// Include footer
include 'includes/footer.php';
?>