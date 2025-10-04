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
    case 'employee_stats':
        if ($method === 'GET') {
            handleEmployeeStats();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'financial_reports':
        if ($method === 'GET') {
            handleFinancialReports();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'category_breakdown':
        if ($method === 'GET') {
            handleCategoryBreakdown();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'high_value_expenses':
        if ($method === 'GET') {
            handleHighValueExpenses();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function handleEmployeeStats() {
    global $pdo;
    
    $user = currentUser();
    
    // Only HR, Admin, and CFO can view employee stats
    if (!in_array($user['role'], ['HR', 'Admin', 'CFO'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    try {
        $sql = "SELECT u.id, u.name, u.email, u.role, u.created_at,
                COUNT(e.id) as total_expenses,
                COALESCE(SUM(e.company_amount), 0) as total_amount,
                COALESCE(AVG(e.company_amount), 0) as avg_amount,
                c.currency as company_currency
                FROM users u
                LEFT JOIN expenses e ON u.id = e.user_id
                LEFT JOIN companies c ON u.company_id = c.id
                WHERE u.company_id = ? AND u.role != 'Admin'
                GROUP BY u.id, u.name, u.email, u.role, u.created_at, c.currency
                ORDER BY total_amount DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['company_id']]);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleFinancialReports() {
    global $pdo;
    
    $user = currentUser();
    
    // Only CFO, Admin, and Finance can view financial reports
    if (!in_array($user['role'], ['CFO', 'Admin', 'Finance'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    try {
        $sql = "SELECT 
                DATE_FORMAT(e.created_at, '%Y-%m') as period,
                COUNT(e.id) as total_expenses,
                SUM(CASE WHEN e.status = 'Approved' THEN 1 ELSE 0 END) as approved_expenses,
                SUM(CASE WHEN e.status = 'Pending' THEN 1 ELSE 0 END) as pending_expenses,
                SUM(CASE WHEN e.status = 'Approved' THEN e.company_amount ELSE 0 END) as total_amount,
                AVG(CASE WHEN e.status = 'Approved' THEN e.company_amount ELSE NULL END) as avg_amount,
                c.currency as company_currency
                FROM expenses e
                JOIN companies c ON e.company_id = c.id
                WHERE e.company_id = ?
                GROUP BY DATE_FORMAT(e.created_at, '%Y-%m'), c.currency
                ORDER BY period DESC
                LIMIT 12";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['company_id']]);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'reports' => $reports
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleCategoryBreakdown() {
    global $pdo;
    
    $user = currentUser();
    
    // Only CFO, Admin, and Finance can view category breakdown
    if (!in_array($user['role'], ['CFO', 'Admin', 'Finance'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    try {
        $sql = "SELECT 
                e.category,
                COUNT(e.id) as count,
                SUM(e.company_amount) as total_amount,
                AVG(e.company_amount) as avg_amount,
                (COUNT(e.id) * 100.0 / (SELECT COUNT(*) FROM expenses WHERE company_id = ?)) as percentage,
                c.currency as company_currency
                FROM expenses e
                JOIN companies c ON e.company_id = c.id
                WHERE e.company_id = ?
                GROUP BY e.category, c.currency
                ORDER BY total_amount DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['company_id'], $user['company_id']]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleHighValueExpenses() {
    global $pdo;
    
    $user = currentUser();
    
    // Only CFO, Admin, and Finance can view high value expenses
    if (!in_array($user['role'], ['CFO', 'Admin', 'Finance'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $threshold = (float)($_GET['threshold'] ?? 1000);
    
    try {
        $sql = "SELECT e.*, u.name as user_name,
                (SELECT COUNT(*) FROM approvals a WHERE a.expense_id = e.id AND a.status = 'Approved') as approved_count,
                (SELECT COUNT(*) FROM approvals a WHERE a.expense_id = e.id) as total_approvals
                FROM expenses e
                JOIN users u ON e.user_id = u.id
                WHERE e.company_id = ? AND e.company_amount >= ?
                ORDER BY e.company_amount DESC
                LIMIT 50";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['company_id'], $threshold]);
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'expenses' => $expenses
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}