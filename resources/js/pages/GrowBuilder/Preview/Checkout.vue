<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { ShoppingBagIcon, ArrowLeftIcon, CheckCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

interface PaymentMethod {
    id: string;
    name: string;
    icon: string;
}

interface CartItem {
    product: { id: number; name: string; price: number; priceFormatted: string; image: string | null };
    quantity: number;
}

const props = defineProps<{
    site: Site;
    settings: Record<string, any> | null;
    paymentMethods: PaymentMethod[];
}>();

const cart = ref<CartItem[]>([]);
const isSubmitting = ref(false);
const orderSuccess = ref(false);
const orderNumber = ref('');
const orderError = ref('');

// Form fields
const customerName = ref('');
const customerPhone = ref('');
const customerEmail = ref('');
const customerAddress = ref('');
const selectedPayment = ref(props.paymentMethods[0]?.id || 'cod');
const notes = ref('');

const cartStorageKey = computed(() => `gb_cart_${props.site.subdomain}`);
const cartTotal = computed(() => cart.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0));
const cartTotalFormatted = computed(() => 'K' + (cartTotal.value / 100).toFixed(2));
const homeUrl = computed(() => `/sites/${props.site.subdomain}`);

onMounted(() => {
    // Load cart from sessionStorage (set by cart sidebar) or localStorage
    const checkoutCart = sessionStorage.getItem(`gb_checkout_${props.site.subdomain}`);
    const savedCart = localStorage.getItem(cartStorageKey.value);
    
    if (checkoutCart) {
        try { cart.value = JSON.parse(checkoutCart); } catch (e) { cart.value = []; }
    } else if (savedCart) {
        try { cart.value = JSON.parse(savedCart); } catch (e) { cart.value = []; }
    }
    
    // Redirect if cart is empty
    if (cart.value.length === 0 && !orderSuccess.value) {
        window.location.href = homeUrl.value;
    }
});

const updateQuantity = (productId: number, qty: number) => {
    const item = cart.value.find(i => i.product.id === productId);
    if (item) {
        if (qty <= 0) {
            cart.value = cart.value.filter(i => i.product.id !== productId);
        } else {
            item.quantity = qty;
        }
    }
};

const removeItem = (productId: number) => {
    cart.value = cart.value.filter(i => i.product.id !== productId);
    if (cart.value.length === 0) {
        window.location.href = homeUrl.value;
    }
};

const submitOrder = async () => {
    if (!customerName.value || !customerPhone.value) {
        orderError.value = 'Please fill in your name and phone number';
        return;
    }

    isSubmitting.value = true;
    orderError.value = '';

    try {
        const response = await fetch(`/gb-api/${props.site.subdomain}/checkout`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                customer_name: customerName.value,
                customer_phone: customerPhone.value,
                customer_email: customerEmail.value || null,
                customer_address: customerAddress.value || null,
                items: cart.value.map(item => ({ product_id: item.product.id, quantity: item.quantity })),
                payment_method: selectedPayment.value,
                notes: notes.value || null,
            }),
        });

        const data = await response.json();

        if (data.success) {
            orderSuccess.value = true;
            orderNumber.value = data.order.order_number;
            
            // Clear cart
            localStorage.removeItem(cartStorageKey.value);
            sessionStorage.removeItem(`gb_checkout_${props.site.subdomain}`);
            
            // Handle WhatsApp redirect
            if (data.whatsapp_url) {
                window.open(data.whatsapp_url, '_blank');
            }
            
            // Handle online payment redirect
            if (data.payment?.checkout_url) {
                window.location.href = data.payment.checkout_url;
            }
        } else {
            orderError.value = data.error || 'Failed to place order. Please try again.';
        }
    } catch (error) {
        orderError.value = 'Network error. Please try again.';
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <Head :title="`Checkout - ${site.name}`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <a :href="homeUrl" class="flex items-center gap-2 text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="w-5 h-5" aria-hidden="true" />
                        <span class="text-sm font-medium">Continue Shopping</span>
                    </a>
                    <span class="text-lg font-bold text-gray-900">Checkout</span>
                    <div class="w-24"></div>
                </div>
            </div>
        </nav>

        <!-- Order Success -->
        <div v-if="orderSuccess" class="max-w-lg mx-auto px-4 py-16 text-center">
            <CheckCircleIcon class="w-20 h-20 mx-auto text-green-500 mb-6" aria-hidden="true" />
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Order Placed Successfully!</h1>
            <p class="text-gray-600 mb-4">Your order number is <span class="font-semibold">{{ orderNumber }}</span></p>
            <p class="text-sm text-gray-500 mb-8">We'll contact you shortly to confirm your order.</p>
            <a :href="homeUrl" class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                Back to Store
            </a>
        </div>

        <!-- Checkout Form -->
        <main v-else class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Form -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Contact Info -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Contact Information</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                <input v-model="customerName" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Your full name" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                <input v-model="customerPhone" type="tel" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="0977123456" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email (Optional)</label>
                                <input v-model="customerEmail" type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="your@email.com" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address (Optional)</label>
                                <textarea v-model="customerAddress" rows="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" placeholder="Your delivery address"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Payment Method</h2>
                        <div class="space-y-3">
                            <label v-for="method in paymentMethods" :key="method.id" class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" :class="selectedPayment === method.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                <input v-model="selectedPayment" type="radio" :value="method.id" class="w-4 h-4 text-blue-600" />
                                <span class="text-xl">{{ method.icon }}</span>
                                <span class="font-medium">{{ method.name }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Order Notes (Optional)</h2>
                        <textarea v-model="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" placeholder="Any special instructions..."></textarea>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                        <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                        
                        <div class="space-y-4 mb-6">
                            <div v-for="item in cart" :key="item.product.id" class="flex gap-3">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    <img v-if="item.product.image" :src="item.product.image" :alt="item.product.name" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                        <ShoppingBagIcon class="w-6 h-6" aria-hidden="true" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-medium line-clamp-1">{{ item.product.name }}</h3>
                                    <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
                                    <p class="text-sm font-semibold text-blue-600">{{ item.product.priceFormatted }}</p>
                                </div>
                                <button @click="removeItem(item.product.id)" class="text-gray-400 hover:text-red-500" aria-label="Remove item">
                                    <XMarkIcon class="w-5 h-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>

                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span>{{ cartTotalFormatted }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Shipping</span>
                                <span class="text-green-600">Free</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold pt-2 border-t">
                                <span>Total</span>
                                <span class="text-blue-600">{{ cartTotalFormatted }}</span>
                            </div>
                        </div>

                        <div v-if="orderError" class="mt-4 p-3 bg-red-50 text-red-600 text-sm rounded-lg">{{ orderError }}</div>

                        <button @click="submitOrder" :disabled="isSubmitting || cart.length === 0" class="w-full mt-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ isSubmitting ? 'Processing...' : 'Place Order' }}
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>
