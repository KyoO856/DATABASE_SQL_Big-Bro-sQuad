<?php
session_start();

if (!isset($_SESSION['userid'])) {
    die("Please login first");
}

include_once("config/Database.php");

$connectDB = new Database();
$db = $connectDB->getConnection();

$query = "SELECT id, name, image_path FROM products ORDER BY id DESC";
$stmt = $db->query($query);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h1>🔍 Check Product Images</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .product { background: white; padding: 20px; margin: 10px 0; border-radius: 10px; display: flex; gap: 20px; align-items: center; }
    .status-ok { color: green; font-weight: bold; }
    .status-error { color: red; font-weight: bold; }
    img { width: 100px; height: 100px; object-fit: cover; border: 2px solid #ddd; border-radius: 8px; }
    .fix-btn { background: #667eea; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; }
</style>";

$totalProducts = count($products);
$okCount = 0;
$errorCount = 0;

foreach ($products as $product) {
    $imagePath = $product['image_path'];
    $fileExists = !empty($imagePath) && file_exists($imagePath);
    
    echo "<div class='product'>";
    echo "<div>";
    if ($fileExists) {
        echo "<img src='{$imagePath}' alt='{$product['name']}'>";
    } else {
        echo "<img src='https://via.placeholder.com/100/dc3545/ffffff?text=Error' alt='Error'>";
    }
    echo "</div>";
    echo "<div style='flex: 1;'>";
    echo "<strong>#{$product['id']}</strong> - {$product['name']}<br>";
    echo "<small>Path: {$imagePath}</small><br>";
    
    if ($fileExists) {
        echo "<span class='status-ok'>✅ OK - File exists</span>";
        $okCount++;
    } else {
        echo "<span class='status-error'>❌ ERROR - File not found</span>";
        echo "<br><small>Full path: " . realpath('.') . "/{$imagePath}</small>";
        $errorCount++;
    }
    echo "</div>";
    echo "</div>";
}

echo "<div style='background: white; padding: 20px; margin-top: 20px; border-radius: 10px;'>";
echo "<h2>📊 Summary</h2>";
echo "<p><strong>Total Products:</strong> {$totalProducts}</p>";
echo "<p><strong>✅ OK:</strong> {$okCount}</p>";
echo "<p><strong>❌ Errors:</strong> {$errorCount}</p>";
echo "</div>";

if ($errorCount > 0) {
    echo "<div style='background: #fff3cd; padding: 20px; margin-top: 20px; border-radius: 10px; border-left: 4px solid #ffc107;'>";
    echo "<h3>⚠️ Solutions:</h3>";
    echo "<ol>";
    echo "<li><strong>Option 1:</strong> Re-upload images from admin panel (Edit each product)</li>";
    echo "<li><strong>Option 2:</strong> Check if 'uploads/products/' folder exists and has correct permissions (777)</li>";
    echo "<li><strong>Option 3:</strong> If images are external URLs, use <a href='fix_images.php'>fix_images.php</a> to download them</li>";
    echo "</ol>";
    echo "</div>";
}
?>