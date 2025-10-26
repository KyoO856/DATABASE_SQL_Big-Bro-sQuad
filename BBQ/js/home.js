// ==================== DATA ====================
const PRODUCTS = Object.freeze([{
    id: 1,
    img: 'img/BTS.png',
    name: 'BBQ T-shirt',
    price: 500,
    description: 'BBQ T-shirt high quality cotton material',
    type: 'shirt'
}, {
    id: 2,
    img: 'img/hoodie.png',
    name: 'BBQ Hoodie',
    price: 800,
    description: 'Comfortable hoodie for gym',
    type: 'Hoodie'
}, {
    id: 3,
    img: 'img/sp2.png',
    name: 'BBQ Sweatpants',
    price: 600,
    description: 'Premium sweatpants',
    type: 'Sweatpants'
}, {
    id: 4,
    img: 'img/cs.png',
    name: 'BBQ Compression',
    price: 450,
    description: 'Compression shirt for workout',
    type: 'shirt'
}, {
    id: 5,
    img: 'img/whry.png',
    name: 'BBQ Whey',
    price: 1200,
    description: 'Whey protein 100%',
    type: 'Whey Protein'
}]);

// ==================== CART MANAGER ====================
const CartManager = {
    STORAGE_KEY: 'bbq_cart',

    // Get cart from localStorage (always fresh)
    getCart() {
        try {
            const saved = localStorage.getItem(this.STORAGE_KEY);
            if (saved && saved !== 'undefined' && saved !== 'null') {
                const parsed = JSON.parse(saved);
                if (Array.isArray(parsed)) {
                    return parsed.filter(item => 
                        item.id && item.name && item.price && item.quantity > 0
                    );
                }
            }
        } catch (error) {
            console.error('❌ Error reading cart:', error);
        }
        return [];
    },

    // Save cart to localStorage
    saveCart(cart) {
        try {
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(cart));
            console.log('✓ Cart saved:', cart);
            return true;
        } catch (error) {
            console.error('❌ Error saving cart:', error);
            return false;
        }
    },

    // Add item to cart
    addItem(productId) {
        const id = Number(productId);
        
        console.log('\n🛒 === ADD ITEM START ===');
        console.log('   Adding product ID:', id);
        
        // Get fresh cart from localStorage
        let cart = this.getCart();
        console.log('   Current cart from storage:', JSON.stringify(cart));
        
        // Find if product already exists
        const existingIndex = cart.findIndex(item => Number(item.id) === id);
        
        if (existingIndex >= 0) {
            // Product exists, increase quantity
            cart[existingIndex].quantity++;
            console.log(`   ✓ Updated existing: ${cart[existingIndex].name} x${cart[existingIndex].quantity}`);
        } else {
            // New product, add to cart
            const product = PRODUCTS.find(p => p.id === id);
            if (!product) {
                console.error('   ❌ Product not found with ID:', id);
                return false;
            }

            const newItem = {
                id: product.id,
                name: product.name,
                price: product.price,
                img: product.img,
                quantity: 1
            };
            
            cart.push(newItem);
            console.log(`   ✓ Added new item: ${product.name}`);
        }

        console.log('   New cart to save:', JSON.stringify(cart));
        
        // Save back to localStorage
        this.saveCart(cart);
        
        // Verify it was saved
        const verify = this.getCart();
        console.log('   Verification - cart now has:', verify.length, 'items');
        console.log('🛒 === ADD ITEM END ===\n');
        
        return true;
    },

    // Update quantity
    updateQuantity(productId, delta) {
        const id = Number(productId);
        let cart = this.getCart();
        
        const item = cart.find(item => Number(item.id) === id);
        if (!item) {
            console.error('❌ Item not found in cart:', id);
            return false;
        }

        item.quantity += delta;

        if (item.quantity <= 0) {
            // Remove item if quantity is 0
            cart = cart.filter(item => Number(item.id) !== id);
            console.log('✓ Item removed (quantity = 0)');
        } else {
            console.log(`✓ Updated quantity: ${item.name} → ${item.quantity}`);
        }

        this.saveCart(cart);
        return true;
    },

    // Remove item from cart
    removeItem(productId) {
        const id = Number(productId);
        let cart = this.getCart();
        
        const initialLength = cart.length;
        cart = cart.filter(item => Number(item.id) !== id);

        if (cart.length < initialLength) {
            console.log('✓ Item removed from cart');
            this.saveCart(cart);
            return true;
        }
        return false;
    },

    // Get total items count
    getTotalItems() {
        const cart = this.getCart();
        return cart.reduce((sum, item) => sum + item.quantity, 0);
    },

    // Get total price
    getTotalPrice() {
        const cart = this.getCart();
        return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },

    // Clear cart
    clear() {
        this.saveCart([]);
        console.log('✓ Cart cleared');
    },

    // Get cart items
    getItems() {
        return this.getCart();
    }
};

