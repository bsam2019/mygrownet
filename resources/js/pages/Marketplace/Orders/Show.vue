<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import {
    ArrowLeftIcon,
    ShoppingBagIcon,
    TruckIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    MapPinIcon,
    PhoneIcon,
} from '@heroicons/vue/24/outline';

interface Order {
    id: number;
    order_number: string;
    status: string;
    status_label: string;
    status_color: string;
    subtotal: number;
    delivery_fee: number;
    total: number;
    formatted_subtotal: string;
    formatted_delivery_fee: string;
    formatted_total: string;
    delivery_method: string;
    delivery_address: {
        name: string;
        phone: string;
        province?: string;
        district?: string;
        address?: string;
    };
    delivery_notes?: string;
    created_at: string;
    paid_at?: string;
    shipped_at?: string;
    delivered_at?: string;
    confirmed_at?: string;
    seller: {
        id: number;
        business_name: string;
        phone: string;
        trust_badge: string;
    };
    items: Array<{
        id: number;
        quantity: number;
        unit_price: number;
        total_price: number;
        formatted_unit_price: string;
        formatted_total_price: string;
        product: {
            name: string;
            primary_image_url: string | null;
        };
    }>;
    escrow?: {
        status: string;
        status_label: string;
    };
}

const props = defineProps<{
    order: Order;
}>();

const confirmReceipt = () => {
    if (confirm('Confirm that you have received this order? Funds will be released to the seller.')) {
        router.post(route('marketplace.orders.confirm', props.order.id));
    }
};

const cancelOrder = () => {
    const reason = prompt('Please provide a reason for cancellation:');
    if (reason) {
        router.post(route('marketplace.orders.cancel', props.order.id), { reason });
    }
};

const openDispute = () => {
    const reason = prompt('Please describe the issue with your order:');
    if (reason) {
        router.post(route('marketplace.orders.dispute', props.order.id), { reason });
    }
};

const getStatusColor = (color: string) => ({
    'yellow': 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'blue': 'bg-blue-100 text-blue-800 border-blue-200',
    'purple': 'bg-purple-100 text-purple-800 border-purple-200',
    'teal': 'bg-teal-100 text-teal-800 border-teal-200',
    'green': 'bg-green-100 text-green-800 border-green-200',
    'gray': 'bg-gray-100 text-gray-800 border-gray-200',
    'red': 'bg-red-100 text-red-800 border-red-200',
    'orange': 'bg-orange-100 text-orange-800 border-orange-200',
}[color] || 'bg-gray-100 text-gray-800 border-gray-200');

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-ZM', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const orderSteps = [
    { key: 'pending', label: 'Order Placed', icon: ShoppingBagIcon },
    { key: 'paid', label: 'Payment Confirmed', icon: CheckCircleIcon },
    { key: 'shipped', label: 'Shipped', icon: TruckIcon },
    { key: 'delivered', label: 'Delivered', icon: MapPinIcon },
    { key: 'completed', label: 'Completed', icon: CheckCircleIcon },
];

const getStepStatus = (stepKey: string) => {
    const statusOrder = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'completed'];
    const currentIndex = statusOrder.indexOf(props.order.status);
    const stepIndex = statusOrder.indexOf(stepKey);
    
    if (props.order.status === 'cancelled' || props.order.status === 'disputed') {
        return 'inactive';
    }
    
    if (stepIndex < currentIndex) return 'completed';
    if (stepIndex === currentIndex) return 'current';
    return 'inactive';
};
</script>

