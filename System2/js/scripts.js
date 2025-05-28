// Wait for DOM content to load
document.addEventListener('DOMContentLoaded', function() {
    // Add floating action button for quick creation
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        const actionButton = document.createElement('div');
        actionButton.className = 'floating-action-btn';
        actionButton.innerHTML = '<i class="fas fa-plus"></i>';
        actionButton.setAttribute('title', 'Quick Actions');
        
        // Create and append menu
        const actionMenu = document.createElement('div');
        actionMenu.className = 'floating-action-menu';
        actionMenu.style.display = 'none';
        
        // Add menu items based on context
        if (window.location.href.includes('debtors.php')) {
            actionMenu.innerHTML = `
                <a href="add-debtor.php"><i class="fas fa-user-plus"></i> Add Debtor</a>
                <a href="financial-summary.php"><i class="fas fa-chart-line"></i> View Summary</a>
            `;
        } else if (window.location.href.includes('payments.php')) {
            actionMenu.innerHTML = `
                <a href="add-payment.php"><i class="fas fa-money-bill-wave"></i> Add Payment</a>
                <a href="overdue.php"><i class="fas fa-exclamation-circle"></i> View Overdue</a>
            `;
        } else {
            actionMenu.innerHTML = `
                <a href="add-debtor.php"><i class="fas fa-user-plus"></i> Add Debtor</a>
                <a href="add-payment.php"><i class="fas fa-money-bill-wave"></i> Add Payment</a>
            `;
        }
        
        // Toggle menu on button click
        actionButton.addEventListener('click', function(e) {
            e.stopPropagation();
            if (actionMenu.style.display === 'none') {
                actionMenu.style.display = 'flex';
                actionButton.classList.add('active');
            } else {
                actionMenu.style.display = 'none';
                actionButton.classList.remove('active');
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function() {
            actionMenu.style.display = 'none';
            actionButton.classList.remove('active');
        });
        
        // Append to main content
        mainContent.appendChild(actionButton);
        mainContent.appendChild(actionMenu);
    }
    
    // Mobile sidebar toggle with improved animation
    const toggleButton = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (toggleButton && sidebar) {
        toggleButton.addEventListener('click', function() {
            sidebar.classList.toggle('expanded');
            
            // Add animation class to indicate the transition
            document.querySelector('.main-content').classList.add('adjusting');
            setTimeout(() => {
                document.querySelector('.main-content').classList.remove('adjusting');
            }, 300);
        });
    }
    
    // Close sidebar when clicking outside on mobile - improved with better event handling
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            sidebar && 
            sidebar.classList.contains('expanded') && 
            !sidebar.contains(e.target) && 
            e.target !== toggleButton &&
            !e.target.closest('#sidebar-toggle')) {
            sidebar.classList.remove('expanded');
        }
    });
    
    // Create Toast notification system
    function createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }
    
    // Show toast notification
    window.showToast = function(message, type = 'success', duration = 1000) {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = createToastContainer();
        }
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Set icon based on type
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';
        if (type === 'warning') icon = 'exclamation-triangle';
        
        toast.innerHTML = `
            <div class="toast-icon"><i class="fas fa-${icon}"></i></div>
            <div class="toast-content">${message}</div>
            <button class="toast-close"><i class="fas fa-times"></i></button>
        `;
        
        // Add to container
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.add('show');
        }, 2);
        
        // Set up auto dismiss
        const dismissTimeout = setTimeout(() => {
            dismissToast(toast);
        }, duration);
        
        // Close button functionality
        toast.querySelector('.toast-close').addEventListener('click', () => {
            clearTimeout(dismissTimeout);
            dismissToast(toast);
        });
        
        // Pause timeout on hover
        toast.addEventListener('mouseenter', () => {
            clearTimeout(dismissTimeout);
        });
        
        // Resume timeout on mouse leave
        toast.addEventListener('mouseleave', () => {
            const newTimeout = setTimeout(() => {
                dismissToast(toast);
            }, duration);
        });
        
        return toast;
    };
    
    // Dismiss toast animation
    function dismissToast(toast) {
        toast.classList.remove('show');
        toast.classList.add('hiding');
        
        setTimeout(() => {
            toast.remove();
        }, 100);
    }
    
    // Add CSS for toast notifications
    function addToastStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 350px;
            }
            
            .toast {
                background-color: var(--card-bg);
                border-left: 4px solid var(--primary-color);
                box-shadow: var(--shadow-md);
                border-radius: var(--radius-md);
                padding: 15px;
                display: flex;
                align-items: center;
                opacity: 0;
                transform: translateX(50px);
                transition: all 0.15s ease;
                overflow: hidden;
            }
            
            .toast.show {
                opacity: 1;
                transform: translateX(0);
            }
            
            .toast.hiding {
                opacity: 0;
                transform: translateX(50px);
            }
            
            .toast-success {
                border-left-color: var(--success-color);
            }
            
            .toast-error {
                border-left-color: var(--danger-color);
            }
            
            .toast-warning {
                border-left-color: var(--warning-color);
            }
            
            .toast-info {
                border-left-color: var(--accent-color);
            }
            
            .toast-icon {
                margin-right: 12px;
                font-size: 20px;
            }
            
            .toast-success .toast-icon {
                color: var(--success-color);
            }
            
            .toast-error .toast-icon {
                color: var(--danger-color);
            }
            
            .toast-warning .toast-icon {
                color: var(--warning-color);
            }
            
            .toast-info .toast-icon {
                color: var(--accent-color);
            }
            
            .toast-content {
                flex: 1;
                color: var(--text-primary);
            }
            
            .toast-close {
                background: transparent;
                border: none;
                color: var(--text-secondary);
                cursor: pointer;
                font-size: 14px;
                margin-left: 10px;
                opacity: 0.7;
                transition: opacity 0.3s;
            }
            
            .toast-close:hover {
                opacity: 1;
            }
            
            .floating-action-menu {
                position: fixed;
                bottom: 100px;
                right: 30px;
                background-color: var(--card-bg);
                border-radius: var(--radius-md);
                box-shadow: var(--shadow-lg);
                padding: 10px 0;
                display: flex;
                flex-direction: column;
                z-index: 99;
                min-width: 200px;
                transition: all 0.3s ease;
            }
            
            .floating-action-menu a {
                padding: 12px 20px;
                color: var(--text-primary);
                display: flex;
                align-items: center;
                transition: all 0.2s ease;
            }
            
            .floating-action-menu a i {
                margin-right: 10px;
                width: 20px;
                text-align: center;
                color: var(--primary-color);
            }
            
            .floating-action-menu a:hover {
                background-color: rgba(37, 99, 235, 0.1);
            }
            
            .floating-action-btn.active {
                transform: rotate(45deg);
                background: linear-gradient(135deg, var(--danger-color), var(--warning-color));
            }
        `;
        document.head.appendChild(style);
    }
    
    // Add toast styles to document
    addToastStyles();
    
    // Add print functionality with date stamping
    const printButtons = document.querySelectorAll('.print-btn');
    printButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add current date for the print
            const now = new Date();
            const formattedDate = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
            document.querySelector('.dashboard-content').setAttribute('data-print-date', formattedDate);
            
            // Show toast notification
            showToast('Preparing document for printing...', 'info');
            
            // Print the document
            setTimeout(() => {
                window.print();
            }, 250);
        });
    });
    
    // Dynamic table sorting
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sortKey = this.getAttribute('data-sort');
            const tbody = this.closest('table').querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Toggle sort direction
            const currentDirection = this.getAttribute('data-direction') || 'asc';
            const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
            
            // Update all headers
            sortableHeaders.forEach(h => h.removeAttribute('data-direction'));
            this.setAttribute('data-direction', newDirection);
            
            // Sort the rows
            rows.sort((a, b) => {
                let aValue = a.querySelector(`td[data-key="${sortKey}"]`)?.textContent || '';
                let bValue = b.querySelector(`td[data-key="${sortKey}"]`)?.textContent || '';
                
                // Try parsing as numbers if possible
                const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
                const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return newDirection === 'asc' ? aNum - bNum : bNum - aNum;
                }
                
                // Otherwise sort as strings
                return newDirection === 'asc' 
                    ? aValue.localeCompare(bValue) 
                    : bValue.localeCompare(aValue);
            });
            
            // Re-append rows in the new order with animation
            rows.forEach((row, index) => {
                row.style.animation = 'none';
                row.offsetHeight; // Force reflow
                row.style.animation = `fadeIn 0.3s ease forwards`;
                row.style.animationDelay = `${index * 0.05}s`;
                tbody.appendChild(row);
            });
            
            // Show toast notification
            showToast(`Sorted by ${header.textContent.trim()} (${newDirection === 'asc' ? 'Ascending' : 'Descending'})`, 'info');
        });
    });
    
    // Handle card form fields visibility with smoother transitions
    const paymentMethodSelect = document.getElementById('payment_method');
    const referenceNumberGroup = document.getElementById('reference_number_group');
    const cardDetailsGroup = document.getElementById('card_details_group');
    
    if (paymentMethodSelect && referenceNumberGroup && cardDetailsGroup) {
        function updateVisibility() {
            const selectedMethod = paymentMethodSelect.value;
            const requiresReference = ['Bank Transfer', 'Check', 'PayPal', 'Money Order', 'Bitcoin'].includes(selectedMethod);
            const requiresCard = ['Credit Card', 'Debit Card'].includes(selectedMethod);
            
            // Animate transitions
            if (requiresReference) {
                referenceNumberGroup.style.opacity = '0';
                referenceNumberGroup.style.maxHeight = '0';
                referenceNumberGroup.style.display = 'block';
                setTimeout(() => {
                    referenceNumberGroup.style.opacity = '1';
                    referenceNumberGroup.style.maxHeight = '100px';
                }, 10);
            } else {
                referenceNumberGroup.style.opacity = '0';
                referenceNumberGroup.style.maxHeight = '0';
                setTimeout(() => {
                    referenceNumberGroup.style.display = 'none';
                }, 300);
            }
            
            if (requiresCard) {
                cardDetailsGroup.style.opacity = '0';
                cardDetailsGroup.style.maxHeight = '0';
                cardDetailsGroup.style.display = 'block';
                setTimeout(() => {
                    cardDetailsGroup.style.opacity = '1';
                    cardDetailsGroup.style.maxHeight = '200px';
                }, 10);
            } else {
                cardDetailsGroup.style.opacity = '0';
                cardDetailsGroup.style.maxHeight = '0';
                setTimeout(() => {
                    cardDetailsGroup.style.display = 'none';
                }, 300);
            }
        }
        
        // Apply transition styles
        [referenceNumberGroup, cardDetailsGroup].forEach(el => {
            el.style.transition = 'opacity 0.3s ease, max-height 0.3s ease';
            el.style.overflow = 'hidden';
        });
        
        // Set initial state and add event listener
        updateVisibility();
        paymentMethodSelect.addEventListener('change', updateVisibility);
        paymentMethodSelect.addEventListener('change', function() {
            showToast(`Payment method updated to ${this.value}`, 'info');
        });
    }
    
    // Form validation enhancement
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Get all required fields
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;
            let firstInvalid = null;
            
            requiredFields.forEach(field => {
                // Remove previous error
                const formGroup = field.closest('.form-group');
                if (formGroup) {
                    formGroup.classList.remove('has-error');
                    const existingError = formGroup.querySelector('.field-error');
                    if (existingError) existingError.remove();
                }
                
                // Check validity
                if (!field.value.trim()) {
                    valid = false;
                    if (!firstInvalid) firstInvalid = field;
                    
                    // Add error indication
                    if (formGroup) {
                        formGroup.classList.add('has-error');
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'field-error';
                        errorMessage.textContent = 'This field is required';
                        formGroup.appendChild(errorMessage);
                    }
                }
            });
            
            // If form is invalid
            if (!valid) {
                e.preventDefault();
                if (firstInvalid) firstInvalid.focus();
                showToast('Please fill in all required fields', 'error');
                
                // Add styles for error state
                if (!document.querySelector('#form-validation-styles')) {
                    const style = document.createElement('style');
                    style.id = 'form-validation-styles';
                    style.textContent = `
                        .form-group.has-error input, 
                        .form-group.has-error select, 
                        .form-group.has-error textarea {
                            border-color: var(--danger-color);
                            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
                        }
                        
                        .field-error {
                            color: var(--danger-color);
                            font-size: 0.8rem;
                            margin-top: 0.3rem;
                            animation: fadeIn 0.3s ease;
                        }
                    `;
                    document.head.appendChild(style);
                }
            } else {
                showToast('Form submitted successfully!', 'success');
            }
        });
    });
    
    // Enable CSV export functionality
    const exportButtons = document.querySelectorAll('.export-csv-btn');
    exportButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const table = document.querySelector(this.getAttribute('data-target'));
            if (!table) return;
            
            // Show processing toast
            showToast('Preparing CSV export...', 'info');
            
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            // Extract headers
            const headers = Array.from(rows[0].querySelectorAll('th')).map(th => 
                '"' + th.textContent.trim().replace(/"/g, '""') + '"'
            );
            csv.push(headers.join(','));
            
            // Extract data rows
            for (let i = 1; i < rows.length; i++) {
                const row = [];
                const cells = rows[i].querySelectorAll('td');
                cells.forEach(cell => {
                    row.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
                });
                csv.push(row.join(','));
            }
            
            // Create download link
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            
            const filename = 'export_' + new Date().toISOString().split('T')[0] + '.csv';
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.display = 'none';
            document.body.appendChild(link);
            
            // Delay to show processing
            setTimeout(() => {
                link.click();
                document.body.removeChild(link);
                showToast(`CSV exported successfully as ${filename}`, 'success');
            }, 400);
        });
    });
    
    // Initial welcome toast on dashboard
    if (window.location.href.includes('dashboard.php')) {
        setTimeout(() => {
            showToast('Welcome to the Debt Management System!', 'info', 5000);
        }, 500);
    }
});

// Toggle fee sections in settings page
function toggleFeeSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.toggle('active');
        const button = section.querySelector('.toggle-section');
        if (button) {
            button.innerHTML = section.classList.contains('active') ? 
                '<i class="fas fa-chevron-up"></i>' : 
                '<i class="fas fa-chevron-down"></i>';
        }
    }
}

// Print report utility for settings page
window.printReport = function(type) {
    let url = '';
    if (type === 'debtors') url = 'debtors.php?print=1';
    else if (type === 'payments') url = 'payments.php?print=1';
    else if (type === 'overdue') url = 'overdue.php?print=1';
    else return;

    // Open the report in a new window
    const printWindow = window.open(url, '_blank');
    if (!printWindow) {
        showToast('Popup blocked! Please allow popups for this site.', 'error');
        return;
    }
    // Fallback: try to print after a delay if onload doesn't fire
    let printed = false;
    function doPrint() {
        if (printed) return;
        printed = true;
        printWindow.focus();
        printWindow.print();
        printWindow.onafterprint = function() { printWindow.close(); };
    }
    printWindow.onload = doPrint;
    setTimeout(doPrint, 1200); // fallback after 1.2s
}; 