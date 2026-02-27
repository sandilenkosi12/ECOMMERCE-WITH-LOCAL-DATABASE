// Cart functions
function addToCart(productId, quantity = 1) {
    fetch(`cart.php?action=add&id=${productId}&qty=${quantity}`)
        .then(() => {
            updateCartCount();
            showNotification('Product added to cart!');
        });
}

function updateCartCount() {
    // This would need an API endpoint - for demo, we'll reload the count from session
    location.reload();
}

function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'position-fixed top-0 end-0 m-3 p-3 bg-success text-white rounded shadow';
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Quantity buttons
document.querySelectorAll('.quantity-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.quantity-input');
        let value = parseInt(input.value);
        
        if (this.classList.contains('plus')) {
            value++;
        } else if (this.classList.contains('minus') && value > 1) {
            value--;
        }
        
        input.value = value;
    });
});

// Image gallery
document.querySelectorAll('.thumbnail').forEach(thumb => {
    thumb.addEventListener('click', function() {
        const main = document.getElementById('main-image');
        main.src = this.src;
    });
});