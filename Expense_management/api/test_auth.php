<?php
header('Content-Type: application/json');

// Simple test without database
echo json_encode([
    'success' => true,
    'message' => 'Auth API is working',
    'time' => date('Y-m-d H:i:s')
]);
?>
