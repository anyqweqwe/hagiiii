<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Initialize settings if they don't exist in session
if (!isset($_SESSION['settings'])) {
    $_SESSION['settings'] = [
        'document_management' => [
            'auto_generate_schedules' => true,
            'welcome_letters' => true,
            'payment_confirmations' => true,
            'version_tracking' => false
        ],
        'interest' => [
            'calculation_method' => 'reducing_balance',
            'annual_rate' => 5.00, 
            'compound_frequency' => 'monthly'
        ],
        'fees' => [
            'late_fee' => [
                'enabled' => true,
                'amount' => 25.00,
                'type' => 'fixed', // fixed or percentage
                'grace_period' => 3 // days
            ],
            'processing_fee' => [
                'enabled' => true,
                'amount' => 5.00,
                'type' => 'fixed'
            ],
            'setup_fee' => [
                'enabled' => true,
                'amount' => 50.00,
                'type' => 'fixed'
            ]
        ],
        'compounding' => [
            'interest_compounds' => true,
            'fees_generate_interest' => false
        ]
    ];
}

// Handle form submission
$show_message = false;
$message = '';
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        // Document Management settings
        $_SESSION['settings']['document_management']['auto_generate_schedules'] = isset($_POST['auto_generate_schedules']);
        $_SESSION['settings']['document_management']['welcome_letters'] = isset($_POST['welcome_letters']);
        $_SESSION['settings']['document_management']['payment_confirmations'] = isset($_POST['payment_confirmations']);
        $_SESSION['settings']['document_management']['version_tracking'] = isset($_POST['version_tracking']);
        
        // Interest settings
        $_SESSION['settings']['interest']['calculation_method'] = $_POST['calculation_method'];
        $_SESSION['settings']['interest']['annual_rate'] = floatval($_POST['annual_rate']);
        $_SESSION['settings']['interest']['compound_frequency'] = $_POST['compound_frequency'];
        
        // Fee settings
        $_SESSION['settings']['fees']['late_fee']['enabled'] = isset($_POST['late_fee_enabled']);
        $_SESSION['settings']['fees']['late_fee']['amount'] = floatval($_POST['late_fee_amount']);
        $_SESSION['settings']['fees']['late_fee']['type'] = $_POST['late_fee_type'];
        $_SESSION['settings']['fees']['late_fee']['grace_period'] = intval($_POST['grace_period']);
        
        $_SESSION['settings']['fees']['processing_fee']['enabled'] = isset($_POST['processing_fee_enabled']);
        $_SESSION['settings']['fees']['processing_fee']['amount'] = floatval($_POST['processing_fee_amount']);
        $_SESSION['settings']['fees']['processing_fee']['type'] = $_POST['processing_fee_type'];
        
        $_SESSION['settings']['fees']['setup_fee']['enabled'] = isset($_POST['setup_fee_enabled']);
        $_SESSION['settings']['fees']['setup_fee']['amount'] = floatval($_POST['setup_fee_amount']);
        $_SESSION['settings']['fees']['setup_fee']['type'] = $_POST['setup_fee_type'];
        
        // Compounding settings
        $_SESSION['settings']['compounding']['interest_compounds'] = isset($_POST['interest_compounds']);
        $_SESSION['settings']['compounding']['fees_generate_interest'] = isset($_POST['fees_generate_interest']);
        
        $show_message = true;
        $message = "Settings have been saved successfully!";
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