// ==================== UI MANAGER ====================
const UIManager = {
    // Store current product ID being viewed
    currentProductId: null,

    // Render all products
    renderProducts() {
        const html = PRODUCTS.map(product => `
            <div class="product-items ${product.type}" data-id="${product.id}">
                <img class="product-img" src="${product.img}" alt="${product.name}">
                <p style="font-size: 1.2vw;">${product.name}</p>
                <p style="font-size: 1.2vw;">${product.price.toLocaleString()} THB</p>
            </div>
        `).join('');

        $('#productlist').html(html);
    },

    // Show product detail modal
    showProductModal(productId) {
        const id = Number(productId);
        const product = PRODUCTS.find(p => p.id === id);
        
        if (!product) {
            console.error('❌ Product not found:', productId);
            return;
        }

        // Store the current product ID
        this.currentProductId = id;

        console.log('🔍 Opening modal for:', product.name, '(ID:', id, ')');

        const modal = $('.modal').first();
        modal.find('.modaldesc-img').attr('src', product.img);
        modal.find('.modaldesc-detail p').eq(0).text(product.name);
        modal.find('.modaldesc-detail p').eq(1).text(`${product.price.toLocaleString()} THB`);
        modal.find('.modaldesc-detail p').eq(2).text(product.description);
        
        // CRITICAL FIX: Set data attribute on the button
        modal.find('.btn-buy').attr('data-product-id', id);
        
        modal.fadeIn(200);
    },

    // Show cart modal
    showCartModal() {
        const modal = $('.modal').last();
        const items = CartManager.getItems();
        
        console.log('🛒 Showing cart modal with', items.length, 'items:', items);
        
        if (items.length === 0) {
            const emptyHtml = '<p style="text-align: center; color: gray; padding: 40px; font-size: 1.2vw;">🛒 Your cart is empty</p>';
            modal.find('.cartlist').html(emptyHtml);
            modal.find('.btn-Buy').prop('disabled', true);
        } else {
            const cartHtml = this.generateCartHTML(items);
            modal.find('.cartlist').html(cartHtml);
            modal.find('.btn-Buy').prop('disabled', false);
        }

        modal.fadeIn(200);
    },

    // Generate cart HTML
    generateCartHTML(items) {
        const itemsHtml = items.map(item => {
            const subtotal = item.price * item.quantity;
            return `
                <div class="cart-item" data-id="${item.id}">
                    <img src="${item.img}" alt="${item.name}" class="cart-img">
                    <div class="cart-detail">
                        <p class="cart-name">${item.name}</p>
                        <p class="cart-price">${item.price.toLocaleString()} THB × ${item.quantity}</p>
                        <p class="cart-subtotal">Subtotal: <b>${subtotal.toLocaleString()} THB</b></p>
                        <div class="cart-quantity-control">
                            <button class="btnc btn-decrease" data-product-id="${item.id}">−</button>
                            <span class="quantity-display">${item.quantity}</span>
                            <button class="btnc btn-increase" data-product-id="${item.id}">+</button>
                        </div>
                    </div>
                    <button class="btnc btn-remove" data-product-id="${item.id}">×</button>
                </div>
            `;
        }).join('');

        const total = CartManager.getTotalPrice();
        const totalHtml = `
            <div style="border-top: 2px solid #000; padding-top: 15px; margin-top: 15px;">
                <p style="font-size: 1.5vw; font-weight: bold; text-align: right;">
                    Total: ${total.toLocaleString()} THB
                </p>
            </div>
        `;

        return itemsHtml + totalHtml;
    },

    // Update cart badge
    updateCartBadge() {
        const count = CartManager.getTotalItems();
        $('.cartcount').text(count);
        console.log('🔢 Cart badge updated to:', count);
        
        // Add animation
        $('.cartcount').addClass('bounce');
        setTimeout(() => $('.cartcount').removeClass('bounce'), 300);
    },

    // Show toast notification
    showToast(message, type = 'success') {
        const bgColor = type === 'success' ? '#28a745' : '#dc3545';
        const toast = $('<div>')
            .text(message)
            .css({
                position: 'fixed',
                bottom: '20px',
                right: '20px',
                background: bgColor,
                color: '#fff',
                padding: '15px 25px',
                borderRadius: '8px',
                boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
                zIndex: 9999,
                fontSize: '14px',
                fontWeight: '500',
                opacity: 0,
                transform: 'translateY(20px)',
                transition: 'all 0.3s ease'
            });

        $('body').append(toast);
        
        setTimeout(() => {
            toast.css({ opacity: 1, transform: 'translateY(0)' });
        }, 10);

        setTimeout(() => {
            toast.css({ opacity: 0, transform: 'translateY(-20px)' });
            setTimeout(() => toast.remove(), 300);
        }, 2700);
    },

    // Close modal and reset state
    closeModal(modalElement) {
        $(modalElement).fadeOut(200);
        // Reset current product ID when closing modal
        this.currentProductId = null;
    }
};


