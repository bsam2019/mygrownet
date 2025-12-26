<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ $site['name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="/" class="text-blue-600 hover:text-blue-700 text-sm mb-4 inline-block">← Back to Store</a>
            <h1 class="text-2xl font-bold text-gray-900">Checkout</h1>
            <p class="text-gray-500">{{ $site['name'] }}</p>
        </div>

        <form id="checkout-form" class="space-y-6">
            <!-- Cart Summary -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                <div id="checkout-items" class="divide-y divide-gray-200"></div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between text-lg font-semibold">
                        <span>Total</span>
                        <span id="checkout-total">K0.00</span>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Details</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="customer_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                        <input type="tel" name="customer_phone" required placeholder="0977123456" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (optional)</label>
                        <input type="email" name="customer_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                        <textarea name="customer_address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h2>
                <div class="space-y-3">
                    @if($paymentSettings['momo_enabled'] ?? false)
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="momo" class="h-4 w-4 text-blue-600" />
                        <div class="flex-1">
                            <span class="font-medium">MTN MoMo</span>
                            <p class="text-sm text-gray-500">Pay with MTN Mobile Money</p>
                        </div>
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="text-yellow-600 font-bold text-xs">MTN</span>
                        </div>
                    </label>
                    @endif

                    @if($paymentSettings['airtel_enabled'] ?? false)
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="airtel" class="h-4 w-4 text-blue-600" />
                        <div class="flex-1">
                            <span class="font-medium">Airtel Money</span>
                            <p class="text-sm text-gray-500">Pay with Airtel Money</p>
                        </div>
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <span class="text-red-600 font-bold text-xs">AM</span>
                        </div>
                    </label>
                    @endif

                    @if($paymentSettings['cod_enabled'] ?? true)
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="cod" checked class="h-4 w-4 text-blue-600" />
                        <div class="flex-1">
                            <span class="font-medium">Cash on Delivery</span>
                            <p class="text-sm text-gray-500">Pay when you receive your order</p>
                        </div>
                    </label>
                    @endif

                    @if($paymentSettings['bank_enabled'] ?? false)
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="bank" class="h-4 w-4 text-blue-600" />
                        <div class="flex-1">
                            <span class="font-medium">Bank Transfer</span>
                            <p class="text-sm text-gray-500">{{ $paymentSettings['bank_name'] ?? 'Bank' }} - {{ $paymentSettings['bank_account_number'] ?? '' }}</p>
                        </div>
                    </label>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Notes (optional)</h2>
                <textarea name="notes" rows="2" placeholder="Any special instructions..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <!-- Submit -->
            <button type="submit" id="submit-btn" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50">
                Place Order
            </button>
        </form>

        <!-- Success Message (hidden by default) -->
        <div id="success-message" class="hidden text-center py-12">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Order Placed!</h2>
            <p class="text-gray-500 mb-4">Your order number is <span id="order-number" class="font-mono font-semibold"></span></p>
            <a href="/" class="text-blue-600 hover:text-blue-700">Continue Shopping</a>
        </div>
    </div>

    <script>
        const subdomain = '{{ $site["subdomain"] }}';
        let cart = JSON.parse(localStorage.getItem('gb_cart_' + subdomain) || '[]');

        document.addEventListener('DOMContentLoaded', function() {
            if (cart.length === 0) {
                window.location.href = '/';
                return;
            }
            renderCheckoutItems();
        });

        function renderCheckoutItems() {
            const container = document.getElementById('checkout-items');
            const totalEl = document.getElementById('checkout-total');
            let total = 0;

            container.innerHTML = cart.map(item => {
                const itemTotal = item.price * item.quantity;
                total += itemTotal;
                return `
                    <div class="py-3 flex justify-between">
                        <div>
                            <p class="font-medium">${item.name}</p>
                            <p class="text-sm text-gray-500">K${(item.price / 100).toFixed(2)} × ${item.quantity}</p>
                        </div>
                        <p class="font-medium">K${(itemTotal / 100).toFixed(2)}</p>
                    </div>
                `;
            }).join('');

            totalEl.textContent = `K${(total / 100).toFixed(2)}`;
        }

        document.getElementById('checkout-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.textContent = 'Processing...';

            const formData = new FormData(this);
            const data = {
                customer_name: formData.get('customer_name'),
                customer_phone: formData.get('customer_phone'),
                customer_email: formData.get('customer_email') || null,
                customer_address: formData.get('customer_address') || null,
                payment_method: formData.get('payment_method'),
                notes: formData.get('notes') || null,
                items: cart.map(item => ({
                    product_id: item.id,
                    name: item.name,
                    price: item.price,
                    quantity: item.quantity
                }))
            };

            try {
                const response = await fetch(`/gb-api/${subdomain}/checkout`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    localStorage.removeItem('gb_cart_' + subdomain);
                    document.getElementById('checkout-form').classList.add('hidden');
                    document.getElementById('success-message').classList.remove('hidden');
                    document.getElementById('order-number').textContent = result.order_number;
                } else {
                    alert(result.message || 'Failed to place order. Please try again.');
                    btn.disabled = false;
                    btn.textContent = 'Place Order';
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
                btn.disabled = false;
                btn.textContent = 'Place Order';
            }
        });
    </script>
</body>
</html>
