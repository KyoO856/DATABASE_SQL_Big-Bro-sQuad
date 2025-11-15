<?php
// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

// ตรวจสอบการ login
if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// ฟังก์ชันตรวจสอบและแก้ไข URL รูปภาพ
function validateAndFixImageUrl($url, $productName = '') {
    // ถ้า URL ว่างเปล่า
    if (empty($url) || trim($url) === '') {
        return 'https://via.placeholder.com/300/667eea/ffffff?text=' . urlencode($productName ?: 'No Image');
    }
    
    // ถ้า URL ไม่ถูกต้อง
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return 'https://via.placeholder.com/300/667eea/ffffff?text=' . urlencode($productName ?: 'Invalid URL');
    }
    
    // ตรวจสอบว่าเป็น URL รูปภาพหรือไม่
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $urlPath = parse_url($url, PHP_URL_PATH);
    $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $imageExtensions)) {
        return 'https://via.placeholder.com/300/667eea/ffffff?text=' . urlencode($productName ?: 'Not Image');
    }
    
    return $url;
}

// ฟังก์ชันทำความสะอาดข้อมูล
function sanitizeProduct($product) {
    return [
        'name' => isset($product['name']) ? trim($product['name']) : 'Unnamed Product',
        'price' => isset($product['price']) ? floatval($product['price']) : 0,
        'category' => isset($product['category']) ? trim($product['category']) : 'Uncategorized',
        'description' => isset($product['description']) ? trim($product['description']) : '',
        'stock' => isset($product['stock']) ? intval($product['stock']) : 100,
        'image_url' => validateAndFixImageUrl(
            isset($product['image_url']) ? $product['image_url'] : '', 
            isset($product['name']) ? $product['name'] : ''
        ),
        'active' => isset($product['active']) ? intval($product['active']) : 1
    ];
}

try {
    $type = $_POST['type'] ?? '';
    $products = [];
    
    // ==================== CSV IMPORT ====================
    if ($type === 'csv' && isset($_FILES['file'])) {
        $file = $_FILES['file']['tmp_name'];
        $fileExtension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        
        // ตรวจสอบนามสกุลไฟล์
        if (!in_array($fileExtension, ['csv', 'xlsx', 'xls'])) {
            throw new Exception('Invalid file type. Only CSV, XLSX, XLS are allowed.');
        }
        
        // อ่าน CSV
        if ($fileExtension === 'csv') {
            if (($handle = fopen($file, 'r')) !== false) {
                // อ่านหัวตาราง
                $headers = fgetcsv($handle);
                
                if (!$headers) {
                    throw new Exception('CSV file is empty or invalid');
                }
                
                // ทำความสะอาดหัวตาราง (ลบช่องว่าง)
                $headers = array_map('trim', $headers);
                
                // ตรวจสอบว่ามีคอลัมน์ที่จำเป็นหรือไม่
                $requiredColumns = ['name', 'price'];
                foreach ($requiredColumns as $col) {
                    if (!in_array($col, $headers)) {
                        throw new Exception("Missing required column: $col");
                    }
                }
                
                $rowNumber = 1;
                while (($row = fgetcsv($handle)) !== false) {
                    $rowNumber++;
                    
                    // ข้ามแถวว่าง
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    // สร้าง associative array
                    if (count($row) === count($headers)) {
                        $product = array_combine($headers, $row);
                        $products[] = sanitizeProduct($product);
                    } else {
                        error_log("Row $rowNumber: Column count mismatch");
                    }
                }
                fclose($handle);
            } else {
                throw new Exception('Cannot open CSV file');
            }
        }
        
        // สำหรับ Excel (XLSX, XLS) - ต้องใช้ library เช่น PhpSpreadsheet
        // ในตัวอย่างนี้จะแสดงเฉพาะ CSV
        elseif (in_array($fileExtension, ['xlsx', 'xls'])) {
            // ต้อง install PhpSpreadsheet: composer require phpoffice/phpspreadsheet
            // require 'vendor/autoload.php';
            // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            // ... code for Excel processing
            
            throw new Exception('Excel import requires PhpSpreadsheet library. Please use CSV format.');
        }
    }
    
    // ==================== JSON IMPORT ====================
    elseif ($type === 'json' && isset($_FILES['file'])) {
        $jsonContent = file_get_contents($_FILES['file']['tmp_name']);
        
        if ($jsonContent === false) {
            throw new Exception('Cannot read JSON file');
        }
        
        $jsonData = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON format: ' . json_last_error_msg());
        }
        
        if (!is_array($jsonData)) {
            throw new Exception('JSON must contain an array of products');
        }
        
        // ทำความสะอาดและ validate ข้อมูล
        foreach ($jsonData as $product) {
            $products[] = sanitizeProduct($product);
        }
    }
    
    // ==================== API IMPORT ====================
    elseif ($type === 'api') {
        $apiUrl = $_POST['api_url'] ?? '';
        $apiKey = $_POST['api_key'] ?? '';
        
        if (empty($apiUrl)) {
            throw new Exception('API URL is required');
        }
        
        if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid API URL');
        }
        
        // สร้าง HTTP context
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "Content-type: application/json\r\n" .
                           "User-Agent: SportShopImporter/1.0\r\n" .
                           (!empty($apiKey) ? "Authorization: Bearer $apiKey\r\n" : ""),
                'timeout' => 30
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ]
        ];
        
        $context = stream_context_create($options);
        
        // เรียก API
        $response = @file_get_contents($apiUrl, false, $context);
        
        if ($response === false) {
            $error = error_get_last();
            throw new Exception('API request failed: ' . ($error['message'] ?? 'Unknown error'));
        }
        
        $apiData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response from API: ' . json_last_error_msg());
        }
        
        if (!is_array($apiData)) {
            throw new Exception('API must return an array of products');
        }
        
        // ทำความสะอาดข้อมูล
        foreach ($apiData as $product) {
            $products[] = sanitizeProduct($product);
        }
    }
    
    else {
        throw new Exception('Invalid import type or missing file');
    }
    
    // ตรวจสอบว่ามีสินค้าหรือไม่
    if (empty($products)) {
        throw new Exception('No valid products found in the file');
    }
    
    // ส่งผลลัพธ์กลับ
    echo json_encode([
        'success' => true,
        'products' => $products,
        'count' => count($products),
        'message' => count($products) . ' products ready to import'
    ]);
    
} catch (Exception $e) {
    error_log('Import Error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'products' => [],
        'count' => 0
    ]);
}
?>