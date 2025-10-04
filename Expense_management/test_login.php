<?php
session_start();

// Test login with sample data
$test_user = [
    'id' => 1,
    'name' => 'Test User',
    'email' => 'test@example.com',
    'role' => 'Admin',
    'company_id' => 1
];

// Simulate login
$_SESSION['user'] = $test_user;

echo "<h2>Login Test</h2>";
echo "<p>User logged in: " . $test_user['name'] . "</p>";
echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";
echo "<p><a href='public/dashboard_employee.php'>Go to Dashboard</a></p>";
?>
