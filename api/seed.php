<?php

// Demo data seeder for Supabase
header('Content-Type: application/json');

try {
    // Database connection to Supabase
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
    
    $pdo->beginTransaction();
    
    // Insert demo user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, email_verified_at, password, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?) 
        ON CONFLICT (email) DO NOTHING
        RETURNING id
    ");
    
    $now = date('Y-m-d H:i:s');
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt->execute([
        'Demo Toko Owner',
        'demo@example.com',
        $now,
        $hashedPassword,
        $now,
        $now
    ]);
    
    $result = $stmt->fetch();
    $userId = $result ? $result['id'] : null;
    
    // If user already exists, get the ID
    if (!$userId) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute(['demo@example.com']);
        $user = $stmt->fetch();
        $userId = $user['id'];
    }
    
    // Insert demo toko
    $stmt = $pdo->prepare("
        INSERT INTO toko (user_id, nama_toko, url_toko, deskripsi, alamat, whatsapp, kota, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
        ON CONFLICT (url_toko) DO NOTHING
    ");
    
    $stmt->execute([
        $userId,
        'Warung Makan Sederhana',
        'demo-toko',
        'Warung makan dengan menu tradisional Indonesia yang lezat dan terjangkau',
        'Jl. Contoh No. 123, Yogyakarta',
        '6281234567890',
        'Yogyakarta',
        $now,
        $now
    ]);
    
    // Insert demo categories
    $categories = [
        ['Makanan Utama', 'Nasi dan lauk pauk'],
        ['Minuman', 'Minuman segar dan hangat'],
        ['Snack', 'Camilan dan makanan ringan']
    ];
    
    $categoryIds = [];
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("
            INSERT INTO kategori (user_id, nama, deskripsi, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?) 
            ON CONFLICT (user_id, nama) DO NOTHING
            RETURNING id
        ");
        
        $stmt->execute([
            $userId,
            $category[0],
            $category[1],
            $now,
            $now
        ]);
        
        $result = $stmt->fetch();
        if ($result) {
            $categoryIds[$category[0]] = $result['id'];
        } else {
            // Get existing category ID
            $stmt = $pdo->prepare("SELECT id FROM kategori WHERE user_id = ? AND nama = ?");
            $stmt->execute([$userId, $category[0]]);
            $cat = $stmt->fetch();
            if ($cat) {
                $categoryIds[$category[0]] = $cat['id'];
            }
        }
    }
    
    // Insert demo products
    $products = [
        ['Nasi Gudeg', 'Makanan Utama', 15000, 'Nasi gudeg khas Yogyakarta dengan ayam dan telur'],
        ['Nasi Pecel', 'Makanan Utama', 12000, 'Nasi dengan sayuran dan bumbu pecel'],
        ['Soto Ayam', 'Makanan Utama', 18000, 'Soto ayam dengan kuah bening dan rempah'],
        ['Es Teh Manis', 'Minuman', 5000, 'Es teh manis segar'],
        ['Es Jeruk', 'Minuman', 8000, 'Es jeruk peras segar'],
        ['Kopi Tubruk', 'Minuman', 6000, 'Kopi tubruk tradisional'],
        ['Kerupuk', 'Snack', 3000, 'Kerupuk renyah'],
        ['Tempe Goreng', 'Snack', 5000, 'Tempe goreng crispy']
    ];
    
    foreach ($products as $product) {
        $categoryId = $categoryIds[$product[1]] ?? null;
        if ($categoryId) {
            $stmt = $pdo->prepare("
                INSERT INTO produk (user_id, kategori_id, nama, harga, deskripsi, stok, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                ON CONFLICT (user_id, nama) DO NOTHING
            ");
            
            $stmt->execute([
                $userId,
                $categoryId,
                $product[0],
                $product[2],
                $product[3],
                100, // Default stock
                $now,
                $now
            ]);
        }
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Demo data seeded successfully',
        'user_id' => $userId,
        'categories' => count($categoryIds),
        'products' => count($products)
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'error' => 'Database Error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'error' => 'Seeding Error',
        'message' => $e->getMessage()
    ]);
}