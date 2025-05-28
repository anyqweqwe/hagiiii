<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['debtor_error'] = "No debtor ID provided for editing.";
    header("Location: debtors.php");
    exit;
}

$debtor_id = $_GET['id'];
$debtor = null;
$is_demo = false;

// Demo debtors data
$demo_debtors = [
    'DEB001' => [
        'id' => 'DEB001', 
        'name' => 'John Smith', 
        'email' => 'john@example.com', 
        'phone' => '(555) 123-4567', 
        'address' => '123 Main St', 
        'city' => 'New York', 
        'state' => 'US', 
        'total_debt' => 150000.00, 
        'debt_reason' => 'Mortgage',
        'due_date' => '2023-12-31',
        'date_added' => '2023-01-15',
        'status' => 'Active'
    ],
    'DEB002' => [
        'id' => 'DEB002', 
        'name' => 'Maria Garcia', 
        'email' => 'maria@example.com', 
        'phone' => '(555) 987-6543', 
        'address' => '456 Park Ave', 
        'city' => 'Los Angeles', 
        'state' => 'US', 
        'total_debt' => 95250.00, 
        'debt_reason' => 'Business Loan',
        'due_date' => '2023-11-15',
        'date_added' => '2023-02-10',
        'status' => 'Active'
    ],
    'DEB003' => [
        'id' => 'DEB003', 
        'name' => 'Robert Johnson', 
        'email' => 'robert@example.com', 
        'phone' => '(555) 456-7890', 
        'address' => '789 Broadway', 
        'city' => 'Chicago', 
        'state' => 'US', 
        'total_debt' => 102000.00, 
        'debt_reason' => 'Auto Loan',
        'due_date' => '2023-06-30',
        'date_added' => '2023-01-05',
        'status' => 'Overdue'
    ]
];

// Check if it's a demo debtor
if (isset($demo_debtors[$debtor_id]) && !in_array($debtor_id, $_SESSION['deleted_demo_debtors'] ?? [])) {
    $debtor = $demo_debtors[$debtor_id];
    $is_demo = true;
} else {
    // Look for the debtor in the session
    if (isset($_SESSION['debtors']) && is_array($_SESSION['debtors'])) {
        foreach ($_SESSION['debtors'] as $key => $d) {
            if ($d['id'] === $debtor_id) {
                $debtor = $d;
                $debtor_key = $key;
                break;
            }
        }
    }
}

// If debtor not found, show error
if (!$debtor) {
    $_SESSION['debtor_error'] = "Debtor with ID {$debtor_id} not found.";
    header("Location: debtors.php");
    exit;
}

$error = '';
$success = false;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    if (empty($_POST['name'])) {
        $error = "Name is required";
    } elseif (empty($_POST['email'])) {
        $error = "Email is required";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (empty($_POST['phone'])) {
        $error = "Phone is required";
    } elseif (!is_numeric(preg_replace('/[^0-9]/', '', $_POST['phone']))) {
        $error = "Please enter a valid phone number";
    } elseif (empty($_POST['address'])) {
        $error = "Address is required";
    } elseif (empty($_POST['city'])) {
        $error = "City is required";
    } elseif (empty($_POST['state'])) {
        $error = "Province is required";
    } elseif (!is_numeric($_POST['debt_amount']) || $_POST['debt_amount'] <= 0) {
        $error = "Valid debt amount is required";
    } elseif (empty($_POST['due_date'])) {
        $error = "Due date is required";
    } else {
        // Prepare updated data
        $updated_debtor = [
            'id' => $debtor_id,
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'total_debt' => (float)$_POST['debt_amount'],
            'debt_reason' => $_POST['debt_reason'],
            'due_date' => $_POST['due_date'],
            'date_added' => $debtor['date_added'],
            'status' => $_POST['status']
        ];
        
        if ($is_demo) {
            // For demo debtors, we store the edited version in a special session array
            if (!isset($_SESSION['edited_demo_debtors'])) {
                $_SESSION['edited_demo_debtors'] = [];
            }
            $_SESSION['edited_demo_debtors'][$debtor_id] = $updated_debtor;
        } else {
            // For regular debtors, update it in the session array
            $_SESSION['debtors'][$debtor_key] = $updated_debtor;
        }
        
        // Set success message
        $success = true;
        $_SESSION['debtor_added'] = true;
        $_SESSION['debtor_message'] = "Debtor {$_POST['name']} has been updated successfully!";
        header("Location: debtors.php");
        exit;
    }
    
    // If there was an error, use the POST data to pre-populate form
    if ($error) {
        $debtor = [
            'id' => $debtor_id,
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address'],
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'total_debt' => $_POST['debt_amount'],
            'debt_reason' => $_POST['debt_reason'],
            'due_date' => $_POST['due_date'],
            'date_added' => $debtor['date_added'],
            'status' => $_POST['status']
        ];
    }
}

