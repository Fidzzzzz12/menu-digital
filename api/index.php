<?php

// Vercel Serverless PHP Handler for Laravel
try {
    // Set environment for serverless
    $_ENV['APP_ENV'] = 'production';
    $_ENV['LOG_CHANNEL'] = 'stderr';
    $_ENV['CACHE_DRIVER'] = 'array';
    $_ENV['SESSION_DRIVER'] = 'array';
    $_ENV['VIEW_COMPILED_PATH'] = '/tmp/views';
    
    // Create temp directories if needed
    if (!is_dir('/tmp/views')) {
        mkdir('/tmp/views', 0755, true);
    }
    
    // Load Laravel
    require_once __DIR__ . '/../public/index.php';
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Laravel Error',
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'PHP Error', 
        'message' => $e->getMessage()
    ]);
}