<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    ShieldCheckIcon,
    TruckIcon,
    MapPinIcon,
} from '@heroicons/vue/24/outline';

interface CartItem {
    product_id: number;
    name: string;
    price: number;
    quantity: number;
    total: number;
    image_url: string | null;
}

interface Cart {
    items: CartItem[];
    item_count: number;
    subtotal: number;
}

interface DeliveryMethod {
    value: string;
    label: string;
    fee: number;
}

const props = defineProps<{
    cart: Cart;
    provinces: string[];
    deliveryMethods: DeliveryMethod[];
}>();

const form = useForm({
    name: '',
    phone: '',
    delivery_method: 'self',
    province: '',
    district: '',
    address: '',
    notes: '',
});

const selectedDeliveryMethod = computed(() => 
    props.deliveryMethods.find(m => m.value === form.delivery_method)
);

const deliveryFee = computed(() => selectedDeliveryMethod.value?.fee || 0);
const total = computed(() => props.cart.subtotal + deliveryFee.value);

const formatPrice = (amount: number) => 'K' + (amount / 100).toFixed(2);

const submit = () => {
    form.post(route('marketplace.checkout.store'));
};
</script>

<template>
    <Head title="Checkout - Marketplace" />
    
    <MarketplaceLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-8">Checkout</h1>

            <form @submit.prevent="submit" class="grid lg:grid-cols-3 gap-8">
                <!-- Delivery Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Contact -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Contact Information</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                <input 
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                    placeholder="Your name"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                <input 
                                    v-model="form.phone"
                                    type="tel"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                    placeholder="0977123456"
                                />
                                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Method -->
                    <div class="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Delivery Method</h2>
                        <div class="space-y-3">
                            <label 
                                v-for="method in deliveryMethods" 
                                :key="method.value"
                                :class="[
                                    'flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-colors',
                                    form.delivery_method === method.value 
                                        ? 'border-orange-500 bg-orange-50' 
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <input 
                                    type="radio" 
                                    v-model="form.delivery_method" 
                                    :value="method.value"
                                    class="text-orange-500 focus:ring-orange-500"
                                />
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ method.label }}</p>
                                </div>
                                <span class="font-semibold text-gray-900">
                                    {{ method.fee === 0 ? 'Free' : formatPrice(method.fee) }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div v-if="form.delivery_method !== 'pickup'" class="bg-white rounded-xl border border-gray-200 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Delivery Address</h2>
                        <div class="space-y-4">
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Province *</label>
                                    <select 
                                        v-model="form.province"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                    >
                                        <option value="">Select province</option>
                                        <option v-for="province in provinces" :key="province" :value="province">
                                            {{ province }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.province" class="mt-1 text-sm text-red-600">{{ form.errors.province }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                                    <input 
                                        v-model="form.district"
                                        type="text"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                        placeholder="Your district"
                                    />
                                    <p v-if="form.errors.district" class="mt-1 text-sm text-red-600">{{ form.errors.district }}</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Street Address *</label>
                                <textarea 
                                    v-model="form.address"
                                    rows="2"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                    placeholder="House number, street name, area..."
                                ></textarea>
                                <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Notes (Optional)</label>
                                <textarea 
                                    v-model="form.notes"
                                    rows="2"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors"
                                    placeholder="Any special instructions..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div>
                    <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-24">
                        <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>
                        
                        <!-- Items -->
                        <div class="space-y-3 mb-4 pb-4 border-b border-gray-200">
                            <div 
                                v-for="item in cart.items" 
                                :key="item.product_id"
                                class="flex gap-3"
                            >
                                <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    <img 
                                        v-if="item.image_url"
                                        :src="item.image_url"
                                        :alt="item.name"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</p>
                                    <p class="text-xs text-gray-500">Qty: {{ item.quantity }}</p>
                                </div>
                                <p class="text-sm font-medium text-gray-900">{{ formatPrice(item.total) }}</p>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="space-y-2 text-sm mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="text-gray-900">{{ formatPrice(cart.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Delivery</span>
                                <span class="text-gray-900">{{ deliveryFee === 0 ? 'Free' : formatPrice(deliveryFee) }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-gray-200 text-base font-semibold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-orange-600">{{ formatPrice(total) }}</span>
                            </div>
                        </div>

                        <!-- Trust Badge -->
                        <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg mb-4">
                            <ShieldCheckIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                            <span class="text-sm text-green-800">Escrow protected payment</span>
                        </div>

                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full px-6 py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Processing...' : 'Place Order' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </MarketplaceLayout>
</template>