// States/Countries for dropdown
$states = [
    'PH' => 'Philippines',
    'US' => 'United States',
    'CA' => 'Canada',
    'UK' => 'United Kingdom',
    'AU' => 'Australia',
    'JP' => 'Japan',
    'SG' => 'Singapore',
    'MY' => 'Malaysia',
    'HK' => 'Hong Kong',
    'AE' => 'United Arab Emirates'
];

// Cities by state/country for dropdown (same as add-debtor.php)
$cities = [
    'PH' => [
        // Luzon (100 cities)
        'Alaminos', 'Angeles', 'Antipolo', 'Balanga', 'Baguio', 'Batac', 'Batangas City', 'Baguio', 
        'Biñan', 'Bocaue', 'Cabanatuan', 'Cabuyao', 'Calamba', 'Caloocan', 'Candon', 'Cauayan',
        'Cavite City', 'Dagupan', 'Dasmariñas', 'Dinalupihan', 'Gapan', 'General Trias', 'Ilagan', 
        'Imus', 'Iriga', 'Laoag', 'Las Piñas', 'Legazpi', 'Ligao', 'Lipa', 'Lucena', 'Mabalacat',
        'Makati', 'Malabon', 'Malaybalay', 'Malolos', 'Mandaluyong', 'Manila', 'Marikina', 
        'Masbate City', 'Meycauayan', 'Muntinlupa', 'Naga', 'Navotas', 'Olongapo', 'Palayan', 
        'Parañaque', 'Pasay', 'Pasig', 'Puerto Princesa', 'Quezon City', 'San Fernando (La Union)', 
        'San Fernando (Pampanga)', 'San Jose', 'San Jose del Monte', 'San Juan', 'San Pablo', 
        'San Pedro', 'Santa Rosa', 'Santiago', 'Sorsogon City', 'Tabaco', 'Tabuk', 'Tacloban', 
        'Taguig', 'Tagum', 'Tanauan', 'Tanjay', 'Tarlac City', 'Tayabas', 'Trece Martires', 
        'Tuguegarao', 'Urdaneta', 'Valenzuela', 'Vigan', 'Bacoor', 'Carmona', 'Lal-lo', 'Paniqui',
        'Angono', 'Cainta', 'Taytay', 'Marilao', 'Calumpit', 'Plaridel', 'Pulilan', 'Hagonoy',
        'Santa Maria', 'Rodriguez', 'San Mateo', 'Sta. Cruz', 'Pagsanjan', 'Los Baños', 'Bay',
        'Rosario', 'Silang', 'Nasugbu', 'Calatagan', 'Taal', 'Bauan', 'Tanza', 'General Mariano Alvarez',
        'Kawit', 'Naic', 'Noveleta', 'Tuy',

        // Visayas (15 cities)
        'Bacolod', 'Bago', 'Bayawan', 'Borongan', 'Cadiz', 'Cebu City', 'Dumaguete', 'Iloilo City', 
        'Kabankalan', 'Maasin', 'Ormoc', 'Roxas', 'Sagay', 'Tacloban', 'Tagbilaran',

        // Mindanao (10 cities)
        'Butuan', 'Cagayan de Oro', 'Cotabato City', 'Davao City', 'Digos', 'General Santos', 
        'Iligan', 'Koronadal', 'Marawi', 'Zamboanga City'
    ],
    'US' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'],
    'CA' => ['Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Edmonton', 'Ottawa', 'Winnipeg', 'Quebec City', 'Hamilton', 'Victoria'],
    'UK' => ['London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool', 'Bristol', 'Leeds', 'Edinburgh', 'Sheffield', 'Newcastle'],
    'AU' => ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Gold Coast', 'Newcastle', 'Canberra', 'Wollongong', 'Hobart'],
    'JP' => ['Tokyo', 'Yokohama', 'Osaka', 'Nagoya', 'Sapporo', 'Kobe', 'Kyoto', 'Fukuoka', 'Kawasaki', 'Saitama'],
    'SG' => ['Singapore'],
    'MY' => ['Kuala Lumpur', 'Penang', 'Johor Bahru', 'Ipoh', 'Melaka', 'Kota Kinabalu', 'Kuching', 'Shah Alam', 'Petaling Jaya', 'Subang Jaya'],
    'HK' => ['Hong Kong', 'Kowloon', 'Tsuen Wan', 'Tuen Mun', 'Sha Tin'],
    'AE' => ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Ras Al Khaimah', 'Fujairah', 'Umm Al Quwain']
];

// Common debt reasons
$debt_reasons = [
    'Invoice Payment',
    'Loan',
    'Credit Card',
    'Mortgage',
    'Auto Loan',
    'Personal Loan',
    'Business Loan',
    'Services Rendered',
    'Products Purchased',
    'Other'
];

