console.log('🔍 Search & Filter module loaded');

// Image error handling
function handleImageError(img, productName) {
    console.log('❌ Image failed to load:', img.src);
    img.classList.add('error');
    img.src = 'https://via.placeholder.com/300x300/667eea/ffffff?text=' + encodeURIComponent(productName);
    
    // Show error overlay
    const container = img.closest('.product-image');
    if (container && !container.querySelector('.image-error')) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'image-error';
        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i><small>Image not found</small>';
        container.appendChild(errorDiv);
    }
}

function handleImageLoad(img) {
    console.log('✅ Image loaded:', img.src);
    img.style.opacity = '1';
}

// Populate Categories from existing products
function populateCategories() {
    const products = document.querySelectorAll('.product-card');
    const categories = new Set();
    
    products.forEach(card => {
        const category = card.dataset.category;
        if (category) categories.add(category);
    });
    
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        // Clear existing options (except "All Categories")
        categoryFilter.innerHTML = '<option value="">All Categories</option>';
        
        // Add categories alphabetically
        const sortedCategories = Array.from(categories).sort();
        sortedCategories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
            categoryFilter.appendChild(option);
        });
        
        console.log('📁 Categories loaded:', sortedCategories.length);
    }
}

// Filter products based on search criteria
function filterProducts() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase().trim() || '';
    const categoryFilter = document.getElementById('categoryFilter')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('statusFilter')?.value || '';
    
    const products = document.querySelectorAll('.product-card');
    let visibleCount = 0;
    
    products.forEach(card => {
        const name = card.dataset.name || '';
        const category = card.dataset.category || '';
        const status = card.dataset.status || '';
        
        // Check each filter
        const matchesSearch = !searchTerm || name.includes(searchTerm);
        const matchesCategory = !categoryFilter || category.includes(categoryFilter);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        // Show/Hide based on all filters
        if (matchesSearch && matchesCategory && matchesStatus) {
            card.style.display = 'block';
            visibleCount++;
            
            // Highlight search term
            if (searchTerm) {
                const h3 = card.querySelector('h3');
                if (h3) {
                    const originalText = h3.dataset.originalText || h3.textContent;
                    h3.dataset.originalText = originalText;
                    
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    h3.innerHTML = originalText.replace(regex, '<mark style="background: #ffd700; padding: 2px 4px; border-radius: 3px;">$1</mark>');
                }
            } else {
                const h3 = card.querySelector('h3');
                if (h3 && h3.dataset.originalText) {
                    h3.textContent = h3.dataset.originalText;
                }
            }
        } else {
            card.style.display = 'none';
        }
    });
    
    // Update results text
    updateSearchResults(visibleCount, products.length);
    
    console.log(`🔍 Filtered: ${visibleCount} / ${products.length} products`);
}

// Update search results text
function updateSearchResults(visibleCount, totalCount) {
    const resultsDiv = document.getElementById('searchResults');
    if (resultsDiv) {
        if (visibleCount === totalCount) {
            resultsDiv.textContent = `Showing ${totalCount} product(s)`;
            resultsDiv.style.color = '#667eea';
        } else {
            resultsDiv.textContent = `Found ${visibleCount} / ${totalCount} product(s)`;
            resultsDiv.style.color = visibleCount > 0 ? '#28a745' : '#dc3545';
        }
    }
}

// Clear all filters
function clearFilters() {
    console.log('🔄 Clearing all filters');
    
    // Reset form fields
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) searchInput.value = '';
    if (categoryFilter) categoryFilter.value = '';
    if (statusFilter) statusFilter.value = '';
    
    // Show all products
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = 'block';
        
        // Remove highlights
        const h3 = card.querySelector('h3');
        if (h3 && h3.dataset.originalText) {
            h3.textContent = h3.dataset.originalText;
        }
    });
    
    // Update results
    const totalProducts = document.querySelectorAll('.product-card').length;
    updateSearchResults(totalProducts, totalProducts);
    
    console.log('✅ Filters cleared');
}

// Debounce function for search input
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize search and filter
function initializeSearchFilter() {
    console.log('🚀 Initializing Search & Filter...');
    
    // Populate categories dropdown
    populateCategories();
    
    // Get elements
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    // Add event listeners with debounce for search
    if (searchInput) {
        const debouncedFilter = debounce(filterProducts, 300);
        searchInput.addEventListener('input', debouncedFilter);
        console.log('✅ Search input listener attached');
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
        console.log('✅ Category filter listener attached');
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterProducts);
        console.log('✅ Status filter listener attached');
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Escape to clear filters
        if (e.key === 'Escape') {
            clearFilters();
        }
    });
    
    console.log('✅ Search & Filter initialized successfully');
    console.log('📊 Total products:', document.querySelectorAll('.product-card').length);
    console.log('💡 Tip: Press Ctrl+K to quick search, ESC to clear');
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSearchFilter);
} else {
    initializeSearchFilter();
}