// Set page title and custom CSS
$page_title = "Settings - DMS";
$extra_css = "
    .settings-container {
        margin: 20px 0;
    }
    .settings-section {
        background-color: var(--card-bg);
        border-radius: 8px;
        box-shadow: var(--shadow-md);
        padding: 20px;
        margin-bottom: 20px;
    }
    .settings-section h3 {
        margin-top: 0;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 10px;
        margin-bottom: 20px;
        color: var(--primary-color);
        display: flex;
        align-items: center;
    }
    .settings-section h3 i {
        margin-right: 10px;
    }
    .settings-group {
        margin-bottom: 15px;
    }
    .checkbox-group {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    .checkbox-group input[type=\"checkbox\"] {
        margin-right: 10px;
        width: auto;
    }
    .inline-input {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    .inline-input label {
        min-width: 200px;
        margin-bottom: 0;
        color: var(--text-secondary);
    }
    .inline-input input, .inline-input select {
        width: auto;
        margin-right: 10px;
    }
    .description {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin-top: 5px;
    }
    .fee-section {
        background-color: rgba(var(--light-bg-rgb), 0.5);
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .fee-section h4 {
        margin-top: 0;
        color: var(--text-primary);
    }
    .fee-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .fee-details {
        padding-left: 20px;
        display: none;
    }
    .fee-section.active .fee-details {
        display: block;
    }
    .toggle-section {
        background: none;
        border: none;
        color: var(--primary-color);
        cursor: pointer;
        font-size: 1.2rem;
    }
    .report-actions, .export-actions {
        margin-bottom: 20px;
    }
";

// Include header
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
            <h1>Settings</h1>
            
            <?php if($show_message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="settings.php">
                <div class="settings-container">
                    <!-- Document Management Section -->
                    <div class="settings-section">
                        <h3><i class="fas fa-file-alt"></i> Document Management</h3>
                        <div class="settings-group">
                            <p class="description">Configure automatic document generation and tracking features.</p>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="auto_generate_schedules" name="auto_generate_schedules" 
                                    <?php echo ($_SESSION['settings']['document_management']['auto_generate_schedules']) ? 'checked' : ''; ?>>
                                <label for="auto_generate_schedules">Auto-generate payment schedules</label>
                            </div>
                            <p class="description">Automatically create payment schedule documents for new debt agreements.</p>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="welcome_letters" name="welcome_letters" 
                                    <?php echo ($_SESSION['settings']['document_management']['welcome_letters']) ? 'checked' : ''; ?>>
                                <label for="welcome_letters">Generate welcome letters</label>
                            </div>
                            <p class="description">Create personalized welcome letters when adding new debtors.</p>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="payment_confirmations" name="payment_confirmations" 
                                    <?php echo ($_SESSION['settings']['document_management']['payment_confirmations']) ? 'checked' : ''; ?>>
                                <label for="payment_confirmations">Send payment confirmations</label>
                            </div>
                            <p class="description">Generate confirmation documents when payments are received.</p>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="version_tracking" name="version_tracking" 
                                    <?php echo ($_SESSION['settings']['document_management']['version_tracking']) ? 'checked' : ''; ?>>
                                <label for="version_tracking">Enable version tracking for documents</label>
                            </div>
                            <p class="description">Track version history of signed documents and compliance notices.</p>
                        </div>
                    </div>
                    
                    <!-- Interest Settings Section -->
                    <div class="settings-section">
                        <h3><i class="fas fa-percent"></i> Interest Settings</h3>
                        <div class="settings-group">
                            <p class="description">Configure how interest is calculated on debts.</p>
                            
                            <div class="inline-input">
                                <label for="calculation_method">Interest calculation method:</label>
                                <select id="calculation_method" name="calculation_method">
                                    <option value="flat_rate" <?php echo ($_SESSION['settings']['interest']['calculation_method'] === 'flat_rate') ? 'selected' : ''; ?>>Flat Rate</option>
                                    <option value="reducing_balance" <?php echo ($_SESSION['settings']['interest']['calculation_method'] === 'reducing_balance') ? 'selected' : ''; ?>>Reducing Balance</option>
                                    <option value="daily_accrual" <?php echo ($_SESSION['settings']['interest']['calculation_method'] === 'daily_accrual') ? 'selected' : ''; ?>>Daily Accrual</option>
                                    <option value="compound" <?php echo ($_SESSION['settings']['interest']['calculation_method'] === 'compound') ? 'selected' : ''; ?>>Compound Interest</option>
                                </select>
                            </div>
                            <p class="description">Select the method used to calculate interest on outstanding balances.</p>
                            
                            <div class="inline-input">
                                <label for="annual_rate">Annual interest rate (%):</label>
                                <input type="number" id="annual_rate" name="annual_rate" step="0.01" min="0" max="100" 
                                    value="<?php echo $_SESSION['settings']['interest']['annual_rate']; ?>">
                            </div>
                            <p class="description">Default annual interest rate applied to new debts.</p>
                            
                            <div class="inline-input">
                                <label for="compound_frequency">Compounding frequency:</label>
                                <select id="compound_frequency" name="compound_frequency">
                                    <option value="daily" <?php echo ($_SESSION['settings']['interest']['compound_frequency'] === 'daily') ? 'selected' : ''; ?>>Daily</option>
                                    <option value="weekly" <?php echo ($_SESSION['settings']['interest']['compound_frequency'] === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                                    <option value="monthly" <?php echo ($_SESSION['settings']['interest']['compound_frequency'] === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                                    <option value="quarterly" <?php echo ($_SESSION['settings']['interest']['compound_frequency'] === 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                                    <option value="annually" <?php echo ($_SESSION['settings']['interest']['compound_frequency'] === 'annually') ? 'selected' : ''; ?>>Annually</option>
                                </select>
                            </div>
                            <p class="description">How often interest is compounded (if using compound interest).</p>
                        </div>
                    </div>
                    
                    <!-- Fees Settings Section -->
                    <div class="settings-section">
                        <h3><i class="fas fa-dollar-sign"></i> Fee Structure</h3>
                        <div class="settings-group">
                            <p class="description">Configure various fees applied to debt accounts.</p>
                            
                            <!-- Late Fee -->
                            <div class="fee-section active" id="late-fee-section">
                                <div class="fee-header">
                                    <h4>Late Fees</h4>
                                    <button type="button" class="toggle-section" onclick="toggleFeeSection('late-fee-section')">
                                        <i class="fas fa-chevron-up"></i>
                                    </button>
                                </div>
                                <div class="fee-details">
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="late_fee_enabled" name="late_fee_enabled" 
                                            <?php echo ($_SESSION['settings']['fees']['late_fee']['enabled']) ? 'checked' : ''; ?>>
                                        <label for="late_fee_enabled">Enable late fees</label>
                                    </div>
                                    
                                    <div class="inline-input">
                                        <label for="late_fee_amount">Amount:</label>
                                        <input type="number" id="late_fee_amount" name="late_fee_amount" step="0.01" min="0" 
                                            value="<?php echo $_SESSION['settings']['fees']['late_fee']['amount']; ?>">
                                        <select id="late_fee_type" name="late_fee_type">
                                            <option value="fixed" <?php echo ($_SESSION['settings']['fees']['late_fee']['type'] === 'fixed') ? 'selected' : ''; ?>>Fixed ($)</option>
                                            <option value="percentage" <?php echo ($_SESSION['settings']['fees']['late_fee']['type'] === 'percentage') ? 'selected' : ''; ?>>Percentage (%)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="inline-input">
                                        <label for="grace_period">Grace period (days):</label>
                                        <input type="number" id="grace_period" name="grace_period" min="0" 
                                            value="<?php echo $_SESSION['settings']['fees']['late_fee']['grace_period']; ?>">
                                    </div>
                                    <p class="description">Days after due date before late fee applies.</p>
                                </div>
                            </div>
                            
                            <!-- Processing Fee -->
                            <div class="fee-section" id="processing-fee-section">
                                <div class="fee-header">
                                    <h4>Processing Fees</h4>
                                    <button type="button" class="toggle-section" onclick="toggleFeeSection('processing-fee-section')">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="fee-details">
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="processing_fee_enabled" name="processing_fee_enabled" 
                                            <?php echo ($_SESSION['settings']['fees']['processing_fee']['enabled']) ? 'checked' : ''; ?>>
                                        <label for="processing_fee_enabled">Enable processing fees</label>
                                    </div>
                                    
                                    <div class="inline-input">
                                        <label for="processing_fee_amount">Amount:</label>
                                        <input type="number" id="processing_fee_amount" name="processing_fee_amount" step="0.01" min="0" 
                                            value="<?php echo $_SESSION['settings']['fees']['processing_fee']['amount']; ?>">
                                        <select id="processing_fee_type" name="processing_fee_type">
                                            <option value="fixed" <?php echo ($_SESSION['settings']['fees']['processing_fee']['type'] === 'fixed') ? 'selected' : ''; ?>>Fixed ($)</option>
                                            <option value="percentage" <?php echo ($_SESSION['settings']['fees']['processing_fee']['type'] === 'percentage') ? 'selected' : ''; ?>>Percentage (%)</option>
                                        </select>
                                    </div>
                                    <p class="description">Fee applied when processing payments or transactions.</p>
                                </div>
                            </div>
                            
                            <!-- Setup Fee -->
                            <div class="fee-section" id="setup-fee-section">
                                <div class="fee-header">
                                    <h4>Setup Fees</h4>
                                    <button type="button" class="toggle-section" onclick="toggleFeeSection('setup-fee-section')">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="fee-details">
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="setup_fee_enabled" name="setup_fee_enabled" 
                                            <?php echo ($_SESSION['settings']['fees']['setup_fee']['enabled']) ? 'checked' : ''; ?>>
                                        <label for="setup_fee_enabled">Enable setup fees</label>
                                    </div>
                                    
                                    <div class="inline-input">
                                        <label for="setup_fee_amount">Amount:</label>
                                        <input type="number" id="setup_fee_amount" name="setup_fee_amount" step="0.01" min="0" 
                                            value="<?php echo $_SESSION['settings']['fees']['setup_fee']['amount']; ?>">
                                        <select id="setup_fee_type" name="setup_fee_type">
                                            <option value="fixed" <?php echo ($_SESSION['settings']['fees']['setup_fee']['type'] === 'fixed') ? 'selected' : ''; ?>>Fixed ($)</option>
                                            <option value="percentage" <?php echo ($_SESSION['settings']['fees']['setup_fee']['type'] === 'percentage') ? 'selected' : ''; ?>>Percentage (%)</option>
                                        </select>
                                    </div>
                                    <p class="description">One-time fee charged when creating a new debt account.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Compounding Settings -->
                    <div class="settings-section">
                        <h3><i class="fas fa-sync"></i> Rollover & Compounding</h3>
                        <div class="settings-group">
                            <p class="description">Configure how interest and fees compound over time.</p>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="interest_compounds" name="interest_compounds" 
                                    <?php echo ($_SESSION['settings']['compounding']['interest_compounds']) ? 'checked' : ''; ?>>
                                <label for="interest_compounds">Interest compounds according to frequency</label>
                            </div>
                            <p class="description">When enabled, interest will be added to the principal for future interest calculations.</p>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="fees_generate_interest" name="fees_generate_interest" 
                                    <?php echo ($_SESSION['settings']['compounding']['fees_generate_interest']) ? 'checked' : ''; ?>>
                                <label for="fees_generate_interest">Fees generate interest</label>
                            </div>
                            <p class="description">When enabled, fees added to an account will also accrue interest.</p>
                        </div>
                    </div>
                    
                    <!-- Export & Reports Section -->
                    <div class="settings-section">
                        <h3><i class="fas fa-file-export"></i> Export & Reports</h3>
                        <div class="settings-group">
                            <p class="description">Generate reports and export data from your debt management system.</p>
                            
                            <div class="report-actions">
                                <h4>Print Reports</h4>
                                <div class="action-group">
                                    <button type="button" class="btn-secondary" onclick="printReport('debtors')">
                                        <i class="fas fa-print"></i> Debtors Report
                                    </button>
                                    <button type="button" class="btn-secondary" onclick="printReport('payments')">
                                        <i class="fas fa-print"></i> Payments Report
                                    </button>
                                    <button type="button" class="btn-secondary" onclick="printReport('overdue')">
                                        <i class="fas fa-print"></i> Overdue Debts Report
                                    </button>
                                </div>
                                <p class="description">Generate printable reports for different aspects of your debt management.</p>
                            </div>
                            
                            <div class="export-actions">
                                <h4>Export Data (CSV)</h4>
                                <div class="action-group">
                                    <a href="export-data.php?type=debtors" class="btn-secondary">
                                        <i class="fas fa-file-csv"></i> Export Debtors
                                    </a>
                                    <a href="export-data.php?type=payments" class="btn-secondary">
                                        <i class="fas fa-file-csv"></i> Export Payments
                                    </a>
                                    <a href="export-data.php?type=overdue" class="btn-secondary">
                                        <i class="fas fa-file-csv"></i> Export Overdue Debts
                                    </a>
                                </div>
                                <p class="description">Download data in CSV format for use in spreadsheet applications.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" name="save_settings" class="btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
// Include footer with custom scripts
$before_body_close = "
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle fee sections
        const toggleButtons = document.querySelectorAll('.toggle-section');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const feeSection = this.closest('.fee-section');
                feeSection.classList.toggle('active');
                this.innerHTML = feeSection.classList.contains('active') ? '<i class=\"fas fa-chevron-up\"></i>' : '<i class=\"fas fa-chevron-down\"></i>';
            });
        });
        
        // Handle fee type changes
        const feeTypeSelects = document.querySelectorAll('.fee-type-select');
        feeTypeSelects.forEach(select => {
            select.addEventListener('change', function() {
                const amountLabel = this.closest('.fee-details').querySelector('.amount-label');
                if (this.value === 'percentage') {
                    amountLabel.textContent = 'Percentage (%)';
                } else {
                    amountLabel.textContent = 'Amount ($)';
                }
            });
        });
    });
</script>
";
include 'includes/footer.php';
?> 