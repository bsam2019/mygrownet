{{-- Shopping Cart Sidebar --}}
<div id="cart-sidebar" class="fixed inset-y-0 right-0 w-full max-w-md bg-white shadow-xl transform translate-x-full transition-transform duration-300 z-50">
    <div class="flex flex-col h-full">
        {{-- Header --}}
        <div class="flex items-center justify-between p-4 border-b">
            <h2 class="text-lg font-semibold">Shopping Cart</h2>
            <button onclick="toggleCart()" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Close cart">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        {{-- Cart Items --}}
        <div id="cart-items" class="flex-1 overflow-y-auto p-4">
            <div id="cart-empty" class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p>Your cart is empty</p>
            </div>
            <div id="cart-list" class="space-y-4 hidden"></div>
        </div>
        
        {{-- Footer --}}
        <div id="cart-footer" class="border-t p-4 hidden">
            <div class="flex justify-between mb-4">
                <span class="font-medium">Total</span>
                <span id="cart-total" class="font-bold text-lg">K0.00</span>
            </div>
            <button onclick="proceedToCheckout()" class="w-full py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                Proceed to Checkout
            </button>
            <button onclick="orderViaWhatsApp()" class="w-full mt-2 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Order via WhatsApp
            </button>
        </div>
    </div>
</div>

{{-- Cart Overlay --}}
<div id="cart-overlay" onclick="toggleCart()" class="fixed inset-0 bg-black/50 z-40 hidden"></div>

{{-- Floating Cart Button --}}
<button onclick="toggleCart()" id="cart-button" class="fixed bottom-6 right-6 p-4 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 z-30">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
    <span id="cart-count" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center hidden">0</span>
</button>
