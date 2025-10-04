<?php
session_start();

// Simple login form
if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple hardcoded login for testing
    if ($email === 'admin@acme.com' && $password === 'password123') {
        $_SESSION['user'] = [
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@acme.com',
            'role' => 'Admin',
            'company_id' => 1
        ];
        header('Location: simple_dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Simple Login Test</h2>
    
    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="admin@acme.com" required>
        </div>
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" value="password123" required>
        </div>
        <button type="submit">Login</button>
    </form>
    
    <p><a href="test_session.php">Check Session</a></p>
</body>
</html>
