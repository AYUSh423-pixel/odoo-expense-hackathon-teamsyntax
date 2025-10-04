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
    case 'approve':
        if ($method === 'POST') {
            handleApprove();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'reject':
        if ($method === 'POST') {
            handleReject();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'pending':
        if ($method === 'GET') {
            handlePendingApprovals();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case 'override':
        if ($method === 'POST') {
            handleOverride();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function handleApprove() {
    global $pdo;
    
    $user = currentUser();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $expense_id = (int)($input['expense_id'] ?? 0);
    $comments = sanitizeInput($input['comments'] ?? '');
    
    if (!$expense_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Expense ID required']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if user has pending approval for this expense
        $stmt = $pdo->prepare("SELECT a.* FROM approvals a 
                              JOIN expenses e ON a.expense_id = e.id 
                              WHERE a.expense_id = ? AND a.approver_id = ? AND a.status = 'Pending' 
                              AND e.company_id = ?");
        $stmt->execute([$expense_id, $user['id'], $user['company_id']]);
        $approval = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$approval) {
            $pdo->rollback();
            http_response_code(403);
            echo json_encode(['error' => 'No pending approval found for this expense']);
            return;
        }
        
        // Update approval
        $stmt = $pdo->prepare("UPDATE approvals SET status = 'Approved', comments = ?, action_time = NOW() WHERE id = ?");
        $stmt->execute([$comments, $approval['id']]);
        
        // Evaluate approval rules
        evaluateApprovalRules($pdo, $expense_id);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Expense approved successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleReject() {
    global $pdo;
    
    $user = currentUser();
    $input = json_decode(file_get_contents('php://input'), true);
    
    $expense_id = (int)($input['expense_id'] ?? 0);
    $comments = sanitizeInput($input['comments'] ?? '');
    
    if (!$expense_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Expense ID required']);
        return;
    }
    
    if (empty($comments)) {
        http_response_code(400);
        echo json_encode(['error' => 'Comments are required for rejection']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Check if user has pending approval for this expense
        $stmt = $pdo->prepare("SELECT a.* FROM approvals a 
                              JOIN expenses e ON a.expense_id = e.id 
                              WHERE a.expense_id = ? AND a.approver_id = ? AND a.status = 'Pending' 
                              AND e.company_id = ?");
        $stmt->execute([$expense_id, $user['id'], $user['company_id']]);
        $approval = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$approval) {
            $pdo->rollback();
            http_response_code(403);
            echo json_encode(['error' => 'No pending approval found for this expense']);
            return;
        }
        
        // Update approval
        $stmt = $pdo->prepare("UPDATE approvals SET status = 'Rejected', comments = ?, action_time = NOW() WHERE id = ?");
        $stmt->execute([$comments, $approval['id']]);
        
        // Reject the entire expense
        $stmt = $pdo->prepare("UPDATE expenses SET status = 'Rejected' WHERE id = ?");
        $stmt->execute([$expense_id]);
        
        // Skip remaining approvals
        $stmt = $pdo->prepare("UPDATE approvals SET status = 'Skipped' WHERE expense_id = ? AND status = 'Pending'");
        $stmt->execute([$expense_id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Expense rejected successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function handlePendingApprovals() {
    global $pdo;
    
    $user = currentUser();
    $page = (int)($_GET['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    try {
        // Get pending approvals for current user
        $sql = "SELECT e.*, a.id as approval_id, a.step_order, a.comments, u.name as submitter_name,
                (SELECT COUNT(*) FROM approvals a2 WHERE a2.expense_id = e.id AND a2.status = 'Approved') as approved_count,
                (SELECT COUNT(*) FROM approvals a3 WHERE a3.expense_id = e.id) as total_approvals
                FROM approvals a
                JOIN expenses e ON a.expense_id = e.id
                JOIN users u ON e.user_id = u.id
                WHERE a.approver_id = ? AND a.status = 'Pending' AND e.company_id = ?
                ORDER BY e.created_at DESC
                LIMIT $limit OFFSET $offset";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['id'], $user['company_id']]);
        $approvals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM approvals a 
                              JOIN expenses e ON a.expense_id = e.id 
                              WHERE a.approver_id = ? AND a.status = 'Pending' AND e.company_id = ?");
        $stmt->execute([$user['id'], $user['company_id']]);
        $total = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'approvals' => $approvals,
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

function handleOverride() {
    global $pdo;
    
    $user = currentUser();
    
    // Only admin can override
    if ($user['role'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Only admin can override approvals']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $expense_id = (int)($input['expense_id'] ?? 0);
    $status = sanitizeInput($input['status'] ?? '');
    $comments = sanitizeInput($input['comments'] ?? '');
    
    if (!$expense_id || !in_array($status, ['Approved', 'Rejected'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Valid expense ID and status required']);
        return;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Update expense status
        $stmt = $pdo->prepare("UPDATE expenses SET status = ? WHERE id = ? AND company_id = ?");
        $stmt->execute([$status, $expense_id, $user['company_id']]);
        
        if ($stmt->rowCount() === 0) {
            $pdo->rollback();
            http_response_code(404);
            echo json_encode(['error' => 'Expense not found']);
            return;
        }
        
        // Update all pending approvals
        $stmt = $pdo->prepare("UPDATE approvals SET status = ?, comments = ?, action_time = NOW() WHERE expense_id = ? AND status = 'Pending'");
        $stmt->execute([$status === 'Approved' ? 'Approved' : 'Skipped', $comments, $expense_id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Expense status overridden successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}

function evaluateApprovalRules($pdo, $expense_id) {
    // Get company approval rules
    $stmt = $pdo->prepare("SELECT ar.*, e.company_id FROM approval_rules ar 
                          JOIN expenses e ON ar.company_id = e.company_id 
                          WHERE e.id = ?");
    $stmt->execute([$expense_id]);
    $rule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$rule) {
        return; // No rules defined
    }
    
    // Get all approvals for this expense
    $stmt = $pdo->prepare("SELECT * FROM approvals WHERE expense_id = ? ORDER BY step_order");
    $stmt->execute([$expense_id]);
    $approvals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check specific approver rule
    if ($rule['rule_type'] === 'Specific' && $rule['specific_approver_id']) {
        $specific_approved = false;
        foreach ($approvals as $approval) {
            if ($approval['approver_id'] == $rule['specific_approver_id'] && $approval['status'] === 'Approved') {
                $specific_approved = true;
                break;
            }
        }
        
        if ($specific_approved) {
            // Approve entire expense
            $stmt = $pdo->prepare("UPDATE expenses SET status = 'Approved' WHERE id = ?");
            $stmt->execute([$expense_id]);
            
            // Skip remaining approvals
            $stmt = $pdo->prepare("UPDATE approvals SET status = 'Skipped' WHERE expense_id = ? AND status = 'Pending'");
            $stmt->execute([$expense_id]);
            return;
        }
    }
    
    // Check parallel/percentage rule
    if ($rule['mode'] === 'PARALLEL' && $rule['rule_type'] === 'Percentage' && $rule['threshold']) {
        $current_step = min(array_column($approvals, 'step_order'));
        $step_approvals = array_filter($approvals, function($a) use ($current_step) {
            return $a['step_order'] == $current_step;
        });
        
        $approved_count = count(array_filter($step_approvals, function($a) {
            return $a['status'] === 'Approved';
        }));
        
        $percentage = ($approved_count / count($step_approvals)) * 100;
        
        if ($percentage >= $rule['threshold']) {
            // Approve entire expense
            $stmt = $pdo->prepare("UPDATE expenses SET status = 'Approved' WHERE id = ?");
            $stmt->execute([$expense_id]);
            
            // Skip remaining approvals
            $stmt = $pdo->prepare("UPDATE approvals SET status = 'Skipped' WHERE expense_id = ? AND status = 'Pending'");
            $stmt->execute([$expense_id]);
            return;
        }
    }
    
    // Check sequential progression
    if ($rule['mode'] === 'SEQUENTIAL') {
        $current_step = min(array_column(array_filter($approvals, function($a) {
            return $a['status'] === 'Pending';
        }), 'step_order'));
        
        $current_step_approvals = array_filter($approvals, function($a) use ($current_step) {
            return $a['step_order'] == $current_step;
        });
        
        $all_approved = true;
        foreach ($current_step_approvals as $approval) {
            if ($approval['status'] !== 'Approved') {
                $all_approved = false;
                break;
            }
        }
        
        if ($all_approved) {
            // Check if this was the last step
            $max_step = max(array_column($approvals, 'step_order'));
            if ($current_step >= $max_step) {
                // Approve entire expense
                $stmt = $pdo->prepare("UPDATE expenses SET status = 'Approved' WHERE id = ?");
                $stmt->execute([$expense_id]);
            }
        }
    }
}
