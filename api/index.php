<?php

// Simple Laravel API Handler for Vercel
header('Content-Type: application/json');

try {
    // Basic Laravel bootstrap without full framework
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Simple routing
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Remove query string
    $uri = parse_url($uri, PHP_URL_PATH);
    
    // API Routes
    if ($uri === '/api/test' || $uri === '/api/test.php') {
        echo json_encode([
            'status' => 'success',
            'message' => 'Laravel API is working!',
            'method' => $method,
            'uri' => $uri,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    if ($uri === '/api/v1/provinces') {
        // Mock provinces data
        echo json_encode([
            'success' => true,
            'data' => [
                ['province_id' => '1', 'province' => 'Bali'],
                ['province_id' => '2', 'province' => 'Jawa Barat'],
                ['province_id' => '3', 'province' => 'DKI Jakarta']
            ]
        ]);
        exit;
    }
    
    // Default response
    echo json_encode([
        'status' => 'success',
        'message' => 'Laravel API Server',
        'available_endpoints' => [
            '/api/test',
            '/api/v1/provinces'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'API Error',
        'message' => $e->getMessage()
    ]);
}