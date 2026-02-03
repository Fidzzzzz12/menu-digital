<?php

// API Handler untuk Vercel dengan Supabase
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Koneksi database ke Supabase
    $host = $_ENV['DB_HOST'] ?? 'aws-0-ap-southeast-1.pooler.supabase.com';
    $port = $_ENV['DB_PORT'] ?? '6543';
    $dbname = $_ENV['DB_DATABASE'] ?? 'postgres';
    $username = $_ENV['DB_USERNAME'] ?? 'postgres.ywqjqjqjqjqjqjqj';
    $password = $_ENV['DB_PASSWORD'] ?? 'your_password_here';
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Simple routing
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    
    // Remove query string
    $uri = parse_url($uri, PHP_URL_PATH);
    
    // Helper functions
    function generateOrderId() {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
    
    function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "$field wajib diisi";
            }
        }
        return $errors;
    }
    
    // API Routes
    if ($uri === '/api/test' || $uri === '/api/test.php') {
        echo json_encode([
            'status' => 'success',
            'message' => 'API berjalan dengan Supabase!',
            'method' => $method,
            'uri' => $uri,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    // Get toko by URL (untuk katalog)
    if (preg_match('/^\/api\/katalog\/(.+)$/', $uri, $matches)) {
        $urlToko = $matches[1];
        
        $stmt = $pdo->prepare("
            SELECT t.*, u.name as owner_name 
            FROM toko t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.url_toko = ?
        ");
        $stmt->execute([$urlToko]);
        $toko = $stmt->fetch();
        
        if (!$toko) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Toko tidak ditemukan']);
            exit;
        }
        
        // Get categories
        $stmt = $pdo->prepare("SELECT * FROM kategori WHERE user_id = ? ORDER BY nama");
        $stmt->execute([$toko['user_id']]);
        $categories = $stmt->fetchAll();
        
        // Get products
        $stmt = $pdo->prepare("
            SELECT p.*, k.nama as kategori_nama 
            FROM produk p 
            LEFT JOIN kategori k ON p.kategori_id = k.id 
            WHERE p.user_id = ? 
            ORDER BY p.nama
        ");
        $stmt->execute([$toko['user_id']]);
        $products = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'toko' => $toko,
                'categories' => $categories,
                'products' => $products
            ]
        ]);
        exit;
    }
    
    // Create pesanan
    if ($uri === '/api/pesanan/create' && $method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validation
        $required = ['url_toko', 'nama_lengkap', 'whatsapp', 'alamat', 'metode_pengiriman', 'items'];
        $errors = validateRequired($input, $required);
        
        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Validasi gagal', 'errors' => $errors]);
            exit;
        }
        
        // Validate metode_pengiriman
        if (!in_array($input['metode_pengiriman'], ['dikirim', 'ambil_sendiri'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Metode pengiriman tidak valid']);
            exit;
        }
        
        // Find toko
        $stmt = $pdo->prepare("SELECT * FROM toko WHERE url_toko = ?");
        $stmt->execute([$input['url_toko']]);
        $toko = $stmt->fetch();
        
        if (!$toko) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Toko tidak ditemukan']);
            exit;
        }
        
        try {
            $pdo->beginTransaction();
            
            // Calculate totals
            $subtotal = 0;
            foreach ($input['items'] as $item) {
                $subtotal += $item['harga'] * $item['quantity'];
            }
            
            $ongkir = $input['ongkir'] ?? 0;
            $totalHarga = $subtotal + $ongkir;
            
            // Insert pesanan
            $stmt = $pdo->prepare("
                INSERT INTO pesanan (
                    user_id, url_toko, order_id, nama_lengkap, whatsapp, alamat, catatan,
                    metode_pengiriman, ongkir, kurir, layanan_kurir, estimasi_kirim,
                    total_harga, status, order_date, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $orderId = generateOrderId();
            $now = date('Y-m-d H:i:s');
            
            $stmt->execute([
                $toko['user_id'],
                $input['url_toko'],
                $orderId,
                $input['nama_lengkap'],
                $input['whatsapp'],
                $input['alamat'],
                $input['catatan'] ?? '',
                $input['metode_pengiriman'],
                $ongkir,
                $input['kurir'] ?? null,
                $input['layanan_kurir'] ?? null,
                $input['estimasi_kirim'] ?? null,
                $totalHarga,
                'pending',
                $now,
                $now,
                $now
            ]);
            
            $pesananId = $pdo->lastInsertId();
            
            // Insert pesanan items
            $stmt = $pdo->prepare("
                INSERT INTO pesanan_item (
                    pesanan_id, produk_id, nama_produk, variant, harga, quantity, subtotal,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($input['items'] as $item) {
                $stmt->execute([
                    $pesananId,
                    $item['produk_id'],
                    $item['nama_produk'],
                    $item['variant'] ?? null,
                    $item['harga'],
                    $item['quantity'],
                    $item['harga'] * $item['quantity'],
                    $now,
                    $now
                ]);
            }
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Pesanan berhasil disimpan',
                'data' => [
                    'pesanan_id' => $pesananId,
                    'order_id' => $orderId,
                    'total_harga' => $totalHarga,
                    'ongkir' => $ongkir,
                    'subtotal' => $subtotal
                ]
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // RajaOngkir provinces
    if ($uri === '/api/v1/provinces') {
        $apiKey = 'EVHaKf6f836a12669421d071Ka2L2N7b';
        $url = 'https://api.rajaongkir.com/starter/province';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'key: ' . $apiKey
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        echo $response;
        exit;
    }
    
    // RajaOngkir cities
    if (preg_match('/^\/api\/v1\/cities\/(.+)$/', $uri, $matches)) {
        $provinceId = $matches[1];
        $apiKey = 'EVHaKf6f836a12669421d071Ka2L2N7b';
        $url = 'https://api.rajaongkir.com/starter/city?province=' . $provinceId;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'key: ' . $apiKey
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        echo $response;
        exit;
    }
    
    // RajaOngkir shipping cost
    if ($uri === '/api/v1/cost' && $method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $apiKey = 'EVHaKf6f836a12669421d071Ka2L2N7b';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.rajaongkir.com/starter/cost');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'origin' => $input['origin'] ?? '501', // Default Yogyakarta
            'destination' => $input['destination'],
            'weight' => $input['weight'] ?? 1000,
            'courier' => $input['courier'] ?? 'jne'
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'key: ' . $apiKey,
            'content-type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        echo $response;
        exit;
    }
    
    // Default response
    echo json_encode([
        'status' => 'success',
        'message' => 'Menu Digital API Server',
        'available_endpoints' => [
            'GET /api/test',
            'GET /api/katalog/{url_toko}',
            'POST /api/pesanan/create',
            'GET /api/v1/provinces',
            'GET /api/v1/cities/{province_id}',
            'POST /api/v1/cost'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database Connection Error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'API Error',
        'message' => $e->getMessage()
    ]);
}