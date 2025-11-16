<?php
session_start();

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['userid'])) {
    header("Location: log in.php");
    exit;
}

include_once("config/Database.php");
include_once("class/UserLogin.php");

$connectDB = new Database();
$db = $connectDB->getConnection();

$user = new UserLogin($db);
$userData = $user->userData($_SESSION['userid']);

if (!$userData) {
    header("Location: log in.php");
    exit;
}

// ดึงข้อมูลสินค้าทั้งหมด
$query = "SELECT * FROM products ORDER BY id DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Management - BBQ</title>
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/CSS home.css">
    <link rel="stylesheet" type="text/css" href="css/CSS admin_products.css">
</head>
<body>
    <?php include_once("nav.php"); ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-box"></i> Product Management</h1>
            <button class="btn-add-product" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New Product
            </button>
        </div>

        <a href="home.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>

        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card <?php echo $product['active'] ? '' : 'inactive'; ?>">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php if (!$product['active']): ?>
                            <div class="inactive-badge">Inactive</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="price"><?php echo number_format($product['price'], 0); ?> ฿</p>
                        <p class="category"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['category']); ?></p>
                        <p class="stock"><i class="fas fa-boxes"></i> Stock: <?php echo $product['stock']; ?></p>
                        <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
                    </div>
                    <div class="product-actions">
                        <button class="btn-edit" onclick='editProduct(<?php echo json_encode($product); ?>)'>
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-toggle" onclick="toggleProduct(<?php echo $product['id']; ?>, <?php echo $product['active']; ?>)">
                            <i class="fas fa-<?php echo $product['active'] ? 'eye-slash' : 'eye'; ?>"></i>
                            <?php echo $product['active'] ? 'Hide' : 'Show'; ?>
                        </button>
                        <button class="btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal สำหรับเพิ่ม/แก้ไขสินค้า -->
    <div id="productModal" class="modal" style="display: none;">
        <div class="modal-bg" onclick="closeModal()"></div>
        <div class="modal-page">
            <h2 id="modalTitle">Add New Product</h2>
            <form id="productForm" enctype="multipart/form-data">
                <input type="hidden" id="productId" name="product_id">
                <input type="hidden" id="existingImage" name="existing_image">
                
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Product Name *</label>
                    <input type="text" id="productName" name="name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Price (THB) *</label>
                        <input type="number" id="productPrice" name="price" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-boxes"></i> Stock</label>
                        <input type="number" id="productStock" name="stock" value="100" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-list"></i> Category *</label>
                    <input type="text" id="productCategory" name="category" required 
                           placeholder="e.g., shirt, Hoodie, Sweatpants">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Description</label>
                    <textarea id="productDescription" name="description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-image"></i> Product Image *</label>
                    <div class="image-upload-area" id="imageUploadArea">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload or drag and drop</p>
                            <small>PNG, JPG, GIF up to 5MB</small>
                        </div>
                        <img id="imagePreview" style="display: none;">
                    </div>
                    <input type="file" id="productImage" name="image" accept="image/*" style="display: none;">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/admin_products.js"></script>
    <script src="js/admin_products_search.js"></script>
</body>
</html>