// Status options
$status_options = ['Active', 'Overdue', 'Paid', 'Deferred'];
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Debtor - DMS</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/themes.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // Apply theme before page loads to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="js/scripts.js"></script>
    <script src="js/animations.js"></script>
    <script src="js/theme.js"></script>
    <script>
        // Store cities data in JavaScript
        const citiesByState = <?php echo json_encode($cities); ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');
            
            // Function to update cities when state changes
            function updateCities() {
                // Clear current options
                citySelect.innerHTML = '<option value="">-- Select City --</option>';
                
                // Get selected state
                const selectedState = stateSelect.value;
                
                // If state is selected and has cities
                if (selectedState && citiesByState[selectedState]) {
                    // Add city options for the selected state
                    citiesByState[selectedState].forEach(city => {
                        const option = document.createElement('option');
                        option.value = city;
                        option.textContent = city;
                        citySelect.appendChild(option);
                    });
                    
                    // Set previously selected city if it exists in the new state
                    const selectedCity = '<?php echo $debtor['city'] ?? ''; ?>';
                    if (selectedCity && citiesByState[selectedState].includes(selectedCity)) {
                        citySelect.value = selectedCity;
                    }
                }
            }
            
            // Set initial cities
            updateCities();
            
            // Add event listener for state change
            stateSelect.addEventListener('change', updateCities);
        });
    </script>
    <style>
    [data-theme="dark"] .dashboard-content,
    [data-theme="dark"] .form-container {
        background-color: var(--card-bg) !important;
        color: var(--text-primary) !important;
    }
    [data-theme="dark"] .form-container h3 {
        color: var(--text-secondary) !important;
    }
    </style>
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
                    <i class="fas fa-home"></i> <span>Dashboard</span>
                </a>
                <a href="debtors.php" class="active">
                    <i class="fas fa-users"></i> <span>Debtors</span>
                </a>
                <a href="payments.php">
                    <i class="fas fa-credit-card"></i> <span>Payments</span>
                </a>
                <a href="financial-summary.php">
                    <i class="fas fa-chart-pie"></i> <span>Financial Summary</span>
                </a>
                <a href="logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
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
                <button type="button" class="theme-toggle" onclick="window.ThemeManager.toggleTheme()" title="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="user-avatar">
                    <div class="avatar">AD</div>
                </div>
            </div>
            
            <!-- Dashboard content -->
            <div class="dashboard-content">
                <div class="action-buttons" style="margin-bottom: 20px;">
                    <a href="debtors.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Debtors
                    </a>
                </div>
                
                <h1>Edit Debtor</h1>
                
                <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="form-container">
                    <form method="post" action="">
                        <h3>Personal Information</h3>
                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($debtor['name']); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($debtor['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number <span class="required">*</span></label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($debtor['phone']); ?>" placeholder="(555) 123-4567" required>
                            </div>
                        </div>
                        
                        <h3>Address Information</h3>
                        <div class="form-group">
                            <label for="address">Street Address <span class="required">*</span></label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($debtor['address'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="state">Country/State <span class="required">*</span></label>
                                <select id="state" name="state" required>
                                    <option value="">-- Select Country --</option>
                                    <?php foreach ($states as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" <?php if($debtor['state'] === $code) echo 'selected'; ?>><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="city">City <span class="required">*</span></label>
                                <select id="city" name="city" required>
                                    <option value="">-- Select City --</option>
                                    <?php if(isset($cities[$debtor['state']])): ?>
                                    <?php foreach ($cities[$debtor['state']] as $city): ?>
                                    <option value="<?php echo $city; ?>" <?php if($debtor['city'] === $city) echo 'selected'; ?>><?php echo $city; ?></option>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        
                        <h3>Debt Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="debt_amount">Debt Amount (₱) <span class="required">*</span></label>
                                <input type="number" id="debt_amount" name="debt_amount" value="<?php echo htmlspecialchars($debtor['total_debt']); ?>" min="0.01" step="0.01" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="due_date">Due Date <span class="required">*</span></label>
                                <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($debtor['due_date'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="debt_reason">Debt Reason <span class="required">*</span></label>
                                <select id="debt_reason" name="debt_reason" required>
                                    <option value="">-- Select Reason --</option>
                                    <?php foreach ($debt_reasons as $reason): ?>
                                    <option value="<?php echo $reason; ?>" <?php if(($debtor['debt_reason'] ?? '') === $reason) echo 'selected'; ?>><?php echo $reason; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status <span class="required">*</span></label>
                                <select id="status" name="status" required>
                                    <?php foreach ($status_options as $option): ?>
                                    <option value="<?php echo $option; ?>" <?php if($debtor['status'] === $option) echo 'selected'; ?>><?php echo $option; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <a href="debtors.php" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">Update Debtor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 