<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function loginUser($user) {
    $_SESSION['user'] = [
        'id' => $user['id'],
        'role' => $user['role'],
        'company_id' => $user['company_id'],
        'name' => $user['name'],
        'email' => $user['email']
    ];
    // Force session write
    session_write_close();
    session_start();
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function requireRole($roles = []) {
    $u = currentUser();
    if (!$u || !in_array($u['role'], (array)$roles)) {
        http_response_code(403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }
}

function requireLogin() {
    if (!currentUser()) {
        header('Location: /Expense_management/public/index.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: /Expense_management/public/index.php');
    exit;
}
