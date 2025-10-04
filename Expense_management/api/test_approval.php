<?php
// Simple test to check if the approval API is working
header('Content-Type: application/json');

try {
    // Test basic JSON response
    echo json_encode([
        'success' => true,
        'message' => 'API is working',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
