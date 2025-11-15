<?php
session_start();
header('Content-Type: application/json');

// Log สำหรับ debug
error_log("=== SAVE PRODUCT REQUEST ===");
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

if (!isset($_SESSION['userid'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Please login first']);
    exit;
}

include_once("config/Database.php");

try {
    $connectDB = new Database();
    $db = $connectDB->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($_POST['name'])) {
        throw new Exception('Product name is required');
    }
    
    if (empty($_POST['price']) || $_POST['price'] <= 0) {
        throw new Exception('Valid price is required');
    }
    
    if (empty($_POST['category'])) {
        throw new Exception('Category is required');
    }
    
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 100;
    $category = trim($_POST['category']);
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    error_log("Product ID: $productId");
    error_log("Name: $name, Price: $price, Stock: $stock");
    
    // Handle image upload
    $imagePath = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        error_log("Processing image upload...");
        
        // มีการอัปโหลดรูปใหม่
        $uploadDir = 'uploads/products/';
        
        // สร้างโฟลเดอร์ถ้ายังไม่มี
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception('Failed to create upload directory');
            }
            error_log("Created directory: $uploadDir");
        }
        
        $fileInfo = pathinfo($_FILES['image']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // ตรวจสอบนามสกุลไฟล์
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF, WEBP allowed.');
        }
        
        // ตรวจสอบขนาดไฟล์ (5MB)
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            throw new Exception('File size must be less than 5MB');
        }
        
        // สร้างชื่อไฟล์ใหม่ (ไม่ซ้ำ)
        $newFileName = 'product_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $newFileName;
        
        error_log("Moving file to: $targetPath");
        
        // ย้ายไฟล์
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $targetPath;
            error_log("✅ Image uploaded successfully: $imagePath");
            
            // ลบรูปเก่าถ้ามี (กรณีแก้ไข)
            if ($productId > 0 && isset($_POST['existing_image']) && !empty($_POST['existing_image'])) {
                $oldImage = $_POST['existing_image'];
                if (file_exists($oldImage) && strpos($oldImage, 'uploads/') === 0) {
                    unlink($oldImage);
                    error_log("Deleted old image: $oldImage");
                }
            }
        } else {
            throw new Exception('Failed to upload image. Check folder permissions.');
        }
    } else {
        error_log("No new image uploaded");
        
        // ไม่มีการอัปโหลดรูปใหม่
        if ($productId > 0) {
            // กรณีแก้ไข - ใช้รูปเดิม
            if (isset($_POST['existing_image']) && !empty($_POST['existing_image'])) {
                $imagePath = $_POST['existing_image'];
                error_log("Using existing image: $imagePath");
            } else {
                throw new Exception('No image provided for update');
            }
        } else {
            // กรณีเพิ่มใหม่ - ต้องมีรูป
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File too large (php.ini limit)',
                    UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
                    UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'No temp directory',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
                    UPLOAD_ERR_EXTENSION => 'PHP extension stopped upload'
                ];
                $errorMsg = isset($uploadErrors[$_FILES['image']['error']]) 
                    ? $uploadErrors[$_FILES['image']['error']] 
                    : 'Unknown upload error';
                throw new Exception('Product image is required. Upload error: ' . $errorMsg);
            } else {
                throw new Exception('Product image is required for new products');
            }
        }
    }
    
    if ($productId > 0) {
        // แก้ไขสินค้า
        error_log("Updating product ID: $productId");
        
        $query = "UPDATE products SET 
                    name = :name, 
                    price = :price, 
                    stock = :stock, 
                    category = :category, 
                    description = :description, 
                    image_path = :image_path,
                    updated_at = NOW()
                  WHERE id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $productId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_path', $imagePath);
        
        if ($stmt->execute()) {
            error_log("✅ Product updated successfully");
            
            // ตรวจสอบว่าอัปเดตจริง
            $verifyQuery = "SELECT * FROM products WHERE id = :id";
            $verifyStmt = $db->prepare($verifyQuery);
            $verifyStmt->bindParam(':id', $productId);
            $verifyStmt->execute();
            $updatedProduct = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            error_log("Updated product data: " . print_r($updatedProduct, true));
            
            echo json_encode([
                'success' => true, 
                'message' => 'Product updated successfully!',
                'product_id' => $productId,
                'product_name' => $updatedProduct['name']
            ]);
        } else {
            error_log("❌ Failed to update: " . print_r($stmt->errorInfo(), true));
            throw new Exception('Failed to update product in database');
        }
        
    } else {
        // เพิ่มสินค้าใหม่
        error_log("Inserting new product");
        
        $query = "INSERT INTO products 
                  (name, price, stock, category, description, image_path, active, created_at, updated_at) 
                  VALUES (:name, :price, :stock, :category, :description, :image_path, 1, NOW(), NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_path', $imagePath);
        
        if ($stmt->execute()) {
            $newId = $db->lastInsertId();
            error_log("✅ Product added successfully. New ID: $newId");
            
            // ตรวจสอบว่าบันทึกจริง
            $verifyQuery = "SELECT * FROM products WHERE id = :id";
            $verifyStmt = $db->prepare($verifyQuery);
            $verifyStmt->bindParam(':id', $newId);
            $verifyStmt->execute();
            $savedProduct = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            error_log("Saved product data: " . print_r($savedProduct, true));
            
            if (!$savedProduct) {
                throw new Exception('Product was inserted but cannot be retrieved');
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Product added successfully!',
                'product_id' => $newId,
                'product_name' => $savedProduct['name'],
                'active' => $savedProduct['active']
            ]);
        } else {
            error_log("❌ Failed to insert: " . print_r($stmt->errorInfo(), true));
            throw new Exception('Failed to add product to database');
        }
    }
    
} catch (Exception $e) {
    error_log("❌ ERROR: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>