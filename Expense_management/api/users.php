<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/utils.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Require login for all actions
requireLogin();

switch ($action) {
    case 'list':
        if ($method === 'GET') {
            handleListUsers();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'create':
        if ($method === 'POST') {
            handleCreateUser();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'update':
        if ($method === 'POST') {
            handleUpdateUser();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'delete':
        if ($method === 'POST') {
            handleDeleteUser();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'get':
        if ($method === 'GET') {
            handleGetUser();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function handleListUsers() {
    global $pdo;
    
    $user = currentUser();
    
    // Only admin can list users
    if ($user['role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Only admin can list users']);
        return;
    }
    
    $page = (int)($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    try {
        // Get total count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_id = ?");
        $stmt->execute([$user['company_id']]);
        $total = $stmt->fetchColumn();
        
        // Get users with manager names
        $sql = "SELECT u.*, m.name as manager_name 
                FROM users u 
                LEFT JOIN users m ON u.manager_id = m.id 
                WHERE u.company_id = ? 
                ORDER BY u.created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['company_id']]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Remove passwords from response
        foreach ($users as &$u) {
            unset($u['password']);
        }
        
        echo json_encode([
            'success' => true,
            'users' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleCreateUser() {
    global $pdo;
    
    $user = currentUser();
    
    // Only admin can create users
    if ($user['role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Only admin can create users']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = sanitizeInput($input['name'] ?? '');
    $email = sanitizeInput($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $role = sanitizeInput($input['role'] ?? 'Employee');
    $manager_id = (int)($input['manager_id'] ?? 0);
    $is_manager_approver = (int)($input['is_manager_approver'] ?? 1);
    
    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Name, email, and password are required']);
        return;
    }
    
    if (!validateEmail($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        return;
    }
    
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters']);
        return;
    }
    
    if (!in_array($role, ['Admin', 'Manager', 'Employee', 'Finance', 'Director', 'HR', 'CFO'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid role']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $pdo->rollback();
            http_response_code(400);
            echo json_encode(['error' => 'Email already exists']);
            return;
        }
        
        // Validate manager if specified
        if ($manager_id) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND company_id = ? AND role IN ('Manager', 'Admin', 'HR', 'CFO')");
            $stmt->execute([$manager_id, $user['company_id']]);
            if (!$stmt->fetch()) {
                $pdo->rollback();
                http_response_code(400);
                echo json_encode(['error' => 'Invalid manager selected']);
                return;
            }
        }
        
        // Create user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role, manager_id, is_manager_approver) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['company_id'],
            $name,
            $email,
            $hashed_password,
            $role,
            $manager_id ?: null,
            $is_manager_approver
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'user_id' => $user_id,
            'message' => 'User created successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleUpdateUser() {
    global $pdo;
    
    $user = currentUser();
    
    // Only admin can update users
    if ($user['role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Only admin can update users']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = (int)($input['user_id'] ?? 0);
    $name = sanitizeInput($input['name'] ?? '');
    $email = sanitizeInput($input['email'] ?? '');
    $role = sanitizeInput($input['role'] ?? '');
    $manager_id = (int)($input['manager_id'] ?? 0);
    $is_manager_approver = (int)($input['is_manager_approver'] ?? 1);
    
    if (!$user_id || empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID, name, and email are required']);
        return;
    }
    
    if (!validateEmail($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        return;
    }
    
    if (!in_array($role, ['Admin', 'Manager', 'Employee', 'Finance', 'Director', 'HR', 'CFO'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid role']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if user exists and belongs to company
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND company_id = ?");
        $stmt->execute([$user_id, $user['company_id']]);
        if (!$stmt->fetch()) {
            $pdo->rollback();
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }
        
        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $pdo->rollback();
            http_response_code(400);
            echo json_encode(['error' => 'Email already exists']);
            return;
        }
        
        // Validate manager if specified
        if ($manager_id) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND company_id = ? AND role IN ('Manager', 'Admin', 'HR', 'CFO')");
            $stmt->execute([$manager_id, $user['company_id']]);
            if (!$stmt->fetch()) {
                $pdo->rollback();
                http_response_code(400);
                echo json_encode(['error' => 'Invalid manager selected']);
                return;
            }
        }
        
        // Update user
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, manager_id = ?, is_manager_approver = ? WHERE id = ? AND company_id = ?");
        $stmt->execute([
            $name,
            $email,
            $role,
            $manager_id ?: null,
            $is_manager_approver,
            $user_id,
            $user['company_id']
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleDeleteUser() {
    global $pdo;
    
    $user = currentUser();
    
    // Only admin can delete users
    if ($user['role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Only admin can delete users']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = (int)($input['user_id'] ?? 0);
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID required']);
        return;
    }
    
    // Prevent deleting self
    if ($user_id == $user['id']) {
        http_response_code(400);
        echo json_encode(['error' => 'Cannot delete your own account']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if user exists and belongs to company
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND company_id = ?");
        $stmt->execute([$user_id, $user['company_id']]);
        if (!$stmt->fetch()) {
            $pdo->rollback();
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }
        
        // Update manager references to null
        $stmt = $pdo->prepare("UPDATE users SET manager_id = NULL WHERE manager_id = ? AND company_id = ?");
        $stmt->execute([$user_id, $user['company_id']]);
        
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND company_id = ?");
        $stmt->execute([$user_id, $user['company_id']]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleGetUser() {
    global $pdo;
    
    $user = currentUser();
    
    // Only admin can get user details
    if ($user['role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Only admin can get user details']);
        return;
    }
    
    $user_id = (int)($_GET['id'] ?? 0);
    
    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'User ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT u.*, m.name as manager_name 
                              FROM users u 
                              LEFT JOIN users m ON u.manager_id = m.id 
                              WHERE u.id = ? AND u.company_id = ?");
        $stmt->execute([$user_id, $user['company_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user_data) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }
        
        unset($user_data['password']);
        
        echo json_encode([
            'success' => true,
            'user' => $user_data
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
