<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    business: { id: number; name: string; slug: string; currency: string; profile: any };
    cart: Record<string, any>;
    subtotal: number;
    delivery_fee: number;
}>();

const customerName = ref('');
const customerPhone = ref('');
const customerEmail = ref('');
const deliveryAddress = ref('');
const notes = ref('');
const paymentMethod = ref('airtel_money');
const submitting = ref(false);

const total = computed(() => props.subtotal + props.delivery_fee);

const formatPrice = (amount: number) => {
    const cur = props.business.currency || 'ZMW';
    return `${cur} ${Number(amount).toFixed(2)}`;
};

const placeOrder = () => {
    if (!customerName.value || !customerPhone.value || !paymentMethod.value) return;
    submitting.value = true;
    router.post(route('bizboost.public.shop.checkout.store', props.business.slug), {
        customer_name: customerName.value,
        customer_phone: customerPhone.value,
        customer_email: customerEmail.value || null,
        delivery_address: deliveryAddress.value || null,
        notes: notes.value || null,
        payment_method: paymentMethod.value,
    }, {
        preserveState: false,
        onFinish: () => { submitting.value = false; },
    });
};
</script>

<template>
    <Head :title="`Checkout - ${business.name}`" />
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-lg mx-auto px-4 py-4">
            <Link :href="route('bizboost.public.shop', business.slug)" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
                <ArrowLeftIcon class="h-4 w-4" /> Back to shop
            </Link>

            <h1 class="text-xl font-bold text-gray-900 mb-6">Checkout</h1>

            <div class="space-y-4">
                <!-- Contact Info -->
                <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
                    <h2 class="font-semibold text-gray-900">Contact Information</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input v-model="customerName" type="text" required class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="John Doe" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                        <input v-model="customerPhone" type="tel" required class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="+260 97 000 0000" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email (optional)</label>
                        <input v-model="customerEmail" type="email" class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="john@example.com" />
                    </div>
                </div>

                <!-- Delivery -->
                <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
                    <h2 class="font-semibold text-gray-900">Delivery Details</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address (optional)</label>
                        <textarea v-model="deliveryAddress" rows="2" class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="Street, town, landmark..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Order Notes (optional)</label>
                        <textarea v-model="notes" rows="2" class="w-full px-4 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" placeholder="Any special instructions..."></textarea>
                    </div>
                </div>

                <!-- Payment -->
                <div class="bg-white rounded-xl shadow-sm border p-5 space-y-3">
                    <h2 class="font-semibold text-gray-900">Payment Method</h2>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50" :class="paymentMethod === 'airtel_money' ? 'border-emerald-500 bg-emerald-50' : ''">
                        <input v-model="paymentMethod" type="radio" value="airtel_money" class="text-emerald-600" />
                        <span class="text-sm font-medium">Airtel Money</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50" :class="paymentMethod === 'mtn_money' ? 'border-emerald-500 bg-emerald-50' : ''">
                        <input v-model="paymentMethod" type="radio" value="mtn_money" class="text-emerald-600" />
                        <span class="text-sm font-medium">MTN Mobile Money</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50" :class="paymentMethod === 'cash_on_delivery' ? 'border-emerald-500 bg-emerald-50' : ''">
                        <input v-model="paymentMethod" type="radio" value="cash_on_delivery" class="text-emerald-600" />
                        <span class="text-sm font-medium">Cash on Delivery</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50" :class="paymentMethod === 'bank_transfer' ? 'border-emerald-500 bg-emerald-50' : ''">
                        <input v-model="paymentMethod" type="radio" value="bank_transfer" class="text-emerald-600" />
                        <span class="text-sm font-medium">Bank Transfer</span>
                    </label>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-xl shadow-sm border p-5 space-y-3">
                    <h2 class="font-semibold text-gray-900">Order Summary</h2>
                    <div v-for="(item, id) in cart" :key="id" class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ item.name }} x{{ item.quantity }}</span>
                        <span class="font-medium">{{ formatPrice(item.price * item.quantity) }}</span>
                    </div>
                    <div class="border-t pt-3 flex justify-between font-semibold text-lg">
                        <span>Total</span>
                        <span class="text-emerald-600">{{ formatPrice(total) }}</span>
                    </div>
                </div>

                <button @click="placeOrder" :disabled="submitting || !customerName || !customerPhone"
                    class="w-full py-3.5 bg-emerald-600 text-white rounded-xl font-medium hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    {{ submitting ? 'Placing Order...' : 'Place Order' }}
                </button>
            </div>
        </div>
    </div>
</template>
