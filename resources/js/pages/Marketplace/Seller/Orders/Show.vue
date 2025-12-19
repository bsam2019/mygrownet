<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import { ArrowLeftIcon, TruckIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline';

interface Order {
    id: number;
    order_number: string;
    status: string;
    status_label: string;
    formatted_total: string;
    formatted_subtotal: string;
    formatted_delivery_fee: string;
    delivery_address: { name: string; phone: string; province?: string; district?: string; address?: string };
    delivery_notes?: string;
    created_at: string;
    buyer: { name: string; email: string };
    items: Array<{ id: number; quantity: number; formatted_unit_price: string; formatted_total_price: string; 
        product: { name: string; primary_image_url: string | null } }>;
}

const props = defineProps<{ order: Order }>();
const trackingInfo = ref('');

const markAsShipped = () => {
    router.post(route('marketplace.seller.orders.ship', props.order.id), { tracking_info: trackingInfo.value });
};

const markAsDelivered = () => {
    router.post(route('marketplace.seller.orders.deliver', props.order.id));
};

const cancelOrder = () => {
    const reason = prompt('Reason for cancellation:');
    if (reason) router.post(route('marketplace.seller.orders.cancel', props.order.id), { reason });
};

const formatDate = (date: string) => new Date(date).toLocaleString('en-ZM');
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />
    <MarketplaceLayout>
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="flex items-center gap-4 mb-6">
                <Link :href="route('marketplace.seller.orders.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold">{{ order.order_number }}</h1>
                    <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                </div>
                <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                    {{ order.status_label }}
                </span>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Items -->
                    <div class="bg-white rounded-xl border p-4">
                        <h2 class="font-semibold mb-4">Order Items</h2>
                        <div class="space-y-3">
                            <div v-for="item in order.items" :key="item.id" class="flex gap-3">
                                <div class="w-14 h-14 bg-gray-100 rounded-lg overflow-hidden">
                                    <img v-if="item.product.primary_image_url" :src="item.product.primary_image_url" 
                                        class="w-full h-full object-cover" />
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium">{{ item.product.name }}</p>
                                    <p class="text-sm text-gray-500">{{ item.formatted_unit_price }} × {{ item.quantity }}</p>
                                </div>
                                <p class="font-semibold">{{ item.formatted_total_price }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-if="order.status === 'paid'" class="bg-white rounded-xl border p-4">
                        <h3 class="font-semibold mb-3">Ship Order</h3>
                        <input v-model="trackingInfo" type="text" placeholder="Tracking info (optional)"
                            class="w-full border-gray-300 rounded-lg mb-3" />
                        <button @click="markAsShipped" 
                            class="w-full py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 flex items-center justify-center gap-2">
                            <TruckIcon class="h-5 w-5" /> Mark as Shipped
                        </button>
                    </div>

                    <div v-if="order.status === 'shipped'" class="bg-white rounded-xl border p-4">
                        <button @click="markAsDelivered" 
                            class="w-full py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 flex items-center justify-center gap-2">
                            <CheckCircleIcon class="h-5 w-5" /> Mark as Delivered
                        </button>
                    </div>

                    <div v-if="['pending', 'paid'].includes(order.status)" class="bg-white rounded-xl border p-4">
                        <button @click="cancelOrder" class="w-full py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50">
                            Cancel Order
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Summary -->
                    <div class="bg-white rounded-xl border p-4">
                        <h3 class="font-semibold mb-3">Summary</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span>{{ order.formatted_subtotal }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Delivery</span><span>{{ order.formatted_delivery_fee }}</span></div>
                            <div class="flex justify-between pt-2 border-t font-semibold">
                                <span>Total</span><span class="text-orange-600">{{ order.formatted_total }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer -->
                    <div class="bg-white rounded-xl border p-4">
                        <h3 class="font-semibold mb-3">Customer</h3>
                        <p class="font-medium">{{ order.buyer.name }}</p>
                        <p class="text-sm text-gray-500">{{ order.buyer.email }}</p>
                    </div>

                    <!-- Delivery -->
                    <div class="bg-white rounded-xl border p-4">
                        <h3 class="font-semibold mb-3">Delivery Address</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p class="font-medium text-gray-900">{{ order.delivery_address.name }}</p>
                            <p>{{ order.delivery_address.phone }}</p>
                            <p v-if="order.delivery_address.address">{{ order.delivery_address.address }}</p>
                            <p v-if="order.delivery_address.district">{{ order.delivery_address.district }}, {{ order.delivery_address.province }}</p>
                        </div>
                        <p v-if="order.delivery_notes" class="mt-3 text-sm text-gray-500 italic">{{ order.delivery_notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
