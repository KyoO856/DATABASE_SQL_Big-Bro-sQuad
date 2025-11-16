<?php
header('Content-Type: application/json');

include_once("config/Database.php");

try {
    $connectDB = new Database();
    $db = $connectDB->getConnection();
    
    // Query ดึงข้อมูลสินค้าทั้งหมด
    $query = "SELECT id, name, price, description, image_path, category FROM products WHERE active = 1 ORDER BY id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // แปลงข้อมูลให้ตรงกับ format เดิม
    $formattedProducts = [];
    foreach ($products as $product) {
        $formattedProducts[] = [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'description' => $product['description'],
            'img' => $product['image_path'], // path รูปภาพ
            'type' => $product['category']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading products: ' . $e->getMessage()
    ]);
}
?>