<template>
    <Head :title="`Order ${order.order_number} - Marketplace`" />
    
    <MarketplaceLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <Link 
                    :href="route('marketplace.orders.index')"
                    class="p-2 hover:bg-gray-100 rounded-lg"
                    aria-label="Back to orders"
                >
                    <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ order.order_number }}</h1>
                    <p class="text-sm text-gray-500">Placed on {{ formatDate(order.created_at) }}</p>
                </div>
                <span :class="['px-3 py-1 text-sm font-medium rounded-full border', getStatusColor(order.status_color)]">
                    {{ order.status_label }}
                </span>
            </div>

            <!-- Order Progress -->
            <div v-if="!['cancelled', 'disputed', 'refunded'].includes(order.status)" class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div 
                        v-for="(step, index) in orderSteps" 
                        :key="step.key"
                        class="flex flex-col items-center flex-1"
                    >
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center mb-2',
                            getStepStatus(step.key) === 'completed' ? 'bg-green-500 text-white' :
                            getStepStatus(step.key) === 'current' ? 'bg-orange-500 text-white' :
                            'bg-gray-200 text-gray-400'
                        ]">
                            <component :is="step.icon" class="h-5 w-5" aria-hidden="true" />
                        </div>
                        <span :class="[
                            'text-xs text-center',
                            getStepStatus(step.key) === 'inactive' ? 'text-gray-400' : 'text-gray-700'
                        ]">
                            {{ step.label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Escrow Info -->
            <div v-if="order.escrow" class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <CheckCircleIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                    </div>
                    <div>
                        <p class="font-medium text-blue-900">{{ order.escrow.status_label }}</p>
                        <p class="text-sm text-blue-700">
                            {{ order.escrow.status === 'held' 
                                ? 'Your payment is protected until you confirm delivery.' 
                                : 'Payment has been processed.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Order Items -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl border border-gray-200">
                        <div class="p-4 border-b border-gray-200">
                            <h2 class="font-semibold text-gray-900">Order Items</h2>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <div 
                                v-for="item in order.items" 
                                :key="item.id"
                                class="p-4 flex gap-4"
                            >
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    <img 
                                        v-if="item.product.primary_image_url"
                                        :src="item.product.primary_image_url"
                                        :alt="item.product.name"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ item.product.name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ item.formatted_unit_price }} × {{ item.quantity }}
                                    </p>
                                </div>
                                <p class="font-semibold text-gray-900">{{ item.formatted_total_price }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div v-if="order.status === 'delivered'" class="bg-white rounded-xl border border-gray-200 p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Confirm Delivery</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Have you received your order? Confirming will release payment to the seller.
                        </p>
                        <div class="flex gap-3">
                            <button 
                                @click="confirmReceipt"
                                class="flex-1 px-4 py-2 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600"
                            >
                                Confirm Receipt
                            </button>
                            <button 
                                @click="openDispute"
                                class="px-4 py-2 border border-red-300 text-red-600 font-medium rounded-lg hover:bg-red-50"
                            >
                                Report Issue
                            </button>
                        </div>
                    </div>

                    <div v-if="['pending', 'paid'].includes(order.status)" class="bg-white rounded-xl border border-gray-200 p-4">
                        <button 
                            @click="cancelOrder"
                            class="w-full px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                        >
                            Cancel Order
                        </button>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Order Summary -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Order Summary</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="text-gray-900">{{ order.formatted_subtotal }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Delivery</span>
                                <span class="text-gray-900">{{ order.formatted_delivery_fee }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-gray-200 font-semibold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-orange-600">{{ order.formatted_total }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Seller Info -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Seller</h3>
                        <Link 
                            :href="route('marketplace.seller.show', order.seller.id)"
                            class="flex items-center gap-3 hover:bg-gray-50 -mx-2 px-2 py-2 rounded-lg"
                        >
                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                <span class="font-semibold text-orange-600">{{ order.seller.business_name.charAt(0) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ order.seller.trust_badge }} {{ order.seller.business_name }}</p>
                                <p class="text-sm text-gray-500">{{ order.seller.phone }}</p>
                            </div>
                        </Link>
                    </div>

                    <!-- Delivery Address -->
                    <div class="bg-white rounded-xl border border-gray-200 p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Delivery Address</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p class="font-medium text-gray-900">{{ order.delivery_address.name }}</p>
                            <p>{{ order.delivery_address.phone }}</p>
                            <p v-if="order.delivery_address.address">{{ order.delivery_address.address }}</p>
                            <p v-if="order.delivery_address.district">
                                {{ order.delivery_address.district }}, {{ order.delivery_address.province }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
