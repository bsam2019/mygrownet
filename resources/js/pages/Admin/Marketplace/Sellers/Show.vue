<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import MarketplaceAdminLayout from '@/layouts/MarketplaceAdminLayout.vue';
import {
    ArrowLeftIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    BuildingStorefrontIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    ShoppingBagIcon,
    CurrencyDollarIcon,
    StarIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Seller {
    id: number;
    business_name: string;
    business_type: string;
    business_description: string;
    province: string;
    district: string;
    address: string;
    phone: string;
    kyc_status: string;
    is_active: boolean;
    rating: number;
    total_orders: number;
    rejection_reason?: string;
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
    products: any[];
    orders: any[];
}

interface Stats {
    total_products: number;
    active_products: number;
    total_orders: number;
    completed_orders: number;
    total_revenue: number;
    disputes: number;
}

const props = defineProps<{
    seller: Seller;
    stats: Stats;
}>();

const showRejectModal = ref(false);
const rejectForm = useForm({
    reason: '',
});

const approve = () => {
    if (confirm('Are you sure you want to approve this seller?')) {
        router.post(route('admin.marketplace.sellers.approve', props.seller.id));
    }
};

const reject = () => {
    rejectForm.post(route('admin.marketplace.sellers.reject', props.seller.id), {
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
};

const suspend = () => {
    if (confirm('Are you sure you want to suspend this seller?')) {
        router.post(route('admin.marketplace.sellers.suspend', props.seller.id));
    }
};

const activate = () => {
    if (confirm('Are you sure you want to activate this seller?')) {
        router.post(route('admin.marketplace.sellers.activate', props.seller.id));
    }
};

const getStatusBadge = (status: string) => {
    return {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
    }[status] || 'bg-gray-100 text-gray-800';
};

const getStatusIcon = (status: string) => {
    return {
        'pending': ClockIcon,
        'approved': CheckCircleIcon,
        'rejected': XCircleIcon,
    }[status] || ClockIcon;
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="`${seller.business_name} - Seller Details`" />
    
    <MarketplaceAdminLayout title="Seller Details">
        <!-- Header with Actions -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <Link :href="route('admin.marketplace.sellers.index')" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-1">
                    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    Back to Sellers
                </Link>
                <h2 class="text-xl font-bold text-gray-900">{{ seller.business_name }}</h2>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <!-- View Shop Button -->
                <Link
                    :href="route('marketplace.seller.show', seller.id)"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    <BuildingStorefrontIcon class="h-5 w-5" aria-hidden="true" />
                    View Shop
                </Link>
                
                <template v-if="seller.kyc_status === 'pending'">
                    <button
                        @click="approve"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                    >
                        <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                        Approve
                    </button>
                    <button
                        @click="showRejectModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                    >
                        <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                        Reject
                    </button>
                </template>
                <template v-else-if="seller.kyc_status === 'approved'">
                    <button
                        v-if="seller.is_active"
                        @click="suspend"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition"
                    >
                        <ExclamationTriangleIcon class="h-5 w-5" aria-hidden="true" />
                        Suspend
                    </button>
                    <button
                        v-else
                        @click="activate"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                    >
                        <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                        Activate
                    </button>
                </template>
            </div>
        </div>

        <!-- Status Banner -->
        <div v-if="seller.kyc_status === 'pending'" class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <ClockIcon class="h-6 w-6 text-yellow-600" aria-hidden="true" />
                <div>
                    <p class="font-medium text-yellow-800">Pending Approval</p>
                    <p class="text-sm text-yellow-700">This seller is waiting for KYC verification and approval.</p>
                </div>
            </div>
        </div>

        <div v-if="seller.kyc_status === 'rejected'" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <XCircleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                <div>
                    <p class="font-medium text-red-800">Application Rejected</p>
                    <p class="text-sm text-red-700">Reason: {{ seller.rejection_reason || 'No reason provided' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Business Details -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <BuildingStorefrontIcon class="h-5 w-5 text-orange-500" aria-hidden="true" />
                        Business Details
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Business Name</label>
                            <p class="text-gray-900">{{ seller.business_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Business Type</label>
                            <p class="text-gray-900">{{ seller.business_type }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Description</label>
                            <p class="text-gray-900">{{ seller.business_description || 'No description provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusBadge(seller.kyc_status)]">
                                <component :is="getStatusIcon(seller.kyc_status)" class="h-3 w-3" aria-hidden="true" />
                                {{ seller.kyc_status }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Registered</label>
                            <p class="text-gray-900">{{ formatDate(seller.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
                    
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <PhoneIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                            <span class="text-gray-900">{{ seller.phone }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <EnvelopeIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                            <span class="text-gray-900">{{ seller.user.email }}</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <MapPinIcon class="h-5 w-5 text-gray-400 mt-0.5" aria-hidden="true" />
                            <div>
                                <p class="text-gray-900">{{ seller.address || 'No address provided' }}</p>
                                <p class="text-sm text-gray-500">{{ seller.district }}, {{ seller.province }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Owner Information -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Owner Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Name</label>
                            <p class="text-gray-900">{{ seller.user.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="text-gray-900">{{ seller.user.email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Stats -->
            <div class="space-y-6">
                <!-- Stats Cards -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Performance</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <ShoppingBagIcon class="h-5 w-5 text-blue-500" aria-hidden="true" />
                                <span class="text-sm text-gray-600">Total Products</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ stats.total_products }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <CheckCircleIcon class="h-5 w-5 text-green-500" aria-hidden="true" />
                                <span class="text-sm text-gray-600">Active Products</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ stats.active_products }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <ShoppingBagIcon class="h-5 w-5 text-purple-500" aria-hidden="true" />
                                <span class="text-sm text-gray-600">Total Orders</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ stats.total_orders }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <CurrencyDollarIcon class="h-5 w-5 text-green-500" aria-hidden="true" />
                                <span class="text-sm text-gray-600">Total Revenue</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ formatCurrency(stats.total_revenue) }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <StarIcon class="h-5 w-5 text-yellow-500" aria-hidden="true" />
                                <span class="text-sm text-gray-600">Rating</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ seller.rating?.toFixed(1) || '0.0' }} ⭐</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <ExclamationTriangleIcon class="h-5 w-5 text-red-500" aria-hidden="true" />
                                <span class="text-sm text-gray-600">Disputes</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ stats.disputes }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    
                    <div class="space-y-2">
                        <Link
                            :href="route('admin.marketplace.products.index', { seller_id: seller.id })"
                            class="block w-full px-4 py-2 text-center text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                        >
                            View Products
                        </Link>
                        <Link
                            :href="route('admin.marketplace.orders.index', { seller_id: seller.id })"
                            class="block w-full px-4 py-2 text-center text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                        >
                            View Orders
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showRejectModal = false"></div>
                
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Seller Application</h3>
                    
                    <form @submit.prevent="reject">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Reason for Rejection <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="rejectForm.reason"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="Please provide a reason for rejecting this seller..."
                                required
                            ></textarea>
                            <p v-if="rejectForm.errors.reason" class="mt-1 text-sm text-red-600">
                                {{ rejectForm.errors.reason }}
                            </p>
                        </div>
                        
                        <div class="flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showRejectModal = false"
                                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="rejectForm.processing"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50"
                            >
                                {{ rejectForm.processing ? 'Rejecting...' : 'Reject Seller' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MarketplaceAdminLayout>
</template>
