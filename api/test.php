<?php

// Simple test endpoint
header('Content-Type: application/json');

echo json_encode([
    'status' => 'success',
    'message' => 'PHP is working on Vercel!',
    'php_version' => phpversion(),
    'timestamp' => date('Y-m-d H:i:s')
]);