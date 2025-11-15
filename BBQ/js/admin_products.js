console.log('🚀 Admin Products JS loaded');

// ==================== MODAL MANAGEMENT ====================
function openAddModal() {
    console.log('📝 Opening Add Product Modal');
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('existingImage').value = '';
    
    const preview = document.getElementById('imagePreview');
    const placeholder = document.querySelector('.upload-placeholder');
    
    if (preview) preview.style.display = 'none';
    if (placeholder) placeholder.style.display = 'flex';
    
    document.getElementById('productModal').style.display = 'block';
}

function editProduct(product) {
    console.log('✏️ Editing product:', product);
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productStock').value = product.stock;
    document.getElementById('productCategory').value = product.category;
    document.getElementById('productDescription').value = product.description || '';
    document.getElementById('existingImage').value = product.image_path;
    
    // Show existing image
    const preview = document.getElementById('imagePreview');
    const placeholder = document.querySelector('.upload-placeholder');
    
    if (preview && product.image_path) {
        preview.src = product.image_path;
        preview.style.display = 'block';
    }
    
    if (placeholder) {
        placeholder.style.display = 'none';
    }
    
    document.getElementById('productModal').style.display = 'block';
}

function closeModal() {
    console.log('❌ Closing modal');
    const modal = document.getElementById('productModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ==================== IMAGE UPLOAD ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM Content Loaded');
    
    const imageUploadArea = document.getElementById('imageUploadArea');
    const productImageInput = document.getElementById('productImage');
    const imagePreview = document.getElementById('imagePreview');

    if (!imageUploadArea || !productImageInput || !imagePreview) {
        console.error('❌ Image upload elements not found!');
        return;
    }

    // Click to upload
    imageUploadArea.addEventListener('click', function() {
        console.log('📸 Image upload area clicked');
        productImageInput.click();
    });

    // File selected
    productImageInput.addEventListener('change', function(e) {
        console.log('📁 File selected:', e.target.files[0]);
        if (e.target.files && e.target.files[0]) {
            handleImageSelect(e.target.files[0]);
        }
    });

    // Drag and drop
    imageUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#667eea';
        this.style.background = 'rgba(102, 126, 234, 0.05)';
    });

    imageUploadArea.addEventListener('dragleave', function() {
        this.style.borderColor = '#ddd';
        this.style.background = '#f9f9f9';
    });

    imageUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#ddd';
        this.style.background = '#f9f9f9';
        
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            productImageInput.files = dataTransfer.files;
            handleImageSelect(file);
        } else {
            alert('❌ Please drop an image file');
        }
    });

    function handleImageSelect(file) {
        if (!file) {
            console.log('⚠️ No file provided');
            return;
        }
        
        console.log('🖼️ Processing image:', file.name, file.size, 'bytes');
        
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('❌ File size must be less than 5MB');
            productImageInput.value = '';
            return;
        }
        
        // Check file type
        if (!file.type.startsWith('image/')) {
            alert('❌ Please select an image file');
            productImageInput.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            console.log('✅ Image preview ready');
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
            const placeholder = document.querySelector('.upload-placeholder');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        };
        reader.onerror = function() {
            console.error('❌ Failed to read file');
            alert('❌ Failed to read image file');
        };
        reader.readAsDataURL(file);
    }

    // ==================== FORM SUBMISSION ====================
    const productForm = document.getElementById('productForm');
    if (productForm) {
        productForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('💾 Form submitted');
            
            const formData = new FormData(this);
            const productId = document.getElementById('productId').value;
            
            // ถ้าเป็นการแก้ไขและไม่มีรูปใหม่ ให้ใช้รูปเดิม
            if (productId && !productImageInput.files[0]) {
                formData.delete('image');
                console.log('ℹ️ Using existing image');
            }
            
            // Validate before submit
            const productName = document.getElementById('productName').value.trim();
            const productPrice = document.getElementById('productPrice').value;
            const productCategory = document.getElementById('productCategory').value.trim();
            
            if (!productName) {
                alert('❌ Product name is required');
                return;
            }
            
            if (!productPrice || productPrice <= 0) {
                alert('❌ Valid price is required');
                return;
            }
            
            if (!productCategory) {
                alert('❌ Category is required');
                return;
            }
            
            // Check if new product has image
            if (!productId && !productImageInput.files[0]) {
                alert('❌ Product image is required for new products');
                return;
            }
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            try {
                console.log('📤 Sending data to server...');
                console.log('FormData contents:');
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]));
                }
                
                const response = await fetch('save_product.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('📥 Response received:', response.status, response.statusText);
                
                // อ่าน response เป็น text ก่อน
                const responseText = await response.text();
                console.log('📄 Raw response:', responseText);
                
                // พยายาม parse เป็น JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('❌ JSON Parse Error:', parseError);
                    throw new Error('Server returned invalid JSON. Response: ' + responseText.substring(0, 200));
                }
                
                console.log('📦 Parsed result:', result);
                
                if (result.success) {
                    console.log('✅ Product saved successfully');
                    alert('✅ ' + result.message);
                    
                    // Force reload with cache bypass
                    window.location.href = 'admin_products.php?t=' + new Date().getTime();
                } else {
                    let errorMsg = '❌ Error: ' + result.message;
                    if (result.debug) {
                        errorMsg += '\n\n🔍 Debug info:\nFile: ' + result.debug.file + '\nLine: ' + result.debug.line;
                    }
                    alert(errorMsg);
                    console.error('Server error:', result);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('❌ Caught Error:', error);
                console.error('Error stack:', error.stack);
                alert('❌ An error occurred:\n\n' + error.message + '\n\n🔍 Check console (F12) for details.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
});

