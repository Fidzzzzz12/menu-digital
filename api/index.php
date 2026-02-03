<?php

// Debug Vercel PHP Handler
try {
    // Check if Laravel bootstrap exists
    if (!file_exists(__DIR__ . '/../public/index.php')) {
        http_response_code(500);
        echo json_encode(['error' => 'Laravel public/index.php not found']);
        exit;
    }
    
    // Try to load Laravel
    require_once __DIR__ . '/../public/index.php';
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Laravel Error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'PHP Error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}