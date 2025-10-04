<?php
/**
 * Setup script for Expense Management System
 * This script helps verify the installation and setup
 */

// Check if we're running from command line or web
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Expense Manager Setup</title>";
    echo "<style>body{font-family:Arial,sans-serif;margin:40px;background:#f5f5f5;}";
    echo ".container{background:white;padding:30px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
    echo ".success{color:#28a745;}.error{color:#dc3545;}.warning{color:#ffc107;}</style></head><body>";
    echo "<div class='container'><h1>Expense Manager Setup</h1>";
}

function output($message, $type = 'info') {
    global $isCli;
    if ($isCli) {
        echo $message . "\n";
    } else {
        $class = $type === 'error' ? 'error' : ($type === 'success' ? 'success' : ($type === 'warning' ? 'warning' : ''));
        echo "<p class='$class'>$message</p>";
    }
}

// Check PHP version
$phpVersion = phpversion();
if (version_compare($phpVersion, '7.4.0', '>=')) {
    output("✓ PHP version $phpVersion is supported", 'success');
} else {
    output("✗ PHP version $phpVersion is not supported. Please upgrade to PHP 7.4 or higher.", 'error');
    exit(1);
}

// Check required extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        output("✓ $ext extension is loaded", 'success');
    } else {
        output("✗ $ext extension is missing", 'error');
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    output("Please install the missing extensions: " . implode(', ', $missingExtensions), 'error');
    exit(1);
}

// Check if config file exists
if (file_exists('includes/config.php')) {
    output("✓ Configuration file exists", 'success');
} else {
    output("✗ Configuration file not found. Please create includes/config.php", 'error');
    exit(1);
}

// Test database connection
try {
    $config = include 'includes/config.php';
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    output("✓ Database connection successful", 'success');
} catch (PDOException $e) {
    output("✗ Database connection failed: " . $e->getMessage(), 'error');
    output("Please make sure MySQL is running and the database 'expense_manager' exists", 'warning');
    exit(1);
}

// Check if tables exist
$tables = ['companies', 'users', 'expenses', 'approvals', 'approval_rules', 'approval_sequences', 'exchange_rates'];
$missingTables = [];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            output("✓ Table '$table' exists", 'success');
        } else {
            output("✗ Table '$table' is missing", 'error');
            $missingTables[] = $table;
        }
    } catch (PDOException $e) {
        output("✗ Error checking table '$table': " . $e->getMessage(), 'error');
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    output("Please run the database_schema.sql file to create the missing tables", 'warning');
    output("Missing tables: " . implode(', ', $missingTables), 'error');
}

// Check if sample data exists
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM companies");
    $companyCount = $stmt->fetchColumn();
    
    if ($companyCount > 0) {
        output("✓ Sample data found ($companyCount companies)", 'success');
    } else {
        output("ℹ No sample data found. You can run sample_data.sql to add test data", 'warning');
    }
} catch (PDOException $e) {
    output("✗ Error checking sample data: " . $e->getMessage(), 'error');
}

// Check file permissions
$writableDirs = ['public', 'api', 'includes'];
foreach ($writableDirs as $dir) {
    if (is_writable($dir)) {
        output("✓ Directory '$dir' is writable", 'success');
    } else {
        output("⚠ Directory '$dir' is not writable", 'warning');
    }
}

// Test API endpoints
$baseUrl = $config['base_url'] ?? 'http://localhost/expense_manager';
$testEndpoints = [
    '/api/auth.php?action=login',
    '/api/expenses.php?action=list',
    '/api/users.php?action=list'
];

output("\nTesting API endpoints...", 'info');
foreach ($testEndpoints as $endpoint) {
    $url = $baseUrl . $endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5
        ]
    ]);
    
    $result = @file_get_contents($url, false, $context);
    if ($result !== false) {
        output("✓ API endpoint $endpoint is accessible", 'success');
    } else {
        output("⚠ API endpoint $endpoint may not be accessible", 'warning');
    }
}

// Final summary
output("\n" . str_repeat("=", 50), 'info');
if (empty($missingExtensions) && empty($missingTables)) {
    output("🎉 Setup completed successfully!", 'success');
    output("You can now access the application at: $baseUrl/public/", 'info');
    output("\nDefault test accounts:", 'info');
    output("Admin: admin@acme.com / password123", 'info');
    output("Manager: sarah@acme.com / password123", 'info');
    output("Employee: tom@acme.com / password123", 'info');
} else {
    output("❌ Setup incomplete. Please fix the issues above.", 'error');
}

if (!$isCli) {
    echo "</div></body></html>";
}
?>
