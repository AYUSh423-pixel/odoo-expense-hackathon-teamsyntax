<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/utils.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        if ($method === 'POST') {
            handleLogin();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'signup':
        if ($method === 'POST') {
            handleSignup();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'logout':
        if ($method === 'POST') {
            handleLogout();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function handleLogin() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $email = sanitizeInput($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required']);
        return;
    }
    
    if (!validateEmail($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT u.*, c.name as company_name, c.currency as company_currency FROM users u JOIN companies c ON u.company_id = c.id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }
        
        unset($user['password']);
        loginUser($user);
        
        echo json_encode([
            'success' => true,
            'user' => $user,
            'message' => 'Login successful'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleSignup() {
    global $pdo;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $company_name = sanitizeInput($input['company_name'] ?? '');
    $country = sanitizeInput($input['country'] ?? '');
    $currency = sanitizeInput($input['currency'] ?? '');
    $admin_name = sanitizeInput($input['admin_name'] ?? '');
    $admin_email = sanitizeInput($input['admin_email'] ?? '');
    $admin_password = $input['admin_password'] ?? '';
    
    if (empty($company_name) || empty($country) || empty($currency) || empty($admin_name) || empty($admin_email) || empty($admin_password)) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        return;
    }
    
    if (!validateEmail($admin_email)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        return;
    }
    
    if (strlen($admin_password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$admin_email]);
        if ($stmt->fetch()) {
            $pdo->rollback();
            http_response_code(400);
            echo json_encode(['error' => 'Email already exists']);
            return;
        }
        
        // Create company
        $stmt = $pdo->prepare("INSERT INTO companies (name, country, currency) VALUES (?, ?, ?)");
        $stmt->execute([$company_name, $country, $currency]);
        $company_id = $pdo->lastInsertId();
        
        // Create admin user
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role, is_manager_approver) VALUES (?, ?, ?, ?, 'Admin', 1)");
        $stmt->execute([$company_id, $admin_name, $admin_email, $hashed_password]);
        $user_id = $pdo->lastInsertId();
        
        // Create default approval rules
        $stmt = $pdo->prepare("INSERT INTO approval_rules (company_id, mode, rule_type) VALUES (?, 'SEQUENTIAL', 'None')");
        $stmt->execute([$company_id]);
        
        // Create default approval sequence (Manager -> Finance -> Director)
        $sequences = [
            ['company_id' => $company_id, 'step_order' => 1, 'approver_type' => 'MANAGER'],
            ['company_id' => $company_id, 'step_order' => 2, 'approver_type' => 'ROLE', 'approver_role' => 'Finance'],
            ['company_id' => $company_id, 'step_order' => 3, 'approver_type' => 'ROLE', 'approver_role' => 'Director']
        ];
        
        foreach ($sequences as $seq) {
            $stmt = $pdo->prepare("INSERT INTO approval_sequences (company_id, step_order, approver_type, approver_role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$seq['company_id'], $seq['step_order'], $seq['approver_type'], $seq['approver_role']]);
        }
        
        $pdo->commit();
        
        // Get user data for login
        $stmt = $pdo->prepare("SELECT u.*, c.name as company_name, c.currency as company_currency FROM users u JOIN companies c ON u.company_id = c.id WHERE u.id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        unset($user['password']);
        
        loginUser($user);
        
        echo json_encode([
            'success' => true,
            'user' => $user,
            'message' => 'Company and admin created successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleLogout() {
    logout();
}
