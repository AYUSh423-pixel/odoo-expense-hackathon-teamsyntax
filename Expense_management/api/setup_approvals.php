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
    case 'setup_default':
        if ($method === 'POST') {
            handleSetupDefault();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'get_rules':
        if ($method === 'GET') {
            handleGetRules();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'update_rules':
        if ($method === 'POST') {
            handleUpdateRules();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function handleSetupDefault() {
    global $pdo;
    
    $user = currentUser();
    
    // Only admin can setup approval rules
    if ($user['role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Only admin can setup approval rules']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Clear existing sequences and rules
        $stmt = $pdo->prepare("DELETE FROM approval_sequences WHERE company_id = ?");
        $stmt->execute([$user['company_id']]);
        
        $stmt = $pdo->prepare("DELETE FROM approval_rules WHERE company_id = ?");
        $stmt->execute([$user['company_id']]);
        
        // Create default approval sequence
        $sequences = [
            ['step_order' => 1, 'approver_type' => 'ROLE', 'approver_role' => 'Manager'],
            ['step_order' => 2, 'approver_type' => 'ROLE', 'approver_role' => 'HR'],
            ['step_order' => 3, 'approver_type' => 'ROLE', 'approver_role' => 'Finance'],
            ['step_order' => 4, 'approver_type' => 'ROLE', 'approver_role' => 'CFO'],
            ['step_order' => 5, 'approver_type' => 'ROLE', 'approver_role' => 'Director']
        ];
        
        foreach ($sequences as $seq) {
            $stmt = $pdo->prepare("INSERT INTO approval_sequences (company_id, step_order, approver_type, approver_role) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $user['company_id'],
                $seq['step_order'],
                $seq['approver_type'],
                $seq['approver_role']
            ]);
        }
        
        // Create default approval rule (Percentage Rule - 60%)
        $stmt = $pdo->prepare("INSERT INTO approval_rules (company_id, mode, rule_type, threshold) VALUES (?, 'PARALLEL', 'Percentage', 60)");
        $stmt->execute([$user['company_id']]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Default approval workflow setup successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleGetRules() {
    global $pdo;
    
    $user = currentUser();
    
    // Only admin can view approval rules
    if ($user['role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Only admin can view approval rules']);
        return;
    }
    
    try {
        // Get approval sequences
        $stmt = $pdo->prepare("SELECT * FROM approval_sequences WHERE company_id = ? ORDER BY step_order");
        $stmt->execute([$user['company_id']]);
        $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get approval rules
        $stmt = $pdo->prepare("SELECT * FROM approval_rules WHERE company_id = ?");
        $stmt->execute([$user['company_id']]);
        $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'sequences' => $sequences,
            'rules' => $rules
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleUpdateRules() {
    global $pdo;
    
    $user = currentUser();
    
    // Only admin can update approval rules
    if ($user['role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Only admin can update approval rules']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $rule_type = sanitizeInput($input['rule_type'] ?? 'Percentage');
    $mode = sanitizeInput($input['mode'] ?? 'PARALLEL');
    $threshold = (int)($input['threshold'] ?? 60);
    $specific_approver_id = (int)($input['specific_approver_id'] ?? 0);
    
    if (!in_array($rule_type, ['None', 'Percentage', 'Specific', 'Hybrid'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid rule type']);
        return;
    }
    
    if (!in_array($mode, ['SEQUENTIAL', 'PARALLEL'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid mode']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update approval rules
        $stmt = $pdo->prepare("UPDATE approval_rules SET rule_type = ?, mode = ?, threshold = ?, specific_approver_id = ? WHERE company_id = ?");
        $stmt->execute([$rule_type, $mode, $threshold, $specific_approver_id ?: null, $user['company_id']]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Approval rules updated successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
