/**
 * Debt Management System Animations
 */

// Create and append loading screen
function createLoadingScreen() {
    const loadingScreen = document.createElement('div');
    loadingScreen.className = 'loading-screen';
    loadingScreen.innerHTML = `
        <div class="loading-content">
            <div class="loading-logo">DMS</div>
            <div class="loading-spinner">
                <div class="spinner-circle"></div>
                <div class="spinner-circle-outer"></div>
            </div>
            <div class="loading-text">Loading your dashboard...</div>
            <div class="loading-progress">
                <div class="progress-bar"></div>
            </div>
        </div>
    `;
    document.body.appendChild(loadingScreen);
    
    // Add styles for loading screen
    const style = document.createElement('style');
    style.textContent = `
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--light-bg, #f8fafc);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        
        .loading-screen.hiding {
            opacity: 0;
            visibility: hidden;
        }
        
        .loading-content {
            text-align: center;
            max-width: 400px;
            padding: 2rem;
        }
        
        .loading-logo {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color, #2563eb);
            margin-bottom: 1.5rem;
            letter-spacing: -0.05em;
            text-shadow: 0 2px 10px rgba(37, 99, 235, 0.2);
            animation: pulseLogo 2s infinite ease-in-out;
        }
        
        @keyframes pulseLogo {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .loading-spinner {
            position: relative;
            width: 70px;
            height: 70px;
            margin: 0 auto 1.5rem;
        }
        
        .spinner-circle, .spinner-circle-outer {
            position: absolute;
            border: 4px solid transparent;
            border-top-color: var(--primary-color, #2563eb);
            border-radius: 50%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }
        
        .spinner-circle {
            animation: spin 0.5s linear infinite;
            border-top-color: var(--primary-color, #2563eb);
        }
        
        .spinner-circle-outer {
            animation: spin 1s linear infinite;
            border-left-color: var(--accent-color, #0ea5e9);
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            color: var(--text-secondary, #64748b);
            margin-bottom: 1rem;
            font-size: 1rem;
            animation: pulse 1.5s infinite ease-in-out;
        }
        
        .loading-progress {
            height: 4px;
            background-color: rgba(37, 99, 235, 0.1);
            border-radius: 4px;
            overflow: hidden;
            margin: 0 auto;
            width: 100%;
        }
        
        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary-color, #2563eb), var(--accent-color, #0ea5e9));
            border-radius: 4px;
            transition: width 0.3s ease;
            animation: progressAnim 2s ease forwards;
        }
        
        @keyframes progressAnim {
            0% { width: 0%; }
            10% { width: 40%; }
            30% { width: 75%; }
            60% { width: 90%; }
            100% { width: 100%; }
        }
        
        [data-theme="dark"] .loading-screen {
            background-color: var(--light-bg, #0f172a);
        }
    `;
    document.head.appendChild(style);
    
    return loadingScreen;
}

// Document ready event
document.addEventListener('DOMContentLoaded', function() {
    // Show loading screen
    const loadingScreen = createLoadingScreen();
    
    // Simulate loading time and hide loading screen
    setTimeout(() => {
        loadingScreen.classList.add('hiding');
        setTimeout(() => {
            loadingScreen.remove();
        }, 150);
        
        // Start page animations after loading
        initializePageAnimations();
    }, 450);
});

// Initialize page animations (separated for clarity)
function initializePageAnimations() {
    // Page load animation with staggered element reveals
    document.body.classList.add('fade-in');
    
    // Enhanced card animations with staggered timing
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate-in');
        
        // Add hover state animation handlers
        card.addEventListener('mouseenter', () => {
            card.querySelector('.stat-icon')?.classList.add('pulse');
        });
        
        card.addEventListener('mouseleave', () => {
            card.querySelector('.stat-icon')?.classList.remove('pulse');
        });
    });

    // Improved table row animations with staggered timing
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach((row, index) => {
        row.style.animationDelay = `${0.2 + index * 0.05}s`;
        row.classList.add('slide-in');
        
        // Highlight rows on hover to improve interaction
        row.addEventListener('mouseenter', () => {
            row.style.transition = 'all 0.2s ease';
            row.style.backgroundColor = 'rgba(37, 99, 235, 0.04)';
        });
        
        row.addEventListener('mouseleave', () => {
            row.style.backgroundColor = '';
        });
    });

    // Form field animations with better focus states
    const formFields = document.querySelectorAll('.form-group');
    formFields.forEach((field, index) => {
        field.style.animationDelay = `${0.1 + index * 0.05}s`;
        field.classList.add('fade-in-up');
        
        // Add focus animation to form fields
        const inputs = field.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                field.classList.add('focus');
            });
            
            input.addEventListener('blur', () => {
                field.classList.remove('focus');
            });
        });
    });

    // Success message animation with improved timing
    const alertMessages = document.querySelectorAll('.alert');
    alertMessages.forEach(message => {
        message.classList.add('bounce-in');
        
        // Auto-hide success messages after 5 seconds with smoother transition
        if (message.classList.contains('alert-success')) {
            setTimeout(() => {
                message.classList.add('fade-out');
                setTimeout(() => {
                    message.style.display = 'none';
                }, 500);
            }, 5000);
        }
    });

    // Enhance buttons with interactive animations
    const actionButtons = document.querySelectorAll('.btn-primary, .btn-secondary');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', () => {
            button.classList.add('pulse');
        });
        
        button.addEventListener('animationend', () => {
            button.classList.remove('pulse');
        });
        
        // Add ripple effect to buttons
        button.addEventListener('click', createRippleEffect);
    });
    
    // Enhance sidebar toggle animation
    const toggleButton = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (toggleButton && sidebar) {
        toggleButton.addEventListener('click', function() {
            sidebar.classList.add('animated');
            setTimeout(() => {
                sidebar.classList.remove('animated');
            }, 300);
            
            // Rotate toggle button icon
            const icon = toggleButton.querySelector('i');
            if (icon) {
                icon.style.transition = 'transform 0.3s';
                if (sidebar.classList.contains('expanded')) {
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    icon.style.transform = 'rotate(180deg)';
                }
            }
        });
    }
    
    // Notification badge pulse animation
    const notificationBadges = document.querySelectorAll('.notification-badge');
    notificationBadges.forEach(badge => {
        badge.classList.add('pulse');
    });
    
    // Add smooth scrolling to anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== "#") {
                e.preventDefault();
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Create ripple effect function for buttons
    function createRippleEffect(e) {
        const button = e.currentTarget;
        
        // Remove any existing ripple
        const ripple = button.querySelector('.ripple');
        if (ripple) {
            ripple.remove();
        }
        
        // Create new ripple element
        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        
        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${e.clientX - button.getBoundingClientRect().left - diameter / 2}px`;
        circle.style.top = `${e.clientY - button.getBoundingClientRect().top - diameter / 2}px`;
        circle.classList.add('ripple');
        
        button.appendChild(circle);
        
        // Remove ripple after animation completes
        setTimeout(() => {
            circle.remove();
        }, 300);
    }
} 