// ==================== CHECKOUT ====================
const Checkout = {
    async process() {
        const items = CartManager.getItems();
        
        if (items.length === 0) {
            UIManager.showToast('Cart is empty!', 'error');
            return;
        }

        const total = CartManager.getTotalPrice();
        const itemsList = items
            .map(item => `• ${item.name} ×${item.quantity} = ${(item.price * item.quantity).toLocaleString()} THB`)
            .join('\n');
        
        const confirmMsg = `Order Summary:\n\n${itemsList}\n\n━━━━━━━━━━━━━━━━━━━━\nTotal: ${total.toLocaleString()} THB\n\nConfirm purchase?`;
        
        if (confirm(confirmMsg)) {
 
            // Save order to database
            const saved = await this.saveToDatabase(items, total);
            
            if (saved) {
                this.success(total, saved.order_id);
            } else {
                UIManager.showToast('Failed to save order. Please try again.', 'error');
            }
        } else {
            console.log('Checkout cancelled');
        }
    },

    async saveToDatabase(items, total) {
        try {
            console.log('💾 Attempting to save order:', { items, total });
            
            const response = await fetch('save_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    items: items,
                    total: total
                })
            });

            console.log('📡 Response status:', response.status);
            
            // Check if response is ok
            if (!response.ok) {
                console.error('❌ HTTP error:', response.status, response.statusText);
                return false;
            }

            const result = await response.json();
            console.log('📦 Server response:', result);
            
            if (result.success) {
                console.log('✅ Order saved to database. Order ID:', result.order_id);
                return result;
            } else {
                console.error('❌ Failed to save order:', result.message);
                if (result.debug) {
                    console.error('Debug info:', result.debug);
                }
                if (result.error) {
                    console.error('Error:', result.error);
                }
                alert('Error saving order: ' + result.message);
                return false;
            }
        } catch (error) {
            console.error('❌ Error saving order:', error);
            alert('Network error: Unable to save order. Please check your connection.');
            return false;
        }
    },

    success(total, orderId) {
        alert(`✔ Payment Successful!\n\nOrder ID: #${orderId}\nAmount paid: ${total.toLocaleString()} THB\n\nThank you for shopping with us!`);
        
        CartManager.clear();
        UIManager.updateCartBadge();
        $('.modal').last().fadeOut(200);
        
        console.log('✅ Checkout completed - Order ID:', orderId);
    }
};

