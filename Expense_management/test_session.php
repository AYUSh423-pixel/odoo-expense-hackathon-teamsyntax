<?php
session_start();

echo "<h2>Session Debug</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";

if (isset($_SESSION['user'])) {
    echo "<p style='color: green;'>User is logged in: " . $_SESSION['user']['name'] . "</p>";
} else {
    echo "<p style='color: red;'>User is NOT logged in</p>";
}

echo "<p><a href='public/index.php'>Go to Login</a></p>";
echo "<p><a href='public/dashboard_employee.php'>Go to Dashboard</a></p>";
?>
