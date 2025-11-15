<?php
header('Content-Type: application/json');

// เพิ่มเวลาและ memory สำหรับการประมวลผล
set_time_limit(300); // 5 นาที
ini_set('memory_limit', '256M'); // เพิ่ม memory
ini_set('max_execution_time', 300);

// ปิด output buffering ที่อาจทำให้ JSON เสีย
if (ob_get_level()) ob_end_clean();

// ปิด error แสดงหน้าเว็บ
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'import_error.log');

session_start();

// ตรวจสอบการ login
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// เชื่อมต่อฐานข้อมูล
if (!file_exists("config/Database.php")) {
    echo json_encode(['success' => false, 'message' => 'Database config not found']);
    exit;
}

include_once("config/Database.php");

/**
 * ดาวน์โหลดรูปภาพจาก URL และบันทึกลง server
 */
function downloadImage($url, $productId, $productName) {
    // ถ้า URL ว่างเปล่า หรือเป็น placeholder
    if (empty($url) || strpos($url, 'placeholder') !== false || strpos($url, 'placehold') !== false) {
        return createPlaceholder($productName);
    }
    
    // ถ้าเป็นไฟล์ local อยู่แล้ว
    if (strpos($url, 'uploads/') === 0) {
        return $url;
    }
    
    // สร้างโฟลเดอร์ถ้ายังไม่มี
    $uploadDir = 'uploads/products/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // หาประเภทไฟล์
    $extension = 'jpg';
    $urlPath = parse_url($url, PHP_URL_PATH);
    if ($urlPath) {
        $ext = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $extension = $ext;
        }
    }
    
    // สร้างชื่อไฟล์
    $filename = 'product_' . $productId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    try {
        // ดาวน์โหลดรูป
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $imageData = @file_get_contents($url, false, $context);
        
        if ($imageData === false || strlen($imageData) < 100) {
            error_log("Failed to download image from: $url");
            return createPlaceholder($productName);
        }
        
        // บันทึกไฟล์
        if (file_put_contents($filepath, $imageData)) {
            error_log("Downloaded image: $filepath");
            return $filepath;
        }
        
    } catch (Exception $e) {
        error_log("Download error: " . $e->getMessage());
    }
    
    return createPlaceholder($productName);
}

/**
 * สร้างรูป placeholder ด้วย GD Library
 */
function createPlaceholder($text, $width = 300, $height = 300) {
    $uploadDir = 'uploads/products/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $filename = 'placeholder_' . md5($text) . '.png';
    $filepath = $uploadDir . $filename;
    
    // ถ้ามีอยู่แล้ว ไม่ต้องสร้างใหม่
    if (file_exists($filepath)) {
        return $filepath;
    }
    
    // สร้างรูป
    $image = imagecreatetruecolor($width, $height);
    
    // สีพื้นหลัง
    $bgColor = imagecolorallocate($image, 102, 126, 234);
    imagefill($image, 0, 0, $bgColor);
    
    // สีตัวอักษร
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    // ตัดข้อความ
    $displayText = substr($text, 0, 20);
    
    // เขียนข้อความ
    $fontSize = 3;
    $textWidth = imagefontwidth($fontSize) * strlen($displayText);
    $textHeight = imagefontheight($fontSize);
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    imagestring($image, $fontSize, $x, $y, $displayText, $textColor);
    
    // บันทึกรูป
    imagepng($image, $filepath);
    imagedestroy($image);
    
    return $filepath;
}

try {
    $input = file_get_contents('php://input');
    error_log("=== BULK IMPORT START ===");
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    if (!isset($data['products']) || !is_array($data['products'])) {
        throw new Exception('Products array is required');
    }
    
    $products = $data['products'];
    $total = count($products);
    error_log("Products to import: $total");
    
    if (empty($products)) {
        throw new Exception('No products to import');
    }
    
    // เชื่อมต่อฐานข้อมูล
    $connectDB = new Database();
    $db = $connectDB->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    // ไม่ใช้ transaction สำหรับข้อมูลจำนวนมาก
    // $db->beginTransaction();
    
    // SQL statement
    $sql = "INSERT INTO products (
        name, price, category, description, stock, image_path, active, created_at, updated_at
    ) VALUES (
        :name, :price, :category, :description, :stock, :image_path, :active, NOW(), NOW()
    )";
    
    $stmt = $db->prepare($sql);
    
    $imported = 0;
    $errors = [];
    $batchSize = 10; // ประมวลผลทีละ 10 รายการ
    
    foreach ($products as $index => $product) {
        try {
            // Validate
            if (empty($product['name'])) {
                throw new Exception("Product name required");
            }
            
            if (!isset($product['price']) || $product['price'] < 0) {
                throw new Exception("Valid price required");
            }
            
            // จัดการรูปภาพ - ไม่ดาวน์โหลดเพื่อประหยัดเวลา (ทำทีหลังได้)
            $imageUrl = isset($product['image_url']) ? $product['image_url'] : '';
            
            // ใช้ placeholder หรือ URL เดิม
            if (empty($imageUrl) || strpos($imageUrl, 'placeholder') !== false) {
                $imagePath = 'https://placehold.co/300x300/667eea/white?text=' . urlencode(substr($product['name'], 0, 10));
            } else {
                $imagePath = $imageUrl; // เก็บ URL เดิมไว้ก่อน
            }
            
            // Bind parameters
            $stmt->bindParam(':name', $product['name']);
            $stmt->bindParam(':price', $product['price']);
            $stmt->bindParam(':category', $product['category']);
            $stmt->bindParam(':description', $product['description']);
            $stmt->bindParam(':stock', $product['stock']);
            $stmt->bindParam(':image_path', $imagePath);
            $stmt->bindParam(':active', $product['active']);
            
            // Execute
            if ($stmt->execute()) {
                $imported++;
                
                // Log ทุก 10 รายการ
                if ($imported % $batchSize === 0) {
                    error_log("Progress: $imported / $total");
                    
                    // Flush output เพื่อป้องกัน timeout
                    if (ob_get_level() > 0) {
                        ob_flush();
                        flush();
                    }
                }
            } else {
                $errorInfo = $stmt->errorInfo();
                $errors[] = "Row " . ($index + 1) . ": " . $errorInfo[2];
            }
            
        } catch (Exception $e) {
            $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
        }
    }
    
    // ไม่ใช้ commit เพราะไม่ได้ใช้ transaction
    // $db->commit();
    
    error_log("=== COMPLETE: $imported / $total ===");
    
    // Response
    $response = [
        'success' => true,
        'imported' => $imported,
        'total' => $total,
        'message' => "Successfully imported $imported out of $total products"
    ];
    
    if (!empty($errors)) {
        $response['errors'] = array_slice($errors, 0, 10); // แสดงแค่ 10 error แรก
        $response['error_count'] = count($errors);
    }
    
    // Ensure clean JSON output
    echo json_encode($response);
    exit;
    
} catch (Exception $e) {
    error_log('❌ ERROR: ' . $e->getMessage());
    
    $errorResponse = [
        'success' => false,
        'message' => $e->getMessage(),
        'imported' => isset($imported) ? $imported : 0,
        'total' => isset($products) ? count($products) : 0
    ];
    
    echo json_encode($errorResponse);
    exit;
}
?>