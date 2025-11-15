<?php
// http://localhost/BBQ/import_products.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// ตรวจสอบว่า login แล้วหรือยัง
if (!isset($_SESSION['userid'])) {
    die("Please login first. <a href='log in.php'>Login here</a>");
}

// ตรวจสอบไฟล์ที่จำเป็น
if (!file_exists("config/Database.php")) {
    die("Error: config/Database.php not found!");
}

if (!file_exists("class/UserLogin.php")) {
    die("Error: class/UserLogin.php not found!");
}

include_once("config/Database.php");
include_once("class/UserLogin.php");

try {
    $connectDB = new Database();
    $db = $connectDB->getConnection();

    $user = new UserLogin($db);
    $userData = $user->userData($_SESSION['userid']);

    if (!$userData) {
        die("User data not found. <a href='log in.php'>Login again</a>");
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Products - BBQ</title>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Files -->
    <link rel="stylesheet" type="text/css" href="css/CSS home.css">
    <link rel="stylesheet" type="text/css" href="css/CSS import_products.css">
    
</head>
<body>
    <?php 
    if (file_exists("nav.php")) {
        include_once("nav.php"); 
    } else {
        echo "<p style='color: red;'>Warning: nav.php not found!</p>";
    }
    ?>

    <div class="import-container">
        <div class="import-header">
            <h1><i class="fas fa-file-import"></i> Import Products</h1>
            <p>Bulk import products from CSV, JSON, or API</p>
        </div>

        <a href="admin_products.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>

        <!-- Import Methods -->
        <div class="import-methods">
            
            <!-- CSV Upload -->
            <div class="import-card">
                <div class="card-icon"><i class="fas fa-file-csv" style="font-size: 3rem; color: #667eea;"></i></div>
                <h2>CSV / Excel File</h2>
                <p>Upload a CSV or Excel file with your products</p>
                
                <form id="csvUploadForm">
                    <div class="file-upload-area" id="csvUploadArea" style="border: 3px dashed #ddd; border-radius: 12px; padding: 40px 20px; text-align: center; cursor: pointer; background: #f9f9f9; margin: 20px 0;">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #667eea;"></i>
                            <p>Click to upload or drag and drop</p>
                            <small>CSV, XLSX files</small>
                        </div>
                    </div>
                    <input type="file" id="csvFile" accept=".csv,.xlsx,.xls" style="display: none;">
                    <button type="submit" class="btn-import" disabled style="width: 100%; padding: 12px 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-upload"></i> Upload & Preview
                    </button>
                </form>
                
                <div class="help-section" style="margin-top: 15px; text-align: center;">
                    <button class="btn-help" onclick="downloadTemplate('csv')" style="padding: 8px 15px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 5px; cursor: pointer; margin: 5px;">
                        <i class="fas fa-download"></i> Download CSV Template
                    </button>
                    <small style="display: block; color: #999; margin-top: 10px;">Required columns: name, price, category</small>
                </div>
            </div>

            <!-- JSON Upload -->
            <div class="import-card">
                <div class="card-icon"><i class="fas fa-file-code" style="font-size: 3rem; color: #667eea;"></i></div>
                <h2>JSON File</h2>
                <p>Upload a JSON file with product data</p>
                
                <form id="jsonUploadForm">
                    <div class="file-upload-area" id="jsonUploadArea" style="border: 3px dashed #ddd; border-radius: 12px; padding: 40px 20px; text-align: center; cursor: pointer; background: #f9f9f9; margin: 20px 0;">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: #667eea;"></i>
                            <p>Click to upload or drag and drop</p>
                            <small>JSON files only</small>
                        </div>
                    </div>
                    <input type="file" id="jsonFile" accept=".json" style="display: none;">
                    <button type="submit" class="btn-import" disabled style="width: 100%; padding: 12px 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-upload"></i> Upload & Preview
                    </button>
                </form>
                
                <div class="help-section" style="margin-top: 15px; text-align: center;">
                    <button class="btn-help" onclick="downloadTemplate('json')" style="padding: 8px 15px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 5px; cursor: pointer; margin: 5px;">
                        <i class="fas fa-download"></i> Download JSON Template
                    </button>
                    <button class="btn-help" onclick="showJsonExample()" style="padding: 8px 15px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 5px; cursor: pointer; margin: 5px;">
                        <i class="fas fa-code"></i> View Example
                    </button>
                </div>
            </div>

            <!-- API Import -->
            <div class="import-card">
                <div class="card-icon"><i class="fas fa-cloud" style="font-size: 3rem; color: #667eea;"></i></div>
                <h2>API Import</h2>
                <p>Import products from external API</p>
                
                <form id="apiImportForm">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; color: #555; font-weight: 600; margin-bottom: 8px;"><i class="fas fa-link"></i> API Endpoint URL</label>
                        <input type="url" id="apiUrl" placeholder="https://api.example.com/products" required style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; color: #555; font-weight: 600; margin-bottom: 8px;"><i class="fas fa-key"></i> API Key (Optional)</label>
                        <input type="text" id="apiKey" placeholder="Your API key" style="width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px;">
                    </div>
                    
                    <button type="submit" class="btn-import" style="width: 100%; padding: 12px 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-sync"></i> Fetch from API
                    </button>
                </form>
                
                <div class="help-section" style="margin-top: 15px; text-align: center;">
                    <small style="color: #999;">API must return JSON array of products</small>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div id="importPreview" style="display: none; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1); margin-top: 30px;">
            <div class="preview-header" style="margin-bottom: 25px;">
                <h2><i class="fas fa-eye"></i> Preview Import Data</h2>
                <p id="previewCount">0 products found</p>
            </div>
            
            <div class="preview-table-container" style="overflow-x: auto; margin-bottom: 25px;">
                <table id="previewTable" style="width: 100%; border-collapse: collapse;">
                    <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <tr>
                            <th style="padding: 15px; text-align: left;">#</th>
                            <th style="padding: 15px; text-align: left;">Image</th>
                            <th style="padding: 15px; text-align: left;">Name</th>
                            <th style="padding: 15px; text-align: left;">Price</th>
                            <th style="padding: 15px; text-align: left;">Category</th>
                            <th style="padding: 15px; text-align: left;">Stock</th>
                            <th style="padding: 15px; text-align: left;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="previewTableBody">
                        <!-- Data will be loaded by JavaScript -->
                    </tbody>
                </table>
            </div>
            
            <div class="preview-actions" style="display: flex; gap: 15px; justify-content: center;">
                <button class="btn-cancel" onclick="cancelImport()" style="padding: 12px 25px; background: #6c757d; color: white; border: none; border-radius: 8px; cursor: pointer;">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-confirm" onclick="confirmImport()" style="padding: 12px 25px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 8px; cursor: pointer;">
                    <i class="fas fa-check"></i> Confirm Import
                </button>
            </div>
        </div>

        <!-- Progress Section -->
        <div id="importProgress" style="display: none; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1); text-align: center; margin-top: 30px;">
            <div class="progress-container">
                <h3><i class="fas fa-spinner fa-spin"></i> Importing Products...</h3>
                <div class="progress-bar" style="width: 100%; height: 30px; background: #e9ecef; border-radius: 15px; overflow: hidden; margin: 20px 0;">
                    <div class="progress-fill" id="progressFill" style="width: 0%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); transition: width 0.3s;"></div>
                </div>
                <p id="progressText">0 / 0 products imported</p>
            </div>
        </div>
    </div>

    <!-- JSON Example Modal -->
    <div id="jsonExampleModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
        <div class="modal-bg" onclick="closeJsonExample()" style="position: absolute; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7);"></div>
        <div class="modal-page" style="position: relative; background: white; padding: 40px; border-radius: 20px; max-width: 700px; width: 90%; max-height: 85vh; overflow-y: auto; z-index: 2001;">
            <h2><i class="fas fa-code"></i> JSON Format Example</h2>
            <pre style="background: #f8f9fa; padding: 20px; border-radius: 8px; overflow-x: auto;"><code>[
  {
    "name": "Product Name",
    "price": 500,
    "category": "Category",
    "description": "Product description here",
    "stock": 100,
    "image_url": "https://example.com/image.jpg",
    "active": 1
  },
  {
    "name": "Another Product",
    "price": 800,
    "category": "Another Category",
    "description": "Another description",
    "stock": 50,
    "image_url": "https://example.com/image2.jpg",
    "active": 1
  }
]</code></pre>
            <button class="btn-close" onclick="closeJsonExample()" style="width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; margin-top: 20px;">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        console.log('✅ Page loaded successfully');
        console.log('jQuery:', typeof jQuery !== 'undefined' ? 'Loaded' : 'NOT LOADED');
    </script>
    
    <?php if (file_exists("js/import_products.js")): ?>
        <script src="js/import_products.js"></script>
    <?php else: ?>
        <script>
            console.error('❌ js/import_products.js not found!');
            alert('Warning: import_products.js not found. Some features may not work.');
        </script>
    <?php endif; ?>
</body>
</html>