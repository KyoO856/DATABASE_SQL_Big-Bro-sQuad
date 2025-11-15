console.log('📦 Import Products JS loaded');

let importData = [];

// ==================== CSV/EXCEL UPLOAD ====================
const csvUploadArea = document.getElementById('csvUploadArea');
const csvFileInput = document.getElementById('csvFile');
const csvForm = document.getElementById('csvUploadForm');

if (csvUploadArea && csvFileInput) {
    // Drag and Drop Support
    csvUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        csvUploadArea.style.borderColor = '#667eea';
        csvUploadArea.style.background = '#f0f4ff';
    });

    csvUploadArea.addEventListener('dragleave', () => {
        csvUploadArea.style.borderColor = '#ddd';
        csvUploadArea.style.background = '#f9f9f9';
    });

    csvUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        csvUploadArea.style.borderColor = '#ddd';
        csvUploadArea.style.background = '#f9f9f9';

        const file = e.dataTransfer.files[0];
        if (file && (file.name.endsWith('.csv') || file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))) {
            csvFileInput.files = e.dataTransfer.files;
            document.querySelector('#csvUploadForm .upload-placeholder').innerHTML =
                `<i class="fas fa-file-check" style="font-size: 3rem; color: #28a745;"></i><p style="margin-top: 10px; font-weight: 600;">${file.name}</p><small style="color: #999;">${(file.size / 1024).toFixed(2)} KB</small>`;
            document.querySelector('#csvUploadForm .btn-import').disabled = false;
        } else {
            alert('Please upload a CSV or Excel file');
        }
    });

    csvUploadArea.addEventListener('click', () => csvFileInput.click());

    csvFileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            document.querySelector('#csvUploadForm .upload-placeholder').innerHTML =
                `<i class="fas fa-file-check" style="font-size: 3rem; color: #28a745;"></i><p style="margin-top: 10px; font-weight: 600;">${file.name}</p><small style="color: #999;">${(file.size / 1024).toFixed(2)} KB</small>`;
            document.querySelector('#csvUploadForm .btn-import').disabled = false;
        }
    });
}

csvForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('file', csvFileInput.files[0]);
    formData.append('type', 'csv');

    await processImport('process_import.php', formData);
});

// ==================== JSON UPLOAD ====================
const jsonUploadArea = document.getElementById('jsonUploadArea');
const jsonFileInput = document.getElementById('jsonFile');
const jsonForm = document.getElementById('jsonUploadForm');

if (jsonUploadArea && jsonFileInput) {
    // Drag and Drop Support
    jsonUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        jsonUploadArea.style.borderColor = '#667eea';
        jsonUploadArea.style.background = '#f0f4ff';
    });

    jsonUploadArea.addEventListener('dragleave', () => {
        jsonUploadArea.style.borderColor = '#ddd';
        jsonUploadArea.style.background = '#f9f9f9';
    });

    jsonUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        jsonUploadArea.style.borderColor = '#ddd';
        jsonUploadArea.style.background = '#f9f9f9';

        const file = e.dataTransfer.files[0];
        if (file && file.name.endsWith('.json')) {
            jsonFileInput.files = e.dataTransfer.files;
            document.querySelector('#jsonUploadForm .upload-placeholder').innerHTML =
                `<i class="fas fa-file-check" style="font-size: 3rem; color: #28a745;"></i><p style="margin-top: 10px; font-weight: 600;">${file.name}</p><small style="color: #999;">${(file.size / 1024).toFixed(2)} KB</small>`;
            document.querySelector('#jsonUploadForm .btn-import').disabled = false;
        } else {
            alert('Please upload a JSON file');
        }
    });

    jsonUploadArea.addEventListener('click', () => jsonFileInput.click());

    jsonFileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            document.querySelector('#jsonUploadForm .upload-placeholder').innerHTML =
                `<i class="fas fa-file-check" style="font-size: 3rem; color: #28a745;"></i><p style="margin-top: 10px; font-weight: 600;">${file.name}</p><small style="color: #999;">${(file.size / 1024).toFixed(2)} KB</small>`;
            document.querySelector('#jsonUploadForm .btn-import').disabled = false;
        }
    });
}

jsonForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('file', jsonFileInput.files[0]);
    formData.append('type', 'json');

    await processImport('process_import.php', formData);
});