// ==================== PRODUCT ACTIONS ====================
async function toggleProduct(productId, currentStatus) {
    const newStatus = currentStatus ? 0 : 1;
    const action = newStatus ? 'show' : 'hide';
    
    console.log(`🔄 Toggle product ${productId}: ${action}`);
    console.log('Current status:', currentStatus, 'New status:', newStatus);
    
    if (!confirm(`Are you sure you want to ${action} this product?`)) {
        return;
    }
    
    try {
        const payload = {
            product_id: productId,
            active: newStatus
        };
        console.log('Sending payload:', payload);
        
        const response = await fetch('toggle_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        });
        
        console.log('Response status:', response.status, response.statusText);
        
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Invalid server response: ' + responseText.substring(0, 200));
        }
        
        console.log('Parsed result:', result);
        
        if (result.success) {
            alert('✅ ' + (result.message || 'Product updated successfully!'));
            window.location.href = 'admin_products.php?t=' + new Date().getTime();
        } else {
            let errorMsg = '❌ Error: ' + result.message;
            if (result.debug) {
                errorMsg += '\n\n🔍 Debug: ' + JSON.stringify(result.debug, null, 2);
            }
            alert(errorMsg);
            console.error('Server error:', result);
        }
    } catch (error) {
        console.error('❌ Caught error:', error);
        console.error('Error stack:', error.stack);
        alert('❌ An error occurred:\n\n' + error.message + '\n\n🔍 Check console (F12) for details.');
    }
}

async function deleteProduct(productId, productName) {
    console.log(`🗑️ Delete product ${productId}: ${productName}`);
    
    if (!confirm(`⚠️ Are you sure you want to DELETE "${productName}"?\n\n🚨 This action cannot be undone!`)) {
        return;
    }
    
    try {
        const response = await fetch('delete_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId
            })
        });
        
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        const result = JSON.parse(responseText);
        console.log('Delete result:', result);
        
        if (result.success) {
            alert('✅ ' + result.message);
            window.location.href = 'admin_products.php?t=' + new Date().getTime();
        } else {
            alert('❌ Error: ' + result.message);
        }
    } catch (error) {
        console.error('❌ Error:', error);
        alert('❌ An error occurred. Please try again.');
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Click outside modal to close
window.addEventListener('click', function(e) {
    const modal = document.getElementById('productModal');
    if (e.target === modal) {
        closeModal();
    }
});

console.log('✅ All event listeners attached');