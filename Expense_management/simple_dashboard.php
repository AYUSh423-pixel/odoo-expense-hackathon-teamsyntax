<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: simple_login.php');
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .header { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .user-info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .logout { background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Simple Dashboard</h1>
        <p>This is a test dashboard to verify session management is working.</p>
    </div>
    
    <div class="user-info">
        <h3>User Information:</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
        <p><strong>Company ID:</strong> <?php echo htmlspecialchars($user['company_id']); ?></p>
    </div>
    
    <div>
        <h3>Session Data:</h3>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    
    <p>
        <a href="simple_login.php" class="logout">Logout</a> | 
        <a href="test_session.php">Check Session</a> |
        <a href="public/index.php">Try Main App</a>
    </p>
</body>
</html>
