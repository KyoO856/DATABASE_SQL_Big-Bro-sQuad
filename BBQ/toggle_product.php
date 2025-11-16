<?php
session_start();
header('Content-Type: application/json');

// Log สำหรับ debug
error_log("=== TOGGLE PRODUCT REQUEST ===");
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
    
    if (!isset($input['active'])) {
        throw new Exception('Active status is missing');
    }
    
    $productId = intval($input['product_id']);
    $active = intval($input['active']);
    
    if ($productId <= 0) {
        throw new Exception('Invalid product ID: ' . $productId);
    }
    
    if ($active !== 0 && $active !== 1) {
        throw new Exception('Invalid active status: must be 0 or 1');
    }
    
    error_log("Toggling product ID: $productId to active=$active");
    
    // ตรวจสอบว่าสินค้ามีอยู่หรือไม่
    $checkQuery = "SELECT id, name, active FROM products WHERE id = :id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $checkStmt->execute();
    $product = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        throw new Exception('Product not found with ID: ' . $productId);
    }
    
    error_log("Found product: " . $product['name'] . " (current active: " . $product['active'] . ")");
    
    // อัพเดทสถานะ
    $updateQuery = "UPDATE products SET active = :active WHERE id = :id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':active', $active, PDO::PARAM_INT);
    $updateStmt->bindParam(':id', $productId, PDO::PARAM_INT);
    
    if ($updateStmt->execute()) {
        $rowsAffected = $updateStmt->rowCount();
        error_log("✅ Product toggled successfully. Rows affected: $rowsAffected");
        
        $statusText = $active ? 'shown' : 'hidden';
        
        echo json_encode([
            'success' => true, 
            'message' => "Product \"{$product['name']}\" has been {$statusText} successfully!",
            'product_id' => $productId,
            'new_status' => $active
        ]);
    } else {
        error_log("❌ Failed to toggle product: " . print_r($updateStmt->errorInfo(), true));
        throw new Exception('Failed to update product status in database');
    }
    
} catch (Exception $e) {
    error_log("❌ TOGGLE ERROR: " . $e->getMessage());
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