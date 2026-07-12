<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import GrowMartLayout from '@/layouts/GrowMartLayout.vue';
import { ShoppingBagIcon, TruckIcon, CheckCircleIcon, XCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface TrackingUpdate {
    status: string;
    message: string;
    timestamp: string;
}

interface OrderItem {
    product_name: string;
    quantity: number;
    unit_price: number;
    unit_price_formatted: string;
    subtotal: number;
    subtotal_formatted: string;
}

interface CouponInfo {
    code: string;
    type: string;
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    payment_status: string;
    payment_method?: string;
    payment_reference?: string;
    payment_phone?: string;
    payment_notes?: string;
    payment_submitted_at?: string;
    delivery_method: string;
    delivery_zone?: string;
    delivery_address?: string;
    contact_phone?: string;
    special_instructions?: string;
    tracking_number?: string;
    tracking_url?: string;
    estimated_delivery_at?: string;
    tracking_updates: TrackingUpdate[];
    subtotal: number;
    subtotal_formatted: string;
    delivery_fee: number;
    delivery_fee_formatted: string;
    discount: number;
    discount_formatted: string;
    total: number;
    total_formatted: string;
    coupon: CouponInfo | null;
    items: OrderItem[];
    created_at: string;
    paid_at?: string;
    delivered_at?: string;
}

interface Props {
    order: Order;
    cartCount: number;
}

const props = defineProps<Props>();

const cancelOrder = () => {
    if (!confirm('Cancel this order?')) return;
    router.post(route('growmart.orders.cancel', props.order.id), {}, { preserveScroll: true });
};

const statusSteps = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered'];
const currentStep = statusSteps.indexOf(props.order.status);

const deliveryLabel = (method: string): string => {
    const map: Record<string, string> = { own_vehicle: 'Own Vehicle', yango: 'Yango Delivery', pickup: 'Store Pickup' };
    return map[method] || method;
};

const paymentLabel = (status: string): string => {
    const map: Record<string, string> = { pending: 'Pending', pending_verification: 'Pending Verification', paid: 'Paid', failed: 'Failed', refunded: 'Refunded' };
    return map[status] || status;
};

const formatPrice = (ngwee: number): string => 'K' + (ngwee / 100).toFixed(2);
</script>

<template>
    <Head :title="'Order ' + order.order_number + ' - GrowMart'" />

    <GrowMartLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="mb-6">
                <Link :href="route('growmart.orders.index')" class="text-sm text-emerald-600 hover:text-emerald-700">← Back to Orders</Link>
            </div>

            <!-- Header -->
            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-4">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ order.order_number }}</h1>
                        <p class="text-sm text-gray-500">Placed on {{ order.created_at }}</p>
                    </div>
                    <div class="text-right">
                        <span :class="order.status === 'delivered' ? 'bg-green-100 text-green-800' : order.status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'" class="px-3 py-1 rounded-full text-sm font-medium capitalize">
                            {{ order.status.replace('_', ' ') }}
                        </span>
                        <p class="text-xs text-gray-500 mt-1">Payment: {{ paymentLabel(order.payment_status) }}</p>
                    </div>
                </div>

                <!-- Progress -->
                <div class="flex items-center gap-0 mb-2">
                    <div v-for="(step, idx) in statusSteps" :key="step" class="flex-1 flex items-center">
                        <div class="flex items-center gap-2">
                            <div :class="idx <= currentStep ? 'bg-emerald-600' : 'bg-gray-200'" class="w-8 h-8 rounded-full flex items-center justify-center">
                                <CheckCircleIcon v-if="idx < currentStep" class="h-5 w-5 text-white" />
                                <ClockIcon v-else-if="idx === currentStep" class="h-5 w-5 text-white" />
                                <span v-else class="text-sm font-medium text-gray-500">{{ idx + 1 }}</span>
                            </div>
                            <span class="text-xs font-medium" :class="idx <= currentStep ? 'text-emerald-700' : 'text-gray-400'">{{ step.replace(/_/g, ' ') }}</span>
                        </div>
                        <div v-if="idx < statusSteps.length - 1" class="flex-1 h-0.5 mx-2" :class="idx < currentStep ? 'bg-emerald-600' : 'bg-gray-200'"></div>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <!-- Items -->
                <div class="md:col-span-2 space-y-4">
                    <div class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Items</h2>
                        <div class="space-y-3">
                            <div v-for="item in order.items" :key="item.product_name" class="flex justify-between items-center pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                                <div>
                                    <p class="font-medium text-gray-900">{{ item.product_name }}</p>
                                    <p class="text-sm text-gray-500">{{ item.quantity }} x {{ item.unit_price_formatted }}</p>
                                </div>
                                <span class="font-medium text-gray-900">{{ item.subtotal_formatted }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Info -->
                    <div v-if="order.delivery_address" class="bg-white rounded-lg border border-gray-200 p-6">
                        <h2 class="font-semibold text-gray-900 mb-3">Delivery Details</h2>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p><span class="font-medium">Method:</span> {{ deliveryLabel(order.delivery_method) }}</p>
                            <p v-if="order.delivery_zone"><span class="font-medium">Zone:</span> {{ order.delivery_zone }}</p>
                            <p><span class="font-medium">Address:</span> {{ order.delivery_address }}</p>
                            <p v-if="order.contact_phone"><span class="font-medium">Phone:</span> {{ order.contact_phone }}</p>
                            <p v-if="order.special_instructions"><span class="font-medium">Notes:</span> {{ order.special_instructions }}</p>
                            <p v-if="order.tracking_number"><span class="font-medium">Tracking:</span>
                                <a v-if="order.tracking_url" :href="order.tracking_url" target="_blank" class="text-emerald-600 hover:underline">{{ order.tracking_number }}</a>
                                <span v-else>{{ order.tracking_number }}</span>
                            </p>
                            <p v-if="order.estimated_delivery_at"><span class="font-medium">Est. Delivery:</span> {{ order.estimated_delivery_at }}</p>
                        </div>

                        <div v-if="order.tracking_updates.length > 0" class="mt-4 pt-4 border-t border-gray-100">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Tracking Updates</h3>
                            <div class="space-y-2">
                                <div v-for="(update, idx) in order.tracking_updates" :key="idx" class="flex gap-2 text-xs">
                                    <div class="w-2 h-2 mt-1 rounded-full bg-emerald-500 shrink-0"></div>
                                    <div>
                                        <p class="font-medium text-gray-700 capitalize">{{ update.status.replace('_', ' ') }}</p>
                                        <p class="text-gray-500">{{ update.message }}</p>
                                        <p class="text-gray-400">{{ new Date(update.timestamp).toLocaleString() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-lg border border-gray-200 p-6 sticky top-24">
                        <h2 class="font-semibold text-gray-900 mb-4">Summary</h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span class="text-gray-900">{{ order.subtotal_formatted }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Delivery</span><span class="text-gray-900">{{ formatPrice(order.delivery_fee) }}</span></div>
                            <div v-if="order.discount > 0" class="flex justify-between"><span class="text-green-600">Discount</span><span class="text-green-600">-{{ order.discount_formatted }}</span></div>
                            <hr />
                            <div class="flex justify-between font-bold text-lg"><span class="text-gray-900">Total</span><span class="text-emerald-700">{{ order.total_formatted }}</span></div>
                        </div>

                        <!-- Payment Submission Info -->
                        <div v-if="order.payment_status === 'pending_verification' && order.payment_reference" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs space-y-1">
                            <p class="font-medium text-blue-800">Payment Submitted</p>
                            <p><strong>Ref:</strong> {{ order.payment_reference }}</p>
                            <p v-if="order.payment_phone"><strong>Phone:</strong> {{ order.payment_phone }}</p>
                            <p v-if="order.payment_submitted_at"><strong>Submitted:</strong> {{ order.payment_submitted_at }}</p>
                            <p class="text-blue-600 mt-1">Awaiting verification.</p>
                        </div>

                        <Link v-if="order.payment_status === 'pending'"
                            :href="route('growmart.payment.show', order.id)"
                            class="mt-4 block w-full text-center py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium">
                            Pay Now
                        </Link>
                        <button v-else-if="order.payment_status === 'pending_verification'"
                            @click="router.get(route('growmart.payment.show', order.id))"
                            class="mt-3 block w-full text-center py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            View Payment Details
                        </button>
                        <button v-if="['pending', 'confirmed'].includes(order.status)" @click="cancelOrder" class="mt-3 w-full text-center py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors text-sm font-medium">
                            Cancel Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GrowMartLayout>
</template>