// ==================== EVENT HANDLERS ====================
$(document).ready(() => {
    // Initialize
    UIManager.updateCartBadge();
    UIManager.renderProducts();

    console.log('✅ Page ready - Cart has:', CartManager.getTotalItems(), 'items');

    // Click on product -> open modal
    $(document).on('click', '.product-items', function() {
        const productId = $(this).data('id');
        UIManager.showProductModal(productId);
    });

    // Click on background -> close modal
    $(document).on('click', '.modal-bg', function() {
        UIManager.closeModal($(this).closest('.modal'));
    });

    // Close button in product modal
    $(document).on('click', '.btn-close', function() {
        UIManager.closeModal($(this).closest('.modal'));
    });

    // Cancel button in cart modal
    $(document).on('click', '.btn-cancel', function() {
        UIManager.closeModal($(this).closest('.modal'));
    });

    // Add to cart button - WITH AUTO REFRESH
    $(document).on('click', '.btn-buy', function(e) {
        e.stopPropagation();
        
        // Get product ID from the button's data attribute
        const productId = $(this).data('product-id');
        
        console.log('\n>>> ADD TO CART BUTTON CLICKED <<<');
        console.log('Product ID from button:', productId, 'Type:', typeof productId);
        
        // Use the product ID from button
        const idToAdd = Number(productId);
        
        if (!idToAdd || isNaN(idToAdd)) {
            return;
        }
        
        if (CartManager.addItem(idToAdd)) {
            // Show quick feedback before refresh
            UIManager.showToast('✓ Added to cart!');
            
            // Refresh the page after a brief delay
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            UIManager.showToast('Failed to add item', 'error');
        }
    });

    // Open cart modal
    $('.nav-profile-cart').click(function() {
        UIManager.showCartModal();
    });

    // Increase quantity in cart
    $(document).on('click', '.btn-increase', function(e) {
        e.stopPropagation();
        const productId = $(this).data('product-id');
        
        if (CartManager.updateQuantity(productId, 1)) {
            UIManager.updateCartBadge();
            UIManager.showCartModal();
        }
    });

    // Decrease quantity in cart
    $(document).on('click', '.btn-decrease', function(e) {
        e.stopPropagation();
        const productId = $(this).data('product-id');
        
        if (CartManager.updateQuantity(productId, -1)) {
            UIManager.updateCartBadge();
            UIManager.showCartModal();
        }
    });

    // Remove item button
    $(document).on('click', '.btn-remove', function(e) {
        e.stopPropagation();
        const productId = $(this).data('product-id');
        
        if (confirm('Remove this item from cart?')) {
            if (CartManager.removeItem(productId)) {
                UIManager.updateCartBadge();
                UIManager.showCartModal();
                UIManager.showToast('Item removed');
            }
        }
    });

    // Checkout button
    $(document).on('click', '.btn-checkout, .btn-Buy', function(e) {
        e.stopPropagation();
        Checkout.process();
    });

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.modal:visible').fadeOut(200);
            UIManager.currentProductId = null;
        }
    });
});

// ==================== UTILITY FUNCTIONS ====================
function clearCart() {
    if (confirm('Clear all items from cart?')) {
        CartManager.clear();
        UIManager.updateCartBadge();
        UIManager.showToast('Cart cleared');
        console.log('✓ Cart cleared manually');
    }
}

function debugCart() {
    console.log('=== CART DEBUG ===');
    console.log('Cart items:', CartManager.getItems());
    console.log('Total items:', CartManager.getTotalItems());
    console.log('Total price:', CartManager.getTotalPrice(), 'THB');
    console.log('localStorage raw:', localStorage.getItem(CartManager.STORAGE_KEY));
    console.log('Current product viewing:', UIManager.currentProductId);
}

// Export for console use
window.BBQShop = {
    CartManager,
    UIManager,
    clearCart,
    debugCart
};