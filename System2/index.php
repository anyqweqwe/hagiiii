<?php
session_start();
if (isset($_GET['type']) && strtolower($_GET['type']) === 'dashboard') {
    header("Location: dashboard.php");
    exit;
}
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Handle login attempt
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // In a real application, validate credentials against database
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($username == 'admin' && $password == 'admin') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'Administrator';
        $_SESSION['role'] = 'admin';
        
        // Initialize session arrays for debtors and payments if not already set
        if (!isset($_SESSION['debtors'])) {
            $_SESSION['debtors'] = [];
        }
        if (!isset($_SESSION['payments'])) {
            $_SESSION['payments'] = [];
        }
        
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DMS - Debt Management System</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme management script -->
    <script src="js/theme.js"></script>
    <style>
        /* High-fidelity login page styles */
        .login-container {
            background: radial-gradient(circle at 10% 20%, rgba(249, 250, 251, 1) 0%, rgba(245, 247, 250, 1) 90%);
            overflow-x: hidden;
            position: relative;
        }
        
        [data-theme="dark"] .login-container {
            background: radial-gradient(circle at 10% 20%, rgba(15, 23, 42, 1) 0%, rgba(30, 41, 59, 1) 90%);
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%232563eb' fill-opacity='0.03'%3E%3Cpath opacity='.5' d='M96 95h4v1h-4v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9zm-1 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9z'/%3E%3Cpath d='M6 5V0H5v5H0v1h5v94h1V6h94V5H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
            z-index: 0;
        }
        
        .login-image {
            background: linear-gradient(145deg, #e6e9f0 0%, #eef1f5 100%);
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.05);
            z-index: 1;
        }
        
        [data-theme="dark"] .login-image {
            background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
        }

        .card-stack {
            transform-style: preserve-3d;
            perspective: 1500px;
            margin-bottom: 120px;
            transform: rotateY(10deg) rotateX(5deg);
            transition: transform 0.4s ease;
        }
        
        .card-stack:hover {
            transform: rotateY(0deg) rotateX(0deg) scale(1.05);
        }
        
        .credit-card {
            background-image: none;
            transform-style: preserve-3d;
            border-radius: 18px;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }
        
        .card1 {
            background: linear-gradient(135deg, #ffffff 0%, #f5f5f7 100%);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.1), 0 5px 10px rgba(0, 0, 0, 0.05);
            transform: translateY(-30px) rotate(-5deg) translateZ(20px);
        }
        
        .card2 {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 100%);
            box-shadow: 0 15px 25px rgba(37, 99, 235, 0.2), 0 5px 10px rgba(37, 99, 235, 0.1);
            transform: translateY(-15px) rotate(-2deg) translateZ(15px);
        }
        
        .card3 {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 0 15px 25px rgba(15, 23, 42, 0.3), 0 5px 10px rgba(15, 23, 42, 0.2);
            transform: translateY(0) rotate(1deg) translateZ(10px);
        }
        
        .card4 {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            box-shadow: 0 15px 25px rgba(245, 158, 11, 0.2), 0 5px 10px rgba(245, 158, 11, 0.1);
            transform: translateY(15px) rotate(4deg) translateZ(5px);
        }
        
        .card-chip {
            background: linear-gradient(135deg, #e9ecef 0%, #ced4da 100%);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .card-chip::after {
            content: '';
            position: absolute;
            width: 60%;
            height: 35%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            top: 0;
            left: -100%;
            transform: rotate(25deg);
            animation: chipShine 3s infinite;
        }
        
        @keyframes chipShine {
            0% { left: -100%; }
            20% { left: 100%; }
            100% { left: 100%; }
        }
        
        .overlay-text {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 75%;
            transform: translateY(0);
            animation: float 6s ease-in-out infinite;
        }
        
        [data-theme="dark"] .overlay-text {
            background: rgba(30, 41, 59, 0.95);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0); }
        }
        
        .overlay-text h2 {
            color: var(--primary-color);
            font-size: 1.75rem;
            margin-bottom: 0.75rem;
            font-weight: 700;
        }
        
        .overlay-text p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            line-height: 1.5;
        }
        
        .login-form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem;
            position: relative;
            z-index: 1;
            background-color: var(--card-bg);
            box-shadow: -5px 0 30px rgba(0, 0, 0, 0.05);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.05em;
        }
        
        .login-header p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            font-weight: 500;
            opacity: 0.9;
        }
        
        .login-form h2 {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .form-group label {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
            display: block;
            transition: all 0.2s ease;
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-size: 1.05rem;
            transition: all 0.2s ease;
            background-color: var(--card-bg);
            color: var(--text-primary);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        }
        
        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            border: none;
            padding: 1rem;
            font-size: 1.05rem;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 1rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.25);
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .forgot-password a {
            color: var(--primary-color);
            font-weight: 600;
            transition: all 0.2s ease;
            border-bottom: 1px dashed transparent;
            padding-bottom: 2px;
        }
        
        .forgot-password a:hover {
            border-bottom-color: var(--primary-color);
        }
        
        .alert {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            border: none;
            display: flex;
            align-items: center;
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
        
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-left: 4px solid #ef4444;
        }
        
        .alert-danger::before {
            content: '\f071';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        /* Theme toggle for login */
        .theme-toggle-login {
            position: absolute;
            top: 20px;
            right: 20px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 1.2rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 10;
        }
        
        .theme-toggle-login:hover {
            background-color: rgba(203, 213, 225, 0.1);
            color: var(--primary-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .login-container {
                height: auto;
                min-height: 100vh;
            }
            
            .login-form {
                padding: 2rem;
            }
            
            .login-image {
                min-height: 40vh;
            }
            
            .overlay-text {
                max-width: 90%;
            }
            
            .login-header h1 {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <button class="theme-toggle-login" id="theme-toggle-login" title="Toggle dark mode">
            <i class="fas fa-moon"></i>
        </button>
        
        <div class="login-image">
            <div class="credit-cards-display">
                <!-- CSS Credit Card Stack -->
                <div class="card-stack">
                    <div class="credit-card card1">
                        <div class="card-chip"></div>
                        <div class="card-logo">
                            <i class="fab fa-apple"></i>
                        </div>
                    </div>
                    <div class="credit-card card2">
                        <div class="card-chip"></div>
                        <div class="card-logo">
                            <i class="fab fa-cc-amex"></i> 
                        </div>
                    </div>
                    <div class="credit-card card3">
                        <div class="card-chip"></div>
                        <div class="card-logo">
                            <i class="fas fa-credit-card"></i>
                        </div>
                    </div>
                    <div class="credit-card card4">
                        <div class="card-chip"></div>
                        <div class="card-logo">
                            <i class="fab fa-cc-visa"></i>
                        </div>
                    </div>
                </div>
                <div class="overlay-text">
                    <h2>Manage Multiple Payment Methods</h2>
                    <p>Track cards, bank transfers, and all payment types in one place</p>
                </div>
            </div>
        </div>
        <div class="login-form">
            <div class="login-header">
                <h1>DMS</h1>
                <p>Debt Management System</p>
            </div>
            
            <h2>Login to Your Account</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn-login">Login</button>
                
                <div class="forgot-password">
                    Forgot your password? <a href="reset-password.php">Reset it here</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Theme toggler already handled by theme.js

        document.addEventListener('DOMContentLoaded', function() {
            // 3D Card effect - FIXED
            const cardStack = document.querySelector('.card-stack');
            const creditCards = document.querySelectorAll('.credit-card');
            const loginContainer = document.querySelector('.login-image');
            
            if (cardStack && loginContainer) {
                // Initialize default transform
                cardStack.style.transition = 'transform 0.4s ease-out';
                
                loginContainer.addEventListener('mousemove', function(e) {
                    const rect = loginContainer.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    
                    // Calculate rotation based on mouse position relative to center
                    const xRotation = (centerY - e.clientY) / 15;
                    const yRotation = (e.clientX - centerX) / 15;
                    
                    // Apply smooth transform
                    cardStack.style.transform = `perspective(1000px) rotateX(${xRotation}deg) rotateY(${yRotation}deg) scale(1.05)`;
                });
                
                // Reset on mouse leave
                loginContainer.addEventListener('mouseleave', function() {
                    cardStack.style.transform = 'rotateY(10deg) rotateX(5deg) scale(1)';
                });
                
                // Add click handler to prevent issues
                cardStack.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Apply a subtle animation when clicked
                    this.style.transition = 'transform 0.2s ease-out';
                    this.style.transform = 'rotateY(0deg) rotateX(0deg) scale(1.1)';
                    
                    // Return to hover state
                    setTimeout(() => {
                        this.style.transition = 'transform 0.4s ease-out';
                        const rect = loginContainer.getBoundingClientRect();
                        const centerX = rect.left + rect.width / 2;
                        const centerY = rect.top + rect.height / 2;
                        
                        // Return to last position
                        const xRotation = (centerY - e.clientY) / 15;
                        const yRotation = (e.clientX - centerX) / 15;
                        
                        this.style.transform = `perspective(1000px) rotateX(${xRotation}deg) rotateY(${yRotation}deg) scale(1.05)`;
                    }, 200);
                });
                
                // Make individual cards interactive
                creditCards.forEach(card => {
                    card.addEventListener('click', function(e) {
                        // Prevent event bubbling
                        e.stopPropagation();
                    });
                });
            }
            
            // Form animation
            const formInputs = document.querySelectorAll('.form-group input');
            formInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focus');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focus');
                    }
                });
                
                // Check if input has value on page load
                if (input.value) {
                    input.parentElement.classList.add('focus');
                }
            });
            
            // Button animation
            const loginButton = document.querySelector('.btn-login');
            if (loginButton) {
                loginButton.addEventListener('mouseenter', function() {
                    this.classList.add('pulse');
                });
                
                loginButton.addEventListener('animationend', function() {
                    this.classList.remove('pulse');
                });
            }
        });
    </script>
</body>
</html> 