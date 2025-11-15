<?php
session_start();

if (!isset($_SESSION['userid'])) {
    die("Please login first");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fix Product Images</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/fix_images.css">
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-tools"></i> Fix Product Images</h1>
        <p>Download external images to your server</p>
        
        <div class="info">
            <h3><i class="fas fa-info-circle"></i> What this does:</h3>
            <ul>
                <li>✓ Downloads all product images from external URLs</li>
                <li>✓ Saves them to your server (uploads/products/)</li>
                <li>✓ Updates database with local paths</li>
                <li>✓ Creates placeholders for failed downloads</li>
                <li>✓ Makes your images load faster and more reliably</li>
            </ul>
        </div>
        
        <button class="btn btn-primary" onclick="startFix()">
            <i class="fas fa-download"></i> Start Downloading Images
        </button>
        
        <div id="progress">
            <h3><i class="fas fa-spinner fa-spin"></i> Processing...</h3>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%;">0%</div>
            </div>
            <p id="progressText">Preparing...</p>
        </div>
        
        <div id="result"></div>
        
        <br><br>
        <a href="admin_products.php" style="color: #667eea; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
    
    <script>
        async function startFix() {
            const progressDiv = document.getElementById('progress');
            const resultDiv = document.getElementById('result');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            
            progressDiv.style.display = 'block';
            resultDiv.style.display = 'none';
            
            try {
                // Simulate progress
                let progress = 0;
                const interval = setInterval(() => {
                    if (progress < 90) {
                        progress += Math.random() * 10;
                        progressFill.style.width = progress + '%';
                        progressFill.textContent = Math.floor(progress) + '%';
                    }
                }, 300);
                
                // Call API
                const response = await fetch('download_images.php', {
                    method: 'POST'
                });
                
                const result = await response.json();
                
                clearInterval(interval);
                progressFill.style.width = '100%';
                progressFill.textContent = '100%';
                
                // Show result
                setTimeout(() => {
                    progressDiv.style.display = 'none';
                    resultDiv.style.display = 'block';
                    
                    if (result.success) {
                        resultDiv.className = 'success';
                        resultDiv.innerHTML = `
                            <h3><i class="fas fa-check-circle"></i> Success!</h3>
                            <p><strong>Total:</strong> ${result.total} products</p>
                            <p><strong>Downloaded:</strong> ${result.downloaded} images</p>
                            <p><strong>Failed:</strong> ${result.failed} images (placeholders created)</p>
                            <p><strong>Skipped:</strong> ${result.skipped} (already local)</p>
                            <br>
                            <button class="btn btn-primary" onclick="location.href='admin_products.php'">
                                <i class="fas fa-eye"></i> View Products
                            </button>
                        `;
                    } else {
                        resultDiv.className = 'warning';
                        resultDiv.innerHTML = `
                            <h3><i class="fas fa-exclamation-triangle"></i> Error</h3>
                            <p>${result.message}</p>
                        `;
                    }
                }, 500);
                
            } catch (error) {
                progressDiv.style.display = 'none';
                resultDiv.style.display = 'block';
                resultDiv.className = 'warning';
                resultDiv.innerHTML = `
                    <h3><i class="fas fa-exclamation-triangle"></i> Error</h3>
                    <p>${error.message}</p>
                `;
            }
        }
    </script>
</body>
</html>