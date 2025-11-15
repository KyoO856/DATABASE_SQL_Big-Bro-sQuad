<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

/**
 * ดาวน์โหลดรูปภาพจาก URL และบันทึกลง server
 */
function downloadImage($url, $productId, $productName) {
    // สร้างโฟลเดอร์ถ้ายังไม่มี
    $uploadDir = 'uploads/products/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // สร้างชื่อไฟล์ที่ปลอดภัย
    $extension = 'jpg'; // default
    $urlPath = parse_url($url, PHP_URL_PATH);
    if ($urlPath) {
        $ext = pathinfo($urlPath, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $extension = strtolower($ext);
        }
    }
    
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
        
        if ($imageData === false) {
            return false;
        }
        
        // บันทึกไฟล์
        if (file_put_contents($filepath, $imageData)) {
            return $filepath;
        }
        
    } catch (Exception $e) {
        error_log("Download error: " . $e->getMessage());
        return false;
    }
    
    return false;
}

/**
 * สร้างรูป placeholder
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
    $bgColor = imagecolorallocate($image, 102, 126, 234); // #667eea
    imagefill($image, 0, 0, $bgColor);
    
    // สีตัวอักษร
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    // เขียนข้อความ
    $fontSize = 20;
    $textBox = imagettfbbox($fontSize, 0, __DIR__ . '/fonts/Arial.ttf', $text);
    
    // ถ้าไม่มี font ใช้ default
    if (!$textBox) {
        $x = ($width - (strlen($text) * 10)) / 2;
        $y = $height / 2;
        imagestring($image, 5, $x, $y, $text, $textColor);
    }
    
    // บันทึกรูป
    imagepng($image, $filepath);
    imagedestroy($image);
    
    return $filepath;
}

try {
    include_once("config/Database.php");
    
    $connectDB = new Database();
    $db = $connectDB->getConnection();
    
    // ดึงสินค้าที่มี URL รูปภาพ
    $sql = "SELECT id, name, image_url FROM products WHERE image_url != '' AND image_url IS NOT NULL";
    $stmt = $db->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total = count($products);
    $downloaded = 0;
    $failed = 0;
    $skipped = 0;
    
    $updateStmt = $db->prepare("UPDATE products SET image_url = :new_url WHERE id = :id");
    
    foreach ($products as $product) {
        $imageUrl = $product['image_url'];
        
        // ข้ามถ้าเป็นไฟล์ local อยู่แล้ว
        if (strpos($imageUrl, 'uploads/') === 0) {
            $skipped++;
            continue;
        }
        
        // พยายามดาวน์โหลด
        $localPath = downloadImage($imageUrl, $product['id'], $product['name']);
        
        if ($localPath) {
            // อัพเดท path ในฐานข้อมูล
            $updateStmt->bindParam(':new_url', $localPath);
            $updateStmt->bindParam(':id', $product['id']);
            $updateStmt->execute();
            
            $downloaded++;
        } else {
            // สร้าง placeholder
            $placeholderPath = createPlaceholder(substr($product['name'], 0, 20));
            
            $updateStmt->bindParam(':new_url', $placeholderPath);
            $updateStmt->bindParam(':id', $product['id']);
            $updateStmt->execute();
            
            $failed++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'total' => $total,
        'downloaded' => $downloaded,
        'failed' => $failed,
        'skipped' => $skipped,
        'message' => "Downloaded: $downloaded, Failed: $failed, Skipped: $skipped out of $total products"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>