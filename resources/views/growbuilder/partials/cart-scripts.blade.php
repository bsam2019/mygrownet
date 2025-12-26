<script>
// Cart state
let cart = JSON.parse(localStorage.getItem('gb_cart_{{ $site->subdomain }}') || '[]');

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartUI();
});

function addToCart(product) {
    const existing = cart.find(item => item.id === product.id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({ ...product, quantity: 1 });
    }
    saveCart();
    updateCartUI();
    toggleCart(true);
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCart();
    updateCartUI();
}

function updateQuantity(productId, delta) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.quantity += delta;
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            saveCart();
            updateCartUI();
        }
    }
}

function saveCart() {
    localStorage.setItem('gb_cart_{{ $site->subdomain }}', JSON.stringify(cart));
}

function updateCartUI() {
    const countEl = document.getElementById('cart-count');
    const emptyEl = document.getElementById('cart-empty');
    const listEl = document.getElementById('cart-list');
    const footerEl = document.getElementById('cart-footer');
    const totalEl = document.getElementById('cart-total');
    
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    // Update count badge
    if (totalItems > 0) {
        countEl.textContent = totalItems;
        countEl.classList.remove('hidden');
    } else {
        countEl.classList.add('hidden');
    }
    
    // Update cart list
    if (cart.length > 0) {
        emptyEl.classList.add('hidden');
        listEl.classList.remove('hidden');
        footerEl.classList.remove('hidden');
        
        listEl.innerHTML = cart.map(item => `
            <div class="flex gap-3 p-3 bg-gray-50 rounded-lg">
                ${item.image ? `<img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded" />` : '<div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center"><svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></div>'}
                <div class="flex-1">
                    <h4 class="font-medium text-sm">${item.name}</h4>
                    <p class="text-blue-600 font-semibold">K${(item.price / 100).toFixed(2)}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <button onclick="updateQuantity(${item.id}, -1)" class="w-6 h-6 bg-gray-200 rounded text-sm hover:bg-gray-300">-</button>
                        <span class="text-sm">${item.quantity}</span>
                        <button onclick="updateQuantity(${item.id}, 1)" class="w-6 h-6 bg-gray-200 rounded text-sm hover:bg-gray-300">+</button>
                        <button onclick="removeFromCart(${item.id})" class="ml-auto text-red-500 text-sm hover:text-red-700">Remove</button>
                    </div>
                </div>
            </div>
        `).join('');
        
        totalEl.textContent = `K${(totalPrice / 100).toFixed(2)}`;
    } else {
        emptyEl.classList.remove('hidden');
        listEl.classList.add('hidden');
        footerEl.classList.add('hidden');
    }
}

function toggleCart(forceOpen = null) {
    const sidebar = document.getElementById('cart-sidebar');
    const overlay = document.getElementById('cart-overlay');
    const isOpen = !sidebar.classList.contains('translate-x-full');
    
    if (forceOpen === true || (!isOpen && forceOpen !== false)) {
        sidebar.classList.remove('translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        sidebar.classList.add('translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function proceedToCheckout() {
    if (cart.length === 0) return;
    window.location.href = '/checkout';
}

function orderViaWhatsApp() {
    if (cart.length === 0) return;
    
    const whatsappNumber = '{{ $site->settings["whatsapp_number"] ?? "" }}';
    if (!whatsappNumber) {
        alert('WhatsApp ordering is not available for this store.');
        return;
    }
    
    let message = `Hi! I'd like to order:\n\n`;
    let total = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        message += `• ${item.name} x${item.quantity} - K${(itemTotal / 100).toFixed(2)}\n`;
    });
    
    message += `\nTotal: K${(total / 100).toFixed(2)}`;
    
    const encodedMessage = encodeURIComponent(message);
    const cleanNumber = whatsappNumber.replace(/[^0-9]/g, '');
    window.open(`https://wa.me/${cleanNumber}?text=${encodedMessage}`, '_blank');
}
</script>
