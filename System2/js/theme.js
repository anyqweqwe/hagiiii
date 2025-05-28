/**
 * Theme management for DMS application
 * This script ensures consistent dark/light mode across all pages
 */

// Immediately apply theme before any content loads
(function() {
    // Get saved theme or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    // Apply theme to document
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // For pages that might load content dynamically, we also add a class to the body
    if (document.body) {
        document.body.classList.add(`theme-${savedTheme}`);
    }
    
    // Create a global object to manage themes
    window.ThemeManager = {
        // Get current theme
        getCurrentTheme: function() {
            return localStorage.getItem('theme') || 'light';
        },
        
        // Set theme and apply it
        setTheme: function(theme) {
            if (theme !== 'light' && theme !== 'dark') {
                console.error('Invalid theme:', theme);
                return;
            }
            
            // Store in localStorage
            localStorage.setItem('theme', theme);
            
            // Apply to document
            document.documentElement.setAttribute('data-theme', theme);
            
            // Apply to body for consistent styling on dynamic content
            if (document.body) {
                document.body.classList.remove('theme-light', 'theme-dark');
                document.body.classList.add(`theme-${theme}`);
            }
            
            // Update all theme toggle buttons
            this.updateToggleButtons(theme);
            
            // Dispatch event for components that might need to react to theme changes
            document.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
            
            return theme;
        },
        
        // Toggle between light and dark
        toggleTheme: function() {
            const currentTheme = this.getCurrentTheme();
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            if (window.showToast) {
                window.showToast(`${newTheme.charAt(0).toUpperCase() + newTheme.slice(1)} mode activated`, 'info');
            }
            
            return this.setTheme(newTheme);
        },
        
        // Update all theme toggle buttons in the document
        updateToggleButtons: function(theme) {
            // Update all theme toggle buttons
            const toggleButtons = document.querySelectorAll('.theme-toggle, #theme-toggle-login');
            toggleButtons.forEach(button => {
                button.innerHTML = theme === 'dark' ? 
                    '<i class="fas fa-sun"></i>' : 
                    '<i class="fas fa-moon"></i>';
                button.setAttribute('title', 
                    theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
            });
        },
        
        // Add a theme toggle button to the top bar
        addToggleToTopBar: function() {
            const topBar = document.querySelector('.top-bar');
            if (!topBar) return;
            
            // Skip if toggle already exists
            if (topBar.querySelector('.theme-toggle')) return;
            
            const currentTheme = this.getCurrentTheme();
            const themeToggle = document.createElement('button');
            themeToggle.className = 'theme-toggle';
            themeToggle.innerHTML = currentTheme === 'dark' ? 
                '<i class="fas fa-sun"></i>' : 
                '<i class="fas fa-moon"></i>';
            themeToggle.setAttribute('title', 
                currentTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
            
            // Insert before user avatar
            const userAvatar = topBar.querySelector('.user-avatar');
            if (userAvatar) {
                topBar.insertBefore(themeToggle, userAvatar);
            } else {
                topBar.appendChild(themeToggle);
            }
            
            // Add theme toggle functionality
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }
    };
    
    // Add event listener for when DOM is ready to handle any theme-dependent components
    document.addEventListener('DOMContentLoaded', function() {
        // Apply theme again to ensure dynamic components get styled correctly
        const currentTheme = window.ThemeManager.getCurrentTheme();
        
        // Add class to body
        document.body.classList.add(`theme-${currentTheme}`);
        
        // Add theme toggle to login page if it exists
        const loginThemeToggle = document.getElementById('theme-toggle-login');
        if (loginThemeToggle) {
            // Update the icon based on current theme
            loginThemeToggle.innerHTML = currentTheme === 'dark' ? 
                '<i class="fas fa-sun"></i>' : 
                '<i class="fas fa-moon"></i>';
            
            // Set title
            loginThemeToggle.setAttribute('title', 
                currentTheme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
            
            // Add event listener
            loginThemeToggle.addEventListener('click', () => window.ThemeManager.toggleTheme());
        }
        
        // Add theme toggle to top bar (dashboard pages)
        window.ThemeManager.addToggleToTopBar();
    });
})(); 