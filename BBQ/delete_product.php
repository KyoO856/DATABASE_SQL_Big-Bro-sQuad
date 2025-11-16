<?php
session_start();
header('Content-Type: application/json');

// Log สำหรับ debug
error_log("=== DELETE PRODUCT REQUEST ===");
error_log("Session userid: " . (isset($_SESSION['userid']) ? $_SESSION['userid'] : 'NOT SET'));

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
    
    // อ่าน input
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput);
    
    if (empty($rawInput)) {
        throw new Exception('No data received from client');
    }
    
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    error_log("Parsed input: " . print_r($input, true));
    
    if (!isset($input['product_id'])) {
        throw new Exception('Product ID is missing');
    }
    
    $productId = intval($input['product_id']);
    
    if ($productId <= 0) {
        throw new Exception('Invalid product ID: ' . $productId);
    }
    
    error_log("Deleting product ID: $productId");
    
    // ดึงข้อมูลสินค้าเพื่อลบรูปภาพ
    $query = "SELECT id, name, image_path FROM products WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('Product not found with ID: ' . $productId);
    }
    
    error_log("Found product: " . $product['name'] . " (Image: " . $product['image_path'] . ")");
    
    // ลบรูปภาพ (ถ้าอยู่ในโฟลเดอร์ uploads)
    if (!empty($product['image_path'])) {
        if (file_exists($product['image_path']) && strpos($product['image_path'], 'uploads/') === 0) {
            if (unlink($product['image_path'])) {
                error_log("✅ Deleted image file: " . $product['image_path']);
            } else {
                error_log("⚠️ Failed to delete image file: " . $product['image_path']);
            }
        } else {
            error_log("ℹ️ Image not in uploads folder or doesn't exist: " . $product['image_path']);
        }
    }
    
    // ลบข้อมูลในฐานข้อมูล
    $deleteQuery = "DELETE FROM products WHERE id = :id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':id', $productId, PDO::PARAM_INT);
    
    if ($deleteStmt->execute()) {
        $rowsAffected = $deleteStmt->rowCount();
        error_log("✅ Product deleted successfully. Rows affected: $rowsAffected");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Product "' . $product['name'] . '" deleted successfully!',
            'product_id' => $productId
        ]);
    } else {
        error_log("❌ Failed to delete product: " . print_r($deleteStmt->errorInfo(), true));
        throw new Exception('Failed to delete product from database');
    }
    
} catch (Exception $e) {
    error_log("❌ DELETE ERROR: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ]
    ]);
}
?>