// ==================== API IMPORT ====================
const apiForm = document.getElementById('apiImportForm');

apiForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const apiUrl = document.getElementById('apiUrl').value;
    const apiKey = document.getElementById('apiKey').value;

    if (!apiUrl) {
        alert('Please enter API URL');
        return;
    }

    const formData = new FormData();
    formData.append('api_url', apiUrl);
    formData.append('api_key', apiKey);
    formData.append('type', 'api');

    await processImport('process_import.php', formData);
});

// ==================== PROCESS IMPORT ====================
async function processImport(endpoint, formData) {
    try {
        console.log('📤 Processing import...');

        // Show loading
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'loadingOverlay';
        loadingOverlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        loadingOverlay.innerHTML = '<div style="background: white; padding: 40px; border-radius: 15px; text-align: center;"><i class="fas fa-spinner fa-spin" style="font-size: 3rem; color: #667eea;"></i><p style="margin-top: 20px; font-size: 18px; font-weight: 600;">Processing your file...</p></div>';
        document.body.appendChild(loadingOverlay);

        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        console.log('📥 Import result:', result);

        // Remove loading
        document.body.removeChild(loadingOverlay);

        if (result.success) {
            importData = result.products;
            showPreview(result.products);
        } else {
            alert('❌ Error: ' + result.message);
        }
    } catch (error) {
        console.error('❌ Import error:', error);

        // Remove loading if exists
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) document.body.removeChild(overlay);

        alert('An error occurred during import: ' + error.message);
    }
}

