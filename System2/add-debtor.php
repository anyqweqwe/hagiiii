<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$success = false;
$error = '';
$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'city' => '',
    'state' => '',
    'debt_amount' => '',
    'debt_reason' => '',
    'due_date' => ''
];

// Generate a new debtor ID based on current timestamp
$new_debtor_id = 'DEB' . date('YmdHis');

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture all form data
    $formData = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'state' => $_POST['state'] ?? '',
        'debt_amount' => $_POST['debt_amount'] ?? 0,
        'debt_reason' => $_POST['debt_reason'] ?? '',
        'due_date' => $_POST['due_date'] ?? ''
    ];
    
    // Enhanced validation
    if (empty($formData['name'])) {
        $error = "Name is required";
    } elseif (empty($formData['email'])) {
        $error = "Email is required";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (empty($formData['phone'])) {
        $error = "Phone is required";
    } elseif (!is_numeric(preg_replace('/[^0-9]/', '', $formData['phone']))) {
        $error = "Please enter a valid phone number";
    } elseif (empty($formData['address'])) {
        $error = "Address is required";
    } elseif (empty($formData['city'])) {
        $error = "City is required";
    } elseif (empty($formData['state'])) {
        $error = "Province is required";
    } elseif (!is_numeric($formData['debt_amount']) || $formData['debt_amount'] <= 0) {
        $error = "Valid debt amount is required";
    } elseif (empty($formData['due_date'])) {
        $error = "Due date is required";
    } else {
        // In a real application, save to database
        // For demo, just show success message and redirect
        
        // Store in session for demo - this would normally go to a database
        $_SESSION['debtors'][] = [
            'id' => $new_debtor_id,
            'name' => $formData['name'],
            'email' => $formData['email'],
            'phone' => $formData['phone'],
            'address' => $formData['address'],
            'city' => $formData['city'],
            'state' => $formData['state'],
            'total_debt' => (float)$formData['debt_amount'],
            'debt_reason' => $formData['debt_reason'],
            'due_date' => $formData['due_date'],
            'date_added' => date('Y-m-d'),
            'status' => 'Active'
        ];
        
        $success = true;
        $_SESSION['debtor_added'] = true;
        $_SESSION['debtor_message'] = "Debtor {$formData['name']} has been added successfully!";
        header("Location: debtors.php");
        exit;
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

// Cities by state/country for dropdown
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

$page_title = 'Add Debtor';
include 'includes/header.php';
?>

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
            <div class="user-avatar">
                <div class="avatar">AD</div>
            </div>
        </div>
        
        <!-- Dashboard content -->
        <div class="dashboard-content">
            <h1>Add New Debtor</h1>
            
            <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem;">
                    <button type="button" class="theme-toggle" onclick="window.ThemeManager.toggleTheme()" title="Toggle dark mode">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
                <form method="post" action="">
                    <h3>Personal Information</h3>
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number <span class="required">*</span></label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>" placeholder="(555) 123-4567" required>
                        </div>
                    </div>
                    
                    <h3>Address Information</h3>
                    <div class="form-group">
                        <label for="address">Street Address <span class="required">*</span></label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($formData['address']); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="state">Country/State <span class="required">*</span></label>
                            <select id="state" name="state" required>
                                <option value="">-- Select Country --</option>
                                <?php foreach ($states as $code => $name): ?>
                                <option value="<?php echo $code; ?>" <?php if($formData['state'] === $code) echo 'selected'; ?>><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="city">City <span class="required">*</span></label>
                            <select id="city" name="city" required>
                                <option value="">-- Select City --</option>
                                <?php if(isset($cities[$formData['state']])): ?>
                                <?php foreach ($cities[$formData['state']] as $city): ?>
                                <option value="<?php echo $city; ?>" <?php if($formData['city'] === $city) echo 'selected'; ?>><?php echo $city; ?></option>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <h3>Debt Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="debt_amount">Debt Amount ($) <span class="required">*</span></label>
                            <input type="number" id="debt_amount" name="debt_amount" value="<?php echo htmlspecialchars($formData['debt_amount']); ?>" min="0.01" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="due_date">Due Date <span class="required">*</span></label>
                            <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($formData['due_date']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="debt_reason">Debt Reason <span class="required">*</span></label>
                        <select id="debt_reason" name="debt_reason" required>
                            <option value="">-- Select Reason --</option>
                            <?php foreach ($debt_reasons as $reason): ?>
                            <option value="<?php echo $reason; ?>" <?php if($formData['debt_reason'] === $reason) echo 'selected'; ?>><?php echo $reason; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-buttons">
                        <a href="debtors.php" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">Add Debtor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Store cities data in JavaScript
    const citiesByState = <?php echo json_encode($cities); ?>;
    document.addEventListener('DOMContentLoaded', function() {
        const stateSelect = document.getElementById('state');
        const citySelect = document.getElementById('city');
        function updateCities() {
            citySelect.innerHTML = '<option value="">-- Select City --</option>';
            const selectedState = stateSelect.value;
            if (selectedState && citiesByState[selectedState]) {
                citiesByState[selectedState].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }
        }
        updateCities();
        stateSelect.addEventListener('change', updateCities);
    });
</script>

<?php include 'includes/footer.php'; ?>