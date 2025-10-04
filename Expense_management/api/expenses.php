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
    case 'submit':
        if ($method === 'POST') {
            handleSubmitExpense();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'list':
        if ($method === 'GET') {
            handleListExpenses();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'get':
        if ($method === 'GET') {
            handleGetExpense();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'upload_receipt':
        if ($method === 'POST') {
            handleUploadReceipt();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function handleSubmitExpense() {
    global $pdo;
    
    $user = currentUser();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $original_amount = $input['original_amount'] ?? '';
    $original_currency = sanitizeInput($input['original_currency'] ?? '');
    $category = sanitizeInput($input['category'] ?? '');
    $description = sanitizeInput($input['description'] ?? '');
    $expense_date = $input['expense_date'] ?? '';
    
    if (empty($original_amount) || empty($original_currency) || empty($category) || empty($expense_date)) {
        http_response_code(400);
        echo json_encode(['error' => 'All required fields must be filled']);
        return;
    }
    
    if (!validateAmount($original_amount)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid amount']);
        return;
    }
    
    if (!validateDate($expense_date)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get company currency
        $stmt = $pdo->prepare("SELECT currency FROM companies WHERE id = ?");
        $stmt->execute([$user['company_id']]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        $company_currency = $company['currency'];
        
        // Convert currency if needed
        $conversion = convertToCompanyCurrency($pdo, $original_amount, $original_currency, $company_currency);
        
        // Insert expense
        $stmt = $pdo->prepare("INSERT INTO expenses (company_id, user_id, original_amount, original_currency, company_amount, company_currency, exchange_rate, category, description, expense_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['company_id'],
            $user['id'],
            $original_amount,
            $original_currency,
            $conversion['amount'],
            $company_currency,
            $conversion['rate'],
            $category,
            $description,
            $expense_date
        ]);
        
        $expense_id = $pdo->lastInsertId();
        
        // Create approval records
        createApprovalRecords($pdo, $expense_id, $user['company_id'], $user['id']);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'expense_id' => $expense_id,
            'conversion' => $conversion,
            'message' => 'Expense submitted successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleListExpenses() {
    global $pdo;
    
    $user = currentUser();
    $page = (int)($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    try {
        $where_clause = "WHERE e.company_id = ?";
        $params = [$user['company_id']];
        
        // Filter by user if not admin, manager, hr, cfo, finance, or director
        if (!in_array($user['role'], ['Admin', 'Manager', 'HR', 'CFO', 'Finance', 'Director'])) {
            $where_clause .= " AND e.user_id = ?";
            $params[] = $user['id'];
        }
        
        // Get total count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM expenses e $where_clause");
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get expenses with approval status
        $sql = "SELECT e.*, u.name as user_name, 
                (SELECT COUNT(*) FROM approvals a WHERE a.expense_id = e.id AND a.status = 'Approved') as approved_count,
                (SELECT COUNT(*) FROM approvals a WHERE a.expense_id = e.id) as total_approvals
                FROM expenses e 
                JOIN users u ON e.user_id = u.id 
                $where_clause 
                ORDER BY e.created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'expenses' => $expenses,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ],
            'debug' => [
                'user_role' => $user['role'],
                'company_id' => $user['company_id']
            ]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleGetExpense() {
    global $pdo;
    
    $user = currentUser();
    $expense_id = (int)($_GET['id'] ?? 0);
    
    if (!$expense_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Expense ID required']);
        return;
    }
    
    try {
        // Get expense details
        $stmt = $pdo->prepare("SELECT e.*, u.name as user_name, c.name as company_name 
                              FROM expenses e 
                              JOIN users u ON e.user_id = u.id 
                              JOIN companies c ON e.company_id = c.id 
                              WHERE e.id = ? AND e.company_id = ?");
        $stmt->execute([$expense_id, $user['company_id']]);
        $expense = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$expense) {
            http_response_code(404);
            echo json_encode(['error' => 'Expense not found']);
            return;
        }
        
        // Get approval details
        $stmt = $pdo->prepare("SELECT a.*, u.name as approver_name, u.role as approver_role 
                              FROM approvals a 
                              JOIN users u ON a.approver_id = u.id 
                              WHERE a.expense_id = ? 
                              ORDER BY a.step_order, a.created_at");
        $stmt->execute([$expense_id]);
        $approvals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $expense['approvals'] = $approvals;
        
        echo json_encode([
            'success' => true,
            'expense' => $expense
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function createApprovalRecords($pdo, $expense_id, $company_id, $user_id) {
    // Get approval sequence for company
    $stmt = $pdo->prepare("SELECT * FROM approval_sequences WHERE company_id = ? ORDER BY step_order");
    $stmt->execute([$company_id]);
    $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($sequences)) {
        return; // No approval sequence defined
    }
    
    // Get user's manager
    $stmt = $pdo->prepare("SELECT manager_id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $manager_id = $user['manager_id'];
    
    foreach ($sequences as $seq) {
        $approver_id = null;
        
        switch ($seq['approver_type']) {
            case 'MANAGER':
                $approver_id = $manager_id;
                break;
            case 'ROLE':
                // Find user with specific role in company
                $stmt = $pdo->prepare("SELECT id FROM users WHERE company_id = ? AND role = ? LIMIT 1");
                $stmt->execute([$company_id, $seq['approver_role']]);
                $approver = $stmt->fetch(PDO::FETCH_ASSOC);
                $approver_id = $approver ? $approver['id'] : null;
                break;
            case 'USER':
                $approver_id = $seq['approver_user_id'];
                break;
        }
        
        if ($approver_id) {
            $stmt = $pdo->prepare("INSERT INTO approvals (expense_id, approver_id, step_order) VALUES (?, ?, ?)");
            $stmt->execute([$expense_id, $approver_id, $seq['step_order']]);
        }
    }
}

function handleUploadReceipt() {
    global $pdo;
    
    $user = currentUser();
    $expense_id = (int)($_POST['expense_id'] ?? 0);
    
    if (!$expense_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Expense ID required']);
        return;
    }
    
    // Check if expense belongs to user's company
    $stmt = $pdo->prepare("SELECT id FROM expenses WHERE id = ? AND company_id = ?");
    $stmt->execute([$expense_id, $user['company_id']]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Expense not found']);
        return;
    }
    
    if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded']);
        return;
    }
    
    $file = $_FILES['receipt'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($file['type'], $allowed_types)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Only JPEG, PNG, and GIF allowed.']);
        return;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        http_response_code(400);
        echo json_encode(['error' => 'File too large. Maximum 5MB allowed.']);
        return;
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/receipts/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'receipt_' . $expense_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update expense with receipt path
        $stmt = $pdo->prepare("UPDATE expenses SET receipt_path = ? WHERE id = ?");
        $stmt->execute([$filename, $expense_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Receipt uploaded successfully',
            'filename' => $filename
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
    }
}