// ==================== SHOW PREVIEW ====================
function showPreview(products) {
    console.log('👁️ Showing preview for', products.length, 'products');

    document.getElementById('previewCount').textContent =
        `Found ${products.length} products to import`;

    const tbody = document.getElementById('previewTableBody');
    tbody.innerHTML = '';

    products.forEach((product, index) => {
        const row = document.createElement('tr');
        row.style.transition = 'background 0.3s';
        row.addEventListener('mouseenter', () => row.style.background = '#f8f9fa');
        row.addEventListener('mouseleave', () => row.style.background = 'white');

        // ตรวจสอบและแสดงรูปภาพ
        let imageHtml = '';
        const imageUrl = product.image_url || '';
        const isValidUrl = imageUrl && imageUrl.trim() !== '' && !imageUrl.includes('placeholder');

        if (isValidUrl) {
            // มีรูปภาพ - แสดงรูปพร้อม fallback
            imageHtml = `
                <div style="position: relative; width: 60px; height: 60px;">
                    <img src="${imageUrl}" 
                         alt="${product.name}" 
                         data-original="${imageUrl}"
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0; display: block; background: #f0f0f0;"
                         onload="handleImageSuccess(this)"
                         onerror="handleImageError(this, '${product.name}')">
                    <div class="image-loader" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); display: none;">
                        <i class="fas fa-spinner fa-spin" style="color: #667eea;"></i>
                    </div>
                </div>
            `;
        } else {
            // ไม่มีรูปภาพ - แสดง placeholder
            imageHtml = `
                <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 10px; text-align: center; border: 2px solid #667eea;">
                    ${product.name.substring(0, 3).toUpperCase()}
                </div>
            `;
        }

        row.innerHTML = `
            <td style="padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: center; font-weight: 600; color: #667eea;">${index + 1}</td>
            <td style="padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: center;">${imageHtml}</td>
            <td style="padding: 15px; border-bottom: 1px solid #e0e0e0; font-weight: 500;">${product.name || 'N/A'}</td>
            <td style="padding: 15px; border-bottom: 1px solid #e0e0e0; font-weight: 600; color: #28a745;">${parseFloat(product.price || 0).toLocaleString()} ฿</td>
            <td style="padding: 15px; border-bottom: 1px solid #e0e0e0;"><span style="background: #f0f4ff; color: #667eea; padding: 5px 12px; border-radius: 20px; font-size: 13px;">${product.category || 'Uncategorized'}</span></td>
            <td style="padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: center;">${product.stock || 100}</td>
            <td style="padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: center;">
                <span style="color: ${product.active != 0 ? '#28a745' : '#dc3545'}; font-weight: 600;">
                    <i class="fas fa-circle" style="font-size: 8px;"></i> ${product.active != 0 ? 'Active' : 'Inactive'}
                </span>
            </td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('importPreview').style.display = 'block';
    document.getElementById('importPreview').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ฟังก์ชันจัดการเมื่อรูปโหลดสำเร็จ
function handleImageSuccess(img) {
    console.log('✅ Image loaded:', img.src);
    img.style.border = '2px solid #28a745';
    img.style.background = 'transparent';
    const loader = img.parentElement.querySelector('.image-loader');
    if (loader) loader.style.display = 'none';
}

// ฟังก์ชันจัดการเมื่อรูปโหลดไม่สำเร็จ
function handleImageError(img, productName) {
    console.error('❌ Image failed to load:', img.dataset.original);

    // สร้าง placeholder แทน
    const container = img.parentElement;
    container.innerHTML = `
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-direction: column; color: white; border: 2px solid #dc3545;">
            <i class="fas fa-exclamation-triangle" style="font-size: 20px; margin-bottom: 2px;"></i>
            <small style="font-size: 8px;">Error</small>
        </div>
    `;
    container.title = 'Failed to load: ' + img.dataset.original;
}

// ==================== IMAGE MODAL ====================
function showImageModal(imageUrl, productName) {
    const modal = document.createElement('div');
    modal.id = 'imageModal';
    modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); display: flex; align-items: center; justify-content: center; z-index: 10000;';
    modal.innerHTML = `
        <div style="position: relative; max-width: 90%; max-height: 90vh;">
            <img src="${imageUrl}" alt="${productName}" style="max-width: 100%; max-height: 90vh; border-radius: 10px; box-shadow: 0 10px 50px rgba(0,0,0,0.5);">
            <button onclick="closeImageModal()" style="position: absolute; top: 20px; right: 20px; background: white; border: none; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; font-size: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
                <i class="fas fa-times"></i>
            </button>
            <p style="color: white; text-align: center; margin-top: 15px; font-size: 16px; font-weight: 600;">${productName}</p>
        </div>
    `;
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeImageModal();
    });
    document.body.appendChild(modal);
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    if (modal) document.body.removeChild(modal);
}

// ==================== CONFIRM IMPORT ====================
async function confirmImport() {
    if (importData.length === 0) {
        alert('No products to import');
        return;
    }

    if (!confirm(`🚀 Import ${importData.length} products?\n\nThis will add all products to your database.\nAre you sure?`)) {
        return;
    }

    // Hide preview, show progress
    document.getElementById('importPreview').style.display = 'none';
    document.getElementById('importProgress').style.display = 'block';
    document.getElementById('importProgress').scrollIntoView({ behavior: 'smooth' });

    try {
        // Simulate progress
        let currentProgress = 0;
        const progressInterval = setInterval(() => {
            if (currentProgress < 90) {
                currentProgress += Math.random() * 10;
                updateProgress(Math.floor(currentProgress), 100);
            }
        }, 200);

        const response = await fetch('save_bulk_products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ products: importData })
        });

        const result = await response.json();
        console.log('💾 Bulk save result:', result);

        clearInterval(progressInterval);

        if (result.success) {
            updateProgress(100, 100);
            setTimeout(() => {
                alert(`✅ Successfully imported ${result.imported} products!\n\nYou will be redirected to the products page.`);
                window.location.href = 'admin_products.php';
            }, 1000);
        } else {
            alert('❌ Error: ' + result.message);
            document.getElementById('importProgress').style.display = 'none';
            document.getElementById('importPreview').style.display = 'block';
        }
    } catch (error) {
        console.error('❌ Import error:', error);
        alert('An error occurred: ' + error.message);
        document.getElementById('importProgress').style.display = 'none';
        document.getElementById('importPreview').style.display = 'block';
    }
}

function updateProgress(current, total) {
    const percent = Math.min((current / total) * 100, 100);
    document.getElementById('progressFill').style.width = percent + '%';
    document.getElementById('progressText').textContent =
        `${Math.floor(percent)}% complete - Processing ${current} / ${total} products`;
}

function cancelImport() {
    if (confirm('❌ Cancel import?\n\nAll preview data will be lost.')) {
        importData = [];
        document.getElementById('importPreview').style.display = 'none';
        location.reload();
    }
}

// ==================== DOWNLOAD TEMPLATES ====================
function downloadTemplate(type) {
    if (type === 'csv') {
        const csv = 'name,price,category,description,stock,image_url,active\n' +
            '"Nike Air Max 2024",2500,"Running Shoes","Premium running shoes with air cushioning",100,"https://via.placeholder.com/300/667eea/ffffff?text=Nike+Air+Max",1\n' +
            '"Adidas Ultraboost 2024",3200,"Running Shoes","Energy-returning running shoes",75,"https://via.placeholder.com/300/667eea/ffffff?text=Adidas+Ultraboost",1\n' +
            '"Basketball Pro",1800,"Basketball","Professional basketball",50,"https://via.placeholder.com/300/667eea/ffffff?text=Basketball",1\n' +
            '"Yoga Mat Premium",890,"Fitness Equipment","Non-slip yoga mat with carrying strap",200,"https://via.placeholder.com/300/667eea/ffffff?text=Yoga+Mat",1\n' +
            '"Dumbbell Set 20kg",2400,"Fitness Equipment","Adjustable dumbbell set",30,"https://via.placeholder.com/300/667eea/ffffff?text=Dumbbell",1';

        downloadFile(csv, 'product_template.csv', 'text/csv');
        console.log('📥 CSV template downloaded');
    } else if (type === 'json') {
        const json = JSON.stringify([
            {
                name: "Nike Air Max 2024",
                price: 2500,
                category: "Running Shoes",
                description: "Premium running shoes with air cushioning",
                stock: 100,
                image_url: "https://via.placeholder.com/300/667eea/ffffff?text=Nike+Air+Max",
                active: 1
            },
            {
                name: "Adidas Ultraboost 2024",
                price: 3200,
                category: "Running Shoes",
                description: "Energy-returning running shoes",
                stock: 75,
                image_url: "https://via.placeholder.com/300/667eea/ffffff?text=Adidas+Ultraboost",
                active: 1
            },
            {
                name: "Basketball Pro",
                price: 1800,
                category: "Basketball",
                description: "Professional basketball",
                stock: 50,
                image_url: "https://via.placeholder.com/300/667eea/ffffff?text=Basketball",
                active: 1
            },
            {
                name: "Yoga Mat Premium",
                price: 890,
                category: "Fitness Equipment",
                description: "Non-slip yoga mat with carrying strap",
                stock: 200,
                image_url: "https://via.placeholder.com/300/667eea/ffffff?text=Yoga+Mat",
                active: 1
            },
            {
                name: "Dumbbell Set 20kg",
                price: 2400,
                category: "Fitness Equipment",
                description: "Adjustable dumbbell set",
                stock: 30,
                image_url: "https://via.placeholder.com/300/667eea/ffffff?text=Dumbbell",
                active: 1
            }
        ], null, 2);

        downloadFile(json, 'product_template.json', 'application/json');
        console.log('📥 JSON template downloaded');
    }
}

function downloadFile(content, filename, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    // Show success message
    const toast = document.createElement('div');
    toast.style.cssText = 'position: fixed; bottom: 30px; right: 30px; background: #28a745; color: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); z-index: 9999; font-weight: 600;';
    toast.innerHTML = `<i class="fas fa-check-circle"></i> Template downloaded: ${filename}`;
    document.body.appendChild(toast);
    setTimeout(() => document.body.removeChild(toast), 3000);
}

// ==================== JSON EXAMPLE MODAL ====================
function showJsonExample() {
    document.getElementById('jsonExampleModal').style.display = 'flex';
}

function closeJsonExample() {
    document.getElementById('jsonExampleModal').style.display = 'none';
}

// Close modal on ESC key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeJsonExample();
        closeImageModal();
    }
});

// Log ข้อมูลรูปภาพเพื่อ debug
console.log('=== Image Debug Info ===');
window.addEventListener('load', () => {
    const images = document.querySelectorAll('#previewTableBody img');
    console.log('Total images in preview:', images.length);
    images.forEach((img, i) => {
        console.log(`Image ${i + 1}:`, img.src);
    });
});

console.log('✅ Import Products initialized successfully');
console.log('📊 Ready to import products from CSV, JSON, or API');